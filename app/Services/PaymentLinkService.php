<?php

namespace App\Services;

use App\Dtos\CreatePaymentLinkDto;
use App\Models\PaymentLink;
use DateTimeImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PaymentLinkService
{
    public function create(array $paymentLinkBody): ?CreatePaymentLinkDto
    {

        $uuid = (string) Str::uuid();
        $expires_at = new DateTimeImmutable('+2 hours');

        Log::info('[SERVICE][CREATE][PAYMENT-LINK][START] Starting create process', [
            'payment_link_id' => $uuid,
            'amount' => $paymentLinkBody['amount'],
            'currency' => $paymentLinkBody['currency'],
            'notification_url' => $paymentLinkBody['notificationUrl'],
            'return_url' => $paymentLinkBody['returnUrl'],
            'expires_at' => $expires_at
        ]);

        $paymentLinkDto = new CreatePaymentLinkDto(
            $uuid,
            $paymentLinkBody['amount'],
            $paymentLinkBody['currency'],
            $paymentLinkBody['notificationUrl'],
            $paymentLinkBody['returnUrl'],
            $expires_at
        );

        $paymentLink = new PaymentLink();
        $paymentLink->payment_link_id = $paymentLinkDto->paymentLinkId;
        $paymentLink->amount = $paymentLinkDto->amount;
        $paymentLink->currency = $paymentLinkDto->currency;
        $paymentLink->notification_url = $paymentLinkDto->notificationUrl;
        $paymentLink->return_url = $paymentLinkDto->returnUrl;
        $paymentLink->expires_at = Carbon::instance($paymentLinkDto->expiresAt);

        if (!$paymentLink->save()) {
            Log::error('[SERVICE][CREATE][PAYMENT-LINK][DB][ERROR] Failed to save payment link to the database', [
                'paymentLinkId' => $paymentLinkDto->paymentLinkId,
            ]);
        }

        Log::info('[SERVICE][CREATE][PAYMENT-LINK][COMPLETED] Payment link created successfully', [
            'paymentLinkId' => $uuid,
        ]);

        return $paymentLinkDto;
    }
}