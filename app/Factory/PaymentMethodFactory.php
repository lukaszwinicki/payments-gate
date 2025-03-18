<?php

namespace App\Factory;

use App\Enums\PaymentMethod;
use App\Services\TPayService;
use Nette\NotImplementedException;

class PaymentMethodFactory
{

    public static function getInstanceByPaymentMethod(string $paymentMethod) 
    {

        return match($paymentMethod)
        {
            PaymentMethod::PAYMENT_METHOD_TPAY => new TPayService(),
            default => throw new NotImplementedException("Payment method ". $paymentMethod . " is not implemented.")
        };

    } 

}
