<div class="row">
    <div class="col-md-4">
        <img src="{{ GeneralFunctions::doctorImage($doctor->photo, '') }}" class="img-fluid rounded">
    </div>

    <div class="col-md-8">
        <h4>{!! $doctor->name !!}</h4>
       <p>
    {!! $doctor->qualification !!}<br>
    <i class="fa fa-user-md me-1"></i> {!! $doctor->designation !!}
</p>
                <p>{!! $doctor->bio ?? '' !!}</p>
            {{-- Appointment modal --}}
                <button
                    class="btn btn-book open-appointment"
                    data-id="{{ $doctor->id }}"
                    data-drkey="{{ $doctor->drKey }}"
                    data-bs-toggle="modal"
                    data-bs-target="#appointmentModal">
                    Book an Appointment
                </button>
    </div>
</div>
