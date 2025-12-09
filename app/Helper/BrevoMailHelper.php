<?php

namespace App\Helper;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class BrevoMailHelper
{
    /**
     * Send an email via Brevo API.
     *
     * You can pass:
     *  - a Blade view name (e.g. 'emails.invoice')
     *  - OR raw HTML directly
     *
     * @param string $toEmail
     * @param string $subject
     * @param string $templateOrHtml
     * @param array $data
     * @return array
     */
    public static function sendMail($toEmail, $subject, $templateOrHtml, $data = [])
    {
        try {
            // ✅ Determine if it's a Blade view or raw HTML
            if (View::exists($templateOrHtml)) {
                $htmlContent = View::make($templateOrHtml, $data)->render();
            } else {
                $htmlContent = $templateOrHtml;
            }

            // ✅ Prepare Brevo payload
            $payload = [
                'sender' => [
                    'name'  => env('MAIL_FROM_NAME', config('app.name')),
                    'email' => env('MAIL_FROM_ADDRESS'),
                ],
                'to' => [
                    ['email' => $toEmail],
                ],
                'subject' => $subject,
                'htmlContent' => $htmlContent,
            ];

            // ✅ Send via Brevo API
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'api-key' => env('BREVO_API_KEY'),
                'content-type' => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', $payload);

            // ✅ Handle API response
            if ($response->successful()) {
                Log::info("✅ Brevo mail sent to {$toEmail}");
                return [
                    'success' => true,
                    'message' => 'Mail sent successfully',
                    'response' => $response->json()
                ];
            } else {
                Log::error('❌ Brevo mail failed: ' . $response->body());
                return [
                    'success' => false,
                    'message' => 'Mail sending failed',
                    'error' => $response->json()
                ];
            }

        } catch (\Exception $e) {
            Log::error('💥 Brevo mail error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Exception while sending mail',
                'error' => $e->getMessage()
            ];
        }
    }
}
