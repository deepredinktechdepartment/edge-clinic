<!DOCTYPE html>
<html>
<head>
    <title>Appointment Invoice</title>

    <style>
        /* Half A4 page */
        @page {
            size: A5 portrait;
            margin: 8mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .invoice-box {
            width: 100%;
            border: 1px solid #000;
            padding: 12px;
            box-sizing: border-box;
        }

        h1 {
            font-size: 18px;
            margin: 0;
            text-align: center;
            letter-spacing: 1px;
        }

        .clinic-address {
            text-align: center;
            font-size: 10px;
            margin-bottom: 6px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .section div {
            margin-bottom: 3px;
        }

        .label {
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        table th,
        table td {
            padding: 5px 4px;
            font-size: 12px;
        }

        table th {
            border-bottom: 1px solid #000;
            text-align: left;
        }

        table th:last-child,
        table td:last-child {
            text-align: right;
        }

        .total-row td {
            border-top: 1px solid #000;
            font-weight: bold;
            font-size: 13px;
        }

        .footer {
            margin-top: 8px;
            text-align: center;
            font-size: 10px;
        }
        .details-grid {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            column-gap: 20px;
            font-size: 12px;
        }

        .details-grid .row {
            margin-bottom: 4px;
        }

        .details-grid .label {
            font-weight: bold;
        }


        @media print {
            button {
                display: none;
            }
        }
    </style>
</head>
<body>

<button onclick="window.print()">Print</button>

<div class="invoice-box">

    <!-- Clinic Header -->
    <h1>EDGE CLINIC</h1>
<div class="clinic-address">
    4th Floor, 8-2-293/82/A/1355-H/403<br>
    Niharika Jubilee One, Road No 1, Jubilee Hills<br>
    Hyderabad, Telangana – 500033<br>
    Phone: +91 63021 62484
</div>


    <div class="divider"></div>

    <!-- Appointment Details -->
    <div class="details-grid">

    <!-- LEFT COLUMN -->
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
            <span class="label">Booked On:</span>
            {{ \Carbon\Carbon::parse($payment->created_at)->format('d M Y h:i A') }}
        </div>
    </div>

    <!-- RIGHT COLUMN -->
    <div>
        <div class="row">
            <span class="label">Appointment Date:</span>
            {{ \Carbon\Carbon::parse($payment->aptDate)->format('d M Y') }}
        </div>

        <div class="row">
            <span class="label">Appointment Time:</span>
            {{ $payment->aptTime }}
        </div>
    </div>

</div>


    <div class="divider"></div>

    <!-- Fee Table -->
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
                <td>{{ number_format($payment->doctor_fee, 2) }}</td>
            </tr>

            <tr>
                <td>
                    Registration Fee
                    @if($payment->registration_fee > 0)
                        <br>
                        <small>
                            (Valid till:
                            {{ optional($payment)->registration_valid_till
                                ? \Carbon\Carbon::parse($payment->registration_valid_till)->format('d M Y')
                                : '-' }})
                        </small>
                    @endif
                </td>
                <td>{{ number_format($payment->registration_fee, 2) }}</td>
            </tr>

            <tr class="total-row">
                <td>Total</td>
                <td>{{ number_format($payment->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        This is a computer-generated receipt
    </div>

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
