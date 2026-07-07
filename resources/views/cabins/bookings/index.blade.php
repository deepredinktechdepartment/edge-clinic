@extends('template_v1')

@section('content')
@include('cabins.partials.styles')

<div class="cabin-shell">
    @php
        $currentMonthStart = now()->startOfMonth()->format('Y-m-d');
        $currentMonthEnd = now()->endOfMonth()->format('Y-m-d');
    @endphp
    @include('cabins.partials.page-header', [
        'title' => $pageTitle,
        'subtitle' => 'Track hourly cabin allocations, schedule conflicts, and payment status.',
        'actions' => [
            ['url' => route('admin.cabins.dashboard'), 'label' => 'Dashboard', 'icon' => 'bi bi-grid', 'class' => 'btn-outline-secondary'],
            ['url' => $addlink, 'label' => 'Add Booking', 'icon' => 'bi bi-calendar-plus', 'class' => 'btn-brand'],
        ],
    ])

    <div class="cabin-panel">
        <div class="panel-body">
            <div class="cabin-filterbar mb-4">
                <div class="cabin-filter-field">
                    <label class="cabin-filter-label" for="booking_search">Search</label>
                    <input type="text" id="booking_search" class="cabin-filter-input" placeholder="Search doctor, cabin or amount">
                </div>
                <div class="cabin-filter-field">
                    <label class="cabin-filter-label" for="booking_type_filter">Booking Type</label>
                    <select id="booking_type_filter" class="cabin-filter-select">
                        <option value="">All Types</option>
                        <option value="hourly">Hourly</option>
                        <option value="half_day">Half Day</option>
                        <option value="full_day">Full Day</option>
                    </select>
                </div>
                <div class="cabin-filter-field">
                    <label class="cabin-filter-label" for="booking_status_filter">Status</label>
                    <select id="booking_status_filter" class="cabin-filter-select">
                        <option value="">All Status</option>
                        <option value="booked">Booked</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="cabin-filter-field">
                    <label class="cabin-filter-label" for="booking_payment_filter">Payment</label>
                    <select id="booking_payment_filter" class="cabin-filter-select">
                        <option value="">All Payment</option>
                        <option value="pay_now">Pay Now</option>
                        <option value="pay_later">Pay Later</option>
                    </select>
                </div>
                <div class="cabin-filter-field">
                    <label class="cabin-filter-label" for="booking_date_from">From Date</label>
                    <input type="date" id="booking_date_from" class="cabin-filter-input" value="{{ $currentMonthStart }}">
                </div>
                <div class="cabin-filter-field">
                    <label class="cabin-filter-label" for="booking_date_to">To Date</label>
                    <input type="date" id="booking_date_to" class="cabin-filter-input" value="{{ $currentMonthEnd }}">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="cabin-bookings-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Cabin</th>
                            <th>Doctor</th>
                            <th>Type</th>
                            <th>Time</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th width="160">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $booking)
                            <tr
                                data-search="{{ strtolower(trim(($booking->cabin->cabin_code ?? '') . ' ' . ($booking->doctor->name ?? '') . ' ' . ($booking->cabin->name ?? '') . ' ' . number_format((float) $booking->total_amount, 2))) }}"
                                data-type="{{ strtolower((string) $booking->booking_type) }}"
                                data-status="{{ strtolower((string) $booking->status) }}"
                                data-payment="{{ strtolower((string) ($booking->payment_choice ?? 'pay_later')) }}"
                                data-booking-date="{{ optional($booking->booking_date)->format('Y-m-d') }}">
                                <td>{{ optional($booking->booking_date)->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.cabins.bookings.show', $booking->id) }}" class="cabin-name-link">
                                        {{ $booking->cabin->cabin_code ?? '-' }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('admin.cabins.doctors.show', $booking->doctor_id) }}" class="cabin-name-link">
                                        {{ $booking->doctor->name ?? '-' }}
                                    </a>
                                </td>
                                <td>{{ ucwords(str_replace('_', ' ', $booking->booking_type)) }}</td>
                                <td>{{ \Carbon\Carbon::createFromFormat('H:i:s', $booking->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::createFromFormat('H:i:s', $booking->end_time)->format('h:i A') }}</td>
                                <td>Rs {{ number_format((float) $booking->total_amount, 2) }}</td>
                                <td>
                                    <div>{{ ucwords(str_replace('_', ' ', $booking->payment_choice ?? 'pay_later')) }}</div>
                                    <div class="small text-muted">{{ $booking->payment_status ?: '-' }}</div>
                                </td>
                                <td><span class="badge text-bg-info">{{ ucfirst($booking->status) }}</span></td>
                                <td>
                                    <a href="{{ route('admin.cabins.bookings.show', $booking->id) }}" class="text-primary me-2"><i class="fa fa-eye"></i></a>
                                    <a href="{{ route('admin.cabins.bookings.edit', $booking->id) }}" class="text-warning me-2"><i class="fa fa-edit"></i></a>
                                    <form action="{{ route('admin.cabins.bookings.destroy', $booking->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this booking?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="border-0 bg-transparent p-0"><i class="fa fa-trash text-danger"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof $ === 'undefined' || !$('#cabin-bookings-table').length) {
        return;
    }

    var table = $('#cabin-bookings-table').DataTable({
        pageLength: 50,
        lengthMenu: [[50, 100, 150, -1], [50, 100, 150, 'All']],
        responsive: true,
        dom: 'rt<"d-flex justify-content-between align-items-center mt-3"lip>'
    });

    var searchInput = document.getElementById('booking_search');
    var typeFilter = document.getElementById('booking_type_filter');
    var statusFilter = document.getElementById('booking_status_filter');
    var paymentFilter = document.getElementById('booking_payment_filter');
    var dateFromFilter = document.getElementById('booking_date_from');
    var dateToFilter = document.getElementById('booking_date_to');

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable !== table.table().node()) {
            return true;
        }

        var row = table.row(dataIndex).node();
        if (!row) {
            return true;
        }

        var searchValue = (searchInput.value || '').trim().toLowerCase();
        var typeValue = (typeFilter.value || '').trim().toLowerCase();
        var statusValue = (statusFilter.value || '').trim().toLowerCase();
        var paymentValue = (paymentFilter.value || '').trim().toLowerCase();
        var dateFromValue = (dateFromFilter.value || '').trim();
        var dateToValue = (dateToFilter.value || '').trim();

        var rowSearch = row.getAttribute('data-search') || '';
        var rowType = row.getAttribute('data-type') || '';
        var rowStatus = row.getAttribute('data-status') || '';
        var rowPayment = row.getAttribute('data-payment') || '';
        var rowDate = row.getAttribute('data-booking-date') || '';

        var matchesDate = true;
        if (rowDate) {
            if (dateFromValue && rowDate < dateFromValue) {
                matchesDate = false;
            }
            if (dateToValue && rowDate > dateToValue) {
                matchesDate = false;
            }
        }

        return (!searchValue || rowSearch.indexOf(searchValue) !== -1)
            && (!typeValue || rowType === typeValue)
            && (!statusValue || rowStatus === statusValue)
            && (!paymentValue || rowPayment === paymentValue)
            && matchesDate;
    });

    [searchInput, typeFilter, statusFilter, paymentFilter, dateFromFilter, dateToFilter].forEach(function (element) {
        element.addEventListener('input', function () {
            table.draw();
        });
        element.addEventListener('change', function () {
            table.draw();
        });
    });
});
</script>
@endsection
