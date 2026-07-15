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
        'subtitle' => 'All cabin usage invoices sent or pending for doctors.',
        'actions' => [
            ['url' => route('admin.cabins.invoices.create'), 'label' => 'New Invoice', 'icon' => 'bi bi-plus-circle', 'class' => 'btn-brand'],
        ],
    ])

    <div class="cabin-panel">
        <div class="panel-body">
            <div class="cabin-filterbar mb-4">
                <div class="cabin-filter-field">
                    <label class="cabin-filter-label" for="invoice_search">Search</label>
                    <input type="text" id="invoice_search" class="cabin-filter-input" placeholder="Search invoice, doctor, cabin or amount">
                </div>
                <div class="cabin-filter-field">
                    <label class="cabin-filter-label" for="invoice_type_filter">Billing Type</label>
                    <select id="invoice_type_filter" class="cabin-filter-select">
                        <option value="">All Types</option>
                        <option value="hourly">Hourly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>
                <div class="cabin-filter-field">
                    <label class="cabin-filter-label" for="invoice_status_filter">Status</label>
                    <select id="invoice_status_filter" class="cabin-filter-select">
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="sent">Sent</option>
                        <option value="paid">Paid</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </div>
                <div class="cabin-filter-field">
                    <label class="cabin-filter-label" for="invoice_sent_filter">Sent Via</label>
                    <select id="invoice_sent_filter" class="cabin-filter-select">
                        <option value="">All Delivery</option>
                        <option value="email">Email</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="both">Both</option>
                        <option value="not_sent">Not Sent</option>
                    </select>
                </div>
                <div class="cabin-filter-field">
                    <label class="cabin-filter-label" for="invoice_date_from">From Date</label>
                    <input type="date" id="invoice_date_from" class="cabin-filter-input" value="{{ $currentMonthStart }}">
                </div>
                <div class="cabin-filter-field">
                    <label class="cabin-filter-label" for="invoice_date_to">To Date</label>
                    <input type="date" id="invoice_date_to" class="cabin-filter-input" value="{{ $currentMonthEnd }}">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle" id="cabin-invoices-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Invoice No</th>
                            <th>Doctor</th>
                            <th>Type</th>
                            <th>Period</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Sent Via</th>
                            <th width="110">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                            <tr
                                data-search="{{ strtolower(trim(($invoice->invoice_number ?? '') . ' ' . ($invoice->doctor->name ?? '') . ' ' . ($invoice->cabin->cabin_code ?? '') . ' ' . number_format((float) $invoice->total_amount, 2))) }}"
                                data-type="{{ strtolower((string) $invoice->billing_type) }}"
                                data-status="{{ strtolower((string) $invoice->status) }}"
                                data-sent="{{ strtolower((string) ($invoice->sent_via ?: 'not_sent')) }}"
                                data-invoice-date="{{ optional($invoice->invoice_date)->format('Y-m-d') }}">
                                <td>
                                    <a href="{{ route('admin.cabins.invoices.show', $invoice->id) }}" class="cabin-name-link">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('admin.cabins.doctors.show', $invoice->doctor_id) }}" class="cabin-name-link">
                                        {{ $invoice->doctor->name ?? '-' }}
                                    </a>
                                </td>
                                <td>{{ ucfirst($invoice->billing_type) }}</td>
                                <td>{{ optional($invoice->period_start)->format('d M Y') }} - {{ optional($invoice->period_end)->format('d M Y') }}</td>
                                <td>Rs {{ number_format((float) $invoice->total_amount, 2) }}</td>
                                <td>{{ optional($invoice->due_date)->format('d M Y') }}</td>
                                <td><span class="badge text-bg-secondary">{{ ucfirst($invoice->status) }}</span></td>
                                <td>{{ $invoice->sent_via ? ucfirst($invoice->sent_via) : '-' }}</td>
                                <td><a href="{{ route('admin.cabins.invoices.show', $invoice->id) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
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
    if (typeof $ === 'undefined' || !$('#cabin-invoices-table').length) {
        return;
    }

    var table = $('#cabin-invoices-table').DataTable({
        pageLength: 50,
        lengthMenu: [[50, 100, 150, -1], [50, 100, 150, 'All']],
        responsive: true,
        dom: 'rt<"d-flex justify-content-between align-items-center mt-3"lip>'
    });

    var searchInput = document.getElementById('invoice_search');
    var typeFilter = document.getElementById('invoice_type_filter');
    var statusFilter = document.getElementById('invoice_status_filter');
    var sentFilter = document.getElementById('invoice_sent_filter');
    var dateFromFilter = document.getElementById('invoice_date_from');
    var dateToFilter = document.getElementById('invoice_date_to');

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
        var sentValue = (sentFilter.value || '').trim().toLowerCase();
        var dateFromValue = (dateFromFilter.value || '').trim();
        var dateToValue = (dateToFilter.value || '').trim();

        var rowSearch = row.getAttribute('data-search') || '';
        var rowType = row.getAttribute('data-type') || '';
        var rowStatus = row.getAttribute('data-status') || '';
        var rowSent = row.getAttribute('data-sent') || '';
        var rowDate = row.getAttribute('data-invoice-date') || '';

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
            && (!sentValue || rowSent === sentValue)
            && matchesDate;
    });

    [searchInput, typeFilter, statusFilter, sentFilter, dateFromFilter, dateToFilter].forEach(function (element) {
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
