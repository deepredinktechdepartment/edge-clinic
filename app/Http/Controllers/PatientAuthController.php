<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Doctor;
use Illuminate\Support\Facades\DB;
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

        // ✅ Redirect to Razorpay order creation
        return redirect()->route('razorpay.create-order', [
            'patientId' => $patient->id,
            'doctorId'  => $doctor->id,
            'drKey'     => $doctor->drKey,
            'slotDate'  => $validated['slotDate'],
            'slotTime'  => $validated['slotTime'],
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
