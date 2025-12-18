@extends('template_v1')

@section('content')

<div class="tt-posts">
    <div class="d-flex justify-content-between tt-wrap bg-white mb-3">
        <div class="p-2 bd-highlight">
            <h5 class="mb-0 pb-0">{{ $pageTitle ?? '' }}</h5>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <form id="patient-form" method="POST" action="{{ isset($patient) ? route('patients.update', $patient->id) : route('patients.store') }}">
                
<input type="hidden" name="action" value="{{ $action??'default' }}">   
                @csrf
            

                    <input type="hidden" name="id" value="{{ $patient->id ?? '' }}">

<!-- Phone -->
<div class="mb-3">
    <label class="fw-semibold">Phone <span class="text-danger">*</span></label><br>
   <input type="tel"
       id="phone"
       name="phone"
       class="form-control"
       value="{{ $patient->mobile ?? '' }}"
       data-country-code="{{ $patient->country_code ?? '' }}"
       data-phone="{{ $patient->mobile ?? '' }}">
<!-- Hidden inputs for submission -->
<input type="hidden" name="country_code" id="country_code" value="{{ $patient->country_code ?? '' }}">
<input type="hidden" name="phone_number" id="phone_number" value="{{ $patient->mobile ?? '' }}">

</div>
                    <!-- Name -->
                    <div class="mb-3">
                        <label class="fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ $patient->name ?? '' }}" required>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label class="fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ $patient->email ?? '' }}">
                    </div>

                    <!-- Age -->
                    <div class="mb-3">
                        <label class="fw-semibold">Age <span class="text-danger">*</span></label>
                        <input type="number" name="age" class="form-control" value="{{ $patient->age ?? '' }}" required>
                    </div>

                    <!-- Gender -->
                    <div class="mb-3">
                        <label class="fw-semibold">Gender <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3 mt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="gender" value="M" {{ (isset($patient) && $patient->gender=='M') ? 'checked' : '' }}>
                                <label class="form-check-label">Male</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="gender" value="F" {{ (isset($patient) && $patient->gender=='F') ? 'checked' : '' }}>
                                <label class="form-check-label">Female</label>
                            </div>
                        </div>
                    </div>

                    <!-- Booking For -->
                    <div class="mb-3">
                        <label class="fw-semibold">Booking For <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            @php $bfOptions = ['Self','Spouse','Parent','Child','Others']; @endphp
                            @foreach($bfOptions as $opt)
                                <div class="form-check">
                                    <input class="form-check-input bookingfor" type="radio" name="bookingfor" value="{{ $opt }}" {{ (isset($patient) && $patient->bookingfor==$opt) ? 'checked' : '' }}>
                                    <label class="form-check-label">{{ $opt }}</label>
                                </div>
                            @endforeach
                        </div>
                        <input type="text" name="other_reason" id="other_reason" class="form-control mt-2" placeholder="Specify other"
                               value="{{ $patient->other_reason ?? '' }}"
                               style="{{ (isset($patient) && $patient->bookingfor=='Others') ? '' : 'display:none;' }}">
                    </div>

                

                    <!-- Submit -->
<button type="submit" class="btn btn-brand">
    @if(isset($patient))
        Update
    @elseif(($action ?? '') === 'appointment')
        Save & Continue
    @else
        Save
    @endif
</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function(){
    $('.bookingfor').on('change', function(){
        if ($(this).val() === 'Others') {
            $('#other_reason').show().attr('required', true);
        } else {
            $('#other_reason').hide().attr('required', false).val('');
        }
    });
});
</script>
<script>
$(document).ready(function () {

    /* =====================================================
       INIT PHONE INPUT
    ===================================================== */
    var phoneInput = document.querySelector("#phone");

    var iti = window.intlTelInput(phoneInput, {
        separateDialCode: true,
        preferredCountries: ["in"],
        initialCountry: "in",
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"
    });

    /* =====================================================
       UPDATE HIDDEN FIELDS (SINGLE SOURCE OF TRUTH)
    ===================================================== */
    function updateHiddenPhoneFields() {

        if (typeof intlTelInputUtils === "undefined") return;

        var countryData = iti.getSelectedCountryData();
        if (!countryData) return;

        var dialCode = countryData.dialCode;

        var e164 = iti.getNumber(intlTelInputUtils.numberFormat.E164);
        if (!e164) return;

        var phoneNumber = e164
            .replace('+' + dialCode, '')
            .replace(/\D/g, '')
            .replace(/^0+/, '');

        $('#country_code').val(parseInt(dialCode));
        $('#phone_number').val(phoneNumber);
    }

    /* =====================================================
       PREFILL FOR EDIT (SAFE WAY)
    ===================================================== */
    setTimeout(function () {

        var savedCountryCode = $('#phone').data('country-code');
        var savedPhone = $('#phone').data('phone');

        if (savedCountryCode) {
            iti.getCountryData().forEach(function (c) {
                if (parseInt(c.dialCode) === parseInt(savedCountryCode)) {
                    iti.setCountry(c.iso2);
                }
            });
        }

        if (savedPhone) {
            iti.setNumber('+' + savedCountryCode + savedPhone);
        }

        updateHiddenPhoneFields();

    }, 500); // wait for utils.js

    /* =====================================================
       SYNC ON USER ACTION
    ===================================================== */
    $('#phone').on('keyup blur change', updateHiddenPhoneFields);

    phoneInput.addEventListener('countrychange', updateHiddenPhoneFields);

    /* =====================================================
       BOOKING FOR - OTHERS TOGGLE
    ===================================================== */
    $('.bookingfor').on('change', function () {
        if ($(this).val() === 'Others') {
            $('#other_reason').show().attr('required', true);
        } else {
            $('#other_reason').hide().attr('required', false).val('');
        }
    });

    /* =====================================================
       JQUERY VALIDATION
    ===================================================== */
    $.validator.addMethod("phoneIntl", function () {
        return iti.isValidNumber();
    }, "Please enter a valid phone number");

    $('#patient-form').validate({
        ignore: [],
        rules: {
            phone: {
                required: true,
                phoneIntl: true
            },
            name: {
                required: true,
                minlength: 2
            },
            email: {
                email: true
            },
            age: {
                required: true,
                digits: true,
                min: 0,
                max: 120
            },
            gender: {
                required: true
            },
            bookingfor: {
                required: true
            },
            other_reason: {
                required: function () {
                    return $('input[name="bookingfor"]:checked').val() === 'Others';
                },
                minlength: 2
            }
        },
        messages: {
            phone: {
                required: "Please enter phone number"
            },
            name: {
                required: "Please enter name",
                minlength: "Minimum 2 characters"
            },
            age: {
                required: "Please enter age"
            }
        },
        errorElement: 'div',
        errorClass: 'text-danger mt-1',
        highlight: function (el) {
            $(el).addClass('is-invalid');
        },
        unhighlight: function (el) {
            $(el).removeClass('is-invalid');
        },
        submitHandler: function (form) {

            // FINAL sync before submit (IMPORTANT)
            updateHiddenPhoneFields();

            form.submit();
        }
    });

});
</script>


@endpush
