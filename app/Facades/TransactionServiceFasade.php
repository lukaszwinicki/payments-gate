<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use App\Services\TransactionService;

class TransactionServiceFasade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return TransactionService::class;
    }
}
