<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Ramsey\Uuid\Uuid;
use stdClass;

class PaynowRefundStatusService
{
    public static function getRefundPaymentStatus(string $refundCode, Client $client = null, $uuid = null): ?string
    {
        $uuid = $uuid ?? Uuid::uuid4()->toString();
        $client = $client ?? new Client();
        $signatureKey = config('app.paynow.signatureKey');

        $signatureBody = [
            'headers' => [
                'Api-Key' => config('app.paynow.apiKey'),
                'Idempotency-Key' => $uuid
            ],
            'parameters' => new stdClass,
            'body' => ''
        ];

        $signature = base64_encode(hash_hmac('sha256', json_encode($signatureBody), $signatureKey, true));

        try {
            $getStatus = $client->request('GET', config('app.paynow.sandboxApiUrl') . '/refunds/' . $refundCode . '/status', [

                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Api-Key' => config('app.paynow.apiKey'),
                    'Signature' => $signature,
                    'Idempotency-Key' => $uuid,
                ],
                'http_errors' => false
            ]);
        } catch (GuzzleException) {
            throw new \RuntimeException('Failed to fetch transaction status');
        }

        if ($getStatus->getStatusCode() !== 200) {
            return null;
        }

        $responseStatus = json_decode($getStatus->getBody()->getContents(), true);
        return $responseStatus['status'];
    }
}