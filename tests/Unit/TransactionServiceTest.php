<?php

namespace Tests\Unit;

use App\Dtos\CreateTransactionDto;
use App\Dtos\Dashboard\DashboardBalancesDto;
use App\Dtos\Dashboard\FailedTransactionsCountDto;
use App\Dtos\Dashboard\MerchantTransactionsListDto;
use App\Dtos\Dashboard\PaymentMethodShareListDto;
use App\Dtos\Dashboard\RecentTransactionsListDto;
use App\Dtos\Dashboard\TransactionNotificationsListDto;
use App\Dtos\Dashboard\TransactionsCountDto;
use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Factories\TransactionFactory;
use App\Factory\PaymentMethodFactory;
use App\Models\Merchant;
use App\Models\Notification;
use App\Models\Transaction;
use App\Services\TPayService;
use App\Services\PaynowService;
use App\Services\NodaService;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class TransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    private TransactionService $service;

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TransactionService::class);
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

        $merchant = Merchant::factory()->create([
            'api_key' => $apiKey,
        ]);

        $service = app(\App\Services\TransactionService::class);
        $result = $service->createTransaction($payload, $merchant);

        $this->assertNull($result);
    }

    #[DataProvider('transactionServiceDataProvider')]
    public function test_create_transaction_returns_null_when_payment_service_returns_null(array $payload, string $apiKey, string $serviceClass, CreateTransactionDto $dto): void
    {

        Log::shouldReceive('info')->byDefault();
        Log::shouldReceive('error')
            ->once()
            ->with(
                '[SERVICE][CREATE-TRANSACTION][ERROR] Payment service returned null for transaction creation',
                ['paymentMethod' => $payload['paymentMethod']]
            );

        $paymentServiceMock = Mockery::mock($serviceClass);
        $paymentServiceMock->shouldReceive('create')
            ->once()
            ->with($payload)
            ->andReturn(null);

        $factoryMock = Mockery::mock(PaymentMethodFactory::class);
        $factoryMock->shouldReceive('getInstanceByPaymentMethod')
            ->with(PaymentMethod::tryFrom($payload['paymentMethod']))
            ->andReturn($paymentServiceMock);
        $this->app->instance(PaymentMethodFactory::class, $factoryMock);

        $merchant = Merchant::factory()->create([
            'api_key' => $apiKey,
        ]);

        $service = app(\App\Services\TransactionService::class);
        $result = $service->createTransaction($payload, $merchant);

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
        $result = $service->createTransaction($payload, $merchant);

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
        $result = $service->createTransaction($payload, $merchant);

        $this->assertInstanceOf(CreateTransactionDto::class, $result);
        $this->assertEquals($dto->link, $result->link);
        $this->assertEquals($dto->uuid, $result->uuid);
    }

    public function test_get_merchant_transactions(): void
    {
        $merchant = Merchant::factory()->create();

        Transaction::factory()->count(3)->create([
            'merchant_id' => $merchant->id,
        ]);

        $result = $this->service->getMerchantTransactions($merchant->id);

        $this->assertInstanceOf(MerchantTransactionsListDto::class, $result);
        $this->assertCount(3, $result->transactions);
    }

    public function test_get_transaction_notifications(): void
    {
        $merchant = Merchant::factory()->create();
        $transaction = Transaction::factory()->create([
            'merchant_id' => $merchant->id,
        ]);

        Notification::factory()->count(2)->create([
            'transaction_id' => $transaction->id,
        ]);

        $result = $this->service->getTransactionNotifications($merchant->id);

        $this->assertInstanceOf(TransactionNotificationsListDto::class, $result);
        $this->assertCount(2, $result->notifications);
    }

    public function test_get_recent_transactions_returns_max_10(): void
    {
        $merchant = Merchant::factory()->create();

        Transaction::factory()->count(15)->create([
            'merchant_id' => $merchant->id,
        ]);

        $result = $this->service->getRecentTransactions($merchant->id);

        $this->assertInstanceOf(RecentTransactionsListDto::class, $result);
        $this->assertCount(10, $result->transactions);
    }

    public function test_get_failed_transactions_count(): void
    {
        $merchant = Merchant::factory()->create();

        Transaction::factory()->count(2)->create([
            'merchant_id' => $merchant->id,
            'status' => TransactionStatus::FAIL,
        ]);

        Transaction::factory()->count(3)->create([
            'merchant_id' => $merchant->id,
            'status' => TransactionStatus::SUCCESS,
        ]);

        $result = $this->service->getFailedCount($merchant->id);

        $this->assertInstanceOf(FailedTransactionsCountDto::class, $result);
        $this->assertEquals(2, $result->total);
    }

    public function test_get_transactions_balances(): void
    {
        $merchant = Merchant::factory()->create();

        Transaction::factory()->create([
            'merchant_id' => $merchant->id,
            'currency' => 'PLN',
            'amount' => 100,
        ]);

        Transaction::factory()->create([
            'merchant_id' => $merchant->id,
            'currency' => 'EUR',
            'amount' => 50,
        ]);

        $result = $this->service->getTransactionsBalances($merchant->id);

        $this->assertInstanceOf(DashboardBalancesDto::class, $result);
        $this->assertEquals(100, $result->pln);
        $this->assertEquals(50, $result->eur);
        $this->assertEquals(0, $result->usd);
    }

    public function test_get_total_transactions_count(): void
    {
        $merchant = Merchant::factory()->create();

        Transaction::factory()->count(7)->create([
            'merchant_id' => $merchant->id,
        ]);

        $result = $this->service->getTotalCount($merchant->id);

        $this->assertInstanceOf(TransactionsCountDto::class, $result);
        $this->assertEquals(7, $result->total);
    }

    public function test_get_payment_method_share(): void
    {
        $merchant = Merchant::factory()->create();

        Transaction::factory()->count(3)->create([
            'merchant_id' => $merchant->id,
            'payment_method' => 'TPAY',
        ]);

        Transaction::factory()->count(1)->create([
            'merchant_id' => $merchant->id,
            'payment_method' => 'PAYNOW',
        ]);

        $result = $this->service->getPaymentMethodShare($merchant->id);

        $this->assertInstanceOf(PaymentMethodShareListDto::class, $result);
        $this->assertCount(2, $result->shares);

        $tpay = collect($result->shares)
            ->first(fn($s) => $s->paymentMethod === PaymentMethod::PAYMENT_METHOD_TPAY);

        $this->assertNotNull($tpay);
        $this->assertEquals(75, $tpay->percentage);
    }

    public function test_get_payment_method_share_empty(): void
    {
        $merchant = Merchant::factory()->create();

        $result = $this->service->getPaymentMethodShare($merchant->id);

        $this->assertInstanceOf(PaymentMethodShareListDto::class, $result);
        $this->assertCount(0, $result->shares);
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
