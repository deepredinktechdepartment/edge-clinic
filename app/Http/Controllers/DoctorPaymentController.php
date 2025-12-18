<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Appointment;
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
use Barryvdh\DomPDF\Facade\Pdf;


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



    public function appointments_list(Request $request)
{
    $pageTitle = "Appointments";

    // ------------------------------------------------
    // 📅 DEFAULT DATE = TODAY
    // ------------------------------------------------
    $fromDate = $request->from_date ?? now()->toDateString();
    $toDate   = $request->to_date ?? now()->toDateString();

    // ------------------------------------------------
    // 🔗 Base Query (APPOINTMENTS FIRST)
    // ------------------------------------------------
    $baseQuery = Appointment::query()
        ->leftJoin('payments', 'payments.payment_id', '=', 'appointments.payment_id')
        ->leftJoin('doctors', 'doctors.id', '=', 'appointments.doctor_id')
        ->leftJoin('patients', 'patients.id', '=', 'appointments.patient_id');

    // ------------------------------------------------
    // 🔍 Filters
    // ------------------------------------------------
    if ($request->filled('doctor')) {
        $baseQuery->where('appointments.doctor_id', $request->doctor);
    }

    // ✅ Always apply date filter (default = today)
    $baseQuery->whereBetween('appointments.date', [$fromDate, $toDate]);

    if ($request->filled('payment_status')) {
        if ($request->payment_status === 'success') {
            $baseQuery->where('payments.status', 'Authorized');
        } elseif ($request->payment_status === 'failed') {
            $baseQuery->where(function ($q) {
                $q->whereNull('payments.status')
                  ->orWhere('payments.status', '!=', 'Authorized');
            });
        }
    }

    // ------------------------------------------------
    // 📊 SUMMARY CARDS
    // ------------------------------------------------
    $summaryData = [
        'total_appointments' => (clone $baseQuery)->count(),

        'paid_appointments' => (clone $baseQuery)
            ->where('payments.status', 'Authorized')
            ->count(),

        'failed_appointments' => (clone $baseQuery)
            ->where(function ($q) {
                $q->whereNull('payments.status')
                  ->orWhere('payments.status', '!=', 'Authorized');
            })
            ->count(),

        'total_revenue' => (clone $baseQuery)
            ->where('payments.status', 'Authorized')
            ->sum('appointments.fee'),

        'appointments' => (clone $baseQuery)
            ->select([
                'appointments.id',
                'appointments.appointment_no',
                'appointments.date',
                'appointments.time_slot',
                'appointments.fee',

                'doctors.name as doctor_name',

                'patients.name as patient_name',
                'patients.email as patient_email',
                'patients.mobile as patient_phone',

                'payments.payment_id',
                'payments.status as payment_status',
                'payments.created_at as payment_date',
            ])
            ->orderBy('appointments.date', 'desc')
            ->get(),
    ];

    $doctors = $this->getDoctors();

    return view(
        'admin.appointments.appointments_list',
        compact('pageTitle', 'summaryData', 'doctors', 'fromDate', 'toDate')
    );
}

use Barryvdh\DomPDF\Facade\Pdf;

public function appointmentsReportPdf(Request $request)
{
    // ------------------------------------------------
    // 📅 Default date = today
    // ------------------------------------------------
    $fromDate = $request->from_date ?? now()->toDateString();
    $toDate   = $request->to_date ?? now()->toDateString();

    // ------------------------------------------------
    // 🔗 Base Query
    // ------------------------------------------------
    $query = Appointment::query()
        ->leftJoin('payments', 'payments.payment_id', '=', 'appointments.payment_id')
        ->leftJoin('doctors', 'doctors.id', '=', 'appointments.doctor_id')
        ->leftJoin('patients', 'patients.id', '=', 'appointments.patient_id')
        ->whereBetween('appointments.date', [$fromDate, $toDate]);

    if ($request->filled('doctor')) {
        $query->where('appointments.doctor_id', $request->doctor);
    }

    if ($request->filled('payment_status')) {
        if ($request->payment_status === 'success') {
            $query->where('payments.status', 'Authorized');
        } elseif ($request->payment_status === 'failed') {
            $query->where(function ($q) {
                $q->whereNull('payments.status')
                  ->orWhere('payments.status', '!=', 'Authorized');
            });
        }
    }

    // ------------------------------------------------
    // 📋 Fetch Data
    // ------------------------------------------------
    $appointments = $query
        ->select([
            'appointments.appointment_no',
            'appointments.date',
            'appointments.time_slot',
            'appointments.fee',

            'doctors.id as doctor_id',
            'doctors.name as doctor_name',

            'patients.name as patient_name',
            'patients.mobile as patient_phone',

            'payments.status as payment_status',
        ])
        ->orderBy('doctors.name')
        ->orderBy('appointments.date')
        ->get();

    // ------------------------------------------------
    // 📦 GROUP BY DOCTOR
    // ------------------------------------------------
    $groupedAppointments = $appointments->groupBy('doctor_id');

    // ------------------------------------------------
    // 📄 Generate PDF
    // ------------------------------------------------
    $pdf = Pdf::loadView(
        'admin.appointments.pdf',
        compact('groupedAppointments', 'fromDate', 'toDate')
    )->setPaper('A4', 'portrait');

    return $pdf->download(
        'appointments-report-' . now()->format('d-m-Y') . '.pdf'
    );
}



}
