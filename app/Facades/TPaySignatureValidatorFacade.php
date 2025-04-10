<?php 

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class TPaySignatureValidatorFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'tpay-signature-validator';
    }
}
