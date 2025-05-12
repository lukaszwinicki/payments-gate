<?php

namespace Tests\Unit;

use App\Enums\TransactionStatus;
use App\Facades\TPaySignatureValidatorFacade;
use App\Dtos\CreateTransactionDto;
use App\Dtos\ConfirmTransactionDto;
use App\Dtos\RefundPaymentDto;
use App\Models\Transaction;
use App\Services\TPayService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Mockery;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TPayServiceTest extends TestCase
{
    use RefreshDatabase;
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_transaction_success(): void
    {
        Str::createUuidsUsing(fn () => Uuid::fromString('123e4567-e89b-12d3-a456-426614174000'));

        $transactionBody = [
            'amount' => 100,
            'email' => 'jankowalski@example.com',
            'name' => 'Jan Kowalski',
            'paymentMethod' => 'TPAY',
        ];

        $mockedResponse = [
            'transactionId' => '12345',
            'hiddenDescription' => 'uuid-12345',
            'payer' => [
                'name' => 'Jan Kowalski',
                'email' => 'jankowalski@example.com',
            ],
            'amount' => 100,
            'currency' => 'PLN',
            'transactionPaymentUrl' => 'https://example.com/link',
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode($mockedResponse)),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        Cache::shouldReceive('get')
            ->with('token')
            ->andReturn('mocked_access_token');

        Cache::shouldReceive('has')
            ->with('token')
            ->andReturn(true);

        $paymentService = new TPayService($client);

        $createTransactionDto = $paymentService->create($transactionBody);
        $this->assertInstanceOf(CreateTransactionDto::class, $createTransactionDto);
        $this->assertEquals('12345', $createTransactionDto->transactionId);
        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $createTransactionDto->uuid);
        $this->assertEquals('Jan Kowalski', $createTransactionDto->name);
        $this->assertEquals('jankowalski@example.com', $createTransactionDto->email);
        $this->assertEquals(100, $createTransactionDto->amount);
        $this->assertEquals('PLN', $createTransactionDto->currency);
        $this->assertEquals('https://example.com/link', $createTransactionDto->link);
    }

    public function test_create_transaction_failed(): void
    {
        $transactionBody = [
            'amount' => 100,
            'email' => 'jankowalski@example.com',
            'name' => 'Jan Kowalski',
            'paymentMethod' => 'TPAY',
        ];

        $mock = new MockHandler([
            new Response(500, [], ''),
        ]);

        Cache::shouldReceive('get')
            ->with('token')
            ->andReturn('mocked_access_token');

        Cache::shouldReceive('has')
            ->with('token')
            ->andReturn(true);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        $paymentService = new TPayService($client);
        $createTransactionDto = $paymentService->create($transactionBody);
        $this->assertNull($createTransactionDto);
    }

    public function test_confirm_invalid_jws_signature_header(): void
    {
        $webhookBody = [
            'tr_status' => 'TRUE',
            'tr_crc' => '123456789'
        ];

        $headers = [
            'x-jws-signature' => ['invalid-signature']
        ];

        TPaySignatureValidatorFacade::shouldReceive('confirm')
            ->once()
            ->withAnyArgs()
            ->andReturn(false);

        $paymentService = new TPayService();
        $result = $paymentService->confirm($webhookBody, $headers);
        $this->assertNull($result);
    }

    public function test_confirm_invalid_webhookBody(): void
    {
        $webhookBody = [
            'tr_status' => 'TRUE',
            'tr_crc' => '123456789'
        ];
        $headers = [
            'x-jws-signature' => ['valid-signature']
        ];
        TPaySignatureValidatorFacade::shouldReceive('confirm')
            ->once()
            ->withAnyArgs()
            ->andReturn(false);
        $paymentService = new TPayService();
        $result = $paymentService->confirm($webhookBody, $headers);
        $this->assertNull($result);
    }

    public function test_confirm_tr_status_is_true(): void
    {
        $webhookBody = [
            'tr_status' => 'TRUE',
            'tr_crc' => '123456789'
        ];
        $headers = [
            'x-jws-signature' => ['valid-signature']
        ];
        TPaySignatureValidatorFacade::shouldReceive('confirm')
            ->once()
            ->withAnyArgs()
            ->andReturn(true);
        $paymentService = new TPayService();
        $result = $paymentService->confirm($webhookBody, $headers);
        $this->assertInstanceOf(ConfirmTransactionDto::class, $result);
        $this->assertEquals(TransactionStatus::SUCCESS, $result->status);
        $this->assertEquals('TRUE', $result->responseBody);
        $this->assertEquals('123456789', $result->remoteCode);
    }

    public function test_confirm_tr_status_is_chargeback(): void
    {
        $webhookBody = [
            'tr_status' => 'CHARGEBACK',
            'tr_crc' => '123456789'
        ];
        $headers = [
            'x-jws-signature' => ['valid-signature']
        ];
        TPaySignatureValidatorFacade::shouldReceive('confirm')
            ->once()
            ->withAnyArgs()
            ->andReturn(true);
        $paymentService = new TPayService();
        $result = $paymentService->confirm($webhookBody, $headers);
        $this->assertInstanceOf(ConfirmTransactionDto::class, $result);
        $this->assertEquals(TransactionStatus::REFUND_SUCCESS, $result->status);
        $this->assertEquals('TRUE', $result->responseBody);
        $this->assertEquals('123456789', $result->remoteCode);
    }

    public function test_refund_success(): void
    {
        $mockTransaction = Mockery::mock('alias:App\Models\Transaction');
        $mockTransaction->shouldReceive('where')
            ->with('transaction_uuid', 'valid-uuid')
            ->andReturnSelf();

        $mockTransaction->shouldReceive('first')
            ->andReturn((object) [
                'transactions_id' => '12345',
                'transaction_uuid' => 'valid-uuid',
                'status' => TransactionStatus::SUCCESS
            ]);

        $mockedResponseBody = [
            'result' => 'success',
            'status' => 'refund'
        ];

        Cache::shouldReceive('get')
            ->with('token')
            ->andReturn('mocked_access_token');

        Cache::shouldReceive('has')
            ->with('token')
            ->andReturn(true);

        $mockRequest = new MockHandler([
            new Response(200, [], json_encode($mockedResponseBody)),
        ]);

        $handlerStack = HandlerStack::create($mockRequest);
        $client = new Client(['handler' => $handlerStack]);
        $paymentService = new TPayService($client);
        $refundBody = [
            'transactionUuid' => 'valid-uuid'
        ];
        $result = $paymentService->refund($refundBody);

        $transaction = Transaction::where('transaction_uuid', $refundBody['transactionUuid'])->first();
        $this->assertNotNull($transaction);
        $this->assertEquals('12345', $transaction->transactions_id);
        $this->assertEquals('valid-uuid', $transaction->transaction_uuid);
        $this->assertInstanceOf(RefundPaymentDto::class, $result);
        $this->assertEquals(TransactionStatus::REFUND_PENDING, $result->status);
    }

        public function test_refund_failed(): void
    {
        $mockTransaction = Mockery::mock('alias:App\Models\Transaction');
        $mockTransaction->shouldReceive('where')
            ->with('transaction_uuid', 'valid-uuid')
            ->andReturnSelf();

        $mockTransaction->shouldReceive('first')
            ->andReturn((object) [
                'transactions_id' => '12345',
                'transaction_uuid' => 'valid-uuid',
                'status' => TransactionStatus::SUCCESS
            ]);

        Cache::shouldReceive('get')
            ->with('token')
            ->andReturn('mocked_access_token');

        Cache::shouldReceive('has')
            ->with('token')
            ->andReturn(true);

        $mockRequest = new MockHandler([
            new Response(500, [], ''),
        ]);

        $handlerStack = HandlerStack::create($mockRequest);
        $client = new Client(['handler' => $handlerStack]);
        $paymentService = new TPayService($client);
        $refundBody = [
            'transactionUuid' => 'valid-uuid'
        ];
        $result = $paymentService->refund($refundBody);
        $this->assertNull($result);
    }

    public function test_refund_transaction_status_is_refund(): void
    {
        $mockTransaction = Mockery::mock('alias:App\Models\Transaction');
        $mockTransaction->shouldReceive('where')
            ->with('transaction_uuid', 'valid-uuid')
            ->andReturnSelf();

        $mockTransaction->shouldReceive('first')
            ->andReturn((object) [
                'transactions_id' => '12345',
                'transaction_uuid' => 'valid-uuid',
                'status' => TransactionStatus::REFUND_SUCCESS
            ]);


        $paymentService = new TPayService();
        $refundBody = [
            'transactionUuid' => 'valid-uuid'
        ];
        $result = $paymentService->refund($refundBody);
        $this->assertNull($result);
        $transaction = Transaction::where('transaction_uuid', $refundBody['transactionUuid'])->first();
        $this->assertNotNull($transaction);
        $this->assertEquals(TransactionStatus::REFUND_SUCCESS, $transaction->status);
    }

    public function test_get_token_success(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => 'token'])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        try {
            $response = $client->request('POST', 'https://example.com/oauth/auth', [
                'json' => [
                    'client_id' => 'valid-id',
                    'client_secret' => 'valid-secret'
                ]
            ]);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Failed to get token');
        }

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals('token', $responseData['access_token']);
    }

    public function test_get_token_invalid_client(): void
    {
        $mock = new MockHandler([
            new Response(401, [], json_encode(['error' => 'invalid_client']))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        try {
            $response = $client->request('POST', 'https://example.com/oauth/auth', [
                'json' => [
                    'client_id' => 'valid-id',
                    'client_secret' => 'valid-secret'
                ],
                'http_errors' => false
            ]);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Invalid client token');
        }

        $this->assertEquals(401, $response->getStatusCode());
        $responseData = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals('invalid_client', $responseData['error']);
    }
}