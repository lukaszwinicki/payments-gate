<?php

namespace Tests\Unit;

use App\Dtos\ConfirmTransactionDto;
use App\Dtos\CreateTransactionDto;
use App\Enums\TransactionStatus;
use App\Services\NodaService;
use App\Exceptions\UnsupportedCurrencyException;
use Tests\TestCase;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class NodaServiceTest extends TestCase
{
    public function test_throws_exception_when_currency_not_supported_by_payment_method(): void
    {
        $transactionBody = [
            'amount' => 100,
            'email' => 'test@example.com',
            'currency' => 'PLN',
            'name' => 'Test User',
            'paymentMethod' => 'NODA',
        ];

        $mock = new MockHandler([
            new Response(500, [], ''),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        $paynowService = new NodaService($client);

        $this->expectException(UnsupportedCurrencyException::class);
        $this->expectExceptionMessage('Currency PLN is not supported by NODA.');
        $paynowService->create($transactionBody);
    }
    public function test_create_transaction_success(): void
    {
        Str::createUuidsUsing(fn() => Uuid::fromString('123e4567-e89b-12d3-a456-426614174000'));

        $transactionBody = [
            'amount' => 100,
            'email' => 'jankowalski@example.com',
            'currency' => 'USD',
            'name' => 'Jan Kowalski',
            'paymentMethod' => 'NODA',
            'notificationUrl' => 'https://test.com',
            'returnUrl' => 'https://test.com'
        ];

        $mockedResponse = [
            'id' => 'test-12345',
            'url' => 'https://test-payment-url'
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode($mockedResponse))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $nodaService = new NodaService($client);
        $createTransactionDto = $nodaService->create($transactionBody);
        $this->assertInstanceOf(CreateTransactionDto::class, $createTransactionDto);
        $this->assertEquals('test-12345', $createTransactionDto->transactionId);
        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $createTransactionDto->uuid);
        $this->assertEquals('Jan Kowalski', $createTransactionDto->name);
        $this->assertEquals('jankowalski@example.com', $createTransactionDto->email);
        $this->assertEquals('USD', $createTransactionDto->currency);
        $this->assertEquals(100, $createTransactionDto->amount);
        $this->assertEquals('https://test-payment-url', $createTransactionDto->link);
    }

    public function test_create_transaction_failed(): void
    {
        Str::createUuidsUsing(fn() => Uuid::fromString('123e4567-e89b-12d3-a456-426614174000'));

        $transactionBody = [
            'amount' => 100,
            'email' => 'jankowalski@example.com',
            'currency' => 'USD',
            'name' => 'Jan Kowalski',
            'paymentMethod' => 'NODA',
        ];

        $mockedResponse = [
            'id' => 'test-12345',
            'url' => 'https://test-payment-url'
        ];

        $mock = new MockHandler([
            new Response(400, [], json_encode($mockedResponse))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $nodaService = new NodaService($client);
        $createTransactionDto = $nodaService->create($transactionBody);
        $this->assertNull($createTransactionDto);
    }

    public function test_confirm_transaction_invalid_signature(): void
    {
        $webhookBody = [
            'PaymentId' => '717b62f9-d92c-4faa-99a5-b967a2eb14fc',
            'Status' => 'Done',
            'Signature' => 'invalid-signature'
        ];

        $headers = [];

        $nodaService = new NodaService();
        $confirmTransactionDto = $nodaService->confirm($webhookBody, $headers);
        $this->assertNull($confirmTransactionDto);
    }

    public function test_confirm_transaction_success(): void
    {
        $webhookBody = [
            'PaymentId' => '717b62f9-d92c-4faa-99a5-b967a2eb14fc',
            'Status' => 'Done',
            'Signature' => 'a0be8a3b50aec5bd14684c0a46c8a1278fb134870607d0cd36ada937432161c3',
            'MerchantPaymentId' => 'ffcb1ed7-c066-4521-a7d8-bb08424e56e5'
        ];

        $headers = [];

        $nodaService = new NodaService();
        $confirmTransactionDto = $nodaService->confirm($webhookBody, $headers);
        $this->assertNotNull($confirmTransactionDto);
        $this->assertInstanceOf(ConfirmTransactionDto::class, $confirmTransactionDto);
        $this->assertEquals(TransactionStatus::SUCCESS, $confirmTransactionDto->status);
        $this->assertEquals('', $confirmTransactionDto->responseBody);
        $this->assertEquals('ffcb1ed7-c066-4521-a7d8-bb08424e56e5', $confirmTransactionDto->remoteCode);
    }
}