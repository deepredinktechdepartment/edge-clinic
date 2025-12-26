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
}
