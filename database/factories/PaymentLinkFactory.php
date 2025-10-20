<?php

namespace Database\Factories;

use App\Models\Merchant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentLink>
 */
class PaymentLinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payment_link_id' => $this->faker->uuid(),
            'transaction_id' => null,
            'merchant_id' => Merchant::factory(),
            'amount' => $this->faker->randomFloat(2, 1, 1000),
            'currency' => 'PLN',
            'notification_url' => $this->faker->url(),
            'return_url' => $this->faker->url(),
            'expires_at' => \DateTimeImmutable::createFromMutable($this->faker->dateTime()),
        ];
    }
}
