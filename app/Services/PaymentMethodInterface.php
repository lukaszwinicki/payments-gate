<?php

namespace App\Services;

use App\Factory\Dtos\CreateTransactionDto;

interface PaymentMethodInterface
{
    public function create(array $transactionBody): CreateTransactionDto;
} 