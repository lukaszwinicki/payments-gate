<?php

namespace App\Dtos;

use DateTimeImmutable;

readonly class CreatePaymentLinkDto
{
    public function __construct(
        public string $paymentLinkId,
        public string $amount,
        public string $currency,
        public string $notificationUrl,
        public string $returnUrl,
        public DateTimeImmutable $expiresAt,
        public int $merchantId
    ) {
    }
}