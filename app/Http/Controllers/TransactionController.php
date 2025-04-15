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

class TransactionController extends Controller
{
    public function __construct(private CreateTransactionValidatorService $validator)
    {
    }

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