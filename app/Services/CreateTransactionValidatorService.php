<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;

class CreateTransactionValidatorService
{
    public function validate(array $transactionBody)
    {
        $rules = [
            'amount' => 'required|numeric',
            'email' => 'required|email:rfc,dns|max:255',
            'name' => 'required|string|max:255',
            'payment_method' => 'required|string',
            'notification_url' => 'required|string|url'
        ];

        return Validator::make($transactionBody, $rules);
    }
}