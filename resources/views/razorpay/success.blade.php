<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payment Successful</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

  <style>
    body {
      background: linear-gradient(135deg, #e3f2fd, #f8f9fa);
      font-family: 'Poppins', sans-serif;
      color: #333;
    }

    .success-card {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
      padding: 40px;
      max-width: 700px;
      margin: auto;
      text-align: center;
    }

    .success-icon {
      width: 90px;
      height: 90px;
      background: #d1f7d6;
      color: #22c55e;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 45px;
      margin: 0 auto 15px;
      animation: pop 0.6s ease;
    }

    @keyframes pop {
      0% { transform: scale(0.5); opacity: 0; }
      100% { transform: scale(1); opacity: 1; }
    }

    .info-card {
      background: #f8f9fa;
      border-radius: 12px;
      padding: 15px 20px;
      margin-bottom: 15px;
      text-align: left;
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

    .btn-custom {
      background: #2b8dfd;
      border: none;
      color: white;
      border-radius: 8px;
      padding: 12px 25px;
      font-weight: 500;
      transition: 0.3s ease;
    }

    .btn-custom:hover {
      background: #176de0;
    }

    footer {
      margin-top: 40px;
      color: #6c757d;
      font-size: 0.9rem;
    }
    .success-card{
        background: linear-gradient(-125deg, #0F131B 0%, #31151C 100%);
    }
    .btn.btn-brand {
        flex-grow: 0;
        padding: 8px 25px 8px 25px;
        border-radius: 45.7px;
        box-shadow: 0 4px 14px 0 rgba(230, 0, 0, 0.29);
        background-color: #f22804;
        color: #fff;
    }
    .btn.btn-brand:hover {
        background: #000;
    }
  </style>
</head>

<body class="py-5">
  <div class="container">
      
    <div class="text-center mb-4">
        <img src="https://bridgegapconsultants.com/wp-content/uploads/elementor/thumbs/new-logo-bg-rbxjxzgvu84ayxnpre7j8z22ixokxui9dzepkqi6zw.png" alt="Bridgegap Consultants" class="img-fluid logo-img"/>
    </div>
    <div class="success-card">
      <div class="success-icon">
        <i class="ri-check-line"></i>
      </div>
      <h2 class="text-success fw-bold mb-2 text-white">Payment Successful!</h2>
      <p class="mb-4 text-white">Thank you for your payment. Your transaction has been completed successfully.</p>

      <!-- Payment Summary -->
      <div class="info-card">
        <h6><i class="ri-bill-line me-1"></i> Transaction Summary</h6>
        <div class="info-grid">
          <div class="label">Payment ID</div>
          <div class="value">{{ $paymentDetails['payment_id'] }}</div>

          <div class="label">Amount</div>
          <div class="value">₹{{ $paymentDetails['amount'] }} {{ $paymentDetails['currency'] }}</div>

          <div class="label">Status</div>
          <div class="value"><span class="badge bg-success">{{ ucfirst($paymentDetails['status']) }}</span></div>
        </div>
      </div>

      <!-- Customer Info -->
      <!--<div class="info-card">-->
      <!--  <h6><i class="ri-user-line me-1"></i> Customer Information</h6>-->
      <!--  <div class="info-grid">-->
      <!--    <div class="label">Name</div>-->
      <!--    <div class="value">{{ $paymentDetails['name'] }}</div>-->

      <!--    <div class="label">Email</div>-->
      <!--    <div class="value">{{ $paymentDetails['email'] }}</div>-->

      <!--    <div class="label">Phone</div>-->
      <!--    <div class="value">{{ $paymentDetails['phone'] }}</div>-->
      <!--  </div>-->
      <!--</div>-->

      <!-- Business Info -->
      <!--<div class="info-card">-->
      <!--  <h6><i class="ri-building-4-line me-1"></i> Business Details</h6>-->
      <!--  <div class="info-grid">-->
      <!--    <div class="label">Industry</div>-->
      <!--    <div class="value">{{ $paymentDetails['industry'] }}</div>-->

      <!--    <div class="label">Firm Type</div>-->
      <!--    <div class="value">{{ $paymentDetails['firmtype'] }}</div>-->

      <!--    <div class="label">Business Name</div>-->
      <!--    <div class="value">{{ $paymentDetails['businessname'] }}</div>-->

      <!--    <div class="label">Employees</div>-->
      <!--    <div class="value">{{ $paymentDetails['employees'] }}</div>-->
      <!--  </div>-->
      <!--</div>-->

      <a href="{{ url('/') }}" class="btn btn-brand mt-3">
        <i class="ri-arrow-left-line me-1"></i> Back to Home
      </a>
    </div>

      <footer class="text-center mt-4">
      <p>© {{ date('Y') }} Bridgegap Consultants. All rights reserved.</p>
    </footer>
  </div>
</body>
</html>
