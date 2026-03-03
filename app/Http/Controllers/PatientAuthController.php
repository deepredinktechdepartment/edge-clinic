<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Doctor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PatientAuthController extends Controller
{
    public function registerForm()
    {
        return view('patient.auth.register');
    }

public function register(Request $request)
{

    // ✅ Validate incoming request
    $validated = $request->validate([
        'patient_id'   => 'nullable|exists:patients,id',
        'name'         => 'required|string|max:255',
        'email'        => 'nullable|email|max:255',
        'phone'        => 'required|string|max:20',
        'country_code' => 'nullable|string|max:10',
        'bookingfor'   => 'required|string',
        'other_reason' => 'nullable|string|max:255',
        'gender'       => 'required|in:M,F',
        'age'          => 'required|integer|min:1|max:120',
        'doctorKey'    => 'required',
        'slotDate'     => 'required|date',
        'slotTime'     => 'required',
        'action'       => 'nullable|string',
    ]);

    try {
        $patient = DB::transaction(function () use ($validated, $request) {

            // 🔹 Check if creating a new patient and phone has >= 4 records
            if (empty($validated['patient_id'])) {
                $existingCount = \App\Models\Patient::where('mobile', $validated['phone'])->count();
                if ($existingCount >= 4) {
                    throw new \Exception('Maximum 4 patient records allowed for this phone number.');
                }
            }

            if (!empty($validated['patient_id'])) {
                // 🔹 Existing patient - update
                $patient = \App\Models\Patient::findOrFail($validated['patient_id']);
                $patient->update([
                    'name'         => $validated['name'],
                    'email'        => $validated['email'] ?? null,
                    'mobile'       => $validated['phone'],
                    'country_code' => $validated['country_code'] ?? null,
                    'gender'       => $validated['gender'],
                    'age'          => $validated['age'],
                    'bookingfor'   => $validated['bookingfor'],
                    'other_reason' => $validated['other_reason'] ?? null,
                    'ipAddress'    => $request->ip(),
                    'registration_source' => $validated['action'] ?? $patient->registration_source,
                ]);
            } else {
                // 🔹 New patient - create user + patient
                $user = \App\Models\User::firstOrCreate(
                    ['phone' => $validated['phone']],
                    [
                        'name'  => $validated['name'],
                        'email' => $validated['email'] ?? null,
                        'isd'   => $validated['country_code'] ?? null,
                        'role'  => 4, // patient role
                    ]
                );

                $isPrimary = ! \App\Models\Patient::where('user_id', $user->id)->exists();

                $patient = \App\Models\Patient::create([
                    'user_id'             => $user->id,
                    'name'                => $validated['name'],
                    'email'               => $validated['email'] ?? null,
                    'mobile'              => $validated['phone'],
                    'country_code'        => $validated['country_code'] ?? null,
                    'gender'              => $validated['gender'],
                    'age'                 => $validated['age'],
                    'bookingfor'          => $validated['bookingfor'],
                    'other_reason'        => $validated['other_reason'] ?? null,
                    'ipAddress'           => $request->ip(),
                    'is_primary_account'  => $isPrimary,
                    'registration_source' => $validated['action'] ?? 'default',
                    'stage'               => 'patient_created',
                    'stages'              => json_encode([
                        'patient_created'       => now()->toDateTimeString(),
                        'doctor_slot_selected'  => null,
                        'payment_received'      => null,
                    ]),
                ]);
            }

            return $patient;
        });

        // ✅ Store patient in session
        session(['patient_id' => $patient->id]);


        // ✅ Fetch doctor
        $doctor = \App\Models\Doctor::where('drKey', $validated['doctorKey'])->firstOrFail();


        /* =====================================================
        ZERO AMOUNT DIRECT BOOKING (NO GATEWAY)
        ===================================================== */

        $doctorFee       = (float) $request->doctor_fee;
        $registrationFee = (float) $request->registration_fee;
        $totalAmount     = (float) $request->total_amount;

        if ($totalAmount <= 0) {

            DB::beginTransaction();

            try {

                /* ===============================
                FOLLOW-UP FINAL CHECK
                =============================== */

                $isFollowup   = 0;
                $mainVisitId  = null;

                $lastAppointment = \App\Models\Payment::where('doctor_id', $doctor->id)
                    ->where('patient_id', $patient->id)
                    ->where('status', 'Authorized')
                    ->orderBy('id', 'desc')
                    ->first();

                if ($lastAppointment && $lastAppointment->aptDate) {

                    $lastVisit = \Carbon\Carbon::createFromFormat('Ymd', $lastAppointment->aptDate);

                    $validTill = $lastVisit->copy()
                        ->addDays($doctor->followup_days);

                    if (\Carbon\Carbon::today()->lessThanOrEqualTo($validTill)) {

                        $isFollowup = 1;
                        $mainVisitId = $lastAppointment->main_visit_id
                            ?? $lastAppointment->id;
                    }
                }

                /* ===============================
                GENERATE IDS
                =============================== */

                $paymentId = 'FREE_' . strtoupper(Str::random(8));
                $orderId   = 'FREE_ORDER_' . strtoupper(Str::random(6));

                /* ===============================
                INSERT PAYMENT
                =============================== */

                DB::table('payments')->insert([
                    'patient_id'       => $patient->id,
                    'payment_id'       => $paymentId,
                    'order_id'         => $orderId,
                    'doctor_id'        => $doctor->id,
                    'doctor_fee'       => 0,
                    'registration_fee' => 0,
                    'amount'           => 0,
                    'currency'         => 'INR',
                    'status'           => 'Authorized',
                    'type'             => 'appointment',
                    'is_followup'      => $isFollowup,
                    'main_visit_id'    => $mainVisitId,
                    'aptDate'          => $validated['slotDate'],
                    'aptTime'          => $validated['slotTime'],
                    'email'            => $patient->email,
                    'phone'            => $patient->mobile,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);

                /* ===============================
                BOOK MOCDOC
                =============================== */

                $details = [
                    'payment_id' => $paymentId,
                    'amount'     => 0,
                    'currency'   => 'INR',
                    'status'     => 'Authorized',
                    'first_name' => $patient->name,
                    'last_name'  => '',
                    'email'      => $patient->email,
                    'phone'      => $patient->mobile,
                    'patient_id' => $patient->id,
                    'user_id'    => $patient->user_id,
                    'doctor_id'  => $doctor->id,
                    'dr'         => $doctor->drKey,
                    'date'       => $validated['slotDate'],
                    'start'      => $validated['slotTime'],
                    'end'        => \Carbon\Carbon::createFromFormat('H:i', $validated['slotTime'])
                                        ->addMinutes(10)
                                        ->format('H:i'),
                ];

                $mocdocResponse = app(\App\Http\Controllers\RazorpayController::class)
                    ->bookMocdocAppointment($details);

                if (empty($mocdocResponse['apptkey'])) {
                    throw new \Exception('MocDoc booking failed');
                }

                DB::table('payments')
                    ->where('payment_id', $paymentId)
                    ->update([
                        'mocdoc_apptkey'   => $mocdocResponse['apptkey'],
                        'mocdoc_response'  => json_encode($mocdocResponse),
                        'updated_at'       => now(),
                    ]);

                DB::commit();

                session([
                    'payment_details' => array_merge($details, [
                        'apptkey' => $mocdocResponse['apptkey']
                    ])
                ]);

                return redirect()->route('razorpay.success');

            } catch (\Throwable $e) {

                DB::rollBack();

                return back()->with('error', 'Booking failed: '.$e->getMessage());
            }
        }

        // ✅ Redirect to Razorpay order creation
        return redirect()->route('razorpay.create-order', [
            'patientId'        => $patient->id,
            'doctorId'         => $doctor->id,
            'drKey'            => $doctor->drKey,
            'slotDate'         => $validated['slotDate'],
            'slotTime'         => $validated['slotTime'],
            'doctor_fee'       => $request->doctor_fee,
            'registration_fee' => $request->registration_fee,
            'total_amount'     => $request->total_amount,
        ]);




    } catch (\Exception $e) {
        // ❌ Handle errors gracefully
        return back()
            ->with('error', $e->getMessage())
            ->withInput();
    }
}
    public function loginForm()
    {
        return view('patient.auth.login');
    }

   public function login(Request $request)
{
    $request->validate([
        'email'    => 'required', // email or mobile
        'password' => 'required'
    ]);

    $loginInput = $request->email;

    // Detect if input is email or mobile
    $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';

    // Find patient
    $patient = Patient::where($field, $loginInput)->first();

    // 1. Patient not found
    if (!$patient) {
        return back()->withErrors([
            'email' => 'Account not found. Please check your email or mobile number.'
        ]);
    }

    // 2. Check active status (if column exists)
    if (isset($patient->status) && $patient->status !== 'active') {
        return back()->withErrors([
            'email' => 'Your account is not active. Please contact support.'
        ]);
    }

    // 3. Password validation
    if (!Hash::check($request->password, $patient->password)) {
        return back()->withErrors([
            'password' => 'Incorrect password.'
        ]);
    }

    // 4. Login user
    Auth::login($patient);

    return redirect('/')->with('success', 'Logged in successfully!');
}

    public function logout()
    {
        Auth::logout();
        return redirect('/patient/login');
    }
}
