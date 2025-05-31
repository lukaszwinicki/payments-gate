<?php

namespace App\Services;

use App\Dtos\CreateTransactionDto;
use App\Dtos\ConfirmTransactionDto;
use App\Dtos\RefundPaymentDto;

interface PaymentMethodInterface
{
    public function create(array $transactionBody): ?CreateTransactionDto;
    public function confirm(array $webHookBody, array $headers): ?ConfirmTransactionDto;
    public function refund(array $refundBody): ?RefundPaymentDto;
    public function isSupportCurrency(string $currency): bool;
}