<?php

namespace App\Factory\Dtos;

use App\Enums\TransactionStatus;

readonly class RefundPaymentDto
{
    public function __construct(public TransactionStatus $status)
    {
    }
}