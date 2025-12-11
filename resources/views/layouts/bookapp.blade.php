<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title')</title>

    <link rel="stylesheet preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" as="style">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.1/css/intlTelInput.css"/>
    <link rel="stylesheet" href="{{ asset('assets/css/doctor.css') }}">
</head>

<body>
    
    <header class="py-3 px-3">
        <div class="container">
            <div class="row align-items-sm-end">
                <div class="col-sm-2 col-6">
                    <a href="#"><img src="https://edge.clinic/wp-content/uploads/2025/06/edge_logo.png" alt="Edge Clinic Logo" class="img-fluid" width="130"></a>
                </div>
                <div class="col-sm-10 col-6">
                    <nav class="navbar navbar-expand-lg p-0">
                      <div class="container-fluid p-0 justify-content-end">
                        <button class="navbar-toggler mt-3" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                          <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                          <ul class="navbar-nav ms-auto mb-0 mb-lg-0 p-0">
                            <li class="nav-item">
                              <a class="nav-link active" aria-current="page" href="{{url('doctors')}}">Home</a>
                            </li>
                            <li class="nav-item">
                              <a class="nav-link" href="{{url('doctors')}}">Doctors</a>
                            </li>
                            <!--<li class="nav-item dropdown">-->
                            <!--  <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">-->
                            <!--    Dropdown-->
                            <!--  </a>-->
                            <!--  <ul class="dropdown-menu" aria-labelledby="navbarDropdown">-->
                            <!--    <li><a class="dropdown-item" href="#">Action</a></li>-->
                            <!--    <li><a class="dropdown-item" href="#">Another action</a></li>-->
                            <!--  </ul>-->
                            <!--</li>-->
                          </ul>
                        </div>
                      </div>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    @yield('content')
    
    <footer>
    <div class="container">
        <div class="row">
            <div class="col-sm-6">
                <div class="mb-4">
                    <img src="https://edge.clinic/wp-content/uploads/2025/06/edge_logo.png" alt="Edge Clinic" class="img-fluid" width="150">
                </div>
                <div>
                    <h4>Connect with Us</h4>
                    <ul class="mt-3 mb-3">
                        <li><a href="tel:+91 6303285050"><i class="fa-solid fa-phone"></i> &nbsp; &nbsp; +91 6303285050</a></li>
                        <li><a href="tel:+91 9908085050"><i class="fa-brands fa-whatsapp"></i> &nbsp; &nbsp; +91 9908085050</a></li>
                    </ul>
                    <p><b>Hyderabad</b> | Pune* | Bengaluru* *Upcoming</p>
                </div>
                <div class="mt-4 mb-4">
                    <ul class="social-links">
                        <li><a href="#"><i class="fa-brands fa-facebook"></i></a></li>
                        <li><a href="#"><i class="fa-brands fa-instagram"></i></a></li>
                        <li><a href="#"><i class="fa-brands fa-linkedin"></i></a></li>
                        <li><a href="#"><i class="fa-brands fa-youtube"></i></a></li>
                    </ul>
                </div>
                <div class="mb-4">
                    <h4>Corporate Address:</h4>
                    <p>24th Floor, One West, Financial District,<br>
                    Nanakaramguda, Makthakousarali,<br>
                    Telangana 500008</p>
                </div>
                <div class="mb-4">
                    <h4>Registered Office:</h4>
                    <p>4th Floor, 8-2-293/82/A/1355-H/403<br>
                    Niharika Jubilee One, Road No 1, Jubilee Hills<br>
                    Hyderabad, Telangana- 500033</p>
                </div>
                <div>
                    <h4>Registered Office Phone No:</h4>
                    <p>+91 6302162484</p>
                </div>
            </div>
            
            <div class="col-sm-6">
                <p><b>Edge Clinic at HITEC City, Hyderabad</b><br>
                4th Floor, The Medical Centre, HITEC City</p>
                <div class="mt-4">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d30451.095783169894!2d78.376525!3d17.441183!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bcb93e4be46997b%3A0x352bcb4922f7dc4f!2sThe%20Medical%20Centre%20(TMC)%2C%20HITEC%20City!5e0!3m2!1sen!2sin!4v1765343476683!5m2!1sen!2sin" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
                <div>
                    <p>Copyrights 2025 (C) Edge | <a href="#">Privacy Policy</a></p>
                </div>
            </div>
        </div>
    </div>
</footer>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- Bootstrap JS (optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.1/js/intlTelInput.min.js"></script>


    <!-- Stack for page-specific scripts -->
    @stack('scripts')

</body>
</html>
