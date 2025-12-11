<?php

namespace App\Http\Controllers;

use App\Services\MocDocService;

class MocDocController extends Controller
{
     private $accessKey = "399e911b4a2a28f5";
    private $secretKey = "4bfb224da2d03660188a65027dd8265b"; // HEX or raw string from MocDoc

    public function doctors($entityKey)
    {
        $service = new MocDocService();

       
        // Optional entitylocation
        $entityLocation = request()->get('entitylocation');

        $result = $service->getDoctors($entityKey, $entityLocation);

        return response()->json($result);
    }
    /**
     * Generate HMAC headers for a request
     */
    private function mocdocHmacHeaders($url, $method = 'POST', $body = '')
    {
        $contentType = "application/x-www-form-urlencoded";
        $date =    "Wed, ". now() . " IST";

        // MD5 hash of raw body, base64 encoded
        $contentMd5 = $body !== "" ? base64_encode(md5($body, true)) : "";

        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'];

        $toSign = $method . "\n" .
                  $contentMd5 . "\n" .
                  $contentType . "\n" .
                  $date . "\n\n" .
                  $path;

        // HMAC-SHA1 signature
        $hmac = base64_encode(hash_hmac('sha1', $toSign, $this->secretKey, true));

        return [
            "Content-Type: $contentType",
            "Date: $date",
            "Authorization: MD {$this->accessKey}:$hmac"
        ];
    }

    /**
     * Get doctors list
     */
 public function sendHmacRequest($entity = 'jv-medi-clinic', $extraData = [])
{
    $url = "https://mocdoc.com/api/get/dr/" . $entity;

    // No POST body for this request (empty)
    $body = "";

    // Generate HMAC headers
    $headers = $this->mocdocHmacHeaders($url, 'POST', $body);

    // ----------------------------------------
    // BEAUTIFIED DEBUG OUTPUT
    // ----------------------------------------
    echo "\n================= MOCDOC REQUEST DEBUG =================\n";
    echo "URL: $url\n";
    echo "POST Body: (empty)\n\n";

    echo "HEADERS:\n";
    foreach ($headers as $h) {
        echo "  → $h\n";
    }
    echo "--------------------------------------------------------\n\n";

        // Form-encoded POST body
    $postDataArray = [
        'entitylocation' => 'location1',

    ];

    $body = http_build_query($postDataArray);
    // cURL request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // ----------------------------------------
    // BEAUTIFIED RESPONSE
    // ----------------------------------------
    echo "============== MOCDOC RESPONSE DEBUG ==================\n";
    echo "HTTP Code: $httpCode\n";
    echo "Raw Response:\n$response\n\n";

    echo "Pretty JSON:\n";
    echo json_encode(json_decode($response, true), JSON_PRETTY_PRINT);
    echo "\n=======================================================\n";

    return [
        'status' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

    /**
     * Get doctor's calendar
     */
   public function getDoctorCalendar($entityKey, $drKey, $startDate, $endDate)
{
    $url = "https://mocdoc.com/api/calendar/" . $entityKey;

    // Form-encoded POST body
    $postDataArray = [
        'entitykey' => $entityKey,
        'drkey' => $drKey,
        'startdate' => $startDate,
        'enddate' => $endDate
    ];

    $body = http_build_query($postDataArray);

    // Debug
    echo "URL: $url\n";
    echo "POST Data: $body\n";

    // ❗ SIGN WITH REAL BODY
    $headers = $this->mocdocHmacHeaders($url, 'POST', "");

    echo "Headers:\n";
    print_r($headers);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);

    // Send real body
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

    // Send headers including Content-Type
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP Code: $httpCode\n";
    echo "Response: $response\n";

    return [
        'status' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

}