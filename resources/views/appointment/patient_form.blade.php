@extends('layouts.bookapp')

@section('title', 'Patient Details')

@section('content')
<div class="container mt-5">
    <h3 class="mb-4 text-primary fw-bold">Patient Details</h3>

    <div class="row g-4">
        {{-- Left Side: Form --}}
        <div class="col-md-7">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form id="patient-details-form" method="POST" action="{{url('razorpay/create-order')}}">
                        @csrf

                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" id="first_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" id="last_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" name="phone" id="phone" required>
                        </div>

                        {{-- OTP Verification --}}
                        <div class="mb-3 d-flex align-items-center">
                            <button type="button" class="btn btn-outline-primary me-2" id="send-otp">Send OTP</button>
                            <input type="text" class="form-control" name="otp" id="otp" placeholder="Enter OTP" required>
                        </div>

                        {{-- Appointment Fee --}}
                        <div class="mb-4 fw-semibold text-dark">
                            Appointment Fee: ₹{{ $appointmentFee }}
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Continue to Payment</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Right Side: Appointment Info --}}
        <div class="col-md-5">
            <div class="card shadow-sm border-primary">
                <div class="card-header bg-primary text-white fw-bold">
                    Appointment Details
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Date:</strong> {{ $appointmentDate }}</p>
                    <p class="mb-0"><strong>Fee:</strong> ₹{{ $appointmentFee }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Simple JS to simulate OTP --}}
<script>
document.getElementById('send-otp').addEventListener('click', function() {
    alert('OTP sent to your phone (simulation)');
});
</script>
@endsection
