<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Facades\TPaySignatureValidatorFacade;
use App\Dtos\ConfirmTransactionDto;
use App\Dtos\CreateTransactionDto;
use App\Dtos\RefundPaymentDto;
use App\Models\Transaction;
use App\Services\PaymentMethodInterface;
use App\Enums\TransactionStatus;
use App\Exceptions\UnsupportedCurrencyException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;


class TPayService implements PaymentMethodInterface
{
    public function __construct(public Client $client = new Client())
    {
    }

    public function create(array $transactionBody): ?CreateTransactionDto
    {
        $uuid = (string) Str::uuid();

        $currency = $transactionBody['currency'];
        $paymentMethod = PaymentMethod::from($transactionBody['paymentMethod']);

        if (!$this->isSupportCurrency($currency)) {
            Log::error("[SERVICE][CREATE][TPAY][ERROR] Currency {$currency} is not supported by {$paymentMethod->value}.", [
                'currency' => $currency,
                'paymentMethod' => $paymentMethod->value,
            ]);
            throw new UnsupportedCurrencyException("Currency {$currency} is not supported by {$paymentMethod->value}.");
        }

        Log::info('[SERVICE][CREATE][TPAY][START] Starting create process', [
            'uuid' => $uuid,
            'amount' => $transactionBody['amount'],
            'email' => $transactionBody['email'],
            'name' => $transactionBody['name'],
            'paymentMethod' => $transactionBody['paymentMethod'] ?? null,
        ]);

        $tpayRequestBody = [
            'amount' => $transactionBody['amount'],
            'description' => $uuid,
            'hiddenDescription' => $uuid,
            'payer' => [
                'email' => $transactionBody['email'],
                'name' => $transactionBody['name']
            ],
            'callbacks' => [
                'payerUrls' => [
                    'success' => config('app.returnUrl') . "payment-status?transaction_uuid={$uuid}",
                    'error' => config('app.returnUrl') . "payment-status?transaction_uuid={$uuid}",
                ],
                'notification' => [
                    'url' => config('app.url') . '/api/confirm-transaction?payment-method=' . $transactionBody['paymentMethod']
                ]
            ]
        ];

        try {
            $createTransaction = $this->client->request('POST', config('app.tpay.openApiUrl') . '/transactions', [
                'headers' => [
                    'authorization' => 'Bearer ' . $this->accessToken()
                ],
                'json' => $tpayRequestBody,
                'http_errors' => false
            ]);
        } catch (GuzzleException $e) {
            Log::error('[SERVICE][CREATE][TPAY][ERROR] API request failed', [
                'message' => $e->getMessage()
            ]);
            throw new \RuntimeException('The transaction could not be completed');
        }

        if ($createTransaction->getStatusCode() !== 200) {
            Log::error('[SERVICE][CREATE][TPAY][ERROR] Transaction creation failed - unexpected status code', [
                'statusCode' => $createTransaction->getStatusCode(),
                'responseBody' => $createTransaction->getBody()->getContents(),
            ]);
            return null;
        }

        $tpayResponseBody = json_decode($createTransaction->getBody()->getContents(), true);
        $createTransactionDto = new CreateTransactionDto(
            $tpayResponseBody['transactionId'],
            $uuid,
            $tpayResponseBody['payer']['name'],
            $tpayResponseBody['payer']['email'],
            $tpayResponseBody['currency'],
            $tpayResponseBody['amount'],
            $tpayResponseBody['transactionPaymentUrl']
        );

        Log::info('[SERVICE][CREATE][TPAY][COMPLETED] Transaction created successfully', [
            'transactionUuid' => $uuid,
        ]);

        return $createTransactionDto;
    }

    public function confirm(array $webHookBody, array $headers): ?ConfirmTransactionDto
    {
        Log::info('[SERVICE][CONFIRM][TPAY][START] Starting transaction confirmation process');

        $jws = $headers['x-jws-signature'][0];
        $resultValidate = TPaySignatureValidatorFacade::confirm(http_build_query($webHookBody), $jws);

        if (!$resultValidate) {
            Log::error('[SERVICE][CONFIRM][TPAY][ERROR] Webhook signature validation failed', [
                'webhookBody' => $webHookBody,
                'jws' => $jws,
            ]);
            return null;
        }

        $remoteCode = $webHookBody['tr_crc'];
        $status = null;

        if ($webHookBody['tr_status'] == 'TRUE') {
            $status = TransactionStatus::SUCCESS;
            Log::info('[SERVICE][CONFIRM][TPAY][COMPLETED] Transaction is confirmed');
        }

        if ($webHookBody['tr_status'] == 'CHARGEBACK') {
            $status = TransactionStatus::REFUND_SUCCESS;
            Log::info('[SERVICE][CONFIRM][TPAY][REFUND_SUCCESS][COMPLETED] Transaction is refunded');
        }

        return new ConfirmTransactionDto($status ?? TransactionStatus::FAIL, 'TRUE', $remoteCode);
    }

    public function refund(array $refundBody): ?RefundPaymentDto
    {
        Log::info('[SERVICE][REFUND][TPAY][START] Starting refund process');

        $transaction = Transaction::where('transaction_uuid', $refundBody['transactionUuid'])->first();

        if ($transaction->status !== TransactionStatus::SUCCESS && $transaction->status !== TransactionStatus::REFUND_FAIL) {
            Log::error('[SERVICE][REFUND][TPAY][ERROR] Unexpected refund transaction status', [
                'status' => $transaction->status->value
            ]);
            return null;
        }

        try {
            $responseRefund = $this->client->request('POST', config('app.tpay.openApiUrl') . '/transactions/' . $transaction?->transactions_id . '/refunds', [
                'headers' => [
                    'authorization' => 'Bearer ' . $this->accessToken(),
                ],
                'http_errors' => false
            ]);
        } catch (GuzzleException $e) {
            Log::error('[SERVICE][REFUND][TPAY][ERROR] API request failed', [
                'message' => $e->getMessage()
            ]);
            throw new \RuntimeException('Failed to refund payment');
        }

        if ($responseRefund->getStatusCode() !== 200) {
            Log::error('[SERVICE][REFUND][TPAY][ERROR] Transaction refund failed - unexpected status code', [
                'statusCode' => $responseRefund->getStatusCode(),
                'responseBody' => $responseRefund->getBody()->getContents(),
            ]);
            return null;
        }

        $responseBodyRefund = json_decode($responseRefund->getBody()->getContents(), true);

        if ($responseBodyRefund['result'] === 'success' && $responseBodyRefund['status'] === 'refund') {

            Log::info('[SERVICE][REFUND][TPAY][COMPLETED] Transaction refund successfully', [
                'status' => $responseBodyRefund['status'],
                'result' => $responseBodyRefund['result'],
                'transactionUuid' => $responseBodyRefund['transactionUuid'] ?? null,
            ]);
            return new RefundPaymentDto(TransactionStatus::REFUND_PENDING);
        }

        return null;
    }

    public function getToken(): ?string
    {
        $tokenBody = [
            'client_id' => config('app.tpay.tokenClientId'),
            'client_secret' => config('app.tpay.tokenClientSecret')
        ];

        try {
            $getTokenResponse = $this->client->request('POST', config('app.tpay.openApiUrl') . '/oauth/auth', [
                'json' => $tokenBody,
                'http_errors' => false
            ]);
        } catch (GuzzleException $e) {
            Log::error('[SERVICE][GET_TOKEN][TPAY][ERROR] API request failed', [
                'message' => $e->getMessage()
            ]);
            throw new \RuntimeException('Failed to get token');
        }

        if ($getTokenResponse->getStatusCode() !== 200) {
            Log::error('[SERVICE][GET_TOKEN][TPAY][ERROR] Unexpected status code', [
                'statusCode' => $getTokenResponse->getStatusCode(),
                'responseBody' => $getTokenResponse->getBody()->getContents(),
            ]);
            return null;
        }

        $token = json_decode($getTokenResponse->getBody()->getContents(), true);
        Cache::put('token', $token['access_token'], 120);
        return $token['access_token'];
    }

    private function accessToken(): string
    {
        $accessToken = Cache::get('token');
        if (!Cache::has('token')) {
            $accessToken = $this->getToken();
        }
        return $accessToken;
    }

    public function isSupportCurrency(string $currency): bool
    {
        return match ($currency) {
            'PLN' => true,
            default => false,
        };
    }
}