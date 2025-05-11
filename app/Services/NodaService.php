<?php

namespace App\Services;

use App\Dtos\ConfirmTransactionDto;
use App\Dtos\CreateTransactionDto;
use App\Dtos\RefundPaymentDto;
use App\Enums\TransactionStatus;
use App\Exceptions\RefundNotSupportedException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Str;

class NodaService implements PaymentMethodInterface
{
    public function __construct(public Client $client = new Client())
    {
    }

    public function create(array $transactionBody): ?CreateTransactionDto
    {
        $uuid = (string) Str::uuid();
        $nodaRequestBody = [
            'amount' => $transactionBody['amount'],
            'currency' => $transactionBody['currency'],
            'returnUrl' => config('app.noda.notificationUrl').'/api/confirm-transaction?payment-method=' . $transactionBody['paymentMethod'],
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
            throw new \RuntimeException('The transaction could not be completed');
        }

        if ($createTransaction->getStatusCode() !== 200) {
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
            $nodaResponseBody['url']
        );
        return $createTransactionDto;
    }

    public function confirm(array $webHookBody, array $headers): ?ConfirmTransactionDto
    {
        $signature = $webHookBody['Signature'];
        $calculatedSignatureFromWebhook = hash('sha256', $webHookBody['PaymentId'] . $webHookBody['Status'] . config('app.noda.signetureKey'));

        if ($signature !== $calculatedSignatureFromWebhook) {
            return null;
        }

        $remonteCode = $webHookBody['MerchantPaymentId'];
        $status = null;

        if($webHookBody['Status'] == 'Done'){
            $status = TransactionStatus::SUCCESS;
        }

        return new ConfirmTransactionDto($status ?? TransactionStatus::FAIL, '', $remonteCode);
    }

    public function refund(array $refund): RefundPaymentDto
    {
        throw new RefundNotSupportedException('Refund is not supported for this payment method.');
    }
}