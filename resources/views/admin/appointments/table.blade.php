@if(count($list) > 0)
@php $role = auth()->user()?->role; @endphp
<div class="t-job-sheet container-fluid g-0">
    <div class="t-table table-responsive">
        <style>
            .appt-meta { font-size: 12px; color: #6c7a89; }
            .appt-primary-link { font-weight: 700; color: #205b96; text-decoration: none; }
            .appt-primary-link:hover { color: #163f69; text-decoration: underline; }
            .appt-stack { display: grid; gap: 2px; }
            .appt-stack-compact { display: grid; gap: 4px; }
            .appt-patient { min-width: 220px; }
            .appt-patient-name { font-weight: 700; color: #1d3557; }
            .appt-chip { display: inline-block; padding: 3px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; margin: 2px 4px 0 0; }
            .appt-chip-soft { background: #eef6ff; color: #245b93; }
            .appt-chip-success { background: #e8f7ef; color: #1f7a45; }
            .appt-chip-warn { background: #fff5e8; color: #9a5a00; }
            .appt-chip-danger { background: #fdecec; color: #b42318; }
            .appt-slot { font-weight: 600; color: #243b53; }
            .appt-status-wrap {
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .appt-actions {
                display: flex;
                gap: 6px;
                align-items: center;
                min-width: 112px;
            }
            .appt-action-btn {
                width: 34px;
                height: 34px;
                border-radius: 8px;
                font-size: 13px;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0;
            }
            .appt-action-btn-lg {
                width: auto;
                min-width: 76px;
                padding: 0.45rem 0.75rem;
                gap: 6px;
            }
            .appt-more-toggle {
                width: 34px;
                height: 34px;
                padding: 0;
                border-radius: 8px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
            .appt-more-menu {
                min-width: 210px;
                padding: 6px;
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                box-shadow: 0 10px 25px rgba(30, 41, 59, .14);
            }
            .appt-more-menu .dropdown-item {
                display: flex;
                align-items: center;
                gap: 9px;
                padding: 8px 10px;
                border-radius: 6px;
                font-size: 13px;
            }
            .appt-more-menu .dropdown-item i { width: 15px; text-align: center; }
            .appt-action-btn.btn-success,
            .appt-action-btn.btn-outline-success:hover {
                color: #fff;
            }
            .appt-money-strong {
                font-weight: 700;
                color: #23364a;
            }
            .amount-cell { white-space: nowrap; }
        </style>
        <table class="table table-borderless table-hover" id="default-datatable" style="width: 100%;">
        <thead>
        <tr>
            <th>#</th>
            <th>Appointment</th>
            @if($role != 5)
            <th>Doctor</th>
            @endif
            <th>Patient Details</th>
            <th>Source</th>
            <th>Charges</th>
            <th>Discount</th>
            <th>Payment</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        </thead>

        <tbody>
        @forelse($list as $row)
            @php
                $appointmentDateValue = trim((string) ($row->appointment_date ?? ''));
                $appointmentDateObject = null;

                if ($appointmentDateValue !== '') {
                    try {
                        $appointmentDateObject = strlen($appointmentDateValue) === 8
                            ? \Carbon\Carbon::createFromFormat('Ymd', $appointmentDateValue)->startOfDay()
                            : \Carbon\Carbon::parse($appointmentDateValue)->startOfDay();
                    } catch (\Throwable $e) {
                        $appointmentDateObject = null;
                    }
                }

                $isFutureAppointment = $appointmentDateObject?->gt(\Carbon\Carbon::today()) ?? false;
                $futureLockTitle = 'This appointment is available for action only on the appointment date or after rescheduling.';
            @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>

                <td>
                    <div class="appt-stack">
                        <a href="javascript:void(0);"
                           class="appointment-log-link appt-primary-link"
                           data-id="{{ $row->appointment_row_id }}">
                            {{ $row->appointment_no ?? '' }}
                        </a>
                        @if(!empty($row->appointment_date) && !empty($row->appointment_time))
                            <div class="appt-slot">{{ $row->appointment_time }}</div>
                            <div class="appt-meta">{{ \GeneralFunctions::formatDate($row->appointment_date) }}</div>
                        @endif
                        @if(($row->is_after_slot ?? false) == 1)
                            <span class="appt-chip appt-chip-warn">After-slot walk-in</span>
                        @endif
                    </div>
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
                <td>
                    <div class="appt-stack-compact">
                        <div class="appt-meta">Reg: <span class="appt-money-strong">Rs {{ number_format((float) ($row->registration_fee ?? 0), 2) }}</span></div>
                        <div class="appt-meta">Doctor: <span class="appt-money-strong">Rs {{ number_format((float) ($row->doctor_fee ?? 0), 2) }}</span></div>
                        <div class="appt-meta">Gross: <span class="appt-money-strong">Rs {{ number_format((float) ($row->gross_amount ?? (($row->doctor_fee ?? 0) + ($row->registration_fee ?? 0))), 2) }}</span></div>
                    </div>
                </td>
                <td>
                    <div class="appt-stack-compact">
                        <div class="appt-meta">{{ number_format((float) ($row->discount_percentage ?? 0), 2) }}%</div>
                        <div class="appt-money-strong">Rs {{ number_format((float) ($row->discount_amount ?? 0), 2) }}</div>
                    </div>
                </td>

                <td>
                    @php
                        $paymentMode = $row->payment_mode ?? $row->payment_method ?? '';
                        $splitParts = [];

                        if ($paymentMode === 'split' && !empty($row->reference_no)) {
                            foreach (explode('|', $row->reference_no) as $part) {
                                $segments = array_map('trim', explode(':', trim($part), 3));
                                if (count($segments) < 2) {
                                    continue;
                                }

                                $splitParts[] = [
                                    'mode' => strtoupper($segments[0]),
                                    'amount' => (float) ($segments[1] ?? 0),
                                    'reference' => $segments[2] ?? null,
                                ];
                            }
                        }
                    @endphp
                    <div>
                        @php
                            $paymentStatus = (string) ($row->payment_status ?? '');
                            $isPaid = in_array($paymentStatus, ['success', 'Authorized'], true);
                            $isPending = in_array($paymentStatus, ['pending', 'Pending', 'initiated', 'Initiated'], true);
                            $isNoPaymentRequired = in_array($paymentStatus, ['No Payment Required', 'no_payment_required'], true)
                                || in_array(($row->payment_mode ?? $row->payment_method ?? ''), ['no_payment_required'], true);
                        @endphp

                        @if($isNoPaymentRequired)
                            No payment required
                        @elseif($isPaid)
                            Paid
                        @elseif($isPending)
                            Payment pending
                        @elseif(in_array(($row->payment_mode ?? $row->payment_method ?? ''), ['free', 'free_booking']))
                            Free
                        @else
                            Payment failed
                        @endif
                    </div>
                    <div class="small text-muted mt-1">
                        {{ $paymentMode ? strtoupper(str_replace('_', ' ', $paymentMode)) : '-' }}
                        @if(count($splitParts) > 0)
                            <button type="button"
                                    class="btn btn-link btn-sm p-0 ms-1 align-baseline show-split-details"
                                    data-split-details="{{ e($row->reference_no ?? '') }}"
                                    title="View split bill details">
                                <i class="fa-solid fa-circle-info"></i>
                            </button>
                        @endif
                    </div>
                    <div class="appt-money-strong mt-1">
                        @if($role != 5)
                            Rs {{ number_format((float) ($row->amount ?? 0), 2) }}
                        @else
                            Rs {{ number_format((float) ($row->doctor_fee ?? 0), 2) }}
                        @endif
                    </div>
                </td>

                <td>
                    @php
                        $status = $row->appointment_status ?? 'Scheduled';

                        $statusColor = match($status) {
                            'Scheduled' => '#6c757d',
                            'Checked-In' => '#0dcaf0',
                            'In-Consultation' => '#0d6efd',
                            'Completed' => '#198754',
                            'Cancelled' => '#dc3545',
                            default => '#e0e0e0',
                        };
                    @endphp

                    <div class="appt-status-wrap">
                        <span id="status-{{ $row->id }}" style="color: {{ $statusColor }}; font-weight: 700;">
                            {{ $status }}
                        </span>
                        @if($role != 5 && ($row->appointment_status ?? 'Scheduled') !== 'Completed' && ! $isFutureAppointment)
                            <button class="btn btn-sm btn-outline-primary appt-action-btn open-status-modal"
                                    data-id="{{ $row->id }}"
                                    data-status="{{ $row->appointment_status ?? 'Scheduled' }}"
                                    title="Update status">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                        @elseif($role != 5 && ($row->appointment_status ?? 'Scheduled') !== 'Completed' && $isFutureAppointment)
                            <button class="btn btn-sm btn-outline-secondary appt-action-btn" type="button" disabled title="{{ $futureLockTitle }}">
                                <i class="fa-solid fa-lock"></i>
                            </button>
                        @endif
                    </div>
                    <div class="appt-meta mt-1">
                        {{ ($row->is_followup ?? 0) == 0 ? 'Main Visit' : 'Follow-up' }}
                    </div>
                    @if($isFutureAppointment)
                        <div class="appt-meta mt-1 text-warning">
                            Action unlocks on {{ $appointmentDateObject->format('d M Y') }}
                        </div>
                    @endif
                </td>

                <td>
                    <div class="appt-actions">
                    @php
                        $hasMoreActions = ($role != 5 && !empty($row->payment_row_id) && ($row->appointment_status ?? 'Scheduled') !== 'Completed')
                            || (! $isFutureAppointment && $role != 5 && !empty($row->payment_row_id) && ! $isPaid && ! $isNoPaymentRequired)
                            || (! $isFutureAppointment && $role != 5 && !empty($row->payment_row_id))
                            || (! $isFutureAppointment && !empty($row->payment_id))
                            || (! $isFutureAppointment && ($row->consultation_id || !empty($row->payment_row_id)));
                    @endphp
                    @if($hasMoreActions)
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary appt-more-toggle"
                                    type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                    title="More appointment actions" aria-label="More appointment actions">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end appt-more-menu">
                                @if(! $isFutureAppointment && $row->consultation_id)
                                    <a href="{{ route('consultations.edit', $row->consultation_id) }}" target="_blank" class="dropdown-item text-success">
                                        <i class="fa-solid fa-stethoscope"></i> {{ $role == 5 ? 'Open visit' : 'View visit' }}
                                    </a>
                                @elseif(! $isFutureAppointment && !empty($row->payment_row_id))
                                    <a href="{{ route('consultations.create', ['payment_id' => $row->payment_row_id]) }}" target="_blank" class="dropdown-item text-success">
                                        <i class="fa-solid fa-notes-medical"></i> Create visit
                                    </a>
                                @endif
                                @if($role != 5 && !empty($row->payment_row_id) && ($row->appointment_status ?? 'Scheduled') !== 'Completed')
                                    <button class="dropdown-item text-warning open-reschedule-modal" type="button" data-id="{{ $row->payment_row_id }}">
                                        <i class="fa-solid fa-calendar-days"></i> Reschedule appointment
                                    </button>
                                @endif
                                @if(! $isFutureAppointment && $role != 5 && !empty($row->payment_row_id) && !$isPaid && !$isNoPaymentRequired)
                                    <button class="dropdown-item text-success open-payment-modal" type="button"
                                            data-id="{{ $row->payment_row_id }}"
                                            data-payment-mode="{{ $row->payment_mode ?? '' }}"
                                            data-reference-no="{{ $row->reference_no ?? '' }}"
                                            data-amount="{{ $row->amount ?? 0 }}">
                                        <i class="fa-solid fa-wallet"></i> Record payment
                                    </button>
                                @endif
                                @if(! $isFutureAppointment && $role != 5 && !empty($row->payment_row_id))
                                    <button class="dropdown-item open-prescription-modal" type="button"
                                            data-payment-id="{{ $row->payment_row_id }}"
                                            data-consultation-id="{{ $row->consultation_id ?? '' }}"
                                            data-front-url="{{ !empty($row->consultation_id) && !empty($row->prescription_front_path) ? route('consultations.case_sheet_files.view', [$row->consultation_id, 'front']) : '' }}"
                                            data-back-url="{{ !empty($row->consultation_id) && !empty($row->prescription_back_path) ? route('consultations.case_sheet_files.view', [$row->consultation_id, 'back']) : '' }}">
                                        <i class="fa-solid fa-prescription"></i> {{ !empty($row->prescription_front_path) ? 'View or update prescription' : 'Upload prescription' }}
                                    </button>
                                @endif
                                @if(! $isFutureAppointment && $role != 5 && ($row->appointment_status ?? 'Scheduled') === 'Completed' && !empty($row->consultation_id) && !empty($row->prescription_front_path))
                                    <button class="dropdown-item text-success send-prescription-sms" type="button" data-consultation-id="{{ $row->consultation_id }}">
                                        <i class="fa-solid fa-file-prescription"></i> Send prescription by SMS
                                    </button>
                                @endif
                                @if(! $isFutureAppointment)
                                    <a href="{{ route('consultations.case_sheet_template.pdf', array_filter(['appointment_id' => $row->appointment_row_id ?? null, 'payment_id' => $row->payment_row_id ?? null])) }}" target="_blank" class="dropdown-item">
                                        <i class="fa-solid fa-file-lines"></i> Open case sheet
                                    </a>
                                @endif
                                @if(! $isFutureAppointment && !empty($row->payment_id))
                                    <a href="{{ route('invoice.appointment', ['paymentId' => $row->payment_id]) }}" target="_blank" class="dropdown-item">
                                        <i class="fa-solid fa-print"></i> Print invoice
                                    </a>
                                @endif
                                @if(! $isFutureAppointment && $role != 5 && !empty($row->payment_row_id) && ($row->appointment_status ?? 'Scheduled') == 'Scheduled' && ($row->sms_delivered ?? 0) == 0)
                                    <button class="dropdown-item text-success send-appointment-sms" type="button" data-id="{{ $row->payment_row_id }}">
                                        <i class="fa-solid fa-paper-plane"></i> Send appointment SMS
                                    </button>
                                @endif
                            </div>
                        </div>
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
