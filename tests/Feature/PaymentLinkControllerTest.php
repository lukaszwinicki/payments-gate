<?php

namespace Tests\Feature;

use App\Models\Merchant;
use App\Models\PaymentLink;
use App\Models\Transaction;
use App\Services\TPayService;
use App\Services\PaynowService;
use App\Services\NodaService;
use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class PaymentLinkControllerTest extends TestCase
{
    use RefreshDatabase;

    public function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
    
    public function test_create_payment_link_returns_400_if_api_key_is_missing(): void
    {
        $response = $this
            ->withHeaders([
                'x-api-key' => ''
            ])->postJson('/api/create-payment-link');

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Unauthorized'
            ]);
    }

    public function test_create_payment_link_fails_validation(): void
    {
        Merchant::factory()->create();

        $payload = [
            'currency' => 'PLN',
            'expiresAt' => '10.10.2025 12:55:00',
            'notificationUrl' => 'https://notofication.url',
            'returnUrl' => 'https://return.url'
        ];

        $apiKey = 'testowy-api-key';

        $response = $this
            ->withHeaders([
                'x-api-key' => $apiKey,
            ])
            ->postJson('/api/create-payment-link', $payload);

        $response->assertStatus(422);

        $response->assertJsonStructure([
            'error' => [
                'amount',
            ],
        ]);
    }
    
    public function test_create_payment_link_returns_null(): void
    {
        $this->withoutMiddleware(); 
        Merchant::factory()->create();

        $payload = [
            'amount' => 10.00,
            'currency' => 'PLN',
            'expiresAt' => now()->addHour()->toAtomString(),
            'notificationUrl' => 'https://notification.url',
            'returnUrl' => 'https://return.url'
        ];

        $response = $this
            ->withHeaders([
                'x-api-key' => 'non-existing-api-key'
            ])->postJson('/api/create-payment-link', $payload);

        $response->assertStatus(500)
            ->assertJson([
                'error' => 'The payment link could not be generated'
            ]);
    }
    
    #[DataProvider('createPaymentLinkPayloadAndApiKey')]
    public function test_create_payment_link_returns_successful_response_when_transaction_is_created(array $payload, string $apiKey): void
    {
        Merchant::factory()->create();

        $response = $this
            ->withHeaders([
                'x-api-key' => $apiKey
            ])->postJson('/api/create-payment-link', $payload);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['paymentLink']);

        $json = $response->json();
        $frontendUrl = Config::get('app.frontendUrl');

        $this->assertStringStartsWith($frontendUrl . '/payment/', $json['paymentLink']);
    }

    public function test_payment_details_payment_link_not_found(): void
    {
        $response = $this->getJson('/api/payment/aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Payment link not found'
            ]);
    }

    public function test_payment_details_payment_link_expired(): void
    {
        $paymentLink = PaymentLink::factory()->create([
            'expires_at' => new \DateTimeImmutable('2025-10-19 11:00:00')
        ]);

        $response = $this->getJson('/api/payment/' . $paymentLink->payment_link_id);

        $response->assertStatus(410)
            ->assertJson([
                'error' => 'Payment link expired'
            ]);
    }

    public function test_payment_details_payment_link_was_created_successfully(): void
    {
        $paymentLink = PaymentLink::factory()->create([
            'expires_at' => (new \DateTimeImmutable())->add(new \DateInterval('PT1H'))
        ]);

        $response = $this->getJson('/api/payment/' . $paymentLink->payment_link_id);

        $response->assertStatus(200)
            ->assertJson([
                'payment' => [
                    'paymentLinkId' => $paymentLink->payment_link_id,
                    'amount' => $paymentLink->amount,
                    'currency' => $paymentLink->currency,
                ],
                'transaction' => null
            ]);
    }

    public function test_payment_details_returns_transaction_data_if_transaction_exists(): void
    {
        $transaction = Transaction::factory()->create([
            'status' => TransactionStatus::SUCCESS,
            'amount' => 10.0,
            'currency' => 'PLN',
            'payment_method' => PaymentMethod::PAYMENT_METHOD_PAYNOW,
        ]);

        $paymentLink = PaymentLink::factory()->create([
            'payment_link_id' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
            'transaction_id' => $transaction->id,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'expires_at' => (new \DateTimeImmutable())->add(new \DateInterval('PT1H'))
        ]);

        $response = $this->getJson('/api/payment/' . $paymentLink->payment_link_id);

        $response->assertStatus(200);
        $this->assertEquals('aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee', $response->json('payment.paymentLinkId'));
        $this->assertEquals(10.00, $response->json('payment.amount'));
        $this->assertEquals('PLN', $response->json('payment.currency'));
        $this->assertEquals('SUCCESS', $response->json('transaction.status'));
        $this->assertEquals(10.00, $response->json('transaction.amount'));
        $this->assertEquals('PLN', $response->json('transaction.currency'));
        $this->assertEquals('PAYNOW', $response->json('transaction.paymentMethod'));
    }

    public function test_confirm_payment_link_returns_null(): void
    {
        $payload = [
            'paymentLinkId' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
            'paymentMethod' => 'TPAY',
            'fullname' => 'Jan Kowalski',
            'email' => 'test@email.com'
        ];

        $response = $this->postJson('/api/confirm-payment-link', $payload);

        $response->assertStatus(500)
            ->assertJson([
                'error' => 'The transaction could not be completed'
            ]);
    }

    #[DataProvider('confirmPaymentLinkProvider')]
    public function test_confirm_payment_link_returns_successful_response_when_transaction_is_created(string $serviceClass, array $payload, array $mockResponse, array $paymentLinkDb): void
    {
        $mock = new MockHandler($mockResponse);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $service = new $serviceClass($client);
        $this->app->bind($serviceClass, fn() => $service);

        Merchant::factory()->create([
            'id' => 1
        ]);
        PaymentLink::factory()->create($paymentLinkDb);

        $response = $this->postJson('/api/confirm-payment-link', $payload);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['link']);
    }

    public static function createPaymentLinkPayloadAndApiKey(): array
    {
        return [
            'payload' => [
                [
                    'amount' => 10.00,
                    'currency' => 'PLN',
                    'expiresAt' => now()->addHour()->toAtomString(),
                    'notificationUrl' => 'https://notofication.url',
                    'returnUrl' => 'https://return.url'
                ],
                'testowy-api-key'
            ]
        ];
    }

    public static function confirmPaymentLinkProvider(): array
    {
        return [
            'TPAY' => [
                TPayService::class,
                [
                    'paymentLinkId' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                    'paymentMethod' => 'TPAY',
                    'fullname' => 'Jan Kowalski',
                    'email' => 'test@email.com'
                ],
                [
                    new Response(200, [], json_encode(['access_token' => 'mock-token'], JSON_THROW_ON_ERROR)),
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
                    ], JSON_THROW_ON_ERROR)),
                ],
                [
                    'payment_link_id' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                    'transaction_id' => null,
                    'merchant_id' => 1,
                    'amount' => 10.00,
                    'currency' => 'PLN',
                    'expires_at' => (new \DateTimeImmutable())->add(new \DateInterval('PT1H'))
                ]
            ],
            'PAYNOW' => [
                PaynowService::class,
                [
                    'paymentLinkId' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                    'paymentMethod' => 'PAYNOW',
                    'fullname' => 'Jan Kowalski',
                    'email' => 'test@email.com'
                ],
                [
                    new Response(201, [], json_encode([
                        'paymentId' => '12345',
                        'redirectUrl' => 'https://example.com/link',
                    ], JSON_THROW_ON_ERROR)),
                ],
                [
                    'payment_link_id' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                    'transaction_id' => null,
                    'merchant_id' => 1,
                    'amount' => 10.00,
                    'currency' => 'PLN',
                    'expires_at' => (new \DateTimeImmutable())->add(new \DateInterval('PT1H'))
                ]
            ],
            'NODA' => [
                NodaService::class,
                [
                    'paymentLinkId' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                    'paymentMethod' => 'NODA',
                    'fullname' => 'Jan Kowalski',
                    'email' => 'test@email.com'
                ],
                [
                    new Response(200, [], json_encode([
                        'id' => 'test-12345',
                        'url' => 'https://example.com/link'
                    ], JSON_THROW_ON_ERROR)),
                ],
                [
                    'payment_link_id' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                    'transaction_id' => null,
                    'merchant_id' => 1,
                    'amount' => 10.00,
                    'currency' => 'USD',
                    'expires_at' => (new \DateTimeImmutable())->add(new \DateInterval('PT1H'))
                ]
            ]
        ];
    }
}