<?php

namespace Tests\Unit;

use App\Dtos\CreateTransactionDto;
use App\Enums\PaymentMethod;
use App\Factories\TransactionFactory;
use App\Factory\PaymentMethodFactory;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Services\TPayService;
use App\Services\PaynowService;
use App\Services\NodaService;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class TransactionServiceTest extends TestCase
{
    use RefreshDatabase;
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[DataProvider('transactionServiceDataProvider')]
    public function test_create_transaction_payment_service_returned_null(array $payload, string $apiKey, string $serviceClass, CreateTransactionDto $dto): void
    {
        Log::shouldReceive('info')->byDefault();
        Log::shouldReceive('error')
            ->once()
            ->with('[SERVICE][CREATE-TRANSACTION][ERROR] Payment service returned null for transaction creation', [
                'paymentMethod' => $payload['paymentMethod']
            ]);

        $paymentMethodEnum = PaymentMethod::tryFrom($payload['paymentMethod']);
        $paymentServiceMock = Mockery::mock($serviceClass);
        $paymentServiceMock->shouldReceive('create')
            ->once()
            ->with($payload)
            ->andReturn(null);

        $factoryMock = Mockery::mock(PaymentMethodFactory::class);
        $factoryMock->shouldReceive('getInstanceByPaymentMethod')
            ->with($paymentMethodEnum)
            ->andReturn($paymentServiceMock);

        $this->app->instance(PaymentMethodFactory::class, $factoryMock);

        $service = app(\App\Services\TransactionService::class);
        $result = $service->createTransaction($payload, $apiKey);

        $this->assertNull($result);
    }

    #[DataProvider('transactionServiceDataProvider')]
    public function test_create_transaction_returns_null_when_merchant_not_found(array $payload, string $apiKey, string $serviceClass, CreateTransactionDto $dto): void
    {
        Log::shouldReceive('info')->byDefault();
        Log::shouldReceive('error')
            ->once()
            ->with('[SERVICE][CREATE-TRANSACTION][ERROR] MerchantId returned null');

        $paymentMethodEnum = PaymentMethod::tryFrom($payload['paymentMethod']);
        $paymentServiceMock = Mockery::mock($serviceClass);
        $paymentServiceMock->shouldReceive('create')
            ->once()
            ->with($payload)
            ->andReturn($dto);

        $factoryMock = Mockery::mock(PaymentMethodFactory::class);
        $factoryMock->shouldReceive('getInstanceByPaymentMethod')
            ->with($paymentMethodEnum)
            ->andReturn($paymentServiceMock);
        $this->app->instance(PaymentMethodFactory::class, $factoryMock);

        $merchantBuilderMock = Mockery::mock(Merchant::class);
        $merchantBuilderMock->shouldReceive('where')
            ->with('api_key', $apiKey)
            ->andReturnSelf();
        $merchantBuilderMock->shouldReceive('first')
            ->andReturn(null);

        $service = app(\App\Services\TransactionService::class);
        $result = $service->createTransaction($payload, $apiKey);

        $this->assertNull($result);
    }

    #[DataProvider('transactionServiceDataProvider')]
    public function test_create_transaction_returns_null_when_transaction_save_fails(array $payload, string $apiKey, string $serviceClass, CreateTransactionDto $dto): void
    {
        Log::shouldReceive('info')->byDefault();
        Log::shouldReceive('error')
            ->once()
            ->with('[SERVICE][CREATE-TRANSACTION][ERROR] Transaction not created', [
                'paymentMethod' => $payload['paymentMethod']
            ]);

        $paymentMethodEnum = PaymentMethod::tryFrom($payload['paymentMethod']);
        $paymentServiceMock = Mockery::mock($serviceClass);
        $paymentServiceMock->shouldReceive('create')
            ->once()
            ->with($payload)
            ->andReturn($dto);

        $factoryMock = Mockery::mock(PaymentMethodFactory::class);
        $factoryMock->shouldReceive('getInstanceByPaymentMethod')
            ->with($paymentMethodEnum)
            ->andReturn($paymentServiceMock);
        $this->app->instance(PaymentMethodFactory::class, $factoryMock);

        $merchant = Merchant::factory()->create();

        $merchantBuilderMock = Mockery::mock(Merchant::class);
        $merchantBuilderMock->shouldReceive('where')
            ->with('api_key', $apiKey)
            ->andReturnSelf();
        $merchantBuilderMock->shouldReceive('first')
            ->andReturn($merchant);

        $transactionMock = Mockery::mock(Transaction::class)->makePartial();
        $transactionMock->shouldReceive('save')->once()->andReturn(false);

        $transactionFactoryMock = Mockery::mock(TransactionFactory::class);
        $transactionFactoryMock->shouldReceive('make')->andReturn($transactionMock);

        $this->instance(TransactionFactory::class, $transactionFactoryMock);

        $service = app(\App\Services\TransactionService::class);
        $result = $service->createTransaction($payload, $apiKey);

        $this->assertNull($result);
    }

    #[DataProvider('transactionServiceDataProvider')]
    public function test_create_transaction_created_successfull(array $payload, string $apiKey, string $serviceClass, CreateTransactionDto $dto): void
    {
        Log::shouldReceive('info')->byDefault();

        $paymentMethodEnum = PaymentMethod::tryFrom($payload['paymentMethod']);
        $paymentServiceMock = Mockery::mock($serviceClass);
        $paymentServiceMock->shouldReceive('create')
            ->once()
            ->with($payload)
            ->andReturn($dto);

        $factoryMock = Mockery::mock(PaymentMethodFactory::class);
        $factoryMock->shouldReceive('getInstanceByPaymentMethod')
            ->with($paymentMethodEnum)
            ->andReturn($paymentServiceMock);
        $this->app->instance(PaymentMethodFactory::class, $factoryMock);

        $merchant = Merchant::factory()->create();

        $merchantBuilderMock = Mockery::mock(Merchant::class);
        $merchantBuilderMock->shouldReceive('where')
            ->with('api_key', $apiKey)
            ->andReturnSelf();
        $merchantBuilderMock->shouldReceive('first')
            ->andReturn($merchant);

        Log::shouldReceive('info')
            ->once()
            ->with('[SERVICE][CREATE-TRANSACTION][COMPLETED] Transaction is waiting for confirmation', [
                'paymentMethod' => $payload['paymentMethod'],
                'transactionUuid' => $dto->uuid,
            ]);

        $service = app(\App\Services\TransactionService::class);
        $result = $service->createTransaction($payload, $apiKey);

        $this->assertInstanceOf(CreateTransactionDto::class, $result);
        $this->assertEquals($dto->link, $result->link);
        $this->assertEquals($dto->uuid, $result->uuid);
    }

    public static function transactionServiceDataProvider(): array
    {
        return [
            'TPAY' => [
                [
                    'amount' => 100,
                    'email' => 'jankowalski@gmail.com',
                    'name' => 'Jan Kowalski',
                    'currency' => 'PLN',
                    'paymentMethod' => 'TPAY',
                    'returnUrl' => 'https://test.com',
                    'notificationUrl' => 'https://notification.url'
                ],
                'testowy-api-key',
                TPayService::class,
                'dto' => new CreateTransactionDto(
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
                )
            ],
            'PAYNOW' => [
                [
                    'amount' => 100,
                    'email' => 'jankowalski@gmail.com',
                    'name' => 'Jan Kowalski',
                    'currency' => 'PLN',
                    'paymentMethod' => 'PAYNOW',
                    'returnUrl' => 'https://test.com',
                    'notificationUrl' => 'https://notification.url'
                ],
                'testowy-api-key',
                PaynowService::class,
                'dto' => new CreateTransactionDto(
                    'abc-123',
                    'test-uuid-1234',
                    'Test Test',
                    'test@email.com',
                    'PLN',
                    '10',
                    'https://test.com',
                    'https://test.com',
                    PaymentMethod::PAYMENT_METHOD_PAYNOW,
                    'https://example.com/link',
                )
            ],
            'NODA' => [
                [
                    'amount' => 100,
                    'email' => 'jankowalski@gmail.com',
                    'name' => 'Jan Kowalski',
                    'currency' => 'PLN',
                    'paymentMethod' => 'NODA',
                    'returnUrl' => 'https://test.com',
                    'notificationUrl' => 'https://notification.url'
                ],
                'testowy-api-key',
                NodaService::class,
                'dto' => new CreateTransactionDto(
                    'abc-123',
                    'test-uuid-1234',
                    'Test Test',
                    'test@email.com',
                    'PLN',
                    '10',
                    'https://test.com',
                    'https://test.com',
                    PaymentMethod::PAYMENT_METHOD_NODA,
                    'https://example.com/link',
                )
            ]
        ];
    }
}
