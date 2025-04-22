<?php
namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class TPaySignatureValidator
{
    public function confirm(string $webHookBody, ?string $jws): bool
    {

        if (null === $jws) {
            Log::debug("FALSE - Missing JSW header");
            return false;
        }

        // Extract JWS header properties
        $jwsData = explode(".", $jws);
        $headers = isset($jwsData[0]) ? $jwsData[0] : null;
        $signature = isset($jwsData[2]) ? $jwsData[2] : null;

        if (empty($headers)) {
            Log::debug("FALSE - Invalid JWS header");
            return false;
        }

        if (null === $signature) {
            Log::debug("FALSE - Invalid JWS signature");
            return false;
        }

        // Decode received headers json string from base64_url_safe
        $headersJson = base64_decode(strtr($headers, "-_", "+/"));
        // Get x5u header from headers json
        $headersData = json_decode($headersJson, true);
        $x5u = isset($headersData["x5u"]) ? $headersData["x5u"] : null;


        if (null === $x5u) {
            Log::debug("FALSE - Missing x5u header");
            return false;
        }

        // Check certificate url
        $prefix = Config::get('app.tpay.gateway');

        if (substr($x5u, 0, strlen($prefix)) !== $prefix) {
            Log::debug("FALSE - Wrong x5u url");
            return false;
        }

        // Get JWS sign certificate from x5u uri
        $certificate = $this->getCertificate($x5u);

        // Verify JWS sign certificate with Tpay CA certificate
        // Get Tpay CA certificate to verify JWS sign certificate. CA certificate be cached locally.
        $trusted = $this->getTrustedCertificate();

        // in php7.4+ with ext-openssl you can use openssl_x509_verify
        if (1 !== openssl_x509_verify($certificate, $trusted)) {
            Log::debug("FALSE - Signing certificate is not signed by Tpay CA certificate");
            return false;
        }

        // Encode body to base46_url_safe
        $payload = str_replace("=", "", strtr(base64_encode($webHookBody), "+/", "-_"));
        // Decode received signature from base64_url_safe
        $decodedSignature = base64_decode(strtr($signature, "-_", "+/"));
        // Verify RFC 7515: JSON Web Signature (JWS) with ext-openssl
        // Get public key from certificate
        $publicKey = openssl_pkey_get_public($certificate);
        if (
            1 !==
            openssl_verify(
                $headers . "." . $payload,
                $decodedSignature,
                $publicKey,
                OPENSSL_ALGO_SHA256
            )
        ) {
            Log::debug("FALSE - Invalid JWS signature with correct certs");
            return false;
        }
        return true;
    }

    public function getCertificate(string $x5u): string
    {
        return file_get_contents($x5u);
    }

    public function getTrustedCertificate(): string
    {
        return file_get_contents(config('app.tpay.gateway') . "/x509/tpay-jws-root.pem");
    }
}