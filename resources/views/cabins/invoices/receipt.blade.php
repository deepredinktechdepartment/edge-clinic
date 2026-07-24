<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Cabin Invoice - {{ $invoice->invoice_number ?? 'N/A' }}</title>

<style>
* { margin:0; padding:0; box-sizing:border-box; }
@page { size:A4; margin:12mm 14mm 22mm; }

html,
body {
    margin: 0;
    padding: 0;
    background: #fff !important;
}

body {
    font-family: Arial, sans-serif;
    font-size: 12px;
    color:#333;
    background: #fff;
}

.letterhead {
    width:100%;
    max-width:100%;
    margin:auto;
    display:block;
}

.header {
    text-align:center;
    padding:6px 0 10px;
    border-bottom:1px solid #666;
}

.logo img { width:82px; }

.content-area {
    display:block;
    padding:14px 4px 34mm;
}

.section-title {
    font-weight:bold;
    margin:0 0 10px;
    padding-bottom:0;
    text-align:center;
    font-size:18px;
}

.details-table {
    width:100%;
    border-collapse:collapse;
    margin-bottom:12px;
}

.details-table td {
    width:50%;
    vertical-align:top;
    padding:0 14px 0 0;
    border:none;
}

.details-table td:last-child {
    padding-right:0;
}

.row { margin-bottom:4px; }
.label { font-weight:bold; }

table {
    width:100%;
    border-collapse:collapse;
    margin-top:8px;
}

table th, table td {
    border:1px solid #ccc;
    padding:8px 9px;
    text-align:left;
    vertical-align:top;
}

table th {
    background:#f3f3f3;
}

table td:last-child,
table th:last-child {
    text-align:right;
}

.summary-table td {
    border:none;
    padding:3px 0;
}

.summary-table td:last-child {
    text-align:right;
}

.footer {
    position: fixed;
    left: 0;
    right: 0;
    bottom: -12mm;
    text-align:center;
    border-top:1px solid #000;
    padding:8px 0 0;
    margin-top:18px;
    font-size:11px;
    background:#fff;
    page-break-inside: avoid;
}

.footer-copy {
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:2px;
}

.footer-copy img {
    margin-top:8px;
    width:40px;
}

@media screen {
    body {
        background:#f4f6f9;
        padding:16px 0;
    }

    .letterhead {
        width: 210mm;
        background:#fff;
        box-shadow:0 10px 28px rgba(16, 24, 40, 0.12);
    }

    .header {
        padding-top:8px;
    }

    .content-area {
        padding:16px 14mm 10px;
    }

    .footer {
        position:absolute;
        left:0;
        right:0;
        bottom:0;
        padding-left: 14mm;
        padding-right: 14mm;
        background:#fff;
    }
}

@media print {
    button { display:none; }
    body {
        background:#fff;
    }

    .letterhead {
        width: 100%;
        margin: 0;
        background:#fff;
    }

    .content-area {
        padding:14px 4px 28mm;
    }

    .footer {
        bottom: -18mm;
        margin-top: 0;
        background:#fff !important;
    }
}
</style>
</head>
<body>

<div class="letterhead">
    <header class="header">
        <div class="logo">
            <img src="{{ asset('storage/app/public/edge_logo.png') }}" alt="Edge Clinic">
        </div>
    </header>

    <main class="content-area">
        <div class="section-title">Cabin Tax Invoice</div>

        <table class="details-table">
            <tr>
                <td>
                    <div class="row"><span class="label">Invoice No:</span> {{ $invoice->invoice_number ?? '-' }}</div>
                    <div class="row"><span class="label">Doctor Name:</span> {{ $invoice->doctor->name ?? '-' }}</div>
                    <div class="row"><span class="label">Department:</span> {{ $invoice->doctor->department->name ?? ($invoice->doctor->designation ?? '-') }}</div>
                    <div class="row"><span class="label">Cabin:</span> {{ $invoice->cabin->cabin_code ?? 'Multiple Cabins' }}</div>
                </td>
                <td>
                    <div class="row"><span class="label">Invoice Date:</span> {{ optional($invoice->invoice_date)->format('d M Y') }}</div>
                    <div class="row"><span class="label">Due Date:</span> {{ optional($invoice->due_date)->format('d M Y') }}</div>
                    <div class="row"><span class="label">Billing {{ optional($invoice->period_start)->isSameDay($invoice->period_end) ? 'Date' : 'Period' }}:</span> {{ optional($invoice->period_start)->format('d M Y') }}@if(!optional($invoice->period_start)->isSameDay($invoice->period_end)) to {{ optional($invoice->period_end)->format('d M Y') }}@endif</div>
                    <div class="row"><span class="label">Status:</span> {{ ucfirst($invoice->status ?? '-') }}</div>
                </td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>{{ $invoice->billing_type === 'hourly' ? 'Hours' : 'Months' }}</th>
                    <th>Rate (Rs)</th>
                    <th>Amount (Rs)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td>{{ number_format((float) $item->quantity, 2) }}</td>
                        <td>{{ number_format((float) $item->unit_rate, 2) }}</td>
                        <td>{{ number_format((float) $item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="summary-table">
            <tr>
                <td></td>
                <td><strong>Subtotal</strong></td>
                <td>{{ number_format((float) $invoice->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td></td>
                <td>GST ({{ number_format((float) $invoice->gst_percent, 2) }}%)</td>
                <td>{{ number_format((float) $invoice->gst_amount, 2) }}</td>
            </tr>
            <tr>
                <td></td>
                <td><strong>Grand Total</strong></td>
                <td><strong>{{ number_format((float) $invoice->total_amount, 2) }}</strong></td>
            </tr>
        </table>

        @if($invoice->notes)
            <div style="margin-top:10px;">
                <div class="label">Notes:</div>
                <div>{{ $invoice->notes }}</div>
            </div>
        @endif
    </main>

    <footer class="footer">
        <div class="footer-copy">
            <p><strong>4th Floor, The Medical Centre, HITEC City</strong></p>
            <p>Survey No. 64, Huda Techno Park, Phase 2, Hyderabad - 500081</p>
            <p>Ph: 9392585050</p>
            <img src="{{ asset('storage/app/public/plus-icons.png')}}" alt="Edge Clinic icon">
        </div>
    </footer>
</div>

</body>
</html>
