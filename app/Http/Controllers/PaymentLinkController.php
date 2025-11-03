<?php

namespace App\Http\Controllers;

use App\Models\PaymentLink;
use App\Models\Transaction;
use App\Services\PaymentLinkValidatorService;
use App\Services\PaymentLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PaymentLinkController
{
    public function __construct(
        private PaymentLinkService $paymentLinkService,
        private PaymentLinkValidatorService $validator
    ) {
    }

    public function createPaymentLink(Request $request): JsonResponse
    {
        $paymentLinkBody = $request->all();
        $apiKey = $request->header('x-api-key');

        Log::info('[CONTROLLER][CREATE][PAYMENT-LINK][START] Received create payment link request', [
            'transactionBody' => $paymentLinkBody,
            'apiKey' => $apiKey
        ]);

        if (!$apiKey) {
            Log::error('[CONTROLLER][CREATE][PAYMENT-LINK][ERROR] Missing required header: X-API-KEY');
            return response()->json(['error' => 'Unauthorized missing X-API-KEY'], 401);
        }

        $paymentLinkBodyRequestValidator = $this->validator->validate($paymentLinkBody);

        if ($paymentLinkBodyRequestValidator->fails()) {
            Log::error('[CONTROLLER][CREATE][PAYMENT-LINK][VALIDATION][FAIL]', [
                'errors' => $paymentLinkBodyRequestValidator->errors()->toArray()
            ]);
            return response()->json(['error' => $paymentLinkBodyRequestValidator->errors()], 422);
        }

        $paymentLink = $this->paymentLinkService->createPaymentLink($paymentLinkBody, $apiKey);

        if ($paymentLink === null) {
            Log::error('[CONTROLLER][CREATE][PAYMENT-LINK][ERROR] Payment link service returned null');
            return response()->json(['error' => 'The payment link could not be generated'], 500);
        }

        Log::info('[CONTROLLER][CREATE][PAYMENT-LINK][COMPLETED] Payment link was created', [
            'transactionBody' => $paymentLinkBody,
            'apiKey' => $apiKey
        ]);

        return response()->json([
            'paymentLink' => config('app.frontendUrl') . '/payment/' . $paymentLink->paymentLinkId,
        ]);
    }

    public function paymentDetails(string $paymentLinkId): JsonResponse
    {
        if (!Str::isUuid($paymentLinkId)) {
            Log::error('[CONTROLLER][PAYMENT-DETAILS][ERROR] Invalid UUID', [
                'paymentLinkId' => $paymentLinkId
            ]);
            return response()->json([
                'error' => 'Invalid payment link'
            ], 500);
        }

        $paymentLink = PaymentLink::where('payment_link_id', $paymentLinkId)->first();

        if (!$paymentLink) {
            Log::info('[CONTROLLER][PAYMENT-DETAILS][ERROR] PaymentLink not found', [
                'paymentLinkId' => $paymentLinkId
            ]);
            return response()->json([
                'error' => 'Payment link not found'
            ], 404);
        }

        if (now()->greaterThan($paymentLink->expires_at)) {
            Log::info('[CONTROLLER][PAYMENT-DETAILS][ERROR] Payment link expired', [
                'paymentLinkId' => $paymentLinkId
            ]);
            return response()->json([
                'error' => 'Payment link expired'
            ], 410);
        }

        $transactionDetailsFromLink = Transaction::where('id', $paymentLink->transaction_id)->first();

        return response()->json([
            'payment' => [
                'paymentLinkId' => $paymentLink->payment_link_id,
                'amount' => $paymentLink->amount,
                'currency' => $paymentLink->currency,
            ],
            'transaction' => $transactionDetailsFromLink === null ? null : [
                'status' => $transactionDetailsFromLink->status,
                'amount' => $transactionDetailsFromLink->amount,
                'currency' => $transactionDetailsFromLink->currency,
                'paymentMethod' => $transactionDetailsFromLink->payment_method,
                'returnUrl' => $transactionDetailsFromLink->return_url
            ]
        ]);
    }

    public function confirmPaymentLink(Request $request): JsonResponse
    {
        $paymentLinkBody = $request->all();

        Log::info('[CONTROLLER][CREATE][CONFIRM-PAYMENT-LINK][START] Received create transaction from payment link request', [
            'paymentLinkBody' => $paymentLinkBody
        ]);

        $createTransactionFromPaymentLinkDto = $this->paymentLinkService->createTransactionForPaymentLink($paymentLinkBody);

        if ($createTransactionFromPaymentLinkDto === null) {
            Log::error('[CONTROLLER][PAYMENT-LINK][ERROR] Payment link service returned null for payment link transaction creation');
            return response()->json(['error' => 'The transaction could not be completed'], 500);
        }

        Log::info('[CONTROLLER][CREATE][CONFIRM-PAYMENT-LINK][COMPLETED] Transaction from payment link was created successfully', [
            'transactionUuid' => $createTransactionFromPaymentLinkDto->transactionUuid,
            'link' => $createTransactionFromPaymentLinkDto->paymentLink
        ]);

        return response()->json(['link' => $createTransactionFromPaymentLinkDto->paymentLink]);
    }
}
?>