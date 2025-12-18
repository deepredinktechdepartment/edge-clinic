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
    return view('patients.create', compact('pageTitle'));
    }
  public function store(Request $request)
{
    $validated = $request->validate([
        'name'         => 'required|string|max:255',
        'email'        => 'nullable|email|max:255',
        'gender'       => 'required|in:M,F',
        'age'          => 'required|integer|min:0|max:120',
        'bookingfor'   => 'required|string',
        'other_reason' => 'nullable|string|max:255',
        'country_code' => 'nullable|string',
        'phone_number' => 'required|string',
    ]);

    DB::transaction(function () use ($validated, $request) {

        /* =========================
           CREATE / GET USER
        ========================= */
        $user = User::firstOrCreate(
            ['phone' => $validated['phone_number']],
            [
                'name'  => $validated['name'],
                'email' => $validated['email'] ?? null,
                'isd'   => $validated['country_code'] ?? null,
                'role'  => 4,
            ]
        );

        /* =========================
           CHECK PRIMARY ACCOUNT
        ========================= */
        $isPrimary = ! Patient::where('user_id', $user->id)->exists();

        /* =========================
           CREATE PATIENT
        ========================= */
        Patient::create([
            'user_id'            => $user->id,
            'name'               => $validated['name'],
            'email'              => $validated['email'] ?? null,
            'mobile'             => $validated['phone_number'],
            'country_code'       => $validated['country_code'] ?? null,
            'gender'             => $validated['gender'],
            'age'                => $validated['age'],
            'bookingfor'         => $validated['bookingfor'],
            'other_reason'       => $validated['other_reason'] ?? null,
            'ipAddress'          => $request->ip(),
            'is_primary_account' => $isPrimary, // ✅ HERE
        ]);
    });

    return redirect()
        ->route('patients.index')
        ->with('success', 'Patient created successfully');
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

    try {

        DB::transaction(function () use ($request, &$redirect, &$response) {

            $patientId = Crypt::decryptString($request->ID);

            $patient = Patient::find($patientId);

            if (! $patient) {
                throw new \Exception('Patient not found');
            }

            $userId = $patient->user_id;

            /* =========================
               🔒 PREVENT PRIMARY DELETE
            ========================= */
            if (
                $patient->is_primary_account &&
                Patient::where('user_id', $userId)->count() > 1
            ) {
                throw new \Exception(
                    'Primary account holder cannot be deleted while family members exist.'
                );
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

            $redirect = $request->filled('redirecturl')
                ? $request->redirecturl
                : url()->previous();

            $response = [
                'success'  => true,
                'message'  => 'Patient deleted successfully.',
                'redirect' => $redirect
            ];
        });

        return response()->json($response);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 403);

    } catch (\Throwable $e) {

        Log::error('Patient Delete Error', [
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong while deleting patient.'
        ], 500);
    }
}

}
