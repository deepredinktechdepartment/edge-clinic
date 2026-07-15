@extends('template_v1')

@section('content')
@include('cabins.partials.styles')

<div class="cabin-shell">
    @include('cabins.partials.page-header', [
        'title' => $pageTitle,
        'subtitle' => 'Doctor-wise cabin demand, active subscription, hourly usage, and outstanding cabin billing.',
        'actions' => [
            ['url' => route('admin.cabins.dashboard'), 'label' => 'Dashboard', 'icon' => 'bi bi-grid', 'class' => 'btn-outline-secondary'],
            ['url' => route('admin.cabins.invoices.create'), 'label' => 'Generate Invoice', 'icon' => 'bi bi-receipt', 'class' => 'btn-brand'],
        ],
    ])

    <div class="row g-4">
        @forelse($doctors as $doctor)
            <div class="col-lg-4 col-md-6">
                <div class="cabin-panel h-100">
                    <div class="panel-body">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <h5 class="mb-1">{{ $doctor->name }}</h5>
                                <div class="text-muted">{{ $doctor->department->name ?? $doctor->designation ?? 'Doctor' }}</div>
                            </div>
                            <span class="badge text-bg-success">{{ $doctor->is_active ? 'Active' : 'Inactive' }}</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="mini-label">Current Cabin</div>
                                <div class="mini-value">{{ $doctor->active_subscription->cabin->cabin_code ?? $doctor->last_booking->cabin->cabin_code ?? '-' }}</div>
                            </div>
                            <div class="col-6">
                                <div class="mini-label">Allocation</div>
                                <div class="mini-value">{{ $doctor->active_subscription ? 'Monthly' : ($doctor->last_booking ? 'Hourly' : '-') }}</div>
                            </div>
                            <div class="col-6">
                                <div class="mini-label">Hourly Bookings</div>
                                <div class="mini-value">{{ $doctor->cabin_hourly_bookings }}</div>
                            </div>
                            <div class="col-6">
                                <div class="mini-label">Outstanding</div>
                                <div class="mini-value">₹{{ number_format((float) $doctor->outstanding_cabin_amount, 2) }}</div>
                            </div>
                        </div>
                        <div class="mt-3 d-flex gap-2">
                            <a href="{{ route('admin.cabins.doctors.show', $doctor->id) }}" class="btn btn-outline-secondary btn-sm flex-fill">Profile</a>
                            <a href="{{ route('admin.cabins.invoices.create', ['doctor_id' => $doctor->id]) }}" class="btn btn-outline-primary btn-sm flex-fill">Invoice</a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="empty-note">No doctors available.</div>
            </div>
        @endforelse
    </div>
</div>
@endsection
