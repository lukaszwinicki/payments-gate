<?php

namespace App\Services;

use App\Facades\TPaySignatureValidatorFacade;
use App\Factory\Dtos\ConfirmTransactionDto;
use App\Factory\Dtos\CreateTransactionDto;
use App\Factory\Dtos\RefundPaymentDto;
use App\Models\Transaction;
use App\Services\PaymentMethodInterface;
use App\Enums\TransactionStatus;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class TPayService implements PaymentMethodInterface
{
    public function __construct(public ?Client $client = new Client())
    {
    }
    public function create(array $transactionBody): ?CreateTransactionDto
    {
        $uuid = Str::uuid();
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
                    'url' => config('app.url') . '/api/confirm-transaction?payment_method=' . $transactionBody['payment_method']
                ]
            ]
        ];

        $createTransaction = $this->client->request('POST', config('app.tpay.openApiUrl') . '/transactions', [
            'headers' => [
                'authorization' => 'Bearer ' . $this->accessToken()
            ],
            'json' => $tpayRequestBody,
            'http_errors' => false
        ]);

        if ($createTransaction->getStatusCode() === 500) {
            return null;
        }

        $tpayResponseBody = json_decode($createTransaction->getBody()->getContents(), true);
        $createTransactionDto = new CreateTransactionDto(
            $tpayResponseBody['transactionId'],
            $tpayResponseBody['hiddenDescription'],
            $tpayResponseBody['payer']['name'],
            $tpayResponseBody['payer']['email'],
            $tpayResponseBody['currency'],
            $tpayResponseBody['amount'],
            $tpayResponseBody['transactionPaymentUrl']
        );

        return $createTransactionDto;
    }

    public function confirm(string $webHookBody, array $headers): ConfirmTransactionDto
    {
        $jws = $headers['x-jws-signature'][0];
        $resultValidate = TPaySignatureValidatorFacade::confirm($webHookBody, $jws);

        if (!$resultValidate) {
            return new ConfirmTransactionDto(TransactionStatus::FAIL);
        }

        // Converts x-www-form-urlencode to array  
        parse_str(urldecode($webHookBody), $result);
        $json = json_encode($result);
        $tpayWebhookBody = json_decode($json, true);
        $remoteCode = $tpayWebhookBody['tr_crc'];
        $completed = false;
        $status = null;

        if ($tpayWebhookBody['tr_status'] == 'TRUE') {
            $completed = $tpayWebhookBody['tr_status'] == 'TRUE';
            $status = TransactionStatus::SUCCESS;
        }

        if ($tpayWebhookBody['tr_status'] == 'CHARGEBACK') {
            $completed = $tpayWebhookBody['tr_status'] == 'CHARGEBACK';
            $status = TransactionStatus::REFUND;
        }

        return new ConfirmTransactionDto($status, 'TRUE', $remoteCode, $completed);
    }


    public function refund(string $transactionUuid): ?RefundPaymentDto
    {
        $transaction = Transaction::where('transaction_uuid', $transactionUuid)->first();
        if ($transaction->status === TransactionStatus::REFUND) {
            return null;
        } 

        $responseRefund = $this->client->request('POST', config('app.tpay.openApiUrl') . '/transactions/' . $transaction->transactions_id . '/refunds', [
            'headers' => [
                'authorization' => 'Bearer ' . $this->accessToken()
            ],
            'http_errors' => false
        ]);

        if ($responseRefund->getStatusCode() === 500) {
            return null;
        }

        $responseBodyRefund = json_decode($responseRefund->getBody()->getContents(), true);
        if ($responseRefund->getStatusCode() == 200 && $responseBodyRefund['result'] === 'success' && $responseBodyRefund['status'] === 'refund') {
            return new RefundPaymentDto(TransactionStatus::REFUND);
        }

        return null;
    }

    public function getToken()
    {
        $tokenBody = [
            'client_id' => config('app.tpay.tokenClientId'),
            'client_secret' => config('app.tpay.tokenClientSecret')
        ];

        $getTokenResponse = $this->client->request('POST', config('app.tpay.openApiUrl') . '/oauth/auth', [
            'json' => $tokenBody,
            'http_errors' => false
        ]);

        $token = json_decode($getTokenResponse->getBody()->getContents(), true);
        Cache::put('token', $token['access_token'], 120);

        return $token['access_token'];
    }

    private function accessToken()
    {
        $accessToken = Cache::get('token');
        if (!Cache::has('token')) {
            $accessToken = $this->getToken();
        }
        return $accessToken;
    }
}