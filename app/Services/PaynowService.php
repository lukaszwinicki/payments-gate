<?php

namespace App\Services;

use App\Dtos\ConfirmTransactionDto;
use App\Dtos\CreateTransactionDto;
use App\Dtos\RefundPaymentDto;
use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Exceptions\UnexpectedStatusCodeException;
use App\Exceptions\UnsupportedCurrencyException;
use App\Models\Transaction;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use stdClass;

class PaynowService implements PaymentMethodInterface
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

        Log::info('[SERVICE][CREATE][PAYNOW][START] Starting create process', [
            'uuid' => $uuid,
            'amount' => $transactionBody['amount'],
            'email' => $transactionBody['email'],
            'name' => $transactionBody['name'],
            'paymentMethod' => $transactionBody['paymentMethod'] ?? null,
        ]);

        $paynowRequestBody = [
            'amount' => $transactionBody['amount'] * 100,
            'buyer' => [
                'email' => $transactionBody['email']
            ],
            'description' => $uuid,
            'externalId' => $uuid,
            'continueUrl' => config('app.returnUrl') . "/payment-status?transaction_uuid={$uuid}",
        ];
        
        $paynowPayload = json_encode($paynowRequestBody, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        try {
            $createTransaction = $this->client->request('POST', config('app.paynow.sandboxApiUrl') . '/payments', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Api-Key' => config('app.paynow.apiKey'),
                    'Signature' => $this->calculatedSignature($paynowPayload, $uuid),
                    'Idempotency-Key' => $uuid,
                ],
                'body' => $paynowPayload,
                'http_errors' => false
            ]);
        } catch (GuzzleException $e) {
            Log::error('[SERVICE][CREATE][PAYNOW][ERROR] API request failed', [
                'message' => $e->getMessage()
            ]);
            throw new \RuntimeException('The transaction could not be completed');
        }

        if ($createTransaction->getStatusCode() !== 201) {
            Log::error('[SERVICE][CREATE][PAYNOW][ERROR] Transaction creation failed - unexpected status code', [
                'statusCode' => $createTransaction->getStatusCode(),
                'responseBody' => $createTransaction->getBody()->getContents(),
            ]);
            return null;
        }

        $paynowResponseBody = json_decode($createTransaction->getBody()->getContents(), true);
        $createTransactionDto = new CreateTransactionDto(
            $paynowResponseBody['paymentId'],
            $uuid,
            $transactionBody['name'],
            $transactionBody['email'],
            $transactionBody['currency'],
            $transactionBody['amount'],
            $transactionBody['notificationUrl'],
            $transactionBody['returnUrl'],
            $paymentMethod,
            $paynowResponseBody['redirectUrl']
        );

        Log::info('[SERVICE][CREATE][PAYNOW][COMPLETED] Transaction created successfully', [
            'transactionUuid' => $uuid,
        ]);

        return $createTransactionDto;
    }

    public function confirm(array $webHookBody, array $headers): ?ConfirmTransactionDto
    {
        Log::info('[SERVICE][CONFIRM][PAYNOW][START] Starting transaction confirmation process');

        $signature = $headers['signature'][0];
        $calculatedSignatureFromWebhook = base64_encode(hash_hmac('sha256', json_encode($webHookBody), config('app.paynow.signatureKey'), true));

        if ($signature !== $calculatedSignatureFromWebhook) {
            Log::error('[SERVICE][CONFIRM][PAYNOW][ERROR] Signature check failed', [
                'receivedSignature' => $signature,
                'expectedSignature' => $calculatedSignatureFromWebhook,
                'webhookBody' => $webHookBody,
            ]);
            return null;
        }

        $remoteCode = $webHookBody['externalId'];
        $status = null;

        if ($webHookBody['status'] == 'CONFIRMED') {
            $status = TransactionStatus::SUCCESS;
            Log::info('[SERVICE][CONFIRM][PAYNOW][COMPLETED] Transaction is confirmed');
        }

        if (in_array($webHookBody['status'], ['NEW', 'PENDING'])) {
            $status = TransactionStatus::PENDING;
            Log::info('[SERVICE][CONFIRM][PAYNOW][PENDING] Transaction is pending');
        }

        if (in_array($webHookBody['status'], ['ERROR', 'REJECTED', 'EXPIRED'])) {
            $status = TransactionStatus::FAIL;
            Log::info('[SERVICE][CONFIRM][PAYNOW][FAIL] Transaction is failed');
        }

        return new ConfirmTransactionDto($status ?? TransactionStatus::FAIL, '', $remoteCode);
    }

    public function refund(array $refundBody): ?RefundPaymentDto
    {
        Log::info('[SERVICE][REFUND][PAYNOW][START] Starting refund process');

        $transaction = Transaction::where('transaction_uuid', $refundBody['transactionUuid'])->first();

        if ($transaction->status !== TransactionStatus::SUCCESS && $transaction->status !== TransactionStatus::REFUND_FAIL) {
            Log::error('[SERVICE][REFUND][PAYNOW][ERROR] Unexpected refund transaction status', [
                'status' => $transaction->status->value
            ]);
            return null;
        }

        try {
            $uuid = (string) Str::uuid();
            $paynowRequestBody = [
                'amount' => $transaction->amount * 100,
            ];

            $responseRefund = $this->client->request('POST', config('app.paynow.sandboxApiUrl') . '/payments/' . $transaction->transaction_id . '/refunds', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Api-Key' => config('app.paynow.apiKey'),
                    'Signature' => $this->calculatedSignature(json_encode($paynowRequestBody), $uuid),
                    'Idempotency-Key' => $uuid,
                ],
                'json' => $paynowRequestBody,
            ]);

            if ($responseRefund->getStatusCode() !== 201) {
                Log::error('[SERVICE][REFUND][PAYNOW][ERROR] Transaction refund failed - unexpected status code', [
                    'statusCode' => $responseRefund->getStatusCode(),
                    'responseBody' => $responseRefund->getBody()->getContents(),
                ]);
                throw new UnexpectedStatusCodeException('Unexpected status code: ' . $responseRefund->getStatusCode());
            }

        } catch (ClientException $e) {
            $responseClientException = $e->getResponse();
            $bodyClientException = $responseClientException?->getBody()?->getContents();

            $decoded = json_decode($bodyClientException, true);

            $errorMessage = $decoded['errors'][0]['message'] ?? 'Unknown error';
            $errorType = $decoded['errors'][0]['errorType'] ?? 'UNKNOWN';

            Log::error('[SERVICE][REFUND][PAYNOW][ERROR] Refund failed (ClientException)', [
                'statusCode' => $responseClientException->getStatusCode(),
                'errorType' => $errorType,
                'message' => $errorMessage,
                'rawBody' => $bodyClientException,
            ]);

            throw new UnexpectedStatusCodeException($errorMessage);
        } catch (RequestException $e) {
            Log::error('[SERVICE][REFUND][PAYNOW][ERROR] Refund failed (RequestException)', [
                'message' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Failed to refund due to request error');
        }

        $responseBodyRefund = json_decode($responseRefund->getBody()->getContents(), true);

        if ($responseBodyRefund['status'] == 'PENDING') {
            $transaction->update([
                'refund_code' => $responseBodyRefund['refundId']
            ]);

            Log::info('[SERVICE][REFUND][PAYNOW][COMPLETED] Transaction refund successfully', [
                'refundCode' => $responseBodyRefund['refundId']
            ]);

            return new RefundPaymentDto(TransactionStatus::REFUND_PENDING);
        }

        return null;
    }

    public function calculatedSignature(string $body, string $uuid): string
    {
        $apiKey = config('app.paynow.apiKey');
        $signatureKey = config('app.paynow.signatureKey');

        $signatureBody = [
            'headers' => [
                'Api-Key' => $apiKey,
                'Idempotency-Key' => $uuid
            ],
            'parameters' => new stdClass,
            'body' => $body
        ];
        return base64_encode(hash_hmac('sha256', json_encode($signatureBody, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $signatureKey, true));
    }

    public function isSupportCurrency(string $currency): bool
    {
        return match ($currency) {
            'PLN' => true,
            default => false,
        };
    }
}