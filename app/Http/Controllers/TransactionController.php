<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Factory\PaymentMethodFactory;
use App\Jobs\ProcessWebhookJob;
use App\Models\Transaction;
use App\Services\CreateTransactionValidatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TransactionController extends Controller
{
    public function __construct(private CreateTransactionValidatorService $validator)
    {
    }

    #[OA\Post(
        path: "/api/create-transaction",
        tags: ["Transactions"],
        summary: "Create new transaction",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["amount", "email", "name", "payment_method", "notification_url"],
                properties: [
                    new OA\Property(property: "amount", type: "string", example: "12.34"),
                    new OA\Property(property: "email", type: "string", example: "jan.kowalski@gmail.com"),
                    new OA\Property(property: "name", type: "string", example: "Jan Kowalski"),
                    new OA\Property(property: "payment_method", type: "string", example: "TPAY"),
                    new OA\Property(property: "notification_url", type: "string", example: "https://test.payment-gate.pl/callback"),
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
                        new OA\Property(property: "transaction_uuid", type: "string", example: "c6e2c816-3f5e-417b-9e91-a794223aa903"),
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
        $transactionBodyRequestValidator = $this->validator->validate($transactionBody);

        if ($transactionBodyRequestValidator->fails()) {
            return response()->json(['error' => $transactionBodyRequestValidator->errors()], 422);
        }

        $paymentService = PaymentMethodFactory::getInstanceByPaymentMethod(PaymentMethod::tryFrom($transactionBody['payment_method']));
        $createTransactionDto = $paymentService->create($transactionBody);

        if ($createTransactionDto === null) {
            return response()->json(['error' => 'The transaction could not be completed'], 500);
        }

        $transaction = new Transaction();
        $transaction->transaction_uuid = $createTransactionDto->uuid;
        $transaction->transactions_id = $createTransactionDto->transactionId;
        $transaction->amount = $createTransactionDto->amount;
        $transaction->name = $createTransactionDto->name;
        $transaction->email = $createTransactionDto->email;
        $transaction->currency = $createTransactionDto->currency;
        $transaction->status = TransactionStatus::PENDING;
        $transaction->notification_url = $transactionBody['notification_url'];
        $transaction->payment_method = $transactionBody['payment_method'];
        $transaction->save();

        return response()->json(['link' => $createTransactionDto->link, 'transaction_uuid' => $transaction->transaction_uuid]);
    }

    public function confirmTransaction(Request $request): mixed
    {
        $webHookBody = $request->getContentTypeFormat() == 'json' ? $request->json()->all() : $request->request->all();
        $headers = $request->header();

        $paymentSevice = PaymentMethodFactory::getInstanceByPaymentMethod(PaymentMethod::tryFrom($request->query('payment_method')));
        $confirmTransactionDto = $paymentSevice->confirm($webHookBody, $headers);

        if ($confirmTransactionDto->status === TransactionStatus::SUCCESS) {
            Transaction::where('transaction_uuid', $confirmTransactionDto->remoteCode)->update([
                'status' => $confirmTransactionDto->completed ? TransactionStatus::SUCCESS : TransactionStatus::FAIL
            ]);
            $transaction = Transaction::where('transaction_uuid', $confirmTransactionDto->remoteCode)->first();

            if ($transaction) {
                ProcessWebhookJob::dispatch($transaction);
            }

            return response($confirmTransactionDto->responseBody, 200);
        } elseif ($confirmTransactionDto->status === TransactionStatus::REFUND) {
            return response()->json(['transaction_uuid' => $confirmTransactionDto->remoteCode], 200);
        } else {
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
                required: ["payment_method", "transactionUuid"],
                properties: [
                    new OA\Property(property: "payment_method", type: "string", example: "TPAY"),
                    new OA\Property(property: "transaction_uuid", type: "string", example: "c6e2c816-3f5e-417b-9e91-a794223aa903"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Payment refund",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "string", example: "Refund"),
                        new OA\Property(property: "transaction_uuid", type: "string", example: "c6e2c816-3f5e-417b-9e91-a794223aa903"),
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

        if (empty($refundBody['transactionUuid']) || !PaymentMethod::tryFrom($refundBody['payment_method'])) {
            return response()->json(['error' => 'Missing or invalid data.'], 400);
        }

        $paymentService = PaymentMethodFactory::getInstanceByPaymentMethod(PaymentMethod::tryFrom($refundBody['payment_method']));
        $refundPaymentDto = $paymentService->refund($refundBody);
        $transaction = Transaction::where('transaction_uuid', $refundBody['transactionUuid'])->first();

        if ($transaction && $refundPaymentDto !== null && $refundPaymentDto->status === TransactionStatus::REFUND) {
            $transaction->status = TransactionStatus::REFUND;
            $transaction->save();
            ProcessWebhookJob::dispatch($transaction);

        } else {
            return response()->json(['error' => 'Refund payment not completed.'], 500);
        }
        return response()->json(['success' => 'Refund', 'transaction_uuid' => $transaction->transaction_uuid], 200);
    }
}