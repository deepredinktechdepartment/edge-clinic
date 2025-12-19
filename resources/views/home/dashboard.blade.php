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
          <x-dashboard-card
                title="Today's Appointments"
                :count="$appointments_count"
                route="{{ route('admin.appointments.report') }}"
            />
            <x-dashboard-card
                title="Today's Payments"
                :count="'₹ ' . number_format($today_collection, 2)"
                route="{{ route('admin.payment.report') }}"
            />
            <!-- <x-dashboard-card
                title="Specializations"
                :count="$departments_count"
                route="{{ route('admin.specializations') }}"
            /> -->

            <x-dashboard-card
                title="Doctors"
                :count="$doctors_count"
                route="{{ route('admin.doctors') }}"
            />

            <x-dashboard-card
                title="Patients"
                :count="$patients_count"
                route="{{ route('patients.index') }}"
            />





        </div>
    </div>
</div>

@endsection
