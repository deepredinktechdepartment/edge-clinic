<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\Patient;
use App\Services\DoctorSyncService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
Use Exception;
use Hash;
use Validator;
use Auth;
use Session;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\MocDocController;

class HomeController extends Controller
{


    public function auth_login(Request $request)
    {
        $user  = auth()->user();


        if($user){

           return redirect('admin/dashboard')->with('success', 'Successfully logged in.');
        }

        else{

           $pageTitle="Login";
            return view('auth.login', compact('pageTitle'));
        }


    }
   public function Loginsubmit(Request $request)
{
    // 1️⃣ Validate input
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required'
    ]);

    // 2️⃣ Check if email exists
    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return redirect('/admin')
            ->withInput($request->only('email'))
            ->with('error', 'Email address not found.');
    }

    // 3️⃣ Check if account is active
    if ($user->is_active != 1) {
        return redirect('/admin')
            ->withInput($request->only('email'))
            ->with('error', 'Your account is inactive. Please contact admin.');
    }

    // 4️⃣ Check password
    if (!Hash::check($request->password, $user->password)) {
        return redirect('/admin')
            ->withInput($request->only('email'))
            ->with('error', 'Invalid password.');
    }

    // 5️⃣ Login user
    Auth::login($user);

    // 🔥 DAILY MOC DOC FETCH (ONCE PER DAY)
    $todayKey = 'mocdoc_daily_fetch_' . date('Y-m-d');

    if (!Cache::has($todayKey)) {

        Cache::put($todayKey, true, 86400); // 24 hours

        try {
            app(\App\Http\Controllers\MocDocController::class)->fetchDoctors();
        } catch (\Exception $e) {
            \Log::error('Daily MocDoc fetch failed: '.$e->getMessage());
        }
    }

    return redirect('admin/dashboard')
        ->with('success', 'Successfully logged in.');
}
    public function logout()
    {

        Auth::logout();
        Session::flush();
        return redirect('/admin')->with('error', 'You have been successfully logged out!');
    }

public function dashboard_lists()
{
    $pageTitle = 'Dashboard';
    $addLink = '';

    $today = \Carbon\Carbon::today();
    $monthStart = \Carbon\Carbon::now()->startOfMonth();
    $monthEnd = \Carbon\Carbon::now()->endOfMonth();

    $role = auth()->user()->role;
    $userId = auth()->id();

    // ===============================
    // DEFAULT VALUES
    // ===============================
    $departments_count = 0;
    $doctors_count     = 0;
    $patients_count    = 0;

    $appointments = ['today' => 0, 'month' => 0];
    $payments     = ['today' => 0, 'month' => 0];

    $localDoctors = collect();
    $mocdocDoctors = collect();

    // ===============================
    // ROLE 1 & 3 → FULL DATA
    // ===============================
    if (in_array($role, [1,3])) {

        $departments_count = Department::count();
        $doctors_count     = Doctor::count();
        $patients_count    = Patient::count();

        /* ===============================
           ✅ APPOINTMENTS (FIXED)
        =============================== */
        $appointments = [
            'today' => DB::table('appointments')
                ->whereDate('created_at', $today)
                ->count(),

            'month' => DB::table('appointments')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count(),
        ];

        /* ===============================
           ✅ PAYMENTS (UNCHANGED)
        =============================== */
        $payments = [
            'today' => Payment::where('status', 'Authorized')
                ->whereDate('created_at', $today)
                ->sum('amount'),

            'month' => Payment::where('status', 'Authorized')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('amount'),
        ];

        $localDoctors = Doctor::select(
            'id','name','drKey','photo','qualification',
            'department_id','expertise','sync_status'
        )->get();

        $mocdocDoctors = collect(\Cache::get('mocdoc_daily_doctors', [])); // optional keep
    }

    // ===============================
    // ROLE 5 (DOCTOR)
    // ===============================
    if ($role == 5) {

        $doctorId = auth()->user()->doctor_id ?? null;

        if ($doctorId) {

            /* ===============================
               ✅ PATIENT COUNT (FROM APPOINTMENTS)
            =============================== */
            $patients_count = DB::table('appointments')
                ->where('doctor_id', $doctorId)
                ->distinct('patient_id')
                ->count('patient_id');

            /* ===============================
               ✅ APPOINTMENTS (FIXED)
            =============================== */
            $appointments = [
                'today' => DB::table('appointments')
                    ->where('doctor_id', $doctorId)
                    ->whereDate('created_at', $today)
                    ->count(),

                'month' => DB::table('appointments')
                    ->where('doctor_id', $doctorId)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->count(),
            ];

            /* ===============================
               ✅ PAYMENTS (UNCHANGED)
            =============================== */
            $payments = [
                    'today' => Payment::where('doctor_id', $doctorId)
                        ->where('status', 'Authorized')
                        ->whereDate('created_at', $today)
                        ->sum('doctor_fee'),

                    'month' => Payment::where('doctor_id', $doctorId)
                        ->where('status', 'Authorized')
                        ->whereBetween('created_at', [$monthStart, $monthEnd])
                        ->sum('doctor_fee'),
                ];
        }
    }

    return view('home.dashboard', compact(
        'pageTitle',
        'addLink',
        'departments_count',
        'doctors_count',
        'patients_count',
        'appointments',
        'payments',
        'today',
        'monthStart',
        'monthEnd',
        'localDoctors',
        'mocdocDoctors'
    ));
}

}
