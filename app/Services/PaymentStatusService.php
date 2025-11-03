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
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'paymentMethod' => $transaction->payment_method,
            'returnUrl' => $transaction->return_url,
        ];
    }
}