<?php

namespace App\Dtos;

readonly class PaymentLinkTransactionBodyDto
{
    public function __construct(
        public string $paymentLink,
        public string $transactionUuid
    ){}
}