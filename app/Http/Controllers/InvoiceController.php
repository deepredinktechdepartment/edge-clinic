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
use App\Models\Service;
use App\Models\Patient;

class InvoiceController extends Controller
{
    /* =========================================
       INDEX
    ========================================= */

    public function index(Request $request)
    {
        $query = Invoice::with('patient')
            ->orderBy('id', 'desc');

        if ($request->search) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%');
        }

        $invoices = $query->paginate(15);

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

        $request->validate([
            'patient_id'   => 'required|exists:patients,id',
            'invoice_date' => 'required|date',
            'items'        => 'required|array|min:1',
        ]);

        // ===============================
        // SAFE INVOICE NUMBER GENERATION
        // ===============================

        $lastInvoice = Invoice::lockForUpdate()->orderBy('id', 'desc')->first();
        $nextNumber  = $lastInvoice ? $lastInvoice->id + 1 : 1;

        $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        // ===============================
        // CREATE INVOICE
        // ===============================

        $invoice = Invoice::create([
            'invoice_number' => $invoiceNumber,
            'order_id'       => $request->order_id ?? null,
            'patient_id'     => $request->patient_id,
            'invoice_date'   => $request->invoice_date,

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
        // LOOP ITEMS
        // ===============================

        foreach ($request->items as $item) {

            $qty  = isset($item['quantity']) ? (float)$item['quantity'] : 0;
            $rate = isset($item['rate']) ? (float)$item['rate'] : 0;

            if ($qty <= 0 || $rate <= 0) {
                continue;
            }

            $amount = $qty * $rate;

            $cgstPercent = isset($item['cgst_percent']) ? (float)$item['cgst_percent'] : 0;
            $sgstPercent = isset($item['sgst_percent']) ? (float)$item['sgst_percent'] : 0;
            $igstPercent = isset($item['igst_percent']) ? (float)$item['igst_percent'] : 0;

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
        // UPDATE TOTALS
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
        // LOG ENTRY
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
        dd($e->getMessage());
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

    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();

        return back()->with('success','Invoice Deleted');
    }

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
    public function pay(Request $request)
{
    $request->validate([
        'invoice_id' => 'required',
        'amount' => 'required|numeric|min:1',
        'payment_mode' => 'required',
        'transaction_number' => 'required_if:payment_mode,upi'
    ]);

    DB::beginTransaction();

    try {

        $invoice = Invoice::findOrFail($request->invoice_id);

        // Generate Payment ID
        $last = InvoicePayment::latest()->first();
        $next = $last ? $last->id + 1 : 1;

        $paymentId = 'PAY-' . date('Y') . '-' . str_pad($next,5,'0',STR_PAD_LEFT);

        InvoicePayment::create([
            'invoice_id' => $invoice->id,
            'payment_id' => $paymentId,
            'amount' => $request->amount,
            'payment_mode' => $request->payment_mode,
            'transaction_number' => $request->transaction_number
        ]);

        // Update invoice amounts
        $invoice->paid_amount += $request->amount;
        $invoice->balance_amount -= $request->amount;

        if($invoice->balance_amount <= 0){
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
}