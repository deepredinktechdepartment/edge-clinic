<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use App\Services\OtpService;
use App\Services\Sms\NettyfishSmsService;

class OtpController extends Controller
{
    public function send(Request $request, OtpService $otpService, NettyfishSmsService $sms)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|digits_between:6,15',
            'country_code' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid phone'], 422);
        }

        $otp = $otpService->generate($request->phone);

        // Store OTP for 5 minutes
        Cache::put('otp_' . $request->phone, $otp, now()->addMinutes(5));

        $sms->sendOtp($request->phone, 'User', $otp);

        return response()->json([
            'status' => true,
            'message' => 'OTP sent successfully'
        ]);
    }


	  public function verify(Request $request, OtpService $otpService)
    {
        $request->validate([
            'phone' => 'required|digits:10',
            'otp'    => 'required|digits:6',
        ]);

        try {
            $otpService->verifyotp($request->phone, $request->otp);

            return response()->json([
                'status'  => 'success',
                'message' => 'OTP verified',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
