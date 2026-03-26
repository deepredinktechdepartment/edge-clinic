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
                <x-card-today-month title="Appointments" :today="$appointments['today']" :month="$appointments['month']" :todayRoute="route('admin.appointments.report', [
                    'from_date' => $today->toDateString(),
                    'to_date' => $today->toDateString(),
                ])"
                    :monthRoute="route('admin.appointments.report', [
                        'from_date' => $monthStart->toDateString(),
                        'to_date' => $monthEnd->toDateString(),
                    ])" />

                {{-- PAYMENTS --}}
                <x-card-today-month title="Payments" :today="'₹ ' . number_format($payments['today'], 2)" :month="'₹ ' . number_format($payments['month'], 2)" :todayRoute="route('admin.payment.report', [
                    'from_date' => $today->toDateString(),
                    'to_date' => $today->toDateString(),
                ])"
                    :monthRoute="route('admin.payment.report', [
                        'from_date' => $monthStart->toDateString(),
                        'to_date' => $monthEnd->toDateString(),
                    ])" />
                @if(in_array(auth()->user()->role, [1,3]))
                {{-- DOCTORS --}}
                <x-dashboard-card title="Doctors" :count="$doctors_count" route="{{ route('admin.doctors') }}" />
                @endif
                {{-- PATIENTS --}}
                <x-dashboard-card title="Patients" :count="$patients_count" route="{{ route('patients.index') }}" />

            </div>
        </div>
    </div>

    {{--
    @if(in_array(auth()->user()->role, [1,3]))
    <div class="row">

        <!-- Local Doctors -->
        <div class="col-md-6">
            <div class="d-flex justify-content-between align-items-center mt-4 mb-3">

                <h4 class="mb-0">
                    Local Database Doctors
                    ({{ $localDoctors->count() }})
                </h4>

                <div>
                    <span class="badge bg-success me-2">
                        Synced:
                        {{ $localDoctors->filter(fn($d) => $mocdocDoctors->firstWhere('drkey', $d->drKey))->count() }}
                    </span>

                    <span class="badge bg-warning">
                        Local Only:
                        {{ $localDoctors->filter(fn($d) => !$mocdocDoctors->firstWhere('drkey', $d->drKey))->count() }}
                    </span>
                </div>

            </div>
            <div class="card shadow-sm mt-3">
                <div class="card-body p-0">

                    <table class="table table-bordered table-striped mb-0 align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start">Doctor</th>
                                <th>Image</th>
                                <th>Qualification</th>
                                <th>Experience</th>
                                <th>Speciality</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>

                            @forelse($localDoctors as $doctor)
                                @php
                                    $apiDoctor = $mocdocDoctors->firstWhere('drkey', $doctor->drKey);
                                @endphp

                                <tr>

                                    <td class="text-start fw-bold">
                                        {{ $doctor->name }}
                                        <small class="text-muted">
                                            ({{ $doctor->drKey }})
                                        </small>
                                    </td>


                                    <td>
                                        @if ($doctor->photo)
                                            <span class="text-success fw-bold">✅ Yes</span>
                                        @else
                                            <span class="text-danger fw-bold">❌ No</span>
                                        @endif
                                    </td>


                                    <td>
                                        @if ($doctor->qualification)
                                            <span class="text-success fw-bold">✅ Yes</span>
                                        @else
                                            <span class="text-danger fw-bold">❌ No</span>
                                        @endif
                                    </td>


                                    <td>
                                        @if (!empty($doctor->experience))
                                            <span class="text-success fw-bold">✅ Yes</span>
                                        @else
                                            <span class="text-danger fw-bold">❌ No</span>
                                        @endif
                                    </td>


                                    <td>
                                        @if ($doctor->department_id)
                                            <span class="text-success fw-bold">✅ Yes</span>
                                        @else
                                            <span class="text-danger fw-bold">❌ No</span>
                                        @endif
                                    </td>


                                    <td>
                                        @if ($apiDoctor)
                                            <span class="badge bg-success">Synced</span>
                                        @else
                                            <span class="badge bg-warning">Local Only</span>
                                        @endif
                                    </td>

                                </tr>

                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        No doctors found
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>
                    </table>

                </div>
            </div>

        </div>

        <!-- MocDoc Doctors -->
        <div class="col-md-6">

            <div class="row align-items-center mt-4 mb-3">

                <div class="col-sm-6">
                    <h4 class="mb-0">
                        MocDoc Doctors
                        (<span id="mocdocCount">
                            {{ $mocdocDoctors->count() }}
                        </span>)
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

            <div id="syncResult" class="mb-2"></div>

            <div class="card shadow-sm">
                <div id="mocdocDoctorsContainer">

                    @if ($mocdocDoctors->count())
                        @foreach ($mocdocDoctors as $doc)
                            @php
                                $existsLocal = $localDoctors->firstWhere('drKey', $doc['drkey']);
                            @endphp

                            <div class="border-bottom py-2 px-2 d-flex justify-content-between">

                                <span>
                                    {{ $doc['name'] ?? '' }}
                                    ({{ $doc['drkey'] ?? '' }})
                                </span>

                                @if ($existsLocal)
                                    <span class="badge bg-success">✔ In Local</span>
                                @else
                                    <span class="badge bg-danger">✖ Not in Local</span>
                                @endif

                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center py-2">
                            Click Refresh to load MocDoc doctors
                        </p>
                    @endif

                </div>
            </div>

        </div>

    </div>
    @endif
    --}}


@endsection

@push('scripts')
    <script>
        document.getElementById('refreshDoctorsBtn').addEventListener('click', function() {

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

                    location.reload(); // reload to show updated cached data

                })
                .finally(() => {
                    btn.disabled = false;
                });
        });

        document.getElementById('syncDoctorsBtn').addEventListener('click', function() {

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
