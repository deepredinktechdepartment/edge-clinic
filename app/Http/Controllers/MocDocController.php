<?php

namespace App\Http\Controllers;

use App\Services\MocDocService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
     function mocdocHmacHeaders($url, $method = 'POST', $contentType = "application/x-www-form-urlencoded")
    {

        $date =    "Wed, ". now() . " IST";

        // MD5 hash of raw body, base64 encoded
        $body="";
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

    // Form-encoded POST body
    $postDataArray = [
        'entitylocation' => 'location1',
    ];

    $body = http_build_query($postDataArray);

    // Generate HMAC headers
    $headers = $this->mocdocHmacHeaders($url, 'POST');

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
    $headers = $this->mocdocHmacHeaders($url, 'POST');

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
public function _getDoctorCalendar(Request $request)
{
    $entityKey = "jv-medi-clinic";
    $drKey = $request->drKey ?? '';

    // Start date = today
    $startDate = Carbon::today()->format('Ymd');
    // End date = today + 4 days
    $endDate = Carbon::today()->addDays(4)->format('Ymd');

    $url = "https://mocdoc.com/api/calendar/" . $entityKey;

    // Form-encoded POST body
    $postDataArray = [
        'entitykey' => $entityKey,
        'drkey' => $drKey,
        'startdate' => $startDate,
        'enddate' => $endDate
    ];

    $body = http_build_query($postDataArray);

    // Generate HMAC headers
    $headers = $this->mocdocHmacHeaders($url, 'POST');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return response()->json([
        'status' => $httpCode,
        'data' => json_decode($response, true)
    ]);
}

  public function syncDoctors()
    {

        $entityKey = env('MOCDOC_HOSPITAL_ID');

        // 1. Fetch API data
        $response = $this->sendHmacRequest($entityKey);
        if ($response['status'] !== 200 || empty($response['data']['dr'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch doctors from MocDoc API'
            ]);
        }

        $apiDoctors = $response['data']['dr'];
        $apiDrKeys = array_column($apiDoctors, 'drkey');

        // 2. Fetch local doctors
        $localDoctors = DB::table('doctors')->get();
        $localDrKeys = $localDoctors->pluck('drKey')->toArray();

        // 3. Update local DB based on rules
        foreach ($localDoctors as $doctor) {
            $status = in_array($doctor->drKey, $apiDrKeys) ? 'MocDoc_EdgeDB_Existed' : 'EdgeDB';
            DB::table('doctors')
                ->where('id', $doctor->id)
                ->update(['sync_status' => $status]);
        }

        // 4. Insert API-only doctors
        foreach ($apiDoctors as $apiDoctor) {
            if (!in_array($apiDoctor['drkey'], $localDrKeys)) {
                DB::table('doctors')->insert([
                    'name' => $apiDoctor['name'],
                    'slug' => Str::slug($apiDoctor['name']), // generate slug from name
                    'drKey' => $apiDoctor['drkey'],
                    'qualification' => $apiDoctor['ug_degree'] ?? '',
                    'gender' => $apiDoctor['gender'] ?? '',
                    'mobile' => $apiDoctor['mobile'] ?? '',
                    'locations' => $apiDoctor['locations'] ?? '',
                    'expertise' => implode(', ', $apiDoctor['speciality'] ?? []),
                    'sync_status' => 'MocDoc_only',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Doctors sync completed',
            'total_api' => count($apiDoctors),
            'total_local' => count($localDoctors),
        ]);
    }

}