<?php

namespace App\Factory;

use App\Enums\PaymentMethod;
use App\Services\PaymentMethodInterface;
use App\Services\TPayService;
use Nette\NotImplementedException;


class PaymentMethodFactory
{
    public static function getInstanceByPaymentMethod(?PaymentMethod $paymentMethod): PaymentMethodInterface 
    {
        return match($paymentMethod)
        {
            PaymentMethod::PAYMENT_METHOD_TPAY => new TPayService(),
            default => throw new NotImplementedException("Payment method ". $paymentMethod->value . " is not implemented.")
        };
    } 
}
