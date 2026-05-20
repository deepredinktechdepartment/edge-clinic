<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Appointment Receipt - {{ $payment->mocdoc_apptkey ?? 'N/A' }}</title>

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

/* ================= HEADER ================= */
.header {
    text-align:center;
    padding:10px 0;
    border-bottom:1px solid #666;
    margin-bottom:10px;
}

.logo img { width:70px; }

/* ================= CONTENT ================= */
.content-area {
    flex:1;
    padding:10px;
}

.section-title {
    font-weight:bold;
    margin-bottom:6px;
    border-bottom:1px solid #ccc;
    padding-bottom:3px;
}

/* Appointment Details Grid */
.details-grid {
    width:100%;
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:10px 20px;
    margin-bottom:10px;
}

.row { margin-bottom:4px; }

.label { font-weight:bold; }

/* Table */
table {
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}

table th, table td {
    border:1px solid #ccc;
    padding:6px;
    text-align:left;
}

table th {
    background:#f3f3f3;
}

table td:last-child,
table th:last-child {
    text-align:right;
}

.total-row td {
    font-weight:bold;
    font-size:13px;
}

/* ================= FOOTER ================= */
.footer {
    text-align: center;
    border-top: 1px solid #000;
    padding: 12px 0;
    font-size: 11px;
    display: flex;
    gap: 20px;
    justify-content: center;
}

.print-note {
    text-align:center;
    font-size:10px;
    margin-top:8px;
}

@media print {
    button { display:none; }
}
</style>
</head>

<body>


<div class="letterhead">

    <!-- ================= HEADER ================= -->
    <header class="header">
        <div class="logo">
            <img src="{{ asset('storage/app/public/edge_logo.png') }}" alt="Edge Clinic">
        </div>
    </header>

    <!-- ================= CONTENT ================= -->
    <main class="content-area">

        <div class="details-grid">

            <!-- LEFT -->
            <div>
                <div class="row">
                    <span class="label">Appointment No:</span>
                    {{ $payment->mocdoc_apptkey ?? '-' }}
                </div>

                <div class="row">
                    <span class="label">Doctor Name:</span>
                    {{ $payment->doctor_name ?? '-' }}
                </div>
                <div class="row">
                    <span class="label">Patient Name:</span>
                    {{ $payment->patient_name ?? '-' }}
                </div>
                <div class="row">
                    <span class="label">Patient Mobile:</span>
                    {{ $payment->patient_phone ?? '-' }}
                </div>


            </div>

            <!-- RIGHT -->
            <div>
                <div class="row">
                    <span class="label">Appointment Date:</span>
                    {{ \Carbon\Carbon::parse($payment->aptDate)->format('d M Y') }}
                </div>

                <div class="row">
                    <span class="label">Appointment Time:</span>
                    {{ $payment->aptTime }}
                </div>
                <div class="row">
                    <span class="label">Booked On:</span>
                    {{ \Carbon\Carbon::parse($payment->created_at)->format('d M Y h:i A') }}
                </div>
            </div>

        </div>

        <!-- ================= FEE TABLE ================= -->


        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Doctor Consultation Fee</td>
                    <td>{{ number_format($payment->doctor_fee ?? 0, 2) }}</td>
                </tr>

                <tr>
                    <td>
                        Registration Fee
                        @if(($payment->registration_fee ?? 0) > 0)
                            <br>
                            <small>
                                (Valid till:
                                {{ optional($payment)->registration_valid_till
                                    ? \Carbon\Carbon::parse($payment->registration_valid_till)->format('d M Y')
                                    : '-' }})
                            </small>
                        @endif
                    </td>
                    <td>{{ number_format($payment->registration_fee ?? 0, 2) }}</td>
                </tr>

                @if(($payment->discount_amount ?? 0) > 0)
                <tr>
                    <td>
                        Discount
                        <br>
                        <small>{{ number_format($payment->discount_percentage ?? 0, 2) }}%</small>
                    </td>
                    <td>-{{ number_format($payment->discount_amount ?? 0, 2) }}</td>
                </tr>
                @endif

                <tr class="total-row">
                    <td>Total</td>
                    <td>{{ number_format($payment->amount ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>

    </main>

    <!-- ================= FOOTER ================= -->
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

<script>
window.onload = function () {
    window.print();
    window.onafterprint = function () {
        window.close();
    };
};
</script>

</body>
</html>
