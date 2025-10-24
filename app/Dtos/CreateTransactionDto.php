<?php

namespace App\Dtos;

use App\Enums\PaymentMethod;

readonly class CreateTransactionDto
{
    public function __construct(
        public string $transactionId,
        public string $uuid,
        public string $name,
        public string $email,
        public string $currency,
        public string $amount,
        public string $notificationUrl,
        public string $returnUrl,
        public PaymentMethod $paymentMethod,
        public string $link
    ) {
    }
}