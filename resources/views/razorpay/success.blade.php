@extends('layouts.bookapp')

@section('title', 'Payment Successful')

@section('content')

<style>
    body {
        font-family: 'Poppins', sans-serif;
    }

    .success-box {
        max-width: 650px;
        margin: auto;
        border-radius: 16px;
        border-top: 5px solid #22c55e;
    }

    .success-icon {
        width: 90px;
        height: 90px;
        background: #d1f7d6;
        color: #22c55e;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 45px;
        margin-bottom: 15px;
        animation: pop 0.7s ease;
    }

    @keyframes pop {
        0% { transform: scale(0.45); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }

    .info-card {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 15px 20px;
        margin-bottom: 15px;
        text-align: left;
        border: 1px solid #e9ecef;
    }

    .info-card h6 {
        color: #f22804;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 6px 15px;
        font-size: 0.95rem;
    }

    .label {
        font-weight: 500;
        color: #555;
    }

    .value {
        color: #222;
    }

  
</style>

<div class="container py-5">


    {{-- SUCCESS CARD --}}
    <div class="card shadow-lg border-0 success-box">
        <div class="card-body p-5 text-center">

            {{-- SUCCESS ICON --}}
            <div class="success-icon mx-auto text-center mb-4">
                <i class="fas fa-check-circle"></i>
            </div>

            {{-- SUCCESS MESSAGE --}}
            <h3 class="fw-bold text-success mb-4">Payment Successful!</h3>
      

            {{-- PAYMENT SUMMARY --}}
            <div class="info-card">
                <!-- <h6><i class="fas fa-file-invoice-dollar me-2"></i>Transaction Summary</h6> -->

                <div class="info-grid">
                    <div class="label">Payment ID</div>
                    <div class="value">{{ $paymentDetails['payment_id'] }}</div>

                    <div class="label">Amount</div>
                    <div class="value">₹{{ $paymentDetails['amount'] }} {{ $paymentDetails['currency'] }}</div>

                    <!-- <div class="label">Status</div>
                    <div class="value">
                     @php
    $status = strtolower($paymentDetails['status']);
    $isSuccess = in_array($status, ['authorized', 'captured']);
@endphp

<span class="badge bg-success">
    {{ $isSuccess ? 'Success' : ucfirst($paymentDetails['status']) }}
</span>

                    </div> -->
                    
    {{-- Show Appointment Key if exists --}}
<div class="label">Booking ID</div>
<div class="value">
    @if(!empty($paymentDetails['apptkey']))
        <span class="badge bg-success">
            {{ $paymentDetails['apptkey'] }}
        </span>
    @else
        <span class="text-muted">
            Booking ID not generated
        </span>
    @endif
</div>


                </div>
            </div>

            <a href="{{ url('for-patients') }}" class="btn btn-book mt-3">
                <i class="fas fa-arrow-left me-2"></i> Back to Home
            </a>

        </div>
    </div>



</div>

@endsection
