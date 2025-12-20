<?php
namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\DB;
class PatientController extends Controller
{
    // ----------------------------------------
    // List Page
    // ----------------------------------------
  public function index(Request $request)
{
    $pageTitle = 'Patient Profiles';
    $addlink   = url('patients/create');

    $query = Patient::query();

    /* =========================
       NAME SEARCH
    ========================= */
    if ($request->filled('name')) {
        $query->where('name', 'like', '%' . $request->name . '%');
    }

    /* =========================
       PHONE SEARCH
    ========================= */
    if ($request->filled('phone')) {
        $query->where('mobile', 'like', '%' . $request->phone . '%');
    }

    /* =========================
       REGISTERED DATE FILTER
    ========================= */
    if ($request->filled('from_date')) {
        $query->whereDate('created_at', '>=', $request->from_date);
    }

    if ($request->filled('to_date')) {
        $query->whereDate('created_at', '<=', $request->to_date);
    }

    /* =========================
       FINAL RESULT
    ========================= */
    $patients = $query->latest()->get();

    return view('patients.index', compact(
        'patients',
        'pageTitle',
        'addlink'
    ));
}

    // ----------------------------------------
    // Store Patient
    // ----------------------------------------
    public function create()
    {
    $pageTitle='Add a Patient';  
        // flag tells where this form is used

    return view('patients.create', compact('pageTitle'));
    }
  
   public function store(Request $request)
{
    $validated = $request->validate([
        'patient_id'   => 'nullable|exists:patients,id',
        'name'         => 'required|string|max:255',
        'email'        => 'nullable|email|max:255',
        'gender'       => 'required|in:M,F',
        'age'          => 'required|integer|min:0|max:120',
        'bookingfor'   => 'required|string',
        'other_reason' => 'nullable|string|max:255',
        'country_code' => 'nullable|string',
        'phone_number' => 'required|string',
        'action'       => 'nullable|string', // default or appointment
    ]);

   
    try {
        if (($validated['action'] ?? 'default') === 'default') {
           try {
  // Debug: check if code reaches here

    // =========================
    // DEFAULT REGISTRATION
    // =========================
    $existingCount = Patient::where('mobile', $validated['phone_number'])->count();

    if ($existingCount >= 4) {
       
        return redirect()->back()
            ->withInput()
            ->withErrors(['phone_number' => 'Maximum 4 patients already exist with this phone number.']);
    }

    $patient = DB::transaction(function () use ($validated, $request) {
        $user = User::firstOrCreate(
            ['phone' => $validated['phone_number']],
            [
                'name'  => $validated['name'],
                'email' => $validated['email'] ?? null,
                'isd'   => $validated['country_code'] ?? null,
                'role'  => 4,
            ]
        );

        $isPrimary = ! Patient::where('user_id', $user->id)->exists();

        return Patient::create([
            'user_id'             => $user->id,
            'name'                => $validated['name'],
            'email'               => $validated['email'] ?? null,
            'mobile'              => $validated['phone_number'],
            'country_code'        => $validated['country_code'] ?? null,
            'gender'              => $validated['gender'],
            'age'                 => $validated['age'],
            'bookingfor'          => $validated['bookingfor'],
            'other_reason'        => $validated['other_reason'] ?? null,
            'ipAddress'           => $request->ip(),
            'is_primary_account'  => $isPrimary,
            'registration_source' => 'default',
            'stage'               => 'patient_created',
            'stages'              => json_encode([
                'patient_created' => now()->toDateTimeString(),
                'doctor_slot_selected' => null,
                'payment_received' => null,
            ]),
        ]);
    });



    return redirect()->route('patients.index')
        ->with('success', 'Patient created successfully.');

} catch (\Exception $e) {
    // Debug the exception



    return redirect()->back()
        ->withInput()
        ->withErrors(['error' => 'Something went wrong while creating the patient.']);
}

        } else {
        
            // =========================
            // APPOINTMENT REGISTRATION
            // =========================
            $patient = null;

            if (!empty($validated['patient_id'])) {
                // Use existing patient and update details
                $patient = Patient::findOrFail($validated['patient_id']);
                $patient->update([
                    'name'         => $validated['name'],
                    'email'        => $validated['email'] ?? null,
                    'gender'       => $validated['gender'],
                    'age'          => $validated['age'],
                    'bookingfor'   => $validated['bookingfor'],
                    'other_reason' => $validated['other_reason'] ?? null,
                    'country_code' => $validated['country_code'] ?? null,
                    'mobile'       => $validated['phone_number'],
                ]);
            } else {
                // Create new patient
                $patient = DB::transaction(function () use ($validated, $request) {
                    $user = User::firstOrCreate(
                        ['phone' => $validated['phone_number']],
                        [
                            'name'  => $validated['name'],
                            'email' => $validated['email'] ?? null,
                            'isd'   => $validated['country_code'] ?? null,
                            'role'  => 4,
                        ]
                    );

                    $isPrimary = ! Patient::where('user_id', $user->id)->exists();

                    return Patient::create([
                        'user_id'             => $user->id,
                        'name'                => $validated['name'],
                        'email'               => $validated['email'] ?? null,
                        'mobile'              => $validated['phone_number'],
                        'country_code'        => $validated['country_code'] ?? null,
                        'gender'              => $validated['gender'],
                        'age'                 => $validated['age'],
                        'bookingfor'          => $validated['bookingfor'],
                        'other_reason'        => $validated['other_reason'] ?? null,
                        'ipAddress'           => $request->ip(),
                        'is_primary_account'  => $isPrimary,
                        'registration_source' => 'appointment',
                        'stage'               => 'patient_created',
                        'stages'              => json_encode([
                            'patient_created' => now()->toDateTimeString(),
                            'doctor_slot_selected' => null,
                            'payment_received' => null,
                        ]),
                    ]);
                });
            }

            return redirect()
                ->to(url("manualappointment/doctorslotchoose/{$patient->id}"))
                ->with('success', 'Patient data saved. Continue to book appointment.');
        }

    } catch (\Exception $e) {
  
        \Log::error('Patient Store Error: '.$e->getMessage(), ['request' => $request->all()]);

        return redirect()->back()
            ->withInput()
            ->withErrors(['error' => 'Something went wrong while creating/updating the patient.']);
    }
}


    // ----------------------------------------
    // Edit
    // ----------------------------------------
 public function edit(Request $request)
{
    $patientId = Crypt::decryptString($request->ID);
    $patient = Patient::findOrFail($patientId);
    $pageTitle="Update $patient->name Profile";
    $patients = Patient::all(); // still need table list
    return view('patients.create', compact('patients', 'patient','pageTitle'));
}

    // ----------------------------------------
    // Update
    // ----------------------------------------
   public function update(Request $request, $id)
{
    $patient = Patient::findOrFail($id);
 

    // Validate request
    $validated = $request->validate([
    
        'name'         => 'required|string|max:255',
        'email'        => 'nullable|email|max:255',
        'gender'       => 'required|in:M,F',
        'age'          => 'required|integer|min:0|max:120',
        'bookingfor'   => 'required|string',
        'other_reason' => 'nullable|string|max:255',
        'country_code' => 'nullable|string',
        'phone_number' => 'required|string', // from intl-tel-input hidden field
    ]);

    // Update patient
    $patient->update([
        'name'         => $validated['name'],
        'email'        => $validated['email'] ?? null,
        'mobile'       => $validated['phone_number'], // number without country code
        'country_code' => $validated['country_code'] ?? null,
        'gender'       => $validated['gender'],
        'age'          => $validated['age'],
        'bookingfor'   => $validated['bookingfor'],
        'other_reason' => $validated['other_reason'] ?? null,
        'ipAddress'    => $request->ip()
  
    ]);

    // Redirect back to patients list with success message
    return redirect()->route('patients.index')->with('success', 'Patient updated successfully');
}

    // ----------------------------------------
    // Delete
    // ----------------------------------------
public function delete(Request $request)
{
    $request->validate([
        'ID' => 'required'
    ]);

    $response = [
        'success'  => false,
        'message'  => 'Invalid request.',
        'redirect' => null
    ];

    try {

        DB::transaction(function () use ($request, &$response) {

            $patientId = Crypt::decryptString($request->ID);
            $patient   = Patient::find($patientId);

            if (! $patient) {
                $response['message'] = 'Patient record not found.';
                return;
            }

            $userId = $patient->user_id;

            /* =========================
               🔒 PREVENT PRIMARY DELETE
            ========================= */
            if (
                $patient->is_primary_account &&
                Patient::where('user_id', $userId)->count() > 1
            ) {
                $response['message'] =
                    'Primary account holder cannot be deleted while family members exist.';
                return;
            }

            /* =========================
               DELETE PATIENT
            ========================= */
            $patient->delete();

            /* =========================
               DELETE USER IF NO PATIENTS
            ========================= */
            if (! Patient::where('user_id', $userId)->exists()) {
                User::where('id', $userId)->delete();
            }

            $response = [
                'success'  => true,
                'message'  => 'Patient deleted successfully.',
                'redirect' => $request->filled('redirecturl')
                    ? $request->redirecturl
                    : url()->previous()
            ];
        });

    } catch (\Throwable $e) {

        Log::error('Patient Delete Error', [
            'error' => $e->getMessage()
        ]);

        $response['message'] = 'Something went wrong while deleting patient.';
    }

    /* =========================
       FINAL RESPONSE (ALWAYS)
    ========================= */
    return response()->json($response);
}

public function getByPhone(Request $request)
{
    $request->validate([
        'country_code' => 'required',
        'phone' => 'required'
    ]);

    $patients = Patient::where('country_code', $request->country_code)
        ->where('mobile', $request->phone)
        ->get([
            'id','name','email','age','gender','bookingfor','other_reason'
        ]);

    return response()->json([
        'count' => $patients->count(),
        'patients' => $patients
    ]);
}

}
