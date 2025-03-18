<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case SUCCESS = 'SUCCESS';
    case FAIL = 'FAIL';
    case PENDING = 'PENDING';
}