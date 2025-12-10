<?php

namespace App\Http\Controllers;

use App\Services\MocDocService;

class MocDocController extends Controller
{
    public function doctors($entityKey)
    {
        $service = new MocDocService();

       
        // Optional entitylocation
        $entityLocation = request()->get('entitylocation');

        $result = $service->getDoctors($entityKey, $entityLocation);

        return response()->json($result);
    }
    function mocdocHmacHeaders($url, $method = 'POST', $body = '') {
    $accessKey = "399e911b4a2a28f5";
    $secretKey = "4bfb224da2d03660188a65027dd8265b";

    $contentType = "application/x-www-form-urlencoded"; // or "application/json" depending on API
    $date = gmdate('D, d M Y H:i:s T'); // RFC 1123

    // Calculate MD5 of body
    $contentMd5 = $body !== "" ? base64_encode(md5($body, true)) : "";

    // String to sign
    $parsedUrl = parse_url($url);
    $path = $parsedUrl['path'];
    $toSign = $method . "\n" . $contentMd5 . "\n" . $contentType . "\n" . $date . "\n" . "\n" . $path;

    // Calculate HMAC-SHA1
    $hmac = base64_encode(hash_hmac('sha1', $toSign, $secretKey, true));

    // Return headers
    return [
        "Content-Type: $contentType",
        "Date: $date",
        "Authorization: MD $accessKey:$hmac"
    ];
}

function sendHmacRequest($entity = 'jv-medi-clinic') {
    $url = "https://mocdoc.com/api/get/dr/" . $entity;
    $content = ""; // empty POST body

    $headers = $this->mocdocHmacHeaders($url, 'POST', $content);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'status' => $httpCode,
        'data' => json_decode($response, true)
    ];
}
function getDoctorCalendar($entityKey, $drKey, $startDate, $endDate) {
    $url = "https://mocdoc.com/api/calendar/".$drKey;

    // Form-encoded body
        $postData = [
        'entitykey' => $entityKey,
        'drkey' => $drKey,
        'startdate' => $startDate,
        'enddate' => $endDate
        ];
     
    // Convert to JSON for HMAC request
$postData = json_encode($postData, JSON_UNESCAPED_SLASHES);
   
    // Reuse HMAC headers
    $headers = $this->mocdocHmacHeaders($url, 'POST', $postData);

     $content = $postData; // empty POST body
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'status' => $httpCode,
        'data' => json_decode($response, true)
    ];
}








}
