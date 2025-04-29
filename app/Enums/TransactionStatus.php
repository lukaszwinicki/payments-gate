<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case SUCCESS = 'SUCCESS';
    case FAIL = 'FAIL';
    case PENDING = 'PENDING';
    case REFUND_SUCCESS = 'REFUND_SUCCESS';
    case REFUND_FAIL = 'REFUND_FAIL';
    case REFUND_PENDING = 'REFUND_PENDING';
}