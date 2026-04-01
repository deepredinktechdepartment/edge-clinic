@if(count($list) > 0)
@php $role = auth()->user()->role; @endphp
<div class="t-job-sheet container-fluid g-0">
    <div class="t-table table-responsive">
        <style>
            .appt-meta { font-size: 12px; color: #6c7a89; }
            .appt-patient { min-width: 220px; }
            .appt-patient-name { font-weight: 700; color: #1d3557; }
            .appt-chip { display: inline-block; padding: 3px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; margin: 2px 4px 0 0; }
            .appt-chip-soft { background: #eef6ff; color: #245b93; }
            .appt-chip-success { background: #e8f7ef; color: #1f7a45; }
            .appt-chip-warn { background: #fff5e8; color: #9a5a00; }
            .appt-chip-danger { background: #fdecec; color: #b42318; }
            .appt-slot { font-weight: 600; color: #243b53; }
            .appt-actions { display: flex; gap: 6px; flex-wrap: wrap; }
        </style>
        <table class="table table-borderless table-hover" id="default-datatable" style="width: 100%;">
        <thead>
        <tr>
            <th>#</th>
            <th>Appointment No</th>
            <th>Time Slot</th>
            @if($role != 5)
            <th>Doctor</th>
            @endif
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
                            <div class="appt-slot">{{ $row->appointment_time }}</div>
                            <div class="appt-meta">{{ \GeneralFunctions::formatDate($row->appointment_date) }}</div>
                        </div>
                    @endif
                </td>

                <!-- Doctor -->
                @if($role != 5)
                <td>{{ Str::title($row->doctor_name ?? '') }}</td>
                @endif

                <!-- Patient -->
                <td class="appt-patient">
                    <div class="appt-patient-name">{{ Str::title($row->patient_name ?? '') }}</div>
                    <div class="appt-meta">{{ $row->patient_phone ?? '-' }}</div>
                    <div>
                        @if(($row->is_followup ?? 0) == 0)
                            <span class="appt-chip appt-chip-soft">Main Visit</span>
                        @else
                            <span class="appt-chip appt-chip-soft">Follow-up</span>
                        @endif
                        @if($row->consultation_id)
                            <span class="appt-chip appt-chip-success">Visit Ready</span>
                        @endif
                    </div>
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
                        <span class="appt-chip appt-chip-success">Paid</span>
                    @elseif(($row->payment_status ?? '') === 'failed')
                        <span class="appt-chip appt-chip-danger">Failed</span>
                    @else
                        <span class="appt-chip appt-chip-warn">Pending</span>
                    @endif
                    @else
                        <span class="appt-chip appt-chip-soft">Free</span>
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

                    <span id="status-{{ $row->id }}" style="color: {{ $statusColor }}; font-weight: 700;">
                        {{ $status }}
                    </span>
                </td>

                <!-- Visit -->
                <td>
                    {{ ($row->is_followup ?? 0) == 0 ? 'Main' : 'Follow-up' }}
                </td>

                <!-- Actions -->
                <td>
                    <div class="appt-actions">

                    @if($role != 5 && ($row->appointment_status ?? 'Scheduled') !== 'Completed')
                        <button class="btn btn-sm btn-outline-primary open-status-modal"
                                data-id="{{ $row->id }}"
                                data-status="{{ $row->appointment_status ?? 'Scheduled' }}">
                            Update
                        </button>
                    @endif

                    @if($row->consultation_id)
                        <a href="{{ route('consultations.edit', $row->consultation_id) }}"
                        class="btn btn-sm btn-success" target="_blank">
                            {{ $role == 5 ? 'Open Visit' : 'View Visit' }}
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

                    @if(
                        $role != 5 &&
                        !empty($row->payment_id) &&
                        ($row->appointment_status ?? 'Scheduled') == 'Scheduled' &&
                        ($row->sms_delivered ?? 0) == 0
                    )
                        <button class="btn btn-sm btn-outline-success send-appointment-sms"
                                data-id="{{ $row->id }}">
                            Send SMS
                        </button>
                    @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ $role == 5 ? 9 : 10 }}" class="text-center">No appointments found</td>
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
