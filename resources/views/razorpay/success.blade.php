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

    .btn-red {
        background: #f22804;
        color: #fff;
        border-radius: 30px;
        padding: 10px 28px;
        box-shadow: 0 4px 14px rgba(242,40,4,0.3);
        transition: 0.3s;
    }

    .btn-red:hover {
        background: #000;
        color: #fff;
    }
</style>

<div class="container py-5">

    {{-- LOGO --}}
    <div class="text-center mb-4">
        <img src="https://edgeclinic.in/assets/images/logo.png"
             alt="Edge Clinic"
             class="img-fluid"
             style="max-width: 180px;">
    </div>

    {{-- SUCCESS CARD --}}
    <div class="card shadow-lg border-0 success-box">
        <div class="card-body p-5 text-center">

            <div class="success-icon mx-auto">
                <i class="ri-check-line"></i>
            </div>

            <h3 class="fw-bold text-success mb-2">Payment Successful!</h3>
            <p class="text-muted mb-4">
                Thank you! Your payment has been processed successfully.
            </p>

            {{-- PAYMENT SUMMARY --}}
            <div class="info-card">
                <h6><i class="ri-bill-line me-1"></i> Transaction Summary</h6>

                <div class="info-grid">
                    <div class="label">Payment ID</div>
                    <div class="value">{{ $paymentDetails['payment_id'] }}</div>

                    <div class="label">Amount</div>
                    <div class="value">₹{{ $paymentDetails['amount'] }} {{ $paymentDetails['currency'] }}</div>

                    <div class="label">Status</div>
                    <div class="value">
                        <span class="badge bg-success">{{ ucfirst($paymentDetails['status']) }}</span>
                    </div>
                </div>
            </div>

            <a href="{{ url('/') }}" class="btn btn-red mt-3">
                <i class="ri-arrow-left-line me-1"></i> Back to Home
            </a>

        </div>
    </div>

    {{-- FOOTER --}}
    <div class="text-center mt-4">
        <small class="text-muted">
            © {{ date('Y') }} Edge Clinic. All rights reserved.
        </small>
    </div>

</div>

@endsection
