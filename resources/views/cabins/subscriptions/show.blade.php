@extends('template_v1')

@section('content')
@include('cabins.partials.styles')

@php
    $doctorName = $subscription->doctor->name ?? 'Doctor';
    $doctorInitials = collect(explode(' ', trim($doctorName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('');
    $statusClass = match ($subscription->status) {
        'inactive' => 'text-bg-secondary',
        'cancelled' => 'text-bg-danger',
        default => 'text-bg-success',
    };
    $activityRows = collect([
        [
            'title' => 'Subscription created',
            'time' => optional($subscription->created_at)->format('d M Y, h:i A'),
            'dot' => '#216aae',
        ],
        $subscription->updated_at && $subscription->created_at && $subscription->updated_at->ne($subscription->created_at) ? [
            'title' => 'Subscription updated',
            'time' => optional($subscription->updated_at)->format('d M Y, h:i A'),
            'dot' => '#64748b',
        ] : null,
    ])->filter()->values();
@endphp

<div class="cabin-shell">
    @include('cabins.partials.page-header', [
        'titleIcon' => 'bi bi-person-workspace',
        'title' => 'Subscription #' . $subscription->id,
        'subtitle' => ($subscription->cabin->cabin_code ?? '-') . ' - ' . ($subscription->cabin->name ?? '-') . ' | ' . optional($subscription->start_date)->format('d M Y') . ' - ' . optional($subscription->end_date)->format('d M Y'),
        'actions' => [
            ['url' => route('admin.cabins.subscriptions.index'), 'label' => 'Back', 'icon' => 'bi bi-arrow-left', 'class' => 'btn-outline-secondary'],
            ['url' => route('admin.cabins.subscriptions.edit', $subscription->id), 'label' => 'Edit', 'icon' => 'bi bi-pencil-square', 'class' => 'btn-brand'],
        ],
    ])

    <div class="row g-4 align-items-start">
        <div class="col-xl-8">
            <div class="d-flex flex-column gap-4">
                <div class="cabin-panel">
                    <div class="panel-head">
                        <h5 class="mb-0">Subscription Details</h5>
                    </div>
                    <div class="panel-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="mini-label mb-1">Subscription ID</div>
                                <div class="cabin-detail-value">#{{ $subscription->id }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="mini-label mb-1">Status</div>
                                <span class="badge {{ $statusClass }}">{{ ucfirst($subscription->status) }}</span>
                            </div>
                            <div class="col-md-6">
                                <div class="mini-label mb-1">Doctor</div>
                                <div class="cabin-detail-value">{{ $doctorName }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="mini-label mb-1">Cabin</div>
                                <div class="cabin-detail-value">{{ $subscription->cabin->cabin_code ?? '-' }} - {{ $subscription->cabin->name ?? '-' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="mini-label mb-1">Start Date</div>
                                <div class="cabin-detail-value">{{ optional($subscription->start_date)->format('d M Y') }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="mini-label mb-1">End Date</div>
                                <div class="cabin-detail-value">{{ optional($subscription->end_date)->format('d M Y') }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="mini-label mb-1">Daily Time Window</div>
                                <div class="cabin-detail-value">{{ substr($subscription->subscription_start_time ?: '09:00:00', 0, 5) }} - {{ substr($subscription->subscription_end_time ?: '21:00:00', 0, 5) }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="mini-label mb-1">Invoice Day</div>
                                <div class="cabin-detail-value">{{ $subscription->invoice_day ?: '-' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="mini-label mb-1">Auto Invoice</div>
                                <div class="cabin-detail-value">{{ $subscription->auto_invoice ? 'Enabled' : 'Disabled' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="mini-label mb-1">Payment Status</div>
                                <div class="cabin-detail-value">{{ $subscription->payment_status ?: 'Pending' }}</div>
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
                                <div class="mini-label">Period</div>
                                <div class="mini-value">{{ optional($subscription->start_date)->format('d M Y') }} - {{ optional($subscription->end_date)->format('d M Y') }}</div>
                            </div>
                            <div class="booking-invoice-meta-box">
                                <div class="mini-label">Invoice Day</div>
                                <div class="mini-value">{{ $subscription->invoice_day ?: '-' }}</div>
                            </div>
                            <div class="booking-invoice-meta-box">
                                <div class="mini-label">Daily Window</div>
                                <div class="mini-value">{{ substr($subscription->subscription_start_time ?: '09:00:00', 0, 5) }} - {{ substr($subscription->subscription_end_time ?: '21:00:00', 0, 5) }}</div>
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
                                            <th>Qty</th>
                                            <th>Rate</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{{ $subscription->cabin->cabin_code ?? 'Cabin' }} monthly subscription</td>
                                            <td>1.00</td>
                                            <td>Rs {{ number_format((float) $subscription->monthly_rate, 2) }}</td>
                                            <td class="text-end fw-semibold text-dark">Rs {{ number_format((float) $subscription->monthly_rate, 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="booking-invoice-totals">
                            <div class="booking-invoice-total-row">
                                <span>Sub-total</span>
                                <strong>Rs {{ number_format((float) $subscription->monthly_rate, 2) }}</strong>
                            </div>
                            <div class="booking-invoice-total-row">
                                <span>GST ({{ number_format((float) $subscription->gst_percent, 2) }}%)</span>
                                <strong>Rs {{ number_format((float) $subscription->gst_amount, 2) }}</strong>
                            </div>
                            <div class="booking-invoice-total-row booking-invoice-total-grand">
                                <span>Grand Total</span>
                                <strong>Rs {{ number_format((float) $subscription->total_amount, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                @if($subscription->notes)
                    <div class="cabin-panel">
                        <div class="panel-head">
                            <h5 class="mb-0">Notes</h5>
                        </div>
                        <div class="panel-body">
                            <div class="text-muted">{{ $subscription->notes }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-xl-4">
            <div class="d-flex flex-column gap-4">
                <div class="cabin-panel">
                    <div class="panel-head">
                        <h5 class="mb-0">Timeline</h5>
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
                                <div class="small text-muted">{{ $subscription->doctor->department->name ?? $subscription->doctor->designation ?? 'Doctor' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cabin-panel">
                    <div class="panel-head">
                        <h5 class="mb-0">Cabin Facilities</h5>
                    </div>
                    <div class="panel-body">
                        @if($subscription->cabin && $subscription->cabin->facilities->isNotEmpty())
                            <div class="facility-grid">
                                @foreach($subscription->cabin->facilities as $facility)
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
                        @else
                            <div class="empty-note">No facilities linked to this cabin.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
