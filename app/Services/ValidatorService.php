<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;

class ValidatorService
{

    public function validate(array $transactionBody)
    {
        $rules = [
            'amount' => 'required|numeric',
            'email' => 'required|email:rfc,dns|max:255|unique:transactions,email',
            'name' => 'required|string|max:255',
            'currency' => 'required|string|max:3',
            'payment_method' => 'required|string',
            'notification_url' => 'required|string|url' 
        ];

        return Validator::make($transactionBody,$rules);
    }

} 