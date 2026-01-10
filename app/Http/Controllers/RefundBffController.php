<?php

namespace App\Http\Controllers;

use App\Facades\TransactionSignatureFacade;
use App\Http\Requests\MerchantRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\JsonResponse;

class RefundBffController extends Controller
{
    public function refund(MerchantRequest $request): JsonResponse
    {
        $refundPayload = $request->all();
        $merchant = $request->merchant();

        if (!Str::isUuid($refundPayload['transactionUuid'])) {
            Log::error('[CONTROLLER][BFF][REFUND][FAILED] Invalid or missing transaction UUID', [
                'transactionUuid' => $refundPayload['transactionUuid'],
            ]);
            return response()->json([
                'error' => 'Invalid or missing transaction UUID'
            ], 422);
        }

        try {
            $refundResponse = Http::timeout(5)
                ->retry(2, 200)
                ->withHeaders([
                    'X-API-KEY' => $merchant->api_key,
                    'signature' => TransactionSignatureFacade::calculateSignature($refundPayload['transactionUuid'], $merchant),
                ])
                ->post(config('app.paymentsGatewayBaseUrl') . '/api/refund-payment', $refundPayload);

            return response()->json($refundResponse->json(), $refundResponse->status());

        } catch (\Illuminate\Http\Client\RequestException $e) {
            $response = $e->response;
            Log::info('[BFF][REFUND] Refund request returned error', [
                'merchant_id' => $merchant->id,
                'transaction_uuid' => $refundPayload['transactionUuid'],
                'status' => $response?->status(),
                'body' => $response?->json(),
            ]);

            return response()->json($response?->json() ?? ['error' => 'Unknown error'], $response?->status() ?? 500);
        }
    }
}
