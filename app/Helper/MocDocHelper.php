<?php

namespace App\Helper;

use Illuminate\Support\Facades\Http;

class MocDocHelper
{
    public static function generateAuthHeaders($method, $path, $query = '', $body = '', $accessKey, $secretKey)
    {
        $date = gmdate('D, d M Y H:i:s') . ' GMT';

        $host = "mocdoc.com"; // Update if different
        $contentType = "application/x-www-form-urlencoded";

        // Body SHA256 (Base64 encoded)
        $bodyHash = base64_encode(hash('sha256', $body, true));

        // Required signed headers (semicolon-separated values)
        $signedHeaderValues = "{$date};{$host};{$bodyHash}";

        // Path + query
        $pathAndQuery = $query ? "{$path}?{$query}" : $path;

        // String-To-Sign
        $stringToSign =
            strtoupper($method) . "\n" .
            $pathAndQuery . "\n" .
            $signedHeaderValues;

        // Signature (base64 HMAC-SHA256)
        $signature = base64_encode(
            hash_hmac(
                'sha256',
                $stringToSign,
                base64_decode($secretKey),  // SECRET MUST BE BASE64–DECODED
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
