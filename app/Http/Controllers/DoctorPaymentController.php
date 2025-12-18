<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mail;
use Config;
use Validator;
use Auth;
use Session;
use App\Services\MocDocService;
use Carbon\Carbon;

class DoctorPaymentController extends Controller
{

    // --------------------------------------------------------------
    // Show payment report
    // --------------------------------------------------------------
   public function index(Request $request)
{
    $pageTitle = "Payment Reports";

    // ------------------------------------------------
    // 🔗 Base query with CORRECT joins
    // ------------------------------------------------
    $baseQuery = Payment::query()
        ->leftJoin('appointments', 'appointments.payment_id', '=', 'payments.payment_id')
        ->leftJoin('doctors', 'doctors.id', '=', 'appointments.doctor_id')
        ->leftJoin('patients', 'patients.id', '=', 'appointments.patient_id');

    // ------------------------------------------------
    // 🔍 Filters
    // ------------------------------------------------
    if ($request->filled('doctor')) {
        $baseQuery->where('appointments.doctor_id', $request->doctor);
    }

    if ($request->filled('from_date')) {
        $baseQuery->whereDate('payments.created_at', '>=', $request->from_date);
    }

    if ($request->filled('to_date')) {
        $baseQuery->whereDate('payments.created_at', '<=', $request->to_date);
    }

    if ($request->filled('payment_status')) {
        if ($request->payment_status === 'success') {
            $baseQuery->where('payments.status', 'Authorized');
        } elseif ($request->payment_status === 'failed') {
            $baseQuery->where('payments.status', '!=', 'Authorized');
        }
    }

    // ------------------------------------------------
    // 📊 Summary
    // ------------------------------------------------
    $summaryData = [
        'success_count' => (clone $baseQuery)
            ->where('payments.status', 'Authorized')
            ->count(),

        'success_amount' => (clone $baseQuery)
            ->where('payments.status', 'Authorized')
            ->sum('payments.amount'),

        'failed_count' => (clone $baseQuery)
            ->where('payments.status', '!=', 'Authorized')
            ->count(),

        'failed_amount' => (clone $baseQuery)
            ->where('payments.status', '!=', 'Authorized')
            ->sum('payments.amount'),

        // ------------------------------------------------
        // 📋 Success Payments
        // ------------------------------------------------
        'successPayments' => (clone $baseQuery)
            ->where('payments.status', 'Authorized')
            ->select([
                'payments.id as payment_row_id',
                'payments.payment_id',
                'payments.amount',
                'payments.currency',
                'payments.status',
                'payments.created_at',

                'appointments.appointment_no',
                'appointments.date',
                'appointments.time_slot',

                'doctors.name as doctor_name',

                'patients.name as patient_name',
                'patients.email as patient_email',
                'patients.mobile as patient_phone',
            ])
            ->orderBy('payments.created_at', 'desc')
            ->get(),

        // ------------------------------------------------
        // 📋 Failed Payments
        // ------------------------------------------------
        'failedPayments' => (clone $baseQuery)
            ->where('payments.status', '!=', 'Authorized')
            ->select([
                'payments.id as payment_row_id',
                'payments.payment_id',
                'payments.amount',
                'payments.currency',
                'payments.status',
                'payments.created_at',

                'appointments.appointment_no',
                'appointments.date',
                'appointments.time_slot',

                'doctors.name as doctor_name',

                'patients.name as patient_name',
                'patients.email as patient_email',
                'patients.mobile as patient_phone',
            ])
            ->orderBy('payments.created_at', 'desc')
            ->get(),
    ];

    $doctors = $this->getDoctors();

    return view('payment.report', compact('pageTitle', 'summaryData', 'doctors'));
}





    private function getDoctors()
    {
        $doctors_data = Doctor::leftJoin('departments', 'departments.id', '=', 'doctors.department_id')
            ->orderBy('doctors.department_id', 'ASC')
            ->orderBy('doctors.is_active', 'DESC')
            ->orderBy('doctors.sort_order', 'ASC')
            ->get(['doctors.id', 'doctors.name', 'departments.dept_name']);

        // Map for consistency with previous dummy structure
        return $doctors_data->map(function ($doc) {
            return [
                'id'   => $doc->id,
                'name' => $doc->name,
            ];
        })->toArray();
    }

}
