@extends('layouts.app')

@section('content')
@include('alerts')
<style>
.password-toggle-wrap {
    position: relative;
}
.password-toggle-input {
    padding-right: 42px;
}
.password-toggle-btn {
    position: absolute;
    top: 50%;
    right: 12px;
    transform: translateY(-50%);
    border: 0;
    background: transparent;
    color: #6c757d;
    padding: 0;
    line-height: 1;
}
</style>
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
         <div class="password-toggle-wrap">
             <input id="password" type="password" class="form-control password-toggle-input" name="password" required placeholder="Password" autocomplete="new-password" >
             <button type="button" class="password-toggle-btn" data-target="#password" aria-label="Show password">
                 <i class="fa-solid fa-eye"></i>
             </button>
         </div>

        @error('password')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
    <div class="d-grid gap-2 col mt-3">
        <button type="submit" class="btn btn-brand">Login
        </button>
    </div>
</form>
@endsection
@push('scripts')
<script>
$(document).on('click', '.password-toggle-btn', function () {
    const target = $($(this).data('target'));
    const icon = $(this).find('i');
    const isPassword = target.attr('type') === 'password';

    target.attr('type', isPassword ? 'text' : 'password');
    icon.toggleClass('fa-eye fa-eye-slash');
    $(this).attr('aria-label', isPassword ? 'Hide password' : 'Show password');
});
</script>
@endpush
