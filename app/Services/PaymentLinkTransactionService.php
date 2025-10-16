<?php

namespace App\Services;
use App\Dtos\PaymentLinkTransactionBodyDto;
use App\Models\Merchant;
use App\Models\PaymentLink;
use App\Models\Transaction;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentLinkTransactionService
{

    public function __construct(public Client $client = new Client())
    {
    }
    public function create(array $paymentLinkBody): ?PaymentLinkTransactionBodyDto
    {
        if (!Str::isUuid($paymentLinkBody['paymentLinkId'] ?? '')) {
            Log::error('[SERVICE][CREATE][TRANSACTION-PAYMENT-LINK][ERROR] Invalid UUID', [
                'paymentLinkId' => $paymentLinkBody['paymentLinkId'] ?? 'BRAK'
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

        $apiKey = Merchant::where('id', $paymentLinkData->merchant_id)->first();

        if (!$apiKey) {
            Log::error('[SERVICE][CREATE][TRANSACTION-PAYMENT-LINK][ERROR] Merchant not found', [
                'merchantId' => $paymentLinkData->merchant_id
            ]);
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

        try {
            $createPaymentLinkTransaction = $this->client->request('POST', config('app.paymentsGatewayBaseUrl') . '/api/create-transaction', [
                'headers' => [
                    'X-API-KEY' => $apiKey->api_key
                ],
                'json' => $paymentLinkRequest,
                'http_errors' => false
            ]);
        } catch (GuzzleException $e) {
            Log::error('[SERVICE][CREATE][TRANSACTION-PAYMENT-LINK][ERROR] API request failed', [
                'message' => $e->getMessage()
            ]);
            throw new \RuntimeException('The transaction could not be completed');
        }

        $paymentLinkResponseBody = json_decode($createPaymentLinkTransaction->getBody()->getContents(), true);
        $transactionId = Transaction::where('transaction_uuid', $paymentLinkResponseBody['transactionUuid'])->first();

        if ($transactionId) {
            $paymentLinkData->transaction_id = $transactionId->id;
            $paymentLinkData->save();
        }

        $paymentLinkTransactionBodyDto = new PaymentLinkTransactionBodyDto(
            $paymentLinkResponseBody['link'],
            transactionUuid: $paymentLinkResponseBody['transactionUuid']
        );

        return $paymentLinkTransactionBodyDto;
    }
}