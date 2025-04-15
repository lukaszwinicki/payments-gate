<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transaction_uuid' => $this->faker->uuid(),
            'transactions_id' => $this->faker->unique()->numberBetween(1000, 9999),
            'amount' => $this->faker->randomFloat(2, 1, 1000),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'currency' => 'PLN', 
            'status' => TransactionStatus::SUCCESS, 
            'notification_url' => $this->faker->url(),
            'payment_method' => PaymentMethod::PAYMENT_METHOD_TPAY->value, 
        ];
    }
}
