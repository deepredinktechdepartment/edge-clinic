@extends('template_v1')

@section('content')

<div class="my-4">



    <div class="tt-posts">
   	<div class="d-flex justify-content-between tt-wrap mb-3">
	  	<div class="p-2 bd-highlight"><h5 class="mb-0 pb-0">{{$pageTitle??''}}</h5></div>
 {{-- New action link: Book an appointment --}}
            <a href="{{ url('manualappointment/patientcreate?action=appointment') }}"  title="Book an appointment">
                <i class="fa-solid fa-calendar-plus"></i> Book Appointment
            </a>
	</div>
</div>

    @if(!isset($doctorId))
        <div class="card shadow-sm mb-4">
            <div class="card-body">
               <form action="{{ route('admin.appointments.report') }}" method="GET" class="row gy-2 gx-3 align-items-end">

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

            <a href="{{ route('admin.appointments.report') }}" class="btn btn-danger btn-sm text-white">
                Reset
            </a>
        </div>
        <div class="me-2">
            <a href="{{ route('admin.appointments.report.pdf', request()->all()) }}" class="btn btn-brand-blue btn-sm text-white">
                Download pdf
            </a>
        </div>
        <div>
            <a href="{{ route('admin.appointments.report.print', request()->all()) }}" class="btn btn-brand-blue btn-sm text-white">
                Print
            </a>
        </div>
    </div>

</form>

            </div>
        </div>
    @endif

    <!-- Summary Cards -->


    <div class="row g-3 mb-4">
    <x-dashboard-card
        title="Total Appointments"
        :count="$summaryData['total_appointments']"
        color="primary" route="#" />

    <x-dashboard-card
        title="Paid Appointments"
        :count="$summaryData['paid_appointments']"
        color="success" route="#" />

    <x-dashboard-card
        title="Pending / Failed"
        :count="$summaryData['failed_appointments']"
        color="danger" route="#" />

    <x-dashboard-card
        title="Total Revenue"
        :count="'₹ ' . number_format($summaryData['total_revenue'], 2)"
        color="success" route="#" />
</div>






            @include('admin.appointments.table', ['list' =>  $summaryData['appointments']])


</div>

@endsection