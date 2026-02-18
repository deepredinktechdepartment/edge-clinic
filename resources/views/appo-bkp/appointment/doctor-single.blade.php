@extends('layouts.bookapp')

@section('title', $doctor->name)

@section('content')

<section class="pt-5 pb-5 dr-single-view">
    <div class="container">
        <div class="row">

            <div class="col-sm-12">
                <div class="doctor-card p-sm-5">
                    <div class="row align-items-center">

                        {{-- Doctor Image --}}
                        <div class="col-sm-6 order-sm-0 order-1">
                            <div class="avatar-wrap">

                                @php
                                    $photoFile = \GeneralFunctions::doctorImage($doctor->photo, "");
                                @endphp

                                <img class="avatar"
                                     src="{{ $photoFile }}"
                                     alt="{{ $doctor->name }}">
                            </div>
                        </div>

                        {{-- Appointment Button --}}
                        <div class="col-sm-6 order-sm-0 order-3">
                            <div class="doctor-actions mt-sm-0 mt-3 text-center">
                                <a href="{{ route('appointment.book', ['doctor_id' => $doctor->id]) }}"
                                   class="btn btn-book btn-book">
                                    Book an Appointment
                                </a>
                            </div>
                        </div>

                        {{-- Doctor Details --}}
                        <div class="col-sm-12 order-sm-0 order-2">
                            <div class="doctor-info mt-3">

                                <h4 class="dr-name mb-1">{{ $doctor->name }}</h4>

                                @if(!empty($doctor->qualification))
                                    <p class="dr-qualification mb-0">
                                        {{ $doctor->qualification }}
                                    </p>
                                @endif

                                @if(!empty($doctor->designation))
                                    <p class="dr-designation">
                                        {{ $doctor->designation }}
                                    </p>
                                @endif
                            </div>

                            {{-- Bio --}}
                            @if(!empty($doctor->bio))
                                <div class="doctor-bio mt-3">
                                    <p class="mb-0">
                                        {!! $doctor->bio??'' !!}
                                    </p>
                                </div>
                            @endif

                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

@endsection
