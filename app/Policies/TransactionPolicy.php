<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Merchant;

class TransactionPolicy
{
    public function refund(User|Merchant $principal, Transaction $transaction): bool
    {
        $merchantId = $principal instanceof Merchant
            ? $principal->id
            : $principal->merchant_id;

        return $transaction->merchant_id === $merchantId;
    }
}
