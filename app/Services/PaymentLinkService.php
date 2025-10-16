<?php

namespace App\Services;

use App\Dtos\CreatePaymentLinkDto;
use App\Models\Merchant;
use App\Models\PaymentLink;
use DateTimeImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PaymentLinkService
{
    public function create(array $paymentLinkBody, array $apiKeyHeader): ?CreatePaymentLinkDto
    {
        $uuid = (string) Str::uuid();
        $expiresAt = new DateTimeImmutable('+2 hours');
        $merchant = Merchant::where('api_key', $apiKeyHeader['x-api-key'][0])->first();

        if (!$merchant) {
            Log::error('[SERVICE][CREATE][PAYMENT-LINK][ERROR] Merchant not found', [
                'api_key' => $apiKeyHeader['x-api-key'][0],
            ]);
            return null;
        }

        Log::info('[SERVICE][CREATE][PAYMENT-LINK][START] Starting create process', [
            'payment_link_id' => $uuid,
            'amount' => $paymentLinkBody['amount'],
            'currency' => $paymentLinkBody['currency'],
            'notification_url' => $paymentLinkBody['notificationUrl'],
            'return_url' => $paymentLinkBody['returnUrl'],
            'expires_at' => $expiresAt
        ]);

        $paymentLinkDto = new CreatePaymentLinkDto(
            $uuid,
            $paymentLinkBody['amount'],
            $paymentLinkBody['currency'],
            $paymentLinkBody['notificationUrl'],
            $paymentLinkBody['returnUrl'],
            $expiresAt,
            $merchant->id
        );

        $paymentLink = new PaymentLink();
        $paymentLink->payment_link_id = $paymentLinkDto->paymentLinkId;
        $paymentLink->amount = $paymentLinkDto->amount;
        $paymentLink->currency = $paymentLinkDto->currency;
        $paymentLink->notification_url = $paymentLinkDto->notificationUrl;
        $paymentLink->return_url = $paymentLinkDto->returnUrl;
        $paymentLink->expires_at = Carbon::instance($paymentLinkDto->expiresAt);
        $paymentLink->merchant_id = $paymentLinkDto->merchantId;

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