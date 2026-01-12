<?php

namespace Tests\Feature;

use App\Facades\TransactionSignatureFacade;
use App\Enums\TransactionStatus;
use App\Jobs\ProcessWebhookJob;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Services\PaynowService;
use App\Services\TPayService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RefundBffControllerTest extends TestCase
{
    use RefreshDatabase;
    protected Merchant $merchant;
    protected function setUp(): void
    {
        parent::setUp();

        $this->merchant = Merchant::factory()->create();
        $this->merchant->createToken('test-token');
    }

    public function test_refund_fails_with_invalid_uuid(): void
    {
        $response = $this->withHeaders([
            'x-api-key' => $this->merchant->api_key,
        ])->postJson('/api/refund-payment', [
                    'transactionUuid' => 'invalid-uuid',
                ]);

        $response->assertStatus(422)
            ->assertJson(['error' => 'Invalid or missing transaction UUID']);
    }

    public function test_refund_fails_with_invalid_signature(): void
    {
        $transaction = Transaction::factory()->create([
            'merchant_id' => $this->merchant->id,
            'status' => TransactionStatus::SUCCESS,
        ]);

        $response = $this->withHeaders([
            'x-api-key' => $this->merchant->api_key,
            'signature' => 'invalid-signature',
        ])->postJson('/api/refund-payment', [
                    'transactionUuid' => $transaction->transaction_uuid,
                ]);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Missing or invalid signature.']);
    }

    #[DataProvider('refundTransactionIsSuccessProvider')]
    public function test_refund_payment_success(string $uuid, array $transactionData, array $mockResponses, string $serviceClass, array $payload): void
    {
        Queue::fake();
        $merchant = $this->merchant;
        $transaction = Transaction::factory()->create(array_merge($transactionData, ['merchant_id' => $merchant->id]));

        $mock = new MockHandler($mockResponses);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $service = new $serviceClass($client);
        $this->app->instance($serviceClass, $service);

        $response = $this->withHeaders([
            'x-api-key' => $merchant->api_key,
            'signature' => TransactionSignatureFacade::calculateSignature($transaction->transaction_uuid, $merchant)
        ])->postJson('/api/refund-payment', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => 'Refund',
                'transactionUuid' => $transaction->transaction_uuid,
            ]);

        $this->assertDatabaseHas('transactions', [
            'transaction_uuid' => $transaction->transaction_uuid,
            'status' => TransactionStatus::REFUND_PENDING,
        ]);

        Queue::assertPushed(ProcessWebhookJob::class, fn($job) => $job->transaction->id === $transaction->id);
    }

    public function test_refund_fails_for_unauthorized_user(): void
    {
        $otherMerchant = Merchant::factory()->create();
        $transaction = Transaction::factory()->create([
            'merchant_id' => $otherMerchant->id,
            'status' => TransactionStatus::SUCCESS,
        ]);

        $response = $this->withHeaders([
            'x-api-key' => $this->merchant->api_key,
            'signature' => TransactionSignatureFacade::calculateSignature($transaction->transaction_uuid, $this->merchant),
        ])->postJson('/api/refund-payment', [
                    'transactionUuid' => $transaction->transaction_uuid,
                ]);

        $response->assertStatus(403)
            ->assertJson(['error' => 'Unauthorized to refund this transaction.']);
    }

    public static function refundTransactionIsSuccessProvider(): array
    {
        return [
            'TPAY' => [
                'uuid' => '470ea80c-3929-4ee7-964e-c9eb4ac422df',
                'transactionData' => [
                    'transaction_uuid' => '470ea80c-3929-4ee7-964e-c9eb4ac422df',
                    'payment_method' => 'TPAY',
                    'merchant_id' => 1
                ],
                'mockResponses' => [
                    new Response(200, [], json_encode(['access_token' => 'mock-token'], JSON_THROW_ON_ERROR)),
                    new Response(200, [], json_encode(['result' => 'success', 'status' => 'refund'], JSON_THROW_ON_ERROR)),
                ],
                'serviceClass' => TPayService::class,
                'payload' => [
                    'transactionUuid' => '470ea80c-3929-4ee7-964e-c9eb4ac422df',
                ]
            ],
            'PAYNOW' => [
                'uuid' => '470ea80c-3929-4ee7-964e-c9eb4ac422df',
                'transactionData' => [
                    'transaction_uuid' => '470ea80c-3929-4ee7-964e-c9eb4ac422df',
                    'payment_method' => 'PAYNOW',
                    'merchant_id' => 1
                ],
                'mockResponses' => [
                    new Response(201, [], json_encode(['refundId' => '12345', 'status' => 'PENDING'], JSON_THROW_ON_ERROR)),
                ],
                'serviceClass' => PaynowService::class,
                'payload' => [
                    'transactionUuid' => '470ea80c-3929-4ee7-964e-c9eb4ac422df',
                ]
            ],
        ];
    }
}
