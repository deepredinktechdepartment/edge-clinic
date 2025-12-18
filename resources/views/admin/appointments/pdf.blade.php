<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Appointments Report</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
        }

        th {
            background: #f2f2f2;
        }

        .doctor-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .page-break {
            page-break-before: always;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>

@foreach($groupedAppointments as $doctorId => $appointments)

    {{-- Page break for every doctor except first --}}
    @if(!$loop->first)
        <div class="page-break"></div>
    @endif

    {{-- Doctor Header --}}
    <div class="doctor-title">
        Doctor: {{ $appointments->first()->doctor_name }}
    </div>

    <div>
        Period: {{ $fromDate }} to {{ $toDate }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Appointment No</th>
                <th>Date</th>
                <th>Time</th>
                <th>Patient</th>
                <th>Mobile</th>
                <th>Status</th>
                <th class="text-right">Fee</th>
            </tr>
        </thead>
        <tbody>
            @foreach($appointments as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $row->appointment_no }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->date)->format('d-m-Y') }}</td>
                    <td>{{ $row->time_slot ?? '-' }}</td>
                    <td>{{ $row->patient_name }}</td>
                    <td>{{ $row->patient_phone }}</td>
                    <td>
                        @if($row->payment_status === 'Authorized')
                            Paid
                        @elseif(empty($row->payment_status))
                            Pending
                        @else
                            Failed
                        @endif
                    </td>
                    <td class="text-right">
                        ₹ {{ number_format($row->fee, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

@endforeach

</body>
</html>
