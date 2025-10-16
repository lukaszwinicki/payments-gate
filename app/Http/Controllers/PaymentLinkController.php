<?php

namespace App\Http\Controllers;

use App\Models\PaymentLink;
use App\Models\Transaction;
use App\Services\CreatePaymentLinkValidatorService;
use App\Services\PaymentLinkService;
use App\Services\PaymentLinkTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentLinkController
{
    public function __construct(
        private PaymentLinkService $paymentLinkService,
        private PaymentLinkTransactionService $paymentLinkTransactionService,
        private CreatePaymentLinkValidatorService $validator
    ) {
    }

    public function createPaymentLink(Request $request): JsonResponse
    {
        $paymentLinkBody = $request->all();
        $apiKeyHeader = $request->header();

        $paymentLinkBodyRequestValidator = $this->validator->validate($paymentLinkBody);

        if ($paymentLinkBodyRequestValidator->fails()) {
            Log::error('[CONTROLLER][CREATE][PAYMENT-LINK][VALIDATION][FAIL]', [
                'errors' => $paymentLinkBodyRequestValidator->errors()->toArray()
            ]);
            return response()->json(['error' => $paymentLinkBodyRequestValidator->errors()], 422);
        }

        $paymentLink = $this->paymentLinkService->create($paymentLinkBody, $apiKeyHeader);

        if ($paymentLink === null) {
            Log::error('[CONTROLLER][CREATE][PAYMENT-LINK][ERROR] Payment link returned null');
            return response()->json(['error' => 'The paymnet link could not be completed'], 500);
        }

        return response()->json([
            'paymentLink' => config('app.frontendUrl') . '/payment/' . $paymentLink->paymentLinkId,
            'expiresAt' => $paymentLink->expiresAt->format('Y-m-d H:i:s')
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

        $transactionDetailsFromLink = Transaction::where('id', $paymentLink->transaction_id)->first();
        if ($transactionDetailsFromLink) {
            return response()->json([
                'message' => 'Transaction has already been paid',
                'data' => [
                    'transactionPaymentLinkId' => $paymentLinkId,
                    'status' => $transactionDetailsFromLink->status,
                    'amount' => $transactionDetailsFromLink->amount,
                    'currency' => $transactionDetailsFromLink->currency,
                    'paymentMethod' => $transactionDetailsFromLink->payment_method
                ]
            ], 400);
        }

        return response()->json([
            'paymentLinkId' => $paymentLink->payment_link_id,
            'amount' => $paymentLink->amount,
            'currency' => $paymentLink->currency,
        ]);
    }

    public function createPaymentLinkTransaction(Request $request): JsonResponse
    {
        $paymentLinkBody = $request->all();
        $createTransactionFromPaymentLinkDto = $this->paymentLinkTransactionService->create($paymentLinkBody);

        if ($createTransactionFromPaymentLinkDto === null) {
            Log::error('[CONTROLLER][CREATE][PAYMENT-LINK][ERROR] Payment service returned null for payment link transaction creation');
            return response()->json(['error' => 'The transaction could not be completed'], 500);
        }

        return response()->json(['link' => $createTransactionFromPaymentLinkDto->paymentLink]);
    }
}

?>