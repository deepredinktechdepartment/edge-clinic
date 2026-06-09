@extends('template_v1')

@section('content')
@php
    $role = auth()->user()->role;
    $cardRouteBase = array_filter([
        'doctor' => request('doctor'),
        'source_id' => request('source_id'),
        'payment_mode' => request('payment_mode'),
        'type' => request('type'),
    ], fn ($value) => filled($value));
@endphp
<div class="my-4">

    <div class="d-flex justify-content-between p-2 mb-3">
        <h5 class="mb-0">{{ $pageTitle ?? 'Payments Report' }}</h5>
    </div>

    @if(!isset($doctorId))
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('admin.payment.report') }}" method="GET" class="row gy-2 gx-3 align-items-end">

                    @if(in_array($role, [1,3]))
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
                    @endif

                    <div class="col-xxl-1 col-sm-2">
                        <label class="form-label">From</label>
                        <input type="date"
                               name="from_date"
                               class="form-control form-control-sm"
                               value="{{ request('from_date', $fromDate ?? now()->toDateString()) }}">
                    </div>

                    <div class="col-xxl-1 col-sm-2">
                        <label class="form-label">To</label>
                        <input type="date"
                               name="to_date"
                               class="form-control form-control-sm"
                               value="{{ request('to_date', $toDate ?? now()->toDateString()) }}">
                    </div>

                    @if(request('payment_status') != 'failed')
                        <div class="col-md-1">
                            <label class="form-label">Payment Status</label>
                            <select name="payment_status" class="form-select form-select-sm">
                                <option value="">--All--</option>
                                <option value="pending" {{ in_array(request('payment_status'), ['pending', 'initiated'], true) ? 'selected' : '' }}>Pending</option>
                                <option value="success" {{ request('payment_status') == 'success' ? 'selected' : '' }}>Success</option>
                                <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                    @endif

                    <div class="col-md-1">
                        <label class="form-label">Mode</label>
                        <select name="payment_mode" class="form-select form-select-sm">
                            <option value="">--All--</option>
                            <option value="online" {{ request('payment_mode') == 'online' ? 'selected' : '' }}>Online</option>
                            <option value="offline" {{ request('payment_mode') == 'offline' ? 'selected' : '' }}>Offline</option>
                        </select>
                    </div>

                    <div class="col-md-1">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select form-select-sm">
                            <option value="">--All--</option>
                            <option value="appointment" {{ request('type') == 'appointment' ? 'selected' : '' }}>Appointment</option>
                            <option value="service" {{ request('type') == 'service' ? 'selected' : '' }}>Service</option>
                        </select>
                    </div>

                    <div class="col-md-1">
                        <label class="form-label">Source</label>
                        <select name="source_id" class="form-select form-select-sm">
                            <option value="">--All--</option>
                            @foreach($sources as $source)
                                <option value="{{ $source->id }}" {{ (string) request('source_id') === (string) $source->id ? 'selected' : '' }}>
                                    {{ $source->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <div class="me-2">
                            <button class="btn btn-brand btn-sm">Go</button>
                        </div>
                        <div class="me-2">
                            <a href="{{ route('admin.payment.report') }}" class="btn btn-brand btn-sm">Reset</a>
                        </div>
                        <div class="me-2">
                            <a href="{{ route('admin.payment.report.pdf', request()->all()) }}" class="btn btn-brand btn-sm">
                                <i class="fa-solid fa-download" style="color:#fff !important"></i>&nbsp; pdf
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @php
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();
    @endphp

    <div class="row g-3 mb-4">
        <x-card-today-month
            title="Successful Payments"
            :today="$cardData['successful_payments']['today']"
            :month="$cardData['successful_payments']['month']"
            :todayRoute="route('admin.payment.report', array_merge($cardRouteBase, [
                'from_date' => $today,
                'to_date' => $today,
                'payment_status' => 'success'
            ]))"
            :monthRoute="route('admin.payment.report', array_merge($cardRouteBase, [
                'from_date' => $monthStart,
                'to_date' => $monthEnd,
                'payment_status' => 'success'
            ]))"
        />

        <x-card-today-month
            title="Failed Payments"
            :today="$cardData['failed_payments']['today']"
            :month="$cardData['failed_payments']['month']"
            :todayRoute="route('admin.payment.report', array_merge($cardRouteBase, [
                'from_date' => $today,
                'to_date' => $today,
                'payment_status' => 'failed'
            ]))"
            :monthRoute="route('admin.payment.report', array_merge($cardRouteBase, [
                'from_date' => $monthStart,
                'to_date' => $monthEnd,
                'payment_status' => 'failed'
            ]))"
        />

        <x-card-today-month
            title="Total Amount (Success)"
            :today="'Rs '.number_format($cardData['success_amount']['today'], 2)"
            :month="'Rs '.number_format($cardData['success_amount']['month'], 2)"
            :todayRoute="route('admin.payment.report', array_merge($cardRouteBase, [
                'from_date' => $today,
                'to_date' => $today,
                'payment_status' => 'success'
            ]))"
            :monthRoute="route('admin.payment.report', array_merge($cardRouteBase, [
                'from_date' => $monthStart,
                'to_date' => $monthEnd,
                'payment_status' => 'success'
            ]))"
        />

        <x-card-today-month
            title="Total Amount (Failed)"
            :today="'Rs '.number_format($cardData['failed_amount']['today'], 2)"
            :month="'Rs '.number_format($cardData['failed_amount']['month'], 2)"
            :todayRoute="route('admin.payment.report', array_merge($cardRouteBase, [
                'from_date' => $today,
                'to_date' => $today,
                'payment_status' => 'failed'
            ]))"
            :monthRoute="route('admin.payment.report', array_merge($cardRouteBase, [
                'from_date' => $monthStart,
                'to_date' => $monthEnd,
                'payment_status' => 'failed'
            ]))"
        />
    </div>

    @include('payment.table', ['list' => $payments])
</div>

@endsection
