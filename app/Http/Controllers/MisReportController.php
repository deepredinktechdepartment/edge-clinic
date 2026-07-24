<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Source;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

class MisReportController extends Controller
{
    private const RECEIVED_STATUSES = ['Authorized', 'Captured'];

    private const REPORTS = [
        'dashboard' => ['title' => 'MIS Dashboard', 'subtitle' => 'A clear view of clinic collections, visits and pending follow-up.'],
        'collection-summary' => ['title' => 'Collection Summary', 'subtitle' => 'Day-wise or month-wise actual receipts.'],
        'doctor-collection' => ['title' => 'Doctor-wise Collection', 'subtitle' => 'Appointments, fee breakup and collections by doctor.'],
        'service-reports' => ['title' => 'Service Reports', 'subtitle' => 'Service billing, paid invoices and unpaid balances.'],
        'source-referral' => ['title' => 'Source & Referral', 'subtitle' => 'Performance of walk-ins, partners and referral sources.'],
        'patient-visits' => ['title' => 'Patient Visit Report', 'subtitle' => 'New visits compared with follow-ups.'],
        'appointment-operations' => ['title' => 'Appointment Operations', 'subtitle' => 'Booked, checked-in, completed and cancelled visits.'],
        'payment-closing' => ['title' => 'Payment Mode & Closing', 'subtitle' => 'Cash, UPI, online and split-payment reconciliation.'],
        'discount-report' => ['title' => 'Discount Report', 'subtitle' => 'Discount visibility by doctor and collection type.'],
    ];

    public function index(Request $request, string $report = 'dashboard')
    {
        $this->ensureReportAccess();
        abort_unless(isset(self::REPORTS[$report]), 404);

        $data = $this->buildReport($request, $report);
        $data['drilldown'] ??= $this->reportDrilldown($request, $report);

        return view('mis.index', array_merge($data, [
            'report' => $report,
            'reportMeta' => self::REPORTS[$report],
            'reportLinks' => self::REPORTS,
            'pageTitle' => self::REPORTS[$report]['title'],
            'doctors' => Doctor::orderBy('name')->get(['id', 'name']),
            'sources' => Source::where('status', true)->orderBy('name')->get(['id', 'name']),
            'fromDate' => $this->fromDate($request),
            'toDate' => $this->toDate($request),
        ]));
    }

    public function excel(Request $request, string $report)
    {
        $this->ensureReportAccess();
        abort_unless(isset(self::REPORTS[$report]), 404);
        $data = $this->buildReport($request, $report);

        return response()->streamDownload(function () use ($data, $report) {
            $sheet = (new Spreadsheet())->getActiveSheet();
            $sheet->setTitle('MIS Report');
            $sheet->fromArray([self::REPORTS[$report]['title']], null, 'A1');
            $sheet->fromArray([$data['periodLabel']], null, 'A2');
            $sheet->fromArray($data['headers'], null, 'A4');
            $sheet->fromArray(collect($data['rows'])->map(fn ($row) => array_values($row))->all(), null, 'A5');
            $sheet->getStyle('A1:Z1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A4:Z4')->getFont()->setBold(true);
            foreach (range('A', min('Z', chr(64 + max(1, count($data['headers']))))) as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            (new Xlsx($sheet->getParent()))->save('php://output');
        }, $report . '-' . now()->format('Ymd-His') . '.xlsx');
    }

    public function pdf(Request $request, string $report)
    {
        $this->ensureReportAccess();
        abort_unless(isset(self::REPORTS[$report]), 404);
        $data = $this->buildReport($request, $report);

        return Pdf::loadView('mis.pdf', array_merge($data, [
            'reportMeta' => self::REPORTS[$report],
            'generatedAt' => now(),
        ]))->setPaper('a4', 'landscape')->download($report . '-' . now()->format('Ymd-His') . '.pdf');
    }

    private function buildReport(Request $request, string $report): array
    {
        $from = $this->fromDate($request);
        $to = $this->toDate($request);
        $periodLabel = 'Period: ' . Carbon::parse($from)->format('d M Y') . ' to ' . Carbon::parse($to)->format('d M Y');

        if ($report === 'dashboard') {
            $received = $this->payments($request)->whereIn('payments.status', self::RECEIVED_STATUSES);
            $appointmentReceived = (clone $received)->where('payments.type', 'appointment');
            $serviceReceived = (clone $received)->where('payments.type', 'service');
            $pendingAmount = $this->payments($request)->where('payments.type', 'appointment')->whereIn('payments.status', ['Pending', 'Initiated'])->sum('payments.amount');
            $outstandingInvoices = DB::table('invoices')->whereBetween('invoice_date', [$from, $to])->sum('balance_amount');
            $cards = [
                ['label' => 'Actual Collection', 'value' => $this->money((clone $received)->sum('payments.amount')), 'tone' => 'success'],
                ['label' => 'Consultation Fee', 'value' => $this->money((clone $appointmentReceived)->sum('payments.doctor_fee')), 'tone' => 'primary'],
                ['label' => 'Registration Fee', 'value' => $this->money((clone $appointmentReceived)->sum('payments.registration_fee')), 'tone' => 'violet'],
                ['label' => 'Service Collection', 'value' => $this->money((clone $serviceReceived)->sum('payments.amount')), 'tone' => 'teal'],
                ['label' => 'Discount Given', 'value' => $this->money($this->payments($request)->sum('payments.discount_amount')), 'tone' => 'danger'],
                ['label' => 'Paid Appointments', 'value' => (string) (clone $appointmentReceived)->count(), 'tone' => 'indigo'],
                ['label' => 'Pending Collection', 'value' => $this->money($pendingAmount), 'tone' => 'warning'],
                ['label' => 'Invoice Outstanding', 'value' => $this->money($outstandingInvoices), 'tone' => 'rose'],
            ];
            $trend = (clone $received)->selectRaw("DATE(payments.created_at) as period, COUNT(*) as transactions, SUM(CASE WHEN payments.type = 'appointment' THEN payments.amount ELSE 0 END) as appointment_collection, SUM(CASE WHEN payments.type = 'service' THEN payments.amount ELSE 0 END) as service_collection, SUM(payments.amount) as total_collection")
                ->groupByRaw('DATE(payments.created_at)')->orderBy('period')->get();
            $rows = $trend->map(fn ($r) => [
                    'Period' => $r->period, 'Transactions' => $r->transactions, 'Appointment Collection' => $this->money($r->appointment_collection), 'Service Collection' => $this->money($r->service_collection), 'Total Collection' => $this->money($r->total_collection),
                ])->all();
            $doctorPerformance = (clone $appointmentReceived)->selectRaw("COALESCE(doctors.name, 'Not assigned') as name, COUNT(*) as visits, SUM(payments.amount) as collection")
                ->groupBy('doctors.id', 'doctors.name')->orderByDesc('collection')->limit(5)->get();
            $sourcePerformance = (clone $appointmentReceived)->selectRaw("COALESCE(sources.name, 'Not set') as name, COUNT(*) as visits, SUM(payments.amount) as collection")
                ->groupBy('sources.id', 'sources.name')->orderByDesc('collection')->limit(5)->get();
            $paymentModes = (clone $received)->selectRaw("COALESCE(NULLIF(payments.payment_mode, ''), 'Not set') as name, COUNT(*) as transactions, SUM(payments.amount) as collection")
                ->groupBy('payments.payment_mode')->orderByDesc('collection')->get();
            $trend->each(fn ($item) => $item->total_collection_display = $this->money($item->total_collection));
            $doctorPerformance->each(fn ($item) => $item->collection_display = $this->money($item->collection));
            $sourcePerformance->each(fn ($item) => $item->collection_display = $this->money($item->collection));
            $paymentModes->each(fn ($item) => $item->collection_display = $this->money($item->collection));
            return compact('cards', 'rows', 'periodLabel', 'trend', 'doctorPerformance', 'sourcePerformance', 'paymentModes') + ['headers' => ['Period', 'Transactions', 'Appointment Collection', 'Service Collection', 'Total Collection'], 'notice' => 'Actual Collection includes only Authorized or Captured payments. No Payment Required is displayed as a visit status, not cash received.'];
        }

        if ($report === 'collection-summary') {
            $isMonthly = $request->get('group_by', 'day') === 'month';
            $group = $isMonthly ? "DATE_FORMAT(payments.created_at, '%Y-%m')" : 'DATE(payments.created_at)';
            $periodKeys = [];
            $rows = $this->payments($request)->whereIn('payments.status', self::RECEIVED_STATUSES)->selectRaw("{$group} as period, COUNT(*) as transactions, SUM(CASE WHEN payments.type = 'appointment' THEN payments.doctor_fee ELSE 0 END) as consultation_fee, SUM(CASE WHEN payments.type = 'appointment' THEN payments.registration_fee ELSE 0 END) as registration_fee, SUM(CASE WHEN payments.type = 'service' THEN payments.amount ELSE 0 END) as service_collection, SUM(payments.amount) as net_collection")
                ->groupByRaw($group)->orderBy('period')->get()->map(function ($r) use ($isMonthly, &$periodKeys) {
                    $displayPeriod = $isMonthly ? Carbon::createFromFormat('Y-m', $r->period)->format('M Y') : Carbon::parse($r->period)->format('d M Y');
                    $periodKeys[$displayPeriod] = $r->period;
                    return ['Period' => $displayPeriod, 'Transactions' => $r->transactions, 'Consultation Fee' => $this->money($r->consultation_fee), 'Registration Fee' => $this->money($r->registration_fee), 'Service Collection' => $this->money($r->service_collection), 'Net Collection' => $this->money($r->net_collection)];
                })->all();
            $data = $this->tableData($rows, $periodLabel, 'Collection is grouped by the recorded receipt timestamp. Click Transactions, Consultation Fee, or Registration Fee to view the matching details.');
            $data['drilldown'] = $this->collectionSummaryDrilldown($request);
            $data['periodKeys'] = $periodKeys;
            return $data;
        }

        if ($report === 'doctor-collection') {
            $rows = $this->payments($request)->where('payments.type', 'appointment')->whereIn('payments.status', self::RECEIVED_STATUSES)
                ->selectRaw("doctors.id as doctor_id, COALESCE(doctors.name, 'Not assigned') as doctor, COUNT(*) as visits, SUM(CASE WHEN payments.is_followup = 0 THEN 1 ELSE 0 END) as new_visits, SUM(CASE WHEN payments.is_followup = 1 THEN 1 ELSE 0 END) as followups, SUM(COALESCE(payments.doctor_fee, 0)) as consultation_fee, SUM(COALESCE(payments.registration_fee, 0)) as registration_fee, SUM(COALESCE(payments.discount_amount, 0)) as discount, SUM(payments.amount) as collection")
                ->groupBy('doctors.id', 'doctors.name')->orderByDesc('collection')->get()->map(fn ($r) => ['Doctor' => $r->doctor, 'Visits' => $r->visits, 'New Visits' => $r->new_visits, 'Follow-ups' => $r->followups, 'Consultation Fee' => $this->money($r->consultation_fee), 'Registration Fee' => $this->money($r->registration_fee), 'Discount' => $this->money($r->discount), 'Collection' => $this->money($r->collection), '__drill_key' => (string) $r->doctor_id])->all();
            return $this->tableData($rows, $periodLabel, 'Click a doctor filter above to open a focused collection view.');
        }

        if ($report === 'service-reports') {
            $query = DB::table('invoice_items')->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')->leftJoin('services', 'services.id', '=', 'invoice_items.service_id')->whereBetween('invoices.invoice_date', [$from, $to]);
            if ($request->filled('doctor')) $query->where('invoices.doctor_id', $request->doctor);
            $rows = $query->selectRaw("services.id as service_id, COALESCE(services.name, invoice_items.service_name, 'Service not named') as service, COUNT(DISTINCT invoices.id) as invoices, SUM(invoice_items.quantity) as quantity, SUM(invoice_items.total_amount) as billed, SUM(CASE WHEN invoices.status = 'paid' THEN invoice_items.total_amount ELSE 0 END) as confirmed_collection, SUM(CASE WHEN invoices.status != 'paid' THEN invoice_items.total_amount ELSE 0 END) as open_value")
                ->groupBy('services.id', 'services.name', 'invoice_items.service_name')->orderByDesc('billed')->get()->map(fn ($r) => ['Service' => $r->service, 'Invoices' => $r->invoices, 'Quantity' => $r->quantity, 'Billed' => $this->money($r->billed), 'Confirmed Collection' => $this->money($r->confirmed_collection), 'Open Value' => $this->money($r->open_value), '__drill_key' => (string) $r->service_id])->all();
            return $this->tableData($rows, $periodLabel, 'Confirmed Collection is assigned only for fully paid invoices. Partial receipt allocation to individual service lines needs a future payment-allocation link.');
        }

        if ($report === 'source-referral') {
            $rows = $this->payments($request)->where('payments.type', 'appointment')->selectRaw("sources.id as source_id, COALESCE(sources.name, 'Not set') as source, COUNT(*) as appointments, SUM(CASE WHEN payments.is_followup = 0 THEN 1 ELSE 0 END) as new_visits, SUM(CASE WHEN payments.status IN ('Authorized', 'Captured') THEN payments.amount ELSE 0 END) as collection, SUM(CASE WHEN payments.status IN ('Pending', 'Initiated') THEN payments.amount ELSE 0 END) as pending")
                ->groupBy('sources.id', 'sources.name')->orderByDesc('appointments')->get()->map(fn ($r) => ['Source / Referral' => $r->source, 'Appointments' => $r->appointments, 'New Visits' => $r->new_visits, 'Actual Collection' => $this->money($r->collection), 'Pending Value' => $this->money($r->pending), '__drill_key' => $r->source_id ? (string) $r->source_id : 'not-set'])->all();
            return $this->tableData($rows, $periodLabel, 'Individual referral-doctor performance can be added once a referrer is stored separately from the general source.');
        }

        if ($report === 'patient-visits') {
            $rows = $this->payments($request)->where('payments.type', 'appointment')->selectRaw("CASE WHEN payments.is_followup = 1 THEN 'Follow-up' ELSE 'New Visit' END as visit_type, COUNT(*) as appointments, COUNT(DISTINCT payments.patient_id) as patients, SUM(CASE WHEN payments.status IN ('Authorized','Captured') THEN payments.amount ELSE 0 END) as collection")
                ->groupByRaw("CASE WHEN payments.is_followup = 1 THEN 'Follow-up' ELSE 'New Visit' END")->get()->map(fn ($r) => ['Visit Type' => $r->visit_type, 'Appointments' => $r->appointments, 'Unique Patients' => $r->patients, 'Actual Collection' => $this->money($r->collection), '__drill_key' => $r->visit_type === 'Follow-up' ? 'follow-up' : 'new-visit'])->all();
            return $this->tableData($rows, $periodLabel, 'New Visit uses the clinic booking flag; it is different from a patient-created-date report.');
        }

        if ($report === 'appointment-operations') {
            $rows = $this->payments($request)->where('payments.type', 'appointment')->selectRaw("COALESCE(NULLIF(payments.appointment_status, ''), 'Scheduled') as status, COUNT(*) as appointments, SUM(CASE WHEN payments.status IN ('Authorized','Captured') THEN 1 ELSE 0 END) as paid, SUM(CASE WHEN payments.status IN ('Pending','Initiated') THEN 1 ELSE 0 END) as pending_payment")
                ->groupBy('payments.appointment_status')->orderByDesc('appointments')->get()->map(fn ($r) => ['Appointment Status' => $r->status, 'Appointments' => $r->appointments, 'Paid' => $r->paid, 'Pending Payment' => $r->pending_payment, '__drill_key' => $r->status])->all();
            return $this->tableData($rows, $periodLabel, 'Use the Appointments screen for patient-level status updates and drill-down.');
        }

        if ($report === 'payment-closing') {
            $rows = $this->payments($request)->whereIn('payments.status', self::RECEIVED_STATUSES)->selectRaw("COALESCE(NULLIF(payments.payment_mode, ''), 'Not set') as mode, COUNT(*) as transactions, SUM(payments.amount) as collection, SUM(CASE WHEN payments.type = 'appointment' THEN payments.amount ELSE 0 END) as appointment_collection, SUM(CASE WHEN payments.type = 'service' THEN payments.amount ELSE 0 END) as service_collection")
                ->groupBy('payments.payment_mode')->orderByDesc('collection')->get()->map(fn ($r) => ['Payment Mode' => strtoupper($r->mode), 'Transactions' => $r->transactions, 'Appointment Collection' => $this->money($r->appointment_collection), 'Service Collection' => $this->money($r->service_collection), 'Total Collection' => $this->money($r->collection), '__drill_key' => $r->mode === 'Not set' ? 'not-set' : $r->mode])->all();
            return $this->tableData($rows, $periodLabel, 'Use this as the daily cashier closing and payment-mode reconciliation report.');
        }

        $rows = $this->payments($request)->where('payments.discount_amount', '>', 0)->selectRaw("doctors.id as doctor_id, COALESCE(doctors.name, 'Not assigned') as doctor, payments.type, COUNT(*) as transactions, SUM(payments.doctor_fee + payments.registration_fee) as gross, SUM(payments.discount_amount) as discount, SUM(payments.amount) as net")
            ->groupBy('doctors.id', 'doctors.name', 'payments.type')->orderByDesc('discount')->get()->map(fn ($r) => ['Doctor' => $r->doctor, 'Type' => ucfirst($r->type), 'Transactions' => $r->transactions, 'Gross Value' => $this->money($r->gross), 'Discount Given' => $this->money($r->discount), 'Net Amount' => $this->money($r->net), '__drill_key' => ($r->doctor_id ?: 'not-set') . ':' . $r->type])->all();
        return $this->tableData($rows, $periodLabel, 'Service invoice discounts are tracked separately in billing and will be added to this consolidated report next.');
    }

    private function payments(Request $request)
    {
        $query = DB::table('payments')->leftJoin('doctors', 'doctors.id', '=', 'payments.doctor_id')->leftJoin('sources', 'sources.id', '=', 'payments.source_id')->leftJoin('patients', 'patients.id', '=', 'payments.patient_id')->whereBetween(DB::raw('DATE(payments.created_at)'), [$this->fromDate($request), $this->toDate($request)]);
        if ($request->filled('doctor')) $query->where('payments.doctor_id', $request->doctor);
        if ($request->filled('source_id')) $query->where('payments.source_id', $request->source_id);
        if ($request->filled('payment_mode')) $query->where('payments.payment_mode', $request->payment_mode);
        if ($request->filled('payment_status')) $query->where('payments.status', $request->payment_status);
        return $query;
    }

    private function fromDate(Request $request): string { return $request->input('from_date', now()->startOfMonth()->toDateString()); }
    private function toDate(Request $request): string { return $request->input('to_date', now()->toDateString()); }
    private function money($amount): string
    {
        $amount = (float) $amount;
        $formatted = number_format(abs($amount), 2, '.', '');
        [$whole, $decimal] = explode('.', $formatted);
        $lastThree = substr($whole, -3);
        $remaining = substr($whole, 0, -3);
        $whole = $remaining === '' ? $lastThree : preg_replace('/(\d)(?=(\d{2})+(?!\d))/', '$1,', $remaining) . ',' . $lastThree;

        return 'Rs ' . ($amount < 0 ? '-' : '') . $whole . '.' . $decimal;
    }
    private function tableData(array $rows, string $periodLabel, string $notice): array { return ['rows' => $rows, 'headers' => array_values(array_filter(array_keys($rows[0] ?? ['No data' => '']), fn ($header) => !str_starts_with($header, '__'))), 'periodLabel' => $periodLabel, 'notice' => $notice, 'cards' => []]; }

    private function collectionSummaryDrilldown(Request $request): ?array
    {
        $type = $request->input('drill_down');
        $period = $request->input('period');
        if (!in_array($type, ['transactions', 'consultation', 'registration'], true) || !is_string($period)) {
            return null;
        }

        $isMonthly = $request->input('group_by', 'day') === 'month';
        if ($isMonthly ? !preg_match('/^\d{4}-\d{2}$/', $period) : !preg_match('/^\d{4}-\d{2}-\d{2}$/', $period)) {
            return null;
        }

        try {
            $start = $isMonthly ? Carbon::createFromFormat('Y-m', $period)->startOfMonth() : Carbon::createFromFormat('Y-m-d', $period)->startOfDay();
        } catch (\Exception) {
            return null;
        }
        $end = $isMonthly ? $start->copy()->endOfMonth() : $start->copy()->endOfDay();
        $query = $this->payments($request)
            ->whereIn('payments.status', self::RECEIVED_STATUSES)
            ->whereBetween(DB::raw('DATE(payments.created_at)'), [$start->toDateString(), $end->toDateString()]);

        if ($type === 'transactions') {
            $rows = $query->selectRaw("DATE(payments.created_at) as payment_date, payments.id as receipt, COALESCE(patients.name, 'Patient not set') as patient, COALESCE(doctors.name, 'Not assigned') as doctor, payments.type, COALESCE(NULLIF(payments.payment_mode, ''), 'Not set') as payment_mode, payments.amount")
                ->orderByDesc('payments.created_at')->get()->map(fn ($r) => [
                    'Date' => Carbon::parse($r->payment_date)->format('d M Y'), 'Receipt' => '#' . $r->receipt, 'Patient' => $r->patient, 'Doctor' => $r->doctor, 'Type' => ucfirst($r->type), 'Mode' => strtoupper($r->payment_mode), 'Amount' => $this->money($r->amount),
                ])->all();
            return ['title' => 'Transaction Details', 'rows' => $rows, 'headers' => ['Date', 'Receipt', 'Patient', 'Doctor', 'Type', 'Mode', 'Amount']];
        }

        if ($type === 'consultation') {
            $rows = $query->where('payments.type', 'appointment')->selectRaw("COALESCE(doctors.name, 'Not assigned') as doctor, COUNT(*) as transactions, SUM(COALESCE(payments.doctor_fee, 0)) as consultation_fee")
                ->groupBy('doctors.id', 'doctors.name')->orderByDesc('consultation_fee')->get()->map(fn ($r) => [
                    'Doctor' => $r->doctor, 'Transactions' => $r->transactions, 'Consultation Fee' => $this->money($r->consultation_fee),
                ])->all();
            return ['title' => 'Doctor-wise Consultation Fee', 'rows' => $rows, 'headers' => ['Doctor', 'Transactions', 'Consultation Fee']];
        }

        $rows = $query->where('payments.type', 'appointment')->selectRaw("COALESCE(patients.name, 'Patient not set') as patient, COALESCE(doctors.name, 'Not assigned') as doctor, COUNT(*) as transactions, SUM(COALESCE(payments.registration_fee, 0)) as registration_fee")
            ->groupBy('patients.id', 'patients.name', 'doctors.id', 'doctors.name')->orderByDesc('registration_fee')->get()->map(fn ($r) => [
                'Patient' => $r->patient, 'Doctor' => $r->doctor, 'Transactions' => $r->transactions, 'Registration Fee' => $this->money($r->registration_fee),
            ])->all();
        return ['title' => 'Patient-wise Registration Fee', 'rows' => $rows, 'headers' => ['Patient', 'Doctor', 'Transactions', 'Registration Fee']];
    }

    private function reportDrilldown(Request $request, string $report): ?array
    {
        $value = $request->input('drill_value');
        if (!is_string($value) || $value === '' || $report === 'collection-summary') {
            return null;
        }

        if ($report === 'doctor-collection' && ctype_digit($value)) {
            return $this->transactionDrilldown($this->payments($request)->where('payments.type', 'appointment')->whereIn('payments.status', self::RECEIVED_STATUSES)->where('payments.doctor_id', $value), 'Doctor Collection Details');
        }

        if ($report === 'source-referral') {
            $query = $this->payments($request)->where('payments.type', 'appointment');
            $value === 'not-set' ? $query->whereNull('payments.source_id') : $query->where('payments.source_id', $value);
            return $this->transactionDrilldown($query, 'Source / Referral Details');
        }

        if ($report === 'patient-visits' && in_array($value, ['new-visit', 'follow-up'], true)) {
            return $this->transactionDrilldown($this->payments($request)->where('payments.type', 'appointment')->where('payments.is_followup', $value === 'follow-up' ? 1 : 0), $value === 'follow-up' ? 'Follow-up Visit Details' : 'New Visit Details');
        }

        if ($report === 'appointment-operations') {
            $query = $this->payments($request)->where('payments.type', 'appointment');
            $value === 'Scheduled' ? $query->where(fn ($q) => $q->whereNull('payments.appointment_status')->orWhere('payments.appointment_status', '')) : $query->where('payments.appointment_status', $value);
            return $this->transactionDrilldown($query, $value . ' Appointment Details');
        }

        if ($report === 'payment-closing') {
            $query = $this->payments($request)->whereIn('payments.status', self::RECEIVED_STATUSES);
            $value === 'not-set' ? $query->where(fn ($q) => $q->whereNull('payments.payment_mode')->orWhere('payments.payment_mode', '')) : $query->where('payments.payment_mode', $value);
            return $this->transactionDrilldown($query, strtoupper($value === 'not-set' ? 'Not set' : $value) . ' Payment Details');
        }

        if ($report === 'discount-report') {
            [$doctorId, $type] = array_pad(explode(':', $value, 2), 2, null);
            if (in_array($type, ['appointment', 'service'], true)) {
                $query = $this->payments($request)->where('payments.discount_amount', '>', 0)->where('payments.type', $type);
                $doctorId === 'not-set' ? $query->whereNull('payments.doctor_id') : $query->where('payments.doctor_id', $doctorId);
                return $this->transactionDrilldown($query, ucfirst($type) . ' Discount Details');
            }
        }

        if ($report === 'service-reports' && ctype_digit($value)) {
            $query = DB::table('invoice_items')->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')->leftJoin('patients', 'patients.id', '=', 'invoices.patient_id')->leftJoin('doctors', 'doctors.id', '=', 'invoices.doctor_id')->whereBetween('invoices.invoice_date', [$this->fromDate($request), $this->toDate($request)])->where('invoice_items.service_id', $value);
            if ($request->filled('doctor')) $query->where('invoices.doctor_id', $request->doctor);
            $rows = $query->selectRaw("invoices.invoice_date, COALESCE(invoices.invoice_number, CONCAT('#', invoices.id)) as invoice, COALESCE(patients.name, 'Patient not set') as patient, COALESCE(doctors.name, 'Not assigned') as doctor, invoice_items.quantity, invoice_items.total_amount, invoices.status")
                ->orderByDesc('invoices.invoice_date')->get()->map(fn ($r) => ['Date' => Carbon::parse($r->invoice_date)->format('d M Y'), 'Invoice' => $r->invoice, 'Patient' => $r->patient, 'Doctor' => $r->doctor, 'Quantity' => $r->quantity, 'Billed' => $this->money($r->total_amount), 'Status' => ucfirst($r->status)])->all();
            return ['title' => 'Service Invoice Details', 'rows' => $rows, 'headers' => ['Date', 'Invoice', 'Patient', 'Doctor', 'Quantity', 'Billed', 'Status']];
        }

        return null;
    }

    private function transactionDrilldown($query, string $title): array
    {
        $rows = $query->selectRaw("DATE(payments.created_at) as payment_date, payments.id as receipt, COALESCE(patients.name, 'Patient not set') as patient, COALESCE(doctors.name, 'Not assigned') as doctor, payments.type, COALESCE(NULLIF(payments.payment_mode, ''), 'Not set') as payment_mode, payments.status, payments.amount")
            ->orderByDesc('payments.created_at')->get()->map(fn ($r) => [
                'Date' => Carbon::parse($r->payment_date)->format('d M Y'), 'Receipt' => '#' . $r->receipt, 'Patient' => $r->patient, 'Doctor' => $r->doctor, 'Type' => ucfirst($r->type), 'Mode' => strtoupper($r->payment_mode), 'Status' => $r->status, 'Amount' => $this->money($r->amount),
            ])->all();

        return ['title' => $title, 'rows' => $rows, 'headers' => ['Date', 'Receipt', 'Patient', 'Doctor', 'Type', 'Mode', 'Status', 'Amount']];
    }

    private function ensureReportAccess(): void
    {
        abort_unless(in_array((int) auth()->user()?->role, [1, 3], true), 403);
    }
}
