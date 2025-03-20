<?php

namespace App\Services;

use App\Factory\Dtos\ConfirmTransactionDto;
use App\Factory\Dtos\CreateTransactionDto;
use App\Services\PaymentMethodInterface;
use App\Enums\TransactionStatus;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class TPayService implements PaymentMethodInterface
{

    private $client;
    public function __construct()
    {
        $this->client = new Client();
    }

    public function create(array $transactionBody): CreateTransactionDto
    {
        $accessToken = Cache::get('token');
        $uuid = Str::uuid();

        if(!Cache::has('token'))
        {
            $accessToken = $this->getToken();
        }

        $tpayRequestBody = [
            'amount' => $transactionBody['amount'],
            'description' =>  $uuid,
            'hiddenDescription' => $uuid,
            'payer' => [
                'email' => $transactionBody['email'],
                'name' => $transactionBody['name']
            ],
            'callbacks' => [
                'notification' => [
                    'url' => env('APP_URL').'/confirmTransaction?payment_method=' .$transactionBody['payment_method']
                ]
            ]
        ]; 

        $createTransaction = $this->client->request('POST', env('TPAY_OPEN_API_URL').'/transactions',[
            'headers' => [
                'authorization' => 'Bearer '. $accessToken 
            ],
            'json' => $tpayRequestBody
        ]);

        $tpayResponseBody = json_decode($createTransaction->getBody()->getContents(),true);
        
        $createTransactionDto = new CreateTransactionDto();
        $createTransactionDto->setTransactionId($tpayResponseBody['transactionId']);
        $createTransactionDto->setUuid($tpayResponseBody['hiddenDescription']);
        $createTransactionDto->setName($tpayResponseBody['payer']['name']);
        $createTransactionDto->setEmail($tpayResponseBody['payer']['email']);
        $createTransactionDto->setAmount($tpayResponseBody['amount']);
        $createTransactionDto->setCurrency($tpayResponseBody['currency']);
        $createTransactionDto->setLink($tpayResponseBody['transactionPaymentUrl']);

        return $createTransactionDto;
    } 

    public function confirm(string $webHookBody, array $headers): ConfirmTransactionDto
    {
        
        $jws = $headers['x-jws-signature'][0];
        $resultValidate = TPaySignatureValidator::confirm($webHookBody,$jws);
        $confirmTransactionDto = new ConfirmTransactionDto();

        if(!$resultValidate)
        {
            $confirmTransactionDto->setStatus(TransactionStatus::FAIL);
            return $confirmTransactionDto;
        }

        // Converts x-www-form-urlencode to array 
        parse_str(urldecode($webHookBody),$result);
        $json = json_encode($result);
        $tpayWebhookBody = json_decode($json ,true);

        $remoteCode = $tpayWebhookBody['tr_crc'];
        $completed = $tpayWebhookBody['tr_status'] == 'TRUE';

        $confirmTransactionDto->setRemotedCode($remoteCode);
        $confirmTransactionDto->setCompleted($completed);
        $confirmTransactionDto->setStatus(TransactionStatus::SUCCESS);
        $confirmTransactionDto->setResponseBody('TRUE');
        
        return $confirmTransactionDto;
    }

    public function getToken()
    {
        $tokenBody = [
            'client_id' => env('TPAY_TOKEN_CLIENT_ID'),
            'client_secret' => env('TPAY_TOKEN_CLIENT_SECRET')
        ];

        $getTokenResponse = $this->client->request('POST',env('TPAY_OPEN_API_URL').'/oauth/auth',[
            'json' => $tokenBody
        ]);

        $token = json_decode($getTokenResponse->getBody()->getContents(),true);
        Cache::put('token',$token['access_token'],120);

        return $token['access_token'];
    }

}