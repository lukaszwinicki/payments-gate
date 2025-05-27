<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use Illuminate\Support\Facades\Validator;

class CreateTransactionValidatorService
{
    public function validate(array $transactionBody): \Illuminate\Validation\Validator
    {
        $rules = [
            'amount' => 'required|numeric',
            'email' => 'required|email:rfc,dns|max:255',
            'name' => 'required|string|max:255',
            'currency' => 'required|string|max:3',
            'paymentMethod' => 'required|string',
            'notificationUrl' => 'required|string|url'
        ];

        $validator = Validator::make($transactionBody, $rules);

        $validator->after(function ($validator) use ($transactionBody) {
            if (!PaymentMethod::tryFrom($transactionBody['paymentMethod'])) {
                $validator->errors()->add('paymentMethod', 'Invalid payment method.');
            }
        });

        return $validator;
    }
}