<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case PAYMENT_METHOD_TPAY = 'TPAY';
    case PAYMENT_METHOD_PAYNOW = 'PAYNOW';
}