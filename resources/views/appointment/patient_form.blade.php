@extends('layouts.bookapp')

@section('title', 'Patient Login & Register')

@section('content')

<div class="container py-5">
    <div class="row">
        
        {{-- LOGIN FORM --}}
        <div class="col-sm-5">
            <h3 class="mb-4 fw-bold text-center">Login</h3>
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-4">

                    <form id="login-form" method="POST" action="{{ route('patient.login') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Email / Phone <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="email" class="form-control @error('email') is-invalid @enderror" required>
                            @error('email')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Password <span class="text-danger">*</span>
                            </label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-book w-100 py-2 fs-5">
                            Submit
                        </button>
                    </form>

                </div>
            </div>
        </div>

        <div class="col-md-1"></div>


        {{-- REGISTER FORM --}}
        <div class="col-md-6">
            <h3 class="mb-4 fw-bold text-center">Register</h3>
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-4">

                    <form id="register-form" method="POST" action="{{ route('patient.register') }}">
                        @csrf

                        {{-- NAME --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- EMAIL OPTIONAL --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email (Optional)</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror">
                            @error('email')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- PHONE --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Phone Number <span class="text-danger">*</span>
                            </label>
                            <input type="tel" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" required>
                            @error('phone')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                            <input type="hidden" name="country_code" id="country_code">
                        </div>

                  

                        {{-- BOOKING FOR INLINE --}}
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
                                        <input class="form-check-input bookingfor" type="radio" name="bookingfor" value="{{ $opt }}" required>
                                        <label class="form-check-label">{{ $opt }}</label>
                                    </div>
                                @endforeach

                            </div>

                            <input type="text" name="other_reason" id="other_reason" class="form-control mt-2" placeholder="Specify other" style="display:none;">
                        </div>

                        {{-- GENDER INLINE --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Gender <span class="text-danger">*</span>
                            </label>

                            <div class="d-flex gap-3">

                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" value="M" required>
                                    <label class="form-check-label">Male</label>
                                </div>

                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" value="F" required>
                                    <label class="form-check-label">Female</label>
                                </div>

                            </div>
                        </div>

                        {{-- AGE --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Age <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="age" class="form-control @error('age') is-invalid @enderror" min="1" max="100" required>
                            @error('age')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        {{-- PASSWORD --}}
<div class="mb-3">
    <label class="form-label fw-semibold">
        Password <span class="text-danger">*</span>
    </label>
    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
    @error('password')
        <div class="text-danger small">{{ $message }}</div>
    @enderror
    <small class="text-muted">
        Password must be at least 8 characters, include uppercase, lowercase, and a number.
    </small>
</div>

{{-- CONFIRM PASSWORD --}}
<div class="mb-3">
    <label class="form-label fw-semibold">
        Confirm Password <span class="text-danger">*</span>
    </label>
    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
</div>

                        {{-- FEE --}}
                    @php
                    // Convert JSON string to PHP array
                    $doctor = json_decode($doctor, true); // true => associative array
                   
                    @endphp
                        <div class="mb-4 fw-semibold fs-5">
                            Appointment Fee: ₹{{ $appointmentFee ?? 1 }}
                        </div>

                        <button type="submit" class="btn btn-book w-100 py-2 fs-5">
                            Continue to Payment
                        </button>

                        <input type="hidden" name="industry" value="hospital-clinic">
                        <input type="hidden" name="slotDate" value="{{ $appointmentDate ?? '' }}">
                        <input type="hidden" name="slotTime" value="{{ $appointmentTime ?? '' }}">
                        <input type="hidden" name="doctorName" value="hospital-clinic">
                        <input type="hidden" name="doctorKey" value="hospital-clinic">
                    </form>

                </div>
            </div>
        </div>

    </div>
</div>


{{-- SLOT DETAILS --}}
<div class="container py-5">
    <h3 class="mb-4 fw-bold text-center">Patient Details</h3>

    <div class="row g-4 justify-content-center">

        <div class="col-md-5">
            <div class="card shadow-lg border-0 rounded-4 mt-4 mt-md-0">
                <div class="card-header btn-book text-white fw-bold text-center fs-5 rounded-top-4">
                    Slot Details
                </div>

                <div class="card-body p-4">

                    <p class="mb-2 fs-6"><strong>Date:</strong> {{ $appointmentDate ?? '' }}</p>
                    <p class="mb-2 fs-6"><strong>Time:</strong> {{ $appointmentTime ?? '' }}</p>

                    <p class="mb-2 fs-6">
                        <strong>Doctor Name:</strong> {{ $doctor['name'] ?? '' }}
                    </p>

                    <p class="mb-2 fs-6">
                        <strong>Designation:</strong> {{ $doctor['designation'] ?? '' }}
                    </p>

                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).on("change", ".bookingfor", function () {
    if ($(this).val() === "Others") {
        $("#other_reason").show().attr("required", true);
    } else {
        $("#other_reason").hide().attr("required", false);
    }
});

/* ================================
   INTL-TEL-INPUT INITIALIZATION
================================ */
var input = document.querySelector("#phone");

var iti = window.intlTelInput(input, {
    separateDialCode: true,
    preferredCountries: ["in", "us", "ae", "sg"],
    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js",
});

// On change extract dial code
input.addEventListener("countrychange", function () {
    let code = iti.getSelectedCountryData().dialCode;
    $("#country_code").val(code);
});

// On submit → clean phone (only number)
$("#register-form").on("submit", function () {
    let number = input.value.replace(/\D/g, "");  // remove spaces/dashes
    let code = iti.getSelectedCountryData().dialCode;

    $("#country_code").val(code);   // +91
    $("#phone").val(number);        // Ex: 9876543210 only

    return true;
});

// Password strength indicator (medium)
$("#password").on("input", function() {
    let val = $(this).val();
    let msg = "";

    // Medium strength pattern: at least 8 characters, upper, lower, number
    let mediumPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;

    if (val.length === 0) {
        msg = "";
    } else if (!mediumPattern.test(val)) {
        msg = "Password is weak. Use at least 8 characters, include uppercase, lowercase, and a number.";
    } else {
        msg = "Good! Your password is strong enough.";
    }

    $(this).next("small").text(msg);
});
</script>
@endpush