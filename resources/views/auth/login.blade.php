@extends('layouts.app')

@section('content')
@include('alerts')
<form method="POST" action="{{route('admin.adminlogin.verification')}}" autocomplete="off">
@csrf
    <div class="mb-4">
            <input id="email" type="email" class="form-control" name="email" value="" required placeholder="Email" autocomplete="email" >

        @error('email')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
    <div class="mb-3">
         <input id="password" type="password" class="form-control" name="password" required placeholder="Password" autocomplete="new-password" >

        @error('password')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
    <div class="d-grid gap-2 col mt-5">
        <button type="submit" class="btn btn-danger">Login
        </button>
    </div>
</form>
@endsection
@push('scripts')

@endpush
