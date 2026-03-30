@if(count($list) > 0)
<div class="t-job-sheet container-fluid g-0">
    <div class="t-table table-responsive">
        <table class="table table-borderless table-hover" id="default-datatable" style="width: 100%;">
        <thead>
        <tr>
            <th>#</th>
            <th>Appointment No</th>
            <th>Time Slot</th>
            <th>Doctor</th>
            <th>Patient Details</th>
            <th>Amount</th>
            <th>Payment Status</th>
            <th>Status</th>
            <th>Visit</th>
            <th>Action</th>
        </tr>
        </thead>

        <tbody>
        @forelse($list as $row)
            <tr>
                <td>{{ $loop->iteration }}</td>

                <!-- Appointment No -->
                <td>
                    <a href="javascript:void(0);"
                       class="afontopt appointment-log-link"
                       data-id="{{ $row->id }}">
                        {{ $row->appointment_no ?? '' }}
                    </a>
                </td>

                <!-- Date + Time -->
                <td>
                    @if(!empty($row->appointment_date) && !empty($row->appointment_time))
                        <div>
                            {{ \GeneralFunctions::formatDate($row->appointment_date) }},
                            {{ $row->appointment_time }}
                        </div>
                    @endif
                </td>

                <!-- Doctor -->
                <td>{{ Str::title($row->doctor_name ?? '') }}</td>

                <!-- Patient -->
                <td>
                    {{ Str::title($row->patient_name ?? '') }}<br>
                    {{ $row->patient_phone ?? '-' }}
                </td>

                <!-- Amount -->
                @if(auth()->user()->role != 5)
                <td>₹ {{ number_format($row->amount ?? 0, 2) }}</td>
                @else
                <td>₹ {{ number_format($row->doctor_fee ?? 0, 2) }}</td>
                @endif

                <!-- Payment Status -->
                <td>
                    @if(($row->payment_mode ?? '') != 'free')
                    @if(($row->payment_status ?? '') === 'success')
                        <span class="text-success">Paid</span>
                    @elseif(($row->payment_status ?? '') === 'failed')
                        <span class="text-danger">Failed</span>
                    @else
                        <span class="text-warning">Pending</span>
                    @endif
                    @else
                        <span class="text-warning">Free</span>
                    @endif
                </td>

                <!-- Appointment Status -->
                <td>
                    @php
                        $status = $row->appointment_status ?? 'Scheduled';

                        $statusColor = match($status) {
                            'Scheduled' => '#6c757d',
                            'Checked-In' => '#0dcaf0',
                            'In-Consultation' => '#0d6efd',
                            'Checked-Out' => '#ffc107',
                            'Completed' => '#198754',
                            'Cancelled' => '#dc3545',
                            default => '#e0e0e0',
                        };
                    @endphp

                    <span id="status-{{ $row->id }}" style="color: {{ $statusColor }};">
                        {{ $status }}
                    </span>
                </td>

                <!-- Visit -->
                <td>
                    @if(($row->is_followup ?? 0) == 0)
                        Main Visit
                    @else
                        Followup Visit
                    @endif
                </td>

                <!-- Actions -->
                <td>

                    @if(($row->appointment_status ?? 'Scheduled') !== 'Completed')
                        <button class="btn btn-sm btn-outline-primary open-status-modal"
                                data-id="{{ $row->id }}"
                                data-status="{{ $row->appointment_status ?? 'Scheduled' }}">
                            Update
                        </button>
                    @endif

                    @if($row->consultation_id)
                        <a href="{{ route('consultations.edit', $row->consultation_id) }}"
                        class="btn btn-sm btn-success" target="_blank">
                            View Visit
                        </a>
                    @else
                        <a href="{{ route('consultations.create', ['appointment_id' => $row->id]) }}"
                        class="btn btn-sm btn-outline-success" target="_blank">
                            Current Visit
                        </a>
                    @endif

                    @if(!empty($row->payment_id))
                        <a href="{{ route('invoice.appointment', ['paymentId' => $row->payment_id]) }}"
                           target="_blank"
                           class="btn btn-sm btn-outline-primary">
                            Print Invoice
                        </a>
                    @endif

                    {{-- SEND SMS --}}
                    @if(
                        !empty($row->payment_id) &&
                        ($row->appointment_status ?? 'Scheduled') == 'Scheduled' &&
                        ($row->sms_delivered ?? 0) == 0
                    )
                        <button class="btn btn-sm btn-outline-success send-appointment-sms"
                                data-id="{{ $row->id }}">
                            Send SMS
                        </button>
                    @endif

                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="text-center">No appointments found</td>
            </tr>
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
