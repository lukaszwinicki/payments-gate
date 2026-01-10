<?php

namespace App\Services;

use App\Models\Merchant;

class TransactionSignatureService
{
    public function calculateSignature(string $transactionUuid, Merchant $merchant): string
    {
        return hash_hmac('sha256', $transactionUuid, $merchant->secret_key);
    }
}