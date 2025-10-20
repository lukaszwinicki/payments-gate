<?php

namespace Tests\Feature;

use App\Models\Merchant;
use App\Models\PaymentLink;
use App\Models\Transaction;
use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Dtos\PaymentLinkTransactionDto;
use App\Dtos\CreatePaymentLinkDto;
use App\Facades\PaymentLinkServiceFasade;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    #[DataProvider('createPaymentLinkPayloadAndApiKey')]
    public function test_create_payment_link_returns_null(array $payload, string $apiKey): void
    {
        Merchant::factory()->create();
        PaymentLinkServiceFasade::shouldReceive('createPaymentLink')
            ->once()
            ->with($payload, $apiKey)
            ->andReturn(null);

        $response = $this
            ->withHeaders([
                'x-api-key' => $apiKey
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
        $dto = new CreatePaymentLinkDto(
            'payment-link-test.url',
            '10.00',
            'PLN',
            'https://nootofication.url',
            'https://return.url',
            new \DateTimeImmutable('2025-10-21 11:00:00'),
            1
        );

        PaymentLinkServiceFasade::shouldReceive('createPaymentLink')
            ->once()
            ->with($payload, $apiKey)
            ->andReturn($dto);

        $response = $this
            ->withHeaders([
                'x-api-key' => $apiKey
            ])->postJson('/api/create-payment-link', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'paymentLink' => '/payment/' . $dto->paymentLinkId,
            ]);
    }

    public function test_payment_details_payment_link_not_found(): void
    {
        $response = $this->getJson('/api/payment/34af91ea-8baf-4d7f-a05c-5c932ec3b641');

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
            'payment_link_id' => '34af91ea-8baf-4d7f-a05c-5c932ec3b641',
            'transaction_id' => $transaction->id,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'expires_at' => (new \DateTimeImmutable())->add(new \DateInterval('PT1H'))
        ]);

        $response = $this->getJson('/api/payment/' . $paymentLink->payment_link_id);

        $response->assertStatus(200);
        $this->assertEquals('34af91ea-8baf-4d7f-a05c-5c932ec3b641', $response->json('payment.paymentLinkId'));
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
            'amount' => 10.00,
            'currency' => 'PLN',
        ];

        PaymentLinkServiceFasade::shouldReceive('createPaymentFromLink')
            ->once()
            ->with($payload)
            ->andReturn(null);

        $response = $this->postJson('/api/confirm-payment-link', $payload);

        $response->assertStatus(500)
            ->assertJson([
                'error' => 'The transaction could not be completed'
            ]);
    }

    public function test_confirm_payment_link_returns_successful_response_when_transaction_is_created(): void
    {
        $dto = new PaymentLinkTransactionDto(
            '34af91ea-8baf-4d7f-a05c-5c932ec3b641',
            '34af91ea-8baf-4d7f-a05c-5c932ec3b641'
        );

        PaymentLinkServiceFasade::shouldReceive('createPaymentFromLink')
            ->once()
            ->andReturn($dto);

        $response = $this->postJson('/api/confirm-payment-link');

        $response->assertStatus(200)
            ->assertJson([
                'link' => $dto->paymentLink,
            ]);
    }

    public static function createPaymentLinkPayloadAndApiKey(): array
    {
        return [
            'payload' => [
                [
                    'amount' => 10.00,
                    'currency' => 'PLN',
                    'expiresAt' => '2025-10-21 11:00:00',
                    'notificationUrl' => 'https://notofication.url',
                    'returnUrl' => 'https://return.url'
                ],
                'testowy-api-key'
            ]
        ];
    }
}