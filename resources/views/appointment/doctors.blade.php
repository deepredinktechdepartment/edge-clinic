@extends('layouts.bookapp')

@section('title', 'Doctors')

@section('content')

<section class="pt-5 pb-5">
    <div class="container">
        <div class="row g-sm-5 gy-4">

            @foreach($doctors as $doc)
                <div class="col-sm-6">
                    <div class="doctor-card h-100">
                        <div class="row align-items-center">

                            {{-- IMAGE --}}
                            <div class="col-sm-6 order-sm-0 order-1">
                                <div class="avatar-wrap">
                                    <img class="avatar"
                                         src="{{ \GeneralFunctions::doctorImage($doc->photo, '') }}"
                                         alt="{{ $doc->name }}">
                                </div>
                            </div>

                            {{-- BUTTONS --}}
                            <div class="col-sm-6 order-sm-0 order-3">
                                <div class="doctor-actions mt-sm-0 mt-3">

                                    {{-- Profile modal --}}
                                    <button
                                        class="btn btn-profile mb-2 open-profile w-100"
                                        data-id="{{ $doc->id }}"
                                        data-drkey="{{ $doc->drKey }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#profileModal">
                                        View Profile
                                    </button>

                                    {{-- Appointment modal --}}
                                    <button
                                        class="btn btn-book open-appointment w-100"
                                        data-id="{{ $doc->id }}"
                                        data-drkey="{{ $doc->drKey }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#appointmentModal">
                                        Book an Appointment
                                    </button>

                                </div>
                            </div>

                            {{-- DETAILS --}}
                            <div class="col-sm-12 order-sm-0 order-2">
                                <div class="doctor-info mt-3">
                                    <h4 class="dr-name mb-1">{{ $doc->name }}</h4>
                                    <p class="dr-qualification small mb-0">{{ $doc->qualification }}</p>
                                    <p class="dr-designation">{{ $doc->designation }}</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    </div>
</section>


{{-- Profile Modal --}}
<div class="modal fade" id="profileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header p-0 border-bottom-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <!-- Loaded by AJAX -->
            </div>

        </div>
    </div>
</div>

{{-- Appointment Modal --}}
<div class="modal fade" id="appointmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <!-- Loaded by AJAX -->
                <div id="calendarLoader"></div>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')


<script>
/* ------------------------------------------------------
   Load profile content via AJAX
-------------------------------------------------------*/
$(document).on('click', '.open-profile', function () {
    let id = $(this).data('id');

    $('#profileModal .modal-body').html("<p class='text-center p-4'>Loading...</p>");

    $.get("{{ url('/doctor/profile') }}/" + id, function (data) {
        $('#profileModal .modal-body').html(data);
    });
});


/* ------------------------------------------------------
   Load appointment form via AJAX
-------------------------------------------------------*/

$(document).on('click', '.open-appointment', function () {
    let id = $(this).data('id');
    let drKey = $(this).data('drkey');

    $('#appointmentModal .modal-body').html("<p class='text-center p-4'>Loading...</p>");

    $.get("{{ url('/doctor/appointment') }}/" + id, function (data) {

       
        $('#appointmentModal .modal-body').html(data);

    
initializeAppointmentModalSliders();

 // Initialize the appointment JS for this content
        initAppointmentModal();

        // Load calendar API
        
    });
});


/* ------------------------------------------------------
   Initialize Slick Sliders
-------------------------------------------------------*/
function initializeAppointmentModalSliders() {

    // DATE SLIDER
    $('.dr-appo-date-slider').not('.slick-initialized').slick({
        slidesToShow: 3,
        slidesToScroll: 1,
        arrows: true,
        dots: false,
        infinite: false, // 🔴 disable loop
        centerMode: false,
        centerPadding: '0px',

        responsive: [
            {
                breakpoint: 1024, // tablets
                settings: {
                    slidesToShow: 2,
                    arrows: true,
                    infinite: false
                }
            },
            {
                breakpoint: 576, // mobile
                settings: {
                    slidesToShow: 2,
                    arrows: true,
                    infinite: false
                }
            },
            {
                breakpoint: 380, // small mobile
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    arrows: true,
                    infinite: false
                }
            }
        ]
    });


    // TIME SLOTS SLIDER
    $('.dr-appo-time-slots-slider').not('.slick-initialized').slick({
        slidesToShow: 6,
        slidesToScroll: 1,
        arrows: true,
        dots: false,
        infinite: false, // 🔴 disable loop
        centerMode: false,
        centerPadding: '0px',

        responsive: [
            {
                breakpoint: 1024, // tablets
                settings: {
                    slidesToShow: 4,
                    centerMode: false,
                    infinite: false
                }
            },
            {
                breakpoint: 576, // mobile
                settings: {
                    slidesToShow: 2,
                    centerMode: false,
                    infinite: false
                }
            },
            {
                breakpoint: 380, // small mobile
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    centerMode: false,
                    infinite: false
                }
            }
        ]
    });
}





</script>

@endpush
