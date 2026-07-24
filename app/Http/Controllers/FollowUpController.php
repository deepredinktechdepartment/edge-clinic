<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FollowUpController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(in_array((int) auth()->user()?->role, [1, 3], true), 403);

        $fromDate = $request->input('from_date', now()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        $query = DB::table('payments')
            ->leftJoin('patients', 'patients.id', '=', 'payments.patient_id')
            ->leftJoin('doctors', 'doctors.id', '=', 'payments.doctor_id')
            ->leftJoin('sources', 'sources.id', '=', 'payments.source_id')
            ->where('payments.type', 'appointment')
            ->whereNotNull('payments.follow_up_date')
            ->whereBetween('payments.follow_up_date', [$fromDate, $toDate]);

        if ($request->filled('doctor')) {
            $query->where('payments.doctor_id', $request->doctor);
        }

        if ($request->filled('source_id')) {
            $query->where('payments.source_id', $request->source_id);
        }

        $followUps = $query->select([
            'payments.id as payment_id', 'payments.mocdoc_apptkey as appointment_no',
            'payments.aptDate as last_appointment_date', 'payments.aptTime as last_appointment_time',
            'payments.follow_up_date', 'payments.appointment_status', 'payments.remarks',
            'patients.name as patient_name', 'patients.mobile as patient_mobile', 'patients.email as patient_email',
            'doctors.name as doctor_name', 'sources.name as source_name',
        ])->orderBy('payments.follow_up_date')->orderBy('doctors.name')->get();

        return view('followups.index', [
            'pageTitle' => 'Follow-ups',
            'followUps' => $followUps,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'doctors' => Doctor::orderBy('name')->get(['id', 'name']),
            'sources' => Source::where('status', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
