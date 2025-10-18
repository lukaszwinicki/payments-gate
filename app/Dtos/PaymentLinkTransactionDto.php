<?php

namespace App\Dtos;

readonly class PaymentLinkTransactionDto
{
    public function __construct(
        public string $paymentLink,
        public string $transactionUuid
    ){}
}