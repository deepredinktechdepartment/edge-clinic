<?php
namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Enquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;


class EnquiryController extends Controller
{
    public function sendOtp(Request $request)
{
    $request->validate([
        'phone' => 'required|digits:10'
    ]);

    $ip = $request->ip();
    $rateKey = "otp_send_{$ip}";

    if (Cache::get($rateKey, 0) >= 5) {
        return response()->json(['error' => 'Too many OTP requests'], 429);
    }

    $otp = rand(100000, 999999);

    Cache::put("otp_{$request->phone}", $otp, now()->addMinutes(5));
    Cache::increment($rateKey, 1);
    Cache::put($rateKey, Cache::get($rateKey), now()->addMinutes(10));

    return response()->json([
        'success' => 'OTP generated',
        'otp'     => $otp // ⚠ TEMP: remove when SMS is live
    ]);
}


    /* ---------------- VERIFY OTP ---------------- */
    public function verifyOtp(Request $request)
{
    $request->validate([
        'phone' => 'required|digits:10',
        'otp'   => 'required|digits:6'
    ]);

    $storedOtp = Cache::get("otp_{$request->phone}");

    if (!$storedOtp || $storedOtp != $request->otp) {
        return response()->json(['error' => 'Invalid OTP'], 422);
    }

    Cache::forget("otp_{$request->phone}");
    Cache::put("otp_verified_{$request->phone}", true, now()->addMinutes(10));

    return response()->json(['success' => 'OTP verified']);
}


    /* ---------------- SAVE ENQUIRY ---------------- */
    public function store(Request $request)
    {
        // 🐝 Honeypot field (spam bots)
        if ($request->filled('website')) {
            abort(403);
        }

        $request->validate([
            'name'  => 'required|string|max:100',
            'phone' => 'required|digits:10'
        ]);

        if (!Cache::get("otp_verified_{$request->phone}")) {
            return response()->json(['error' => 'OTP not verified'], 403);
        }

        Enquiry::create([
            'name'         => $request->name,
            'phone'        => $request->phone,
            'otp_verified' => true,
            'ip_address'   => $request->ip(),
            'user_agent'   => $request->userAgent()
        ]);

        Cache::forget("otp_verified_{$request->phone}");

        return response()->json(['success' => 'Callback request submitted']);
    }

public function callback_enquiries()
    {
        try{
            $pageTitle="Callback Enquiries";
            $enquiries_data = Enquiry::all();
            return view('admin.enquiries.lists', compact('pageTitle','enquiries_data'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Page can\'t access.');
        }
    }

    public function delete($ID)
    {
        try {
            $id = Crypt::decryptString($ID);

            $enquiry = Enquiry::findOrFail($id);
            $enquiry->delete();

            return redirect()
                ->back()
                ->with('success', 'Enquiry deleted successfully');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Invalid request');
        }
    }

}
