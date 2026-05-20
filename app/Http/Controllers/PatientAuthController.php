<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Doctor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\Sms\NettyfishSmsService;
use App\Services\FollowupEligibilityService;

class PatientAuthController extends Controller
{
    public function registerForm()
    {
        return view('patient.auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'patient_id'     => 'nullable|exists:patients,id',
            'name'           => 'required|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'required|string|max:20',
            'country_code'   => 'nullable|string|max:10',
            'bookingfor'     => 'required|string',
            'other_reason'   => 'nullable|string|max:255',
            'gender'         => 'required|in:M,F',
            'age'            => 'required|integer|min:1|max:120',
            'doctorKey'      => 'required',
            'slotDate'       => 'required|date',
            'slotTime'       => 'required',
            'payment_choice' => 'required|string',
            'total_due'      => 'required|numeric|min:0',
            'action'         => 'nullable|string',
        ]);

        try {
            $patient = DB::transaction(function () use ($validated, $request) {
                if (empty($validated['patient_id'])) {
                    $existingCount = \App\Models\Patient::where('mobile', $validated['phone'])->count();
                    if ($existingCount >= 4) {
                        throw new \Exception('Maximum 4 patient records allowed for this phone number.');
                    }
                }

                if (!empty($validated['patient_id'])) {
                    $patient = \App\Models\Patient::findOrFail($validated['patient_id']);
                    $patient->update([
                        'name'                => $validated['name'],
                        'email'               => $validated['email'] ?? null,
                        'mobile'              => $validated['phone'],
                        'country_code'        => $validated['country_code'] ?? null,
                        'gender'              => $validated['gender'],
                        'age'                 => $validated['age'],
                        'bookingfor'          => $validated['bookingfor'],
                        'other_reason'        => $validated['other_reason'] ?? null,
                        'ipAddress'           => $request->ip(),
                        'registration_source' => $validated['action'] ?? $patient->registration_source,
                    ]);
                } else {
                    $user = \App\Models\User::firstOrCreate(
                        ['phone' => $validated['phone']],
                        [
                            'name'  => $validated['name'],
                            'email' => $validated['email'] ?? null,
                            'isd'   => $validated['country_code'] ?? null,
                            'role'  => 4,
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
                            'patient_created'      => now()->toDateTimeString(),
                            'doctor_slot_selected' => null,
                            'payment_received'     => null,
                        ]),
                    ]);
                }

                return $patient;
            });

            session(['patient_id' => $patient->id]);

            $doctor = Doctor::where('drKey', $validated['doctorKey'])->firstOrFail();

            $doctorFee       = (float) $request->doctor_fee;
            $registrationFee = (float) $request->registration_fee;
            $totalDue        = (float) $request->total_due;
            $payNowAmount    = (float) $request->total_amount;

            if ($registrationFee > 0 && $payNowAmount < $registrationFee) {
                return back()
                    ->withInput()
                    ->with('error', 'Registration fee must be paid online to confirm the appointment.');
            }

            if ($payNowAmount <= 0) {
                DB::beginTransaction();

                try {
                    $followup = app(FollowupEligibilityService::class)
                        ->check($doctor->id, $patient->id, (int) $doctor->followup_days);

                    $isFollowup = $followup['eligible'] ? 1 : 0;
                    $mainVisitId = $followup['eligible'] ? $followup['main_visit_id'] : null;

                    $paymentId = 'FREE_' . strtoupper(Str::random(8));
                    $orderId   = 'FREE_ORDER_' . strtoupper(Str::random(6));
                    $paymentStatus = $totalDue > 0 ? 'Pending' : 'Authorized';
                    $paymentMode = $totalDue > 0 ? 'pay_later' : 'free_booking';

                    DB::table('payments')->insert([
                        'patient_id'       => $patient->id,
                        'payment_id'       => $paymentId,
                        'order_id'         => $orderId,
                        'doctor_id'        => $doctor->id,
                        'doctor_fee'       => $doctorFee,
                        'registration_fee' => $registrationFee,
                        'amount'           => $totalDue,
                        'currency'         => 'INR',
                        'status'           => $paymentStatus,
                        'type'             => 'appointment',
                        'payment_mode'     => $paymentMode,
                        'notes'            => json_encode([
                            'payment_choice'     => $request->payment_choice,
                            'pay_now_amount'     => 0,
                            'total_due'          => $totalDue,
                            'outstanding_amount' => $totalDue,
                        ]),
                        'is_followup'      => $isFollowup,
                        'main_visit_id'    => $mainVisitId,
                        'aptDate'          => $validated['slotDate'],
                        'aptTime'          => $validated['slotTime'],
                        'email'            => $patient->email,
                        'phone'            => $patient->mobile,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);

                    $details = [
                        'payment_id' => $paymentId,
                        'amount'     => $totalDue,
                        'currency'   => 'INR',
                        'status'     => $paymentStatus,
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

                    $exists = DB::table('appointments')
                        ->where('doctor_id', $doctor->id)
                        ->where('date', $validated['slotDate'])
                        ->where('time_slot', $validated['slotTime'])
                        ->exists();

                    if ($exists) {
                        throw new \Exception('Slot already booked');
                    }

                    $appointmentNo = 'APT' . now()->format('YmdHis') . strtoupper(Str::random(3));

                    $appointmentId = DB::table('appointments')->insertGetId([
                        'appointment_no' => $appointmentNo,
                        'doctor_id'      => $doctor->id,
                        'patient_id'     => $patient->id,
                        'date'           => $validated['slotDate'],
                        'time_slot'      => $validated['slotTime'],
                        'fee'            => $totalDue,
                        'payment_id'     => $paymentId,
                        'payment_status' => $totalDue > 0 ? 'pending' : 'success',
                        'payment_method' => $paymentMode,
                        'currency'       => 'INR',
                        'payment_date'   => $totalDue > 0 ? null : now(),
                        'created_at'     => now(),
                    ]);

                    DB::table('appointment_status_logs')->insert([
                        'appointment_no' => $appointmentNo,
                        'appointment_id' => $appointmentId,
                        'to_status'      => 'Booked',
                        'changed_by'     => $patient->id,
                        'changedName'    => $patient->name,
                        'created_at'     => now(),
                    ]);

                    session([
                        'payment_details' => array_merge($details, [
                            'appointment_no' => $appointmentNo,
                        ])
                    ]);

                    $smsService = app(NettyfishSmsService::class);

                    $appointmentSms = $smsService->sendAppointmentConfirmation(
                        $details['phone'],
                        $details['first_name'] ?? 'Patient',
                        'Edge Clinic',
                        \Carbon\Carbon::parse($details['date'])->format('d M Y'),
                        $details['start']
                    );

                    DB::table('payments')
                        ->where('payment_id', $details['payment_id'])
                        ->update([
                            'sms_delivered' => $appointmentSms ? 1 : 0,
                            'sms_sent_at'   => $appointmentSms ? now() : null,
                        ]);

                    DB::commit();

                    return redirect()->route('razorpay.success');
                } catch (\Throwable $e) {
                    DB::rollBack();
                    return back()->with('error', 'Booking failed: ' . $e->getMessage());
                }
            }

            return redirect()->route('razorpay.create-order', [
                'patientId'        => $patient->id,
                'doctorId'         => $doctor->id,
                'drKey'            => $doctor->drKey,
                'slotDate'         => $validated['slotDate'],
                'slotTime'         => $validated['slotTime'],
                'doctor_fee'       => $request->doctor_fee,
                'registration_fee' => $request->registration_fee,
                'total_due'        => $request->total_due,
                'total_amount'     => $request->total_amount,
                'payment_choice'   => $request->payment_choice,
            ]);
        } catch (\Exception $e) {
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
            'email'    => 'required',
            'password' => 'required'
        ]);

        $loginInput = $request->email;
        $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';
        $patient = Patient::where($field, $loginInput)->first();

        if (!$patient) {
            return back()->withErrors([
                'email' => 'Account not found. Please check your email or mobile number.'
            ]);
        }

        if (isset($patient->status) && $patient->status !== 'active') {
            return back()->withErrors([
                'email' => 'Your account is not active. Please contact support.'
            ]);
        }

        if (!Hash::check($request->password, $patient->password)) {
            return back()->withErrors([
                'password' => 'Incorrect password.'
            ]);
        }

        Auth::login($patient);

        return redirect('/')->with('success', 'Logged in successfully!');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/patient/login');
    }
}
