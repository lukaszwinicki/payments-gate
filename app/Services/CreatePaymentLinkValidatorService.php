<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;

class CreatePaymentLinkValidatorService
{
    public function validate(array $paymentLinkBody): \Illuminate\Validation\Validator
    {
        $rules = [
            'amount' => 'required|numeric',
            'currency' => 'required|string|max:3',
            'expiresAt' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    try {
                        $expiration = \Carbon\Carbon::parse($value);
                    } catch (\Exception $e) {
                        $fail('Invalid expiration date format.');
                        return;
                    }

                    $now = now();

                    if ($expiration->lt($now)) {
                        $fail('The expiration date cannot be in the past.');
                    }

                    if ($expiration->lt($now->addMinute())) {
                        $fail('The expiration date must be at least one minute later than now.');
                    }
                }
            ],
            'notificationUrl' => 'required|string|url',
            'returnUrl' => 'required|string|url'
        ];

        $validator = Validator::make($paymentLinkBody, $rules);

        $validator->after(function ($validator) use ($paymentLinkBody) {
            $allowedCurrencies = ['PLN', 'USD', 'EUR'];
            if (!in_array(strtoupper($paymentLinkBody['currency'] ?? ''), $allowedCurrencies)) {
                $validator->errors()->add('currency', 'Unsupported currency. Allowed: PLN, USD, EUR.');
            }
        });

        return $validator;
    }
}