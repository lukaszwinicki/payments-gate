<?php

namespace App\Services;

use App\Dtos\ConfirmTransactionDto;
use App\Dtos\CreateTransactionDto;
use App\Dtos\RefundPaymentDto;
use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Exceptions\RefundNotSupportedException;
use App\Exceptions\UnsupportedCurrencyException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NodaService implements PaymentMethodInterface
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

        Log::info('[SERVICE][CREATE][NODA][START] Starting create process', [
            'uuid' => $uuid,
            'amount' => $transactionBody['amount'],
            'email' => $transactionBody['email'],
            'name' => $transactionBody['name'],
            'paymentMethod' => $transactionBody['paymentMethod'] ?? null,
        ]);

        $nodaRequestBody = [
            'amount' => $transactionBody['amount'],
            'currency' => $transactionBody['currency'],
            'webhookUrl' => config('app.noda.notificationUrl') . '/api/confirm-transaction?payment-method=' . $transactionBody['paymentMethod'],
            'returnUrl' => config('app.returnUrl') . "/payment-status?transaction_uuid={$uuid}",
            'paymentId' => $uuid,
            'description' => $uuid,
            'email' => $transactionBody['email'],
        ];

        try {
            $createTransaction = $this->client->request('POST', config('app.noda.sandboxApiUrl') . '/api/payments', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'text/json',
                    'x-api-key' => config('app.noda.apiKey')
                ],
                'json' => $nodaRequestBody,
                'http_errors' => false
            ]);
        } catch (GuzzleException $e) {
            Log::error('[SERVICE][CREATE][NODA][ERROR] API request failed', [
                'message' => $e->getMessage()
            ]);
            throw new \RuntimeException('The transaction could not be completed');
        }

        if ($createTransaction->getStatusCode() !== 200) {
            Log::error('[SERVICE][CREATE][NODA][ERROR] Transaction creation failed - unexpected status code', [
                'statusCode' => $createTransaction->getStatusCode(),
                'responseBody' => $createTransaction->getBody()->getContents(),
            ]);
            return null;
        }

        $nodaResponseBody = json_decode($createTransaction->getBody()->getContents(), true);
        $createTransactionDto = new CreateTransactionDto(
            $nodaResponseBody['id'],
            $uuid,
            $transactionBody['name'],
            $transactionBody['email'],
            $transactionBody['currency'],
            $transactionBody['amount'],
            $transactionBody['notificationUrl'],
            $transactionBody['returnUrl'],
            $paymentMethod,
            $nodaResponseBody['url']
        );

        Log::info('[SERVICE][CREATE][NODA][COMPLETED] Transaction created successfully', [
            'transactionUuid' => $uuid,
        ]);

        return $createTransactionDto;
    }

    public function confirm(array $webHookBody, array $headers): ?ConfirmTransactionDto
    {
        Log::info('[SERVICE][CONFIRM][NODA][START] Starting transaction confirmation process');

        $signature = $webHookBody['Signature'];
        $calculatedSignatureFromWebhook = hash('sha256', $webHookBody['PaymentId'] . $webHookBody['Status'] . config('app.noda.signetureKey'));

        if ($signature !== $calculatedSignatureFromWebhook) {
            Log::error('[SERVICE][CONFIRM][NODA][ERROR] Signature check failed', [
                'receivedSignature' => $signature,
                'expectedSignature' => $calculatedSignatureFromWebhook,
                'webhookBody' => $webHookBody,
            ]);
            return null;
        }

        $remonteCode = $webHookBody['MerchantPaymentId'];
        $status = null;

        if ($webHookBody['Status'] == 'Done') {
            $status = TransactionStatus::SUCCESS;
            Log::info('[SERVICE][CONFIRM][NODA][COMPLETED] Transaction is confirmed');
        }

        return new ConfirmTransactionDto($status ?? TransactionStatus::FAIL, '', $remonteCode);
    }

    public function refund(array $refund): RefundPaymentDto
    {
        Log::warning('[SERVICE][REFUND][NODA] Refund is not supported for this payment method.', [
            'paymentMethod' => 'NODA',
            'refundData' => $refund,
        ]);
        throw new RefundNotSupportedException('Refund is not supported for this payment method.');
    }

    public function isSupportCurrency(string $currency): bool
    {
        return match ($currency) {
            'USD' => true,
            'EUR' => true,
            default => false,
        };
    }
}