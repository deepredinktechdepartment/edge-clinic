<?php
namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Encryption\DecryptException;

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
    // Validate the request
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

    // Create patient
    Patient::create([
        'name'         => $validated['name'],
        'email'        => $validated['email'] ?? null,
        'mobile'       => $validated['phone_number'], // number without country code
        'country_code' => $validated['country_code'] ?? null,
        'gender'       => $validated['gender'],
        'age'          => $validated['age'],
        'bookingfor'   => $validated['bookingfor'],
        'other_reason' => $validated['other_reason'] ?? null,
        'ipAddress'    => $request->ip(),
       
    ]);

    // Redirect back to patients list with success message
    return redirect()->route('patients.index')->with('success', 'Patient created successfully');
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
    // ✅ Validate input
    $request->validate([
        'ID' => 'required'
    ]);

    try {

        // ✅ Decrypt ID safely
        $patientId = Crypt::decryptString($request->ID);

        // ✅ Check if patient exists
        $patient = Patient::where('id', $patientId)->first();

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient record not found or already deleted.'
            ], 404);
        }

        // ✅ Delete patient
        $patient->delete();

        // ✅ Redirect handling
        $redirect = $request->filled('redirecturl')
            ? $request->redirecturl
            : url()->previous();

        return response()->json([
            'success'  => true,
            'message'  => 'Patient deleted successfully.',
            'redirect' => $redirect
        ]);

    } catch (DecryptException $e) {

        // ❌ Invalid encrypted ID
        Log::warning('Patient Delete: Invalid ID', [
            'id' => $request->ID
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Invalid delete request.'
        ], 400);

    } catch (\Throwable $e) {

        // ❌ Any other unexpected error
        Log::error('Patient Delete Error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong while deleting patient.'
        ], 500);
    }
}
}
