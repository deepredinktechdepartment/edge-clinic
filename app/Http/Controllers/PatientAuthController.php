<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Doctor;
class PatientAuthController extends Controller
{
    public function registerForm()
    {
        return view('patient.auth.register');
    }

public function register(Request $request)
{
    try {

        // ✅ Validation (NO password)
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'nullable|email|max:255',
            'phone'        => 'required|string|max:20',
            'country_code' => 'nullable|string|max:10',
            'bookingfor'   => 'required|string',
            'other_reason' => 'nullable|string|max:255',
            'gender'       => 'required|in:M,F',
            'age'          => 'required|integer|min:1|max:120',
            'doctorKey'          => 'required',
            'slotDate'          => 'required',
            'slotTime'          => 'required',
        ]);

        // ✅ Check if patient already exists by phone
        $patient = Patient::where('mobile', $validated['phone'])->first();
        $doctor = Doctor::where('drKey', $validated['doctorKey'])->first();

        if ($patient) {
            // 🔁 Update existing patient (optional)
            $patient->update([
                'name'         => $validated['name'],
                'email'        => $validated['email'] ?? $patient->email,
                'country_code' => $validated['country_code'],
                'gender'       => $validated['gender'],
                'age'          => $validated['age'],
                'bookingfor'   => $validated['bookingfor'],
                'other_reason' => $validated['other_reason'],
                'ipAddress'    => $request->ip(),
            ]);
        } else {
            // ➕ Create new patient
            $patient = Patient::create([
                'name'         => $validated['name'],
                'email'        => $validated['email'] ?? null,
                'mobile'       => $validated['phone'],
                'country_code' => $validated['country_code'] ?? null,
                'gender'       => $validated['gender'],
                'age'          => $validated['age'],
                'bookingfor'   => $validated['bookingfor'],
                'other_reason' => $validated['other_reason'],
                'ipAddress'    => $request->ip(),
            ]);
        }

        // ✅ OPTIONAL: Store patient in session (OTP-based login)
        session(['patient_id' => $patient->id]);

        // ✅ Success message
        session()->flash('success', 'Patient details saved successfully');

        // ✅ Redirect to payment
   return redirect()->route('razorpay.create-order', [
    'patientId' => $patient->id,
    'doctorId' => $doctor->id,
    'drKey'=>$doctor->drKey,
    'slotDate'=>$validated['slotDate'],
    'slotTime'=>$validated['slotTime'],
]);

    } catch (\Exception $e) {
        return back()
            ->with('error', 'Something went wrong. '.$e->getMessage())
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
