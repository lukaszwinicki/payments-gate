<?php

namespace Tests\Unit;

use App\Dtos\ConfirmTransactionDto;
use App\Dtos\CreateTransactionDto;
use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Services\PaynowService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use stdClass;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class PaynowServiceTest extends TestCase
{
    use RefreshDatabase;
    public function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
    public function test_create_transaction_success(): void
    {
        Str::createUuidsUsing(fn() => Uuid::fromString('123e4567-e89b-12d3-a456-426614174000'));

        $transactionBody = [
            'amount' => 100,
            'email' => 'jankowalski@example.com',
            'currency' => 'PLN',
            'name' => 'Jan Kowalski',
            'paymentMethod' => 'PAYNOW',
        ];

        $mockedResponse = [
            'paymentId' => 'test-12345',
            'redirectUrl' => 'https://test-payment-url'
        ];

        $mock = new MockHandler([
            new Response(201, [], json_encode($mockedResponse))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $paynowService = new PaynowService($client);
        $createTransactionDto = $paynowService->create($transactionBody);
        $this->assertInstanceOf(CreateTransactionDto::class, $createTransactionDto);
        $this->assertEquals('test-12345', $createTransactionDto->transactionId);
        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $createTransactionDto->uuid);
        $this->assertEquals('Jan Kowalski', $createTransactionDto->name);
        $this->assertEquals('jankowalski@example.com', $createTransactionDto->email);
        $this->assertEquals('PLN', $createTransactionDto->currency);
        $this->assertEquals(100, $createTransactionDto->amount);
        $this->assertEquals('https://test-payment-url', $createTransactionDto->link);
    }

    public function test_create_transaction_failed(): void
    {
        Str::createUuidsUsing(fn() => Uuid::fromString('123e4567-e89b-12d3-a456-426614174000'));

        $transactionBody = [
            'amount' => 100,
            'email' => 'jankowalski@example.com',
            'currency' => 'PLN',
            'name' => 'Jan Kowalski',
            'paymentMethod' => 'PAYNOW',
        ];

        $mockedResponse = [
            'paymentId' => 'test-12345',
            'redirectUrl' => 'https://test-payment-url'
        ];

        $mock = new MockHandler([
            new Response(400, [], json_encode($mockedResponse))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $paynowService = new PaynowService($client);
        $createTransactionDto = $paynowService->create($transactionBody);
        $this->assertNull($createTransactionDto);
    }

    public function test_confirm_transaction_invalid_signature(): void
    {
        $webhookBody = [
            'externalId' => '12345',
            'status' => 'PENDING'
        ];

        $headers = [
            'signature' => ['invalid-signature']
        ];

        $paynowService = new PaynowService();
        $confirmTransactionDto = $paynowService->confirm($webhookBody, $headers);
        $this->assertNull($confirmTransactionDto);
    }

    #[DataProvider('statusWebhookProvider')]
    public function test_confirm_transaction_maps_status_based_on_webhook_body(array $webhookBody, array $headers, TransactionStatus $status): void
    {
        $paynowService = new PaynowService();
        $confirmTransactionDto = $paynowService->confirm($webhookBody, $headers);

        $this->assertNotNull($confirmTransactionDto);
        $this->assertInstanceOf(ConfirmTransactionDto::class, $confirmTransactionDto);
        $this->assertEquals($status, $confirmTransactionDto->status);
        $this->assertEquals('', $confirmTransactionDto->responseBody);
        $this->assertEquals('12345', $confirmTransactionDto->remoteCode);
    }

    public function test_refund_transaction_is_exist_and_status_is_success_or_refund__fail(): void
    {
        $refundBody = [
            'transactionUuid' => 'valid-uuid'
        ];

        $transaction = Transaction::factory()->create([
            'transaction_uuid' => 'valid-uuid',
            'transactions_id' => 12345,
            'amount' => 10,
            'status' => TransactionStatus::SUCCESS,
        ]);

        $paynowService = new PaynowService();
        $confirmTransactionDto = $paynowService->refund($refundBody);
        $transaction = Transaction::where('transaction_uuid', $refundBody['transactionUuid'])->first();
        $this->assertNotNull($transaction);
        $this->assertNull($confirmTransactionDto);
    }

    public function test_calculated_signature(): void
    {
        Config::set('app.paynow.apiKey', 'test-api-key');
        Config::set('app.paynow.signatureKey', 'test-signature-key');

        $body = ['amount' => 1000, 'currency' => 'PLN'];
        $uuid = '123e4567-e89b-12d3-a456-426614174000';

        $apiKey = 'test-api-key';
        $signatureKey = 'test-signature-key';

        $signatureBody = [
            'headers' => [
                'Api-Key' => $apiKey,
                'Idempotency-Key' => $uuid
            ],
            'parameters' => new stdClass,
            'body' => json_encode($body)
        ];

        $expectedHash = base64_encode(hash_hmac(
            'sha256',
            json_encode($signatureBody),
            $signatureKey,
            true
        ));

        $paynowService = new PaynowService();
        $signature = $paynowService->calculatedSignature($body, $uuid);
        $this->assertEquals($expectedHash, $signature);
    }

    public static function statusWebhookProvider(): array
    {
        return [
            'status CONFIRMED' => [
                ['externalId' => '12345', 'status' => 'CONFIRMED'],
                ['signature' => ['z+qfdYIsV8k9YG3eBH98CCEVjcSRDYosJcsaiw5u8tY=']],
                TransactionStatus::SUCCESS,
            ],
            'status NEW' => [
                ['externalId' => '12345', 'status' => 'NEW'],
                ['signature' => ['3u61AYo7ggb4zzoG/usMix/59bCFXMldQi5LCsgkDQg=']],
                TransactionStatus::PENDING,
            ],
            'status PENDING' => [
                ['externalId' => '12345', 'status' => 'PENDING'],
                ['signature' => ['P5tc+GCllkLpo0Za8V8Hfa/rC9+InJLbUMY1hvji0Xs=']],
                TransactionStatus::PENDING,
            ],
            'status ERROR' => [
                ['externalId' => '12345', 'status' => 'ERROR'],
                ['signature' => ['XCkZbxK6CQ9GB47buV4dS4XhwDXz1p579bkHT3ZYKVk=']],
                TransactionStatus::FAIL,
            ],
            'status REJECTED' => [
                ['externalId' => '12345', 'status' => 'REJECTED'],
                ['signature' => ['1LJgMRiNdB5UoCpJbcTa42UxZsCZrBy7oQGeBMKP/3s=']],
                TransactionStatus::FAIL,
            ],
            'status EXPIRED' => [
                ['externalId' => '12345', 'status' => 'EXPIRED'],
                ['signature' => ['bRWfyZ3xMx1gabpE3UeRVrdjdNVqUKmsStizHl8zwdQ=']],
                TransactionStatus::FAIL,
            ],
        ];
    }
}