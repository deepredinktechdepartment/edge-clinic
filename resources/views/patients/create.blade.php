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
<div id="patientPicker" class="mt-2 d-none">
    <label class="fw-semibold mb-1">Select Patient</label>
    <div id="patientList" class="list-group"></div>
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

    <div class="gender-group">
        <div class="d-flex gap-3 mt-1">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="gender" value="M">
                <label class="form-check-label">Male</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="gender" value="F">
                <label class="form-check-label">Female</label>
            </div>
        </div>
    </div>
</div>
                    <!-- Booking For -->
                 <!-- Booking For -->
<div class="mb-3">
    <label class="fw-semibold">
        Booking For <span class="text-danger">*</span>
    </label>

    <div class="bookingfor-group">
        <div class="d-flex flex-wrap gap-3 mt-1">
            @php $bfOptions = ['Self','Spouse','Parent','Child','Others']; @endphp
            @foreach($bfOptions as $opt)
                <div class="form-check">
                    <input
                        class="form-check-input bookingfor"
                        type="radio"
                        name="bookingfor"
                        value="{{ $opt }}"
                          {{ (isset($patient) && $patient->bookingfor == $opt) ? 'checked' : '' }}
                    {{ (!isset($patient) && $opt == 'Self') ? 'checked' : '' }}
                    >
                    <label class="form-check-label">{{ $opt }}</label>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Others input -->
    <input
        type="text"
        name="other_reason"
        id="other_reason"
        class="form-control mt-2"
        placeholder="Specify other"
        value="{{ $patient->other_reason ?? '' }}"
        style="{{ (isset($patient) && $patient->bookingfor == 'Others') ? '' : 'display:none;' }}"
    >
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
    let phoneLookupTimer = null;
let lastPhoneChecked = null;

function fetchPatientsByPhone() {

    updateHiddenPhoneFields();

    let countryCode = $('#country_code').val();
    let phone = $('#phone_number').val();

    if (!countryCode || !phone || phone.length < 6) {
        $('#patientPicker').addClass('d-none');
        return;
    }

    let key = countryCode + phone;
    if (key === lastPhoneChecked) return;

    lastPhoneChecked = key;

    $.get("{{ url('/patients/by-phone') }}", {
        country_code: countryCode,
        phone: phone
    })
    .done(function (res) {

        // No patient → new flow
        if (res.count === 0) {
            $('#patientPicker').addClass('d-none');
            return;
        }

        // ONLY ONE → auto-fill (NO picker)
        if (res.count === 1) {
            prefillPatient(res.patients[0]);
            $('#patientPicker').addClass('d-none');
            return;
        }

        // MULTIPLE → show picker
        renderPatientPicker(res.patients);
    });
}
function prefillPatient(patient) {

    $('input[name="id"]').val(patient.id);
    $('input[name="name"]').val(patient.name);
    $('input[name="email"]').val(patient.email);
    $('input[name="age"]').val(patient.age);

    $('input[name="gender"][value="'+patient.gender+'"]').prop('checked', true);

    $('input[name="bookingfor"][value="'+patient.bookingfor+'"]')
        .prop('checked', true)
        .trigger('change');

    if (patient.bookingfor === 'Others') {
        $('#other_reason').val(patient.other_reason).show();
    }
}

function renderPatientPicker(patients) {

    let list = $('#patientList');
    list.html('');
    $('#patientPicker').removeClass('d-none');

    patients.forEach(p => {
        let item = $(`
            <button type="button"
                class="list-group-item list-group-item-action">
                <strong>${p.name}</strong>
                <small class="text-muted ms-2">Age: ${p.age}</small>
            </button>
        `);

        item.on('click', function () {
            prefillPatient(p);
            $('#patientPicker').addClass('d-none');
        });

        list.append(item);
    });
}

$('#phone').on('keyup blur change', function () {
    clearTimeout(phoneLookupTimer);
    phoneLookupTimer = setTimeout(fetchPatientsByPhone, 600);
});

phoneInput.addEventListener('countrychange', function () {
    clearTimeout(phoneLookupTimer);
    phoneLookupTimer = setTimeout(fetchPatientsByPhone, 600);
});


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
            max: 100
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
        },
        gender: {
            required: "Please select gender"
        },
        bookingfor: {
            required: "Please select booking for"
        }
    },
    errorElement: 'div',
    errorClass: 'text-danger mt-1',

    /* 🔥 THIS FIXES ALIGNMENT */
errorPlacement: function (error, element) {

    if (element.attr("name") === "gender") {
        error.appendTo($('.gender-group'));
    }
    else if (element.attr("name") === "bookingfor") {
        error.appendTo($('.bookingfor-group'));
    }
    else {
        error.insertAfter(element);
    }
},

highlight: function (element) {

    if ($(element).attr('type') === 'radio') {
        $(element).closest('.mb-3').addClass('has-error');
    } else {
        $(element).addClass('is-invalid');
    }
},

unhighlight: function (element) {

    if ($(element).attr('type') === 'radio') {
        $(element).closest('.mb-3').removeClass('has-error');
    } else {
        $(element).removeClass('is-invalid');
    }
},

    submitHandler: function (form) {
        updateHiddenPhoneFields();
        form.submit();
    }
});

});
</script>


@endpush
