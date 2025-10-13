<?php

namespace App\Dtos;

use DateTimeImmutable;

class CreatePaymentLinkDto
{
    public function __construct(
        public string $paymentLinkId,
        public string $amount,
        public string $currency,
        public string $notificationUrl,
        public string $returnUrl,
        public DateTimeImmutable $expiresAt
    ) {
    }
}