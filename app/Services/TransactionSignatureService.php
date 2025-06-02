<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Merchant;

class TransactionSignatureService
{
    public function calculateSignature(Transaction $transaction): string
    {
        $merchantSecretKey = Merchant::where('id', $transaction->merchant_id)->first();
        return hash_hmac('sha256', $transaction->transaction_uuid . $transaction->payment_method->value, $merchantSecretKey->secret_key);
    }
}