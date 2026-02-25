<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice - {{ $invoice->invoice_number ?? 'N/A' }}</title>

<style>
* { margin:0; padding:0; box-sizing:border-box; }

@page { size:A5; margin:15mm; }

body {
    font-family: Arial, sans-serif;
    font-size: 12px;
    color:#333;
}

.letterhead {
    width:148mm;
    margin:auto;
    display:flex;
    flex-direction:column;
}

.header {
    text-align:center;
    padding:10px 0;
    border-bottom:1px solid #666;
    margin-bottom:10px;
}

.logo img { width:70px; }

.content-area { flex:1; padding:10px; }

.invoice-header {
    display:flex;
    justify-content:space-between;
    margin-bottom:15px;
}

.invoice-box {
    border:1px solid #ccc;
    padding:8px;
    width:48%;
}

table {
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}

table th, table td {
    border:1px solid #ccc;
    padding:6px;
    text-align:center;
}

table th {
    background:#f3f3f3;
}

.text-left { text-align:left; }
.text-right { text-align:right; }

.summary {
    margin-top:10px;
    width:100%;
}

.summary td {
    border:none;
    padding:4px;
}

.footer {
    text-align: center;
    border-top: 1px solid #000;
    padding: 15px 0;
    font-size: 12px;
    display: flex;
    gap: 20px;
    justify-content: center;
}

.no-data {
    text-align:center;
    padding:15px;
    color:#999;
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

    {{-- ================= PATIENT & INVOICE INFO ================= --}}
    <div class="invoice-header">

        <div class="invoice-box">
            <strong>Invoice To:</strong><br>

            @if($invoice->patient)
                {{ ucfirst($invoice->patient->name ?? '') }}<br>
                {{ $invoice->patient->mobile ?? '' }}<br>
                {{ $invoice->patient->email ?? '' }}<br>
                {{ $invoice->patient->address ?? '' }}
            @else
                <span class="no-data">Patient Data Not Available</span>
            @endif
        </div>

        <div class="invoice-box">
            <strong>Invoice No:</strong> {{ $invoice->invoice_number ?? '-' }}<br>
            <strong>Date:</strong>
            {{ $invoice->invoice_date ? date('d-m-Y', strtotime($invoice->invoice_date)) : '-' }}<br>
            <strong>Status:</strong> {{ ucfirst($invoice->status ?? '-') }}
        </div>

    </div>

    {{-- ================= ITEMS TABLE ================= --}}
    @php
        $showCGST = $invoice->total_cgst > 0;
        $showSGST = $invoice->total_sgst > 0;
        $showIGST = $invoice->total_igst > 0;
    @endphp

    <table>
        <thead>
            <tr>
                <th class="text-left">Service</th>
                <th>Qty</th>
                <th>Rate</th>

                @if($showCGST)
                    <th>CGST</th>
                @endif

                @if($showSGST)
                    <th>SGST</th>
                @endif

                @if($showIGST)
                    <th>IGST</th>
                @endif

                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>

        @if($invoice->items && $invoice->items->count() > 0)

            @foreach($invoice->items as $item)
                <tr>
                    <td class="text-left">{{ $item->service_name ?? '-' }}</td>
                    <td>{{ $item->quantity ?? 0 }}</td>
                    <td>{{ number_format($item->rate ?? 0,2) }}</td>

                    @if($showCGST)
                        <td>{{ number_format($item->cgst_amount ?? 0,2) }}</td>
                    @endif

                    @if($showSGST)
                        <td>{{ number_format($item->sgst_amount ?? 0,2) }}</td>
                    @endif

                    @if($showIGST)
                        <td>{{ number_format($item->igst_amount ?? 0,2) }}</td>
                    @endif

                    <td class="text-right">{{ number_format($item->total_amount ?? 0,2) }}</td>
                </tr>
            @endforeach

        @else
            <tr>
                <td colspan="7" class="no-data">
                    No Invoice Items Found
                </td>
            </tr>
        @endif

        </tbody>
    </table>

    {{-- ================= SUMMARY ================= --}}
    <table class="summary">
        <tr>
            <td></td>
            <td class="text-right"><strong>Taxable Value:</strong></td>
            <td class="text-right">{{ number_format($invoice->sub_total ?? 0,2) }}</td>
        </tr>

        @if($showCGST)
        <tr>
            <td></td>
            <td class="text-right">Total CGST:</td>
            <td class="text-right">{{ number_format($invoice->total_cgst ?? 0,2) }}</td>
        </tr>
        @endif

        @if($showSGST)
        <tr>
            <td></td>
            <td class="text-right">Total SGST:</td>
            <td class="text-right">{{ number_format($invoice->total_sgst ?? 0,2) }}</td>
        </tr>
        @endif

        @if($showIGST)
        <tr>
            <td></td>
            <td class="text-right">Total IGST:</td>
            <td class="text-right">{{ number_format($invoice->total_igst ?? 0,2) }}</td>
        </tr>
        @endif

        <tr>
            <td></td>
            <td class="text-right"><strong>Grand Total:</strong></td>
            <td class="text-right">
                <strong>{{ number_format($invoice->grand_total ?? 0,2) }}</strong>
            </td>
        </tr>
    </table>

</main>

<footer class="footer">
    <div>
        <p><strong>4th Floor, The Medical Centre, HITEC City</strong></p>
        <p>Survey No. 64, Huda Techno Park, Phase 2, Hyderabad - 500081</p>
        <p>Ph: 9392585050</p>
    </div>

    <div>
        <img src="{{ asset('storage/app/public/plus-icons.png')}}" alt="Edge Clinic icon" class="img-fluid" width="40px">
    </div>
</footer>

</div>

</body>
</html>