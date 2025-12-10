@extends('layouts.bookapp')

@section('title', 'Patient Details')

@section('content')
<div class="container py-5">

    {{-- Page Title --}}
    <h3 class="mb-4 fw-bold text-center">Patient Details</h3>

    <div class="row g-4 justify-content-center">

        {{-- Left Card --}}
        <div class="col-md-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-4">

                    <form id="patient-details-form" method="POST" action="{{ url('razorpay/create-order') }}">
                        @csrf

                        {{-- First Name --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>

                        {{-- Last Name --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Last Name</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>

                        {{-- Email --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        {{-- Phone --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Phone Number</label><br>
                            <input type="tel" id="phone" name="phone" class="form-control w-100" required>
                            <input type="hidden" name="country_code" id="country_code">
                        </div>

                        {{-- OTP --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Phone Verification</label>
                            <div class="input-group">
                                <input type="text" id="otp" class="form-control" placeholder="Enter OTP" required value="1234">
                                <button class="btn btn-outline-primary" type="button" id="sendOtpBtn">
                                    Send OTP
                                </button>
                            </div>
                        </div>

                        {{-- Fee --}}
                        <div class="mb-4 fw-semibold fs-5">
                            Appointment Fee: ₹{{ $appointmentFee }}
                        </div>

                        {{-- Submit --}}
                        <button type="submit" class="btn btn-book w-100 py-2 fs-5">
                            Continue to Payment
                        </button>

                        <input type="hidden" name="industry" value="hospital-clinic">
                    </form>

                </div>
            </div>
        </div>

        {{-- Right Card --}}
        <div class="col-md-5">
            <div class="card shadow-lg border-0 rounded-4 mt-4 mt-md-0">
                <div class="card-header btn-book text-white fw-bold text-center fs-5 rounded-top-4">
                    Slot Details
                </div>

                <div class="card-body p-4">

                  @php
                        $doctorData = isset($appointmentData['doctor'])
                            ? json_decode($appointmentData['doctor'], true)
                            : null;
                    @endphp

              

                    <p class="mb-2 fs-6">
                        <strong>Date:</strong>
                        {{ \Carbon\Carbon::parse($appointmentData['appointment_date'])->format('d M Y') }}
                    </p>

                    <p class="mb-2 fs-6">
                        <strong>Time:</strong> {{ $appointmentData['appointment_time'] }}
                    </p>

                    <p class="mb-1 fs-6">
                            <strong>Doctor Name:</strong> {{ $doctorData['name'] ?? 'N/A' }}
                        </p>
                          <p class="mb-2 fs-6">
                            <strong>Designation:</strong> {{ $doctorData['designation'] ?? 'N/A' }}
                        </p>


                

                </div>
            </div>
        </div>

    </div>
</div>
@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // --- INTL TEL INPUT ---
    const phoneInput = document.querySelector("#phone");
    const iti = window.intlTelInput(phoneInput, {
        initialCountry: "in",
        separateDialCode: true,
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.min.js",
        nationalMode: false
    });

    function updateCountryCode() {
        document.getElementById('country_code').value =
            "+" + iti.getSelectedCountryData().dialCode;
    }

    phoneInput.addEventListener('countrychange', updateCountryCode);
    phoneInput.addEventListener('blur', updateCountryCode);

    // --- OTP ---
    document.getElementById('sendOtpBtn').addEventListener('click', function () {
        const full = iti.getNumber();
        const valid = iti.isValidNumber();
        const national = full.replace(/\D/g, '').slice(-10);
        const pattern = /^[6-9]\d{9}$/;

        if (!valid || !pattern.test(national)) {
            alert("Please enter a valid Indian mobile number starting with 6,7,8, or 9.");
            return;
        }

        alert("OTP sent to " + full + " (simulation)");
    });

    // --- FORM VALIDATION ---
    document.getElementById('patient-details-form').addEventListener('submit', function(e) {
        const full = iti.getNumber();
        const valid = iti.isValidNumber();
        const national = full.replace(/\D/g, '').slice(-10);
        const pattern = /^[6-9]\d{9}$/;

        if (!valid || !pattern.test(national)) {
            e.preventDefault();
            alert("Please enter a valid Indian mobile number before submitting.");
            return;
        }

        phoneInput.value = full;
    });

});
</script>
@endpush
