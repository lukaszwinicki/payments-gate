<?php

namespace App\Services;

use App\Factory\Dtos\CreateTransactionDto;
use App\Factory\Dtos\ConfirmTransactionDto;

interface PaymentMethodInterface
{
    public function create(array $transactionBody): CreateTransactionDto;
    public function confirm(string $webHookBody, array $headers): ConfirmTransactionDto;
} 