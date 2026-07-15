@extends('template_v1')

@section('content')
@include('cabins.partials.styles')

<div class="cabin-shell">
    @include('cabins.partials.page-header', [
        'title' => 'Cabin Dashboard',
        'subtitle' => 'Live view of cabin availability, today\'s timeline, and active monthly allocations.',
        'actions' => [
            ['url' => route('admin.cabins.create'), 'label' => 'Add Cabin', 'icon' => 'bi bi-door-open', 'class' => 'btn-brand'],
            ['url' => route('admin.cabins.bookings.create'), 'label' => 'New Booking', 'icon' => 'bi bi-calendar-plus', 'class' => 'btn-brand'],
            ['url' => route('admin.cabins.subscriptions.create'), 'label' => 'New Subscription', 'icon' => 'bi bi-person-workspace', 'class' => 'btn-brand'],
        ],
    ])

    <div class="cabin-grid">
        <div class="cabin-stat">
            <div class="value">{{ $totals['cabins'] }}</div>
            <div class="label">Total Cabins</div>
        </div>
        <div class="cabin-stat">
            <div class="value">{{ $totals['available'] }}</div>
            <div class="label">Available Now</div>
        </div>
        <div class="cabin-stat">
            <div class="value">{{ $totals['booked'] }}</div>
            <div class="label">Booked Right Now</div>
        </div>
        <div class="cabin-stat">
            <div class="value">{{ $totals['monthly'] }}</div>
            <div class="label">Monthly Allocations</div>
        </div>
        <div class="cabin-stat">
            <div class="value">{{ $totals['maintenance'] }}</div>
            <div class="label">Under Maintenance</div>
        </div>
        <div class="cabin-stat">
            <div class="value">₹{{ number_format($monthlyRevenue, 2) }}</div>
            <div class="label">This Month Revenue</div>
        </div>
    </div>

    <div class="cabin-panel">
        <div class="panel-head">
            <div>
                <h5 class="mb-1">Cabin Grid</h5>
                <div class="text-muted">Visual status of each cabin using the Edge Clinic admin shell.</div>
            </div>
            <a href="{{ route('admin.cabins.index') }}" class="btn btn-outline-primary btn-sm">Manage Cabins</a>
        </div>
        <div class="panel-body">
            @if($cabinCards->isEmpty())
                <div class="empty-note">No cabin records are available yet. Add the first cabin to start bookings.</div>
            @else
                <div class="cabin-grid">
                    @foreach($cabinCards as $card)
                        <div class="cabin-card">
                            <div class="status-bar status-{{ $card['status']['class'] }}"></div>
                            <div class="body">
                                <div class="d-flex justify-content-between gap-2 align-items-start">
                                    <div>
                                        <div class="cabin-code">{{ $card['model']->cabin_code }}</div>
                                        <h5>{{ $card['model']->name }}</h5>
                                    </div>
                                    <span class="status-chip">
                                        <span class="badge rounded-pill text-bg-light">{{ ucfirst($card['model']->cabin_type) }}</span>
                                    </span>
                                </div>
                                <div class="cabin-meta mt-3">
                                    <div><strong>Status:</strong> {{ $card['status']['label'] }}</div>
                                    <div><strong>Mode:</strong> {{ ucfirst($card['model']->booking_mode) }}</div>
                                    <div><strong>Rate:</strong> ₹{{ number_format((float) ($card['model']->hourly_rate ?? 0), 2) }} hourly</div>
                                    <div><strong>Note:</strong> {{ $card['status']['meta'] }}</div>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('admin.cabins.edit', $card['model']->id) }}" class="btn btn-sm btn-outline-secondary">Edit Cabin</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="cabin-panel">
        <div class="panel-head">
            <div>
                <h5 class="mb-1">Upcoming Renewals</h5>
                <div class="text-muted">Active subscriptions ending within the next 7 days.</div>
            </div>
            <a href="{{ route('admin.cabins.subscriptions.index') }}" class="btn btn-outline-primary btn-sm">Manage Subscriptions</a>
        </div>
        <div class="panel-body">
            @if($upcomingRenewals->isEmpty())
                <div class="empty-note">No cabin subscriptions are due for renewal in the next 7 days.</div>
            @else
                <div class="d-flex flex-column gap-3">
                    @foreach($upcomingRenewals as $renewal)
                        @php $subscription = $renewal['model']; @endphp
                        <div class="border rounded-4 p-3">
                            <div class="d-flex justify-content-between gap-3 flex-wrap">
                                <div>
                                    <div class="mini-label">{{ $subscription->cabin->cabin_code ?? '-' }}</div>
                                    <div class="mini-value">{{ $subscription->doctor->name ?? '-' }}</div>
                                    <div class="text-muted mt-2">{{ optional($subscription->start_date)->format('d M Y') }} to {{ optional($subscription->end_date)->format('d M Y') }}</div>
                                    <div class="text-muted">{{ substr($subscription->subscription_start_time ?: '09:00:00', 0, 5) }} to {{ substr($subscription->subscription_end_time ?: '21:00:00', 0, 5) }}</div>
                                </div>
                                <div class="text-md-end">
                                    <div class="badge text-bg-warning mb-2">
                                        {{ $renewal['days_left'] === 0 ? 'Ends Today' : $renewal['days_left'] . ' day(s) left' }}
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap justify-content-md-end">
                                        <a href="{{ $renewal['show_url'] }}" class="btn btn-outline-secondary btn-sm">View</a>
                                        <a href="{{ $renewal['edit_url'] }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                                        <a href="{{ $renewal['renew_url'] }}" class="btn btn-brand btn-sm">Renew</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="cabin-split">
        <div class="cabin-panel">
            <div class="panel-head">
                <div>
                    <h5 class="mb-1">Today's Timeline</h5>
                    <div class="text-muted">Hourly cabin bookings scheduled for today.</div>
                </div>
                <a href="{{ route('admin.cabins.bookings.index') }}" class="btn btn-outline-primary btn-sm">All Bookings</a>
            </div>
            <div class="panel-body">
                @if($timeline->isEmpty())
                    <div class="empty-note">No cabin bookings are scheduled for today.</div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle metric-table">
                            <thead>
                                <tr>
                                    <th>Cabin</th>
                                    <th>Doctor</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($timeline as $booking)
                                    <tr>
                                        <td>{{ $booking->cabin->cabin_code ?? '-' }}</td>
                                        <td>{{ $booking->doctor->name ?? '-' }}</td>
                                        <td>{{ \Carbon\Carbon::createFromFormat('H:i:s', $booking->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::createFromFormat('H:i:s', $booking->end_time)->format('h:i A') }}</td>
                                        <td><span class="badge text-bg-info">{{ ucfirst($booking->status) }}</span></td>
                                        <td>₹{{ number_format((float) $booking->total_amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="cabin-panel">
            <div class="panel-head">
                <div>
                    <h5 class="mb-1">Monthly Subscriptions</h5>
                    <div class="text-muted">Cabins currently reserved on monthly plans.</div>
                </div>
                <a href="{{ route('admin.cabins.subscriptions.index') }}" class="btn btn-outline-primary btn-sm">All Subscriptions</a>
            </div>
            <div class="panel-body">
                @if($activeSubscriptions->isEmpty())
                    <div class="empty-note">No active monthly subscriptions at the moment.</div>
                @else
                    <div class="d-flex flex-column gap-3">
                        @foreach($activeSubscriptions as $subscription)
                            <div class="border rounded-4 p-3">
                                <div class="d-flex justify-content-between gap-3">
                                    <div>
                                        <div class="mini-label">{{ $subscription->cabin->cabin_code ?? '-' }}</div>
                                        <div class="mini-value">{{ $subscription->doctor->name ?? '-' }}</div>
                                    </div>
                                    <span class="badge text-bg-success">Active</span>
                                </div>
                                <div class="text-muted mt-2">
                                    {{ optional($subscription->start_date)->format('d M Y') }} to {{ optional($subscription->end_date)->format('d M Y') }}
                                </div>
                                <div class="text-muted">{{ substr($subscription->subscription_start_time ?: '09:00:00', 0, 5) }} to {{ substr($subscription->subscription_end_time ?: '21:00:00', 0, 5) }}</div>
                                <div class="text-muted">₹{{ number_format((float) $subscription->total_amount, 2) }} total</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
