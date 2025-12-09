<?php
namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    // ----------------------------------------
    // List Page
    // ----------------------------------------
    public function index()
    {
        $patients = Patient::latest()->get();
        return view('patients.index', compact('patients'));
    }

    // ----------------------------------------
    // Store Patient
    // ----------------------------------------
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_code' => 'required|unique:patients',
            'name' => 'required',
        ]);

        Patient::create($validated + $request->except('_token'));

        return response()->json(['success' => true, 'msg' => 'Patient created successfully']);
    }

    // ----------------------------------------
    // Edit
    // ----------------------------------------
    public function edit($id)
    {
        return Patient::findOrFail($id);
    }

    // ----------------------------------------
    // Update
    // ----------------------------------------
    public function update(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);

        $validated = $request->validate([
            'patient_code' => 'required|unique:patients,patient_code,' . $id,
            'name' => 'required',
        ]);

        $patient->update($validated + $request->except('_token'));

        return response()->json(['success' => true, 'msg' => 'Patient updated successfully']);
    }

    // ----------------------------------------
    // Delete
    // ----------------------------------------
    public function delete($id)
    {
        Patient::findOrFail($id)->delete();
        return response()->json(['success' => true, 'msg' => 'Patient deleted']);
    }
}
