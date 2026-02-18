@extends('layouts.bookapp')

@section('title', 'Book Appointment')

@section('content')
<section class="pt-5 pb-5 bg-light">
    <div class="container">

        <h3 class="mb-4">Book an Appointment</h3>

        <form id="schedule-appointment" action="{{ route('appointments.store') }}" method="POST">
            @csrf

            {{-- Hidden inputs for date and time --}}
            <input type="hidden" name="appointment_date" id="appointment_date">
            <input type="hidden" name="appointment_time" id="appointment_time">

            {{-- STEP 1: DATE + TIME --}}
            <fieldset class="mb-4">
                <div class="schedule-appointment-card shadow-sm rounded p-4 bg-white">
                    <div class="sch-appo-header mb-3">
                        <h5 class="mb-2">Select Date</h5>
                        <div class="dr-appo-date-slider-wrap">
                            <ul class="dr-appo-date-slider mb-0 list-unstyled d-flex gap-2">
                                @for($i = 0; $i < 8; $i++)
                                    <li>
                                        <a href="#" class="{{ $i == 0 ? 'active' : '' }}">
                                            {{ \Carbon\Carbon::today()->addDays($i)->format('D d-m-Y') }}
                                        </a>
                                    </li>
                                @endfor
                            </ul>
                        </div>
                    </div>

                    <div class="sch-appo-content mt-3">
                        <h5 class="mb-2">Select Time Slot</h5>
                        <div class="dr-appo-time-slots-wrap">
                            <ul class="dr-appo-time-slots-slider mb-0 list-unstyled d-flex gap-2 flex-wrap">
                                @foreach(['10:00 AM','10:30 AM','11:00 AM','11:30 AM','12:00 PM','12:30 PM','01:00 PM','01:30 PM','02:00 PM','02:30 PM','03:00 PM','03:30 PM','04:00 PM','04:30 PM','05:00 PM'] as $time)
                                    <li>
                                        <a style="background-color:#f0f0f0!important;" href="#" class="slot-item btn btn-outline-primary btn-sm px-3 mb-2">{{ $time }}</a>
                                    </li>
                                @endforeach
                            </ul>
                            <p class="no-slots-msg text-center text-danger fw-bold mt-2 d-none">
                                No slots available. Please select a different date.
                            </p>
                        </div>
                    </div>
                </div>
            </fieldset>

            {{-- STEP 2: PATIENT INFO + FINAL CONFIRM --}}
            <fieldset class="border p-4 rounded shadow-sm bg-white" id="step2">
                <legend class="fw-bold mb-3">
                    Appointment with {{ $doctor->name }} ({{ $doctor->qualification }})
                </legend>

                <div class="row g-4">
                    {{-- DOCTOR IMAGE --}}
                    <div class="col-md-3 text-center">
                        <img class="img-fluid rounded shadow-sm"
                             src="{{ \GeneralFunctions::doctorImage($doctor->photo) }}"
                             alt="{{ $doctor->name }}">
                    </div>

                    {{-- DETAILS --}}
                    <div class="col-md-9">
                        {{-- APPOINTMENT ALERT --}}
                        <div class="alert alert-warning shadow-sm" id="selected-slot-msg">
                            <i class="fas fa-calendar-check me-2"></i>
                            Appointment is scheduled on <strong id="selected-slot-text"></strong>
                        </div>

                        {{-- LOCATION INFO --}}
                        <div class="alert alert-info shadow-sm">
                            <strong>Appointment location:</strong><br>
                            JV MEDI CLINIC PRIVATE LIMITED<br>
                            Phase 2, Park, HUDA Techno Enclave, HITEC City, Hyderabad
                        </div>

                        {{-- TERMS --}}
                        <div class="alert alert-warning shadow-sm d-flex align-items-center">
                            <input type="checkbox" name="terms_agree" id="appt-terms-agree" class="form-check-input me-2" required>
                            <label for="appt-terms-agree" class="mb-0">
                                I agree to the <a href="{{ url('terms-of-use') }}" target="_new" class="text-decoration-underline">Terms & Conditions</a>
                            </label>
                        </div>

                        {{-- CONFIRM BUTTON --}}
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-book" id="apptconfirm">Confirm Appointment</button>
                        </div>
                    </div>
                </div>
            </fieldset>

            <input type="hidden" name="doctor" value="{{$doctor??''}}" />

        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function(){

    // Initialize Slick sliders
    $('.dr-appo-date-slider').slick({
        slidesToShow: 3,
        slidesToScroll: 1,
        arrows: true,
        centerMode: true,
        centerPadding: '0px',
        responsive: [{ breakpoint: 480, settings: { slidesToShow: 1, arrows: false, dots: true } }]
    });

    $('.dr-appo-time-slots-slider').slick({
        slidesToShow: 6,
        slidesToScroll: 1,
        arrows: true,
        centerMode: true,
        centerPadding: '0px',
        responsive: [{ breakpoint: 480, settings: { slidesToShow: 1, arrows: false, dots: true } }]
    });

    // Default date and time
    let selectedDate = $('.dr-appo-date-slider li a.active').text().trim();
    let selectedTime = null;

    // Set default hidden date (only dd-mm-yyyy)
    let defaultDate = selectedDate.split(' ')[1]; // "09-12-2025"
    $('#appointment_date').val(defaultDate);
    $('#selected-slot-text').text(selectedDate); // initial alert text

    // Handle date selection
    $('.dr-appo-date-slider li a').click(function(e){
        e.preventDefault();
        $('.dr-appo-date-slider li a').removeClass('active');
        $(this).addClass('active');
        selectedDate = $(this).text().trim();
        let dateOnly = selectedDate.split(' ')[1];
        $('#appointment_date').val(dateOnly);

        // Update alert dynamically if time already selected
        if(selectedTime){
            $('#selected-slot-text').text(selectedDate + ' at ' + selectedTime);
        } else {
            $('#selected-slot-text').text(selectedDate);
        }
    });

    // Handle time slot selection
    $('.slot-item').click(function(e){
        e.preventDefault();
        $('.slot-item').removeClass('selected');
        $(this).addClass('selected');
        selectedTime = $(this).text();
        $('#appointment_time').val(selectedTime);

        // Update alert dynamically
        $('#selected-slot-text').text(selectedDate + ' at ' + selectedTime);
    });

    // Form submission validation
    $('#schedule-appointment').submit(function(e){
        if(!selectedTime){
            alert('Please select a time slot.');
            e.preventDefault();
            return false;
        }
        if(!$('#appt-terms-agree').is(':checked')){
            alert('You must agree to the Terms & Conditions.');
            e.preventDefault();
            return false;
        }
    });

});
</script>
@endpush
