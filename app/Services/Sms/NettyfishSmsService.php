<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class NettyfishSmsService
{
    public function sendOtp(string $mobile, string $name, string $otp): bool
    {
        try {
            /* ===============================
             | 1️⃣ Build Message
             =============================== */
            $message = sprintf(
                'Dear %s, your OTP for registering at Edge Clinic is %s. Please enter this code to verify your mobile number. Do not share this OTP with anyone. Edge Clinic | +91-6303285050 Thank you EDGEJV',
                $name,
                $otp
            );

            $message = preg_replace("/\r|\n/", ' ', $message);

            /* ===============================
             | 2️⃣ API Parameters (AS PER PROVIDER)
             =============================== */
            $params = [
                'APIKEY'   => config('services.nettyfish.api_key'),
                'senderid' => config('services.nettyfish.sender_id'),
                'channel'  => 'Trans',
                'DCS'      => 0,
                'flashsms' => 0,
                'number'   => '91' . $mobile,
                'text'     => $message,
                'route'    => 1,
            ];


            /* ===============================
             | 3️⃣ Send Request
             =============================== */
            $response = Http::timeout(10)->get(
                config('services.nettyfish.url'),
                $params
            );

            /* ===============================
             | 4️⃣ Log for Checking
             =============================== */
            Log::info('Nettyfish SMS Debug', [
                'params' => $params,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            // 🔎 TEMP (remove later)
            // dd($params, $response->body());

            return $response->successful();

        } catch (Throwable $e) {

            Log::error('Nettyfish SMS Failed', [
                'mobile' => $mobile,
                'error'  => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function sendAppointmentConfirmation(
        string $mobile,
        string $name,
        string $clinic,
        string $date,
        string $time
    ): bool {
        try {

            /* ===============================
            | 1️⃣ Build Template Message
            =============================== */

            $message = sprintf(
                'Dear %s, Your appointment is confirmed. Please visit %s on %s %s, For help 6303258050 - EDGE CLINIC | +91-6303285050 www.edge.clinic Thank you EDGEJV',
                $name,
                $clinic,
                $date,
                $time
            );

            $message = preg_replace("/\r|\n/", ' ', $message);

            /* ===============================
            | 2️⃣ API Parameters
            =============================== */

            $params = [
                'APIKEY'   => config('services.nettyfish.api_key'),
                'senderid' => config('services.nettyfish.sender_id'),
                'channel'  => 'Trans',
                'DCS'      => 0,
                'flashsms' => 0,
                'number'   => '91' . $mobile,
                'text'     => $message,
                'route'    => 1,
            ];

            /* ===============================
            | 3️⃣ Send SMS
            =============================== */

            $response = Http::timeout(10)->get(
                config('services.nettyfish.url'),
                $params
            );

            Log::info('Appointment SMS', [
                'params' => $params,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return $response->successful();

        } catch (\Throwable $e) {

            Log::error('Appointment SMS Failed', [
                'mobile' => $mobile,
                'error'  => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function sendInvoiceSms(
        string $mobile,
        string $name,
        string $invoiceUrl,
        string $supportNumber,
        string $clinic,
        string $doctor
    ): bool {

        try {


            // Message (must match DLT template exactly)
            $message = sprintf(
                'Dear %s, Thank you for visiting us. Please click on the following link to download your bill %s. For help, please contact us at %s. Take Care, %s - %s. - EDGE CLINIC | +91-6303285050 www.edge.clinic Thank you EDGEJV',
                $name,
                $invoiceUrl,
                $supportNumber,
                $clinic,
                $doctor
            );

            // Remove line breaks
            $message = preg_replace('/\s+/', ' ', trim($message));

            $params = [
                'APIKEY'   => config('services.nettyfish.api_key'),
                'senderid' => config('services.nettyfish.sender_id'),
                'channel'  => 'Trans',
                'DCS'      => 0,
                'flashsms' => 0,
                'number'   => '91' . $mobile,
                'text'     => urlencode($message),
                'route'    => 1,
            ];

            Log::info('Invoice SMS Request', [
                'mobile' => $mobile,
                'message' => $message,
                'params' => $params
            ]);

            $response = Http::timeout(10)->get(
                config('services.nettyfish.url'),
                $params
            );

            Log::info('Invoice SMS Response', [
                'status' => $response->status(),
                'body'   => $response->body()
            ]);

            return $response->successful();

        } catch (\Throwable $e) {

            Log::error('Invoice SMS Failed', [
                'mobile' => $mobile,
                'error'  => $e->getMessage()
            ]);

            return false;
        }
    }

    public function sendPrescriptionSms(string $mobile, string $name, string $doctor, string $prescriptionUrl): bool
    {
        try {
            $message = sprintf(
                'Dear %s, your prescription from Dr. %s is ready. View or download it here: %s. Thank you, EDGE CLINIC.',
                $name, $doctor, $prescriptionUrl
            );

            $response = Http::timeout(10)->get(config('services.nettyfish.url'), [
                'APIKEY' => config('services.nettyfish.api_key'), 'senderid' => config('services.nettyfish.sender_id'),
                'channel' => 'Trans', 'DCS' => 0, 'flashsms' => 0, 'number' => '91' . $mobile,
                'text' => preg_replace('/\s+/', ' ', trim($message)), 'route' => 1,
            ]);

            Log::info('Prescription SMS', ['mobile' => $mobile, 'status' => $response->status(), 'body' => $response->body()]);
            return $response->successful();
        } catch (Throwable $e) {
            Log::error('Prescription SMS Failed', ['mobile' => $mobile, 'error' => $e->getMessage()]);
            return false;
        }
    }
}
