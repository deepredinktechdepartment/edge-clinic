@extends('layouts.bookapp')

@section('title', 'Patient Details')

@section('content')
<div class="container py-5">

    {{-- Page Title --}}
    <h3 class="mb-4 fw-bold">Patient Details</h3>

    <div class="row g-4">

        {{-- Left Side: Form --}}
        <div class="col-md-7">
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

                        {{-- Phone --}}
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" required>
                        </div>

<div class="mb-3">
    <label class="form-label fw-bold">Phone Verification</label>

    <div class="input-group">
        <input type="text" class="form-control" placeholder="Enter OTP" id="otp">

        <button class="btn btn-outline-primary" type="button" id="sendOtpBtn">
            Send OTP
        </button>
    </div>
</div>



                        {{-- Fee --}}
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

        {{-- Right Side: Appointment Summary --}}
        <div class="col-md-5">
            <div class="card shadow-sm border-0">

                <div class="card-header btn-book text-white fw-bold">
                    Appointment Details
                </div>

                <div class="card-body">
                    <p class="mb-2">
                        <strong>Date: </strong> {{ $appointmentDate }}
                    </p>
                    <p class="mb-0">
                        <strong>Fee: </strong> ₹{{ $appointmentFee }}
                    </p>
                </div>

            </div>
        </div>

    </div>
</div>

{{-- OTP Simulation --}}
<script>
document.getElementById('send-otp').addEventListener('click', function() {
    alert('OTP sent to your phone (simulation)');
});
</script>

@endsection
