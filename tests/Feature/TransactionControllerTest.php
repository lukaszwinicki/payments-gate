<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Jobs\ProcessWebhookJob;
use App\Models\Transaction;
use App\Services\TPayService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    public function test_create_transaction_with_invalid_data(): void
    {
        $transactionBody = [
            'email' => 'jankowalski@gmail.com',
            'payment_method' => 'TPAY',
            'notification_url' => 'https://notification.url'
        ];

        $response = $this->postJson('/api/create-transaction', $transactionBody);
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'error' => [
                'amount',
                'name'
            ]
        ]);
    }

    public function test_create_transaction_failed(): void
    {
        $transactionBody = [
            'amount' => 100,
            'email' => 'jankowalski@gmail.com',
            'name' => 'Jan Kowalski',
            'payment_method' => 'TPAY',
            'notification_url' => 'https://notification.url'
        ];


        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => 'mock-token'])),
            new Response(500, [], ''),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $tpayService = new TPayService($client);
        $this->app->bind(TPayService::class, fn() => $tpayService);

        $response = $this->postJson('/api/create-transaction', $transactionBody);
        $response->assertStatus(500);
        $response->assertJson([
            'error' => 'The transaction could not be completed'
        ]);
    }

    public function test_create_transaction_is_created_and_confirmed(): void
    {
        $transactionBody = [
            'amount' => 100,
            'email' => 'jankowalski@gmail.com',
            'name' => 'Jan Kowalski',
            'payment_method' => 'TPAY',
            'notification_url' => 'https://notification.url'
        ];

        $mockedResponse = [
            'transactionId' => '12345',
            'hiddenDescription' => '8fe22800-d5ed-40e3-8dda-5289bc29e314',
            'payer' => [
                'name' => 'Jan Kowalski',
                'email' => 'jankowalski@gmail.com',
            ],
            'amount' => 100,
            'currency' => 'PLN',
            'transactionPaymentUrl' => 'https://example.com/link',
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => 'mock-token'])),
            new Response(200, [], json_encode($mockedResponse)),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $tpayService = new TPayService($client);
        $this->app->bind(TPayService::class, fn() => $tpayService);

        $response = $this->postJson('/api/create-transaction', $transactionBody);
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertIsString($content);
        $result = json_decode($content, true);
        $this->assertEquals('https://example.com/link', $result['link']);
        $transaction = Transaction::where('transaction_uuid', $result['transaction_uuid'])->first();
        $this->assertNotNull($transaction);

        $headers = [
            'x-jws-signature' => ['eyJhbGciOiJSUzI1NiIsIng1dSI6Imh0dHBzOlwvXC9zZWN1cmUuc2FuZGJveC50cGF5LmNvbVwveDUwOVwvbm90aWZpY2F0aW9ucy1qd3MucGVtIn0..HalI3jDvtvVGCn5wSiUUTH4ZSjAbbSEo2nbcI_PfqJIVgnH_vW6RMLEfOxfDQLkpZZoGNwgYg9fqw3h3zBt11qPDXn_m79iNnBe0-stLk4TPBDUEATEULIkpjLolFm727kNtZs2cxyfZm03SwtVOC7WrOQTEXqCZXtrGhONy-Sz_j4duG-haiAOvKLAbCJC4eRQvjxfX0LaUWhqscmJitZrJjb_l7THT-cA5Pq7FA4zbJMgbAHzRE6we41fFeQXal4Je3s5_KwbDzdXwpaYCo2MhOZTBEaUsMdvZHwKfKPYW7WcLhhqG_-tNaa8ZNTHrh8_B0wvKet4ReBPhAAfjwFjRQ2t4hX8Ukx0OYuTBz2LRh9Z-Gy7YQWR7-da67kwdTlKIfhtvUfwtu62PgV5LkVX9bll_27mKwZfwJvESqJoO4AUV-_Xn7g4sLNG_CkPc7QDk4TIXnKZuj19uvwEW4UVZlXmL4R2FaGJqmEPspBljPhte9Ez-avc1QmfV0WS4AF94GRMBy5XO-1ubiUZq9x6xlUIlwZ3Du7lz71uNpLHoZu2aBTm8ZnsB9zrVkfN1ZVuxEQ7Kx723bJdTH9KerT0fjTa7j9fKMBmwL64XqZmLMRAJmeo_iIfXVgYtdDunHOSYzat16QXbqDLykCxIHUcQ0UnJAai8LlAbGq66q2g'],
        ];

        $webhookBody = [
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
        ];

        Queue::fake();

        $response = $this->withHeaders($headers)
            ->post('/api/confirm-transaction?payment_method=' . $transactionBody['payment_method'], $webhookBody);
        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'transaction_uuid' => '8fe22800-d5ed-40e3-8dda-5289bc29e314',
            'status' => TransactionStatus::SUCCESS
        ]);

        Queue::assertPushed(ProcessWebhookJob::class, function ($job) {
            return $job->transaction->transaction_uuid === '8fe22800-d5ed-40e3-8dda-5289bc29e314';
        });
    }

    public function test_confirm_transaction_with_webhook_body_or_signature_invalid(): void
    {
        $headers = [
            'x-jws-signature' => ['Uuc2FuZGJveC50cGF5LmNvbVwveDUwOVwvbm90aWZpY2F0aW9ucy1qd3MucGVtIn0..HalI3jDvtvVGCn5wSiUUTH4ZSjAbbSEo2nbcI_PfqJIVgnH_vW6RMLEfOxfDQLkpZZoGNwgYg9fqw3h3zBt11qPDXn_m79iNnBe0-stLk4TPBDUEATEULIkpjLolFm727kNtZs2cxyfZm03SwtVOC7WrOQTEXqCZXtrGhONy-Sz_j4duG-haiAOvKLAbCJC4eRQvjxfX0LaUWhqscmJitZrJjb_l7THT-cA5Pq7FA4zbJMgbAHzRE6we41fFeQXal4Je3s5_KwbDzdXwpaYCo2MhOZTBEaUsMdvZHwKfKPYW7WcLhhqG_-tNaa8ZNTHrh8_B0wvKet4ReBPhAAfjwFjRQ2t4hX8Ukx0OYuTBz2LRh9Z-Gy7YQWR7-da67kwdTlKIfhtvUfwtu62PgV5LkVX9bll_27mKwZfwJvESqJoO4AUV-_Xn7g4sLNG_CkPc7QDk4TIXnKZuj19uvwEW4UVZlXmL4R2FaGJqmEPspBljPhte9Ez-avc1QmfV0WS4AF94GRMBy5XO-1ubiUZq9x6xlUIlwZ3Du7lz71uNpLHoZu2aBTm8ZnsB9zrVkfN1ZVuxEQ7Kx723bJdTH9KerT0fjTa7j9fKMBmwL64XqZmLMRAJmeo_iIfXVgYtdDunHOSYzat16QXbqDLykCxIHUcQ0UnJAai8LlAbGq66q2g'],
        ];

        $webhookBody = [
            'data' => 'invalid'
        ];

        $payment_method = 'TPAY';

        $response = $this->withHeaders($headers)
            ->post('/api/confirm-transaction?payment_method=' . $payment_method, $webhookBody);
        $response->assertStatus(500);
        $response->assertJson([
            'error' => 'Invalid webhook payload or signature.'
        ]);
    }

    public function test_confirm_transaction_with_status_chargeback(): void
    {
        $headers = [
            'x-jws-signature' => ['eyJhbGciOiJSUzI1NiIsIng1dSI6Imh0dHBzOlwvXC9zZWN1cmUuc2FuZGJveC50cGF5LmNvbVwveDUwOVwvbm90aWZpY2F0aW9ucy1qd3MucGVtIn0..m12kqrTYyviJtYtVQeoDHtbt_gwHO5KB-MqLSKWmxxYR7VnWgx_Kbvbhet69gREtBeKtf_YRCKdpNeJBxiTgoBisvo5lzSTqiaYimE_dXCc6Hah-j8xGKKFucqV7gauC-NK0KWsT9uE1q5nTSXu4XCxUd1X2jdU9He9Ua8MD37Y7-4GZfu4q9L3SctVPZCgzGMLsW1kvErrF_j3WruiANC95Ynyi7DvgNaLSx-7bdKuFPRuFle4V8ehauCr3Znwp4i79hjx-E7uLHZRMIEEbA5PaBWvk8N1fqT-w0Mwh_k4Ywzg86vInLmthONaVl5NsUPVUDmizvLTbVGTta6LxziXZwSY-7lTNbqHrtX70hptpml52kVuwq8ipArSbc07J-X5Fe2ADSIdx3gNCf1kJVK8Eu49cTnTfaAGcA1Jaz4Tdbe0ou1TI57MZwl-K_oUjhkFiGT5ZpPvEs8M-012lLI7yNAXs3HjPf4hTQDCCKytvzRPQFGXmFEZCJ_jiQNpmg5TBFA3r8FqMGfHf1R9HimL8WMWUL36LOCB-BguJMN80w50rHmjgroFm0_YGbxIjOCl3gHBhZF5lV-JrpyfyZhempoLXQmlVoxHjdkkJRSc6m1jDhca88vQJ7OQbUlyyEXmzhhKTMLUp7MFephvzDunSKPvJQ2h_UkhmmBoIYac'],
        ];

        $webhookBody = [
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
        ];

        $payment_method = 'TPAY';

        $response = $this->withHeaders($headers)
            ->post('/api/confirm-transaction?payment_method=' . $payment_method, $webhookBody);
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertIsString($content);
        $result = json_decode($content, true);
        $this->assertEquals('a875e396-3c67-4415-a865-1fcea225154e', $result['transaction_uuid']);
    }

    public function test_refund_payment_missing_payment_method_or_transactionUuid(): void
    {
        $refundBody = [
            'payment_method' => '',
            'transactionUuid' => 'a875e396-3c67-4415-a865-1fcea225154e'
        ];

        $response = $this->post('/api/refund-payment', $refundBody);
        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'Missing or invalid data.'
        ]);
    }

    public function test_refund_payment_transaction_is_refunded(): void
    {
        $refundBody = [
            'payment_method' => 'TPAY',
            'transactionUuid' => '470ea80c-3929-4ee7-964e-c9eb4ac422df'
        ];

        $transaction = Transaction::factory()->create([
            'transaction_uuid' => '470ea80c-3929-4ee7-964e-c9eb4ac422df',
            'status' => TransactionStatus::REFUND
        ]);

        $response = $this->post('/api/refund-payment', $refundBody);
        $response->assertStatus(500);
        $response->assertJson([
            'error' => 'Refund payment not completed.'
        ]);

        $this->assertDatabaseHas('transactions', [
            'transaction_uuid' => $transaction->transaction_uuid
        ]);
        $this->assertEquals(TransactionStatus::REFUND, $transaction->status);
    }

    public function test_refund_payment_invalid_transaction_uuid(): void
    {
        $refundBody = [
            'payment_method' => 'TPAY',
            'transactionUuid' => 'invalid-uuid'
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => 'mock-token'])),
            new Response(400, [], ''),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $tpayService = new TPayService($client);
        $this->app->bind(TPayService::class, fn() => $tpayService);

        $response = $this->post('/api/refund-payment', $refundBody);
        $response->assertStatus(500);
        $response->assertJson([
            'error' => 'Refund payment not completed.'
        ]);
    }

    public function test_refund_payment_is_success(): void
    {
        $refundBody = [
            'payment_method' => 'TPAY',
            'transactionUuid' => '470ea80c-3929-4ee7-964e-c9eb4ac422df'
        ];

        Transaction::factory()->create([
            'transaction_uuid' => '470ea80c-3929-4ee7-964e-c9eb4ac422df',
        ]);

        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => 'mock-token'])),
            new Response(200, [], json_encode(['result' => 'success', 'status' => 'refund'])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $tpayService = new TPayService($client);
        $this->app->bind(TPayService::class, fn() => $tpayService);

        Queue::fake();

        $response = $this->post('/api/refund-payment', $refundBody);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => 'Refund'
        ]);

        $content = $response->getContent();
        $this->assertIsString($content);
        $result = json_decode($content, true);
        $this->assertEquals('470ea80c-3929-4ee7-964e-c9eb4ac422df', $result['transaction_uuid']);
        $transaction = Transaction::where('transaction_uuid', $result['transaction_uuid'])->first();
        $this->assertNotNull($transaction);
        $this->assertDatabaseHas('transactions', [
            'transaction_uuid' => $transaction->transaction_uuid
        ]);
        $this->assertEquals(TransactionStatus::REFUND, $transaction->status);
    }
}