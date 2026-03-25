@if(count($list) > 0)
<div class="t-job-sheet container-fluid g-0">
    <div class="t-table table-responsive">
        <table class="table table-borderless table-hover" id="default-datatable" style="width: 100%;">
        <thead>
        <tr>
            <th>#</th> <!-- Serial Number -->
            <th>Appointment No </th>
            <th>Time Slot</th>
            <th>Doctor</th>
            <th>Patient Details</th>
            <th>Reg. Fee </th>
            <th>Doctor Fee </th>
            <th>Amount</th>
            <th>Payment Status</th> <!-- New column -->
            <th>Status</th> <!-- New column -->
            <th>Visit</th> <!-- New column -->
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
            @forelse($list as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td> <!-- Serial Number -->

                    <!-- Appointment Details -->
                   <td>
    <a href="javascript:void(0);"
       class="afontopt appointment-log-link "
       data-id="{{ $row['id'] }}">
        {{ $row['appointment_no'] ?? '' }}
    </a>
</td>
                   <td>
    @if(!empty($row['appointment_date']) && !empty($row['appointment_time']))
        <div>{{ \GeneralFunctions::formatDate($row['appointment_date']) }}, {{ $row['appointment_time'] }}</div>
    @endif
</td>
                    <!-- Doctor -->
                    <td>{{ Str::title($row['doctor_name']) ??'' }}</td>

                    <!-- Patient Details -->
                    <td>
                        {{ Str::title($row['patient_name'])??'' }}<br>
                        {{ $row['patient_phone'] ?? '-' }}
                    </td>
                    <td>₹ {{ number_format($row['doctor_fee'], 2) ?? '' }}</td>
                    <td>₹ {{ number_format($row['registration_fee'], 2) ?? '' }}</td>
                    <!-- Fee -->
                    <td>₹ {{ number_format($row['amount'], 2) ?? '' }}</td>

                    <!-- Payment Details -->
                    <td>

                        <div>

                            @if($row['status'] === 'Authorized')
                                Payment is successful
                            @else
                                Payment failed
                            @endif
                        </div>
                    </td>

                    <td>
    @php
        $status = $row['appointment_status'] ?? 'Scheduled';

        $statusColor = match($status) {
            'Scheduled' => '#6c757d',       // grey
            'Checked-In' => '#0dcaf0',      // blue
            'In-Consultation' => '#0d6efd', // darker blue
            'Checked-Out' => '#ffc107',     // yellow
            'Completed' => '#198754',       // green
            'Cancelled' => '#dc3545',       // red
            default => '#e0e0e0',           // light grey
        };
    @endphp

    <span id="status-{{ $row['id'] }}" style="color: {{ $statusColor }};">
        {{ $status }}
    </span>
</td>
<td>
    @if($row['is_followup'] == 0)
    Main Visit
    @else
        Followup Visit
    @endif
</td>
        <td>
    @if(($row['appointment_status'] ?? 'Scheduled') !== 'Completed')
        <button class="btn btn-sm btn-outline-primary open-status-modal"
                data-id="{{ $row['id'] }}"
                data-status="{{ $row['appointment_status'] ?? 'Scheduled' }}">
            Update
        </button>
    @endif

    <a href="{{ route('consultations.create', ['payment_id' => $row['id']]) }}"
       class="btn btn-sm btn-outline-success" target="_blank">
        Current Visit
    </a>

    @if(!empty($row['payment_id']))
    <a href="{{ route('invoice.appointment', ['paymentId' => $row['payment_id']]) }}"
       target="_blank"
       class="btn btn-sm btn-outline-primary">
        Print Invoice
    </a>
@else

@endif

{{-- SEND SMS FOR OFFLINE APPOINTMENT --}}
{{-- SEND SMS FOR OFFLINE APPOINTMENT --}}
@if(
    !empty($row['payment_id']) &&
    (
        ($row['appointment_status'] ?? 'Scheduled') == 'Scheduled' ||
        ($row['appointment_status'] ?? '') == ''
    ) &&
    ($row['sms_delivered'] ?? 0) == 0
)

<button class="btn btn-sm btn-outline-success send-appointment-sms"
        data-id="{{ $row['id'] }}">
    Send SMS
</button>

@endif


</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">No appointments found</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
</div>
@else
<div class="text-center text-muted p-3">
    No records found
</div>
@endif


