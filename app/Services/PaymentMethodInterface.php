<?php

namespace App\Services;

use App\Factory\Dtos\CreateTransactionDto;
use App\Factory\Dtos\ConfirmTransactionDto;
use App\Factory\Dtos\RefundPaymentDto;

interface PaymentMethodInterface
{
    public function create(array $transactionBody): ?CreateTransactionDto;
    public function confirm(string $webHookBody, array $headers): ConfirmTransactionDto;
    public function refund(string $transactionUuid): ?RefundPaymentDto;
} 