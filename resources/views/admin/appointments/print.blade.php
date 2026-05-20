<!DOCTYPE html>
<html>
<head>
    <title>Appointments Print</title>
    <style>
        body { font-family: Arial; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px; }
        th { background: #eee; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>

@foreach($appointments as $doctorId => $rows)

    @if(!$loop->first)
        <div class="page-break"></div>
    @endif

    <h3>Doctor: {{ $rows->first()->doctor_name }}</h3>
    <p>Period: {{ \GeneralFunctions::formatDate($fromDate) }} to {{ \GeneralFunctions::formatDate($toDate) }}</p>

    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>Appt No</th>
            <th>Time Slot</th>
            <th>Patient</th>
            <th>Source</th>
            <th>Status</th>
            <th>Gross</th>
            <th>Discount</th>
            <th>Final</th>
        </tr>
        </thead>
        <tbody>
        @foreach($rows as $row)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $row->appointment_no ?? '' }}</td>
                <td>
                    <div><strong>Date: </strong>{{ \GeneralFunctions::formatDate($row['appointment_date']) ?? '' }}</div>
                    <div><strong>Time: </strong>{{ $row['appointment_time'] ?? '' }}</div>
                </td>
                <td>{{ $row->patient_name ?? '' }}</td>
                <td>{{ $row->source_name ?? '-' }}</td>
                <td>
                    @if($row->payment_status === 'Authorized')
                        Paid
                    @elseif(empty($row->payment_status))
                        Pending
                    @else
                        Failed
                    @endif
                </td>
                <td>Rs {{ number_format((float) ($row->gross_amount ?? (($row->doctor_fee ?? 0) + ($row->registration_fee ?? 0))), 2) }}</td>
                <td>{{ number_format((float) ($row->discount_percentage ?? 0), 2) }}% / Rs {{ number_format((float) ($row->discount_amount ?? 0), 2) }}</td>
                <td>Rs {{ number_format((float) ($row->amount ?? 0), 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endforeach

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
