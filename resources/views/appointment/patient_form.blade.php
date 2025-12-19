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

                     {{-- PHONE --}}
<div class="mb-4">
    <label class="form-label fw-semibold">
        Phone Number <span class="text-danger">*</span>
    </label>

    <div class="position-relative">
        <input type="tel"
               id="phone"             
               class="form-control pe-5"
               placeholder="Enter phone number"
               required>

        <button type="button"
                id="sendOtpBtn"
                class="btn btn-inputright">
            Send OTP
        </button>
    </div>

    <input type="hidden" name="phone" id="clean_phone">
    <input type="hidden" name="country_code" id="country_code">

    {{-- OTP --}}
    <div class="position-relative mt-2">
        <input type="text"
               id="otp"
               class="form-control pe-5"
               placeholder="Enter OTP"
               value="1234"
               maxlength="6">

        <button type="button"
                id="verifyOtpBtn"
                class="btn btn-inputright">
            Verify
        </button>
    </div>

    <small id="otpStatus" class="text-muted d-block mt-1"></small>
</div>

{{-- Your Name --}}
<div class="mb-3">
    <label class="form-label fw-semibold">
        Your Name <span class="text-danger">*</span>
    </label>
    <input type="text" name="yourname" class="form-control" required>
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
                            <label class="form-label fw-semibold">
                                Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email (Optional)</label>
                            <input type="email" name="email" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Gender <span class="text-danger">*</span>
                            </label><br>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" value="M">
                                <label class="form-check-label">Male</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" value="F">
                                <label class="form-check-label">Female</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Age <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="age" class="form-control" required>
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

@endsection
@push('scripts')
<script>
let otpVerified = false;

/* INTL TEL INPUT */
var input = document.querySelector("#phone");
var iti = window.intlTelInput(input, {
    separateDialCode: true,
    preferredCountries: ["in", "us", "ae"],
});

/* SEND OTP */
$("#sendOtpBtn").on("click", function () {

    let number = input.value.replace(/\D/g, "");
    let code = iti.getSelectedCountryData().dialCode;

    if (!number) {
        alert("Enter phone number");
        return;
    }

    $("#clean_phone").val(number);
    $("#country_code").val(code);

    $("#otpStatus").text("OTP sent to your phone").removeClass("text-danger").addClass("text-success");

    // 🔴 Call OTP API here
});

/* VERIFY OTP */
$("#verifyOtpBtn").on("click", function () {

    // 🔴 Verify OTP via API
    otpVerified = true;

    $("#otpStatus").html("✔ Phone number verified").removeClass("text-danger").addClass("text-success");
    $("#submitBtn").prop("disabled", false);

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
@endpush