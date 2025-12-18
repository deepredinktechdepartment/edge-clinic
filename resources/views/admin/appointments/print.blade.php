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
            <th>Status</th>
            <th>Fee</th>
        </tr>
        </thead>
        <tbody>
        @foreach($rows as $row)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $row->appointment_no ??'' }}</td>
                <td><div><strong>Date: </strong>{{ \GeneralFunctions::formatDate($row['appointment_date']) ??'' }}</div>
                        <div><strong>Time: </strong>{{ $row['appointment_time'] ??'' }}</div></td>
                <td>{{ $row->patient_name ?? '' }}</td>
                <td>{{ $row->payment_status ?? 'Pending' }}</td>
                <td>{{ $row->amount }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endforeach

<script>
    window.onload = function () {
        window.print();

        // When print dialog closes (Print OR Cancel)
        window.onafterprint = function () {
            window.close(); // close print tab
        };
    };
</script>

</body>
</html>
