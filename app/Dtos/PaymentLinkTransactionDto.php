<?php 

namespace App\Dtos;

readonly class PaymentLinkTransactionDto
{
    public function __construct(
        public float $amount,
        public string $email,
        public string $fullname,
        public string $currency,
        public string $paymentMethod,
        public string $notificationUrl,
        public string $returnUrl,
    ){}
}