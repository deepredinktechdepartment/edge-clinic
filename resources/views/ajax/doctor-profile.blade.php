<div class="row">
    <div class="col-md-4">
        <img src="{{ GeneralFunctions::doctorImage($doctor->photo, '') }}" class="img-fluid rounded">
    </div>

    <div class="col-md-8">
        <h4>{!! $doctor->name !!}</h4>
        <p>{!! $doctor->qualification !!}</p>
        <p>{!! $doctor->designation !!}</p>
        <p>{!! $doctor->bio ?? '' !!}</p>
    </div>
</div>
