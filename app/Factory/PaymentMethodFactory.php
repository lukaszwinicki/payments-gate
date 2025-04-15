<?php

namespace App\Factory;

use App\Enums\PaymentMethod;
use App\Services\PaymentMethodInterface;
use App\Services\TPayService;
use Nette\NotImplementedException;
use Illuminate\Support\Facades\App;


class PaymentMethodFactory
{
    public static function getInstanceByPaymentMethod(?PaymentMethod $paymentMethod): PaymentMethodInterface
    {
        return match ($paymentMethod) {
            PaymentMethod::PAYMENT_METHOD_TPAY => App::make(TPayService::class),
            default => throw new NotImplementedException("Payment method " . ($paymentMethod) . " is not implemented.")
        };
    }
}
