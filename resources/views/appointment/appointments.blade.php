@extends('layouts.bookapp')

@section('title', 'Doctors')

@section('content')



<section class="banner-sec p-0">
    <div class="banner-slider">
        <div class="banner-item">
            <div class="banner-img" style="background: url('./assets/img/banner-1.jpg') center center no-repeat;">
            </div>
        </div>
        <div class="banner-item">
            <div class="banner-img" style="background: url('./assets/img/banner-2.jpg') center center no-repeat;"></div>
        </div>
        <div class="banner-item">
            <div class="banner-img" style="background: url('./assets/img/banner-3.jpg') center center no-repeat;"></div>
        </div>
    </div>
    <div class="banner-content">
        <div class="container">
            <div class="row">
                <div class="col-sm-8">
                    <div class="ps-sm-0 ps-3">
                        <h1 class="text-white mb-4">Consult Top Doctors. Across Specialties. At Edge Clinic.</h1>
                        <a href="#book-appointment" class="btn-brand">Book Appointment</a>
                    </div>
                </div>
            </div>
            <!--<div class="row mt-5 pt-5">-->
            <!--    <div class="col-sm-12">-->
            <!--        <div class="serach-form-wrapper">-->
            <!--            <form name="searchform" action="#" method="POST">-->
            <!--                <div class="search-box">-->
            <!--                    <input type="text" class="form-control search-input" placeholder="Search For Doctors & Specialities...">-->
            <!--                    <button class="btn search-button">-->
            <!--                        <i class="fas fa-search"></i>-->
            <!--                    </button>-->
            <!--                </div>-->
            <!--            </form>-->
            <!--        </div>-->
            <!--    </div>-->
            <!--</div>-->
        </div>
    </div>
</section>


<section class="pt-5 pb-5">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <h2>Experience Healthcare, the Edge Way.</h2>
                <p>At Edge Clinic, we bring together top doctors and modern facilities to ensure patients get the best of healthcare and experience. A new-age approach to healthcare, with premium healthcare facilities across a wide range of specialties and super-specialties, steps away from Raidurg Metro Station and easy access from HITEC City, Madhapur, Gachibowli, Financial District and Manikonda.</p>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-sm-4">
                <div class="card icon-left">
                    <div class="card-body">
                        <img src="./assets/img/icon_1.png" class="img-fluid">
                        <p class="mb-0">Verified Doctors</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card icon-left">
                    <div class="card-body">
                        <img src="./assets/img/icon-2.png" class="img-fluid">
                        <p class="mb-0">Seamless Booking</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card icon-left">
                    <div class="card-body">
                        <img src="./assets/img/icon_3.png" class="img-fluid">
                        <p class="mb-0">Walk-in Friendly</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>







<section class="pt-5 pb-5 bg-light" id="book-appointment">
    <div class="container">
        <div class="row mb-5">
            <div class="col-sm-12">
                <div class="serach-form-wrapper">
                    <form name="searchform" action="#" method="POST">
                        <div class="search-box">
                            <input type="text" class="form-control search-input" placeholder="Search For Doctors & Specialities...">
                            <button class="btn search-button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
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

<section class="pt-5 pb-5">
    <div class="container">
        <h2 class="mb-4 pb-sm-3 text-center">Specialties We’re Known For</h2>
        <div class="row speciality-cards">
            <div class="col-sm-3">
                <div class="card">
                    <div class="card-body text-center">
                        <img src="./assets/img/specialities/General-Medicine.png" alt="General Medicine" class="img-fluid mb-3">
                        <p class="mb-0">General Medicine</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card">
                    <div class="card-body text-center">
                        <img src="./assets/img/specialities/Gynecology.png" alt="Gynecology" class="img-fluid mb-3">
                        <p class="mb-0">Gynecology</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card">
                    <div class="card-body text-center">
                        <img src="./assets/img/specialities/Pediatrics.png" alt="Pediatrics" class="img-fluid mb-3">
                        <p class="mb-0">Pediatrics</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card">
                    <div class="card-body text-center">
                        <img src="./assets/img/specialities/Orthopedics.png" alt="Orthopedics" class="img-fluid mb-3">
                        <p class="mb-0">Orthopedics</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card">
                    <div class="card-body text-center">
                        <img src="./assets/img/specialities/Dermatology.png" alt="Dermatology" class="img-fluid mb-3">
                        <p class="mb-0">Dermatology</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card">
                    <div class="card-body text-center">
                        <img src="./assets/img/specialities/Physiotherapy.png" alt="Physiotherapy" class="img-fluid mb-3">
                        <p class="mb-0">Physiotherapy</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card">
                    <div class="card-body text-center">
                        <img src="./assets/img/specialities/Chiropractic.png" alt="Chiropractic" class="img-fluid mb-3">
                        <p class="mb-0">Chiropractic</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card">
                    <div class="card-body text-center">
                        <img src="./assets/img/specialities/Homeopathy.png" alt="Homeopathy" class="img-fluid mb-3">
                        <p class="mb-0">Homeopathy</p>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</section>

<section class="bg-gradient pt-5 pb-0">
    <div class="container">
        <div class="row justify-content-center align-items-end">
            <div class="col-sm-5 pb-sm-5 pb-3">
                <div class="request-callback-form-wrapper">
                <h2 class="text-white text-center mb-4 pb-2">Request a Callback</h2>
                    <form id="patient-form" method="POST" action="#">
                        
                            <div class="mb-4">
                                <input type="text" name="name" class="form-control" placeholder="Name*" required="">
                            </div>
                        
                            <div class="mb-4">
                            
                                <div class="position-relative">
                                    <input type="tel" id="phone" class="form-control pe-5" placeholder="Enter phone number" required="">
                            
                                    <button type="button" id="sendOtpBtn" class="btn btn-outline-primary btn-sm position-absolute top-50 end-0 translate-middle-y me-2">
                                        Send OTP
                                    </button>
                                </div>
                                
                                <div class="position-relative mt-4">
                                    <input type="text" id="otp" class="form-control pe-5" placeholder="Enter OTP" maxlength="6">
                            
                                    <button type="button" id="verifyOtpBtn" class="btn btn-outline-success btn-sm position-absolute top-50 end-0 translate-middle-y me-2">
                                        Verify
                                    </button>
                                </div>
                            
                                <small id="otpStatus" class="text-muted d-block mt-1"></small>
                            </div>
                            <button type="submit" id="submitBtn" class="btn btn-book  w-100">Confirm Appointment</button>
                    </form>
                </div>
            </div>
            <div class="col-sm-3"></div>
            <div class="col-sm-4">
                <div class="text-end">
                    <img src="./assets/img/female-researcher.png" class="img-fluid">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="pt-5 pb-5">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <h2 class="text-center mb-4">What’s New at Edge Clinic?</h2>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-sm-3">
                <div class="fullimg-card">
                    <img src="./assets/img/New-Doctors-Onboarded.png" class="img-fluid">
                    <p class="mb-0">New Doctors Onboarded</p>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="fullimg-card">
                    <img src="./assets/img/New-Locations-Launched.png" class="img-fluid">
                    <p class="mb-0">New Locations Launched</p>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="fullimg-card">
                    <img src="./assets/img/Patient-Testimonials.png" class="img-fluid">
                    <p class="mb-0">Patient Testimonials</p>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="fullimg-card">
                    <img src="./assets/img/Upcoming-Free-Camps-Events.png" class="img-fluid">
                    <p class="mb-0">Upcoming Free Camps / Events</p>
                </div>
            </div>
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
        infinite: false, // ðŸ”´ disable loop
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
        infinite: false, // ðŸ”´ disable loop
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


 $('.banner-slider').slick({
    slidesToShow: 1,
    slidesToScroll: 1,
    autoplay: true,
    autoplaySpeed: 3000,
    pauseOnHover: false,
    dots: false,
    fade: true,
    arrows: false,
    infinite: true,
    centerMode: false,
    responsive: [{
        breakpoint: 480,
        settings: {
            slidesToShow: 1,
            slidesToScroll: 1,
            dots: false,
            arrows: false,
        }
    }]
});


</script>

@endpush
