@extends('template_v1')

@section('content')

<div class="tt-posts">
    <div class="d-flex justify-content-between tt-wrap mb-3">
        <div class="p-2 bd-highlight">
            <h5 class="mb-0 pb-0">{{ $pageTitle ?? '' }}</h5>
        </div>
    </div>
</div>

<div class="t-job-sheet container-fluid g-0">
    <div class="pt-3">
        <div class="row align-items-stretch">

            {{-- APPOINTMENTS --}}
            <x-card-today-month
                title="Appointments"
                :today="$appointments['today']"
                :month="$appointments['month']"
                :todayRoute="route('admin.appointments.report', [
                    'from_date' => $today->toDateString(),
                    'to_date' => $today->toDateString()
                ])"
                :monthRoute="route('admin.appointments.report', [
                    'from_date' => $monthStart->toDateString(),
                    'to_date' => $monthEnd->toDateString()
                ])"
            />

            {{-- PAYMENTS --}}
            <x-card-today-month
                title="Payments"
                :today="'₹ '.number_format($payments['today'], 2)"
                :month="'₹ '.number_format($payments['month'], 2)"
                :todayRoute="route('admin.payment.report', [
                    'from_date' => $today->toDateString(),
                    'to_date' => $today->toDateString()
                ])"
                :monthRoute="route('admin.payment.report', [
                    'from_date' => $monthStart->toDateString(),
                    'to_date' => $monthEnd->toDateString()
                ])"
            />

            {{-- DOCTORS --}}
            <x-dashboard-card
                title="Doctors"
                :count="$doctors_count"
                route="{{ route('admin.doctors') }}"
            />

            {{-- PATIENTS --}}
            <x-dashboard-card
                title="Patients"
                :count="$patients_count"
                route="{{ route('patients.index') }}"
            />

        </div>
    </div>
</div>



@endsection
