@extends('layouts.bookapp')

@section('title', 'Patient Details')
@php
// Convert JSON string to PHP array
$doctor = json_decode($doctor, true); // true => associative array

@endphp
@section('content')

<div class="container py-5">
    <div class="row justify-content-center">

        <div class="col-md-7">
              <h4 class="fw-bold text-center mb-3">Patient Registration</h4>
    <p class="text-muted mb-0 text-center mb-3">Enter your details to book an appointment</p>
            <div class="doctor-card">
                <div class="card-body p-4">


                    <form id="patient-form" method="POST" action="{{ route('patient.register') }}">
                        @csrf
                        <input type="hidden" name="patient_id" id="patient_id" value="">

                     {{-- PHONE --}}

                   <div class="mb-4">
    <label class="form-label fw-semibold">
        Phone Number <span class="text-danger">*</span>
    </label>

    <div class="row g-2">
        <div class="col-md-7 position-relative">
            <input type="tel"
                   id="phone"
                   class="form-control pe-5"
                   placeholder="Enter phone number"
                   required>
               <small id="phoneError" class="invalid-feedback d-block"></small>

            <button type="button"
                    id="sendOtpBtn"
                    class="btn btn-inputright">
                Send OTP
            </button>
        </div>

        <div class="col-md-5 position-relative">
            <input type="text"
                   id="otp"
                   class="form-control pe-5"
                   placeholder="Enter OTP"
                   value=""
                   maxlength="6" required>

            <button type="button"
                    id="verifyOtpBtn"
                    class="btn btn-inputright">
                Verify
            </button>
        </div>
    </div>

    <small id="otpStatus" class="text-muted d-block mt-1"></small>

    <input type="hidden" id="phone_number">
    <input type="hidden" name="phone" id="clean_phone">
    <input type="hidden" name="country_code" id="country_code">
</div>




{{-- BOOKING FOR --}}
<div class="mb-3">
    <label class="form-label fw-semibold">
        Booking For <span class="text-danger">*</span>
    </label>

    <div class="d-flex gap-3 flex-wrap">
        @php
            $bfOptions = ['Self','Spouse','Parent','Child','Others'];
        @endphp

        @foreach ($bfOptions as $opt)
            <div class="form-check form-check-inline">
                <input class="form-check-input bookingfor"
                       type="radio"
                       name="bookingfor"
                       value="{{ $opt }}"
                       {{ old('bookingfor', 'Self') === $opt ? 'checked' : '' }}
                       required>
                <label class="form-check-label">{{ $opt }}</label>
            </div>
        @endforeach
    </div>

    {{-- ✅ ADDED: relation selector --}}
    {{-- <div id="patientRelationSelector" class="mt-3 d-none">
        <label class="form-label fw-semibold">Select Patient</label>
        <div id="patientRelationButtons" class="d-flex gap-2 flex-wrap"></div>
    </div> --}}

    <input type="text"
           name="other_reason"
           id="other_reason"
           class="form-control mt-2"
           placeholder="Specify other"
           style="display:none;">
</div>

                        {{-- PATIENT DETAILS --}}
                    <div class="mb-3">
    <label class="form-label fw-semibold" id="nameLabel">
        Your Name <span class="text-danger">*</span>
    </label>
    <input type="text" name="name" class="form-control" required>
</div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email (Optional)</label>
                            <input type="email" name="email" class="form-control">
                        </div>

                     <div class="row mb-3">

    <!-- Gender -->
    <div class="col-md-6">
        <label class="form-label fw-semibold">
            Gender <span class="text-danger">*</span>
        </label>

        <div class="d-flex gap-3 mt-1">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="gender" value="M" required>
                <label class="form-check-label">Male</label>
            </div>

            <div class="form-check">
                <input class="form-check-input" type="radio" name="gender" value="F">
                <label class="form-check-label">Female</label>
            </div>
        </div>
    </div>

    <!-- Age -->
<div class="col-md-6">
    <label class="form-label fw-semibold">
        Age <span class="text-danger">*</span>
    </label>
    <input type="number"
           name="age"
           class="form-control"
           min="0"
           max="120"
           placeholder="Enter age"
           required>
</div>

</div>


                        {{-- APPOINTMENT DETAILS --}}
{{-- PAYMENT DETAILS --}}
<div class="alert alert-info mt-4">

    <strong class="d-block mb-2">Appointment Summary</strong>

    <div class="d-flex justify-content-between">
        <span>Date</span>
        <span>{{ \Carbon\Carbon::createFromFormat('Ymd', $appointmentDate)->format('d M Y') }}</span>
    </div>

    <div class="d-flex justify-content-between">
        <span>Time</span>
        <span>{{ \Carbon\Carbon::createFromFormat('H:i', $appointmentTime)->format('h:i A') }}</span>
    </div>

    <hr>

    <div class="d-flex justify-content-between">
        <span>Doctor Consultation Fee</span>
        <span>₹ <span id="doctorFee">{{ $appointmentFee }}</span></span>
    </div>

    <div id="followupMessage"></div>

    {{-- ✅ REGISTRATION FEE (DYNAMIC) --}}
    <div class="d-flex justify-content-between d-none" id="registrationFeeRow">
        <span>
            Registration Fee
            <small class="text-muted d-block" id="registrationValidity"></small>
        </span>
        <span>₹ <span id="registrationFeeAmount">0</span></span>
    </div>

    <hr>

    <div class="d-flex justify-content-between fw-bold">
        <span>Total Payable</span>
        <span>₹ <span id="totalPayable">{{ $appointmentFee }}</span></span>
    </div>
</div>

{{-- Hidden fields for backend --}}
<input type="hidden" name="doctor_fee" value="{{ $appointmentFee }}">
<input type="hidden" name="registration_fee" id="registrationFeeInput" value="0">
<input type="hidden" name="total_amount" id="totalAmountInput" value="{{ $appointmentFee }}">

                        {{-- HIDDEN SLOT DATA --}}
                        <input type="hidden" name="slotDate" value="{{ $appointmentDate }}">
                        <input type="hidden" name="slotTime" value="{{ $appointmentTime }}">
                        <input type="hidden" name="doctorName" value="{{ $doctor['name'] ?? '' }}">
                        <input type="hidden" name="doctorKey" value="{{ $doctor['drKey'] ?? '' }}">
                        <input type="hidden" name="industry" value="hospital-clinic">

                     <button type="submit" id="submitBtn" class="btn btn-book w-100"> Confirm Appointment </button>

                    </form>

                </div>
            </div>
        </div>

    </div>
</div>

{{-- PATIENT SELECT MODAL --}}
<div class="modal fade" id="patientSelectModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Select Patient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="patientList"></div>
        </div>
    </div>
</div>
<div class="modal fade" id="patientPicker" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Select Patient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="patientPickerBody"></div>

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
/* =====================================================
   DOCTOR FOLLOW-UP CHECK (ONLINE BOOKING)
   ADD THIS AT THE VERY BOTTOM - DO NOT REMOVE ANYTHING
===================================================== */

function checkDoctorFollowupFee() {

    let patientId = $('#patient_id').val();
    let doctorId  = "{{ $doctorId }}";

    if (!otpVerified || !patientId) return;

    $.get("{{ url('/check-followup-fee') }}", {
        doctor_id: doctorId,
        patient_id: patientId
    })
    .done(function(res) {

        let doctorFee = parseFloat(res.doctor_fee || 0);

        // Update doctor fee UI
        $('#doctorFee').text(doctorFee);
        $('input[name="doctor_fee"]').val(doctorFee);

        // Clear old message
        $('#followupMessage').html('');

        if (res.is_followup) {

            let extraInfo = '';

            if (res.followup_count > 0) {
                extraInfo = `
                    Previous Follow-up Visit: ${res.last_followup}<br>
                    Total Free Visits Used: ${res.followup_count}<br>
                `;
            }

            $('#followupMessage').html(`
                <div class="alert alert-success p-2 mt-2">
                    <strong>Follow-up Visit</strong><br>
                    Main Visit Date: ${res.last_visit}<br>
                    Valid Till: ${res.valid_till}<br>
                    ${extraInfo}
                    <strong>Doctor fee not applicable.</strong>
                </div>
            `);

        } else if (res.last_visit) {

            $('#followupMessage').html(`
                <div class="alert alert-warning p-2 mt-2">
                    <strong>Follow-up Expired</strong><br>
                    Main Visit Date: ${res.last_visit}<br>
                    Valid Till: ${res.valid_till}<br>
                    Total Free Visits Used: ${res.followup_count || 0}<br>
                    <strong>Doctor fee applicable.</strong>
                </div>
            `);
        }

        recalculateTotalAfterFollowup();
    });
}


function recalculateTotalAfterFollowup() {

    let doctorFee = parseFloat($('#doctorFee').text() || 0);
    let regFee    = parseFloat($('#registrationFeeInput').val() || 0);

    let total = doctorFee + regFee;

    $('#totalPayable').text(total);
    $('#totalAmountInput').val(total);
}


/* =====================================================
   AUTO TRIGGER FOLLOW-UP CHECK
===================================================== */

// 1️⃣ When patient selected from modal
$(document).on('click', '.patient-select', function () {
    setTimeout(function(){
        checkDoctorFollowupFee();
    }, 300);
});

// 2️⃣ When patient auto filled
$(document).on('click', '.patient-rel-btn', function () {
    setTimeout(function(){
        checkDoctorFollowupFee();
    }, 300);
});

// 3️⃣ After OTP verification success
$(document).ajaxComplete(function(event, xhr, settings) {

    if (settings.url.includes('/verify-otp')) {
        setTimeout(function(){
            checkDoctorFollowupFee();
        }, 500);
    }

});
</script>
<script>
let otpVerified = false;
let otpAutoVerified = false;
let cachedPatients = [];


let isAutoPrefill = false; // ✅ ADD
/* =====================================================
   RESET PATIENT FORM (✅ ADDED)
===================================================== */
function resetPatientFormFields() {
    // $('#patient_id').val('');
    $('input[name=name], input[name=email], input[name=age]').val('');
    $('input[name=gender]').prop('checked', false);
    $('#other_reason').val('').addClass('d-none').removeAttr('required');
}

/* INTL TEL INPUT */
var input = document.querySelector("#phone");
var iti = window.intlTelInput(input, {
    separateDialCode: true,
    preferredCountries: ["in", "us", "ae"],
});



function showPhoneError(message) {
    $('#phone').addClass('is-invalid');   // 🔴 red border
    $('#phoneError').text(message);       // error text
    return false;
}

$('#sendOtpBtn_hold').on('click', function () {

    // Validate phone FIRST
    if (!validatePhoneIntl()) {
        return;
    }

    // ✅ Phone is valid → update hidden fields
    updateHiddenPhoneFields();

    // ✅ OTP success UI
    $('#otpStatus')
        .removeClass('text-danger')
        .addClass('text-success')
        .text('OTP sent successfully');

    // 🔴 Call OTP API here
});
/* VERIFY OTP */
$("#verifyOtpBtn_hold").on("click", function () {

    // 🔴 Verify OTP via API
    otpVerified = true;

    $("#otpStatus").html("✔ Phone number verified").removeClass("text-danger").addClass("text-success");
    $("#submitBtn").prop("disabled", true);

    let phone = $("#clean_phone").val();

    $.post("{{ url('check-patient') }}", {
        phone: phone,
        _token: "{{ csrf_token() }}"
    }, function (patients) {

        if (patients.length > 0) {

            let html = "";
            patients.forEach(p => {
                html += `
                    <div class="border p-2 mb-2 select-patient"
                         style="cursor:pointer"
                         data-patient='${JSON.stringify(p)}'>
                        <strong>${p.name}</strong> – Age ${p.age}
                    </div>`;
            });

            $("#patientList").html(html);
            $("#patientSelectModal").modal("show");
        }
    });
});

/* SELECT PATIENT */
$(document).on("click", ".select-patient", function () {

    let p = $(this).data("patient");

    $("input[name=name]").val(p.name);
    $("input[name=email]").val(p.email);
    $("input[name=age]").val(p.age);
    $("input[name=gender][value=" + p.gender + "]").prop("checked", true);

    $("#patientSelectModal").modal("hide");
});
$(document).on("change", ".bookingfor", function () {
    if ($(this).val() === "Others") {
        $("#other_reason").show().attr("required", true);
    } else {
        $("#other_reason").hide().attr("required", false);
    }
});

</script>
<script>
let phoneLookupTimer = null;
let lastPhoneChecked = null;

/* ============================
   INTL TEL INPUT
============================ */
var phoneInput = document.querySelector("#phone");
var iti = window.intlTelInput(phoneInput, {
    separateDialCode: true,
    preferredCountries: ["in", "us", "ae"],
});


function validatePhoneIntl() {


    // Reset UI
    $('#phone').removeClass('is-invalid');
    $('#phoneError').text('');

    // Empty check
    if (!phoneInput.value.trim()) {
        return showPhoneError('Phone number is required');
    }

    // intl-tel-input validation
    if (!iti.isValidNumber()) {

        let errorCode = iti.getValidationError();
        let message = 'Invalid phone number';

        switch (errorCode) {
            case intlTelInputUtils.validationError.TOO_SHORT:
                message = 'Phone number is wrong';
                break;
            case intlTelInputUtils.validationError.TOO_LONG:
                message = 'Phone number is wrong';
                break;
            case intlTelInputUtils.validationError.INVALID_COUNTRY_CODE:
                message = 'Invalid country code';
                break;
            case intlTelInputUtils.validationError.NOT_A_NUMBER:
                message = 'Invalid phone number';
                break;
        }

        return showPhoneError(message);
    }

    return true;
}
/* ============================
   PHONE CHANGE EVENTS
============================ */
// Event listener
$('#phone').on('keyup change blur', function () {
    $('#phone').removeClass('is-invalid');
    $('#phoneError').text('');
    clearTimeout(phoneLookupTimer);
    phoneLookupTimer = setTimeout(fetchPatientsByPhone, 600);
});

// Optional: if using intl-tel-input country change
phoneInput.addEventListener('countrychange', function () {
    clearTimeout(phoneLookupTimer);
    phoneLookupTimer = setTimeout(fetchPatientsByPhone, 600);
});

/* ============================
   UPDATE HIDDEN PHONE FIELDS
============================ */
function updateHiddenPhoneFields() {

    let number = phoneInput.value.replace(/\D/g, '');
    let code = iti.getSelectedCountryData().dialCode;

    $('#phone_number').val(number);
    $('#clean_phone').val(number);
    $('#country_code').val(code);
}

/* ============================
   FETCH PATIENTS BY PHONE
============================ */
function fetchPatientsByPhone() {

    // ✅ ADD THIS BLOCK (FIRST LINE)
    if (!otpVerified) {
        $('#patientPicker').modal('hide');
        $('#patientRelationSelector').addClass('d-none');
        return;
    }

    // 👇 KEEP EVERYTHING BELOW AS-IS
    updateHiddenPhoneFields();

    let phone = $('#phone_number').val();
    let countryCode = $('#country_code').val();

    if (!phone || phone.length < 6 || !countryCode) {
        $('#patientPicker').modal('hide');
        return;
    }

    let key = countryCode + phone;
    if (key === lastPhoneChecked) return;
    lastPhoneChecked = key;

    $.get("{{ url('/patients/by-phone') }}", {
        phone: phone,
        country_code: countryCode
    })
    .done(function (res) {
        cachedPatients = res.patients || []; // ✅ ADD
        // No patient
        if (res.count === 0) {
            $('#patientPicker').modal('hide');
            return;
        }

        // Single → auto fill
        if (res.count === 1) {
            resetPatientFormFields();
            prefillPatient(res.patients[0]);

            // ✅ ADD
            checkRegistrationFee();

            $('#patientPicker').modal('hide');
            return;
        }

        // ✅ ADD THIS (does NOT affect modal)
        renderPatientRelations(res.patients);

        let html = '';

        // 4 PATIENT LIMIT WARNING
        if (res.count >= 4) {
            html += `
                <div class="alert alert-warning mb-2">
                    This mobile number allows only <strong>4</strong> members.
                </div>
            `;
        }

        // Render max 4 patients
        res.patients.slice(0, 4).forEach(p => {

            let bookingForText =
                p.bookingfor === 'Others' && p.other_reason
                    ? p.other_reason
                    : p.bookingfor;

            html += `
                <div class="border rounded p-3 mb-2 patient-select"
                     style="cursor:pointer"
                     data-patient='${JSON.stringify(p)}'>
                    <strong>${p.name}</strong><br>
                    <small class="text-muted">
                        ${p.gender} - ${p.age} / ${bookingForText}
                    </small>
                </div>
            `;
        });


        $('#patientPickerBody').html(html);
        $('#patientPicker').modal('show');
    });
}



/* =====================================================
   RENDER RELATION BUTTONS (✅ ADDED)
===================================================== */
function renderPatientRelations(patients) {

    if (!otpVerified || patients.length === 0) {
        $('#patientRelationSelector').addClass('d-none');
        return;
    }

    $('#patientRelationButtons').html('');
    $('#patientRelationSelector').removeClass('d-none');

    patients.forEach((p, i) => {
        let label = p.bookingfor === 'Child'
            ? 'Child ' + (i+1)
            : p.bookingfor === 'Parent'
                ? (p.gender === 'F' ? 'Mother' : 'Father')
                : p.bookingfor;

        $('#patientRelationButtons').append(`
            <button type="button"
                class="btn btn-outline-primary btn-sm patient-rel-btn"
                data-patient='${JSON.stringify(p)}'>
                ${label}
            </button>
        `);
    });
}

$(document).on('click', '.patient-rel-btn', function () {
    resetPatientFormFields();
    let patient = $(this).data('patient');
    prefillPatient(patient);

    if (otpVerified) {
        checkRegistrationFee();
    }
});

/* ============================
   PREFILL PATIENT
============================ */
function prefillPatient(p) {
    $('#patient_id').val('');
    // ✅ ONLY set patient_id when patient exists
    if (p && p.id) {
        $('#patient_id').val(p.id);
    }

    $('input[name=name]').val(p.name || '');
    $('input[name=email]').val(p.email || '');
    $('input[name=age]').val(p.age || '');

    if (p.gender) {
        $(`input[name=gender][value="${p.gender}"]`).prop('checked', true);
    }

    if (p.bookingfor) {
        isAutoPrefill = true;

        $(`input[name=bookingfor][value="${p.bookingfor}"]`)
            .prop('checked', true)
            .trigger('change');

        isAutoPrefill = false;

        if (p.bookingfor === 'Others') {
            $('#other_reason')
                .val(p.other_reason || '')
                .show()
                .attr('required', true);
        }
    }

    if (otpVerified) {
        $('#submitBtn').prop('disabled', false);
        checkRegistrationFee(); // ✅ patient_id is now correct
    }
}



/* ============================
   INITIAL STATE
============================ */
$(document).ready(function () {
    $('#submitBtn').prop('disabled', true); // disabled by default
});
/* ============================
   CLICK SELECT PATIENT
============================ */
$(document).on('click', '.patient-select', function () {
    let patient = $(this).data('patient');
    prefillPatient(patient);
    $('#patientPicker').modal('hide');
});
</script>
<script>
$(document).on('change', '.bookingfor', function () {

    if (isAutoPrefill) return;

    let bookingFor = $(this).val();

    // 🔒 Find matching patient
    let match = cachedPatients.find(
        p => p.bookingfor?.toLowerCase() === bookingFor.toLowerCase()
    );

    // 🔥 CRITICAL FIX
    if (!match) {
        // NEW PERSON → must clear patient_id
        $('#patient_id').val('');
        resetPatientFormFields();
    }

    $('#nameLabel').html(
        bookingFor === 'Self'
            ? 'Your Name <span class="text-danger">*</span>'
            : 'Patient Name <span class="text-danger">*</span>'
    );

    if (bookingFor === 'Others') {
        $('#other_reason').show().attr('required', true);
    } else {
        $('#other_reason').hide().removeAttr('required').val('');
    }

    // ✅ ONLY restore if an exact patient exists
    if (match) {
        prefillPatient(match);
    }

    // ✅ Recalculate fee AFTER patient_id is correct
    if (otpVerified) {
        checkRegistrationFee();
    }
});



/* Init on load */
$('.bookingfor:checked').trigger('change');

$('input[name="age"]').on('input', function () {
    let val = parseInt(this.value, 10);
    if (val < 0) this.value = 0;
    if (val > 120) this.value = 120;
});
</script>

<script>
$('#sendOtpBtn').on('click', function () {

    $("#submitBtn").prop("disabled", true);
    if (!validatePhoneIntl()) return;

    updateHiddenPhoneFields();

    let phone = $('#clean_phone').val();
    let countryCode = $('#country_code').val();

    $('#sendOtpBtn').prop('disabled', true).text('Sending...');

    $.post("{{ url('/send-otp') }}", {
        phone: phone,
        country_code: countryCode,
        _token: "{{ csrf_token() }}"
    })
    .done(function (res) {

        // ✅ RESET OTP STATE (IMPORTANT)
        otpVerified = false;
        otpAutoVerified = false;

        $('#otp').val('').prop('disabled', false).focus();
        $('#verifyOtpBtn').prop('disabled', false).text('Verify');

        // Reset registration fee UI
$('#registrationFeeRow').addClass('d-none');
$('#registrationFeeAmount').text(0);
$('#registrationValidity').text('');
$('#registrationFeeInput').val(0);

let doctorFee = parseFloat($('#doctorFee').text());
$('#totalPayable').text(doctorFee);
$('#totalAmountInput').val(doctorFee);

        $('#otpStatus')
            .removeClass('text-danger')
            .addClass('text-success')
            .text(res.message);
    })
    .fail(function (xhr) {
        $('#otpStatus')
            .removeClass('text-success')
            .addClass('text-danger')
            .text(xhr.responseJSON?.message || 'Failed to send OTP');
    })
    .always(function () {
        $('#sendOtpBtn').prop('disabled', false).text('Send OTP');
    });
});
</script>

<script>
$('#verifyOtpBtn').on('click', function () {

    // 🔒 Block repeat verification
    if (otpVerified) return;

    let otp = $('#otp').val().trim();
    let phone = $('#clean_phone').val();

    $("#submitBtn").prop("disabled", true);

    if (otp.length !== 6) {
        $('#otpStatus')
            .removeClass('text-success')
            .addClass('text-danger')
            .text('Enter valid OTP');
        return;
    }

    $('#verifyOtpBtn').prop('disabled', true).text('Verifying...');

    $.post("{{ url('/verify-otp') }}", {
        phone: phone,
        otp: otp,
        _token: "{{ csrf_token() }}"
    })
    .done(function(res) {

        if (res.status === 'success') {

            otpVerified = true;
            otpAutoVerified = true;

            $('#otpStatus')
                .removeClass('text-danger')
                .addClass('text-success')
                .text('✔ ' + res.message);

            $('#submitBtn').prop('disabled', false);

            // 🔒 Lock verify button
            $('#verifyOtpBtn').prop('disabled', true).text('Verified');

            // Fetch patient data ONCE
            fetchPatientsByPhone();

            // ✅ ADD THIS
            checkRegistrationFee();

        } else {

            otpVerified = false;
            otpAutoVerified = false;

            $('#otpStatus')
                .removeClass('text-success')
                .addClass('text-danger')
                .text(res.message || 'OTP verification failed');

            $('#verifyOtpBtn').prop('disabled', false).text('Verify');
            $('#submitBtn').prop('disabled', true);
        }
    })
    .fail(function (xhr) {

        otpVerified = false;
        otpAutoVerified = false;

        $('#otpStatus')
            .removeClass('text-success')
            .addClass('text-danger')
            .text(xhr.responseJSON?.message || 'OTP verification failed');

        $('#verifyOtpBtn').prop('disabled', false).text('Verify');
    });
});
</script>

<script>
$('#otp').on('input', function () {

    // 🔒 Stop if already verified
    if (otpVerified || otpAutoVerified) return;

    if (this.value.length === 6) {
        otpAutoVerified = true;
        $('#verifyOtpBtn').click();
    }
});
</script>

<script>
function checkRegistrationFee() {

    let phone      = $('#clean_phone').val();
    let bookingFor = $('input[name="bookingfor"]:checked').val();
    let patientId  = $('#patient_id').val();



    if (!otpVerified || !phone || !bookingFor) return;

    $.get("{{ url('/check-registration-fee') }}", {
        phone: phone,
        bookingfor: bookingFor,
        patient_id: patientId
    })
    .done(function(res) {

        let doctorFee = parseFloat($('#doctorFee').text());
        let regFee = 0;

        if (res.apply === true) {
            regFee = parseFloat(res.amount);
            $('#registrationFeeAmount').text(regFee);
            $('#registrationValidity').text('Valid till ' + res.valid_till);
            $('#registrationFeeRow').removeClass('d-none');
            $('#registrationFeeInput').val(regFee);
        } else {
            $('#registrationFeeRow').addClass('d-none');
            $('#registrationFeeInput').val(0);
        }

        let total = doctorFee + regFee;
        $('#totalPayable').text(total);
        $('#totalAmountInput').val(total);
    });
}


</script>

@endpush