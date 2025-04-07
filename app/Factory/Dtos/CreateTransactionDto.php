<?php

namespace App\Factory\Dtos;

class CreateTransactionDto
{
    public function __construct(
        public string $transactionId, 
        public string $uuid, 
        public string $name, 
        public string $email, 
        public string $currency, 
        public float $amount, 
        public string $link
    ) {}
}