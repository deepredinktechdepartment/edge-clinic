<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Report</title>

    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; }
        th { background: #f2f2f2; }
        .doctor-title { font-size: 16px; font-weight: bold; }
        .page-break { page-break-before: always; }
        .text-right { text-align: right; }
    </style>
</head>

<body>

@foreach($groupedPayments as $doctorId => $rows)

    @if(!$loop->first)
        <div class="page-break"></div>
    @endif

    <div class="doctor-title">
        Doctor: {{ $rows->first()->doctor_name }}
    </div>

    <div>
        Period:
        {{ $fromDate ?? '-' }} to {{ $toDate ?? '-' }}
    </div>

    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>Appointment No</th>
            <th>Time Slot</th>
            <th>Patient</th>
            <th>Payment ID</th>
            <th>Status</th>
            <th class="text-right">Amount</th>
        </tr>
        </thead>
        <tbody>
        @foreach($rows as $row)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $row->appointment_no }}</td>
                <td><div><strong>Date:</strong> {{ \GeneralFunctions::formatDate($row['appointment_date']) ??'' }}</div>
                        <div><strong>Time:</strong> {{ $row['appointment_time'] ??'' }}</div></td>
                <td>
                    {{ $row->patient_name ??'' }}<br>
                    {{ $row->patient_phone ?? '' }}
                </td>
                <td>{{ $row->payment_id }}</td>
                <td>
                    @if($row->status === 'Authorized')
                        Paid
                    @else
                        Failed
                    @endif
                </td>
                <td class="text-right">
                    ₹ {{ number_format($row->amount, 2) }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endforeach

</body>
</html>
