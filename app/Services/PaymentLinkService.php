<?php

namespace App\Services;

use App\Dtos\CreatePaymentLinkDto;
use App\Dtos\PaymentLinkTransactionDto;
use App\Factories\PaymentLinkFactory;
use App\Models\Transaction;
use App\Models\Merchant;
use App\Models\PaymentLink;
use App\Services\TransactionService;
use DateTimeImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PaymentLinkService
{
    public function __construct(
        private TransactionService $createTransactionService,
        private PaymentLinkFactory $paymentLinkFactory
    ) {
    }

    public function createPaymentLink(array $paymentLinkBody, string $apiKey): ?CreatePaymentLinkDto
    {
        $uuid = (string) Str::uuid();
        $merchant = Merchant::where('api_key', $apiKey)->first();

        if (!$merchant) {
            Log::error('[SERVICE][CREATE][PAYMENT-LINK][ERROR] Merchant not found', [
                'apiKey' => $apiKey,
            ]);
            return null;
        }

        Log::info('[SERVICE][CREATE][PAYMENT-LINK][START] Starting create process', [
            'paymentLinkId' => $uuid,
            'amount' => $paymentLinkBody['amount'],
            'currency' => $paymentLinkBody['currency'],
            'notificationUrl' => $paymentLinkBody['notificationUrl'],
            'returnUrl' => $paymentLinkBody['returnUrl'],
            'expiresAt' => $paymentLinkBody['expiresAt']
        ]);

        $createPaymentLinkDto = new CreatePaymentLinkDto(
            $uuid,
            $paymentLinkBody['amount'],
            $paymentLinkBody['currency'],
            $paymentLinkBody['notificationUrl'],
            $paymentLinkBody['returnUrl'],
            new DateTimeImmutable($paymentLinkBody['expiresAt']),
            $merchant->id
        );

        $paymentLink = $this->paymentLinkFactory->make();
        $paymentLink->payment_link_id = $createPaymentLinkDto->paymentLinkId;
        $paymentLink->amount = $createPaymentLinkDto->amount;
        $paymentLink->currency = $createPaymentLinkDto->currency;
        $paymentLink->notification_url = $createPaymentLinkDto->notificationUrl;
        $paymentLink->return_url = $createPaymentLinkDto->returnUrl;
        $paymentLink->expires_at = Carbon::instance($createPaymentLinkDto->expiresAt);
        $paymentLink->merchant_id = $createPaymentLinkDto->merchantId;

        if (!$paymentLink->save()) {
            Log::error('[SERVICE][CREATE][PAYMENT-LINK][DB][ERROR] Failed to save payment link to the database', [
                'paymentLinkId' => $createPaymentLinkDto->paymentLinkId,
            ]);
            return null;
        }

        Log::info('[SERVICE][CREATE][PAYMENT-LINK][COMPLETED] Payment link created successfully', [
            'paymentLinkId' => $uuid,
        ]);

        return $createPaymentLinkDto;
    }

    public function createTransactionForPaymentLink(array $paymentLinkBody): ?PaymentLinkTransactionDto
    {
        if (!Str::isUuid($paymentLinkBody['paymentLinkId'] ?? '')) {
            Log::error('[SERVICE][CREATE][TRANSACTION-PAYMENT-LINK][ERROR] Invalid UUID', [
                'paymentLinkId' => $paymentLinkBody['paymentLinkId'] ?? 'NULL'
            ]);
            return null;
        }

        $paymentLinkData = PaymentLink::where('payment_link_id', $paymentLinkBody['paymentLinkId'])->first();

        if (!$paymentLinkData) {
            Log::error('[SERVICE][CREATE][TRANSACTION-PAYMENT-LINK][ERROR] PaymentLinkData not found', [
                'paymentLinkId' => $paymentLinkBody['paymentLinkId']
            ]);
            return null;
        }

        $merchant = Merchant::where('id', $paymentLinkData->merchant_id)->first();

        if (!$merchant) {
            Log::error('[SERVICE][CREATE][TRANSACTION-PAYMENT-LINK][ERROR] Merchant not found', [
                'merchantId' => $paymentLinkData->merchant_id
            ]);
            return null;
        }

        if ($paymentLinkData->transaction_id !== null) {
            Log::error('[SERVICE][CREATE][TRANSACTION-PAYMENT-LINK][ERROR] The payment from the link has already been created');
            return null;
        }

        Log::info('[SERVICE][CREATE][TRANSACTION-PAYMENT-LINK][START] Starting create process', [
            'paymentLinkId' => $paymentLinkBody['paymentLinkId']
        ]);

        $paymentLinkRequest = [
            'amount' => $paymentLinkData->amount,
            'email' => $paymentLinkBody['email'],
            'name' => $paymentLinkBody['fullname'],
            'currency' => $paymentLinkData->currency,
            'paymentMethod' => $paymentLinkBody['paymentMethod'],
            'notificationUrl' => $paymentLinkData->notification_url,
            'returnUrl' => $paymentLinkData->return_url
        ];

        $createTransactionDto = $this->createTransactionService->createTransaction($paymentLinkRequest, $merchant->api_key);

        if ($createTransactionDto === null) {
            Log::error('[SERVICE][CREATE][TRANSACTION-PAYMENT-LINK][ERROR] CreateTrasactionDto is null');
            return null;
        }

        $transactionId = Transaction::where('transaction_uuid', $createTransactionDto->uuid)->first();

        if ($transactionId) {
            $paymentLinkData->transaction_id = $transactionId->id;
            $paymentLinkData->save();
            Log::info('[SERVICE][CREATE][TRANSACTION-PAYMENT-LINK][DB] Transaction id was updated', [
                'paymentLinkId' => $paymentLinkBody['paymentLinkId']
            ]);
        }

        $paymentLinkTransactionDto = new PaymentLinkTransactionDto(
            $createTransactionDto->link,
            $createTransactionDto->uuid
        );

        Log::info('[SERVICE][CREATE][TRANSACTION-PAYMENT-LINK][COMPLETED] Transaction from payment link created successfully', [
            'paymentLink' => $createTransactionDto->link,
            'transactionUuid' => $createTransactionDto->uuid
        ]);

        return $paymentLinkTransactionDto;
    }
}