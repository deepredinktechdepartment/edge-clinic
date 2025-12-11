<?php

namespace App\Services;

use App\Helper\MocDocHelper;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class MocDocService
{
    private $client;
    private $baseUrl;

    // Old API Credentials
    private $hospitalId;
    private $username;
    private $password;

    // NEW HMAC API Credentials
    private $accessKey;
    private $secretKey;

    public function __construct()
    {
        $this->client = new Client(['verify' => false]);

        $this->baseUrl    = config('services.mocdoc.base_url');

        // OLD API
        $this->hospitalId = config('services.mocdoc.hospital_id');
        $this->username   = config('services.mocdoc.username');
        $this->password   = config('services.mocdoc.password');

        // NEW HMAC API
        $this->accessKey  = config('services.mocdoc.access_key');
        $this->secretKey  = config('services.mocdoc.secret_key'); // BASE64 ENCODED if required
    }

    /* ============================================
     | OLD API
     ============================================ */
    private function callLegacyApi($endpoint, $body = [])
    {
        $body = array_merge([
            "HospitalID" => $this->hospitalId,
            "UserName"   => $this->username,
            "Password"   => $this->password
        ], $body);

        try {
            $response = $this->client->post($this->baseUrl . $endpoint, [
                'json' => $body
            ]);

            return json_decode($response->getBody(), true);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $responseBody = $e->getResponse()->getBody()->getContents();
            $cleanMessage = strip_tags($responseBody);
            $cleanMessage = trim(preg_replace('/\s+/', ' ', $cleanMessage));

            Log::error("Legacy API Error: " . $cleanMessage);

            return [
                'error' => true,
                'message' => $cleanMessage
            ];
        }
    }

    /* ============================================
     | NEW HMAC API
     ============================================ */
    private function callHmacApi($path, $data = [])
    {
        $url = "https://mocdoc.com" . $path;

        // Convert body to query string for signature
        $body = http_build_query($data);

        // Generate headers using Helper
        $headers = MocDocHelper::generateAuthHeaders(
            "POST",
            $path,
            "", // body hash if needed
            $body,
            $this->accessKey,
            $this->secretKey
        );

        $headers['Content-Type'] = 'application/json';

        // Logging for debugging
        Log::info("HMAC API Request URL: $url");
        Log::info("HMAC API Headers: " . json_encode($headers));
        Log::info("HMAC API Body: " . json_encode($data));

        try {
            $response = $this->client->post($url, [
                "headers" => $headers,
                "json"    => $data
            ]);

            return json_decode($response->getBody(), true);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $responseBody = $e->getResponse()->getBody()->getContents();

            // Clean HTML error
            $cleanMessage = strip_tags($responseBody);
            $cleanMessage = trim(preg_replace('/\s+/', ' ', $cleanMessage));

            Log::error("HMAC API Error: " . $cleanMessage);

            return [
                'error' => true,
                'message' => $cleanMessage
            ];
        }
    }

    /* ============================================
     | OLD API METHODS
     ============================================ */
    public function getDoctorsLegacy()
    {
        return $this->callLegacyApi("GetDoctors", []);
    }

    public function getDoctorDetailLegacy($doctorId)
    {
        return $this->callLegacyApi("GetDoctorDetails", [
            "DoctorID" => $doctorId
        ]);
    }

    public function getAvailabilityLegacy($doctorId, $date)
    {
        return $this->callLegacyApi("GetAppointmentSlots", [
            "DoctorID" => $doctorId,
            "Date"     => $date
        ]);
    }

    public function bookAppointmentLegacy($data)
    {
        return $this->callLegacyApi("BookAppointment", $data);
    }

    public function cancelAppointmentLegacy($bookingId)
    {
        return $this->callLegacyApi("CancelAppointment", [
            "BookingID" => $bookingId
        ]);
    }

    public function getBookingLegacy($bookingId)
    {
        return $this->callLegacyApi("GetAppointmentDetails", [
            "BookingID" => $bookingId
        ]);
    }

    /* ============================================
     | NEW HMAC METHODS
     ============================================ */
    public function getDoctorsHmac($entityKey, $entityLocation = null)
    {
        $path = "/api/get/dr/" . $entityKey;

        $data = [];
        if ($entityLocation) {
            $data['entitylocation'] = $entityLocation;
        }

        return $this->callHmacApi($path, $data);
    }

    /* ============================================
     | UNIFIED METHOD (FOR CONTROLLER)
     ============================================ */
    public function getDoctors($entityKey = null, $entityLocation = null)
    {
        return $entityKey
            ? $this->getDoctorsHmac($entityKey, $entityLocation)
            : $this->getDoctorsLegacy();
    }
}
