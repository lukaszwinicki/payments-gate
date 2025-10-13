<?php

namespace App\Http\Controllers;

use App\Models\PaymentLink;
use App\Services\CreatePaymentLinkValidatorService;
use App\Services\PaymentLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentLinkController
{
    public function __construct(
        private PaymentLinkService $paymentLinkService,
        private CreatePaymentLinkValidatorService $validator
    ) {
    }

    public function createPaymentLink(Request $request): JsonResponse
    {
        $paymentLinkBody = $request->all();

        $paymentLinkBodyRequestValidator = $this->validator->validate($paymentLinkBody);

        if ($paymentLinkBodyRequestValidator->fails()) {
            Log::error('[CONTROLLER][CREATE][PAYMENT-LINK][VALIDATION][FAIL]', [
                'errors' => $paymentLinkBodyRequestValidator->errors()->toArray()
            ]);
            return response()->json(['error' => $paymentLinkBodyRequestValidator->errors()], 422);
        }

        $paymentLink = $this->paymentLinkService->create($paymentLinkBody);

        if ($paymentLink === null) {
            Log::error('[CONTROLLER][CREATE][PAYMENT-LINK][ERROR] Payment link returned null');
            return response()->json(['error' => 'The paymnet link could not be completed'], 500);
        }

        return response()->json([
            'payment_link' => config('app.frontendUrl') . '/payment/' . $paymentLink->paymentLinkId,
            'expires_at' => $paymentLink->expiresAt->format('Y-m-d H:i:s')
        ]);
    }

    public function paymentSummary(string $paymentLinkId): JsonResponse
    {
        $paymentLink = PaymentLink::where('payment_link_id', $paymentLinkId)->first();

        if (!$paymentLink) {
            return response()->json([
                'message' => 'Payment link not found'
            ], 404);
        }

        if (now()->greaterThan($paymentLink->expires_at)) {
            return response()->json([
                'message' => 'Payment link expired'
            ], 410);
        }

        return response()->json([
            'payment_link_id' => $paymentLink->payment_link_id,
            'amount' => $paymentLink->amount,
            'currency' => $paymentLink->currency,
        ]);
    }
}

?>