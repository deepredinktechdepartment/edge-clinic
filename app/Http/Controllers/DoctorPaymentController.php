<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\AppointmentStatusLog;
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
use App\Services\Sms\NettyfishSmsService;



class DoctorPaymentController extends Controller
{

    // --------------------------------------------------------------
    // Show payment report
    // --------------------------------------------------------------
   public function index(Request $request)
{
    $pageTitle = "Payments";

    $fromDate = $request->from_date ?? now()->toDateString();
    $toDate   = $request->to_date ?? now()->toDateString();

    // ----------------------------
    // BASE QUERY
    // ----------------------------
    $baseQuery = Payment::query()
        ->leftJoin('doctors', 'doctors.id', '=', 'payments.doctor_id')
        ->leftJoin('patients', 'patients.id', '=', 'payments.patient_id');

    // ✅ APPLY ROLE FILTER (CRITICAL)
    $baseQuery = $this->applyDoctorScope($baseQuery, $request);

    // ----------------------------
    // FILTERS
    // ----------------------------
    if ($request->filled('payment_status')) {
        if ($request->payment_status === 'success') {
            $baseQuery->where('payments.status', 'Authorized');
        } elseif ($request->payment_status === 'failed') {
            $baseQuery->where('payments.status', '!=', 'Authorized');
        }
    }

    if ($request->filled('payment_mode')) {
        if ($request->payment_mode === 'online') {
            $baseQuery->where('payments.payment_mode', 'online');
        } elseif ($request->payment_mode === 'offline') {
            $baseQuery->where('payments.payment_mode', '!=', 'online');
        }
    }

    if ($request->filled('type')) {
        if ($request->type === 'appointment') {
            $baseQuery->where('payments.type', 'appointment');
        } elseif ($request->type === 'service') {
            $baseQuery->where('payments.type', 'service');
        }
    }

    // ----------------------------
    // DATE RANGES
    // ----------------------------
    $today = Carbon::today()->toDateString();
    $monthStart = Carbon::now()->startOfMonth()->toDateString();
    $monthEnd   = Carbon::now()->endOfMonth()->toDateString();

    // ----------------------------
    // DASHBOARD CARDS
    // ----------------------------
    $cardData = [

        'successful_payments' => [
            'today' => (clone $baseQuery)
                ->whereDate('payments.created_at', $today)
                ->where('payments.status', 'Authorized')
                ->count(),

            'month' => (clone $baseQuery)
                ->whereBetween(DB::raw('DATE(payments.created_at)'), [$monthStart, $monthEnd])
                ->where('payments.status', 'Authorized')
                ->count(),
        ],

        'failed_payments' => [
            'today' => (clone $baseQuery)
                ->whereDate('payments.created_at', $today)
                ->where('payments.status', '!=', 'Authorized')
                ->count(),

            'month' => (clone $baseQuery)
                ->whereBetween(DB::raw('DATE(payments.created_at)'), [$monthStart, $monthEnd])
                ->where('payments.status', '!=', 'Authorized')
                ->count(),
        ],

        'success_amount' => [
            'today' => (clone $baseQuery)
                ->whereDate('payments.created_at', $today)
                ->where('payments.status', 'Authorized')
                ->sum('payments.amount'),

            'month' => (clone $baseQuery)
                ->whereBetween(DB::raw('DATE(payments.created_at)'), [$monthStart, $monthEnd])
                ->where('payments.status', 'Authorized')
                ->sum('payments.amount'),
        ],

        'failed_amount' => [
            'today' => (clone $baseQuery)
                ->whereDate('payments.created_at', $today)
                ->where('payments.status', '!=', 'Authorized')
                ->sum('payments.amount'),

            'month' => (clone $baseQuery)
                ->whereBetween(DB::raw('DATE(payments.created_at)'), [$monthStart, $monthEnd])
                ->where('payments.status', '!=', 'Authorized')
                ->sum('payments.amount'),
        ],
    ];

    // ----------------------------
    // TABLE DATA
    // ----------------------------
    $payments = (clone $baseQuery)
        ->whereBetween(DB::raw('DATE(payments.created_at)'), [$fromDate, $toDate])
        ->select([
            'payments.id',
            'payments.payment_id',
            'payments.mocdoc_apptkey as appointment_no',
            'payments.aptDate as appointment_date',
            'payments.aptTime as appointment_time',
            'payments.amount',
            'payments.currency',
            'payments.type',
            'payments.status',
            'payments.payment_mode',
            'payments.created_at',
            'payments.doctor_fee',
            'payments.registration_fee',

            'doctors.name as doctor_name',

            'patients.name as patient_name',
            'patients.email as patient_email',
            'patients.mobile as patient_phone',
        ])
        ->orderBy('payments.created_at', 'desc')
        ->get();

    // ❌ Hide doctor dropdown for role 5
    $doctors = auth()->user()->role == 5 ? collect() : $this->getDoctors();

    return view('payment.report', compact(
        'pageTitle',
        'payments',
        'cardData',
        'doctors',
        'fromDate',
        'toDate'
    ));
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



// public function appointments_list(Request $request)
// {
//     $pageTitle = "Appointments";

//     // ----------------------------
//     // DATE FILTER FOR TABLE
//     // ----------------------------
//     $fromDate = $request->from_date ?? now()->toDateString();
//     $toDate   = $request->to_date ?? now()->toDateString();

//     // ----------------------------
//     // BASE QUERY
//     // ----------------------------
//     $baseQuery = Payment::query()
//         ->whereNotNull('payments.mocdoc_apptkey')
//         ->leftJoin('doctors', 'doctors.id', '=', 'payments.doctor_id')
//         ->leftJoin('patients', 'patients.id', '=', 'payments.patient_id');

//     // ----------------------------
//     // FILTERS
//     // ----------------------------
//     if ($request->filled('doctor')) {
//         $baseQuery->where('payments.doctor_id', $request->doctor);
//     }

//     if ($request->filled('payment_status')) {
//         if ($request->payment_status === 'success') {
//             $baseQuery->where('payments.status', 'Authorized');
//         } elseif ($request->payment_status === 'failed') {
//             $baseQuery->where('payments.status', '!=', 'Authorized');
//         }
//     }

//     if ($request->filled('payment_mode')) {
//         if ($request->payment_mode === 'online') {
//             $baseQuery->where('payments.payment_mode', 'online');
//         } elseif ($request->payment_mode === 'offline') {
//             $baseQuery->where('payments.payment_mode','!=','online');
//         }
//     }

//     // ----------------------------
//     // DATE RANGES
//     // ----------------------------
//     $today = Carbon::today()->toDateString();
//     $monthStart = Carbon::now()->startOfMonth()->toDateString();
//     $monthEnd   = Carbon::now()->endOfMonth()->toDateString();

//     // ----------------------------
//     // DASHBOARD CARD DATA
//     // ----------------------------
//     $cardData = [

//         'total_appointments' => [
//             'today' => (clone $baseQuery)
//                 ->whereDate('payments.created_at', $today)
//                 ->count(),

//             'month' => (clone $baseQuery)
//                 ->whereBetween(DB::raw('DATE(payments.created_at)'), [$monthStart, $monthEnd])
//                 ->count(),
//         ],

//         'paid_appointments' => [
//             'today' => (clone $baseQuery)
//                 ->whereDate('payments.created_at', $today)
//                 ->where('payments.status', 'Authorized')
//                 ->count(),

//             'month' => (clone $baseQuery)
//                 ->whereBetween(DB::raw('DATE(payments.created_at)'), [$monthStart, $monthEnd])
//                 ->where('payments.status', 'Authorized')
//                 ->count(),
//         ],

//         'failed_appointments' => [
//             'today' => (clone $baseQuery)
//                 ->whereDate('payments.created_at', $today)
//                 ->where('payments.status', '!=', 'Authorized')
//                 ->count(),

//             'month' => (clone $baseQuery)
//                 ->whereBetween(DB::raw('DATE(payments.created_at)'), [$monthStart, $monthEnd])
//                 ->where('payments.status', '!=', 'Authorized')
//                 ->count(),
//         ],

//         'total_revenue' => [
//             'today' => (clone $baseQuery)
//                 ->whereDate('payments.created_at', $today)
//                 ->where('payments.status', 'Authorized')
//                 ->sum('payments.amount'),

//             'month' => (clone $baseQuery)
//                 ->whereBetween(DB::raw('DATE(payments.created_at)'), [$monthStart, $monthEnd])
//                 ->where('payments.status', 'Authorized')
//                 ->sum('payments.amount'),
//         ],
//     ];

//     // ----------------------------
//     // TABLE DATA
//     // ----------------------------
//     $appointments = (clone $baseQuery)
//         ->whereBetween(DB::raw('DATE(payments.created_at)'), [$fromDate, $toDate])
//         ->select([
//             'payments.id',
//             'payments.payment_id',
//             'payments.mocdoc_apptkey as appointment_no',
//             'payments.aptDate as appointment_date',
//             'payments.aptTime as appointment_time',
//             'payments.amount',
//             'payments.doctor_fee',
//             'payments.registration_fee',
//             'payments.status',
//             'payments.payment_mode',
//             'payments.is_followup',
//             'payments.main_visit_id',
//             'payments.created_at',
//             'doctors.name as doctor_name',
//             'patients.name as patient_name',
//             'patients.mobile as patient_phone',
//             'payments.appointment_status as appointment_status',
//             'payments.sms_delivered',
//             'payments.sms_sent_at'
//         ])
//         ->orderBy('payments.created_at', 'desc')
//         ->get();

//     $doctors = $this->getDoctors();

//     return view('admin.appointments.appointments_list',
//         compact(
//             'pageTitle',
//             'appointments',
//             'cardData',
//             'doctors',
//             'fromDate',
//             'toDate'
//         )
//     );
// }
public function appointments_list(Request $request)
{
    $pageTitle = "Appointments";

    $fromDate = $request->from_date ?? now()->toDateString();
    $toDate   = $request->to_date ?? now()->toDateString();

    $baseQuery = Payment::query()
        ->whereNotNull('payments.mocdoc_apptkey')
        ->leftJoin('doctors', 'doctors.id', '=', 'payments.doctor_id')
        ->leftJoin('patients', 'patients.id', '=', 'payments.patient_id');

    // ✅ APPLY ROLE FILTER
    $baseQuery = $this->applyDoctorScope($baseQuery, $request);

    // ---------------- FILTERS ----------------
    if ($request->filled('payment_status')) {
        $request->payment_status === 'success'
            ? $baseQuery->where('payments.status', 'Authorized')
            : $baseQuery->where('payments.status', '!=', 'Authorized');
    }

    if ($request->filled('payment_mode')) {
        $request->payment_mode === 'online'
            ? $baseQuery->where('payments.payment_mode', 'online')
            : $baseQuery->where('payments.payment_mode','!=','online');
    }

    // ---------------- DATE ----------------
    $today = Carbon::today()->toDateString();
    $monthStart = Carbon::now()->startOfMonth()->toDateString();
    $monthEnd   = Carbon::now()->endOfMonth()->toDateString();

    // ---------------- CARDS ----------------
    $cardData = [
        'total_appointments' => [
            'today' => (clone $baseQuery)->whereDate('payments.created_at', $today)->count(),
            'month' => (clone $baseQuery)->whereBetween(DB::raw('DATE(payments.created_at)'), [$monthStart, $monthEnd])->count(),
        ],
        'paid_appointments' => [
            'today' => (clone $baseQuery)->whereDate('payments.created_at', $today)->where('payments.status', 'Authorized')->count(),
            'month' => (clone $baseQuery)->whereBetween(DB::raw('DATE(payments.created_at)'), [$monthStart, $monthEnd])->where('payments.status', 'Authorized')->count(),
        ],
        'failed_appointments' => [
            'today' => (clone $baseQuery)->whereDate('payments.created_at', $today)->where('payments.status', '!=', 'Authorized')->count(),
            'month' => (clone $baseQuery)->whereBetween(DB::raw('DATE(payments.created_at)'), [$monthStart, $monthEnd])->where('payments.status', '!=', 'Authorized')->count(),
        ],
        'total_revenue' => [
            'today' => (clone $baseQuery)->whereDate('payments.created_at', $today)->where('payments.status', 'Authorized')->sum('payments.amount'),
            'month' => (clone $baseQuery)->whereBetween(DB::raw('DATE(payments.created_at)'), [$monthStart, $monthEnd])->where('payments.status', 'Authorized')->sum('payments.amount'),
        ],
    ];

    // ---------------- TABLE ----------------
    $appointments = (clone $baseQuery)
        ->whereBetween(DB::raw('DATE(payments.created_at)'), [$fromDate, $toDate])
        ->select([
            'payments.id',
            'payments.payment_id',
            'payments.mocdoc_apptkey as appointment_no',
            'payments.aptDate as appointment_date',
            'payments.aptTime as appointment_time',
            'payments.amount',
            'payments.doctor_fee',
            'payments.registration_fee',
            'payments.status',
            'payments.payment_mode',
            'payments.is_followup',
            'payments.main_visit_id',
            'payments.created_at',
            'doctors.name as doctor_name',
            'patients.name as patient_name',
            'patients.mobile as patient_phone',
            'payments.appointment_status as appointment_status',
            'payments.sms_delivered',
            'payments.sms_sent_at'
        ]) // keep your same select
        ->orderBy('payments.created_at', 'desc')
        ->get();

    // ❌ hide doctor dropdown for role 5
    $doctors = auth()->user()->role == 5 ? collect() : $this->getDoctors();

    return view('admin.appointments.appointments_list', compact(
        'pageTitle','appointments','cardData','doctors','fromDate','toDate'
    ));
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
            'payments.doctor_fee',
            'payments.registration_fee',
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
    $pdf = Pdf::loadView('admin.appointments.pdf',
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

    $query = Payment::query()
        ->whereNotNull('payments.mocdoc_apptkey')
        ->leftJoin('doctors', 'doctors.id', '=', 'payments.doctor_id')
        ->leftJoin('patients', 'patients.id', '=', 'payments.patient_id');

    // ✅ APPLY ROLE FILTER (IMPORTANT)
    $query = $this->applyDoctorScope($query, $request);

    $appointments = $query
        ->whereBetween(
            DB::raw('DATE(payments.created_at)'),
            [$fromDate, $toDate]
        )
        ->select([
            'payments.mocdoc_apptkey as appointment_no',
            'payments.aptDate as appointment_date',
            'payments.aptTime as appointment_time',
            'payments.amount',
            'payments.doctor_fee',
            'payments.registration_fee',
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

    return view('admin.appointments.print',
        compact('appointments', 'fromDate', 'toDate')
    );
}


public function paymentReportPdf(Request $request)
{
    $fromDate = $request->from_date ?? now()->toDateString();
    $toDate   = $request->to_date ?? now()->toDateString();

    $query = Payment::query()
        ->leftJoin('doctors', 'doctors.id', '=', 'payments.doctor_id')
        ->leftJoin('patients', 'patients.id', '=', 'payments.patient_id');

    // ✅ APPLY ROLE FILTER (IMPORTANT)
    $query = $this->applyDoctorScope($query, $request);

    // ✅ DATE FILTER
    $query->whereBetween(
        DB::raw('DATE(payments.created_at)'),
        [$fromDate, $toDate]
    );

    // ✅ PAYMENT STATUS FILTER
    if ($request->filled('payment_status')) {
        if ($request->payment_status === 'success') {
            $query->where('payments.status', 'Authorized');
        } elseif ($request->payment_status === 'failed') {
            $query->where('payments.status', '!=', 'Authorized');
        }
    }

    $payments = $query
        ->select([
            'payments.payment_id',
            'payments.mocdoc_apptkey as appointment_no',
            'payments.aptDate as appointment_date',
            'payments.aptTime as appointment_time',
            'payments.amount',
            'payments.status',
            'payments.created_at',
            'payments.doctor_fee',
            'payments.registration_fee',

            'doctors.id as doctor_id',
            'doctors.name as doctor_name',

            'patients.name as patient_name',
            'patients.email as patient_email',
            'patients.mobile as patient_phone',
        ])
        ->orderBy('doctors.name')
        ->orderBy('payments.created_at', 'desc')
        ->get();

    $groupedPayments = $payments->groupBy('doctor_id');

    $pdf = Pdf::loadView(
        'payment.report_pdf',
        compact('groupedPayments', 'fromDate', 'toDate')
    )->setPaper('A4', 'portrait');

    return $pdf->download(
        'payment-report-' . now()->format('d-m-Y') . '.pdf'
    );
}
private function applyDoctorScope($query, $request = null)
{
    if (auth()->user()->role == 5) {
        // 👨‍⚕️ Doctor → only own data
        $query->where('payments.doctor_id', auth()->user()->doctor_id);
    } else {
        // 👑 Admin → allow filter
        if ($request && $request->filled('doctor')) {
            $query->where('payments.doctor_id', $request->doctor);
        }
    }

    return $query;
}
public function updateStatus(Request $request)
{
    $appointment = Payment::findOrFail($request->id);


    // Store previous status
    $oldStatus = $appointment->appointment_status ?? 'Scheduled';

    // Update main appointment
    $appointment->update([
        'appointment_status' => $request->status,
        'remarks' => $request->remarks
    ]);

    // Log status change
    AppointmentStatusLog::create([
        'appointment_no' => $appointment->mocdoc_apptkey,
        'appointment_id' => $appointment->id,
        'from_status' => $oldStatus,
        'to_status' => $request->status,
        'remarks' => $request->remarks,
        'changed_by' => auth()->id(),
        'changedName' => auth()->user()->name,
         'ip_address'     => $request->ip(), // 👈 client IP
    ]);

    return response()->json([
        'success' => true,
        'status' => $request->status
    ]);
}
public function getStatusLog($appointmentId)
{
    // Directly fetch logs from AppointmentStatusLog table
    $logs = AppointmentStatusLog::where('appointment_id', $appointmentId)
        ->orderBy('created_at', 'asc')
        ->get();

    return response()->json([
        'success' => true,
        'logs' => $logs
    ]);
}

public function sendAppointmentSms(Request $request)
{

    $appointment = Payment::where('id',$request->id)->first();

    if(!$appointment){
        return response()->json(['status'=>false]);
    }

    $patient = Patient::find($appointment->patient_id);

    if(!$patient){
        return response()->json(['status'=>false]);
    }

    $smsService = app(NettyfishSmsService::class);

    /* ===============================
       1️⃣ Appointment Confirmation SMS
    =============================== */

    $appointmentSms = $smsService->sendAppointmentConfirmation(
        $patient->mobile,
        $patient->name,
        'Edge Clinic',
        \Carbon\Carbon::parse($appointment->aptDate)->format('d M Y'),
        $appointment->aptTime
    );

    /* ===============================
       2️⃣ Invoice SMS (only if first SMS sent)
    =============================== */

    // $invoiceSms = false;

    // if($appointmentSms){

    //     $invoiceUrl = route('invoice.appointment', [
    //         'paymentId' => $appointment->payment_id
    //     ]);

    //     $invoiceSms = $smsService->sendInvoiceSms(
    //         $patient->mobile,
    //         $patient->name,
    //         $invoiceUrl,
    //         '6303258050',
    //         'Edge Clinic',
    //         'Doctor'
    //     );
    // }

    /* ===============================
       Update SMS status in DB
    =============================== */

    $appointment->update([
        'sms_delivered'      => $appointmentSms ? 1 : 0,
        'sms_sent_at'        => $appointmentSms ? now() : null,
    ]);

    return response()->json([
        'status' => $appointmentSms
    ]);

}

}
