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

<div class="row">

    <!-- Local Doctors -->
    <div class="col-md-6">
        <h4 class="mt-4">
            Local Database Doctors
            ({{ $localDoctors->count() }})
        </h4>

        <div class="card">
            <div class="card-body" style="max-height:300px; overflow:auto;">
                @forelse($localDoctors as $doctor)
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>
                            {{ $doctor->name }}
                            ({{ $doctor->drKey }})
                        </span>

                        @if($doctor->exists_in_mocdoc)
                            <span class="badge bg-success">✔ In MocDoc</span>
                        @else
                            <span class="badge bg-danger">✖ Not in MocDoc</span>
                        @endif
                    </div>
                @empty
                    <p>No doctors found in Local DB</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- MocDoc Doctors -->
    <div class="col-md-6">

    <div class="row align-items-center mt-4 mb-3">

        <div class="col-sm-6">
            <h4 class="mb-0">
                MocDoc Doctors
                ({{ $mocdocDoctors->count() }})
            </h4>
        </div>

        <div class="col-sm-6 text-sm-end text-start mt-2 mt-sm-0">
            {{-- <button id="syncDoctorsBtn" class="btn btn-primary btn-sm">
                Sync Doctors from MocDoc
            </button> --}}
        </div>

    </div>

    <div id="syncResult" class="mb-2"></div>

    <div class="card shadow-sm">
        <div class="card-body p-2" style="max-height:350px; overflow-y:auto;">

            @forelse($mocdocDoctors as $doctor)
                <div class="d-flex justify-content-between align-items-center border-bottom py-2 px-2">

                    <span>
                        {{ $doctor['name'] ?? '' }}
                        ({{ $doctor['drkey'] ?? '' }})
                    </span>

                    @if($doctor['exists_in_local'])
                        <span class="badge bg-success">✔ In Local</span>
                    @else
                        <span class="badge bg-danger">✖ Not in Local</span>
                    @endif

                </div>
            @empty
                <p class="text-muted text-center py-2 mb-0">
                    No doctors found from MocDoc
                </p>
            @endforelse

        </div>
    </div>

</div>

</div>



@endsection

@push('scripts')
<script>
document.getElementById('syncDoctorsBtn').addEventListener('click', function () {

    fetch("{{ route('mocdoc.syncDoctors') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
        }
    })
    .then(response => response.json())
    .then(data => {

        if (data.status === 'success') {
            document.getElementById('syncResult').innerHTML =
                `<div class="alert alert-success">
                    ${data.message}<br>
                    Inserted: ${data.inserted}<br>
                    Updated: ${data.updated}
                </div>`;

            setTimeout(() => location.reload(), 1500);

        } else {
            document.getElementById('syncResult').innerHTML =
                `<div class="alert alert-danger">${data.message}</div>`;
        }
    });

});
</script>
@endpush

