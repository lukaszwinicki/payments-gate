<?php

namespace App\Services;

use App\Dtos\ConfirmTransactionDto;
use App\Dtos\CreateTransactionDto;
use App\Dtos\RefundPaymentDto;
use App\Enums\TransactionStatus;
use App\Models\Transaction;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
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
        $paynowRequestBody = [
            'amount' => $transactionBody['amount'] * 100,
            'buyer' => [
                'email' => $transactionBody['email']
            ],
            'description' => $uuid,
            'externalId' => $uuid,
        ];

        try {
            $createTransaction = $this->client->request('POST', config('app.paynow.sandboxApiUrl') . '/payments', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Api-Key' => config('app.paynow.apiKey'),
                    'Signature' => $this->calculatedSignature($paynowRequestBody, $uuid),
                    'Idempotency-Key' => $uuid,
                ],
                'json' => $paynowRequestBody,
                'http_errors' => false
            ]);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('The transaction could not be completed');
        }

        if ($createTransaction->getStatusCode() !== 201) {
            return null;
        }

        $paynowResponsebody = json_decode($createTransaction->getBody()->getContents(), true);
        $createTransactionDto = new CreateTransactionDto(
            $paynowResponsebody['paymentId'],
            $uuid,
            $transactionBody['name'],
            $transactionBody['email'],
            $transactionBody['currency'],
            $transactionBody['amount'],
            $paynowResponsebody['redirectUrl']
        );
        return $createTransactionDto;
    }

    public function confirm(array $webHookBody, array $headers): ?ConfirmTransactionDto
    {
        $signature = $headers['signature'][0];
        $calculatedSignatureFromWebhook = base64_encode(hash_hmac('sha256', json_encode($webHookBody), config('app.paynow.signatureKey'), true));

        if ($signature !== $calculatedSignatureFromWebhook) {
            return null;
        }
        
        $remoteCode = $webHookBody['externalId'];
        $status = null;
        
        if ($webHookBody['status'] == 'CONFIRMED') {
            $status = TransactionStatus::SUCCESS;
        }

        if (in_array($webHookBody['status'], ['NEW', 'PENDING'])) {
            $status = TransactionStatus::PENDING;
        }

        if (in_array($webHookBody['status'], ['ERROR', 'REJECTED', 'EXPIRED'])) {
            $status = TransactionStatus::FAIL;
        }

        return new ConfirmTransactionDto($status ?? TransactionStatus::FAIL, '', $remoteCode);
    }

    public function refund(array $refundBody): ?RefundPaymentDto
    {
        $transaction = Transaction::where('transaction_uuid', $refundBody['transactionUuid'])->first();
      
        if ($transaction->status !== TransactionStatus::SUCCESS && $transaction->status !== TransactionStatus::REFUND_FAIL) {
            return null;
        }

        try {
            $uuid = (string) Str::uuid();
            $paynowRequestBody = [
                'amount' => $transaction->amount * 100,
            ];

            $responseRefund = $this->client->request('POST', config('app.paynow.sandboxApiUrl') . '/payments/' . $transaction->transactions_id . '/refunds', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Api-Key' => config('app.paynow.apiKey'),
                    'Signature' => $this->calculatedSignature($paynowRequestBody, $uuid),
                    'Idempotency-Key' => $uuid,
                ],
                'json' => $paynowRequestBody,
                'http_errors' => false
            ]);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Failed to refund payment');
        }

        if ($responseRefund->getStatusCode() !== 201) {
            return null;
        }

        $responseBodyRefund = json_decode($responseRefund->getBody()->getContents(), true);

        if ($responseBodyRefund['status'] == 'PENDING') {
            $transaction->update([
                'refund_code' => $responseBodyRefund['refundId']
            ]);
            return new RefundPaymentDto(TransactionStatus::REFUND_PENDING);
        }

        return null;
    }

    public function calculatedSignature(array $body, string $uuid): string
    {
        $apiKey = config('app.paynow.apiKey');
        $signatureKey = config('app.paynow.signatureKey');

        $signatureBody = [
            'headers' => [
                'Api-Key' => $apiKey,
                'Idempotency-Key' => $uuid
            ],
            'parameters' => new stdClass,
            'body' => json_encode($body)
        ];

        return base64_encode(hash_hmac('sha256', json_encode($signatureBody), $signatureKey, true));
    }
}