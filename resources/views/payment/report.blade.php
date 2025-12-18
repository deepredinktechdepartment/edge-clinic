@extends('template_v1')

@section('content')

<div class="my-4">

    <div class="d-flex justify-content-between p-2 mb-3">
        <h5 class="mb-0">{{ $pageTitle ?? 'Payments Report' }}</h5>

    </div>

    @if(!isset($doctorId))
        <div class="card shadow-sm mb-4">
            <div class="card-body">
               <form action="{{ route('admin.payment.report') }}" method="GET" class="row gy-2 gx-3 align-items-end">

    <!-- Doctor Filter -->
    <div class="col-md-2">
        <label class="form-label">Doctor</label>
        <select name="doctor" class="form-select form-select-sm">
            <option value="">--All--</option>
            @foreach($doctors as $doc)
                <option value="{{ $doc['id'] }}" {{ request('doctor') == $doc['id'] ? 'selected' : '' }}>
                    {{ $doc['name'] }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- From Date -->
<div class="col-md-2">
    <label class="form-label">From</label>
    <input type="date"
           name="from_date"
           class="form-control form-control-sm"
           value="{{ request('from_date', $fromDate ?? now()->toDateString()) }}">
</div>

<!-- To Date -->
<div class="col-md-2">
    <label class="form-label">To</label>
    <input type="date"
           name="to_date"
           class="form-control form-control-sm"
           value="{{ request('to_date', $toDate ?? now()->toDateString()) }}">
</div>

    <!-- Payment Status -->
    <div class="col-md-2">
        <label class="form-label">Payment Status</label>
        <select name="payment_status" class="form-select form-select-sm">
            <option value="">--All--</option>
            <option value="initiated" {{ request('payment_status') == 'initiated' ? 'selected' : '' }}>Initiated</option>
            <option value="success" {{ request('payment_status') == 'success' ? 'selected' : '' }}>Success</option>
            <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Failed</option>
        </select>
    </div>

    <!-- Filter & Export Buttons -->
    <div class="col-md-3 d-flex align-items-end">
        <div class="me-2">
            <button class="btn btn-brand btn-sm">
                Go
            </button>
        </div>
        <div class="me-2">

            <a href="{{ route('admin.payment.report') }}" class="btn btn-danger btn-sm text-white">
                Reset
            </a>
        </div>
        <div class="me-2">
            <a href="{{ route('admin.payment.report.pdf', request()->all()) }}" class="btn btn-brand-blue btn-sm text-white">
                Download pdf
            </a>
        </div>
    </div>

</form>

            </div>
        </div>
    @endif

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <x-dashboard-card title="Successful Payments" :count="$summaryData['success_count']" route="#" color="success"/>
        <x-dashboard-card title="Total Amount (Success)" :count="'₹ ' . number_format($summaryData['success_amount'])" route="#" color="success"/>
        <x-dashboard-card title="Failed Payments" :count="$summaryData['failed_count']" route="#" color="danger"/>
        <x-dashboard-card title="Total Amount (Failed)" :count="'₹ ' . number_format($summaryData['failed_amount'])" route="#" color="danger"/>
    </div>


            @include('payment.table', ['list' =>  $summaryData['successPayments']])


</div>

@endsection
