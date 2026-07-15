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
        'subtitle' => 'Monthly cabin allocations with doctor ownership, billing amount, and validity period.',
        'actions' => [
            ['url' => route('admin.cabins.dashboard'), 'label' => 'Dashboard', 'icon' => 'bi bi-grid', 'class' => 'btn-outline-secondary'],
            ['url' => $addlink, 'label' => 'Add Subscription', 'icon' => 'bi bi-plus-circle', 'class' => 'btn-brand'],
        ],
    ])

    <div class="cabin-panel">
        <div class="panel-head">
            <div>
                <h5 class="mb-1">Upcoming Renewals</h5>
                <div class="text-muted">Subscriptions ending within the next 7 days so the team can renew them early.</div>
            </div>
        </div>
        <div class="panel-body">
            @if($upcomingRenewals->isEmpty())
                <div class="empty-note">No active subscriptions are due for renewal in the next 7 days.</div>
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
                                    <div class="text-muted">{{ substr($subscription->subscription_start_time ?: '09:00:00', 0, 5) }} - {{ substr($subscription->subscription_end_time ?: '21:00:00', 0, 5) }}</div>
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

    <div class="cabin-panel">
        <div class="panel-body">
            <div class="cabin-filterbar mb-4">
                <div class="cabin-filter-field">
                    <label class="cabin-filter-label" for="subscription_search">Search</label>
                    <input type="text" id="subscription_search" class="cabin-filter-input" placeholder="Search doctor, cabin or amount">
                </div>
                <div class="cabin-filter-field">
                    <label class="cabin-filter-label" for="subscription_status_filter">Status</label>
                    <select id="subscription_status_filter" class="cabin-filter-select">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="cabin-filter-field">
                    <label class="cabin-filter-label" for="subscription_invoice_day_filter">Invoice Day</label>
                    <select id="subscription_invoice_day_filter" class="cabin-filter-select">
                        <option value="">All Invoice Days</option>
                        @for($day = 1; $day <= 31; $day++)
                            <option value="{{ $day }}">{{ $day }}</option>
                        @endfor
                    </select>
                </div>
                <div class="cabin-filter-field">
                    <label class="cabin-filter-label" for="subscription_cabin_filter">Cabin Mode</label>
                    <select id="subscription_cabin_filter" class="cabin-filter-select">
                        <option value="">All Cabin Modes</option>
                        <option value="hourly">Hourly</option>
                        <option value="monthly">Monthly</option>
                        <option value="both">Both</option>
                    </select>
                </div>
                <div class="cabin-filter-field">
                    <label class="cabin-filter-label" for="subscription_date_from">From Date</label>
                    <input type="date" id="subscription_date_from" class="cabin-filter-input" value="{{ $currentMonthStart }}">
                </div>
                <div class="cabin-filter-field">
                    <label class="cabin-filter-label" for="subscription_date_to">To Date</label>
                    <input type="date" id="subscription_date_to" class="cabin-filter-input" value="{{ $currentMonthEnd }}">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="cabin-subscriptions-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Cabin</th>
                            <th>Doctor</th>
                            <th>Period</th>
                            <th>Invoice Day</th>
                            <th>Monthly Rate</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th width="160">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subscriptions as $subscription)
                            <tr
                                data-search="{{ strtolower(trim(($subscription->cabin->cabin_code ?? '') . ' ' . ($subscription->doctor->name ?? '') . ' ' . ($subscription->cabin->name ?? '') . ' ' . number_format((float) $subscription->total_amount, 2))) }}"
                                data-status="{{ strtolower((string) $subscription->status) }}"
                                data-invoice-day="{{ (int) $subscription->invoice_day }}"
                                data-mode="{{ strtolower((string) ($subscription->cabin->booking_mode ?? '')) }}"
                                data-start-date="{{ optional($subscription->start_date)->format('Y-m-d') }}"
                                data-end-date="{{ optional($subscription->end_date)->format('Y-m-d') }}">
                                <td>
                                    <a href="{{ route('admin.cabins.subscriptions.show', $subscription->id) }}" class="cabin-name-link">
                                        {{ $subscription->cabin->cabin_code ?? '-' }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('admin.cabins.doctors.show', $subscription->doctor_id) }}" class="cabin-name-link">
                                        {{ $subscription->doctor->name ?? '-' }}
                                    </a>
                                </td>
                                <td>
                                    <div>{{ optional($subscription->start_date)->format('d M Y') }} - {{ optional($subscription->end_date)->format('d M Y') }}</div>
                                    <div class="small text-muted">{{ substr($subscription->subscription_start_time ?: '09:00:00', 0, 5) }} - {{ substr($subscription->subscription_end_time ?: '21:00:00', 0, 5) }}</div>
                                </td>
                                <td>{{ $subscription->invoice_day }}</td>
                                <td>Rs {{ number_format((float) $subscription->monthly_rate, 2) }}</td>
                                <td>Rs {{ number_format((float) $subscription->total_amount, 2) }}</td>
                                <td><span class="badge text-bg-success">{{ ucfirst($subscription->status) }}</span></td>
                                <td>
                                    <a href="{{ route('admin.cabins.subscriptions.show', $subscription->id) }}" class="text-primary me-2"><i class="fa fa-eye"></i></a>
                                    <a href="{{ route('admin.cabins.subscriptions.create', ['renew_from' => $subscription->id]) }}" class="text-info me-2" title="Renew"><i class="fa fa-refresh"></i></a>
                                    <a href="{{ route('admin.cabins.subscriptions.edit', $subscription->id) }}" class="text-warning me-2"><i class="fa fa-edit"></i></a>
                                    <form action="{{ route('admin.cabins.subscriptions.destroy', $subscription->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this subscription?');">
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
    if (typeof $ === 'undefined' || !$('#cabin-subscriptions-table').length) {
        return;
    }

    var table = $('#cabin-subscriptions-table').DataTable({
        pageLength: 50,
        lengthMenu: [[50, 100, 150, -1], [50, 100, 150, 'All']],
        responsive: true,
        dom: 'rt<"d-flex justify-content-between align-items-center mt-3"lip>'
    });

    var searchInput = document.getElementById('subscription_search');
    var statusFilter = document.getElementById('subscription_status_filter');
    var invoiceDayFilter = document.getElementById('subscription_invoice_day_filter');
    var modeFilter = document.getElementById('subscription_cabin_filter');
    var dateFromFilter = document.getElementById('subscription_date_from');
    var dateToFilter = document.getElementById('subscription_date_to');

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable !== table.table().node()) {
            return true;
        }

        var row = table.row(dataIndex).node();
        if (!row) {
            return true;
        }

        var searchValue = (searchInput.value || '').trim().toLowerCase();
        var statusValue = (statusFilter.value || '').trim().toLowerCase();
        var invoiceDayValue = (invoiceDayFilter.value || '').trim();
        var modeValue = (modeFilter.value || '').trim().toLowerCase();
        var dateFromValue = (dateFromFilter.value || '').trim();
        var dateToValue = (dateToFilter.value || '').trim();

        var rowSearch = row.getAttribute('data-search') || '';
        var rowStatus = row.getAttribute('data-status') || '';
        var rowInvoiceDay = row.getAttribute('data-invoice-day') || '';
        var rowMode = row.getAttribute('data-mode') || '';
        var rowStartDate = row.getAttribute('data-start-date') || '';
        var rowEndDate = row.getAttribute('data-end-date') || '';

        var matchesDate = true;
        if (rowStartDate && rowEndDate) {
            if (dateFromValue && rowEndDate < dateFromValue) {
                matchesDate = false;
            }
            if (dateToValue && rowStartDate > dateToValue) {
                matchesDate = false;
            }
        }

        return (!searchValue || rowSearch.indexOf(searchValue) !== -1)
            && (!statusValue || rowStatus === statusValue)
            && (!invoiceDayValue || rowInvoiceDay === invoiceDayValue)
            && (!modeValue || rowMode === modeValue)
            && matchesDate;
    });

    [searchInput, statusFilter, invoiceDayFilter, modeFilter, dateFromFilter, dateToFilter].forEach(function (element) {
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
