@extends('template_v1')

@section('content')
@include('cabins.partials.styles')

@php
    $dashboardActions = $isReceptionUser
        ? []
        : [
            ['url' => route('admin.cabins.create'), 'label' => 'Add Cabin', 'icon' => 'bi bi-door-open', 'class' => 'btn-brand'],
            ['url' => route('admin.cabins.bookings.create'), 'label' => 'New Booking', 'icon' => 'bi bi-calendar-plus', 'class' => 'btn-brand'],
            ['url' => route('admin.cabins.subscriptions.create'), 'label' => 'New Subscription', 'icon' => 'bi bi-person-workspace', 'class' => 'btn-brand'],
        ];
@endphp

<div class="cabin-shell">
    @include('cabins.partials.page-header', [
        'title' => 'Cabin Dashboard',
        'subtitle' => $isReceptionUser
            ? 'Read-only view of booked rooms, available rooms, and today\'s room-wise free timings.'
            : 'Live view of cabin availability, today\'s timeline, and active monthly allocations.',
        'actions' => $dashboardActions,
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
        @if(!$isReceptionUser)
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
        @endif
    </div>

    <div class="cabin-panel">
        <div class="panel-head"><div><h5 class="mb-1">Cabin Availability Calendar</h5><div class="text-muted">Choose a day to see every cabin's free and booked timings.</div></div>@if(!$isReceptionUser)<a href="{{ route('admin.cabins.index') }}" class="btn btn-outline-primary btn-sm">Manage Cabins</a>@endif</div>
        <div class="panel-body"><div class="cabin-calendar-layout"><div><div class="cabin-calendar-controls"><button type="button" class="btn btn-outline-secondary btn-sm" id="calendarPrevious"><i class="bi bi-chevron-left"></i></button><strong id="calendarTitle"></strong><button type="button" class="btn btn-outline-secondary btn-sm" id="calendarNext"><i class="bi bi-chevron-right"></i></button></div><div class="cabin-month-weekdays"><span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span></div><div class="cabin-month-days" id="cabinMonthDays"></div></div><div class="cabin-day-view"><div class="cabin-day-view-head"><div><strong id="selectedDateTitle"></strong><div class="text-muted small">Click a green time block to start an hourly booking.</div></div><div class="cabin-calendar-legend"><span><i class="available"></i> Available</span><span><i class="booking"></i> Booked</span><span><i class="subscription"></i> Subscription</span><span><i class="unavailable"></i> Unavailable</span></div></div><div id="cabinDaySchedule" class="cabin-day-schedule"><div class="empty-note">Loading cabin availability…</div></div></div></div></div>
    </div>

    <div class="cabin-panel d-none">
        <div class="panel-head">
            <div>
                <h5 class="mb-1">Cabin Grid</h5>
                <div class="text-muted">
                    {{ $isReceptionUser ? 'Room-wise booked status and today\'s available timing blocks.' : 'Visual status of each cabin using the Edge Clinic admin shell.' }}
                </div>
            </div>
            @if(!$isReceptionUser)
                <a href="{{ route('admin.cabins.index') }}" class="btn btn-outline-primary btn-sm">Manage Cabins</a>
            @endif
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
                                    @if(!$isReceptionUser)
                                        <div><strong>Rate:</strong> ₹{{ number_format((float) ($card['model']->hourly_rate ?? 0), 2) }} hourly</div>
                                        <div><strong>Note:</strong> {{ $card['status']['meta'] }}</div>
                                    @endif
                                    <div><strong>Available Today:</strong> {{ $card['availability_text'] }}</div>
                                </div>
                                @if(!$isReceptionUser)
                                    <div class="mt-3">
                                        <a href="{{ route('admin.cabins.edit', $card['model']->id) }}" class="btn btn-sm btn-outline-secondary">Edit Cabin</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    @if(!$isReceptionUser)
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
    @endif

    <div class="cabin-split">
        <div class="cabin-panel">
            <div class="panel-head">
                <div>
                    <h5 class="mb-1">Today's Timeline</h5>
                    <div class="text-muted">Hourly cabin bookings scheduled for today.</div>
                </div>
                @if(!$isReceptionUser)
                    <a href="{{ route('admin.cabins.bookings.index') }}" class="btn btn-outline-primary btn-sm">All Bookings</a>
                @endif
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
                                    @if(!$isReceptionUser)
                                        <th>Amount</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($timeline as $booking)
                                    <tr>
                                        <td>{{ $booking->cabin->cabin_code ?? '-' }}</td>
                                        <td>{{ $booking->doctor->name ?? '-' }}</td>
                                        <td>{{ \Carbon\Carbon::createFromFormat('H:i:s', $booking->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::createFromFormat('H:i:s', $booking->end_time)->format('h:i A') }}</td>
                                        <td><span class="badge text-bg-info">{{ ucfirst($booking->status) }}</span></td>
                                        @if(!$isReceptionUser)
                                            <td>₹{{ number_format((float) $booking->total_amount, 2) }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        @if(!$isReceptionUser)
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
        @endif
    </div>
</div>

@push('scripts')
<script>
(() => {
    const monthDays = document.getElementById('cabinMonthDays');
    if (!monthDays) return;
    const calendarTitle = document.getElementById('calendarTitle');
    const selectedTitle = document.getElementById('selectedDateTitle');
    const schedule = document.getElementById('cabinDaySchedule');
    const availabilityUrl = @json(route('admin.cabins.dashboard.availability'));
    const bookingUrl = @json(route('admin.cabins.bookings.create'));
    const canBook = @json(!$isReceptionUser);
    let selected = new Date(); selected.setHours(0, 0, 0, 0);
    let displayed = new Date(selected.getFullYear(), selected.getMonth(), 1);
    const iso = date => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    const label = value => new Date(`${value}T00:00:00`).toLocaleDateString(undefined, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    const time = value => new Date(`2000-01-01T${value}`).toLocaleTimeString([], {hour: 'numeric', minute: '2-digit'});

    function renderMonth() {
        calendarTitle.textContent = displayed.toLocaleDateString(undefined, {month: 'long', year: 'numeric'});
        monthDays.innerHTML = '';
        const offset = displayed.getDay(), days = new Date(displayed.getFullYear(), displayed.getMonth() + 1, 0).getDate();
        for (let i = 0; i < offset; i++) monthDays.insertAdjacentHTML('beforeend', '<span></span>');
        for (let day = 1; day <= days; day++) {
            const date = new Date(displayed.getFullYear(), displayed.getMonth(), day);
            const today = iso(date) === iso(new Date()), chosen = iso(date) === iso(selected);
            monthDays.insertAdjacentHTML('beforeend', `<button type="button" class="cabin-day-button ${today ? 'is-today' : ''} ${chosen ? 'is-selected' : ''}" data-date="${iso(date)}">${day}</button>`);
        }
    }
    function renderSchedule(data) {
        selectedTitle.textContent = label(data.date);
        if (!data.cabins.length) { schedule.innerHTML = '<div class="empty-note">No cabin records are available yet.</div>'; return; }
        schedule.innerHTML = data.cabins.map(cabin => {
            const blocks = [
                ...cabin.available.map(item => ({...item, type: 'available', label: 'Available'})),
                ...cabin.events
            ].sort((a, b) => a.start.localeCompare(b.start)).map(item => {
                const text = `${time(item.start)} – ${time(item.end)} · ${item.label}`;
                const attributes = item.type === 'available' && canBook ? `data-cabin="${cabin.id}" data-start="${item.start}" data-end="${item.end}" title="Create booking"` : 'disabled';
                return `<button type="button" class="cabin-time-block ${item.type}" ${attributes}>${text}</button>`;
            }).join('');
            return `<div class="cabin-schedule-row"><div class="cabin-schedule-label">${cabin.code}<small>${cabin.name}</small></div><div class="cabin-schedule-events">${blocks || '<span class="text-muted small">No available time</span>'}</div></div>`;
        }).join('');
    }
    async function loadDay() {
        schedule.innerHTML = '<div class="empty-note">Loading cabin availability…</div>';
        try { const response = await fetch(`${availabilityUrl}?date=${encodeURIComponent(iso(selected))}`, {headers: {'Accept': 'application/json'}}); if (!response.ok) throw new Error(); renderSchedule(await response.json()); }
        catch { schedule.innerHTML = '<div class="empty-note">Could not load cabin availability. Please try again.</div>'; }
    }
    monthDays.addEventListener('click', event => { const button = event.target.closest('[data-date]'); if (!button) return; selected = new Date(`${button.dataset.date}T00:00:00`); renderMonth(); loadDay(); });
    schedule.addEventListener('click', event => { const block = event.target.closest('.cabin-time-block.available'); if (!block || !canBook) return; window.location.href = `${bookingUrl}?cabin_id=${block.dataset.cabin}&booking_date=${iso(selected)}&start_time=${block.dataset.start}&end_time=${block.dataset.end}`; });
    document.getElementById('calendarPrevious').addEventListener('click', () => { displayed.setMonth(displayed.getMonth() - 1); renderMonth(); });
    document.getElementById('calendarNext').addEventListener('click', () => { displayed.setMonth(displayed.getMonth() + 1); renderMonth(); });
    renderMonth(); loadDay();
})();
</script>
@endpush
@endsection
