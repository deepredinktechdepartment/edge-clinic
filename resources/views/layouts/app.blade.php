<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Edge Clinic') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css?family=Nunito:300,400,600,700,800" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' />

    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
        body {
            background: linear-gradient(135deg, #e2ecff 0%, #ffffff 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Nunito', sans-serif;
        }

        .login-card {
            background: #ffffff;
            width: 400px;
            border-radius: 20px;
            padding: 40px 35px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            text-align: center;
        }

        .login-card img {
            width: 120px;
            margin-bottom: 20px;
        }

        .login-card h2 {
            font-weight: 800;
            margin-bottom: 5px;
        }

        .login-card p {
            color: #6c757d;
            margin-bottom: 25px;
        }

        .form-control {
            height: 48px;
            border-radius: 12px;
            border: 1px solid #ced4da;
        }

        .btn-login {
            width: 100%;
            height: 48px;
            border-radius: 12px;
            background: #0056b3;
            color: #fff;
            font-weight: 600;
            transition: .3s;
        }

        .btn-login:hover {
            background: #003f87;
        }

        .good-msg {
            font-size: 22px;
            font-weight: 700;
            margin-top: 10px;
            color: #1b1b1b;
        }
    </style>
</head>

<body>

    <div class="login-card">

        <img src="{{ URL::to('assets/img/SH-Final-Logo.png') }}" alt="Logo">

        {{-- Dynamic Greeting --}}
        @php
            date_default_timezone_set('Asia/Kolkata');
            $h = date('G');
            $greet = $h < 12 ? "Good Morning!" : ($h < 17 ? "Good Afternoon!" : "Good Evening!");
        @endphp

        <h2>{{ $greet }}</h2>
        <p>Welcome back, wishing you a productive day</p>

        {{-- AUTH FORM --}}
        @yield('content')

    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <script>
        @if(Session::has('message'))
            toastr.success("{{ session('message') }}");
        @endif
        @if(Session::has('success'))
            toastr.success("{{ session('success') }}");
        @endif
        @if(Session::has('error'))
            toastr.error("{{ session('error') }}");
        @endif
        @if(Session::has('warning'))
            toastr.warning("{{ session('warning') }}");
        @endif
        @if(Session::has('info'))
            toastr.info("{{ session('info') }}");
        @endif
    </script>

</body>
</html>
