@extends('template_v1')

@section('content')
@include('cabins.partials.styles')

<div class="cabin-shell">
    @include('cabins.partials.page-header', [
        'title' => $pageTitle,
        'subtitle' => 'Manage each cabin, its type, rates, facilities, and availability details.',
        'actions' => [
            ['url' => route('admin.cabins.dashboard'), 'label' => 'Dashboard', 'icon' => 'bi bi-grid', 'class' => 'btn-outline-secondary'],
            ['url' => route('admin.cabins.facilities.index'), 'label' => 'Facilities', 'icon' => 'bi bi-ui-checks', 'class' => 'btn-outline-secondary'],
            ['url' => $addlink, 'label' => 'Add Cabin', 'icon' => 'bi bi-plus-circle', 'class' => 'btn-brand'],
        ],
    ])

    <div class="cabin-panel">
        <div class="panel-head">
            <div>
                <h5 class="mb-1">Cabin Master</h5>
                <div class="text-muted">Default grid view now follows the same preview-card pattern.</div>
            </div>
            <div class="cabin-view-switch" role="tablist" aria-label="Cabin master view switch">
                <button type="button" class="btn btn-outline-secondary btn-sm active" data-cabin-view-btn="grid">
                    <i class="bi bi-grid-3x3-gap"></i> Grid View
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-cabin-view-btn="table">
                    <i class="bi bi-table"></i> Table View
                </button>
            </div>
        </div>
        <div class="panel-body">
            <div class="cabin-filterbar mb-4">
                <div class="cabin-filter-field">
                    <label class="cabin-filter-label" for="cabin_search">Search</label>
                    <input type="text" id="cabin_search" class="cabin-filter-input" placeholder="Search cabin code, name or floor">
                </div>
                <div class="cabin-filter-field">
                    <label class="cabin-filter-label" for="cabin_status_filter">Status</label>
                    <select id="cabin_status_filter" class="cabin-filter-select">
                        <option value="">All Status</option>
                        <option value="available">Available</option>
                        <option value="occupied">Occupied</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="booked">Booked</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="cabin-filter-field">
                    <label class="cabin-filter-label" for="cabin_mode_filter">Booking Mode</label>
                    <select id="cabin_mode_filter" class="cabin-filter-select">
                        <option value="">All Modes</option>
                        <option value="hourly">Hourly</option>
                        <option value="monthly">Monthly</option>
                        <option value="both">Both</option>
                    </select>
                </div>
                <div class="cabin-filter-field">
                    <label class="cabin-filter-label" for="cabin_type_filter">Cabin Type</label>
                    <select id="cabin_type_filter" class="cabin-filter-select">
                        <option value="">All Types</option>
                        @foreach($cabins->pluck('cabin_type')->filter()->unique()->sort()->values() as $cabinType)
                            <option value="{{ strtolower((string) $cabinType) }}">{{ ucfirst((string) $cabinType) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="cabin-master-view active" data-cabin-view="grid">
                <div class="cabin-master-grid">
                    @foreach($cabins as $cabin)
                        @php
                            $statusClass = match ($cabin->status) {
                                'maintenance' => 'status-maintenance',
                                'occupied' => 'status-monthly',
                                'booked' => 'status-booked',
                                default => 'status-available',
                            };
                            $statusLabel = ucfirst($cabin->status);
                            $typeClass = match ($cabin->cabin_type) {
                                'premium' => 'cabin-type-premium',
                                'procedure' => 'cabin-type-procedure',
                                'other' => 'cabin-type-other',
                                default => 'cabin-type-standard',
                            };
                            $typeLabel = $cabin->cabin_type === 'consultation' ? 'Standard' : ucfirst($cabin->cabin_type);
                        @endphp
                        <div
                            class="cabin-card cabin-master-card {{ $typeClass }}"
                            data-search="{{ strtolower(trim(($cabin->cabin_code ?? '') . ' ' . ($cabin->name ?? '') . ' ' . ($cabin->floor_name ?? '') . ' ' . ($cabin->room_number ?? ''))) }}"
                            data-status="{{ strtolower((string) $cabin->status) }}"
                            data-mode="{{ strtolower((string) $cabin->booking_mode) }}"
                            data-type="{{ strtolower((string) $cabin->cabin_type) }}">
                            <div class="status-bar {{ $statusClass }}"></div>
                            <div class="body">
                                <div class="cabin-master-head">
                                    <div class="cabin-master-copy">
                                        <div class="cabin-code">{{ $cabin->cabin_code }}</div>
                                        <h5 class="cabin-master-title">{{ $cabin->name }}</h5>
                                        <div class="cabin-meta"><span class="cabin-type-chip {{ $typeClass }}">{{ $typeLabel }}</span> {{ $cabin->floor_name ?: 'Floor not set' }}</div>
                                    </div>
                                    <span class="status-chip {{ $statusClass }}">{{ $statusLabel }}</span>
                                </div>

                                <div class="cabin-master-meta">
                                    <div class="cabin-master-meta-row">
                                        <span>Mode</span>
                                        <strong>{{ ucfirst($cabin->booking_mode) }}</strong>
                                    </div>
                                    <div class="cabin-master-meta-row">
                                        <span>Hourly</span>
                                        <strong>Rs {{ number_format((float) ($cabin->hourly_rate ?? 0), 2) }}</strong>
                                    </div>
                                    <div class="cabin-master-meta-row">
                                        <span>Monthly</span>
                                        <strong>Rs {{ number_format((float) ($cabin->monthly_rate ?? 0), 2) }}</strong>
                                    </div>
                                    <div class="cabin-master-meta-row">
                                        <span>Usage</span>
                                        <strong>{{ $cabin->bookings_count }} bookings | {{ $cabin->subscriptions_count }} subs</strong>
                                    </div>
                                </div>

                                @if($cabin->facilities->isNotEmpty())
                                    <div class="cabin-master-facilities">
                                        <div class="small text-dark">{{ $cabin->facilities->take(2)->pluck('name')->implode(', ') }}</div>
                                        @if($cabin->facilities->count() > 2)
                                            <div class="small text-muted">+{{ $cabin->facilities->count() - 2 }} more facilities</div>
                                        @endif
                                    </div>
                                @endif

                                <div class="cabin-master-actions">
                                    <a href="{{ route('admin.cabins.show', $cabin->id) }}" class="btn btn-outline-secondary btn-sm">View</a>
                                    <a href="{{ route('admin.cabins.edit', $cabin->id) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                                    <form action="{{ route('admin.cabins.destroy', $cabin->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this cabin?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-secondary btn-sm">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="cabin-master-view" data-cabin-view="table">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="cabin-master-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Cabin</th>
                                <th>Type</th>
                                <th>Floor</th>
                                <th>Mode</th>
                                <th>Rates</th>
                                <th>Facilities</th>
                                <th>Status</th>
                                <th>Usage</th>
                                <th width="160">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cabins as $cabin)
                                <tr
                                    data-search="{{ strtolower(trim(($cabin->cabin_code ?? '') . ' ' . ($cabin->name ?? '') . ' ' . ($cabin->floor_name ?? '') . ' ' . ($cabin->room_number ?? ''))) }}"
                                    data-status="{{ strtolower((string) $cabin->status) }}"
                                    data-mode="{{ strtolower((string) $cabin->booking_mode) }}"
                                    data-type="{{ strtolower((string) $cabin->cabin_type) }}">
                                    <td>
                                        <a href="{{ route('admin.cabins.show', $cabin->id) }}" class="cabin-name-link">
                                            <div class="fw-semibold text-dark">{{ $cabin->cabin_code }}</div>
                                            <div class="text-muted small">{{ $cabin->name }}</div>
                                        </a>
                                    </td>
                                    <td>{{ ucfirst($cabin->cabin_type) }}</td>
                                    <td>{{ $cabin->floor_name ?: '-' }}</td>
                                    <td>{{ ucfirst($cabin->booking_mode) }}</td>
                                    <td>
                                        <div>Hourly: Rs {{ number_format((float) ($cabin->hourly_rate ?? 0), 2) }}</div>
                                        <div class="small text-muted">Monthly: Rs {{ number_format((float) ($cabin->monthly_rate ?? 0), 2) }}</div>
                                    </td>
                                    <td>
                                        @if($cabin->facilities->isNotEmpty())
                                            <div class="small text-dark">{{ $cabin->facilities->take(2)->pluck('name')->implode(', ') }}</div>
                                            @if($cabin->facilities->count() > 2)
                                                <div class="small text-muted">+{{ $cabin->facilities->count() - 2 }} more</div>
                                            @endif
                                        @else
                                            <span class="text-muted small">No facilities</span>
                                        @endif
                                    </td>
                                    <td><span class="badge text-bg-secondary">{{ ucfirst($cabin->status) }}</span></td>
                                    <td>
                                        <div>{{ $cabin->bookings_count }} bookings</div>
                                        <div class="small text-muted">{{ $cabin->subscriptions_count }} subscriptions</div>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.cabins.show', $cabin->id) }}" class="text-primary me-2">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.cabins.edit', $cabin->id) }}" class="text-warning me-2">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.cabins.destroy', $cabin->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this cabin?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="border-0 bg-transparent p-0">
                                                <i class="fa fa-trash text-danger"></i>
                                            </button>
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var buttons = document.querySelectorAll('[data-cabin-view-btn]');
    var views = document.querySelectorAll('[data-cabin-view]');
    var tableInitialized = false;
    var table = null;
    var searchInput = document.getElementById('cabin_search');
    var statusFilter = document.getElementById('cabin_status_filter');
    var modeFilter = document.getElementById('cabin_mode_filter');
    var typeFilter = document.getElementById('cabin_type_filter');

    function initCabinTableIfNeeded() {
        if (tableInitialized || typeof $ === 'undefined' || !$('#cabin-master-table').length) {
            return;
        }

        table = $('#cabin-master-table').DataTable({
            "lengthMenu": [
                [50, 100, 150, -1],
                [50, 100, 150, "All"]
            ],
            "responsive": true,
            dom: 'rt<"d-flex justify-content-between align-items-center mt-3"lip>'
        });

        tableInitialized = true;
    }

    function rowMatches(row) {
        var searchValue = (searchInput.value || '').trim().toLowerCase();
        var statusValue = (statusFilter.value || '').trim().toLowerCase();
        var modeValue = (modeFilter.value || '').trim().toLowerCase();
        var typeValue = (typeFilter.value || '').trim().toLowerCase();

        var rowSearch = row.getAttribute('data-search') || '';
        var rowStatus = row.getAttribute('data-status') || '';
        var rowMode = row.getAttribute('data-mode') || '';
        var rowType = row.getAttribute('data-type') || '';

        return (!searchValue || rowSearch.indexOf(searchValue) !== -1)
            && (!statusValue || rowStatus === statusValue)
            && (!modeValue || rowMode === modeValue)
            && (!typeValue || rowType === typeValue);
    }

    function applyGridFilters() {
        document.querySelectorAll('.cabin-master-card').forEach(function (card) {
            card.style.display = rowMatches(card) ? '' : 'none';
        });
    }

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (!table || settings.nTable !== table.table().node()) {
            return true;
        }

        var row = table.row(dataIndex).node();
        return row ? rowMatches(row) : true;
    });

    function applyAllFilters() {
        applyGridFilters();
        if (tableInitialized && table) {
            table.draw();
        }
    }

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            var target = button.getAttribute('data-cabin-view-btn');

            buttons.forEach(function (item) {
                item.classList.toggle('active', item === button);
            });

            views.forEach(function (view) {
                view.classList.toggle('active', view.getAttribute('data-cabin-view') === target);
            });

            if (target === 'table') {
                initCabinTableIfNeeded();
                if (table) {
                    table.draw();
                }
            }
        });
    });

    [searchInput, statusFilter, modeFilter, typeFilter].forEach(function (element) {
        element.addEventListener('input', applyAllFilters);
        element.addEventListener('change', applyAllFilters);
    });

    applyGridFilters();
});
</script>
@endsection
