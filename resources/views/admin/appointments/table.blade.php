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
            .amount-cell { white-space: nowrap; }
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
            <th>Source</th>
            <th>Reg. Fee</th>
            <th>Doctor Fee</th>
            <th>Discount %</th>
            <th>Discount Amt</th>
            <th>Amount</th>
            <th>Final Amount</th>
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

                <td>
                    <a href="javascript:void(0);"
                       class="afontopt appointment-log-link"
                       data-id="{{ $row->id }}">
                        {{ $row->appointment_no ?? '' }}
                    </a>
                </td>

                <td>
                    @if(!empty($row->appointment_date) && !empty($row->appointment_time))
                        <div>
                            <div class="appt-slot">{{ $row->appointment_time }}</div>
                            <div class="appt-meta">{{ \GeneralFunctions::formatDate($row->appointment_date) }}</div>
                        </div>
                    @endif
                </td>

                @if($role != 5)
                <td>{{ Str::title($row->doctor_name ?? '') }}</td>
                @endif

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

                <td>{{ $row->source_name ?? '-' }}</td>
                <td class="amount-cell">Rs {{ number_format((float) ($row->registration_fee ?? 0), 2) }}</td>
                <td class="amount-cell">Rs {{ number_format((float) ($row->doctor_fee ?? 0), 2) }}</td>
                <td>{{ number_format((float) ($row->discount_percentage ?? 0), 2) }}%</td>
                <td class="amount-cell">Rs {{ number_format((float) ($row->discount_amount ?? 0), 2) }}</td>
                <td class="amount-cell">Rs {{ number_format((float) ($row->gross_amount ?? (($row->doctor_fee ?? 0) + ($row->registration_fee ?? 0))), 2) }}</td>
                <td class="amount-cell">
                    @if($role != 5)
                        Rs {{ number_format((float) ($row->amount ?? 0), 2) }}
                    @else
                        Rs {{ number_format((float) ($row->doctor_fee ?? 0), 2) }}
                    @endif
                </td>

                <td>
                    <div>
                        @if(($row->payment_status ?? '') === 'success')
                            Paid
                        @elseif(($row->payment_status ?? '') === 'pending')
                            Payment pending
                        @elseif(in_array(($row->payment_mode ?? $row->payment_method ?? ''), ['free', 'free_booking']))
                            Free
                        @else
                            Payment failed
                        @endif
                    </div>
                    <div class="small text-muted">
                        Mode:
                        {{ ($row->payment_mode ?? $row->payment_method ?? '') ? strtoupper(str_replace('_', ' ', $row->payment_mode ?? $row->payment_method)) : '-' }}
                    </div>
                </td>

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

                <td>
                    {{ ($row->is_followup ?? 0) == 0 ? 'Main' : 'Follow-up' }}
                </td>

                <td>
                    <div class="appt-actions">
                    @if($role != 5 && ($row->appointment_status ?? 'Scheduled') !== 'Completed')
                        <button class="btn btn-sm btn-outline-primary open-status-modal"
                                data-id="{{ $row->id }}"
                                data-status="{{ $row->appointment_status ?? 'Scheduled' }}">
                            Update
                        </button>
                    @endif

                    @if($role != 5 && !empty($row->payment_row_id) && ($row->payment_status ?? '') !== 'success')
                        <button class="btn btn-sm btn-outline-success open-payment-modal"
                                data-id="{{ $row->payment_row_id }}"
                                data-payment-mode="{{ $row->payment_mode ?? '' }}"
                                data-reference-no="{{ $row->reference_no ?? '' }}">
                            Update Payment
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
                        !empty($row->payment_row_id) &&
                        ($row->appointment_status ?? 'Scheduled') == 'Scheduled' &&
                        ($row->sms_delivered ?? 0) == 0
                    )
                        <button class="btn btn-sm btn-outline-success send-appointment-sms"
                                data-id="{{ $row->payment_row_id }}">
                            Send SMS
                        </button>
                    @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ $role == 5 ? 15 : 16 }}" class="text-center">No appointments found</td>
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
