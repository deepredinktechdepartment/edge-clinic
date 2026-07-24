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

    <div class="cabin-panel">
        <div class="panel-head"><div><h5 class="mb-1">Cabin Calendar</h5><div class="text-muted">Choose a day to see who is booked and which timings are free.</div></div><a href="{{ route('admin.cabins.bookings.create', ['cabin_id' => $cabin->id]) }}" class="btn btn-brand btn-sm">New Booking</a></div>
        <div class="panel-body"><div class="cabin-calendar-layout"><div><div class="cabin-calendar-controls"><button type="button" class="btn btn-outline-secondary btn-sm" id="cabinViewPrevious"><i class="bi bi-chevron-left"></i></button><strong id="cabinViewMonth"></strong><button type="button" class="btn btn-outline-secondary btn-sm" id="cabinViewNext"><i class="bi bi-chevron-right"></i></button></div><div class="cabin-month-weekdays"><span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span></div><div class="cabin-month-days" id="cabinViewDays"></div></div><div class="cabin-day-view"><div class="cabin-day-view-head"><div><strong id="cabinViewDate"></strong><div class="text-muted small">Green is free. Blue is booked. Purple is a monthly subscription.</div></div></div><div id="cabinViewSchedule" class="cabin-day-schedule"><div class="empty-note">Loading availability…</div></div></div></div></div>
    </div>

    <div class="cabin-panel">
        <div class="panel-head"><div><h5 class="mb-1">Monthly Subscriptions</h5><div class="text-muted">Each subscription separately shows its booked days and time window.</div></div><a href="{{ route('admin.cabins.subscriptions.create', ['cabin_id' => $cabin->id]) }}" class="btn btn-outline-secondary btn-sm">New Subscription</a></div>
        <div class="panel-body">
            @forelse($cabin->subscriptions as $subscription)
                @php $subscriptionDays = array_map('intval', $subscription->subscription_days ?: [0,1,2,3,4,5,6]); @endphp
                <div class="border rounded-4 p-3 mb-3"><div class="d-flex justify-content-between gap-3 flex-wrap"><div><strong class="text-dark">{{ $subscription->doctor->name ?? '-' }}</strong><div class="text-muted small mt-1">{{ optional($subscription->start_date)->format('d M Y') }} – {{ optional($subscription->end_date)->format('d M Y') }} · {{ substr($subscription->subscription_start_time ?: '', 0, 5) }} – {{ substr($subscription->subscription_end_time ?: '', 0, 5) }}</div></div><span class="badge text-bg-success">{{ ucfirst($subscription->status) }}</span></div><div class="cabin-subscription-days mt-3">@foreach(['S','M','T','W','T','F','S'] as $number => $day)<span class="{{ in_array($number, $subscriptionDays, true) ? 'active' : '' }}">{{ $day }}</span>@endforeach</div></div>
            @empty
                <div class="empty-note">No monthly subscriptions for this cabin.</div>
            @endforelse
        </div>
    </div>

    <div class="row g-4 align-items-start d-none">
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

@push('scripts')
<script>
(() => {
 const days=document.getElementById('cabinViewDays'); if(!days)return;
 const month=document.getElementById('cabinViewMonth'), title=document.getElementById('cabinViewDate'), schedule=document.getElementById('cabinViewSchedule');
 const url=@json(route('admin.cabins.dashboard.availability')), cabinId={{ $cabin->id }}; let selected=new Date(), shown=new Date(selected.getFullYear(),selected.getMonth(),1);
 const iso=d=>`${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; const nice=d=>new Date(`${d}T00:00:00`).toLocaleDateString(undefined,{weekday:'long',day:'numeric',month:'long',year:'numeric'}); const clock=t=>new Date(`2000-01-01T${t}`).toLocaleTimeString([],{hour:'numeric',minute:'2-digit'});
 function draw(){month.textContent=shown.toLocaleDateString(undefined,{month:'long',year:'numeric'});days.innerHTML='';for(let i=0;i<shown.getDay();i++)days.insertAdjacentHTML('beforeend','<span></span>');let total=new Date(shown.getFullYear(),shown.getMonth()+1,0).getDate();for(let n=1;n<=total;n++){let d=new Date(shown.getFullYear(),shown.getMonth(),n), chosen=iso(d)===iso(selected),today=iso(d)===iso(new Date());days.insertAdjacentHTML('beforeend',`<button type="button" data-date="${iso(d)}" class="cabin-day-button ${chosen?'is-selected':''} ${today?'is-today':''}">${n}</button>`);}}
 async function load(){schedule.innerHTML='<div class="empty-note">Loading availability…</div>';try{let r=await fetch(`${url}?date=${iso(selected)}`,{headers:{Accept:'application/json'}});let d=await r.json(), cabin=d.cabins.find(x=>Number(x.id)===Number(cabinId));title.textContent=nice(d.date);let items=[...cabin.available.map(x=>({...x,type:'available',label:'Available'})),...cabin.events].sort((a,b)=>a.start.localeCompare(b.start));schedule.innerHTML=items.length?`<div class="cabin-schedule-events">${items.map(x=>`<span class="cabin-time-block ${x.type}">${clock(x.start)} – ${clock(x.end)} · ${x.label}</span>`).join('')}</div>`:'<div class="empty-note">No available timings for this day.</div>';}catch(e){schedule.innerHTML='<div class="empty-note">Could not load availability.</div>';}}
 days.addEventListener('click',e=>{let b=e.target.closest('[data-date]');if(!b)return;selected=new Date(`${b.dataset.date}T00:00:00`);draw();load()});document.getElementById('cabinViewPrevious').onclick=()=>{shown.setMonth(shown.getMonth()-1);draw()};document.getElementById('cabinViewNext').onclick=()=>{shown.setMonth(shown.getMonth()+1);draw()};draw();load();
})();
</script>
@endpush
@endsection
