<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payment Failed</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

  <style>
    body {
      background: linear-gradient(135deg, #fff3f3, #fdecec);
      font-family: 'Poppins', sans-serif;
      color: #333;
    }

    .failure-card {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
      padding: 40px;
      max-width: 650px;
      margin: auto;
      text-align: center;
    }

    .failure-icon {
      width: 90px;
      height: 90px;
      background: #fbd4d4;
      color: #dc3545;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 45px;
      margin: 0 auto 15px;
      animation: shake 0.6s ease;
    }

    @keyframes shake {
      0%, 100% { transform: rotate(0deg); }
      20% { transform: rotate(-10deg); }
      40% { transform: rotate(10deg); }
      60% { transform: rotate(-8deg); }
      80% { transform: rotate(8deg); }
    }

    .btn-custom {
      background: #2b8dfd;
      border: none;
      color: white;
      border-radius: 8px;
      padding: 12px 25px;
      font-weight: 500;
      transition: 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }

    .btn-custom:hover {
      background: #176de0;
    }

    footer {
      margin-top: 40px;
      color: #6c757d;
      font-size: 0.9rem;
    }
    
    .failure-card{
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
    <div class="failure-card">
      <div class="failure-icon">
        <i class="ri-close-line"></i>
      </div>
      <h2 class="text-white fw-bold mb-2">Payment Failed</h2>
      <p class="text-white mb-3">Unfortunately, your transaction could not be completed.</p>

      @if(!empty($reason))
        <div class="alert border-danger text-danger small mx-auto text-white">
          <strong>Reason:</strong> {{ $reason }}
        </div>
      @endif

      <a href="{{url('')}}" class="btn btn-brand mt-3">
        <i class="ri-refresh-line me-1"></i> Try Again
      </a>
    </div>

    <footer class="text-center mt-4">
      <p>© {{ date('Y') }} Bridgegap Consultants. All rights reserved.</p>
    </footer>
  </div>
</body>
</html>
