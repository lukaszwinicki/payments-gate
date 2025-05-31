<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Factory\PaymentMethodFactory;
use App\Jobs\ProcessWebhookJob;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Services\CreateTransactionValidatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class TransactionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private CreateTransactionValidatorService $validator)
    {
    }

    #[OA\Post(
        path: "/api/create-transaction",
        tags: ["Transactions"],
        summary: "Create new transaction",
        description: "Obsługiwane metody płatności: TPAY, PAYNOW, NODA.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["amount", "email", "name", "paymentMethod", "notificationUrl"],
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

    public function createTransaction(Request $request): JsonResponse
    {
        $transactionBody = $request->all();
        $apiKeyHeader = $request->header();

        Log::info('[CONTROLLER][CREATE][START] Received create payment request', [
            'paymentMethod' => $transactionBody['paymentMethod'],
            'transactionBody' => $transactionBody,
            'apiKeyHeader' => $apiKeyHeader
        ]);

        $transactionBodyRequestValidator = $this->validator->validate($transactionBody);

        if ($transactionBodyRequestValidator->fails()) {
            Log::error('[CONTROLLER][CREATE][VALIDATION][FAIL]', [
                'errors' => $transactionBodyRequestValidator->errors()->toArray()
            ]);
            return response()->json(['error' => $transactionBodyRequestValidator->errors()], 422);
        }

        $paymentService = PaymentMethodFactory::getInstanceByPaymentMethod(PaymentMethod::tryFrom($transactionBody['paymentMethod']));

        try {
            $createTransactionDto = $paymentService->create($transactionBody);
        } catch (\App\Exceptions\UnsupportedCurrencyException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        if ($createTransactionDto === null) {
            Log::error('[CONTROLLER][CREATE][ERROR] Payment service returned null for transaction creation', [
                'paymentMethod' => $transactionBody['paymentMethod']
            ]);
            return response()->json(['error' => 'The transaction could not be completed'], 500);
        }

        $merchantId = Merchant::where('api_key', $apiKeyHeader['x-api-key'][0])->first();

        $transaction = new Transaction();
        $transaction->transaction_uuid = $createTransactionDto->uuid;
        $transaction->transactions_id = $createTransactionDto->transactionId;
        $transaction->merchant_id = $merchantId->id;
        $transaction->amount = $createTransactionDto->amount;
        $transaction->name = $createTransactionDto->name;
        $transaction->email = $createTransactionDto->email;
        $transaction->currency = $createTransactionDto->currency;
        $transaction->status = TransactionStatus::PENDING;
        $transaction->notification_url = $transactionBody['notificationUrl'];
        $transaction->payment_method = $transactionBody['paymentMethod'];
        $transaction->save();

        Log::info('[CONTROLLER][CREATE][COMPLETED] Transaction is waiting for confirmation', [
            'paymentMethod' => $transactionBody['paymentMethod'],
            'transactionUuid' => $transaction->transaction_uuid,
        ]);

        return response()->json(['link' => $createTransactionDto->link, 'transactionUuid' => $transaction->transaction_uuid]);
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

        $paymentSevice = PaymentMethodFactory::getInstanceByPaymentMethod(PaymentMethod::tryFrom($request->query('payment-method')));
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
    public function refundPayment(Request $request): JsonResponse
    {
        $refundBody = $request->all();
        $headers = $request->header();
        $transaction = Transaction::where('transaction_uuid', $refundBody['transactionUuid'])->first();

        if (empty($refundBody['transactionUuid']) || !$transaction) {
            Log::error('[CONTROLLER][REFUND][ERROR] Missing transactionUuid in request or transaction not found', [
                'transactionUuid' => $refundBody['transactionUuid'],
            ]);
            return response()->json(['error' => 'Missing or invalid data.'], 400);
        }

        if ($headers['signature'][0] !== $this->calculateSignature($transaction)) {
            Log::error('[CONTROLLER][REFUND][ERROR] Missing or invalid signature.', [
                'transactionUuid' => $refundBody['transactionUuid'],
                'signatureFromHeader' => $headers['signature'][0],
                'calculatedSignature' => $this->calculateSignature($transaction)
            ]);
            return response()->json(['error' => 'Missing or invalid signature.'], 400);
        }

        if (Gate::denies('refund', $transaction)) {
            Log::error('[CONTROLLER][REFUND][ERROR] Unauthorized to refund this transaction.');
            return response()->json(['error' => 'Unauthorized to refund this transaction.'], 403);
        }

        Log::info('[CONTROLLER][REFUND][START] Received refund payment request', [
            'paymentMethod' => $transaction->payment_method->value,
            'transactionUuid' => $refundBody['transactionUuid'],
        ]);

        $paymentService = PaymentMethodFactory::getInstanceByPaymentMethod($transaction->payment_method);
        $refundPaymentDto = $paymentService->refund($refundBody);

        if ($transaction && $refundPaymentDto !== null && $refundPaymentDto->status === TransactionStatus::REFUND_PENDING) {
            $transaction->status = TransactionStatus::REFUND_PENDING;
            $transaction->save();

            Log::info('[CONTROLLER][REFUND][REFUND_PENDING] Transaction set REFUND_PENDING', [
                'paymnetMethod' => $transaction->payment_method->value,
                'transactionUuid' => $transaction->transaction_uuid
            ]);

            ProcessWebhookJob::dispatch($transaction);

            Log::info('[CONTROLLER][REFUND][NOTIFICATION]', [
                'paymnetMethod' => $transaction->payment_method->value,
                'transactionUuid' => $transaction->transaction_uuid
            ]);
        } else {
            Log::error('[CONTORLLER][REFUND][ERROR] Refund payment not completed', [
                'paymnetMethod' => $transaction->payment_method->value,
                'transactionUuid' => $transaction->transaction_uuid,
            ]);
            return response()->json(['error' => 'Refund payment not completed.'], 500);
        }

        Log::info('[CONTROLLER][REFUND][COMPLETED] Transaction is waiting for confirmation', [
            'paymnetMethod' => $transaction->payment_method->value,
            'transactionUuid' => $transaction->transaction_uuid,
        ]);

        return response()->json(['success' => 'Refund', 'transactionUuid' => $transaction->transaction_uuid], 200);
    }

    public function calculateSignature(Transaction $transaction): string
    {
        $merchantSecretKey = Merchant::where('id', $transaction->merchant_id)->first();
        return hash_hmac('sha256', $transaction->transaction_uuid . $transaction->payment_method->value, $merchantSecretKey->secret_key);
    }
}