@extends('layouts.bookapp')

@section('title', 'Book Appointment - ' . $doctor->name)

@section('content')
<section class="pt-5 pb-5 bg-light">
    @include('ajax.doctor-appointment', ['doctor' => $doctor, 'slots' => $slots])
</section>
@endsection

@push('scripts')
<script>
function initializeAppointmentModalSliders() {
    $('.dr-appo-date-slider').not('.slick-initialized').slick({
        slidesToShow: 3,
        slidesToScroll: 1,
        arrows: true,
        dots: false,
        infinite: false,
        centerMode: false,
        centerPadding: '0px',
        responsive: [
            {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 2,
                    arrows: true,
                    infinite: false
                }
            },
            {
                breakpoint: 576,
                settings: {
                    slidesToShow: 2,
                    arrows: true,
                    infinite: false
                }
            },
            {
                breakpoint: 380,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    arrows: true,
                    infinite: false
                }
            }
        ]
    });

    $('.dr-appo-time-slots-slider').not('.slick-initialized').slick({
        slidesToShow: 6,
        slidesToScroll: 1,
        arrows: true,
        dots: false,
        infinite: false,
        centerMode: false,
        centerPadding: '0px',
        responsive: [
            {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 4,
                    centerMode: false,
                    infinite: false
                }
            },
            {
                breakpoint: 576,
                settings: {
                    slidesToShow: 2,
                    centerMode: false,
                    infinite: false
                }
            },
            {
                breakpoint: 380,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    centerMode: false,
                    infinite: false
                }
            }
        ]
    });
}

document.addEventListener('DOMContentLoaded', function () {
    if (typeof initializeAppointmentModalSliders === 'function') {
        initializeAppointmentModalSliders();
    }

    if (typeof initAppointmentModal === 'function') {
        initAppointmentModal();
    }
});
</script>
@endpush
