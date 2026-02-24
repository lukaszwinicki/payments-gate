<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Facades\TransactionSignatureFacade;
use App\Factory\PaymentMethodFactory;
use App\Http\Requests\MerchantRequest;
use App\Jobs\ProcessWebhookJob;
use App\Models\Transaction;
use App\Services\PaymentStatusService;
use App\Services\TransactionService;
use App\Services\TransactionValidatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use InvalidArgumentException;
use OpenApi\Attributes as OA;

class TransactionController extends Controller
{
    use AuthorizesRequests;

    public function __construct
    (
        private TransactionService $createTransactionService,
        private TransactionValidatorService $transactionValidatorService,
        private PaymentMethodFactory $paymentMethodFactory,
        protected PaymentStatusService $paymentStatusService,
    ) {
    }

    #[OA\Post(
        path: "/api/create-transaction",
        tags: ["Transactions"],
        summary: "Create new transaction",
        description: "Supported payment methods: TPAY, PAYNOW, NODA.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["amount", "email", "name", "paymentMethod", "notificationUrl", "returnUrl"],
                properties: [
                    new OA\Property(property: "amount", type: "string", example: "12.34"),
                    new OA\Property(property: "email", type: "string", example: "jan.kowalski@gmail.com"),
                    new OA\Property(property: "name", type: "string", example: "Jan Kowalski"),
                    new OA\Property(property: "currency", type: "string", example: "PLN"),
                    new OA\Property(
                        property: "paymentMethod",
                        type: "string",
                        example: "TPAY",
                        enum: ["TPAY", "PAYNOW", "NODA"],
                    ),
                    new OA\Property(property: "notificationUrl", type: "string", example: "https://test.payment-gate.pl/callback"),
                    new OA\Property(property: "returnUrl", type: "string", example: "https://test.payment-gate.pl/return-url"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Transaction created",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "link", type: "string", example: "https://link-to-payment"),
                        new OA\Property(property: "transactionUuid", type: "string", example: "c6e2c816-3f5e-417b-9e91-a794223aa903"),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Invalid transaction data provided",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "The transaction could not be completed"),
                    ]
                )
            ),
        ]
    )]

    public function createTransaction(MerchantRequest $request): JsonResponse
    {
        $transactionBody = $request->all();
        $merchant = $request->merchant();

        Log::info('[CONTROLLER][CREATE][START] Received create payment request', [
            'paymentMethod' => $transactionBody['paymentMethod'],
            'transactionBody' => $transactionBody,
            'apiKey' => $request->header('X-API-KEY') ?? $merchant->api_key
        ]);

        $transactionPayloadValidator = $this->transactionValidatorService->validate($transactionBody);

        if ($transactionPayloadValidator->fails()) {
            Log::error('[CONTROLLER][CREATE][PAYMENT-LINK][VALIDATION][FAIL]', [
                'errors' => $transactionPayloadValidator->errors()->toArray()
            ]);
            return response()->json(['error' => $transactionPayloadValidator->errors()], 422);
        }

        $createTransactionDto = $this->createTransactionService->createTransaction($transactionBody, $merchant);

        if ($createTransactionDto === null) {
            Log::error('[CONTROLLER][CREATE][ERROR] Payment service returned null for transaction creation', [
                'paymentMethod' => $transactionBody['paymentMethod']
            ]);
            return response()->json(['error' => 'The transaction could not be completed'], 500);
        }

        Log::info('[CONTROLLER][CREATE][COMPLETED] Transaction was created successfully', [
            'transactionUuid' => $createTransactionDto->uuid,
        ]);

        return response()->json(['link' => $createTransactionDto->link, 'transactionUuid' => $createTransactionDto->uuid]);
    }

    public function confirmTransaction(Request $request): mixed
    {
        $webHookBody = $request->getContentTypeFormat() == 'json' ? $request->json()->all() : $request->request->all();
        $headers = $request->header();

        Log::info('[CONTROLLER][CONFIRM][START] Transaction confirm request', [
            'paymentMethod' => $request->query('payment-method'),
            'webHookBody' => $webHookBody,
            'header' => $headers
        ]);

        $paymentMethod = $request->query('payment-method');
        if (!is_string($paymentMethod)) {
            throw new InvalidArgumentException('Invalid or missing payment method');
        }

        $paymentSevice = $this->paymentMethodFactory->getInstanceByPaymentMethod(PaymentMethod::tryFrom($paymentMethod));
        $confirmTransactionDto = $paymentSevice->confirm($webHookBody, $headers);

        if ($confirmTransactionDto?->status !== null) {
            $transaction = Transaction::where('transaction_uuid', $confirmTransactionDto->remoteCode)->first();

            if ($transaction) {
                $transaction->update([
                    'status' => $confirmTransactionDto->status
                ]);

                Log::info('[CONTROLLER][CONFIRM][COMPLETED] Transaction confirm status updated', [
                    'paymentMethod' => $transaction->payment_method->value,
                    'transactionUuid' => $transaction->transaction_uuid,
                    'status' => $confirmTransactionDto->status->value
                ]);

                ProcessWebhookJob::dispatch($transaction);

                Log::info('[CONTROLLER][CONFIRM][NOTIFICATION]', [
                    'paymentMethod' => $transaction->payment_method->value,
                    'transactionUuid' => $transaction->transaction_uuid
                ]);
            }

            return response($confirmTransactionDto->responseBody, 200);
        } else {
            Log::error('[CONTROLLER][CONFIRM][ERROR] Invalid webhook payload or signature');
            return response()->json(['error' => 'Invalid webhook payload or signature.'], 500);
        }
    }

    #[OA\Post(
        path: "/api/refund-payment",
        tags: ["Transactions"],
        summary: "Refund payment",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["transactionUuid"],
                properties: [
                    new OA\Property(property: "transactionUuid", type: "string", example: "c6e2c816-3f5e-417b-9e91-a794223aa903"),
                ]
            )
        ),
        parameters: [
            new OA\Parameter(
                name: "signature",
                in: "header",
                required: true,
                description: "HMAC signature of the request body",
                schema: new OA\Schema(type: "string", example: "0798f264-f887-4102-b0ce-9d27e3076cc5")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Payment refund",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "string", example: "Refund"),
                        new OA\Property(property: "transactionUuid", type: "string", example: "c6e2c816-3f5e-417b-9e91-a794223aa903"),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Invalid transaction data provided",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Refund payment not completed."),
                    ]
                )
            ),
        ]
    )]
    public function refundPayment(MerchantRequest $request): JsonResponse
    {
        $refundBody = $request->all();
        $merchant = $request->merchant();
        $signature = $request->header('signature');
        $transactionUuid = $refundBody['transactionUuid'] ?? null;

        if (!$transactionUuid || !Str::isUuid($transactionUuid)) {
            Log::error('[CONTROLLER][REFUND][FAILED] Invalid or missing transaction UUID', [
                'transactionUuid' => $transactionUuid,
            ]);
            return response()->json([
                'error' => 'Invalid or missing transaction UUID'
            ], 422);
        }

        $transaction = Transaction::where('transaction_uuid', $transactionUuid)->first();

        if (!$transaction) {
            Log::error('[CONTROLLER][REFUND][ERROR] Transaction not found', [
                'transactionUuid' => $transactionUuid,
            ]);
            return response()->json(['error' => 'Transaction not found.'], 404);
        }

        $calculatedSignature = TransactionSignatureFacade::calculateSignature(
            $transaction->transaction_uuid,
            $merchant
        );

        if (!$signature || $signature !== $calculatedSignature) {
            Log::error('[CONTROLLER][REFUND][ERROR] Missing or invalid signature.', [
                'transactionUuid' => $transactionUuid,
                'signatureFromHeader' => $signature,
                'calculatedSignature' => $calculatedSignature
            ]);
            return response()->json(['error' => 'Missing or invalid signature.'], 400);
        }

        if (Gate::denies('refund', $transaction)) {
            Log::error('[CONTROLLER][REFUND][ERROR] Unauthorized to refund this transaction.');
            return response()->json(['error' => 'Unauthorized to refund this transaction.'], 403);
        }

        Log::info('[CONTROLLER][REFUND][START] Received refund payment request', [
            'paymentMethod' => $transaction->payment_method->value,
            'transactionUuid' => $transactionUuid,
        ]);

        if ($transaction->status === TransactionStatus::REFUND_SUCCESS) {
            Log::info('[CONTROLLER][REFUND][TRANSACTION][CHECK] Transaction has been successfully refunded.', [
                'refundCode' => $transaction->refund_code,
                'transactionUuid' => $transaction->transaction_uuid,
            ]);
            return response()->json(['error' => 'Transaction has been successfully refunded.'], 400);
        }

        if ($transaction->status === TransactionStatus::REFUND_PENDING) {
            Log::info('[CONTROLLER][REFUND][TRANSACTION][CHECK] Transaction refund is in progress.', [
                'refundCode' => $transaction->refund_code,
                'transactionUuid' => $transaction->transaction_uuid,
            ]);
            return response()->json(['error' => 'Transaction refund is in progress.'], 400);
        }

        $paymentService = $this->paymentMethodFactory->getInstanceByPaymentMethod($transaction->payment_method);

        try {
            $refundPaymentDto = $paymentService->refund($refundBody);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }

        if ($refundPaymentDto !== null && $refundPaymentDto->status === TransactionStatus::REFUND_PENDING) {
            $transaction->status = TransactionStatus::REFUND_PENDING;
            $transaction->save();

            Log::info('[CONTROLLER][REFUND][REFUND_PENDING] Transaction set REFUND_PENDING', [
                'paymentMethod' => $transaction->payment_method->value,
                'transactionUuid' => $transaction->transaction_uuid
            ]);

            ProcessWebhookJob::dispatch($transaction);

            Log::info('[CONTROLLER][REFUND][NOTIFICATION]', [
                'paymentMethod' => $transaction->payment_method->value,
                'transactionUuid' => $transaction->transaction_uuid
            ]);

            return response()->json(['success' => 'Refund', 'transactionUuid' => $transaction->transaction_uuid], 200);

        }
        return response()->json(['error' => 'Refund payment not completed.'], 500);
    }

    #[OA\Get(
        path: "/api/transaction/{uuid}/status",
        tags: ["Transactions"],
        summary: "Get transaction status by UUID",
        parameters: [

            new OA\Parameter(
                name: "uuid",
                in: "path",
                required: true,
                description: "Transaction UUID",
                schema: new OA\Schema(
                    type: "string",
                    example: "c6e2c816-3f5e-417b-9e91-a794223aa903"
                )
            ),
        ],
        responses: [

            new OA\Response(
                response: 200,
                description: "Transaction status",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(
                            property: "status",
                            type: "string",
                            example: "SUCCESS"
                        ),
                        new OA\Property(
                            property: "amount",
                            type: "number",
                            format: "float",
                            example: 199.99
                        ),
                        new OA\Property(
                            property: "currency",
                            type: "string",
                            example: "PLN"
                        ),
                        new OA\Property(
                            property: "paymentMethod",
                            type: "string",
                            example: "TPAY"
                        ),
                        new OA\Property(
                            property: "returnUrl",
                            type: "string",
                            format: "uri",
                            example: "https://test.payment-gate.pl/return-url"
                        ),
                    ]
                )
            ),

            new OA\Response(
                response: 404,
                description: "Transaction not found",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(
                            property: "message",
                            type: "string",
                            example: "Transaction not found"
                        ),
                    ]
                )
            ),
        ]
    )]
    public function getStatus(string $uuid): JsonResponse
    {
        $status = $this->paymentStatusService->getStatusByUuid($uuid);

        if (!$status) {
            return response()->json([
                'message' => 'Transaction not found',
            ], 404);
        }

        return response()->json($status);
    }
}