<?php

namespace App\Helper;

use Illuminate\Support\Facades\Http;

class MocDocHelper
{
    public static function generateAuthHeaders($method, $path, $query = '', $body = '', $accessKey, $secretKey)
{
    $date = gmdate('D, d M Y H:i:s') . ' GMT';
    $host = "mocdoc.com";
    $contentType = "application/json"; // match actual request

    // Body SHA256 (Base64 encoded)
    $bodyHash = base64_encode(hash('sha256', $body, true));

    // Signed header string
    $signedHeaderValues = "{$date};{$host};{$bodyHash}";

    $pathAndQuery = $query ? "{$path}?{$query}" : $path;

    $stringToSign = strtoupper($method) . "\n" . $pathAndQuery . "\n" . $signedHeaderValues;

    $signature = base64_encode(
        hash_hmac(
            'sha256',
            $stringToSign,
            base64_decode($secretKey),
            true
        )
    );

    return [
        'Date'                  => $date,
        'Authorization'         => "MD {$accessKey}:{$signature}",
        'Content-Type'          => $contentType,
        'x-ms-content-sha256'   => $bodyHash,
        'Host'                  => $host,
    ];
}

}
