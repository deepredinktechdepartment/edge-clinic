<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PatientAuthController extends Controller
{
    public function registerForm()
    {
        return view('patient.auth.register');
    }

public function register(Request $request)
{
    try {
      
        // Validation rules
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'nullable|email|max:255',
            'phone'        => 'required|string|max:20',
            'country_code' => 'nullable|string|max:10',
            'bookingfor'   => 'required|string',
            'other_reason' => 'nullable|string|max:255',
            'gender'       => 'required|string',
            'age'          => 'required|integer|min:1|max:120',
            'password'     => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',    // at least 1 lowercase
                'regex:/[A-Z]/',    // at least 1 uppercase
                'regex:/[0-9]/',    // at least 1 number
                'confirmed',        // matches password_confirmation
            ],
        ]);

        // Check if combination of email + phone exists
        $exists = Patient::where('email', $validated['email'])
                         ->where('mobile', $validated['phone'])
                         ->exists();

        if ($exists) {
            return back()
                ->with('error', 'A patient with this email & phone number already exists!')
                ->withInput();
        }

        // Create patient
        $patient = Patient::create([
            'name'         => $validated['name'],
            'email'        => $validated['email'] ?? null,
            'mobile'       => $validated['phone'],
            'country_code' => $validated['country_code'] ?? null,
            'other_reason' => $validated['other_reason'] ?? null,
            'bookingfor'   => $validated['bookingfor'],
            'gender'       => $validated['gender'],
            'age'          => $validated['age'],
            'ipAddress'    => $request->ip(),
            'password'     => Hash::make($validated['password']),
        ]);

        // Auto-login patient
        Auth::login($patient);

        // Flash success message
        session()->flash('success', 'Patient registered successfully!');

        // Redirect to Razorpay create order
        return redirect()->route('razorpay.create-order', ['patintId' => $patient->id]);

    } catch (\Exception $e) {
        // Catch unexpected errors
        return back()
            ->with('error', 'Something went wrong! Please try again.'.$e->getmessage())
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
