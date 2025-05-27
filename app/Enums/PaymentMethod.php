<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case PAYMENT_METHOD_TPAY = 'TPAY';
    case PAYMENT_METHOD_PAYNOW = 'PAYNOW';
    case PAYMENT_METHOD_NODA = 'NODA';

    public function supportedCurrencies(): array
    {
        return match($this){
            self::PAYMENT_METHOD_TPAY => ['PLN'],
            self::PAYMENT_METHOD_PAYNOW => ['PLN'],
            self::PAYMENT_METHOD_NODA => ['USD','EUR']
        };
    }
    public function supportsCurrency(string $currency): bool
    {
        return in_array($currency, $this->supportedCurrencies(), true);
    }
}