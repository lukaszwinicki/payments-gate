<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\Merchant;

class TransactionPolicy
{
    public function refund(Merchant $merchant, Transaction $transaction): bool
    {
        return $transaction->merchant_id === $merchant->id;
    }
}
