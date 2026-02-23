<?php

namespace App\Http\Controllers;

use App\Http\Requests\MerchantRequest;
use App\Models\PaymentLink;
use App\Models\Transaction;
use App\Services\PaymentLinkValidatorService;
use App\Services\PaymentLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

class PaymentLinkController
{
    public function __construct(
        private PaymentLinkService $paymentLinkService,
        private PaymentLinkValidatorService $validator
    ) {
    }

    #[OA\Post(
        path: "/api/create-payment-link",
        tags: ["Payment Links"],
        summary: "Create payment link",
        parameters: [
            new OA\Parameter(
                name: "X-API-KEY",
                in: "header",
                required: true,
                description: "Merchant API key",
                schema: new OA\Schema(
                    type: "string",
                    example: "api-key"
                )
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["amount", "currency", "notificationUrl", "returnUrl", "expiresAt"],
                properties: [
                    new OA\Property(property: "amount", type: "number", format: "float", example: 199.99),
                    new OA\Property(property: "currency", type: "string", example: "PLN"),
                    new OA\Property(property: "notificationUrl", type: "string", format: "uri", example: "https://test.payment-gate.pl/callback"),
                    new OA\Property(property: "returnUrl", type: "string", format: "uri", example: "https://test.payment-gate.pl/return-url"),
                    new OA\Property(property: "expiresAt", type: "string", format: "date-time", example: "2026-03-01 12:00:00"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Payment link created",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "paymentLink",
                            type: "string",
                            example: "http://payments-gate.pl/payment/17a4186a-9ba1-493d-aae6-d9c2918e7085"
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error"
            ),
            new OA\Response(
                response: 500,
                description: "Payment link generation failed"
            ),
        ]
    )]
    public function createPaymentLink(MerchantRequest $request): JsonResponse
    {
        $paymentLinkBody = $request->all();
        $apiKey = $request->header('X-API-KEY');
        $merchant = $request->merchant();

        Log::info('[CONTROLLER][CREATE][PAYMENT-LINK][START] Received create payment link request', [
            'transactionBody' => $paymentLinkBody,
            'apiKey' => $apiKey
        ]);

        $paymentLinkBodyRequestValidator = $this->validator->validate($paymentLinkBody);

        if ($paymentLinkBodyRequestValidator->fails()) {
            Log::error('[CONTROLLER][CREATE][PAYMENT-LINK][VALIDATION][FAIL]', [
                'errors' => $paymentLinkBodyRequestValidator->errors()->toArray()
            ]);
            return response()->json(['error' => $paymentLinkBodyRequestValidator->errors()], 422);
        }

        $paymentLink = $this->paymentLinkService->createPaymentLink(
            $paymentLinkBody,
            $merchant
        );

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

    #[OA\Get(
        path: "/api/payment/{payment_link_id}",
        tags: ["Payment Links"],
        summary: "Get payment link details",
        parameters: [
            new OA\Parameter(
                name: "paymentLinkId",
                in: "path",
                required: true,
                description: "Payment link UUID",
                schema: new OA\Schema(
                    type: "string",
                    format: "uuid",
                    example: "c6e2c816-3f5e-417b-9e91-a794223aa903"
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Payment link details",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "payment",
                            type: "object",
                            properties: [
                                new OA\Property(property: "paymentLinkId", type: "string", example: "d0947206-f142-44d1-af78-7b6b865f7394"),
                                new OA\Property(property: "amount", type: "number", format: "float", example: 199.99),
                                new OA\Property(property: "currency", type: "string", example: "PLN"),
                            ]
                        ),
                        new OA\Property(
                            property: "transaction",
                            type: "object",
                            nullable: true,
                            properties: [
                                new OA\Property(property: "status", type: "string", example: "SUCCESS"),
                                new OA\Property(property: "amount", type: "number", format: "float", example: 199.99),
                                new OA\Property(property: "currency", type: "string", example: "PLN"),
                                new OA\Property(property: "paymentMethod", type: "string", example: "TPAY"),
                                new OA\Property(property: "returnUrl", type: "string", format: "uri", example: "https://test.payment-gate.pl/return-url"),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Payment link not found"),
            new OA\Response(response: 410, description: "Payment link expired"),
            new OA\Response(response: 500, description: "Invalid payment link"),
        ]
    )]
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