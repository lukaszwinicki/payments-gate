<?php

namespace App\Services;
use App\Factory\Dtos\CreateTransactionDto;
use App\Services\PaymentMethodInterface;
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

    public function create(array $tpayResponseBody): CreateTransactionDto
    {
        $accessToken = Cache::get('token');
        $uuid = Str::uuid();

        if(!Cache::has('token'))
        {
            $accessToken = $this->getToken();
        }

        $tpayRequestBody = [
            'amount' => $tpayResponseBody['amount'],
            'description' =>  $uuid,
            'hiddenDescription' => $uuid,
            'payer' => [
                'email' => $tpayResponseBody['email'],
                'name' => $tpayResponseBody['name']
            ],
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