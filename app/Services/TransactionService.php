<?php

namespace App\Services;

use App\Dtos\CreateTransactionDto;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Enums\TransactionStatus;
use App\Enums\PaymentMethod;
use App\Factory\PaymentMethodFactory;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    public function __construct(protected PaymentStatusService $paymentStatusService)
    {
    }

    public function createTransaction(array $transactionBody, string $apiKey): ?CreateTransactionDto
    {
        Log::info('[SERVICE][CREATE-TRANSACTION][START] Received create payment request', [
            'paymentMethod' => $transactionBody['paymentMethod'],
            'transactionBody' => $transactionBody,
            'apiKey' => $apiKey
        ]);

        $paymentService = PaymentMethodFactory::getInstanceByPaymentMethod(PaymentMethod::tryFrom($transactionBody['paymentMethod']));
        $createTransactionDto = $paymentService->create($transactionBody);

        if ($createTransactionDto === null) {
            Log::error('[SERVICE][CREATE-TRANSACTION][ERROR] Payment service returned null for transaction creation', [
                'paymentMethod' => $transactionBody['paymentMethod']
            ]);
            return null;
        }

        $merchantId = Merchant::where('api_key', $apiKey)->first();

        if ($merchantId === null) {
            Log::error('[SERVICE][CREATE-TRANSACTION][ERROR] MerchantId returned null');
            return null;
        }

        $transaction = new Transaction();
        $transaction->transaction_uuid = $createTransactionDto->uuid;
        $transaction->transaction_id = $createTransactionDto->transactionId;
        $transaction->merchant_id = $merchantId->id;
        $transaction->amount = $createTransactionDto->amount;
        $transaction->name = $createTransactionDto->name;
        $transaction->email = $createTransactionDto->email;
        $transaction->currency = $createTransactionDto->currency;
        $transaction->status = TransactionStatus::PENDING;
        $transaction->notification_url = $createTransactionDto->notificationUrl;
        $transaction->return_url = $createTransactionDto->returnUrl;
        $transaction->payment_method = $createTransactionDto->paymentMethod;

        if (!$transaction->save()) {
            Log::error('[SERVICE][CREATE-TRANSACTION][ERROR] Transaction not created', [
                'paymentMethod' => $transactionBody['paymentMethod']
            ]);
            return null;
        }

        Log::info('[SERVICE][CREATE-TRANSACTION][COMPLETED] Transaction is waiting for confirmation', [
            'paymentMethod' => $transactionBody['paymentMethod'],
            'transactionUuid' => $createTransactionDto->uuid,
        ]);

        return $createTransactionDto;
    }
}