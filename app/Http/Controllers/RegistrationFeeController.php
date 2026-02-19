<?php
namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use App\Models\RegistrationFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\DB;
use Mail;
use Config;
use Validator;
use Auth;
use Session;

class RegistrationFeeController extends Controller
{
    public function index()
    {
        $fees = RegistrationFee::latest()->get();
        $pageTitle = "Registration Fee";

        // Show Add only if no registration fee exists
        $addlink = $fees->count() === 0
            ? route('admin.registration-fees.create')
            : null;

        return view(
            'admin.registration_fees.index',
            compact('fees', 'pageTitle', 'addlink')
        );
    }


    public function create()
    {
        $pageTitle = "Add Registration Fee";
        return view('admin.registration_fees.create', compact('pageTitle'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        if (!empty($data['is_active'])) {
            RegistrationFee::where('is_active', true)->update(['is_active' => false]);
        }

        RegistrationFee::create($data);

        return redirect()->route('admin.registration-fees.index')
            ->with('success', 'Registration Fee Created Successfully');
    }

    public function edit(RegistrationFee $registrationFee)
    {
        $pageTitle = "Edit Registration Fee";
        return view('admin.registration_fees.create', compact('registrationFee','pageTitle'));
    }

    public function update(Request $request, RegistrationFee $registrationFee)
    {
        $data = $this->validateData($request);

        if (!empty($data['is_active'])) {
            RegistrationFee::where('is_active', true)
                ->where('id', '!=', $registrationFee->id)
                ->update(['is_active' => false]);
        }

        $registrationFee->update($data);

        return redirect()->route('admin.registration-fees.index')
            ->with('success', 'Registration Fee Updated Successfully');
    }

    public function changeStatus(RegistrationFee $registrationFee)
    {
        RegistrationFee::where('is_active', true)->update(['is_active' => false]);
        $registrationFee->update(['is_active' => !$registrationFee->is_active]);

        return back()->with('success', 'Status Updated');
    }

    private function validateData(Request $request)
    {
        return $request->validate([
            'amount' => 'required|numeric|min:0',
            'validity_days' => 'required|integer|min:1',
            'discount_type' => 'nullable|in:percent,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);
    }
}
