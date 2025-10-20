<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use App\Services\PaymentLinkService;

class PaymentLinkServiceFasade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PaymentLinkService::class;
    }
}
