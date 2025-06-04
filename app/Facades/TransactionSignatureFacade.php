<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class TransactionSignatureFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'transaction-signature-service';
    }
}
