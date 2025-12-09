@extends('layouts.bookapp')

@section('title', 'Payment Failed')

@section('content')

<style>
    .failure-box {
        max-width: 600px;
        margin: auto;
    }
    .failure-icon {
        width: 80px;
        height: 80px;
        background: #ffe5e5;
        color: #dc3545;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 38px;
    }
</style>

<div class="container py-5">

    {{-- LOGO --}}
    <div class="text-center mb-4">
        <img src="https://edge.clinic/wp-content/uploads/2025/06/edge_logo.png" 
             class="img-fluid" 
             style="max-width: 180px;" 
             alt="Edge Clinic">
    </div>

    {{-- FAILURE CARD --}}
    <div class="card shadow-lg border-0 failure-box">
        <div class="card-body p-4 text-center">

<div class="failure-icon mx-auto mb-3">
    <i class="fas fa-times"></i>
</div>

            <h3 class="fw-bold text-danger mb-2">Payment Failed</h3>
            <p class="text-muted mb-3">
                Unfortunately, your payment could not be completed.  
                Please try again.
            </p>

            @if(!empty($reason))
                <div class="alert alert-danger small">
                    <strong>Reason:</strong> {{ $reason }}
                </div>
            @endif

            <a href="{{ url('doctors') }}" class="btn btn-danger rounded-pill px-4 mt-2">
                <i class="ri-arrow-go-back-line me-1"></i> Try Again
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
