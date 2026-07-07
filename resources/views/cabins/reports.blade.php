@extends('template_v1')

@section('content')
@include('cabins.partials.styles')

@php
    $occupancySegments = [];
    $occupancyCursor = 0;
    foreach ($bookingTypeSplit as $segment) {
        $segmentPercent = $splitTotal > 0 ? round(($segment['value'] / $splitTotal) * 100, 2) : 0;
        $occupancySegments[] = $segment['color'] . ' ' . $occupancyCursor . '% ' . ($occupancyCursor + $segmentPercent) . '%';
        $occupancyCursor += $segmentPercent;
    }

    $revenueSegments = [];
    $revenueCursor = 0;
    foreach ($revenueTypeBreakdown as $segment) {
        $segmentPercent = $revenueTypeTotal > 0 ? round(($segment['value'] / $revenueTypeTotal) * 100, 2) : 0;
        $revenueSegments[] = $segment['color'] . ' ' . $revenueCursor . '% ' . ($revenueCursor + $segmentPercent) . '%';
        $revenueCursor += $segmentPercent;
    }

    $reportRoute = route('admin.cabins.reports');
    $occupancyResetUrl = route('admin.cabins.reports', [
        'tab' => 'occupancy',
        'rev_from' => $filters['rev_from'],
        'rev_to' => $filters['rev_to'],
        'rev_doctor_id' => $filters['rev_doctor_id'],
        'rev_status' => $filters['rev_status'],
    ]);
    $revenueResetUrl = route('admin.cabins.reports', [
        'tab' => 'revenue',
        'occ_from' => $filters['occ_from'],
        'occ_to' => $filters['occ_to'],
        'occ_cabin_id' => $filters['occ_cabin_id'],
        'occ_cabin_type' => $filters['occ_cabin_type'],
    ]);
@endphp

<div class="cabin-shell">
    @include('cabins.partials.page-header', [
        'title' => 'Reports',
        'subtitle' => 'Occupancy and revenue reports for cabin management.',
        'actions' => [
            ['url' => route('admin.cabins.dashboard'), 'label' => 'Dashboard', 'icon' => 'bi bi-grid', 'class' => 'btn-outline-secondary'],
            ['url' => route('admin.cabins.settings'), 'label' => 'Settings', 'icon' => 'bi bi-sliders', 'class' => 'btn-outline-secondary'],
        ],
    ])

    <div class="cabin-panel">
        <div class="panel-head cabin-report-head">
            <div class="cabin-report-subtitle mb-0">Choose occupancy report or revenue report.</div>
            <div class="cabin-view-switch cabin-report-tabs">
                <a href="{{ route('admin.cabins.reports', array_merge(request()->query(), ['tab' => 'occupancy'])) }}" class="btn btn-outline-secondary btn-sm {{ $activeTab === 'occupancy' ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-line"></i>
                    Occupancy Report
                </a>
                <a href="{{ route('admin.cabins.reports', array_merge(request()->query(), ['tab' => 'revenue'])) }}" class="btn btn-outline-secondary btn-sm {{ $activeTab === 'revenue' ? 'active' : '' }}">
                    <i class="bi bi-cash-stack"></i>
                    Revenue Report
                </a>
            </div>
        </div>

        @if($activeTab === 'occupancy')
            <div class="panel-body">
                <div class="cabin-report-toolbar mb-4">
                    <form method="GET" action="{{ $reportRoute }}" class="cabin-report-filter-form">
                        <input type="hidden" name="tab" value="occupancy">
                        <input type="hidden" name="rev_from" value="{{ $filters['rev_from'] }}">
                        <input type="hidden" name="rev_to" value="{{ $filters['rev_to'] }}">
                        <input type="hidden" name="rev_doctor_id" value="{{ $filters['rev_doctor_id'] }}">
                        <input type="hidden" name="rev_status" value="{{ $filters['rev_status'] }}">

                        <div class="cabin-filterbar cabin-report-filterbar">
                            <div class="cabin-filter-field">
                                <label class="cabin-filter-label" for="occ_from">From Date</label>
                                <input type="date" id="occ_from" name="occ_from" class="cabin-filter-input" value="{{ $filters['occ_from'] }}">
                            </div>
                            <div class="cabin-filter-field">
                                <label class="cabin-filter-label" for="occ_to">To Date</label>
                                <input type="date" id="occ_to" name="occ_to" class="cabin-filter-input" value="{{ $filters['occ_to'] }}">
                            </div>
                            <div class="cabin-filter-field">
                                <label class="cabin-filter-label" for="occ_cabin_id">Cabin</label>
                                <select id="occ_cabin_id" name="occ_cabin_id" class="cabin-filter-select">
                                    <option value="">All cabins</option>
                                    @foreach($filterCabins as $filterCabin)
                                        <option value="{{ $filterCabin->id }}" {{ (string) $filters['occ_cabin_id'] === (string) $filterCabin->id ? 'selected' : '' }}>
                                            {{ $filterCabin->cabin_code }} - {{ $filterCabin->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="cabin-filter-field">
                                <label class="cabin-filter-label" for="occ_cabin_type">Cabin Type</label>
                                <select id="occ_cabin_type" name="occ_cabin_type" class="cabin-filter-select">
                                    <option value="">All types</option>
                                    <option value="standard" {{ $filters['occ_cabin_type'] === 'standard' ? 'selected' : '' }}>Standard</option>
                                    <option value="premium" {{ $filters['occ_cabin_type'] === 'premium' ? 'selected' : '' }}>Premium</option>
                                    <option value="procedure" {{ $filters['occ_cabin_type'] === 'procedure' ? 'selected' : '' }}>Procedure</option>
                                    <option value="other" {{ $filters['occ_cabin_type'] === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="cabin-filter-field cabin-filter-actions">
                                <label class="cabin-filter-label">&nbsp;</label>
                                <button type="submit" class="btn btn-brand btn-sm">Apply Filter</button>
                                <a href="{{ $occupancyResetUrl }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="cabin-report-metrics mb-4">
                    <div class="cabin-stat">
                        <div class="value">{{ number_format($avgOccupancy, 1) }}%</div>
                        <div class="label">Avg Occupancy</div>
                    </div>
                    <div class="cabin-stat">
                        <div class="value">{{ number_format($totalOccupiedHours, 2) }}</div>
                        <div class="label">Hours Occupied</div>
                    </div>
                    <div class="cabin-stat">
                        <div class="value">{{ number_format($idleHours, 2) }}</div>
                        <div class="label">Idle Hours</div>
                    </div>
                    <div class="cabin-stat">
                        <div class="value">{{ $bestUtilisedCabin->cabin_code ?? '-' }}</div>
                        <div class="label">Best Utilised Cabin</div>
                    </div>
                </div>

                <div class="cabin-split">
                    <div class="cabin-panel">
                        <div class="panel-head">
                            <div>
                                <h5 class="mb-1">Cabin Utilisation</h5>
                                <div class="text-muted">Filtered occupancy for {{ $occRangeLabel }}.</div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="cabin-report-bar-list">
                                @forelse($cabinSummaries as $summary)
                                    @php
                                        $barColor = $summary->utilisation_percent >= 85 ? '#059669' : ($summary->utilisation_percent >= 55 ? '#216AAE' : ($summary->utilisation_percent > 0 ? '#D97706' : '#D5DDE8'));
                                    @endphp
                                    <div class="cabin-report-bar-row">
                                        <div class="cabin-report-bar-label">{{ $summary->cabin_code }} - {{ $summary->name }}</div>
                                        <div class="cabin-report-bar-track">
                                            <div class="cabin-report-bar-fill" style="width: {{ $summary->utilisation_percent }}%; background: {{ $barColor }};"></div>
                                        </div>
                                        <div class="cabin-report-bar-value">{{ number_format($summary->utilisation_percent, 1) }}%</div>
                                    </div>
                                @empty
                                    <div class="empty-note">No cabin utilisation data found for the selected filters.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-4">
                        <div class="cabin-panel">
                            <div class="panel-head">
                                <div>
                                    <h5 class="mb-1">Peak Booking Hours</h5>
                                    <div class="text-muted">Busiest hourly slots within the filtered date range.</div>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="cabin-report-bar-list">
                                    @forelse($peakBookingHours as $slot)
                                        @php
                                            $slotWidth = $peakMax > 0 ? round(($slot['count'] / $peakMax) * 100, 2) : 0;
                                            $slotColor = $slotWidth >= 85 ? '#DC2626' : ($slotWidth >= 60 ? '#D97706' : '#216AAE');
                                        @endphp
                                        <div class="cabin-report-bar-row">
                                            <div class="cabin-report-bar-label">{{ $slot['label'] }}</div>
                                            <div class="cabin-report-bar-track">
                                                <div class="cabin-report-bar-fill" style="width: {{ $slotWidth }}%; background: {{ $slotColor }};"></div>
                                            </div>
                                            <div class="cabin-report-bar-value">{{ $slot['count'] }}</div>
                                        </div>
                                    @empty
                                        <div class="empty-note">No booking-hour data found for this date range.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="cabin-panel">
                            <div class="panel-head">
                                <div>
                                    <h5 class="mb-1">Occupancy Split</h5>
                                    <div class="text-muted">Share of subscription, hourly, and idle hours in the filtered range.</div>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="cabin-report-donut-wrap">
                                    <div class="cabin-report-donut" style="background: conic-gradient({{ implode(',', $occupancySegments) }});"></div>
                                    <div class="cabin-report-legend">
                                        @foreach($bookingTypeSplit as $segment)
                                            @php
                                                $segmentPercent = $splitTotal > 0 ? round(($segment['value'] / $splitTotal) * 100, 1) : 0;
                                            @endphp
                                            <div class="cabin-report-legend-row">
                                                <span class="cabin-report-legend-dot" style="background: {{ $segment['color'] }};"></span>
                                                <span>{{ $segment['label'] }} - {{ number_format($segment['value'], 2) }} hrs ({{ $segmentPercent }}%)</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="cabin-panel">
                            <div class="panel-head">
                                <div>
                                    <h5 class="mb-1">Cabin Summary</h5>
                                    <div class="text-muted">Quick occupancy and revenue snapshot for the current occupancy filter.</div>
                                </div>
                            </div>
                            <div class="panel-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Cabin</th>
                                                <th>Hours</th>
                                                <th>Util</th>
                                                <th>Type</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($cabinSummaries as $summary)
                                                <tr>
                                                    <td>{{ $summary->cabin_code }}</td>
                                                    <td>{{ number_format($summary->occupied_hours, 2) }}</td>
                                                    <td><strong>{{ number_format($summary->utilisation_percent, 1) }}%</strong></td>
                                                    <td>{{ ucfirst($summary->usage_type) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">No summary data available.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="panel-body">
                <div class="cabin-report-toolbar mb-4">
                    <form method="GET" action="{{ $reportRoute }}" class="cabin-report-filter-form">
                        <input type="hidden" name="tab" value="revenue">
                        <input type="hidden" name="occ_from" value="{{ $filters['occ_from'] }}">
                        <input type="hidden" name="occ_to" value="{{ $filters['occ_to'] }}">
                        <input type="hidden" name="occ_cabin_id" value="{{ $filters['occ_cabin_id'] }}">
                        <input type="hidden" name="occ_cabin_type" value="{{ $filters['occ_cabin_type'] }}">

                        <div class="cabin-filterbar cabin-report-filterbar">
                            <div class="cabin-filter-field">
                                <label class="cabin-filter-label" for="rev_from">From Date</label>
                                <input type="date" id="rev_from" name="rev_from" class="cabin-filter-input" value="{{ $filters['rev_from'] }}">
                            </div>
                            <div class="cabin-filter-field">
                                <label class="cabin-filter-label" for="rev_to">To Date</label>
                                <input type="date" id="rev_to" name="rev_to" class="cabin-filter-input" value="{{ $filters['rev_to'] }}">
                            </div>
                            <div class="cabin-filter-field">
                                <label class="cabin-filter-label" for="rev_doctor_id">Doctor</label>
                                <select id="rev_doctor_id" name="rev_doctor_id" class="cabin-filter-select">
                                    <option value="">All doctors</option>
                                    @foreach($filterDoctors as $filterDoctor)
                                        <option value="{{ $filterDoctor->id }}" {{ (string) $filters['rev_doctor_id'] === (string) $filterDoctor->id ? 'selected' : '' }}>
                                            {{ $filterDoctor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="cabin-filter-field">
                                <label class="cabin-filter-label" for="rev_status">Invoice Status</label>
                                <select id="rev_status" name="rev_status" class="cabin-filter-select">
                                    <option value="">All statuses</option>
                                    <option value="draft" {{ $filters['rev_status'] === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="sent" {{ $filters['rev_status'] === 'sent' ? 'selected' : '' }}>Sent</option>
                                    <option value="paid" {{ $filters['rev_status'] === 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="overdue" {{ $filters['rev_status'] === 'overdue' ? 'selected' : '' }}>Overdue</option>
                                </select>
                            </div>
                            <div class="cabin-filter-field cabin-filter-actions">
                                <label class="cabin-filter-label">&nbsp;</label>
                                <button type="submit" class="btn btn-brand btn-sm">Apply Filter</button>
                                <a href="{{ $revenueResetUrl }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="cabin-report-metrics mb-4">
                    <div class="cabin-stat">
                        <div class="value">Rs {{ number_format($totalInvoiced, 2) }}</div>
                        <div class="label">Total Invoiced</div>
                    </div>
                    <div class="cabin-stat">
                        <div class="value">Rs {{ number_format($collectedAmount, 2) }}</div>
                        <div class="label">Collected</div>
                    </div>
                    <div class="cabin-stat">
                        <div class="value">Rs {{ number_format($outstandingAmount, 2) }}</div>
                        <div class="label">Outstanding</div>
                    </div>
                    <div class="cabin-stat">
                        <div class="value">Rs {{ number_format($overdueAmount, 2) }}</div>
                        <div class="label">Overdue</div>
                    </div>
                </div>

                <div class="cabin-split">
                    <div class="d-flex flex-column gap-4">
                        <div class="cabin-panel">
                            <div class="panel-head">
                                <div>
                                    <h5 class="mb-1">Doctor-wise Revenue</h5>
                                    <div class="text-muted">Filtered invoice totals grouped by doctor.</div>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="cabin-report-bar-list">
                                    @forelse($doctorRevenue as $row)
                                        @php
                                            $width = $topDoctorRevenue > 0 ? round(($row->invoiced / $topDoctorRevenue) * 100, 2) : 0;
                                            $color = $row->due > 0 ? '#D97706' : '#059669';
                                        @endphp
                                        <div class="cabin-report-bar-row">
                                            <div class="cabin-report-bar-label">{{ $row->doctor_name }}</div>
                                            <div class="cabin-report-bar-track">
                                                <div class="cabin-report-bar-fill" style="width: {{ $width }}%; background: {{ $color }};"></div>
                                            </div>
                                            <div class="cabin-report-bar-value">Rs {{ number_format($row->invoiced, 2) }}</div>
                                        </div>
                                    @empty
                                        <div class="empty-note">No revenue data found for the selected filters.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="cabin-panel">
                            <div class="panel-head">
                                <div>
                                    <h5 class="mb-1">Monthly Trend</h5>
                                    <div class="text-muted">Six-month invoice trend ending with the selected report period.</div>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="cabin-report-trend">
                                    @foreach($monthlyTrend as $point)
                                        @php
                                            $height = $trendMax > 0 ? max(round(($point['total'] / $trendMax) * 100, 2), 4) : 4;
                                        @endphp
                                        <div class="cabin-report-trend-col">
                                            <div class="cabin-report-trend-value">Rs {{ number_format($point['total'], 0) }}</div>
                                            <div class="cabin-report-trend-bar" style="height: {{ $height }}%; background: {{ $loop->last ? 'linear-gradient(135deg, #216aae 0%, #10314f 100%)' : '#216aae' }};"></div>
                                            <div class="cabin-report-trend-label">{{ $point['label'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-4">
                        <div class="cabin-panel">
                            <div class="panel-head">
                                <div>
                                    <h5 class="mb-1">Outstanding by Doctor</h5>
                                    <div class="text-muted">Filtered invoiced, paid, and due amounts.</div>
                                </div>
                            </div>
                            <div class="panel-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Doctor</th>
                                                <th>Invoiced</th>
                                                <th>Paid</th>
                                                <th>Due</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($doctorRevenue as $row)
                                                <tr>
                                                    <td>{{ $row->doctor_name }}</td>
                                                    <td>Rs {{ number_format($row->invoiced, 2) }}</td>
                                                    <td>Rs {{ number_format($row->paid, 2) }}</td>
                                                    <td><strong>Rs {{ number_format($row->due, 2) }}</strong></td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">No outstanding data available.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="cabin-panel">
                            <div class="panel-head">
                                <div>
                                    <h5 class="mb-1">Revenue by Type</h5>
                                    <div class="text-muted">Filtered split between monthly subscriptions and hourly booking invoices.</div>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="cabin-report-donut-wrap">
                                    <div class="cabin-report-donut" style="background: conic-gradient({{ implode(',', $revenueSegments) }});"></div>
                                    <div class="cabin-report-legend">
                                        @foreach($revenueTypeBreakdown as $segment)
                                            @php
                                                $segmentPercent = $revenueTypeTotal > 0 ? round(($segment['value'] / $revenueTypeTotal) * 100, 1) : 0;
                                            @endphp
                                            <div class="cabin-report-legend-row">
                                                <span class="cabin-report-legend-dot" style="background: {{ $segment['color'] }};"></span>
                                                <span>{{ $segment['label'] }} - Rs {{ number_format($segment['value'], 2) }} ({{ $segmentPercent }}%)</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="cabin-panel">
                            <div class="panel-head">
                                <div>
                                    <h5 class="mb-1">Subscriptions in Range</h5>
                                    <div class="text-muted">Monthly allocations falling inside the current revenue filter window.</div>
                                </div>
                            </div>
                            <div class="panel-body">
                                @forelse($revenueSubscriptions->take(5) as $subscription)
                                    <div class="booking-invoice-meta-box mb-3">
                                        <div class="mini-label">{{ $subscription->cabin->cabin_code ?? '-' }}</div>
                                        <div class="mini-value">{{ $subscription->doctor->name ?? '-' }}</div>
                                        <div class="text-muted mt-2">{{ optional($subscription->start_date)->format('d M Y') }} - {{ optional($subscription->end_date)->format('d M Y') }}</div>
                                        <div class="text-muted">{{ substr($subscription->subscription_start_time ?: '09:00:00', 0, 5) }} - {{ substr($subscription->subscription_end_time ?: '21:00:00', 0, 5) }}</div>
                                        <div class="text-muted">Rs {{ number_format((float) $subscription->total_amount, 2) }}</div>
                                    </div>
                                @empty
                                    <div class="empty-note">No subscription data available for the selected filters.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
