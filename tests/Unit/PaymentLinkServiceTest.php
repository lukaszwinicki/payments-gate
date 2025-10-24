<?php

namespace Tests\Unit;

use App\Dtos\CreatePaymentLinkDto;
use App\Dtos\CreateTransactionDto;
use App\Dtos\PaymentLinkTransactionDto;
use App\Enums\PaymentMethod;
use App\Facades\TransactionServiceFasade;
use App\Factories\PaymentLinkFactory;
use App\Models\Merchant;
use App\Models\PaymentLink;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Mockery;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;


class PaymentLinkServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[DataProvider('payloadAndApikey')]
    public function test_create_payment_link_merchant_not_found(array $payload, string $apiKey): void
    {
        Str::createUuidsUsing(fn() => Uuid::fromString('aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee'));
        Merchant::factory()->create([
            'api_key' => 'test'
        ]);
        Log::shouldReceive('info')->byDefault();
        Log::shouldReceive('error')
            ->once()
            ->with('[SERVICE][CREATE][PAYMENT-LINK][ERROR] Merchant not found', [
                'apiKey' => $apiKey,
            ]);

        $result = app(\App\Services\PaymentLinkService::class)->createPaymentLink($payload, $apiKey);
        $this->assertNull($result);
    }

    #[DataProvider('payloadAndApikey')]
    public function test_create_payment_link_save_to_database_fails(array $payload, string $apiKey): void
    {
        Merchant::factory()->create();
        Str::createUuidsUsing(fn() => Uuid::fromString('aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee'));
        Log::shouldReceive('info')->byDefault();

        $mockLink = Mockery::mock(PaymentLink::class)->makePartial();
        $mockLink->shouldReceive('save')->once()->andReturn(false);

        $factoryMock = Mockery::mock(PaymentLinkFactory::class);
        $factoryMock->shouldReceive('make')->andReturn($mockLink);

        $this->instance(PaymentLinkFactory::class, $factoryMock);

        Log::shouldReceive('error')->once()->with(
            '[SERVICE][CREATE][PAYMENT-LINK][DB][ERROR] Failed to save payment link to the database',
            Mockery::subset(['paymentLinkId' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee'])
        );

        $service = app(\App\Services\PaymentLinkService::class);
        $result = $service->createPaymentLink($payload, $apiKey);

        $this->assertNull($result);
    }

    #[DataProvider('payloadAndApikey')]
    public function test_create_payment_link_successfully(array $payload, string $apiKey): void
    {
        $merchant = Merchant::factory()->create();
        Str::createUuidsUsing(fn() => Uuid::fromString('aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee'));
        Log::shouldReceive('info')->byDefault();

        $mockLink = Mockery::mock(PaymentLink::class)->makePartial();
        $mockLink->shouldReceive('save')->once()->andReturn(true);

        $factoryMock = Mockery::mock(PaymentLinkFactory::class);
        $factoryMock->shouldReceive('make')->andReturn($mockLink);

        $this->instance(PaymentLinkFactory::class, $factoryMock);

        $service = app(\App\Services\PaymentLinkService::class);
        $result = $service->createPaymentLink($payload, $apiKey);

        $this->assertInstanceOf(CreatePaymentLinkDto::class, $result);
        $this->assertEquals('aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee', $result->paymentLinkId);
        $this->assertEquals(10.00, $result->amount);
        $this->assertEquals('PLN', $result->currency);
        $this->assertEquals('https://notification.url', $result->notificationUrl);
        $this->assertEquals('https://return.url', $result->returnUrl);
        $this->assertEquals($merchant->id, $result->merchantId);
    }

    public function test_create_transaction_for_payment_link_invalid_uuid(): void
    {
        $payload = [
            'paymentLinkId' => 'aaaaaaaa-bbbb-cccc-dddd',
        ];

        Log::shouldReceive('error')
            ->once()
            ->with('[SERVICE][CREATE][TRANSACTION-PAYMENT-LINK][ERROR] Invalid UUID', [
                'paymentLinkId' => 'aaaaaaaa-bbbb-cccc-dddd',
            ]);

        $service = app(\App\Services\PaymentLinkService::class);
        $result = $service->createTransactionForPaymentLink($payload);

        $this->assertNull($result);
    }

    public function test_create_transaction_for_payment_link_id_not_exists(): void
    {
        PaymentLink::factory()->create();

        $payload = [
            'paymentLinkId' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
        ];

        Log::shouldReceive('error')
            ->once()
            ->with('[SERVICE][CREATE][TRANSACTION-PAYMENT-LINK][ERROR] PaymentLinkData not found', [
                'paymentLinkId' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee'
            ]);

        $service = app(\App\Services\PaymentLinkService::class);
        $result = $service->createTransactionForPaymentLink($payload);

        $this->assertNull($result);
    }

    public function test_create_transaction_for_payment_link_fails(): void
    {
        $merchant = Merchant::factory()->create();
        $paymentLink = PaymentLink::factory()->create([
            'payment_link_id' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
            'merchant_id' => $merchant->id,
        ]);

        $payload = [
            'paymentLinkId' => $paymentLink->payment_link_id,
            'email' => 'test@example.com',
            'fullname' => 'Test User',
            'paymentMethod' => 'card',
        ];

        Log::shouldReceive('info')->byDefault();
        Log::shouldReceive('error')
            ->once()
            ->with('[SERVICE][CREATE][TRANSACTION-PAYMENT-LINK][ERROR] CreateTrasactionDto is null');

        TransactionServiceFasade::shouldReceive('createTransaction')
            ->once()
            ->andReturn(null);

        $service = app(\App\Services\PaymentLinkService::class);
        $result = $service->createTransactionForPaymentLink($payload);

        $this->assertNull($result);
    }

    public function test_it_returns_null_if_transaction_already_exists_for_payment_link(): void
    {
        $merchant = Merchant::factory()->create();
        $transaction = Transaction::factory()->create();
        $paymentLink = PaymentLink::factory()->create([
            'payment_link_id' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
            'merchant_id' => $merchant->id,
            'transaction_id' => $transaction->id
        ]);

        $payload = [
            'paymentLinkId' => $paymentLink->payment_link_id,
            'email' => 'test@example.com',
            'fullname' => 'Test User',
            'paymentMethod' => 'card',
        ];

        Log::shouldReceive('error')
            ->once()
            ->with('[SERVICE][CREATE][TRANSACTION-PAYMENT-LINK][ERROR] The payment from the link has already been created');

        $service = app(\App\Services\PaymentLinkService::class);
        $result = $service->createTransactionForPaymentLink($payload);

        $this->assertNull($result);
    }

    public function test_create_transaction_for_payment_link_successfull(): void
    {
        $merchant = Merchant::factory()->create();
        $paymentLink = PaymentLink::factory()->create([
            'payment_link_id' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
            'merchant_id' => $merchant->id,
            'amount' => 10.00,
            'currency' => 'PLN',
            'notification_url' => 'https://example.com/notify',
            'return_url' => 'https://example.com/return',
        ]);

        $payload = [
            'paymentLinkId' => $paymentLink->payment_link_id,
            'email' => 'test@example.com',
            'fullname' => 'Test User',
            'paymentMethod' => 'TPAY',
        ];

        $mockCreateTransactionDto = new CreateTransactionDto(
            'abc-123',
            'test-uuid-1234',
            'Test Test',
            'test@email.com',
            'PLN',
            '10',
            'https://test.com',
            'https://test.com',
            PaymentMethod::PAYMENT_METHOD_TPAY,
            'https://example.com/link',
        );

        $transaction = Transaction::factory()->create([
            'transaction_uuid' => $mockCreateTransactionDto->uuid,
        ]);

        Log::shouldReceive('info')->once()->with('[SERVICE][CREATE][TRANSACTION-PAYMENT-LINK][START] Starting create process', ['paymentLinkId' => $payload['paymentLinkId']]);
        Log::shouldReceive('info')->once()->with('[SERVICE][CREATE][TRANSACTION-PAYMENT-LINK][DB] Transaction id was updated', ['paymentLinkId' => $payload['paymentLinkId']]);
        Log::shouldReceive('info')->once()->with('[SERVICE][CREATE][TRANSACTION-PAYMENT-LINK][COMPLETED] Transaction from payment link created successfully', [
            'paymentLink' => $mockCreateTransactionDto->link,
            'transactionUuid' => $mockCreateTransactionDto->uuid,
        ]);

        $mockTransactionService = Mockery::mock(TransactionService::class);
        $mockTransactionService
            ->shouldReceive('createTransaction')
            ->once()
            ->with([
                'amount' => $paymentLink->amount,
                'email' => $payload['email'],
                'name' => $payload['fullname'],
                'currency' => $paymentLink->currency,
                'paymentMethod' => $payload['paymentMethod'],
                'notificationUrl' => $paymentLink->notification_url,
                'returnUrl' => $paymentLink->return_url,
            ], $merchant->api_key)
            ->andReturn($mockCreateTransactionDto);

        $service = app(\App\Services\PaymentLinkService::class);

        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('createTransactionService');
        $property->setAccessible(true);
        $property->setValue($service, $mockTransactionService);

        $result = $service->createTransactionForPaymentLink($payload);

        $this->assertInstanceOf(PaymentLinkTransactionDto::class, $result);
        $this->assertEquals($mockCreateTransactionDto->link, $result->paymentLink);
        $this->assertEquals($mockCreateTransactionDto->uuid, $result->transactionUuid);

        $paymentLink->refresh();
        $this->assertEquals($transaction->id, $paymentLink->transaction_id);
    }

    public static function payloadAndApikey(): array
    {
        return [
            'payload' => [
                [
                    'paymentLinkId' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                    'amount' => 10.00,
                    'currency' => 'PLN',
                    'notificationUrl' => 'https://notification.url',
                    'returnUrl' => 'https://return.url',
                    'expiresAt' => (new \DateTimeImmutable())->add(new \DateInterval('PT1H'))->format(DATE_ATOM)
                ],
                'testowy-api-key'
            ]
        ];
    }
}