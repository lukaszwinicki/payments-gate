<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Jobs\ProcessWebhookJob;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Services\NodaService;
use App\Services\PaynowService;
use App\Services\TPayService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    #[DataProvider('createTransactionBodyProviderWithInvalidData')]
    public function test_create_transaction_with_invalid_data(array $transactionBody): void
    {
        Merchant::factory()->create();

        $response = $this->withHeaders([
            'x-api-key' => 'testowy-api-key'
        ])->postJson('/api/create-transaction', $transactionBody);
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'error' => [
                'amount',
                'name'
            ]
        ]);
    }

    #[DataProvider('createTransactionFailedProvider')]
    public function test_create_transaction_failed(array $transactionBody, string $serviceClass, array $mockResponse): void
    {
        $mock = new MockHandler($mockResponse);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $service = new $serviceClass($client);
        $this->app->bind($serviceClass, fn() => $service);

        Merchant::factory()->create();

        $response = $this->withHeaders([
            'x-api-key' => 'testowy-api-key'
        ])->postJson('/api/create-transaction', $transactionBody);
        $response->assertStatus(500);
        $response->assertJson([
            'error' => 'The transaction could not be completed'
        ]);
    }

    #[DataProvider('paymentGatewaysTransactionLifecycleProvider')]
    public function test_create_transaction_is_created_and_confirmed(string $serviceClass, array $transactionBody, array $mockResponse, array $headers, array $webhookBody): void
    {
        Str::createUuidsUsing(fn() => Uuid::fromString('8fe22800-d5ed-40e3-8dda-5289bc29e314'));
        $mock = new MockHandler($mockResponse);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $service = new $serviceClass($client);
        $this->app->bind($serviceClass, fn() => $service);

        Merchant::factory()->create();

        $response = $this->withHeaders([
            'x-api-key' => 'testowy-api-key'
        ])->postJson('/api/create-transaction', $transactionBody);
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertIsString($content);
        $result = json_decode($content, true);
        $this->assertEquals('https://example.com/link', $result['link']);
        $transaction = Transaction::where('transaction_uuid', $result['transactionUuid'])->first();
        $this->assertNotNull($transaction);

        Queue::fake();

        $response = $this->withHeaders($headers)
            ->post('/api/confirm-transaction?payment-method=' . $transactionBody['paymentMethod'], $webhookBody);
        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'transaction_uuid' => $transaction->transaction_uuid,
            'status' => TransactionStatus::SUCCESS
        ]);

        Queue::assertPushed(ProcessWebhookJob::class, function ($job) use ($transaction) {
            return $job->transaction->transaction_uuid === $transaction->transaction_uuid;
        });
    }

    #[DataProvider('confirmTransactionWithWebookBodyOrSignatureInvalid')]
    public function test_confirm_transaction_with_webhook_body_or_signature_invalid(array $headers, array $webhookBody, string $paymentMethod): void
    {
        $response = $this->withHeaders($headers)
            ->post('/api/confirm-transaction?payment-method=' . $paymentMethod, $webhookBody);
        $response->assertStatus(500);
        $response->assertJson([
            'error' => 'Invalid webhook payload or signature.'
        ]);
    }

    #[DataProvider('confirmTransactionStatusRefundProvider')]
    public function test_confirm_transaction_with_status_refund($headers, $webHookBody, $paymentMethod): void
    {
        Transaction::factory()->create([
            'transaction_uuid' => $webHookBody['tr_crc']
        ]);
    
        $uuid = $webHookBody['tr_crc'];
        $transaction = Transaction::where('transaction_uuid', $uuid)->first();

        Queue::fake();

        $response = $this->withHeaders($headers)
            ->post('/api/confirm-transaction?payment-method=' . $paymentMethod, $webHookBody);
        $response->assertStatus(200);
        $response->assertSee('TRUE');

        $this->assertNotNull($transaction);
        $this->assertDatabaseHas('transactions', [
            'transaction_uuid' => $uuid,
            'status' => TransactionStatus::REFUND_SUCCESS,
        ]);

        Queue::assertPushed(ProcessWebhookJob::class, function ($job) use ($uuid) {
            return $job->transaction->transaction_uuid === $uuid;
        });
    }

    public function test_refund_payment_missing_transactionUuid(): void
    {
        $refundBody = [
            'transactionUuid' => ''
        ];

        Merchant::factory()->create();

        $response = $this->withHeaders([
            'x-api-key' => 'testowy-api-key'
        ])->post('/api/refund-payment', $refundBody);
        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'Missing or invalid data.'
        ]);
    }

    #[DataProvider('serviceAndMockProvider')]
    public function test_refund_payment_invalid_transactionUuid(array $mockResponse, string $serviceClass): void
    {
        $refundBody = [
            'transactionUuid' => 'invalid-uuid'
        ];

        $mock = new MockHandler($mockResponse);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $service = new $serviceClass($client);
        $this->app->bind($serviceClass, fn() => $service);

        Merchant::factory()->create();

        $response = $this->withHeaders([
            'x-api-key' => 'testowy-api-key'
        ])->post('/api/refund-payment', $refundBody);
        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'Missing or invalid data.'
        ]);
    }

    public function test_refund_payment_transaction_is_refunded(): void
    {
        $refundBody = [
            'transactionUuid' => '470ea80c-3929-4ee7-964e-c9eb4ac422df'
        ];

        Merchant::factory()->create();
        $transaction = Transaction::factory()->create([
            'transaction_uuid' => '470ea80c-3929-4ee7-964e-c9eb4ac422df',
            'status' => TransactionStatus::REFUND_SUCCESS
        ]);

        $response = $this->withHeaders([
            'x-api-key' => 'testowy-api-key'
        ])->post('/api/refund-payment', $refundBody);
        $response->assertStatus(500);
        $response->assertJson([
            'error' => 'Refund payment not completed.'
        ]);

        $this->assertDatabaseHas('transactions', [
            'transaction_uuid' => $transaction->transaction_uuid
        ]);
        $this->assertEquals(TransactionStatus::REFUND_SUCCESS, $transaction->status);
    }

    #[DataProvider('refundTransactionIsSuccessProvider')]
    public function test_refund_payment_is_success(string $uuid, array $factoryBody, $mockResponse, $serviceClass, $refundBody): void
    {

        Merchant::factory()->create();
        Transaction::factory()->create($factoryBody);

        $mock = new MockHandler($mockResponse);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $service = new $serviceClass($client);
        $this->app->bind($serviceClass, fn() => $service);

        Queue::fake();

        $response = $this->withHeaders([
            'x-api-key' => 'testowy-api-key'
        ])->post('/api/refund-payment', $refundBody);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => 'Refund'
        ]);

        $content = $response->getContent();
        $this->assertIsString($content);
        $result = json_decode($content, true);
        $this->assertEquals($uuid, $result['transactionUuid']);
        $transaction = Transaction::where('transaction_uuid', $result['transactionUuid'])->first();
        $this->assertNotNull($transaction);
        $this->assertDatabaseHas('transactions', [
            'transaction_uuid' => $transaction->transaction_uuid
        ]);
        $this->assertEquals(TransactionStatus::REFUND_PENDING, $transaction->status);
    }

    public static function createTransactionBodyProviderWithInvalidData(): array
    {
        return [
            'TPAY' => [
                [
                    'email' => 'jankowalski@gmail.com',
                    'paymentMethod' => 'TPAY',
                    'notificationUrl' => 'https://notification.url',
                ]
            ],
            'PAYNOW' => [
                [
                    'email' => 'jankowalski@gmail.com',
                    'paymentMethod' => 'PAYNOW',
                    'notificationUrl' => 'https://notification.url'
                ]
            ],
            'NODA' => [
                [
                    'email' => 'jankowalski@gmail.com',
                    'paymentMethod' => 'NODA',
                    'notificationUrl' => 'https://notification.url'
                ]
            ]
        ];
    }

    public static function createTransactionFailedProvider(): array
    {
        return [
            'TPAY' => [
                [
                    'amount' => 100,
                    'email' => 'jankowalski@gmail.com',
                    'name' => 'Jan Kowalski',
                    'paymentMethod' => 'TPAY',
                    'notificationUrl' => 'https://notification.url'
                ],
                TPayService::class,
                [
                    new Response(200, [], json_encode(['access_token' => 'mock-token'])),
                    new Response(500, [], '')
                ]
            ],
            'PAYNOW' => [
                [
                    'amount' => 100,
                    'email' => 'jankowalski@gmail.com',
                    'name' => 'Jan Kowalski',
                    'currency' => 'PLN',
                    'paymentMethod' => 'PAYNOW',
                    'notificationUrl' => 'https://notification.url'
                ],
                PaynowService::class,
                [
                    new Response(500, [], '')
                ]
            ],
            'NODA' => [
                [
                    'amount' => 100,
                    'email' => 'jankowalski@gmail.com',
                    'name' => 'Jan Kowalski',
                    'currency' => 'USD',
                    'paymentMethod' => 'NODA',
                    'notificationUrl' => 'https://notification.url'
                ],
                NodaService::class,
                [
                    new Response(500, [], '')
                ]
            ]
        ];
    }

    public static function paymentGatewaysTransactionLifecycleProvider(): array
    {
        return [
            'TPAY' => [
                TPayService::class,
                [
                    'amount' => 100,
                    'email' => 'jankowalski@gmail.com',
                    'name' => 'Jan Kowalski',
                    'paymentMethod' => 'TPAY',
                    'notificationUrl' => 'https://notification.url',
                ],
                [
                    new Response(200, [], json_encode(['access_token' => 'mock-token'])),
                    new Response(200, [], json_encode([
                        'transactionId' => '12345',
                        'hiddenDescription' => '8fe22800-d5ed-40e3-8dda-5289bc29e314',
                        'payer' => [
                            'name' => 'Jan Kowalski',
                            'email' => 'jankowalski@gmail.com',
                        ],
                        'amount' => 100,
                        'currency' => 'PLN',
                        'transactionPaymentUrl' => 'https://example.com/link',
                    ])),
                ],
                [
                    'x-jws-signature' => ['eyJhbGciOiJSUzI1NiIsIng1dSI6Imh0dHBzOlwvXC9zZWN1cmUuc2FuZGJveC50cGF5LmNvbVwveDUwOVwvbm90aWZpY2F0aW9ucy1qd3MucGVtIn0..HalI3jDvtvVGCn5wSiUUTH4ZSjAbbSEo2nbcI_PfqJIVgnH_vW6RMLEfOxfDQLkpZZoGNwgYg9fqw3h3zBt11qPDXn_m79iNnBe0-stLk4TPBDUEATEULIkpjLolFm727kNtZs2cxyfZm03SwtVOC7WrOQTEXqCZXtrGhONy-Sz_j4duG-haiAOvKLAbCJC4eRQvjxfX0LaUWhqscmJitZrJjb_l7THT-cA5Pq7FA4zbJMgbAHzRE6we41fFeQXal4Je3s5_KwbDzdXwpaYCo2MhOZTBEaUsMdvZHwKfKPYW7WcLhhqG_-tNaa8ZNTHrh8_B0wvKet4ReBPhAAfjwFjRQ2t4hX8Ukx0OYuTBz2LRh9Z-Gy7YQWR7-da67kwdTlKIfhtvUfwtu62PgV5LkVX9bll_27mKwZfwJvESqJoO4AUV-_Xn7g4sLNG_CkPc7QDk4TIXnKZuj19uvwEW4UVZlXmL4R2FaGJqmEPspBljPhte9Ez-avc1QmfV0WS4AF94GRMBy5XO-1ubiUZq9x6xlUIlwZ3Du7lz71uNpLHoZu2aBTm8ZnsB9zrVkfN1ZVuxEQ7Kx723bJdTH9KerT0fjTa7j9fKMBmwL64XqZmLMRAJmeo_iIfXVgYtdDunHOSYzat16QXbqDLykCxIHUcQ0UnJAai8LlAbGq66q2g'],
                ],
                [
                    "id" => "401406",
                    "tr_id" => "TR-4H6K-2KTYDX",
                    "tr_date" => "2025-04-14 16:48:43",
                    "tr_crc" => "8fe22800-d5ed-40e3-8dda-5289bc29e314",
                    "tr_amount" => "10.00",
                    "tr_paid" => "10.00",
                    "tr_desc" => "8fe22800-d5ed-40e3-8dda-5289bc29e314",
                    "tr_status" => "TRUE",
                    "tr_error" => "none",
                    "tr_email" => "artur18@wp.pl",
                    "test_mode" => "0",
                    "md5sum" => "697ba8f227ef5629e953d33a10de35f4"
                ],
            ],
            'PAYNOW' => [
                PaynowService::class,
                [
                    'amount' => 10,
                    'email' => 'jankowalski@gmail.com',
                    'name' => 'Jan Kowalski',
                    'currency' => 'PLN',
                    'paymentMethod' => 'PAYNOW',
                    'notificationUrl' => 'https://notification.url',
                ],
                [
                    new Response(201, [], json_encode([
                        'paymentId' => '12345',
                        'redirectUrl' => 'https://example.com/link',
                    ])),
                ],
                [
                    'signature' => ['bM4K/b1PFP3ic2K+rf3j1UnF7yU0bqt9dJjQvhJ4qMw=']
                ],
                [
                    'paymentId' => '12345',
                    'externalId' => '8fe22800-d5ed-40e3-8dda-5289bc29e314',
                    'status' => 'CONFIRMED'
                ],
            ],
            'NODA' => [
                NodaService::class,
                [
                    'amount' => 10,
                    'email' => 'jankowalski@gmail.com',
                    'name' => 'Jan Kowalski',
                    'currency' => 'USD',
                    'paymentMethod' => 'NODA',
                    'notificationUrl' => 'https://notification.url',
                ],
                [
                    new Response(200, [], json_encode([
                        'id' => 'test-12345',
                        'url' => 'https://example.com/link'
                    ])),
                ],
                [
                    // Signature is included in $webHookBody; no signature is sent via headers.
                ],
                [
                    'PaymentId' => '717b62f9-d92c-4faa-99a5-b967a2eb14fc',
                    'Status' => 'Done',
                    'Signature' => 'a0be8a3b50aec5bd14684c0a46c8a1278fb134870607d0cd36ada937432161c3',
                    'MerchantPaymentId' => '8fe22800-d5ed-40e3-8dda-5289bc29e314'
                ],
            ]
        ];
    }
    public static function confirmTransactionWithWebookBodyOrSignatureInvalid(): array
    {
        return [
            'TPAY' => [
                [
                    'x-jws-signature' => ['Uuc2FuZGJveC50cGF5LmNvbVwveDUwOVwvbm90aWZpY2F0aW9ucy1qd3MucGVtIn0..HalI3jDvtvVGCn5wSiUUTH4ZSjAbbSEo2nbcI_PfqJIVgnH_vW6RMLEfOxfDQLkpZZoGNwgYg9fqw3h3zBt11qPDXn_m79iNnBe0-stLk4TPBDUEATEULIkpjLolFm727kNtZs2cxyfZm03SwtVOC7WrOQTEXqCZXtrGhONy-Sz_j4duG-haiAOvKLAbCJC4eRQvjxfX0LaUWhqscmJitZrJjb_l7THT-cA5Pq7FA4zbJMgbAHzRE6we41fFeQXal4Je3s5_KwbDzdXwpaYCo2MhOZTBEaUsMdvZHwKfKPYW7WcLhhqG_-tNaa8ZNTHrh8_B0wvKet4ReBPhAAfjwFjRQ2t4hX8Ukx0OYuTBz2LRh9Z-Gy7YQWR7-da67kwdTlKIfhtvUfwtu62PgV5LkVX9bll_27mKwZfwJvESqJoO4AUV-_Xn7g4sLNG_CkPc7QDk4TIXnKZuj19uvwEW4UVZlXmL4R2FaGJqmEPspBljPhte9Ez-avc1QmfV0WS4AF94GRMBy5XO-1ubiUZq9x6xlUIlwZ3Du7lz71uNpLHoZu2aBTm8ZnsB9zrVkfN1ZVuxEQ7Kx723bJdTH9KerT0fjTa7j9fKMBmwL64XqZmLMRAJmeo_iIfXVgYtdDunHOSYzat16QXbqDLykCxIHUcQ0UnJAai8LlAbGq66q2g']
                ],
                [
                    'data' => 'invalid'
                ],
                'TPAY',
            ],
            'PAYNOW' => [
                [
                    'signature' => ['invalid-signature']
                ],
                [
                    'data' => 'invalid'
                ],
                'PAYNOW'
            ],
            'NODA' => [
                [
                    // Signature is included in $webHookBody; no signature is sent via headers.
                ],
                [
                    'PaymentId' => '717b62f9-d92c-4faa-99a5-b967a2eb14fc',
                    'Status' => 'Done',
                    'Signature' => 'invalid-signature',
                ],
                'NODA'
            ]
        ];
    }

    public static function confirmTransactionStatusRefundProvider(): array
    {
        return [
            'TPAY' => [
                [
                    'x-jws-signature' => ['eyJhbGciOiJSUzI1NiIsIng1dSI6Imh0dHBzOlwvXC9zZWN1cmUuc2FuZGJveC50cGF5LmNvbVwveDUwOVwvbm90aWZpY2F0aW9ucy1qd3MucGVtIn0..m12kqrTYyviJtYtVQeoDHtbt_gwHO5KB-MqLSKWmxxYR7VnWgx_Kbvbhet69gREtBeKtf_YRCKdpNeJBxiTgoBisvo5lzSTqiaYimE_dXCc6Hah-j8xGKKFucqV7gauC-NK0KWsT9uE1q5nTSXu4XCxUd1X2jdU9He9Ua8MD37Y7-4GZfu4q9L3SctVPZCgzGMLsW1kvErrF_j3WruiANC95Ynyi7DvgNaLSx-7bdKuFPRuFle4V8ehauCr3Znwp4i79hjx-E7uLHZRMIEEbA5PaBWvk8N1fqT-w0Mwh_k4Ywzg86vInLmthONaVl5NsUPVUDmizvLTbVGTta6LxziXZwSY-7lTNbqHrtX70hptpml52kVuwq8ipArSbc07J-X5Fe2ADSIdx3gNCf1kJVK8Eu49cTnTfaAGcA1Jaz4Tdbe0ou1TI57MZwl-K_oUjhkFiGT5ZpPvEs8M-012lLI7yNAXs3HjPf4hTQDCCKytvzRPQFGXmFEZCJ_jiQNpmg5TBFA3r8FqMGfHf1R9HimL8WMWUL36LOCB-BguJMN80w50rHmjgroFm0_YGbxIjOCl3gHBhZF5lV-JrpyfyZhempoLXQmlVoxHjdkkJRSc6m1jDhca88vQJ7OQbUlyyEXmzhhKTMLUp7MFephvzDunSKPvJQ2h_UkhmmBoIYac'],
                ],
                [
                    "id" => "401406",
                    "tr_id" => "TR-4H6K-2KU78X",
                    "tr_date" => "2025-04-15 00:47:24",
                    "tr_crc" => "a875e396-3c67-4415-a865-1fcea225154e",
                    "tr_amount" => "10.00",
                    "tr_paid" => "10.00",
                    "tr_desc" => "a875e396-3c67-4415-a865-1fcea225154e",
                    "tr_status" => "CHARGEBACK",
                    "tr_error" => "none",
                    "tr_email" => "artur18@wp.pl",
                    "test_mode" => "0",
                    "md5sum" => "1caca1c5bd0aef725279392418281d74"
                ],
                'TPAY'
            ],
        ];
    }

    public static function serviceAndMockProvider(): array
    {
        return [
            'TPAY' => [
                [
                    new Response(200, [], json_encode(['access_token' => 'mock-token'])),
                    new Response(400, [], ''),
                ],
                TPayService::class
            ],
            'PAYNOW' => [
                [
                    new Response(400, [], ''),
                ],
                PaynowService::class
            ]
        ];
    }

    public static function refundTransactionIsSuccessProvider(): array
    {
        return [
            'TPAY' => [
                '470ea80c-3929-4ee7-964e-c9eb4ac422df',
                [
                    'transaction_uuid' => '470ea80c-3929-4ee7-964e-c9eb4ac422df',
                    'payment_method' => 'TPAY'
                ],
                [
                    new Response(200, [], json_encode(['access_token' => 'mock-token'])),
                    new Response(200, [], json_encode(['result' => 'success', 'status' => 'refund'])),
                ],
                TPayService::class,
                [
                    'transactionUuid' => '470ea80c-3929-4ee7-964e-c9eb4ac422df',
                ]
            ],
            'PAYNOW' => [
                '470ea80c-3929-4ee7-964e-c9eb4ac422df',
                [
                    'transaction_uuid' => '470ea80c-3929-4ee7-964e-c9eb4ac422df',
                    'payment_method' => 'PAYNOW'
                ],
                [
                    new Response(201, [], json_encode(['refundId' => '12345', 'status' => 'PENDING'])),
                ],
                PaynowService::class,
                [
                    'transactionUuid' => '470ea80c-3929-4ee7-964e-c9eb4ac422df',
                ]
            ]
        ];
    }
}