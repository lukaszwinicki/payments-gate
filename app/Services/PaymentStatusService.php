<?php 

namespace App\Services;

use App\Models\Transaction;

class PaymentStatusService 
{
    public function getStatusByUuid(string $uuid): ?array {
        $transaction = Transaction::where('transaction_uuid', $uuid)->first();

        if(!$transaction){
            return null;
        }
        return [
            'status' => $transaction->status->value,
            'returnUrl' => $transaction->return_url,
        ];
    }
}