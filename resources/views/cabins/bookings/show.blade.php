@extends('template_v1')

@section('content')
@include('cabins.partials.styles')

@php
    $doctorName = $booking->doctor->name ?? 'Doctor';
    $doctorInitials = collect(explode(' ', trim($doctorName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('');
    $bookingTypeLabel = match ($booking->booking_type) {
        'full_day' => 'Full Day',
        'half_day' => 'Half Day',
        default => 'Hourly Session',
    };
    $statusClass = match ($booking->status) {
        'completed' => 'text-bg-success',
        'cancelled' => 'text-bg-danger',
        default => 'text-bg-info',
    };
    $paymentClass = match (strtolower((string) $booking->payment_status)) {
        'authorized' => 'text-bg-success',
        'pending' => 'text-bg-warning',
        'no payment required' => 'text-bg-secondary',
        default => 'text-bg-light',
    };
    $activityRows = collect([
        [
            'title' => 'Booking created',
            'time' => optional($booking->created_at)->format('d M Y, h:i A'),
            'dot' => '#216aae',
        ],
        $booking->payment_choice === 'pay_now' && $booking->paid_on ? [
            'title' => 'Payment received',
            'time' => optional($booking->paid_on)->format('d M Y, h:i A'),
            'dot' => '#059669',
        ] : null,
        $booking->updated_at && $booking->created_at && $booking->updated_at->ne($booking->created_at) ? [
            'title' => 'Booking updated',
            'time' => optional($booking->updated_at)->format('d M Y, h:i A'),
            'dot' => '#64748b',
        ] : null,
    ])->filter()->values();
    $hourlyRate = (float) $booking->total_hours > 0
        ? ((float) $booking->base_amount / (float) $booking->total_hours)
        : (float) $booking->base_amount;
@endphp

<div class="cabin-shell">
    @include('cabins.partials.page-header', [
        'titleIcon' => 'bi bi-calendar2-check',
        'title' => 'Booking #' . $booking->id,
        'subtitle' => ($booking->cabin->cabin_code ?? '-') . ' - ' . ($booking->cabin->name ?? '-') . ' | ' . optional($booking->booking_date)->format('d M Y') . ' | ' . \Carbon\Carbon::createFromFormat('H:i:s', $booking->start_time)->format('h:i A') . ' - ' . \Carbon\Carbon::createFromFormat('H:i:s', $booking->end_time)->format('h:i A'),
        'actions' => [
            ['url' => route('admin.cabins.bookings.index'), 'label' => 'Back', 'icon' => 'bi bi-arrow-left', 'class' => 'btn-outline-secondary'],
            ['url' => route('admin.cabins.bookings.edit', $booking->id), 'label' => 'Edit', 'icon' => 'bi bi-pencil-square', 'class' => 'btn-brand'],
        ],
    ])

    <div class="row g-4 align-items-start">
        <div class="col-xl-8">
            <div class="d-flex flex-column gap-4">
                <div class="cabin-panel">
                    <div class="panel-head">
                        <h5 class="mb-0">Booking Details</h5>
                    </div>
                    <div class="panel-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="mini-label mb-1">Booking ID</div>
                                <div class="cabin-detail-value">#{{ $booking->id }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="mini-label mb-1">Booking Type</div>
                                <span class="badge text-bg-primary">{{ $bookingTypeLabel }}</span>
                            </div>
                            <div class="col-md-6">
                                <div class="mini-label mb-1">Doctor</div>
                                <div class="cabin-detail-value">{{ $doctorName }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="mini-label mb-1">Cabin</div>
                                <div class="cabin-detail-value">{{ $booking->cabin->cabin_code ?? '-' }} - {{ $booking->cabin->name ?? '-' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="mini-label mb-1">Date</div>
                                <div class="cabin-detail-value">{{ optional($booking->booking_date)->format('d M Y') }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="mini-label mb-1">Time Slot</div>
                                <div class="cabin-detail-value">{{ \Carbon\Carbon::createFromFormat('H:i:s', $booking->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::createFromFormat('H:i:s', $booking->end_time)->format('h:i A') }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="mini-label mb-1">Duration</div>
                                <div class="cabin-detail-value">{{ number_format((float) $booking->total_hours, 2) }} hours</div>
                            </div>
                            <div class="col-md-6">
                                <div class="mini-label mb-1">Status</div>
                                <span class="badge {{ $statusClass }}">{{ ucfirst($booking->status) }}</span>
                            </div>
                            <div class="col-md-6">
                                <div class="mini-label mb-1">Payment Choice</div>
                                <div class="cabin-detail-value">{{ ucwords(str_replace('_', ' ', $booking->payment_choice ?? 'pay_later')) }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="mini-label mb-1">Payment Status</div>
                                <span class="badge {{ $paymentClass }}">{{ $booking->payment_status ?: 'Pending' }}</span>
                            </div>
                            <div class="col-md-6">
                                <div class="mini-label mb-1">Reference No.</div>
                                <div class="cabin-detail-value">{{ $booking->transaction_reference ?: '-' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="mini-label mb-1">Paid On</div>
                                <div class="cabin-detail-value">{{ optional($booking->paid_on)->format('d M Y, h:i A') ?: '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cabin-panel">
                    <div class="panel-head">
                        <h5 class="mb-0">Billing Summary</h5>
                    </div>
                    <div class="panel-body">
                        <div class="booking-invoice-meta">
                            <div class="booking-invoice-meta-box">
                                <div class="mini-label">Bill Date</div>
                                <div class="mini-value">{{ optional($booking->booking_date)->format('d M Y') }}</div>
                            </div>
                            <div class="booking-invoice-meta-box">
                                <div class="mini-label">Billing Type</div>
                                <div class="mini-value">{{ $bookingTypeLabel }}</div>
                            </div>
                            <div class="booking-invoice-meta-box">
                                <div class="mini-label">Cabin</div>
                                <div class="mini-value">{{ $booking->cabin->cabin_code ?? '-' }}</div>
                            </div>
                            <div class="booking-invoice-meta-box">
                                <div class="mini-label">Doctor</div>
                                <div class="mini-value">{{ $doctorName }}</div>
                            </div>
                        </div>

                        <div class="booking-invoice-table-wrap">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 booking-invoice-table">
                                <thead>
                                    <tr>
                                        <th>Description</th>
                                        <th>Hours</th>
                                        <th>Rate / Hr</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ $booking->cabin->cabin_code ?? 'Cabin' }} usage - {{ $bookingTypeLabel }}</td>
                                        <td>{{ number_format((float) $booking->total_hours, 2) }}</td>
                                        <td>Rs {{ number_format($hourlyRate, 2) }}</td>
                                        <td class="text-end fw-semibold text-dark">Rs {{ number_format((float) $booking->base_amount, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        </div>

                        <div class="booking-invoice-totals">
                            <div class="booking-invoice-total-row">
                                <span>Sub-total</span>
                                <strong>Rs {{ number_format((float) $booking->base_amount, 2) }}</strong>
                            </div>
                            <div class="booking-invoice-total-row">
                                <span>GST ({{ number_format((float) $booking->gst_percent, 2) }}%)</span>
                                <strong>Rs {{ number_format((float) $booking->gst_amount, 2) }}</strong>
                            </div>
                            <div class="booking-invoice-total-row booking-invoice-total-grand">
                                <span>Grand Total</span>
                                <strong>Rs {{ number_format((float) $booking->total_amount, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                @if($booking->notes)
                    <div class="cabin-panel">
                        <div class="panel-head">
                            <h5 class="mb-0">Notes</h5>
                        </div>
                        <div class="panel-body">
                            <div class="text-muted">{{ $booking->notes }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-xl-4">
            <div class="d-flex flex-column gap-4">
                <div class="cabin-panel">
                    <div class="panel-head">
                        <h5 class="mb-0">Check-in / Check-out</h5>
                    </div>
                    <div class="panel-body">
                        <div class="cabin-timeline">
                            @foreach($activityRows as $row)
                                <div class="cabin-timeline-row">
                                    <span class="cabin-timeline-dot" style="background: {{ $row['dot'] }};"></span>
                                    <div>
                                        <div class="cabin-timeline-title">{{ $row['title'] }}</div>
                                        <div class="cabin-timeline-time">{{ $row['time'] ?: '-' }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="cabin-panel">
                    <div class="panel-head">
                        <h5 class="mb-0">Doctor</h5>
                    </div>
                    <div class="panel-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="cabin-avatar">{{ $doctorInitials ?: 'DR' }}</div>
                            <div>
                                <div class="fw-semibold text-dark">{{ $doctorName }}</div>
                                <div class="small text-muted">{{ $booking->doctor->department->name ?? $booking->doctor->designation ?? 'Doctor' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cabin-panel">
                    <div class="panel-head">
                        <h5 class="mb-0">Payment Status</h5>
                    </div>
                    <div class="panel-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Current Status</span>
                            <span class="badge {{ $paymentClass }}">{{ $booking->payment_status ?: 'Pending' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Paid Amount</span>
                            <strong class="text-dark">Rs {{ number_format((float) ($booking->paid_amount ?? 0), 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Due / Balance</span>
                            <strong class="text-dark">Rs {{ number_format(max((float) $booking->total_amount - (float) ($booking->paid_amount ?? 0), 0), 2) }}</strong>
                        </div>
                    </div>
                </div>

                @if($booking->cabin && $booking->cabin->facilities->isNotEmpty())
                    <div class="cabin-panel">
                        <div class="panel-head">
                            <h5 class="mb-0">Cabin Facilities</h5>
                        </div>
                        <div class="panel-body">
                            <div class="facility-grid">
                                @foreach($booking->cabin->facilities as $facility)
                                    <div class="facility-item facility-item-{{ $facility->pricing_type === 'paid' ? 'paid' : 'free' }}">
                                        <div class="facility-choice">
                                            <span class="facility-copy">
                                                <span class="facility-title-row">
                                                    <span class="facility-title">{{ $facility->name }}</span>
                                                    <span class="facility-badge facility-badge-{{ $facility->pricing_type === 'paid' ? 'paid' : 'free' }}">
                                                        {{ ucfirst($facility->pricing_type) }}
                                                    </span>
                                                </span>
                                                @if($facility->pricing_type === 'paid')
                                                    <small class="facility-meta">Rs {{ number_format((float) $facility->rate, 2) }} {{ $facility->charge_label ?: 'Per Use' }}</small>
                                                @else
                                                    <small class="facility-meta">Included with cabin</small>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
