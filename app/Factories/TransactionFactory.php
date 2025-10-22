<?php

namespace App\Factories;

use App\Models\Transaction;

class TransactionFactory
{
    public function make(): Transaction
    {
        return new Transaction();
    }
}