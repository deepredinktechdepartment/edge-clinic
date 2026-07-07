@extends('template_v1')

@section('content')
@include('cabins.partials.styles')

<div class="cabin-shell">
    @include('cabins.partials.page-header', [
        'title' => $doctor->name,
        'subtitle' => 'Doctor details with full cabin record, including subscriptions, bookings, and invoices.',
        'actions' => [
            ['url' => route('admin.doctors'), 'label' => 'Back to Doctors', 'icon' => 'bi bi-arrow-left', 'class' => 'btn-outline-secondary'],
            ['url' => route('admin.cabins.invoices.create', ['doctor_id' => $doctor->id]), 'label' => 'New Invoice', 'icon' => 'bi bi-receipt', 'class' => 'btn-brand'],
        ],
    ])

    <div class="cabin-panel">
        <div class="panel-body">
            <div class="row g-4 align-items-start">
                <div class="col-lg-3 col-md-4">
                    @php
                        $doctorPhoto = $doctor->photo && file_exists(public_path('uploads/doctors/' . $doctor->photo))
                            ? asset('public/uploads/doctors/' . $doctor->photo)
                            : null;
                    @endphp
                    <div class="booking-invoice-meta-box h-100 text-center">
                        @if($doctorPhoto)
                            <img src="{{ $doctorPhoto }}" alt="{{ $doctor->name }}" class="img-fluid rounded mb-3" style="max-height: 220px; object-fit: cover;">
                        @else
                            <div class="cabin-avatar mx-auto mb-3" style="width:72px;height:72px;font-size:1.4rem;">
                                {{ strtoupper(substr($doctor->name ?? 'D', 0, 1)) }}
                            </div>
                        @endif
                        <div class="mini-label">Department</div>
                        <div class="mini-value">{{ $doctor->department->name ?? '-' }}</div>
                        <div class="text-muted mt-2">{{ $doctor->designation ?? 'Doctor' }}</div>
                    </div>
                </div>
                <div class="col-lg-9 col-md-8">
                    <div class="booking-invoice-meta">
                        <div class="booking-invoice-meta-box">
                            <div class="mini-label">Doctor Name</div>
                            <div class="mini-value">{{ $doctor->name ?? '-' }}</div>
                        </div>
                        <div class="booking-invoice-meta-box">
                            <div class="mini-label">Qualification</div>
                            <div class="mini-value">{{ $doctor->qualification ?: '-' }}</div>
                        </div>
                        <div class="booking-invoice-meta-box">
                            <div class="mini-label">Experience</div>
                            <div class="mini-value">{{ $doctor->experience ?: '-' }}</div>
                        </div>
                        <div class="booking-invoice-meta-box">
                            <div class="mini-label">Appointment Fee</div>
                            <div class="mini-value">Rs {{ number_format((float) ($doctor->appointment_fee ?? 0), 2) }}</div>
                        </div>
                    </div>
                    <div class="booking-invoice-meta-box mt-3">
                        <div class="mini-label">Bio</div>
                        <div class="text-muted">{!! nl2br(e($doctor->bio ?: 'No bio available.')) !!}</div>
                    </div>
                    <div class="booking-invoice-meta-box mt-3">
                        <div class="mini-label">Direct Booking Link</div>
                        <div class="mini-value" style="word-break: break-all;">{{ route('appointment.book', ['doctor' => $doctor->slug ?: $doctor->id]) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="cabin-grid">
        <div class="cabin-stat">
            <div class="value">{{ $subscriptions->count() }}</div>
            <div class="label">Subscriptions</div>
        </div>
        <div class="cabin-stat">
            <div class="value">{{ $bookings->count() }}</div>
            <div class="label">Hourly Bookings</div>
        </div>
        <div class="cabin-stat">
            <div class="value">{{ $invoices->count() }}</div>
            <div class="label">Cabin Invoices</div>
        </div>
        <div class="cabin-stat">
            <div class="value">₹{{ number_format((float) $invoices->whereIn('status', ['draft', 'sent', 'overdue'])->sum('total_amount'), 2) }}</div>
            <div class="label">Outstanding</div>
        </div>
    </div>

    <div class="cabin-split">
        <div class="cabin-panel">
            <div class="panel-head"><h5 class="mb-0">Subscriptions</h5></div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Cabin</th><th>Period</th><th>Status</th><th>Total</th></tr></thead>
                        <tbody>
                        @forelse($subscriptions as $subscription)
                            <tr>
                                <td>{{ $subscription->cabin->cabin_code ?? '-' }}</td>
                                <td>
                                    <div>{{ optional($subscription->start_date)->format('d M Y') }} - {{ optional($subscription->end_date)->format('d M Y') }}</div>
                                    <div class="small text-muted">{{ substr($subscription->subscription_start_time ?: '09:00:00', 0, 5) }} - {{ substr($subscription->subscription_end_time ?: '21:00:00', 0, 5) }}</div>
                                </td>
                                <td>{{ ucfirst($subscription->status) }}</td>
                                <td>₹{{ number_format((float) $subscription->total_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No subscriptions found.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="cabin-panel">
            <div class="panel-head"><h5 class="mb-0">Invoice History</h5></div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Invoice</th><th>Period</th><th>Status</th><th>Total</th><th></th></tr></thead>
                        <tbody>
                        @forelse($invoices as $invoice)
                            <tr>
                                <td>{{ $invoice->invoice_number }}</td>
                                <td>{{ optional($invoice->period_start)->format('d M Y') }} - {{ optional($invoice->period_end)->format('d M Y') }}</td>
                                <td>{{ ucfirst($invoice->status) }}</td>
                                <td>₹{{ number_format((float) $invoice->total_amount, 2) }}</td>
                                <td><a href="{{ route('admin.cabins.invoices.show', $invoice->id) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">No invoices found.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="cabin-panel">
        <div class="panel-head"><h5 class="mb-0">Booking History</h5></div>
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Date</th><th>Cabin</th><th>Time</th><th>Hours</th><th>Amount</th></tr></thead>
                    <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td>{{ optional($booking->booking_date)->format('d M Y') }}</td>
                            <td>{{ $booking->cabin->cabin_code ?? '-' }}</td>
                            <td>{{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}</td>
                            <td>{{ number_format((float) $booking->total_hours, 2) }}</td>
                            <td>₹{{ number_format((float) $booking->total_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No bookings found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
