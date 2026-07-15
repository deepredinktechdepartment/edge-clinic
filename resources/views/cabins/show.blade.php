@extends('template_v1')

@section('content')
@include('cabins.partials.styles')

@php
    $statusClass = match ($cabin->status) {
        'maintenance' => 'status-maintenance',
        'occupied' => 'status-monthly',
        'booked' => 'status-booked',
        default => 'status-available',
    };
    $statusLabelClass = match ($cabin->status) {
        'maintenance' => 'text-bg-warning',
        'occupied' => 'text-bg-success',
        'booked' => 'text-bg-info',
        default => 'text-bg-secondary',
    };
    $bookingModeLabel = ucfirst(str_replace('_', ' ', $cabin->booking_mode ?? 'both'));
    $freeFacilities = $cabin->facilities->where('pricing_type', 'free')->values();
    $paidFacilities = $cabin->facilities->where('pricing_type', 'paid')->values();
@endphp

<div class="cabin-shell">
    @include('cabins.partials.page-header', [
        'title' => ($cabin->cabin_code ?? '-') . ' - ' . ($cabin->name ?? 'Cabin'),
        'subtitle' => ucfirst($cabin->cabin_type ?? 'standard') . ' | ' . ($cabin->floor_name ?: 'Floor not set') . ' | ' . ($cabin->room_number ?: 'Room not set'),
        'actions' => [
            ['url' => route('admin.cabins.index'), 'label' => 'Back', 'icon' => 'bi bi-arrow-left', 'class' => 'btn-outline-secondary'],
            ['url' => route('admin.cabins.edit', $cabin->id), 'label' => 'Edit Cabin', 'icon' => 'bi bi-pencil-square', 'class' => 'btn-brand'],
        ],
    ])

    <div class="row g-4 align-items-start">
        <div class="col-xl-8">
            <div class="d-flex flex-column gap-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="cabin-overview-tile">
                            <div class="mini-label">Cabin Type</div>
                            <div class="mini-value">{{ ucfirst($cabin->cabin_type ?? 'standard') }}</div>
                            <div class="cabin-overview-sub">Configured from cabin settings</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="cabin-overview-tile">
                            <div class="mini-label">Hourly Rate</div>
                            <div class="mini-value">Rs {{ number_format((float) ($cabin->hourly_rate ?? 0), 2) }}</div>
                            <div class="cabin-overview-sub">Used for hourly bookings</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="cabin-overview-tile">
                            <div class="mini-label">Monthly Rate</div>
                            <div class="mini-value">Rs {{ number_format((float) ($cabin->monthly_rate ?? 0), 2) }}</div>
                            <div class="cabin-overview-sub">Used for subscriptions</div>
                        </div>
                    </div>
                </div>

                <div class="cabin-panel">
                    <div class="panel-head">
                        <div>
                            <h5 class="mb-0">Cabin Overview</h5>
                            <div class="small text-muted mt-1">Core cabin details and operating configuration.</div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="cabin-detail-grid">
                            <div class="cabin-detail-box">
                                <div class="mini-label">Cabin Name</div>
                                <div class="cabin-detail-value">{{ $cabin->name ?: '-' }}</div>
                            </div>
                            <div class="cabin-detail-box">
                                <div class="mini-label">Cabin Code</div>
                                <div class="cabin-detail-value">{{ $cabin->cabin_code ?: '-' }}</div>
                            </div>
                            <div class="cabin-detail-box">
                                <div class="mini-label">Room Number</div>
                                <div class="cabin-detail-value">{{ $cabin->room_number ?: '-' }}</div>
                            </div>
                            <div class="cabin-detail-box">
                                <div class="mini-label">Floor</div>
                                <div class="cabin-detail-value">{{ $cabin->floor_name ?: '-' }}</div>
                            </div>
                            <div class="cabin-detail-box">
                                <div class="mini-label">Capacity</div>
                                <div class="cabin-detail-value">{{ $cabin->capacity ?: 1 }}</div>
                            </div>
                            <div class="cabin-detail-box">
                                <div class="mini-label">Booking Mode</div>
                                <div class="cabin-detail-value">{{ $bookingModeLabel }}</div>
                            </div>
                            <div class="cabin-detail-box">
                                <div class="mini-label">Available From</div>
                                <div class="cabin-detail-value">{{ optional($cabin->available_from)->format('d M Y') ?: '-' }}</div>
                            </div>
                            <div class="cabin-detail-box">
                                <div class="mini-label">Operating Hours</div>
                                <div class="cabin-detail-value">
                                    {{ $cabin->operating_start_time ? substr($cabin->operating_start_time, 0, 5) : '-' }}
                                    -
                                    {{ $cabin->operating_end_time ? substr($cabin->operating_end_time, 0, 5) : '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="cabin-note-box mt-4">
                            <div class="mini-label mb-1">Notes</div>
                            <div class="text-muted">{{ $cabin->notes ?: 'No notes added for this cabin yet.' }}</div>
                        </div>
                    </div>
                </div>

                <div class="cabin-panel">
                    <div class="panel-head">
                        <div>
                            <h5 class="mb-0">Facilities & Equipment</h5>
                            <div class="small text-muted mt-1">Grouped by free and paid items for this cabin.</div>
                        </div>
                        <a href="{{ route('admin.cabins.facilities.index') }}" class="btn btn-outline-secondary btn-sm">Manage Facilities</a>
                    </div>
                    <div class="panel-body">
                        @if($cabin->facilities->isEmpty())
                            <div class="empty-note">No facilities selected for this cabin.</div>
                        @else
                            @if($freeFacilities->isNotEmpty())
                                <div class="facility-group">
                                    <div class="facility-group-head">
                                        <div>
                                            <div class="facility-group-title">Free Facilities</div>
                                            <div class="facility-group-sub">Included with the cabin by default.</div>
                                        </div>
                                        <span class="facility-group-count">{{ $freeFacilities->count() }}</span>
                                    </div>
                                    <div class="facility-grid">
                                        @foreach($freeFacilities as $facility)
                                            <div class="facility-item facility-item-free">
                                                <div class="facility-choice">
                                                    <span class="facility-copy">
                                                        <span class="facility-title-row">
                                                            <span class="facility-title">{{ $facility->name }}</span>
                                                            <span class="facility-badge facility-badge-free">Free</span>
                                                        </span>
                                                        <small class="facility-meta">Included with cabin</small>
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($paidFacilities->isNotEmpty())
                                <div class="facility-group">
                                    <div class="facility-group-head">
                                        <div>
                                            <div class="facility-group-title">Paid Facilities</div>
                                            <div class="facility-group-sub">Charged separately when used.</div>
                                        </div>
                                        <span class="facility-group-count facility-group-count-paid">{{ $paidFacilities->count() }}</span>
                                    </div>
                                    <div class="facility-grid">
                                        @foreach($paidFacilities as $facility)
                                            <div class="facility-item facility-item-paid">
                                                <div class="facility-choice">
                                                    <span class="facility-copy">
                                                        <span class="facility-title-row">
                                                            <span class="facility-title">{{ $facility->name }}</span>
                                                            <span class="facility-badge facility-badge-paid">Paid</span>
                                                        </span>
                                                        <small class="facility-meta">Rs {{ number_format((float) $facility->rate, 2) }} {{ $facility->charge_label ?: 'Per Use' }}</small>
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="cabin-panel h-100">
                            <div class="panel-head">
                                <h6 class="mb-0">Recent Bookings</h6>
                            </div>
                            <div class="panel-body">
                                @if($cabin->bookings->isEmpty())
                                    <div class="empty-note">No bookings yet.</div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Doctor</th>
                                                    <th>Time</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($cabin->bookings as $booking)
                                                    <tr>
                                                        <td>{{ optional($booking->booking_date)->format('d M Y') }}</td>
                                                        <td>{{ $booking->doctor->name ?? '-' }}</td>
                                                        <td>{{ \Carbon\Carbon::createFromFormat('H:i:s', $booking->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::createFromFormat('H:i:s', $booking->end_time)->format('h:i A') }}</td>
                                                        <td>Rs {{ number_format((float) $booking->total_amount, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="cabin-panel h-100">
                            <div class="panel-head">
                                <h6 class="mb-0">Recent Subscriptions</h6>
                            </div>
                            <div class="panel-body">
                                @if($cabin->subscriptions->isEmpty())
                                    <div class="empty-note">No subscriptions yet.</div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Doctor</th>
                                                    <th>Period</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($cabin->subscriptions as $subscription)
                                                    <tr>
                                                        <td>{{ $subscription->doctor->name ?? '-' }}</td>
                                                        <td>
                                                            <div>{{ optional($subscription->start_date)->format('d M Y') }} - {{ optional($subscription->end_date)->format('d M Y') }}</div>
                                                            <div class="small text-muted">{{ substr($subscription->subscription_start_time ?: '09:00:00', 0, 5) }} - {{ substr($subscription->subscription_end_time ?: '21:00:00', 0, 5) }}</div>
                                                        </td>
                                                        <td>Rs {{ number_format((float) $subscription->total_amount, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="d-flex flex-column gap-4">
                <div class="cabin-panel">
                    <div class="panel-head">
                        <h5 class="mb-0">Status & Availability</h5>
                    </div>
                    <div class="panel-body">
                        <div class="cabin-status-stack">
                            <div class="cabin-status-row">
                                <span class="mini-label">Current Status</span>
                                <span class="badge {{ $statusLabelClass }}">{{ ucfirst($cabin->status) }}</span>
                            </div>
                            <div class="cabin-status-row">
                                <span class="mini-label">Booking Mode</span>
                                <strong class="text-dark">{{ $bookingModeLabel }}</strong>
                            </div>
                            <div class="cabin-status-row">
                                <span class="mini-label">Available From</span>
                                <strong class="text-dark">{{ optional($cabin->available_from)->format('d M Y') ?: '-' }}</strong>
                            </div>
                            <div class="cabin-status-row">
                                <span class="mini-label">Operating Window</span>
                                <strong class="text-dark">
                                    {{ $cabin->operating_start_time ? substr($cabin->operating_start_time, 0, 5) : '-' }}
                                    -
                                    {{ $cabin->operating_end_time ? substr($cabin->operating_end_time, 0, 5) : '-' }}
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cabin-panel">
                    <div class="panel-head">
                        <h5 class="mb-0">Preview Card</h5>
                    </div>
                    <div class="panel-body">
                        <div class="cabin-card">
                            <div class="status-bar {{ $statusClass }}"></div>
                            <div class="body">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="cabin-code">{{ $cabin->cabin_code ?: 'CABIN' }}</div>
                                        <h5>{{ $cabin->name ?: 'Cabin' }}</h5>
                                        <div class="cabin-meta">{{ ucfirst($cabin->cabin_type ?? 'standard') }} | {{ $cabin->floor_name ?: 'Floor not set' }}</div>
                                    </div>
                                    <span class="status-chip {{ $statusClass }}">{{ ucfirst($cabin->status) }}</span>
                                </div>

                                <div class="empty-note mt-3">
                                    {{ $cabin->status === 'maintenance' ? 'This cabin is currently under maintenance.' : 'This is how the cabin appears in the cabin grid.' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cabin-panel">
                    <div class="panel-body cabin-form-actions">
                        <a href="{{ route('admin.cabins.bookings.create', ['cabin_id' => $cabin->id]) }}" class="btn btn-brand btn-sm w-100">New Booking</a>
                        <a href="{{ route('admin.cabins.subscriptions.create', ['cabin_id' => $cabin->id]) }}" class="btn btn-outline-secondary btn-sm w-100">New Subscription</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
