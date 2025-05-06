<?php

namespace App\Services;

use App\Facades\TPaySignatureValidatorFacade;
use App\Dtos\ConfirmTransactionDto;
use App\Dtos\CreateTransactionDto;
use App\Dtos\RefundPaymentDto;
use App\Models\Transaction;
use App\Services\PaymentMethodInterface;
use App\Enums\TransactionStatus;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
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
        $tpayRequestBody = [
            'amount' => $transactionBody['amount'],
            'description' => $uuid,
            'hiddenDescription' => $uuid,
            'payer' => [
                'email' => $transactionBody['email'],
                'name' => $transactionBody['name']
            ],
            'callbacks' => [
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
            throw new \RuntimeException('The transaction could not be completed');
        }

        if ($createTransaction->getStatusCode() !== 200) {
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

        return $createTransactionDto;
    }

    public function confirm(array $webHookBody, array $headers): ?ConfirmTransactionDto
    {
        $jws = $headers['x-jws-signature'][0];
        $resultValidate = TPaySignatureValidatorFacade::confirm(http_build_query($webHookBody), $jws);

        if (!$resultValidate) {
            return null;
        }

        $remoteCode = $webHookBody['tr_crc'];
        $status = null;

        if ($webHookBody['tr_status'] == 'TRUE') {
            $status = TransactionStatus::SUCCESS;
        }

        if ($webHookBody['tr_status'] == 'CHARGEBACK') {
            $status = TransactionStatus::REFUND_SUCCESS;
        }

        return new ConfirmTransactionDto($status ?? TransactionStatus::FAIL, 'TRUE', $remoteCode);
    }

    public function refund(array $refundBody): ?RefundPaymentDto
    {
        $transaction = Transaction::where('transaction_uuid', $refundBody['transactionUuid'])->first();

        if (
            $transaction &&
            in_array($transaction->status, [
                TransactionStatus::REFUND_SUCCESS,
                TransactionStatus::REFUND_PENDING,
                TransactionStatus::REFUND_FAIL,
            ])
        )
            return null;

        try {
            $responseRefund = $this->client->request('POST', config('app.tpay.openApiUrl') . '/transactions/' . $transaction?->transactions_id . '/refunds', [
                'headers' => [
                    'authorization' => 'Bearer ' . $this->accessToken(),
                ],
                'http_errors' => false
            ]);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Failed to refund payment');
        }

        if ($responseRefund->getStatusCode() !== 200) {
            return null;
        }

        $responseBodyRefund = json_decode($responseRefund->getBody()->getContents(), true);

        if ($responseBodyRefund['result'] === 'success' && $responseBodyRefund['status'] === 'refund') {
            return new RefundPaymentDto(TransactionStatus::REFUND_PENDING);
        }

        return null;
    }

    public function getToken(): string
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
            throw new \RuntimeException('Failed to get token');
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
}