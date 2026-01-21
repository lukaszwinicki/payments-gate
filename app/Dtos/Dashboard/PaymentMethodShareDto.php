<?php

namespace App\Dtos\Dashboard;

use App\Enums\PaymentMethod;

readonly class PaymentMethodShareDto
{
    public function __construct(
        public PaymentMethod $paymentMethod,
        public int $count,
        public float $percentage
    ) {
    }

    public static function fromRow(
        PaymentMethod $method,
        int $count,
        int $total
    ): self {
        return new self(
            paymentMethod: $method,
            count: $count,
            percentage: $total > 0
            ? round(($count / $total) * 100, 2)
            : 0.0
        );
    }
}