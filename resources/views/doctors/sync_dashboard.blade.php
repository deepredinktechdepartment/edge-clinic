@extends('template_v1')

@section('content')

<h3>Doctor Sync Dashboard</h3>

<div class="row">

    <div class="col-md-6">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                Doctors in Local DB but NOT in MocDoc
            </div>
            <div class="card-body">
                @forelse($missingInMocdoc as $doctor)
                    <p>{{ $doctor->name }} ({{ $doctor->drKey }})</p>
                @empty
                    <p class="text-success">All doctors synced ✅</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                Doctors in MocDoc but NOT in Local DB
            </div>
            <div class="card-body">
                @forelse($missingInLocal as $doctor)
                    <p>{{ $doctor['doctorname'] ?? '' }} ({{ $doctor['drkey'] ?? '' }})</p>
                @empty
                    <p class="text-success">All doctors synced ✅</p>
                @endforelse
            </div>
        </div>
    </div>

</div>

@endsection