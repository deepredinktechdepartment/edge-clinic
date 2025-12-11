@extends('layouts.bookapp')

@section('title', 'Doctors')

@section('content')



<section class="pt-5 pb-5">
 <div class="container">
    <div class="row g-sm-5 gy-4">

        {{-- Dynamic Doctors --}}
        @if($doctors->count() > 0)

            @foreach($doctors as $doc)
                <div class="col-sm-6">
                    <div class="doctor-card">
                        <div class="row align-items-center">

                       {{-- IMAGE --}}
<div class="col-sm-6 order-sm-0 order-1">

@php
    $photoFile = \GeneralFunctions::doctorImage($doc->photo, "");
@endphp

    <div class="avatar-wrap">
        <img class="avatar"
             src="{{ $photoFile }}"
             alt="{{ $doc->name }}">
    </div>
</div>
                            {{-- BUTTONS --}}
                            <div class="col-sm-6 order-sm-0 order-3">
                                <div class="doctor-actions mt-sm-0 mt-3">

                                    {{-- Profile --}}
                                    <a href="{{ route('doctor.single', $doc->slug) }}"
                                       class="btn btn-profile mb-2">
                                        View Profile
                                    </a>
                                    <!--<a href="#"-->
                                    <!--   class="btn btn-profile mb-2" data-bs-toggle="modal" data-bs-target="#profileModal">-->
                                    <!--    View Profile-->
                                    <!--</a>-->

                                    {{-- Appointment --}}
                                    <a href="{{ route('appointment.book', ['doctor_id' => $doc->id]) }}"
                                       class="btn btn-book">
                                        Book an Appointment
                                    </a>
                                    
                                    <!--<a href="#"-->
                                    <!--   class="btn btn-book" data-bs-toggle="modal" data-bs-target="#appointmentModal">-->
                                    <!--    Book an Appointment-->
                                    <!--</a>-->

                                </div>
                            </div>

                            {{-- DETAILS --}}
                            <div class="col-sm-12 order-sm-0 order-2">
                                <div class="doctor-info mt-3">

                                    <h4 class="dr-name mb-1">{{ $doc->name }}</h4>

                                    @if(!empty($doc->qualification))
                                        <p class="dr-qualification small mb-0">
                                            {{ $doc->qualification }}
                                        </p>
                                    @endif

                                    @if(!empty($doc->designation))
                                        <p class="dr-designation">
                                            {{ $doc->designation }}
                                        </p>
                                    @endif
                             

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach

        @endif

    </div>
  </div>
</section>




<!-- Profile Modal -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header p-0 border-bottom-0">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        
        
        
        
      </div>
    </div>
  </div>
</div>

<!-- Appointment Modal -->
<div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="appointmentModalLabel">Book an Appointment</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        
      </div>
    </div>
  </div>
</div>


@endsection




@push('scripts')
<script>
    $('.dr-appo-date-slider').slick({
        slidesToShow: 3,
        slidesToScroll: 1,
        autoplay: false,
        autoplaySpeed: 3000,
        pauseOnHover: true,
        dots: false,
        fade: false,
        arrows: true,
        infinite: true,
        centerMode: true,
        centerPadding: '0px',
        responsive: [{
            breakpoint: 480,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
                dots: true,
                arrows: false,
            }
        }]
    });

    $('.dr-appo-time-slots-slider').slick({
        slidesToShow: 6,
        slidesToScroll: 1,
        autoplay: false,
        autoplaySpeed: 3000,
        pauseOnHover: true,
        dots: false,
        fade: false,
        arrows: true,
        infinite: true,
        centerMode: true,
        centerPadding: '0px',
        responsive: [{
            breakpoint: 480,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
                dots: true,
                arrows: false,
            }
        }]
    });
</script>
@endpush
