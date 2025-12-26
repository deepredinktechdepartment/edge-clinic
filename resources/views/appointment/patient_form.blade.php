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
<div class="alert alert-info mt-4 d-flex flex-wrap align-items-center gap-4">
    <strong>Appointment:</strong>

    <span>
        Date:
        {{ \Carbon\Carbon::createFromFormat('Ymd', $appointmentDate)->format('d M Y') }}
    </span>

    <span>
        Time:
        {{ \Carbon\Carbon::createFromFormat('H:i', $appointmentTime)->format('h:i A') }}
    </span>

    <span>
        Fee: ₹{{ $appointmentFee }}
    </span>
</div>
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
let otpVerified = false;

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

        // No patient
        if (res.count === 0) {
            $('#patientPicker').modal('hide');
            return;
        }

        // Single → auto fill
        if (res.count === 1) {
            prefillPatient(res.patients[0]);
            $('#patientPicker').modal('hide');
            return;
        }

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

/* ============================
   PREFILL PATIENT
============================ */
/* ============================
   PREFILL PATIENT
============================ */
function prefillPatient(p) {
    let hasPatient = false; // Track if at least one meaningful field exists

    // Prefill hidden patient_id only if data comes from popup
    if (p.id) {
        $('#patient_id').val(p.id);
        hasPatient = true;
    }

    // Prefill other fields
    if (p.name) {
        $('input[name=name]').val(p.name);
        hasPatient = true;
    }
    if (p.email) {
        $('input[name=email]').val(p.email);
        hasPatient = true;
    }
    if (p.age) {
        $('input[name=age]').val(p.age);
        hasPatient = true;
    }
    if (p.gender) {
        $(`input[name=gender][value="${p.gender}"]`).prop('checked', true);
        hasPatient = true;
    }

    if (p.bookingfor) {
        $(`input[name=bookingfor][value="${p.bookingfor}"]`)
            .prop('checked', true)
            .trigger('change');

        if (p.bookingfor === 'Others') {
            $('#other_reason')
                .val(p.other_reason || '')
                .show()
                .attr('required', true);
            if (p.other_reason) hasPatient = true;
        }

        hasPatient = true; // bookingfor exists, so consider it as selection
    } else {
        // Clear booking reason if not prefilled
        $('input[name=bookingfor]').prop('checked', false);
        $('#other_reason').hide().val('').removeAttr('required');
    }

    // Enable submit button only if at least one meaningful field exists
    //$('#submitBtn').prop('disabled', !hasPatient);
    $('#submitBtn').prop('disabled', true);
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

    let bookingFor = $(this).val();

    if (bookingFor === 'Self') {

        // Label
        $('#nameLabel').html('Your Name <span class="text-danger">*</span>');


    } else {

        // Label
        $('#nameLabel').html('Patient Name <span class="text-danger">*</span>');

    

        // Clear OTP status
        $('#otpStatus').text('');
    }

    // Handle "Others"
    if (bookingFor === 'Others') {
        $('#other_reason').show().attr('required', true);
    } else {
        $('#other_reason').hide().removeAttr('required').val('');
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
        $('#otpStatus')
            .removeClass('text-danger')
            .addClass('text-success')
            .text(res.message);

        $('#otp').prop('disabled', false).focus();
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

    let otp = $('#otp').val().trim();
    let phone = $('#clean_phone').val();
    $("#submitBtn").prop("disabled", true);

    if (otp.length !== 6) {
        $('#otpStatus').text('Enter valid OTP').addClass('text-danger');
        return;
    }

    $('#verifyOtpBtn').prop('disabled', true).text('Verifying...');

    $.post("{{ url('/verify-otp') }}", {
        phone: phone,
        otp: otp,
        _token: "{{ csrf_token() }}"
    })
     .done(function(res) {
        // Check backend status
        if(res.status === 'success') {
            otpVerified = true;

            $('#otpStatus')
                .removeClass('text-danger')
                .addClass('text-success')
                .text('✔ ' + res.message); // Use backend message

            $('#submitBtn').prop('disabled', false);

            // Optional: fetch existing patient data
            fetchPatientsByPhone();
        } else {
            otpVerified = false;

            $('#otpStatus')
                .removeClass('text-success')
                .addClass('text-danger')
                .text(res.message || 'OTP verification failed');

            $('#submitBtn').prop('disabled', true);
        }
    })
    .fail(function (xhr) {
        $('#otpStatus')
            .removeClass('text-success')
            .addClass('text-danger')
            .text(xhr.responseJSON?.message || 'OTP verification failed');
    })
    .always(function () {
        $('#verifyOtpBtn').prop('disabled', false).text('Verify');
    });
});
</script>
<script>
    $('#otp').on('input', function () {
        $('#submitBtn').prop('disabled', true);
    if (this.value.length === 6) {
        $('#verifyOtpBtn').click();
    }
});
</script>
@endpush