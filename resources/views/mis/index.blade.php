@extends('template_v1')

@section('content')
<style>
    .mis-shell { margin: 0; }
    .mis-filter { border:0; border-radius:12px; }
    .mis-filter label { font-size:13px; font-weight:600; color:#4a5a6a; margin-bottom:5px; }
    .mis-filter .form-control,.mis-filter .form-select { min-height:38px; }
    .mis-card { border:0; border-radius:12px; box-shadow:0 5px 20px rgba(17,49,76,.08); padding:18px; min-height:120px; }
    .mis-card-label { color:#647d8f; font-weight:700; font-size:13px; } .mis-card-value { font-size:24px; font-weight:800; color:#183852; margin-top:8px; }
    .mis-card.success { border-left:5px solid #27ae60; }.mis-card.primary { border-left:5px solid #2585e6; }.mis-card.violet { border-left:5px solid #8358d7; }.mis-card.warning { border-left:5px solid #ed9d1a; }.mis-card.teal { border-left:5px solid #159b8f; }.mis-card.danger { border-left:5px solid #dc5d6f; }.mis-card.indigo { border-left:5px solid #5964d8; }.mis-card.rose { border-left:5px solid #cc4b78; }
    .mis-table-card { border:0; border-radius:12px; overflow:hidden; box-shadow:0 5px 20px rgba(17,49,76,.08); }
    .mis-table-card thead th { background:#edf5f7; color:#31556c; font-size:12px; text-transform:uppercase; letter-spacing:.02em; white-space:nowrap; }
    .mis-table-card td { color:#304a5d; vertical-align:middle; }
    .mis-notice { border:0; background:#edf8fb; color:#326176; border-radius:12px; font-size:13px; }
    .mis-dashboard-panel { border:0; border-radius:12px; box-shadow:0 5px 20px rgba(17,49,76,.08); overflow:hidden; height:100%; }
    .mis-dashboard-panel .card-header { background:#fff; border-bottom:1px solid #edf1f4; font-weight:800; color:#294b64; padding:14px 16px; }
    .mis-trend { min-height:240px; display:flex; align-items:flex-end; gap:10px; padding:22px 18px 12px; overflow-x:auto; background:linear-gradient(to bottom,#fbfdfe,#f4f8fa); }
    .mis-trend-item { min-width:48px; flex:1; display:flex; flex-direction:column; justify-content:flex-end; align-items:center; gap:7px; }
    .mis-trend-value { color:#31556c; font-size:11px; font-weight:800; white-space:nowrap; }.mis-trend-bar { width:28px; min-height:7px; border-radius:7px 7px 2px 2px; background:linear-gradient(#47a3c4,#1d5d87); }.mis-trend-label { font-size:10px; color:#6b8393; white-space:nowrap; }
    .mis-mini-table th { color:#778b98; font-size:11px; text-transform:uppercase; }.mis-mini-table td { font-size:13px; color:#2e4d62; }.mis-badge { background:#eaf4f6; color:#176f78; border-radius:20px; padding:4px 8px; font-weight:700; font-size:11px; }
    .mis-drill-link { color:#176f9d; font-weight:700; text-decoration:none; border-bottom:1px dashed currentColor; }.mis-drill-link:hover { color:#0b526f; }
</style>

<div class="mis-shell my-4">
    <div class="tt-posts">
        <div class="d-flex justify-content-between align-items-center tt-wrap bg-white mb-3 p-2">
            <h5 class="mb-0 pb-0">{{ $pageTitle }}</h5>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-success btn-sm" href="{{ route('admin.mis.excel', array_merge([$report], request()->query())) }}"><i class="fa-solid fa-file-excel"></i> Excel</a>
                <a class="btn btn-outline-danger btn-sm" href="{{ route('admin.mis.pdf', array_merge([$report], request()->query())) }}"><i class="fa-solid fa-file-pdf"></i> PDF</a>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mis-filter mb-4"><div class="card-body p-3">
        <form method="GET" action="{{ route('admin.mis.index', $report) }}" class="row g-2 align-items-end">
            <div class="col-xl-2 col-md-3"><label>From date</label><input class="form-control form-control-sm" type="date" name="from_date" value="{{ $fromDate }}"></div>
            <div class="col-xl-2 col-md-3"><label>To date</label><input class="form-control form-control-sm" type="date" name="to_date" value="{{ $toDate }}"></div>
            <div class="col-xl-2 col-md-3"><label>Doctor</label><select class="form-select form-select-sm" name="doctor"><option value="">All doctors</option>@foreach($doctors as $doctor)<option value="{{ $doctor->id }}" @selected(request('doctor') == $doctor->id)>{{ $doctor->name }}</option>@endforeach</select></div>
            <div class="col-xl-2 col-md-3"><label>Source</label><select class="form-select form-select-sm" name="source_id"><option value="">All sources</option>@foreach($sources as $source)<option value="{{ $source->id }}" @selected(request('source_id') == $source->id)>{{ $source->name }}</option>@endforeach</select></div>
            <div class="col-xl-1 col-md-3"><label>Mode</label><select class="form-select form-select-sm" name="payment_mode"><option value="">All</option><option value="cash" @selected(request('payment_mode') === 'cash')>Cash</option><option value="upi" @selected(request('payment_mode') === 'upi')>UPI</option><option value="online" @selected(request('payment_mode') === 'online')>Online</option></select></div>
            @if($report === 'collection-summary')<div class="col-xl-1 col-md-3"><label>View</label><select class="form-select form-select-sm" name="group_by"><option value="day" @selected(request('group_by', 'day') === 'day')>Day</option><option value="month" @selected(request('group_by') === 'month')>Month</option></select></div>@endif
            <div class="col-xl-auto col-md-4 d-flex align-items-end gap-2"><button type="submit" class="btn btn-brand btn-sm px-3">Go</button><a class="btn btn-outline-secondary btn-sm px-3" href="{{ route('admin.mis.index', ['report' => $report]) }}">Reset</a></div>
        </form>
    </div></div>

    @if(!empty($cards))<div class="row g-3 mb-3">@foreach($cards as $card)<div class="col-xxl-3 col-xl-4 col-md-6"><div class="mis-card {{ $card['tone'] }}"><div class="mis-card-label">{{ $card['label'] }}</div><div class="mis-card-value">{{ $card['value'] }}</div></div></div>@endforeach</div>@endif

    @if($report === 'dashboard')
        @php $maxTrend = max(1, collect($trend)->max('total_collection')); @endphp
        <div class="row g-3 mb-3">
            <div class="col-xl-7"><div class="card mis-dashboard-panel"><div class="card-header d-flex justify-content-between"><span>Day-wise Collection Trend</span><span class="small text-muted fw-normal">{{ $periodLabel }}</span></div><div class="mis-trend">@forelse($trend as $item)<div class="mis-trend-item"><div class="mis-trend-value">{{ $item->total_collection_display }}</div><div class="mis-trend-bar" style="height: {{ max(7, round(($item->total_collection / $maxTrend) * 165)) }}px"></div><div class="mis-trend-label">{{ \Carbon\Carbon::parse($item->period)->format('d M') }}</div></div>@empty<div class="w-100 text-center text-muted py-5">No collections in this period.</div>@endforelse</div></div></div>
            <div class="col-xl-5"><div class="card mis-dashboard-panel"><div class="card-header">Payment Mode Summary</div><div class="card-body p-0"><div class="table-responsive"><table class="table table-sm mis-mini-table mb-0"><thead><tr><th class="ps-3 pt-3">Mode</th><th class="pt-3 text-center">Transactions</th><th class="pt-3 pe-3 text-end">Collection</th></tr></thead><tbody>@forelse($paymentModes as $mode)<tr><td class="ps-3"><span class="mis-badge">{{ strtoupper($mode->name) }}</span></td><td class="text-center">{{ $mode->transactions }}</td><td class="pe-3 text-end fw-bold">{{ $mode->collection_display }}</td></tr>@empty<tr><td colspan="3" class="text-center py-4 text-muted">No received payments.</td></tr>@endforelse</tbody></table></div></div></div></div>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-lg-6"><div class="card mis-dashboard-panel"><div class="card-header">Top Doctors by Collection</div><div class="card-body p-0"><table class="table table-sm mis-mini-table mb-0"><thead><tr><th class="ps-3 pt-3">Doctor</th><th class="pt-3 text-center">Visits</th><th class="pt-3 pe-3 text-end">Collection</th></tr></thead><tbody>@forelse($doctorPerformance as $doctor)<tr><td class="ps-3">{{ $doctor->name }}</td><td class="text-center">{{ $doctor->visits }}</td><td class="pe-3 text-end fw-bold">{{ $doctor->collection_display }}</td></tr>@empty<tr><td colspan="3" class="text-center py-4 text-muted">No doctor collection.</td></tr>@endforelse</tbody></table></div></div></div>
            <div class="col-lg-6"><div class="card mis-dashboard-panel"><div class="card-header">Top Sources by Collection</div><div class="card-body p-0"><table class="table table-sm mis-mini-table mb-0"><thead><tr><th class="ps-3 pt-3">Source</th><th class="pt-3 text-center">Visits</th><th class="pt-3 pe-3 text-end">Collection</th></tr></thead><tbody>@forelse($sourcePerformance as $source)<tr><td class="ps-3">{{ $source->name }}</td><td class="text-center">{{ $source->visits }}</td><td class="pe-3 text-end fw-bold">{{ $source->collection_display }}</td></tr>@empty<tr><td colspan="3" class="text-center py-4 text-muted">No source collection.</td></tr>@endforelse</tbody></table></div></div></div>
        </div>
        <div class="alert mis-notice mb-3"><i class="fa-solid fa-circle-info me-1"></i>{{ $notice }}</div>
    @else
        @php $moneyHeaders = ['Amount', 'Consultation Fee', 'Registration Fee', 'Service Collection', 'Net Collection', 'Collection', 'Discount', 'Billed', 'Confirmed Collection', 'Open Value', 'Actual Collection', 'Pending Value', 'Appointment Collection', 'Total Collection', 'Gross Value', 'Discount Given', 'Net Amount']; $periodKeys = $periodKeys ?? []; $reportDrillHeaders = ['doctor-collection' => ['Doctor'], 'service-reports' => ['Service'], 'source-referral' => ['Source / Referral'], 'patient-visits' => ['Visit Type'], 'appointment-operations' => ['Doctor'], 'payment-closing' => ['Payment Mode'], 'discount-report' => ['Discount Given']]; @endphp
        <div class="d-flex justify-content-between align-items-center mb-2 gap-2 flex-wrap"><div class="small text-muted fw-semibold">{{ $periodLabel }}</div></div>
        <div class="alert mis-notice mb-3"><i class="fa-solid fa-circle-info me-1"></i>{{ $notice }}</div>
        <div class="card mis-table-card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr>@foreach($headers as $header)<th class="px-3 py-3 {{ in_array($header, $moneyHeaders, true) ? 'text-end' : '' }}">{{ $header }}</th>@endforeach</tr></thead><tbody>@forelse($rows as $row)
            @php $periodKey = $periodKeys[$row['Period'] ?? ''] ?? ($row['Period'] ?? ''); $drillValue = $report === 'collection-summary' ? $periodKey : ($row['__drill_key'] ?? ''); $isActiveRow = $report === 'collection-summary' ? request('period') === $periodKey : request('drill_value') === $drillValue; @endphp
            <tr>@foreach($headers as $header)@php $drillDownType = $report === 'collection-summary' ? ['Transactions' => 'transactions', 'Consultation Fee' => 'consultation', 'Registration Fee' => 'registration'][$header] ?? null : (in_array($header, $reportDrillHeaders[$report] ?? [], true) ? $report : null); $isActiveDrillDown = $isActiveRow && request('drill_down') === $drillDownType; $drillParams = $report === 'collection-summary' ? ['drill_down' => $drillDownType, 'period' => $periodKey] : ['drill_down' => $drillDownType, 'drill_value' => $drillValue]; @endphp<td class="px-3 py-3 {{ in_array($header, $moneyHeaders, true) ? 'text-end' : '' }}">@if($drillDownType && $drillValue !== '')<a class="mis-drill-link" href="{{ route('admin.mis.index', array_merge(['report' => $report], request()->except(['drill_down', 'period', 'drill_value']), $isActiveDrillDown ? [] : $drillParams)) }}">{{ $row[$header] ?? '-' }}</a>@else{{ $row[$header] ?? '-' }}@endif</td>@endforeach</tr>
            @if($isActiveRow && !empty($drilldown))
                <tr class="table-light"><td colspan="{{ count($headers) }}" class="p-3"><div class="d-flex justify-content-between align-items-center mb-2"><h6 class="mb-0 fw-bold">{{ $drilldown['title'] }}</h6><a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.mis.index', array_merge(['report' => $report], request()->except(['drill_down', 'period', 'drill_value']))) }}">Close</a></div><div class="table-responsive bg-white border rounded"><table class="table table-sm table-hover mb-0"><thead><tr>@foreach($drilldown['headers'] as $header)<th class="px-3 py-2 {{ in_array($header, $moneyHeaders, true) ? 'text-end' : '' }}">{{ $header }}</th>@endforeach</tr></thead><tbody>@forelse($drilldown['rows'] as $detailRow)<tr>@foreach($drilldown['headers'] as $header)<td class="px-3 py-2 {{ in_array($header, $moneyHeaders, true) ? 'text-end' : '' }}">{{ $detailRow[$header] ?? '-' }}</td>@endforeach</tr>@empty<tr><td colspan="{{ count($drilldown['headers']) }}" class="text-center py-4 text-muted">No matching details found.</td></tr>@endforelse</tbody></table></div></td></tr>
            @endif
        @empty<tr><td colspan="{{ max(1, count($headers)) }}" class="text-center py-5 text-muted">No report data found for the selected filters.</td></tr>@endforelse</tbody></table></div></div></div>
    @endif
</div>
@endsection
