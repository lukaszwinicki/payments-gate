<?php

namespace Tests\Unit;

use App\Services\PaynowRefundStatusService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;

class PaynowRefundStatusServiceTest extends TestCase
{
    public function test_get_refund_payment_status_success(): void
    {
        $uuid = 'mocked-uuid-12345';
        $refundCode = 'refund-code-12345';
        $mockedResponse = 'success';

        $mock = new MockHandler([
            new Response(200, [], json_encode(['status' => $mockedResponse])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $status = PaynowRefundStatusService::getRefundPaymentStatus($refundCode, $client, $uuid);
        $this->assertEquals('success', $status);
    }

    public function test_get_refund_payment_status_failed(): void
    {
        $uuid = 'mocked-uuid-12345';
        $refundCode = 'refund-code-12345';
        $mockedResponse = 'success';

        $mock = new MockHandler([
            new Response(400, [], json_encode(['status' => $mockedResponse])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        $status = PaynowRefundStatusService::getRefundPaymentStatus($refundCode, $client, $uuid);
        $this->assertEquals('Error',$status);
    }
}