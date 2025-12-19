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
use Barryvdh\DomPDF\Facade\Pdf; // ✅ CORRECT


class DoctorPaymentController extends Controller
{

    // --------------------------------------------------------------
    // Show payment report
    // --------------------------------------------------------------
   public function index(Request $request)
{
    $pageTitle = "Payments";

    // ------------------------------------------------
    // 📅 DEFAULT DATE = TODAY
    // ------------------------------------------------
    $fromDate = $request->from_date ?? now()->toDateString();
    $toDate   = $request->to_date ?? now()->toDateString();

    // ------------------------------------------------
    // 🔗 BASE QUERY (PAYMENTS TABLE ONLY)
    // ------------------------------------------------
    $baseQuery = Payment::query()
        ->leftJoin('doctors', 'doctors.id', '=', 'payments.doctor_id')
        ->leftJoin('patients', 'patients.id', '=', 'payments.patient_id');

    // ------------------------------------------------
    // 🔍 FILTERS
    // ------------------------------------------------
    if ($request->filled('doctor')) {
        $baseQuery->where('payments.doctor_id', $request->doctor);
    }

    // ✅ Date filter (datetime-safe)
    $baseQuery->whereBetween(
        DB::raw('DATE(payments.created_at)'),
        [$fromDate, $toDate]
    );

    if ($request->filled('payment_status')) {
        if ($request->payment_status === 'success') {
            $baseQuery->where('payments.status', 'Authorized');
        } elseif ($request->payment_status === 'failed') {
            $baseQuery->where('payments.status', '!=', 'Authorized');
        }
    }

    // ------------------------------------------------
    // 📊 SUMMARY
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
        // 📋 PAYMENT LIST
        // ------------------------------------------------
        'successPayments' => (clone $baseQuery)
            ->select([
                'payments.id as payment_row_id',
                'payments.payment_id',
                'payments.mocdoc_apptkey as appointment_no',
                'payments.aptDate as appointment_date',
                'payments.aptTime as appointment_time',
                'payments.amount',
                'payments.currency',
                'payments.status',
                'payments.created_at',

                'doctors.name as doctor_name',

                'patients.name as patient_name',
                'patients.email as patient_email',
                'patients.mobile as patient_phone',
            ])
            ->orderBy('payments.created_at', 'desc')
            ->get(),
    ];

    $doctors = $this->getDoctors();

    return view(
        'payment.report',
        compact('pageTitle', 'summaryData', 'doctors', 'fromDate', 'toDate')
    );
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

    // ----------------------------
    // DATE FILTER FOR TABLE
    // ----------------------------
    $fromDate = $request->from_date ?? now()->toDateString();
    $toDate   = $request->to_date ?? now()->toDateString();

    // ----------------------------
    // BASE QUERY
    // ----------------------------
    $baseQuery = Payment::query()
        ->whereNotNull('payments.mocdoc_apptkey')
        ->leftJoin('doctors', 'doctors.id', '=', 'payments.doctor_id')
        ->leftJoin('patients', 'patients.id', '=', 'payments.patient_id');

    if ($request->filled('doctor')) {
        $baseQuery->where('payments.doctor_id', $request->doctor);
    }

    if ($request->filled('payment_status')) {
        if ($request->payment_status === 'success') {
            $baseQuery->where('payments.status', 'Authorized');
        } elseif ($request->payment_status === 'failed') {
            $baseQuery->where('payments.status', '!=', 'Authorized');
        }
    }

    // ----------------------------
    // DATE RANGES
    // ----------------------------
    $today = Carbon::today()->toDateString();
    $monthStart = Carbon::now()->startOfMonth()->toDateString();
    $monthEnd   = Carbon::now()->endOfMonth()->toDateString();

    // ----------------------------
    // DASHBOARD CARD DATA
    // ----------------------------
    $cardData = [

        'total_appointments' => [
            'today' => (clone $baseQuery)
                ->whereDate('payments.created_at', $today)
                ->count(),

            'month' => (clone $baseQuery)
                ->whereBetween(DB::raw('DATE(payments.created_at)'), [$monthStart, $monthEnd])
                ->count(),
        ],

        'paid_appointments' => [
            'today' => (clone $baseQuery)
                ->whereDate('payments.created_at', $today)
                ->where('payments.status', 'Authorized')
                ->count(),

            'month' => (clone $baseQuery)
                ->whereBetween(DB::raw('DATE(payments.created_at)'), [$monthStart, $monthEnd])
                ->where('payments.status', 'Authorized')
                ->count(),
        ],

        'failed_appointments' => [
            'today' => (clone $baseQuery)
                ->whereDate('payments.created_at', $today)
                ->where('payments.status', '!=', 'Authorized')
                ->count(),

            'month' => (clone $baseQuery)
                ->whereBetween(DB::raw('DATE(payments.created_at)'), [$monthStart, $monthEnd])
                ->where('payments.status', '!=', 'Authorized')
                ->count(),
        ],

        'total_revenue' => [
            'today' => (clone $baseQuery)
                ->whereDate('payments.created_at', $today)
                ->where('payments.status', 'Authorized')
                ->sum('payments.amount'),

            'month' => (clone $baseQuery)
                ->whereBetween(DB::raw('DATE(payments.created_at)'), [$monthStart, $monthEnd])
                ->where('payments.status', 'Authorized')
                ->sum('payments.amount'),
        ],
    ];

    // ----------------------------
    // TABLE DATA
    // ----------------------------
    $appointments = (clone $baseQuery)
        ->whereBetween(DB::raw('DATE(payments.created_at)'), [$fromDate, $toDate])
        ->select([
            'payments.id',
            'payments.mocdoc_apptkey as appointment_no',
            'payments.aptDate',
            'payments.aptTime',
            'payments.amount',
            'payments.status',
            'payments.created_at',
            'doctors.name as doctor_name',
            'patients.name as patient_name',
            'patients.mobile as patient_phone',
        ])
        ->orderBy('payments.created_at', 'desc')
        ->get();

    $doctors = $this->getDoctors();

    return view(
        'admin.appointments.appointments_list',
        compact(
            'pageTitle',
            'appointments',
            'cardData',
            'doctors',
            'fromDate',
            'toDate'
        )
    );
}



public function appointmentsReportPdf(Request $request)
{
    // ------------------------------------------------
    // 📅 DEFAULT DATE = TODAY
    // ------------------------------------------------
    $fromDate = $request->from_date ?? now()->toDateString();
    $toDate   = $request->to_date ?? now()->toDateString();

    // ------------------------------------------------
    // 🔗 BASE QUERY (PAYMENTS TABLE)
    // ------------------------------------------------
    $query = Payment::query()
        ->whereNotNull('payments.mocdoc_apptkey')
        ->leftJoin('doctors', 'doctors.id', '=', 'payments.doctor_id')
        ->leftJoin('patients', 'patients.id', '=', 'payments.patient_id');

    // ------------------------------------------------
    // 🔍 FILTERS
    // ------------------------------------------------
    if ($request->filled('doctor')) {
        $query->where('payments.doctor_id', $request->doctor);
    }

    $query->whereBetween(
        DB::raw('DATE(payments.created_at)'),
        [$fromDate, $toDate]
    );

    if ($request->filled('payment_status')) {
        if ($request->payment_status === 'success') {
            $query->where('payments.status', 'Authorized');
        } elseif ($request->payment_status === 'failed') {
            $query->where('payments.status', '!=', 'Authorized');
        }
    }

    // ------------------------------------------------
    // 📋 FETCH DATA
    // ------------------------------------------------
    $appointments = $query
        ->select([
            'payments.mocdoc_apptkey as appointment_no',
            'payments.aptDate as appointment_date',
            'payments.aptTime as appointment_time',
            'payments.amount',

            'doctors.id as doctor_id',
            'doctors.name as doctor_name',

            'patients.name as patient_name',
            'patients.mobile as patient_phone',

            'payments.status as payment_status',
            'payments.created_at as payment_date',
        ])
        ->orderBy('doctors.name')
        ->orderBy('payments.created_at', 'desc')
        ->get();

    // ------------------------------------------------
    // 📦 GROUP BY DOCTOR
    // ------------------------------------------------
    $groupedAppointments = $appointments->groupBy('doctor_id');

    // ------------------------------------------------
    // 📄 GENERATE PDF
    // ------------------------------------------------
    $pdf = Pdf::loadView(
        'admin.appointments.pdf',
        compact('groupedAppointments', 'fromDate', 'toDate')
    )->setPaper('A4', 'portrait');

    return $pdf->download(
        'appointments-report-' . now()->format('d-m-Y') . '.pdf'
    );
}


public function appointmentsReportPrint(Request $request)
{
    $fromDate = $request->from_date ?? now()->toDateString();
    $toDate   = $request->to_date ?? now()->toDateString();

    $appointments = Payment::query()
        ->whereNotNull('payments.mocdoc_apptkey')
        ->leftJoin('doctors', 'doctors.id', '=', 'payments.doctor_id')
        ->leftJoin('patients', 'patients.id', '=', 'payments.patient_id')
        ->whereBetween(
            DB::raw('DATE(payments.created_at)'),
            [$fromDate, $toDate]
        )
        ->select([
            'payments.mocdoc_apptkey as appointment_no',
            'payments.aptDate as appointment_date',
            'payments.aptTime as appointment_time',
            'payments.amount',

            'doctors.id as doctor_id',
            'doctors.name as doctor_name',

            'patients.name as patient_name',
            'patients.mobile as patient_phone',

            'payments.status as payment_status',
            'payments.created_at as payment_date',
        ])
        ->orderBy('doctors.name')
        ->orderBy('payments.created_at', 'desc')
        ->get()
        ->groupBy('doctor_id');

    return view(
        'admin.appointments.print',
        compact('appointments', 'fromDate', 'toDate')
    );
}


public function paymentReportPdf(Request $request)
{
    // ------------------------------------------------
    // 📅 DEFAULT DATE = TODAY
    // ------------------------------------------------
    $fromDate = $request->from_date ?? now()->toDateString();
    $toDate   = $request->to_date ?? now()->toDateString();

    // ------------------------------------------------
    // 🔗 BASE QUERY (PAYMENTS TABLE ONLY)
    // ------------------------------------------------
    $query = Payment::query()
        ->leftJoin('doctors', 'doctors.id', '=', 'payments.doctor_id')
        ->leftJoin('patients', 'patients.id', '=', 'payments.patient_id');

    // ------------------------------------------------
    // 🔍 FILTERS (SAME AS INDEX)
    // ------------------------------------------------
    if ($request->filled('doctor')) {
        $query->where('payments.doctor_id', $request->doctor);
    }

    // Datetime-safe date filter
    $query->whereBetween(
        DB::raw('DATE(payments.created_at)'),
        [$fromDate, $toDate]
    );

    if ($request->filled('payment_status')) {
        if ($request->payment_status === 'success') {
            $query->where('payments.status', 'Authorized');
        } elseif ($request->payment_status === 'failed') {
            $query->where('payments.status', '!=', 'Authorized');
        }
    }

    // ------------------------------------------------
    // 📋 FETCH DATA
    // ------------------------------------------------
    $payments = $query
        ->select([
            'payments.payment_id',
            'payments.mocdoc_apptkey as appointment_no',
            'payments.aptDate as appointment_date',
            'payments.aptTime as appointment_time',
            'payments.amount',
            'payments.status',
            'payments.created_at',

            'doctors.id as doctor_id',
            'doctors.name as doctor_name',

            'patients.name as patient_name',
            'patients.email as patient_email',
            'patients.mobile as patient_phone',
        ])
        ->orderBy('doctors.name')
        ->orderBy('payments.created_at', 'desc')
        ->get();

    // ------------------------------------------------
    // 📦 GROUP BY DOCTOR
    // ------------------------------------------------
    $groupedPayments = $payments->groupBy('doctor_id');

    // ------------------------------------------------
    // 📄 GENERATE PDF
    // ------------------------------------------------
    $pdf = Pdf::loadView(
        'payment.report_pdf',
        compact('groupedPayments', 'fromDate', 'toDate')
    )->setPaper('A4', 'portrait');

    return $pdf->download(
        'payment-report-' . now()->format('d-m-Y') . '.pdf'
    );
}


}
