<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceLog;
use App\Models\InvoicePayment;
use App\Models\Order;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\Patient;

class InvoiceController extends Controller
{
    /* =========================================
       INDEX
    ========================================= */

    public function index(Request $request)
{
    $invoices = Invoice::with('patient')
        ->orderBy('invoice_number', 'desc')->get();



    $pageTitle = "Invoices";
    $addlink   = route('admin.invoices.create');

    return view('invoices.index', compact('invoices','pageTitle','addlink'));
}

    /* =========================================
       CREATE
    ========================================= */


public function create(Request $request)
{
    $order = null;
    $patient = null;

    // If order_id passed from appointment page
    if ($request->order_id) {

        $order = Order::where('order_id', $request->order_id)->first();

        if ($order) {
            $patient = Patient::find($order->patient_id);
        }
    }

    // Load all patients for search dropdown
    $patients = Patient::select('id','name','mobile','email')->get();

    // Load services
    $services = Service::whereNotNull('parent_id')->get();

    // Auto Invoice Number
    $lastInvoice = Invoice::latest()->first();
    $nextNumber = $lastInvoice ? $lastInvoice->id + 1 : 1;
    $autoInvoiceNumber = 'INV/'.date('Y').'/'.$nextNumber;

    $pageTitle = "Create Invoice";

    return view('invoices.create', compact(
        'order',
        'patient',
        'patients',
        'services',
        'autoInvoiceNumber',
        'pageTitle'
    ));
}

    /* =========================================
       STORE
    ========================================= */

    public function store(Request $request)
{
    DB::beginTransaction();

    try {


        // ===============================
        // VALIDATION
        // ===============================
        $request->validate([
            'patient_id'   => 'required|exists:patients,id',
            'invoice_date' => 'required|date',
            'items'        => 'required|array|min:1',
            'appointment_no' => 'required',
        ]);

        // 🔥 Filter valid items (avoid blank rows)
        $validItems = collect($request->items ?? [])
            ->filter(function ($item) {
                return isset($item['quantity'], $item['rate']) &&
                       $item['quantity'] > 0 &&
                       $item['rate'] > 0;
            });

        if ($validItems->count() == 0) {
            return back()->withInput()
                ->with('error', 'Please add at least one valid item.');
        }

        // ===============================
        // SAFE INVOICE NUMBER GENERATION
        // ===============================
        $lastInvoice = Invoice::lockForUpdate()->orderBy('id', 'desc')->first();
        $nextNumber  = $lastInvoice ? $lastInvoice->id + 1 : 1;

        $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        // ===============================
        // CREATE INVOICE (YOUR ORIGINAL STRUCTURE KEPT)
        // ===============================
        $invoice = Invoice::create([
            'invoice_number' => $invoiceNumber,
            'order_id'       => $request->order_id ?? null,
            'patient_id'     => $request->patient_id,
            'invoice_date'   => $request->invoice_date,
            'appointment_no' => $request->appointment_no,
            'doctor_id'      => $request->doctor_id,
            'sub_total'      => 0,
            'total_cgst'     => 0,
            'total_sgst'     => 0,
            'total_igst'     => 0,
            'tax_total'      => 0,
            'grand_total'    => 0,
            'paid_amount'    => 0,
            'balance_amount' => 0,
            'status'         => 'draft',
        ]);

        $subTotal   = 0;
        $totalCGST  = 0;
        $totalSGST  = 0;
        $totalIGST  = 0;

        // ===============================
        // LOOP ITEMS (FOREACH SAFE)
        // ===============================
        foreach ($validItems as $item) {

            $qty  = (float)$item['quantity'];
            $rate = (float)$item['rate'];

            $amount = $qty * $rate;

            $cgstPercent = (float)($item['cgst_percent'] ?? 0);
            $sgstPercent = (float)($item['sgst_percent'] ?? 0);
            $igstPercent = (float)($item['igst_percent'] ?? 0);

            $cgstAmount = ($amount * $cgstPercent) / 100;
            $sgstAmount = ($amount * $sgstPercent) / 100;
            $igstAmount = ($amount * $igstPercent) / 100;

            $totalAmount = $amount + $cgstAmount + $sgstAmount + $igstAmount;

            InvoiceItem::create([
                'invoice_id'    => $invoice->id,
                'service_id'    => $item['service_id'] ?? null,
                'service_name'  => $item['service_name'] ?? null,
                'quantity'      => $qty,
                'rate'          => $rate,
                'amount'        => $amount,

                'cgst_percent'  => $cgstPercent,
                'sgst_percent'  => $sgstPercent,
                'igst_percent'  => $igstPercent,

                'cgst_amount'   => $cgstAmount,
                'sgst_amount'   => $sgstAmount,
                'igst_amount'   => $igstAmount,

                'total_amount'  => $totalAmount,
            ]);

            $subTotal  += $amount;
            $totalCGST += $cgstAmount;
            $totalSGST += $sgstAmount;
            $totalIGST += $igstAmount;
        }

        // ===============================
        // UPDATE TOTALS (YOUR LOGIC KEPT)
        // ===============================
        $taxTotal   = $totalCGST + $totalSGST + $totalIGST;
        $grandTotal = $subTotal + $taxTotal;

        $invoice->update([
            'sub_total'      => $subTotal,
            'total_cgst'     => $totalCGST,
            'total_sgst'     => $totalSGST,
            'total_igst'     => $totalIGST,
            'tax_total'      => $taxTotal,
            'grand_total'    => $grandTotal,
            'balance_amount' => $grandTotal,
        ]);

        // ===============================
        // LOG ENTRY (YOUR ORIGINAL KEPT)
        // ===============================
        InvoiceLog::create([
            'invoice_id' => $invoice->id,
            'action'     => 'Invoice Created',
            'remarks'    => 'Invoice created successfully',
        ]);

        DB::commit();

        return redirect()
            ->route('admin.invoices.index')
            ->with('success', 'Invoice Created Successfully');

    } catch (\Exception $e) {

        DB::rollBack();

        return back()
            ->withInput()
            ->with('error', $e->getMessage());
    }
}

/* =========================================
       Edit
    ========================================= */

public function edit(Invoice $invoice)
{
    if ($invoice->status !== 'draft') {
        return redirect()->route('admin.invoices.index')
            ->with('error', 'Only draft invoices can be edited.');
    }

    // 🔥 THIS IS THE IMPORTANT LINE
    $invoice->load('items');

    $services = Service::whereNotNull('parent_id')->get();
    $patients = Patient::all();

    $pageTitle = "Edit Invoice";

    return view('invoices.create', compact(
        'invoice',
        'services',
        'patients',
        'pageTitle'
    ));
}

/* =========================================
       Update
    ========================================= */
public function update(Request $request, Invoice $invoice)
{
    DB::beginTransaction();

    try {

        // ===============================
        // ONLY DRAFT CAN BE EDITED
        // ===============================
        if ($invoice->status !== 'draft') {
            return redirect()->route('admin.invoices.index')
                ->with('error', 'Only draft invoices can be edited.');
        }

        // ===============================
        // VALIDATION
        // ===============================
        $request->validate([
            'patient_id'   => 'required|exists:patients,id',
            'invoice_date' => 'required|date',
            'items'        => 'required|array|min:1',
            'appointment_no' => 'required',
        ]);

        // 🔥 Filter valid items (avoid blank rows)
        $validItems = collect($request->items ?? [])
            ->filter(function ($item) {
                return isset($item['quantity'], $item['rate']) &&
                       $item['quantity'] > 0 &&
                       $item['rate'] > 0;
            });

        if ($validItems->count() == 0) {
            return back()->withInput()
                ->with('error', 'Please add at least one valid item.');
        }

        // ===============================
        // UPDATE BASIC INVOICE DETAILS
        // ===============================
        $invoice->update([
            'order_id'     => $request->order_id ?? null,
            'patient_id'   => $request->patient_id,
            'invoice_date' => $request->invoice_date,
            'appointment_no' => $request->appointment_no,
            'doctor_id'      => $request->doctor_id,
        ]);

        // ===============================
        // DELETE OLD ITEMS
        // ===============================
        $invoice->items()->delete();

        $subTotal   = 0;
        $totalCGST  = 0;
        $totalSGST  = 0;
        $totalIGST  = 0;

        // ===============================
        // LOOP ITEMS (SAME LOGIC AS STORE)
        // ===============================
        foreach ($validItems as $item) {

            $qty  = (float)$item['quantity'];
            $rate = (float)$item['rate'];

            $amount = $qty * $rate;

            $cgstPercent = (float)($item['cgst_percent'] ?? 0);
            $sgstPercent = (float)($item['sgst_percent'] ?? 0);
            $igstPercent = (float)($item['igst_percent'] ?? 0);

            $cgstAmount = ($amount * $cgstPercent) / 100;
            $sgstAmount = ($amount * $sgstPercent) / 100;
            $igstAmount = ($amount * $igstPercent) / 100;

            $totalAmount = $amount + $cgstAmount + $sgstAmount + $igstAmount;

            InvoiceItem::create([
                'invoice_id'    => $invoice->id,
                'service_id'    => $item['service_id'] ?? null,
                'service_name'  => $item['service_name'] ?? null,
                'quantity'      => $qty,
                'rate'          => $rate,
                'amount'        => $amount,

                'cgst_percent'  => $cgstPercent,
                'sgst_percent'  => $sgstPercent,
                'igst_percent'  => $igstPercent,

                'cgst_amount'   => $cgstAmount,
                'sgst_amount'   => $sgstAmount,
                'igst_amount'   => $igstAmount,

                'total_amount'  => $totalAmount,
            ]);

            $subTotal  += $amount;
            $totalCGST += $cgstAmount;
            $totalSGST += $sgstAmount;
            $totalIGST += $igstAmount;
        }

        // ===============================
        // UPDATE TOTALS (SAME STRUCTURE)
        // ===============================
        $taxTotal   = $totalCGST + $totalSGST + $totalIGST;
        $grandTotal = $subTotal + $taxTotal;

        $invoice->update([
            'sub_total'      => $subTotal,
            'total_cgst'     => $totalCGST,
            'total_sgst'     => $totalSGST,
            'total_igst'     => $totalIGST,
            'tax_total'      => $taxTotal,
            'grand_total'    => $grandTotal,
            'balance_amount' => max(0, $grandTotal - ($invoice->paid_amount ?? 0)),
        ]);

        // ===============================
        // LOG ENTRY (ADDED FOR UPDATE)
        // ===============================
        InvoiceLog::create([
            'invoice_id' => $invoice->id,
            'action'     => 'Invoice Updated',
            'remarks'    => 'Invoice updated successfully',
        ]);

        DB::commit();

        return redirect()
            ->route('admin.invoices.index')
            ->with('success', 'Invoice Updated Successfully');

    } catch (\Exception $e) {

        DB::rollBack();

        return back()
            ->withInput()
            ->with('error', $e->getMessage());
    }
}

    /* =========================================
       SHOW
    ========================================= */

    public function show($id)
    {
        $invoice = Invoice::with('items','patient')->findOrFail($id);

        return view('invoices.show', compact('invoice'));
    }

    /* =========================================
       DELETE
    ========================================= */



    public function getPatient($id)
    {
        $patient = Patient::find($id);
        return response()->json($patient);
    }

    public function getOrders($id)
    {
        $orders = Order::where('patient_id',$id)
            ->where('status','!=','completed')   // Not completed only
            ->latest()
            ->get();

        return response()->json($orders);
    }
//     public function pay(Request $request)
// {
//     $request->validate([
//         'invoice_id' => 'required',
//         'amount' => 'required|numeric|min:1',
//         'payment_mode' => 'required',
//         'transaction_number' => 'required_if:payment_mode,upi'
//     ]);

//     DB::beginTransaction();

//     try {

//         $invoice = Invoice::findOrFail($request->invoice_id);

//         // Generate Payment ID
//         $last = InvoicePayment::latest()->first();
//         $next = $last ? $last->id + 1 : 1;

//         $paymentId = 'PAY-' . date('Y') . '-' . str_pad($next,5,'0',STR_PAD_LEFT);

//         InvoicePayment::create([
//             'invoice_id' => $invoice->id,
//             'payment_id' => $paymentId,
//             'amount' => $request->amount,
//             'payment_mode' => $request->payment_mode,
//             'transaction_number' => $request->transaction_number
//         ]);

//         // Update invoice amounts
//         $invoice->paid_amount += $request->amount;
//         $invoice->balance_amount -= $request->amount;

//         if($invoice->balance_amount <= 0){
//             $invoice->status = 'paid';
//             $invoice->balance_amount = 0;
//         } else {
//             $invoice->status = 'partial';
//         }

//         $invoice->save();

//         DB::commit();

//         return back()->with('success','Payment Added Successfully');

//     } catch (\Exception $e) {

//         DB::rollBack();
//         return back()->with('error',$e->getMessage());
//     }
// }

public function pay(Request $request)
{
    $request->validate([
        'invoice_id'        => 'required|exists:invoices,id',
        'amount'            => 'required|numeric|min:1',
        'payment_mode'      => 'required',
        'transaction_number'=> 'required_if:payment_mode,upi'
    ]);

    DB::beginTransaction();

    try {

        // 🔒 Lock invoice row
        $invoice = Invoice::where('id', $request->invoice_id)
            ->lockForUpdate()
            ->firstOrFail();

        // 🚫 Prevent payment if already paid
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Invoice already fully paid.');
        }

        // 🚫 Prevent overpayment
        if ($request->amount > $invoice->balance_amount) {
            return back()->with('error', 'Payment exceeds remaining balance.');
        }

        // ===============================
        // Generate Payment ID
        // ===============================
        $last = InvoicePayment::lockForUpdate()->latest()->first();
        $next = $last ? $last->id + 1 : 1;

        $paymentId = 'PAY-' . date('Y') . '-' . str_pad($next,5,'0',STR_PAD_LEFT);

        // ===============================
        // 1️⃣ Insert into invoice_payments table
        // ===============================
        InvoicePayment::create([
            'invoice_id'        => $invoice->id,
            'payment_id'        => $paymentId,
            'amount'            => $request->amount,
            'payment_mode'      => $request->payment_mode,
            'transaction_number'=> $request->transaction_number ?? null
        ]);

        // ===============================
        // 2️⃣ Insert into main payments table
        // ===============================
        DB::table('payments')->insert([
            'payment_id'      => $paymentId,
            'mocdoc_apptkey'  => $invoice->appointment_no,
            'order_id'        => $invoice->order_id??null,
            'patient_id'      => $invoice->patient_id,
            'doctor_id'       => $invoice->doctor_id ?? 0,
            'amount'          => $request->amount,
            'currency'        => 'INR',
            'type'            => 'service',
            'status'          => 'Authorized',
            'payment_mode'    => $request->payment_mode,
            'reference_no'    => $request->transaction_number ?? null,
            'notes'           => 'Service Invoice Payment',
            'ip_address'      => $request->ip(),
            'user_agent'      => $request->userAgent(),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // ===============================
        // 3️⃣ Update Invoice
        // ===============================
        $invoice->paid_amount += $request->amount;
        $invoice->balance_amount -= $request->amount;

        if ($invoice->balance_amount <= 0) {
            $invoice->status = 'paid';
            $invoice->balance_amount = 0;
        } else {
            $invoice->status = 'partial';
        }

        $invoice->save();

        DB::commit();

        return back()->with('success','Payment Added Successfully');

    } catch (\Exception $e) {

        DB::rollBack();
        return back()->with('error',$e->getMessage());
    }
}

public function getLatestAppointment($patientId)
{
    $appointment = DB::table('payments')
        ->where('patient_id', $patientId)
        ->whereNotNull('mocdoc_apptkey')
        ->where('status', '!=', 'completed')
        ->orderByDesc('id')
        ->first();

    if (!$appointment) {
        return response()->json([]);
    }

    $doctor = Doctor::find($appointment->doctor_id);

    return response()->json([
        'appointment_id' => $appointment->id,
        'appointment_no' => $appointment->mocdoc_apptkey,
        'doctor_id'      => $appointment->doctor_id,
        'doctor_name'    => $doctor->name ?? '',
        'apt_date'       => $appointment->aptDate,
        'apt_time'       => $appointment->aptTime
    ]);
}
}