<?php

namespace Tests\Unit;

use App\Enums\TransactionStatus;
use App\Facades\TPaySignatureValidatorFacade;
use App\Factory\Dtos\ConfirmTransactionDto;
use App\Factory\Dtos\RefundPaymentDto;
use App\Models\Transaction;
use App\Services\TPayService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class TPayServiceTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_transaction_success()
    {
        $transactionBody = [
            'amount' => 100,
            'email' => 'jankowalski@example.com',
            'name' => 'Jan Kowalski',
            'payment_method' => 'TPAY',
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
        $this->assertEquals('12345', $createTransactionDto->transactionId);
        $this->assertEquals('uuid-12345', $createTransactionDto->uuid);
        $this->assertEquals('Jan Kowalski', $createTransactionDto->name);
        $this->assertEquals('jankowalski@example.com', $createTransactionDto->email);
        $this->assertEquals(100, $createTransactionDto->amount);
        $this->assertEquals('PLN', $createTransactionDto->currency);
        $this->assertEquals('https://example.com/link', $createTransactionDto->link);
    }

    public function test_create_transaction_failed()
    {
        $transactionBody = [
            'amount' => 100,
            'email' => 'jankowalski@example.com',
            'name' => 'Jan Kowalski',
            'payment_method' => 'TPAY',
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

    public function test_confirm_invalid_jws_signature_header()
    {
        $webhookBody = 'tr_status=TRUE&tr_crc=123456789';
        $headers = [
            'x-jws-signature' => ['invalid-signature']
        ];
        TPaySignatureValidatorFacade::shouldReceive('confirm')
            ->once()
            ->with($webhookBody, 'invalid-signature')
            ->andReturn(false);
        $paymentService = new TPayService();
        $result = $paymentService->confirm($webhookBody, $headers);
        $this->assertInstanceOf(ConfirmTransactionDto::class, $result);
        $this->assertEquals(TransactionStatus::FAIL, $result->status);
    }

    public function test_confirm_invalid_webhookBody()
    {
        $webhookBody = 'invalid-data';
        $headers = [
            'x-jws-signature' => ['valid-signature']
        ];
        TPaySignatureValidatorFacade::shouldReceive('confirm')
            ->once()
            ->with($webhookBody, 'valid-signature')
            ->andReturn(false);
        $paymentService = new TPayService();
        $result = $paymentService->confirm($webhookBody, $headers);
        $this->assertInstanceOf(ConfirmTransactionDto::class, $result);
        $this->assertEquals(TransactionStatus::FAIL, $result->status);
    }

    public function test_confirm_tr_status_is_true()
    {
        $webhookBody = 'tr_status=TRUE&tr_crc=123456789';
        $headers = [
            'x-jws-signature' => ['valid-signature']
        ];
        TPaySignatureValidatorFacade::shouldReceive('confirm')
            ->once()
            ->with($webhookBody, 'valid-signature')
            ->andReturn(true);
        $paymentService = new TPayService();
        $result = $paymentService->confirm($webhookBody, $headers);
        $this->assertInstanceOf(ConfirmTransactionDto::class, $result);
        $this->assertEquals(TransactionStatus::SUCCESS, $result->status);
        $this->assertEquals('TRUE', $result->responseBody);
        $this->assertEquals('123456789', $result->remoteCode);
        $this->assertEquals(true, $result->completed);
    }

    public function test_confirm_tr_status_is_chargeback()
    {
        $webhookBody = 'tr_status=CHARGEBACK&tr_crc=123456789';
        $headers = [
            'x-jws-signature' => ['valid-signature']
        ];
        TPaySignatureValidatorFacade::shouldReceive('confirm')
            ->once()
            ->with($webhookBody, 'valid-signature')
            ->andReturn(true);
        $paymentService = new TPayService();
        $result = $paymentService->confirm($webhookBody, $headers);
        $this->assertInstanceOf(ConfirmTransactionDto::class, $result);
        $this->assertEquals(TransactionStatus::REFUND, $result->status);
        $this->assertEquals('TRUE', $result->responseBody);
        $this->assertEquals('123456789', $result->remoteCode);
        $this->assertEquals(true, $result->completed);
    }

    public function test_refund_success()
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
        $transactionUuid = 'valid-uuid';
        $result = $paymentService->refund($transactionUuid);

        $transaction = Transaction::where('transaction_uuid', $transactionUuid)->first();
        $this->assertEquals('12345', $transaction->transactions_id);
        $this->assertEquals('valid-uuid', $transaction->transaction_uuid);
        $this->assertInstanceOf(RefundPaymentDto::class, $result);
        $this->assertEquals(TransactionStatus::REFUND, $result->status);
    }

    public function test_refund_failed()
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
        $transactionUuid = 'valid-uuid';
        $result = $paymentService->refund($transactionUuid);
        $this->assertNull($result);
    }

    public function test_refund_transaction_status_is_refund()
    {
        $mockTransaction = Mockery::mock('alias:App\Models\Transaction');
        $mockTransaction->shouldReceive('where')
            ->with('transaction_uuid', 'valid-uuid')
            ->andReturnSelf();

        $mockTransaction->shouldReceive('first')
            ->andReturn((object) [
                'transactions_id' => '12345',
                'transaction_uuid' => 'valid-uuid',
                'status' => TransactionStatus::REFUND
            ]);

        $paymentService = new TPayService();
        $transactionUuid = 'valid-uuid';
        $result = $paymentService->refund($transactionUuid);
        $this->assertNull($result);
    }

    public function test_get_token_success()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => 'token'])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        $response = $client->request('POST', 'https://example.com/oauth/auth', [
            'json' => [
                'client_id' => 'valid-id',
                'client_secret' => 'valid-secret'
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals('token', $responseData['access_token']);
    }

    public function test_get_token_invalid_client()
    {
        $mock = new MockHandler([
            new Response(401, [], json_encode(['error' => 'invalid_client']))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $response = $client->request('POST', 'https://example.com/oauth/auth', [
            'json' => [
                'client_id' => 'valid-id',
                'client_secret' => 'valid-secret'
            ],
            'http_errors' => false
        ]);

        $this->assertEquals(401, $response->getStatusCode());
        $responseData = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals('invalid_client', $responseData['error']);
    }
}