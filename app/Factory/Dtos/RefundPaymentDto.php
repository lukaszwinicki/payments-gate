<?php

namespace App\Factory\Dtos;

use App\Enums\TransactionStatus;

class RefundPaymentDto 
{
    public function __construct(public ?TransactionStatus $status = null) {}
}