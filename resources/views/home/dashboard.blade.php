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
        <div class="row align-items-center mt-4 mb-3">
            <h4 class="mb-0">
            Local Database Doctors
            ({{ $localDoctors->count() }})
        </h4>
        </div>
        <div class="card shadow-sm">
            <div class="" id="localDoctorsContainer" style="">
                @forelse($localDoctors as $doctor)
                    <div class="d-flex justify-content-between border-bottom py-2 px-2">
                        <span>
                            {{ $doctor->name }}
                            ({{ $doctor->drKey }})
                        </span>

                        <span class="doctor-status" data-drkey="{{ $doctor->drKey }}">
                            <span class="badge bg-secondary">Not Checked</span>
                        </span>
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
                (<span id="mocdocCount">0</span>)
            </h4>
        </div>

        <div class="col-sm-6 text-sm-end">

            <button id="refreshDoctorsBtn" class="btn btn-outline-secondary btn-sm me-2">
                Refresh
            </button>

            <button id="syncDoctorsBtn" class="btn btn-primary btn-sm">
                Sync Doctors
            </button>

        </div>

    </div>

    {{-- <div id="syncResult" class="mb-2"></div> --}}

    <div class="card shadow-sm">
        <div id="mocdocDoctorsContainer">
            <p class="text-muted text-center py-2">
                Click Refresh to load MocDoc doctors
            </p>
        </div>
    </div>

</div>

</div>



@endsection

@push('scripts')
<script>

document.getElementById('refreshDoctorsBtn').addEventListener('click', function () {

    let btn = this;
    btn.disabled = true;

    fetch("{{ route('mocdoc.fetchDoctors') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
        }
    })
    .then(res => res.json())
    .then(data => {

        if (data.status !== 'success') {
            alert(data.message);
            return;
        }

        let apiDoctors = data.doctors || [];

        // ✅ UPDATE COUNT
        document.getElementById('mocdocCount').innerText = apiDoctors.length;

        let apiDrKeys = apiDoctors.map(d => d.drkey);

        let mocdocContainer = document.getElementById('mocdocDoctorsContainer');
        mocdocContainer.innerHTML = '';

        apiDoctors.forEach(doc => {

            let existsLocal = document.querySelector(
                `[data-drkey="${doc.drkey}"]`
            );

            let badge = existsLocal
                ? `<span class="badge bg-success">✔ In Local</span>`
                : `<span class="badge bg-danger">✖ Not in Local</span>`;

            mocdocContainer.innerHTML += `
                <div class="d-flex justify-content-between border-bottom py-2 px-2">
                    <span>${doc.name} (${doc.drkey})</span>
                    ${badge}
                </div>
            `;
        });

        // Update local column
        document.querySelectorAll('.doctor-status').forEach(el => {

            let drKey = el.getAttribute('data-drkey');

            if (apiDrKeys.includes(drKey)) {
                el.innerHTML =
                    `<span class="badge bg-success">✔ In MocDoc</span>`;
            } else {
                el.innerHTML =
                    `<span class="badge bg-danger">✖ Not in MocDoc</span>`;
            }

        });

    })
    .finally(() => {
        btn.disabled = false;
    });

});

document.getElementById('syncDoctorsBtn').addEventListener('click', function () {

    let btn = this;
    btn.disabled = true;

    fetch("{{ route('mocdoc.syncDoctors') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
        }
    })
    .then(res => res.json())
    .then(data => {

        if (data.status === 'success') {

            alert(data.message);
            setTimeout(() => location.reload(), 1200);

        } else {
            alert(data.message);
        }
    })
    .finally(() => {
        btn.disabled = false;
    });
});

</script>
@endpush

