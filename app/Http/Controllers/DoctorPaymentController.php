<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\AppointmentStatusLog;
use App\Models\Appointment;
use App\Models\Source;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
use Throwable;



class DoctorPaymentController extends Controller
{
    private const PAYMENT_SUCCESS_STATUSES = ['Authorized', 'Captured', 'No Payment Required'];
    private const PAYMENT_PENDING_STATUSES = ['Pending', 'Initiated'];

    // --------------------------------------------------------------
    // Show payment report
    // --------------------------------------------------------------
   public function index(Request $request)
{
    $pageTitle = "Payments";
    $paymentDateSql = $this->paymentReportDateSql();

    // ----------------------------
    // DATE FILTER FOR TABLE
    // ----------------------------
    $fromDate = $request->from_date ?? now()->toDateString();
    $toDate   = $request->to_date ?? now()->toDateString();

    // ----------------------------
    // BASE QUERY
    // ----------------------------
    $baseQuery = Payment::query()
        ->leftJoin('doctors', 'doctors.id', '=', 'payments.doctor_id')
        ->leftJoin('patients', 'patients.id', '=', 'payments.patient_id')
        ->leftJoin('sources', 'sources.id', '=', 'payments.source_id');

    // ----------------------------
    // FILTERS
    // ----------------------------
    if ($request->filled('doctor')) {
        $baseQuery->where('payments.doctor_id', $request->doctor);
    }

    if ($request->filled('source_id')) {
        $baseQuery->where('payments.source_id', $request->source_id);
    }

    $this->applyPaymentStatusFilter($baseQuery, $request->payment_status, 'payments.status');

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
    // DASHBOARD CARD DATA (TODAY / MONTH)
    // ----------------------------
    $cardData = [

        'successful_payments' => [
            'today' => (clone $baseQuery)
                ->whereRaw("{$paymentDateSql} = ?", [$today])
                ->whereIn('payments.status', self::PAYMENT_SUCCESS_STATUSES)
                ->count(),

            'month' => (clone $baseQuery)
                ->whereBetween(DB::raw($paymentDateSql), [$monthStart, $monthEnd])
                ->whereIn('payments.status', self::PAYMENT_SUCCESS_STATUSES)
                ->count(),
        ],

        'failed_payments' => [
            'today' => (clone $baseQuery)
                ->whereRaw("{$paymentDateSql} = ?", [$today])
                ->whereNotIn('payments.status', array_merge(self::PAYMENT_SUCCESS_STATUSES, self::PAYMENT_PENDING_STATUSES))
                ->count(),

            'month' => (clone $baseQuery)
                ->whereBetween(DB::raw($paymentDateSql), [$monthStart, $monthEnd])
                ->whereNotIn('payments.status', array_merge(self::PAYMENT_SUCCESS_STATUSES, self::PAYMENT_PENDING_STATUSES))
                ->count(),
        ],

        'success_amount' => [
            'today' => (clone $baseQuery)
                ->whereRaw("{$paymentDateSql} = ?", [$today])
                ->whereIn('payments.status', self::PAYMENT_SUCCESS_STATUSES)
                ->sum('payments.amount'),

            'month' => (clone $baseQuery)
                ->whereBetween(DB::raw($paymentDateSql), [$monthStart, $monthEnd])
                ->whereIn('payments.status', self::PAYMENT_SUCCESS_STATUSES)
                ->sum('payments.amount'),
        ],

        'failed_amount' => [
            'today' => (clone $baseQuery)
                ->whereRaw("{$paymentDateSql} = ?", [$today])
                ->whereNotIn('payments.status', array_merge(self::PAYMENT_SUCCESS_STATUSES, self::PAYMENT_PENDING_STATUSES))
                ->sum('payments.amount'),

            'month' => (clone $baseQuery)
                ->whereBetween(DB::raw($paymentDateSql), [$monthStart, $monthEnd])
                ->whereNotIn('payments.status', array_merge(self::PAYMENT_SUCCESS_STATUSES, self::PAYMENT_PENDING_STATUSES))
                ->sum('payments.amount'),
        ],
    ];

    // ----------------------------
    // TABLE DATA
    // ----------------------------
    $payments = (clone $baseQuery)
        ->whereBetween(DB::raw($paymentDateSql), [$fromDate, $toDate])
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
            'payments.discount_percentage',
            'payments.discount_amount',
            'sources.name as source_name',
            DB::raw('(COALESCE(payments.doctor_fee, 0) + COALESCE(payments.registration_fee, 0)) as gross_amount'),
            'doctors.name as doctor_name',
            'patients.name as patient_name',
            'patients.email as patient_email',
            'patients.mobile as patient_phone',
        ])
        ->orderBy('payments.created_at', 'desc')
        ->get();

    $doctors = $this->getDoctors();
    $sources = $this->getSources();

    return view('payment.report',
        compact(
            'pageTitle',
            'payments',
            'cardData',
            'doctors',
            'sources',
            'fromDate',
            'toDate'
        )
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

    private function getSources()
    {
        return Source::query()
            ->where('status', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }



public function appointments_list(Request $request)
{
    $pageTitle = "Appointments";
    $appointmentDateSql = $this->appointmentReportDateSql();
    $hasConsultationsTable = Schema::hasTable('consultations');

    // ----------------------------
    // DATE FILTER FOR TABLE
    // ----------------------------
    $fromDate = $request->from_date ?? now()->toDateString();
    $toDate   = $request->to_date ?? now()->addDays(30)->toDateString();

    // ----------------------------
    // BASE QUERY
    // ----------------------------
    $baseQuery = Payment::query()
        ->whereNotNull('payments.mocdoc_apptkey')
        ->where('payments.type', 'appointment')
        ->leftJoin('appointments', 'appointments.payment_id', '=', 'payments.payment_id')
        ->leftJoin('doctors', 'doctors.id', '=', 'payments.doctor_id')
        ->leftJoin('patients', 'patients.id', '=', 'payments.patient_id')
        ->leftJoin('sources', 'sources.id', '=', 'payments.source_id');

    if ($hasConsultationsTable) {
        $baseQuery->leftJoin('consultations', function ($join) {
            $join->on('consultations.payment_id', '=', 'payments.id')
                ->orOn('consultations.appointment_id', '=', 'appointments.id');
        });
    }

    // ----------------------------
    // FILTERS
    // ----------------------------
    if ($request->filled('doctor')) {
        $baseQuery->where('payments.doctor_id', $request->doctor);
    }

    if ($request->filled('source_id')) {
        $baseQuery->where('payments.source_id', $request->source_id);
    }

    $this->applyPaymentStatusFilter($baseQuery, $request->payment_status, 'payments.status');

    if ($request->filled('payment_mode')) {
        if ($request->payment_mode === 'online') {
            $baseQuery->where('payments.payment_mode', 'online');
        } elseif ($request->payment_mode === 'offline') {
            $baseQuery->where('payments.payment_mode','!=','online');
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
                ->whereRaw("{$appointmentDateSql} = ?", [$today])
                ->count(),

            'month' => (clone $baseQuery)
                ->whereBetween(DB::raw($appointmentDateSql), [$monthStart, $monthEnd])
                ->count(),
        ],

        'paid_appointments' => [
            'today' => (clone $baseQuery)
                ->whereRaw("{$appointmentDateSql} = ?", [$today])
                ->whereIn('payments.status', self::PAYMENT_SUCCESS_STATUSES)
                ->count(),

            'month' => (clone $baseQuery)
                ->whereBetween(DB::raw($appointmentDateSql), [$monthStart, $monthEnd])
                ->whereIn('payments.status', self::PAYMENT_SUCCESS_STATUSES)
                ->count(),
        ],

        'failed_appointments' => [
            'today' => (clone $baseQuery)
                ->whereRaw("{$appointmentDateSql} = ?", [$today])
                ->whereNotIn('payments.status', array_merge(self::PAYMENT_SUCCESS_STATUSES, self::PAYMENT_PENDING_STATUSES))
                ->count(),

            'month' => (clone $baseQuery)
                ->whereBetween(DB::raw($appointmentDateSql), [$monthStart, $monthEnd])
                ->whereNotIn('payments.status', array_merge(self::PAYMENT_SUCCESS_STATUSES, self::PAYMENT_PENDING_STATUSES))
                ->count(),
        ],

        'total_revenue' => [
            'today' => (clone $baseQuery)
                ->whereRaw("{$appointmentDateSql} = ?", [$today])
                ->whereIn('payments.status', self::PAYMENT_SUCCESS_STATUSES)
                ->sum('payments.amount'),

            'month' => (clone $baseQuery)
                ->whereBetween(DB::raw($appointmentDateSql), [$monthStart, $monthEnd])
                ->whereIn('payments.status', self::PAYMENT_SUCCESS_STATUSES)
                ->sum('payments.amount'),
        ],
    ];

    // ----------------------------
    // TABLE DATA
    // ----------------------------
    $appointments = (clone $baseQuery)
        ->whereBetween(DB::raw($appointmentDateSql), [$fromDate, $toDate])
        ->select([
            'payments.id',
            'payments.id as payment_row_id',
            'appointments.id as appointment_row_id',
            'payments.payment_id',
            DB::raw('COALESCE(payments.mocdoc_apptkey, appointments.appointment_no) as appointment_no'),
            'payments.aptDate as appointment_date',
            'payments.aptTime as appointment_time',
            'payments.amount',
            'payments.doctor_fee',
            'payments.registration_fee',
            'payments.discount_percentage',
            'payments.discount_amount',
            'payments.status as payment_status',
            'payments.payment_mode',
            'payments.reference_no',
            'payments.is_followup',
            'payments.main_visit_id',
            'payments.created_at',
            'sources.name as source_name',
            DB::raw('(COALESCE(payments.doctor_fee, 0) + COALESCE(payments.registration_fee, 0)) as gross_amount'),
            'doctors.name as doctor_name',
            'patients.name as patient_name',
            'patients.mobile as patient_phone',
            'payments.appointment_status as appointment_status',
            $hasConsultationsTable ? 'consultations.id as consultation_id' : DB::raw('NULL as consultation_id'),
            'payments.sms_delivered',
            'payments.sms_sent_at'
        ])
        ->orderBy('payments.created_at', 'desc')
        ->get();

    $doctors = $this->getDoctors();
    $sources = $this->getSources();

    return view('admin.appointments.appointments_list',
        compact(
            'pageTitle',
            'appointments',
            'cardData',
            'doctors',
            'sources',
            'fromDate',
            'toDate'
        )
    );
}

private function appointmentReportDateSql(): string
{
    return "COALESCE(appointments.date, CASE
        WHEN CHAR_LENGTH(COALESCE(payments.aptDate, '')) = 8 THEN STR_TO_DATE(payments.aptDate, '%Y%m%d')
        WHEN CHAR_LENGTH(COALESCE(payments.aptDate, '')) = 10 THEN STR_TO_DATE(payments.aptDate, '%Y-%m-%d')
        ELSE DATE(payments.created_at)
    END)";
}

private function paymentReportDateSql(): string
{
    return "CASE
        WHEN payments.type = 'appointment' AND CHAR_LENGTH(COALESCE(payments.aptDate, '')) = 8 THEN STR_TO_DATE(payments.aptDate, '%Y%m%d')
        WHEN payments.type = 'appointment' AND CHAR_LENGTH(COALESCE(payments.aptDate, '')) = 10 THEN STR_TO_DATE(payments.aptDate, '%Y-%m-%d')
        ELSE DATE(payments.created_at)
    END";
}

private function applyPaymentStatusFilter($query, ?string $paymentStatus, string $column = 'payments.status'): void
{
    if (! filled($paymentStatus)) {
        return;
    }

    if ($paymentStatus === 'success') {
        $query->whereIn($column, self::PAYMENT_SUCCESS_STATUSES);
        return;
    }

    if ($paymentStatus === 'pending' || $paymentStatus === 'initiated') {
        $query->whereIn($column, self::PAYMENT_PENDING_STATUSES);
        return;
    }

    if ($paymentStatus === 'failed') {
        $query->whereNotIn($column, array_merge(self::PAYMENT_SUCCESS_STATUSES, self::PAYMENT_PENDING_STATUSES));
    }
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
        ->leftJoin('patients', 'patients.id', '=', 'payments.patient_id')
        ->leftJoin('sources', 'sources.id', '=', 'payments.source_id');

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

    $this->applyPaymentStatusFilter($query, $request->payment_status, 'payments.status');

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
            'payments.discount_percentage',
            'payments.discount_amount',
            'sources.name as source_name',
            DB::raw('(COALESCE(payments.doctor_fee, 0) + COALESCE(payments.registration_fee, 0)) as gross_amount'),
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

    $appointments = Payment::query()
        ->whereNotNull('payments.mocdoc_apptkey')
        ->leftJoin('doctors', 'doctors.id', '=', 'payments.doctor_id')
        ->leftJoin('patients', 'patients.id', '=', 'payments.patient_id')
        ->leftJoin('sources', 'sources.id', '=', 'payments.source_id')
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
            'payments.discount_percentage',
            'payments.discount_amount',
            'sources.name as source_name',
            DB::raw('(COALESCE(payments.doctor_fee, 0) + COALESCE(payments.registration_fee, 0)) as gross_amount'),
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
        ->leftJoin('patients', 'patients.id', '=', 'payments.patient_id')
        ->leftJoin('sources', 'sources.id', '=', 'payments.source_id');

    // ------------------------------------------------
    // 🔍 FILTERS (SAME AS INDEX)
    // ------------------------------------------------
    if ($request->filled('doctor')) {
        $query->where('payments.doctor_id', $request->doctor);
    }

    // Datetime-safe date filter
    $paymentDateSql = $this->paymentReportDateSql();

    $query->whereBetween(
        DB::raw($paymentDateSql),
        [$fromDate, $toDate]
    );

    $this->applyPaymentStatusFilter($query, $request->payment_status, 'payments.status');

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
            'payments.doctor_fee',
            'payments.registration_fee',
            'payments.discount_percentage',
            'payments.discount_amount',
            'sources.name as source_name',
            DB::raw('(COALESCE(payments.doctor_fee, 0) + COALESCE(payments.registration_fee, 0)) as gross_amount'),
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

public function updateStatus(Request $request)
{
    $appointment = Payment::findOrFail($request->id);
    $appointmentRow = DB::table('appointments')
        ->where('payment_id', $appointment->payment_id)
        ->first();


    // Store previous status
    $oldStatus = $appointment->appointment_status ?? 'Scheduled';

    // Update main appointment
    $appointment->update([
        'appointment_status' => $request->status,
        'remarks' => $request->remarks
    ]);

    if ($appointmentRow) {
        DB::table('appointments')
            ->where('id', $appointmentRow->id)
            ->update([
                'appointment_status' => $request->status,
                'updated_at' => now(),
            ]);
    }

    // Log status change
    AppointmentStatusLog::create([
        'appointment_no' => $appointmentRow->appointment_no ?? $appointment->mocdoc_apptkey,
        'appointment_id' => $appointmentRow->id ?? $appointment->id,
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

public function updatePayment(Request $request)
{
    $validated = $request->validate([
        'id'              => 'required|exists:payments,id',
        'payment_mode'    => 'required|in:cash,upi,card,split',
        'reference_no'    => 'nullable|string|max:100',
        'cash_amount'     => 'nullable|numeric|min:0',
        'upi_amount'      => 'nullable|numeric|min:0',
        'card_amount'     => 'nullable|numeric|min:0',
        'upi_reference'   => 'nullable|string|max:100',
        'card_reference'  => 'nullable|string|max:100',
    ]);
    $appointment = Payment::findOrFail($validated['id']);

    $referenceNo = null;
    $paymentMode = $validated['payment_mode'];

    if ($paymentMode === 'split') {
        $components = $this->extractSplitComponents($validated);
        $componentCount = collect($components)->where('amount', '>', 0)->count();
        $totalAmount = round(collect($components)->sum('amount'), 2);
        $expectedAmount = round((float) $appointment->amount, 2);

        if ($componentCount < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Split payment needs at least two payment parts.'
            ], 422);
        }

        if ($totalAmount !== $expectedAmount) {
            return response()->json([
                'success' => false,
                'message' => 'Split payment total must match the appointment amount of Rs ' . number_format($expectedAmount, 2)
            ], 422);
        }

        $referenceNo = $this->buildSplitReferenceSummary($components, $appointment->id);
    } else {
        $singleAmount = round((float) $appointment->amount, 2);
        foreach (['cash_amount', 'upi_amount', 'card_amount'] as $field) {
            if (isset($validated[$field]) && (float) $validated[$field] > $singleAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Entered amount cannot exceed the payable amount of Rs ' . number_format($singleAmount, 2)
                ], 422);
            }
        }

        if (in_array($paymentMode, ['upi', 'card'], true) && empty($validated['reference_no'])) {
            return response()->json([
                'success' => false,
                'message' => strtoupper($paymentMode) . ' reference number is required.'
            ], 422);
        }

        $referenceNo = $paymentMode === 'cash'
            ? ($validated['reference_no'] ?: 'CASH_MANUAL_' . $appointment->id)
            : $validated['reference_no'];
    }

    $remarks = trim((string) $request->remarks);
    $remarksPrefix = 'Payment updated manually on ' . now()->format('d M Y h:i A');
    $updatedRemarks = $remarksPrefix . ($remarks !== '' ? ' - ' . $remarks : '');
    if ($paymentMode === 'split') {
        $updatedRemarks .= ' | Split: ' . $referenceNo;
    }

    $appointment->update([
        'payment_mode' => $paymentMode,
        'reference_no' => $referenceNo,
        'status'       => 'Authorized',
        'remarks'      => $updatedRemarks,
    ]);

    return response()->json([
        'success'      => true,
        'status'       => 'Authorized',
        'payment_mode' => $appointment->payment_mode,
        'reference_no' => $appointment->reference_no,
    ]);
}

public function rescheduleSlots(Payment $payment)
{
    if ($payment->type !== 'appointment' || empty($payment->doctor_id)) {
        return response()->json([
            'success' => false,
            'message' => 'Appointment details not found.'
        ], 404);
    }

    $slots = app(DoctorController::class)->_getDoctorCalendar((int) $payment->doctor_id);

    return response()->json([
        'success' => true,
        'doctor_id' => (int) $payment->doctor_id,
        'current_date' => $payment->aptDate,
        'current_time' => $payment->aptTime,
        'dates' => data_get($slots, 'slots.location1', []),
    ]);
}

public function rescheduleAppointment(Request $request)
{
    $validated = $request->validate([
        'id' => 'required|exists:payments,id',
        'date' => 'required',
        'time' => 'required',
        'remarks' => 'nullable|string|max:255',
    ]);

    $payment = Payment::findOrFail($validated['id']);

    if ($payment->type !== 'appointment' || empty($payment->doctor_id)) {
        return response()->json([
            'success' => false,
            'message' => 'Only appointment payments can be rescheduled.'
        ], 422);
    }

    if (($payment->appointment_status ?? 'Scheduled') === 'Completed') {
        return response()->json([
            'success' => false,
            'message' => 'Completed appointments cannot be rescheduled.'
        ], 422);
    }

    $appointmentRow = DB::table('appointments')
        ->where('payment_id', $payment->payment_id)
        ->first();

    if (! $appointmentRow) {
        return response()->json([
            'success' => false,
            'message' => 'Linked appointment record not found.'
        ], 404);
    }

    $availableSlots = app(DoctorController::class)->_getDoctorCalendar((int) $payment->doctor_id);
    $slotsByDate = data_get($availableSlots, 'slots.location1', []);
    $requestedDate = trim((string) $validated['date']);
    $requestedTime = trim((string) $validated['time']);
    $dateSlots = $slotsByDate[$requestedDate] ?? [];

    if (! in_array($requestedTime, $dateSlots, true)) {
        return response()->json([
            'success' => false,
            'message' => 'Selected slot is not available for this doctor.'
        ], 422);
    }

    DB::beginTransaction();

    try {
        $oldDate = $payment->aptDate;
        $oldTime = $payment->aptTime;

        $payment->update([
            'aptDate' => $requestedDate,
            'aptTime' => $requestedTime,
            'remarks' => trim('Rescheduled on ' . now()->format('d M Y h:i A') . '. ' . ($validated['remarks'] ?? '')),
        ]);

        DB::table('appointments')
            ->where('id', $appointmentRow->id)
            ->update([
                'date' => $requestedDate,
                'time_slot' => $requestedTime,
                'updated_at' => now(),
            ]);

        AppointmentStatusLog::create([
            'appointment_no' => $appointmentRow->appointment_no ?? $payment->mocdoc_apptkey,
            'appointment_id' => $appointmentRow->id,
            'from_status' => $payment->appointment_status ?? 'Scheduled',
            'to_status' => $payment->appointment_status ?? 'Scheduled',
            'remarks' => 'Rescheduled from ' . $this->formatAppointmentDateTime($oldDate, $oldTime) . ' to ' . $this->formatAppointmentDateTime($requestedDate, $requestedTime) . (filled($validated['remarks']) ? ' - ' . $validated['remarks'] : ''),
            'changed_by' => auth()->id(),
            'changedName' => auth()->user()->name ?? 'Admin',
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'date' => $requestedDate,
            'time' => $requestedTime,
        ]);
    } catch (Throwable $e) {
        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => 'Unable to reschedule appointment.'
        ], 500);
    }
}

protected function extractSplitComponents(array $validated): array
{
    return [
        [
            'mode' => 'cash',
            'amount' => round((float) ($validated['cash_amount'] ?? 0), 2),
            'reference' => null,
        ],
        [
            'mode' => 'upi',
            'amount' => round((float) ($validated['upi_amount'] ?? 0), 2),
            'reference' => trim((string) ($validated['upi_reference'] ?? '')),
        ],
        [
            'mode' => 'card',
            'amount' => round((float) ($validated['card_amount'] ?? 0), 2),
            'reference' => trim((string) ($validated['card_reference'] ?? '')),
        ],
    ];
}

protected function buildSplitReferenceSummary(array $components, int $paymentId): string
{
    return collect($components)
        ->filter(fn ($component) => $component['amount'] > 0)
        ->map(function ($component) use ($paymentId) {
            $reference = $component['reference'] !== ''
                ? $component['reference']
                : strtoupper($component['mode']) . '_MANUAL_' . $paymentId;

            return strtoupper($component['mode']) . ':' . number_format($component['amount'], 2, '.', '') . ':' . $reference;
        })
        ->implode(' | ');
}

protected function formatAppointmentDateTime(?string $date, ?string $time): string
{
    $formattedDate = $date;

    if (! empty($date)) {
        try {
            $formattedDate = strlen($date) === 8
                ? Carbon::createFromFormat('Ymd', $date)->format('d M Y')
                : Carbon::parse($date)->format('d M Y');
        } catch (Throwable $e) {
            $formattedDate = $date;
        }
    }

    return trim(($formattedDate ?: '-') . ' ' . ($time ?: ''));
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
