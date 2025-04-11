<?php

namespace App\Factory\Dtos;

readonly class CreateTransactionDto
{
    public function __construct(
        public string $transactionId,
        public string $uuid,
        public string $name,
        public string $email,
        public string $currency,
        public string $amount,
        public string $link
    ) {
    }
}