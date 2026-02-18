@extends('layouts.bookapp')

@section('title', 'For Patients')

@section('content')


@push('styles')

.fade-in {
    animation: fadeIn 0.2s ease-in-out;
}

.fade-out {
    animation: fadeOut 0.15s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.97); }
    to   { opacity: 1; transform: scale(1); }
}

@keyframes fadeOut {
    from { opacity: 1; transform: scale(1); }
    to   { opacity: 0; transform: scale(0.97); }
}
.form-msg{
    padding:12px 15px;
    border-radius:6px;
    font-weight:600;
    font-size:14px;
    border:1px solid transparent;
}
.form-msg.success{
    background:#eafff2;
    color:#11884a;
    border-color:#66d19e;
}
.form-msg.error{
    background:#ffecec;
    color:#b00020;
    border-color:#ff9a9a;
}

@endpush

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
                <div class="col-lg-8 col-sm-10">
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
        <div class="row mt-sm-4 mt-2 mob-cards-row">
            <div class="col-sm-4">
                <div class="card icon-left">
                    <div class="card-body">
                        <img src="./assets/img/icon_1.png" class="img-fluid">
                        <p class="mb-0">Qualified Doctors</p>
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
                        <p class="mb-0">Easy Accessible</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>






<section class="pt-5 pb-5 bg-light" id="book-appointment">
    <div class="container">

        {{-- SEARCH --}}
        <div class="row mb-5">
            <div class="col-sm-12">
                <div class="serach-form-wrapper">
                    <form onsubmit="return false;">
                        <div class="search-box">
                            <input
                                type="text"
                                id="doctorSearch"
                                class="form-control search-input"
                                placeholder="Search for doctors & specialities...">
                            <button class="btn search-button" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- DOCTORS LIST --}}
        <div class="row doctors-slider g-sm-5 gy-4" id="doctorList">

            @foreach($doctors as $doc)
                <div class="col-sm-6 doctor-item"
                     data-name="{{ strtolower($doc->name) }}"
                     data-designation="{{ strtolower($doc->designation) }}">

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

                                    <button
                                        class="btn btn-profile mb-2 open-profile w-100"
                                        data-id="{{ $doc->id }}"
                                        data-drkey="{{ $doc->drKey }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#profileModal">
                                        View Profile
                                    </button>

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
        <div class="row">
            
            {{-- NO RESULT --}}
            <div class="col-12 text-center d-none" id="noDoctor">
                <div class="p-4 bg-white rounded shadow-sm text-muted">
                    <i class="fas fa-user-md fa-2x mb-2"></i>
                    <div><strong>No doctors found</strong></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="pt-5 pb-5">
    <div class="container">
        <h2 class="mb-4 pb-sm-3 text-center">Specialties We’re <span class="d-sm-inline d-block">Known For</span></h2>
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
            <div class="col-lg-5 col-sm-6 pb-sm-5 pb-3">
                <div class="request-callback-form-wrapper">
                <h2 class="text-white text-center mb-4 pb-2">Request a Callback</h2>
                    <form id="patient-form" method="POST" action="#">

                            <div class="mb-4">
                                <input type="text" name="name" class="form-control" placeholder="Name*" required="">
                            </div>

                            <div class="mb-4">

                                <div class="position-relative">
                                    <input type="tel" id="phone" class="form-control pe-5" placeholder="Enter phone number" required>

                                    <button type="button" id="sendOtpBtn" class="btn btn-inputright">
                                        Send OTP
                                    </button>
                                </div>
                                <!-- <input type="hidden" name="phone" id="clean_phone">
                                <input type="hidden" name="country_code" id="country_code"> -->
                                <div class="position-relative mt-4">
                                    <input type="text" id="otp" class="form-control pe-5" placeholder="Enter OTP" maxlength="6">

                                    <button type="button" id="verifyOtpBtn" class="btn btn-inputright">
                                        Verify
                                    </button>
                                </div>
                            </div>
                            <small id="otpStatus" class="text-muted d-block mt-1"></small>

                            <input type="hidden" id="phone_number">
                            <input type="hidden" name="phone" id="clean_phone">
                            <input type="hidden" name="country_code" id="country_code">

                            <button type="submit" id="submitBtn" class="btn btn-brand btn-pink w-100">
                                Submit
                            </button>
                            
                            <div id="formMsg" class="form-msg mt-3 text-center d-none"></div>

                    </form>
                </div>
            </div>
            <div class="col-lg-3 col-sm-2"></div>
            <div class="col-sm-4">
                <div class="text-end">
                    <img src="./assets/img/indian-doctor.png" class="img-fluid">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="pt-5 pb-5">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <h2 class="text-center mb-4">What’s New at <span class="d-sm-inline d-block">Edge Clinic?</span></h2>
            </div>
        </div>
        <div class="row whatsnew-slider mt-4">
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

            <div class="modal-body px-0">
                <!-- Loaded by AJAX -->
                <div id="calendarLoader"></div>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<!-- intl-tel-input JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>

<!-- intl-tel-input utils JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"></script>

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

jQuery(document).ready(function ($) {

  function initSpecialitySlider() {
    if ($(window).width() <= 480) {

      if (!$('.speciality-cards').hasClass('slick-initialized')) {
        $('.speciality-cards').slick({
          slidesToShow: 2,
          slidesToScroll: 2,
          autoplay: true,
          autoplaySpeed: 3000,
          pauseOnHover: false,
          dots: true,
          fade: false, // fade doesn't work well with multiple slides
          arrows: false,
          infinite: true,
          centerMode: false
        });
      }

    } else {

      if ($('.speciality-cards').hasClass('slick-initialized')) {
        $('.speciality-cards').slick('unslick');
      }

    }
  }

  initSpecialitySlider();

  $(window).on('resize', function () {
    initSpecialitySlider();
  });

});

jQuery(document).ready(function ($) {

  function initSpecialitySlider() {
    if ($(window).width() <= 480) {

      if (!$('.doctors-slider').hasClass('slick-initialized')) {
        $('.doctors-slider').slick({
          slidesToShow: 1,
          slidesToScroll: 1,
          autoplay: true,
          autoplaySpeed: 3000,
          pauseOnHover: false,
          dots: true,
          fade: false, // fade doesn't work well with multiple slides
          arrows: false,
          infinite: false,
          centerMode: false
        });
      }

    } else {

      if ($('.doctors-slider').hasClass('slick-initialized')) {
        $('.doctors-slider').slick('unslick');
      }

    }
  }

  initSpecialitySlider();

  $(window).on('resize', function () {
    initSpecialitySlider();
  });

});

jQuery(document).ready(function ($) {

  function initSpecialitySlider() {
    if ($(window).width() <= 480) {

      if (!$('.whatsnew-slider').hasClass('slick-initialized')) {
        $('.whatsnew-slider').slick({
          slidesToShow: 1,
          slidesToScroll: 1,
          autoplay: true,
          autoplaySpeed: 3000,
          pauseOnHover: false,
          dots: true,
          fade: false, // fade doesn't work well with multiple slides
          arrows: false,
          infinite: true,
          centerMode: false
        });
      }

    } else {

      if ($('.whatsnew-slider').hasClass('slick-initialized')) {
        $('.whatsnew-slider').slick('unslick');
      }

    }
  }

  initSpecialitySlider();

  $(window).on('resize', function () {
    initSpecialitySlider();
  });

});



</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const searchInput = document.getElementById('doctorSearch');
    const doctors     = document.querySelectorAll('.doctor-item');
    const noDoctor    = document.getElementById('noDoctor');

    searchInput.addEventListener('input', function () {

        const query = this.value.toLowerCase().trim();
        let visibleCount = 0;

        doctors.forEach(doc => {

            const name        = doc.dataset.name;
            const designation = doc.dataset.designation;

            if (name.includes(query) || designation.includes(query)) {
                doc.style.display = '';
                doc.classList.remove('fade-out');
                doc.classList.add('fade-in');
                visibleCount++;
            } else {
                doc.classList.remove('fade-in');
                doc.classList.add('fade-out');
                setTimeout(() => doc.style.display = 'none', 120);
            }

        });

        noDoctor.classList.toggle('d-none', visibleCount > 0);
    });

});
</script>
<script>
let otpVerified = false;

/* ================= INTL TEL ================= */
const iti = window.intlTelInput(document.querySelector("#phone"), {
    separateDialCode:true,
    preferredCountries:["in","us","ae"],
    utilsScript:"https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"
});

/* ================= PHONE CLEAN ================= */
function syncHiddenPhone(){
    let full = iti.getNumber(intlTelInputUtils.numberFormat.E164);
    let code = iti.getSelectedCountryData().dialCode;
    let digits = full.replace("+"+code,"").replace(/^0+/,"");

    $("#clean_phone").val(digits);
    $("#country_code").val(code);
}

/* ================= FORM MSG ================= */
function showFormMsg(msg,type){
    $("#formMsg").removeClass("d-none success error")
                 .addClass(type)
                 .html(msg);
}

/* ================= FORM VALIDATION ================= */
$("#patient-form").validate({
    errorClass:"text-danger",
    errorPlacement:(e,el)=>e.insertAfter(el),

    rules:{ name:{required:true,minlength:3}, phone:{required:true}, otp:{required:true,minlength:6} },
    messages:{
        name:{required:"Enter your name",minlength:"Minimum 3 characters"},
        phone:{required:"Enter phone number"},
        otp:{required:"Enter OTP",minlength:"Enter 6 digit OTP"}
    },

    submitHandler:function(){

        $(".text-danger").remove();
        $("#formMsg").addClass("d-none");

        if(!iti.isValidNumber()){
            $("#phone").after('<label class="text-danger">Enter valid phone number</label>');
            return false;
        }

        if(!otpVerified){
            $("#otp").after('<label class="text-danger">OTP not verified</label>');
            return false;
        }

        syncHiddenPhone();
        $("#submitBtn").prop("disabled",true).text("Submitting...");

        $.ajax({
            url:'{{ url("callback/submit-enquiry") }}',
            type:'POST',
            headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'},
            data:{ name: $('[name="name"]').val(), phone: $("#clean_phone").val(), country_code: $("#country_code").val() },

            success:function(){
                $("#patient-form")[0].reset();
                otpVerified=false;
                $("#otp").prop("disabled",true);
                $("#submitBtn").prop("disabled",true).text("Submit");
                showFormMsg("✔ Form submitted successfully","success");
            },

            error:function(){
                $("#submitBtn").prop("disabled",false).text("Submit");
                showFormMsg("Something went wrong. Try again.","error");
            }
        });
    }
});

/* ================= SEND OTP ================= */
$("#sendOtpBtn").click(function(){

    $(".text-danger").remove();
    $("#formMsg").addClass("d-none");

    if(!iti.isValidNumber()){
        $("#phone").after('<label class="text-danger">Enter valid phone number</label>');
        return;
    }

    syncHiddenPhone();
    $("#otp").prop("disabled",false);
    $(this).prop("disabled",true).text("Sending...");

    $.post('{{ url("/send-otp") }}',{ phone:$("#clean_phone").val(), country_code:$("#country_code").val(), _token:'{{ csrf_token() }}'})
    .done(r=>showFormMsg(r.message,"success"))
    .always(()=>$(this).prop("disabled",false).text("Send OTP"));
});

/* ================= VERIFY OTP ================= */
$("#verifyOtpBtn").click(function(){

    $(".text-danger").remove();
    $("#formMsg").addClass("d-none");

    let otp=$("#otp").val().trim();
    if(otp.length!==6){
        $("#otp").after('<label class="text-danger">Enter valid OTP</label>');
        return;
    }

    $(this).prop("disabled",true).text("Verifying...");

    $.post('{{ url("/verify-otp") }}',{ phone:$("#clean_phone").val(), otp:otp, _token:'{{ csrf_token() }}'})
    .done(res=>{
        if(res.status==="success"){
            otpVerified=true;
            $("#submitBtn").prop("disabled",false);
            showFormMsg("✔ OTP Verified","success");
        }else{
            otpVerified=false;
            $("#submitBtn").prop("disabled",true);
            $("#otp").after('<label class="text-danger">Invalid OTP</label>');
        }
    })
    .always(()=>$(this).prop("disabled",false).text("Verify"));
});

/* AUTO VERIFY */
$("#otp").on("input",()=>$("#otp").val().length===6 && $("#verifyOtpBtn").click());

$("#submitBtn").prop("disabled",true);
$("#otp").prop("disabled",true);

</script>




@endpush
