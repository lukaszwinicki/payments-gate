<?php

namespace App\Services;

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
    public function __construct(public ?Client $client = new Client()) {}

    public function create(array $transactionBody): CreateTransactionDto
    {   
        $uuid = Str::uuid();
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
                    'url' => env('APP_URL').'/api/confirm-transaction?payment_method=' .$transactionBody['payment_method']
                ]
            ]
        ]; 

        $createTransaction = $this->client->request('POST', env('TPAY_OPEN_API_URL').'/transactions',[
            'headers' => [
                'authorization' => 'Bearer '. $this->accessToken()
            ],
            'json' => $tpayRequestBody
        ]);
        
        $tpayResponseBody = json_decode($createTransaction->getBody()->getContents(),true);
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
        $resultValidate = (new TPaySignatureValidator)->confirm($webHookBody,$jws);
        
        if(!$resultValidate)
        {
            return new ConfirmTransactionDto(TransactionStatus::FAIL);
        }

        // Converts x-www-form-urlencode to array 
        parse_str(urldecode($webHookBody),$result);
        $json = json_encode($result);
        $tpayWebhookBody = json_decode($json ,true);
        $remoteCode = $tpayWebhookBody['tr_crc'];
        $completed = false;
        $status = null;
        
        if($tpayWebhookBody['tr_status'] == 'TRUE')
        {
            $completed = $tpayWebhookBody['tr_status'] == 'TRUE';
            $status = TransactionStatus::SUCCESS;
        }

        if($tpayWebhookBody['tr_status'] == 'CHARGEBACK')
        {
            $completed = $tpayWebhookBody['tr_status'] == 'CHARGEBACK';
            $status = TransactionStatus::REFUND;
        }
        
        return new ConfirmTransactionDto($status, 'TRUE', $remoteCode, $completed);
    }

    public function refund(string $transactionUuid): RefundPaymentDto
    {
        $transactionId = Transaction::where('transaction_uuid',$transactionUuid)->first();
        $responseRefund = $this->client->request('POST', env('TPAY_OPEN_API_URL').'/transactions/'.$transactionId['transactions_id'].'/refunds',[
            'headers' => [
                'authorization' => 'Bearer ' . $this->accessToken()
            ]
        ]);
    
        $responseBodyRefund = json_decode($responseRefund->getBody()->getContents(),true);
        $refundPaymentDto = new RefundPaymentDto();

        if($responseRefund->getStatusCode() == 200 && $responseBodyRefund['result'] === 'success' && $responseBodyRefund['status'] === 'refund')
        {
            $refundPaymentDto->status = TransactionStatus::REFUND;
        }
        
        return $refundPaymentDto;
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

    private function accessToken()
    {
        $accessToken = Cache::get('token');

        if(!Cache::has('token'))
        {
            $accessToken = $this->getToken();
        }

        return $accessToken;
    }

}