<?php

namespace Tests\Unit;

use App\Services\TPaySignatureValidator;
use Mockery;
use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\Log;

class TPaySignatureValidatorTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    public function test_missing_jws_header()
    {
        $validator = $this->getMockBuilder(TPaySignatureValidator::class)
        ->onlyMethods([])  
        ->getMock();
        Log::shouldReceive('debug')->once()->with('FALSE - Missing JSW header');
        $result = $validator->confirm('', null);
        $this->assertFalse($result);
    }

    public function test_invalid_jws_header()
    {
        $validator = $this->getMockBuilder(TPaySignatureValidator::class)
        ->onlyMethods([])  
        ->getMock();
        Log::shouldReceive('debug')->once()->with('FALSE - Invalid JWS header');
        $result = $validator->confirm('', '.headers');
        $this->assertFalse($result);
    }

    public function test_invalid_jws_signature()
    {
        $validator = $this->getMockBuilder(TPaySignatureValidator::class)
        ->onlyMethods([])  
        ->getMock();
        Log::shouldReceive('debug')->once()->with('FALSE - Invalid JWS signature');
        $result = $validator->confirm('', 'header.test');
        $this->assertFalse($result);
    }

    public function test_missing_x5u_header()
    {
        $validator = $this->getMockBuilder(TPaySignatureValidator::class)
        ->onlyMethods([])  
        ->getMock();
        $jws = base64_encode('{"alg":"RS256"}');
        Log::shouldReceive('debug')->once()->with('FALSE - Missing x5u header');
        $result = $validator->confirm('', $jws.'.payload.test');
        $this->assertFalse($result);
    }

    public function test_wrong_x5u_prefix()
    {
        $validator = $this->getMockBuilder(TPaySignatureValidator::class)
        ->onlyMethods([])  
        ->getMock();

        $mock = Mockery::mock('alias:Illuminate\Support\Facades\Config');
        $mock->shouldReceive('get')
            ->with('app.tpay.gateway')
            ->andReturn('https://wrong.url');
        
        $jws = base64_encode('{"alg":"RS256","x5u":"https://secure.sandbox.tpay.com/x509/notifications-jws.pem"}');
        Log::shouldReceive('debug')->once()->with('FALSE - Wrong x5u url');
        $result = $validator->confirm('', $jws.'.payload.test');
        $this->assertFalse($result);
    }

    public function test_wrong_certificate()
    {
        $validator = $this->getMockBuilder(TPaySignatureValidator::class)
        ->onlyMethods(['getCertificate','getTrustedCertificate'])  
        ->getMock();

        $validator->expects($this->once())  
        ->method('getCertificate')
        ->willReturn('invalid-cert');

        $validator->expects($this->once())  
        ->method('getTrustedCertificate')
        ->willReturn(file_get_contents(__DIR__.'/Files/TPay/public_key.pem'));

        $mock = Mockery::mock('alias:Illuminate\Support\Facades\Config');
        $mock->shouldReceive('get')
            ->with('app.tpay.gateway')
            ->andReturn('https://secure.sandbox.tpay.com');

        $jws = base64_encode('{"alg":"RS256","x5u":"https://secure.sandbox.tpay.com/x509/notifications-jws.pem"}');
        Log::shouldReceive('debug')->once()->with('FALSE - Signing certificate is not signed by Tpay CA certificate');
        $result = $validator->confirm('', $jws.'.payload.test');
        $this->assertFalse($result);
    }

    public function test_invalid_jws_signature_with_correct_certs()
    {
        $validator = $this->getMockBuilder(TPaySignatureValidator::class)
        ->onlyMethods(['getCertificate','getTrustedCertificate'])  
        ->getMock();

        $validator->expects($this->once())  
        ->method('getCertificate')
        ->willReturn(file_get_contents(__DIR__.'/Files/TPay/certificate.pem'));

        $validator->expects($this->once())  
        ->method('getTrustedCertificate')
        ->willReturn(file_get_contents(__DIR__.'/Files/TPay/public_key.pem'));
        
        $mock = Mockery::mock('alias:Illuminate\Support\Facades\Config');
        $mock->shouldReceive('get')
            ->with('app.tpay.gateway')
            ->andReturn('https://secure.sandbox.tpay.com');

        $jws = base64_encode('{"alg":"RS256","x5u":"https://secure.sandbox.tpay.com/x509/notifications-jws.pem"}');
        Log::shouldReceive('debug')->once()->with('FALSE - Invalid JWS signature with correct certs');
        $result = $validator->confirm('qwerty', $jws.'.payload.signature');
        $this->assertFalse($result);
    }

    public function test_correct()
    {
        $validator = $this->getMockBuilder(TPaySignatureValidator::class)
        ->onlyMethods(['getCertificate','getTrustedCertificate'])  
        ->getMock();

        $validator->expects($this->once())  
        ->method('getCertificate')
        ->willReturn(file_get_contents(__DIR__.'/Files/TPay/certificate.pem'));

        $validator->expects($this->once())  
        ->method('getTrustedCertificate')
        ->willReturn(file_get_contents(__DIR__.'/Files/TPay/public_key.pem'));
        
        $mock = Mockery::mock('alias:Illuminate\Support\Facades\Config');
        $mock->shouldReceive('get')
            ->with('app.tpay.gateway')
            ->andReturn('https://secure.sandbox.tpay.com');

        $jws = 'eyJhbGciOiJSUzI1NiIsIng1dSI6Imh0dHBzOlwvXC9zZWN1cmUuc2FuZGJveC50cGF5LmNvbVwveDUwOVwvbm90aWZpY2F0aW9ucy1qd3MucGVtIn0..dD0eRj6fw7zXiPddrvZREWkfeUnh_rEdgp0a1Ocv2y6hXYEjC8a2ef1kK2FaITbDljbYbye-QK8YvK3SLweMdBT0fvzrlveCC45WRzo2HyhLu6hZXWL1BUXNktzyiqj3bO2xPB4seHOpN6RTkSvzRSpmmVVypcLdoVbrmILsKz5tuUS92q9BQQkaIniVIyccuSIotlZDXwHlVPD0tA2bBAuGkIuZqhL3Z0wWciZ1giMBB5KoxNiTyIcXoEYcY4PA0vdA4Tl5DhKKT4DZJNqGzxLyGuAlhjz-auYbdtvLSVxVNx9o1cMAXEQaxI8Bub-EmXx76ia51brZm45tlqIS3IyrGxYjtJRLtKrMYoPpciV0ll7CgCxXM-CudUzl9FPhb6w8Ih8_ivaHuFhxoPCl-bJ0fyru-Is4-nq-7kzVyCVgmtdYVTPKSjH8QqvBxxjzrnAJCaSstDOzhGJ56EyK2dSSY9M19OX4Qq8oP87x9Ri4lLGJcJpWBqZBsocsV4LvRA_gTc9KbDjbmZgKzFnQuntPoxK_m9tgmDcB15bv2rgN39BwbM_DYk_ZoZ3YklKSaw8gHYIyo8SCE1NuBN8WZhr1k9tZLnYAULAeEz9AdWu7MFSpb_Mope5yG6_RLHPS0QE3lVkctLHkOfCFLetyxaW72-fZvOT6Z6EcGsXFfPE';
        $webhookBody = 'id=401406&tr_id=TR-4H6K-2JWA0X&tr_date=2025-04-06+16%3A00%3A56&tr_crc=8fa97a92-c9f0-45ce-8b25-e1774d92e12a&tr_amount=10.00&tr_paid=10.00&tr_desc=8fa97a92-c9f0-45ce-8b25-e1774d92e12a&tr_status=TRUE&tr_error=none&tr_email=artur18%40wp.pl&test_mode=0&md5sum=2a42abaee702705cade0cd95ff838eb1';
        Log::shouldReceive('debug')->never();
        $result = $validator->confirm($webhookBody, $jws);
        $this->assertTrue($result);
    }

}
