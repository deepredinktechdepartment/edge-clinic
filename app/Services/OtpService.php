<?php

namespace App\Services;

use App\Models\Otp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class OtpService
{
    const OTP_EXPIRY_MINUTES = 5;
    const RESEND_COOLDOWN = 60; // seconds
    const MAX_ATTEMPTS = 5;

    /**
     * Generate and store OTP
     */
    public function generate(string $mobile): string
    {
        $otpRecord = Otp::where('mobile', $mobile)->first();

        // 🔒 Resend cooldown
        if (
            $otpRecord &&
            $otpRecord->last_sent_at &&
            Carbon::now()->diffInSeconds($otpRecord->last_sent_at) < self::RESEND_COOLDOWN
        ) {
            throw new \Exception('Please wait before requesting another OTP.');
        }

        // Generate OTP
        $otp = random_int(100000, 999999);

        // Replace or create OTP
        Otp::updateOrCreate(
            ['mobile' => $mobile],
            [
                'otp_hash'    => Hash::make($otp),
                'expires_at'  => Carbon::now()->addMinutes(self::OTP_EXPIRY_MINUTES),
                'attempts'    => 0,
                'last_sent_at'=> Carbon::now(),
            ]
        );

        return $otp;
    }

    /**
     * Verify OTP
     */
    public function verifyotp(string $mobile, string $otp): bool
    {
        $otpRecord = Otp::where('mobile', $mobile)->first();

        if (!$otpRecord) {
            throw new \Exception('OTP not found.');
        }

        if ($otpRecord->isExpired()) {
            $otpRecord->delete();
            throw new \Exception('OTP expired.');
        }

        if ($otpRecord->attempts >= self::MAX_ATTEMPTS) {
            throw new \Exception('Too many incorrect attempts.');
        }

        if (!Hash::check($otp, $otpRecord->otp_hash)) {
            $otpRecord->increment('attempts');
            throw new \Exception('Invalid OTP.');
        }

        // ✅ OTP verified successfully (one-time use)
        $otpRecord->delete();

        return true;
    }
}
