@extends('layouts.bookapp')

@section('title', 'Patient Details')

@section('content')
<div class="container py-5">

    {{-- Page Title --}}
    <h3 class="mb-4 fw-bold">Patient Details</h3>

    <div class="row g-4">

        {{-- Left Side --}}
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-body">

                    <form id="patient-details-form" method="POST" action="{{ url('razorpay/create-order') }}">
                        @csrf

                        {{-- First Name --}}
                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>

                        {{-- Last Name --}}
                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>

                        {{-- Email --}}
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        {{-- Phone Number --}}
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-control" required>
                            <input type="hidden" name="country_code" id="country_code">
                        </div>

                        {{-- OTP --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Phone Verification</label>
                            <div class="input-group">
                                <input type="text" id="otp" class="form-control" placeholder="Enter OTP" required>
                                <button class="btn btn-outline-primary" type="button" id="sendOtpBtn">
                                    Send OTP
                                </button>
                            </div>
                        </div>

                        {{-- Appointment Fee --}}
                        <div class="mb-4 fw-semibold">
                            Appointment Fee: ₹{{ $appointmentFee }}
                        </div>

                        {{-- Continue Button --}}
                        <button type="submit" class="btn btn-book w-100 py-2">
                            Continue to Payment
                        </button>

                        <input type="hidden" name="industry" value="hospital-clinic">
                    </form>

                </div>
            </div>
        </div>

        {{-- Appointment Summary --}}
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-header btn-book text-white fw-bold">
                    Appointment Details
                </div>

                <div class="card-body">

                    <p class="mb-2">
                        <strong>Date:</strong>
                        {{ \Carbon\Carbon::parse($appointmentData['appointment_date'])->format('d M Y') }}
                    </p>

                    <p class="mb-2">
                        <strong>Time:</strong>
                        {{ $appointmentData['appointment_time'] }}
                    </p>

                    {{-- Doctor Details --}}
                    @php
                        $doctorData = isset($appointmentData['doctor'])
                            ? json_decode($appointmentData['doctor'], true)
                            : null;
                    @endphp

                    @if($doctorData)
                        <hr>
                        <h6 class="fw-bold mb-2">Doctor Information</h6>

                        <p class="mb-1">
                            <strong>Name:</strong> {{ $doctorData['name'] ?? 'N/A' }}
                        </p>

                        <p class="mb-2">
                            <strong>Designation:</strong> {{ $doctorData['designation'] ?? 'N/A' }}
                        </p>
                    @endif

                    <p class="mb-0 mt-2">
                        <strong>Fee:</strong> ₹{{ $appointmentFee }}
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
        const data = iti.getSelectedCountryData();
        document.getElementById('country_code').value = "+" + data.dialCode;
    }

    phoneInput.addEventListener('countrychange', updateCountryCode);
    phoneInput.addEventListener('blur', updateCountryCode);

    // --- OTP SEND ---
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
