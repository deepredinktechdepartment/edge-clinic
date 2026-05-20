@extends('layouts.bookapp')

@section('title', 'Patient Details')
@php
$doctor = json_decode($doctor, true);
@endphp
@section('content')

<style>
.appointment-summary-card {
    background: linear-gradient(180deg, #e6f8ff 0%, #d3f0fb 100%);
    border: 1px solid #a9dff0;
    border-radius: 16px;
    color: #084c61;
}

.payment-choice-list {
    display: grid;
    gap: 12px;
}

.payment-choice-card {
    position: relative;
    display: block;
    border: 1px solid #b9dceb;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.7);
    padding: 14px 16px 14px 44px;
    cursor: pointer;
    transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
}

.payment-choice-card:hover {
    border-color: #5daecc;
    box-shadow: 0 10px 24px rgba(8, 76, 97, 0.08);
    transform: translateY(-1px);
}

.payment-choice-card.is-selected {
    border-color: #1b8db3;
    background: #ffffff;
    box-shadow: 0 12px 28px rgba(27, 141, 179, 0.14);
}

.payment-choice-radio {
    position: absolute;
    top: 18px;
    left: 16px;
}

.payment-choice-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.payment-choice-title {
    font-weight: 700;
    color: #084c61;
}

.payment-choice-amount {
    min-width: 90px;
    text-align: right;
    font-weight: 700;
    color: #053b4d;
}

.payment-choice-subtitle {
    margin-top: 4px;
    font-size: 0.92rem;
    color: #4c6f7b;
}

.summary-currency {
    white-space: nowrap;
}
</style>

<div class="container py-5">
    <div class="row justify-content-center">

        <div class="col-md-7">
            <h4 class="fw-bold text-center mb-3">Patient Registration</h4>
            <p class="text-muted mb-0 text-center mb-3">Enter your details to book an appointment</p>
            <div class="doctor-card">
                <div class="card-body p-4">

                    <form id="patient-form" method="POST" action="{{ route('patient.register') }}">
                        @csrf
                        <input type="hidden" name="patient_id" id="patient_id" value="">

                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                Phone Number <span class="text-danger">*</span>
                            </label>

                            <div class="row g-2">
                                <div class="col-md-7 position-relative">
                                    <input type="tel"
                                           id="phone"
                                           class="form-control pe-5"
                                           placeholder="Enter phone number"
                                           required>
                                    <small id="phoneError" class="invalid-feedback d-block"></small>

                                    <button type="button"
                                            id="sendOtpBtn"
                                            class="btn btn-inputright">
                                        Send OTP
                                    </button>
                                </div>

                                <div class="col-md-5 position-relative">
                                    <input type="text"
                                           id="otp"
                                           class="form-control pe-5"
                                           placeholder="Enter OTP"
                                           value=""
                                           maxlength="6" required>

                                    <button type="button"
                                            id="verifyOtpBtn"
                                            class="btn btn-inputright">
                                        Verify
                                    </button>
                                </div>
                            </div>

                            <small id="otpStatus" class="text-muted d-block mt-1"></small>

                            <input type="hidden" id="phone_number">
                            <input type="hidden" name="phone" id="clean_phone">
                            <input type="hidden" name="country_code" id="country_code">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Booking For <span class="text-danger">*</span>
                            </label>

                            <div class="d-flex gap-3 flex-wrap">
                                @php
                                    $bfOptions = ['Self','Spouse','Parent','Child','Others'];
                                @endphp

                                @foreach ($bfOptions as $opt)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input bookingfor"
                                               type="radio"
                                               name="bookingfor"
                                               value="{{ $opt }}"
                                               {{ old('bookingfor', 'Self') === $opt ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label">{{ $opt }}</label>
                                    </div>
                                @endforeach
                            </div>

                            <input type="text"
                                   name="other_reason"
                                   id="other_reason"
                                   class="form-control mt-2"
                                   placeholder="Specify other"
                                   style="display:none;">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" id="nameLabel">
                                Your Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email (Optional)</label>
                            <input type="email" name="email" class="form-control">
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Gender <span class="text-danger">*</span>
                                </label>

                                <div class="d-flex gap-3 mt-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="gender" value="M" required>
                                        <label class="form-check-label">Male</label>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="gender" value="F">
                                        <label class="form-check-label">Female</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Age <span class="text-danger">*</span>
                                </label>
                                <input type="number"
                                       name="age"
                                       class="form-control"
                                       min="0"
                                       max="120"
                                       placeholder="Enter age"
                                       required>
                            </div>
                        </div>

                        <div class="alert alert-info mt-4 appointment-summary-card">
                            <strong class="d-block mb-2">Appointment Summary</strong>

                            <div class="d-flex justify-content-between">
                                <span>Date</span>
                                <span>{{ \Carbon\Carbon::createFromFormat('Ymd', $appointmentDate)->format('d M Y') }}</span>
                            </div>

                            <div class="d-flex justify-content-between">
                                <span>Time</span>
                                <span>{{ \Carbon\Carbon::createFromFormat('H:i', $appointmentTime)->format('h:i A') }}</span>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between">
                                <span>Doctor Consultation Fee</span>
                                <span>&#8377; <span id="doctorFee">{{ $appointmentFee }}</span></span>
                            </div>

                            <div id="followupMessage"></div>

                            <div class="d-flex justify-content-between d-none" id="registrationFeeRow">
                                <span>
                                    Registration Fee
                                    <small class="text-muted d-block" id="registrationValidity"></small>
                                </span>
                                <span>&#8377; <span id="registrationFeeAmount">0</span></span>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between fw-bold">
                                <span>Total Charges</span>
                                <span>&#8377; <span id="totalPayable">{{ $appointmentFee }}</span></span>
                            </div>

                            <div id="paymentChoiceSection" class="mt-3">
                                <div class="fw-semibold mb-2">Choose Payment Option</div>
                                <div id="paymentChoiceOptions"></div>
                                <small class="text-muted d-block mt-2" id="paymentChoiceHelp"></small>
                            </div>

                            <div class="d-flex justify-content-between fw-bold mt-3">
                                <span>Pay Now</span>
                                <span class="summary-currency">&#8377; <span id="payNowAmount">{{ $appointmentFee }}</span></span>
                            </div>
                        </div>

                        <input type="hidden" name="doctor_fee" value="{{ $appointmentFee }}">
                        <input type="hidden" name="registration_fee" id="registrationFeeInput" value="0">
                        <input type="hidden" name="total_amount" id="totalAmountInput" value="{{ $appointmentFee }}">
                        <input type="hidden" name="total_due" id="totalDueInput" value="{{ $appointmentFee }}">
                        <input type="hidden" name="payment_choice" id="paymentChoiceInput" value="full_payment">

                        <input type="hidden" name="slotDate" value="{{ $appointmentDate }}">
                        <input type="hidden" name="slotTime" value="{{ $appointmentTime }}">
                        <input type="hidden" name="doctorName" value="{{ $doctor['name'] ?? '' }}">
                        <input type="hidden" name="doctorKey" value="{{ $doctor['drKey'] ?? '' }}">
                        <input type="hidden" name="industry" value="hospital-clinic">

                        <button type="submit" id="submitBtn" class="btn btn-book w-100"> Confirm Appointment </button>
                    </form>

                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="patientSelectModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Select Patient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="patientList"></div>
        </div>
    </div>
</div>
<div class="modal fade" id="patientPicker" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Select Patient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="patientPickerBody"></div>

        </div>
    </div>
</div>

@endsection
@push('scripts')

<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"></script>
<script>
function formatAmount(amount) {
    return parseFloat(amount || 0).toFixed(2);
}

function setSummaryAmount(targetSelector, amount) {
    const target = $(targetSelector);
    const safeAmount = formatAmount(amount);

    if (!target.length) return;
    const targetId = target.attr('id');

    target.parent().replaceWith(
        `<span class="summary-currency">&#8377; <span id="${targetId}">${safeAmount}</span></span>`
    );
}

function getFeeState() {
    return {
        doctorFee: parseFloat($('#doctorFee').text() || 0),
        regFee: parseFloat($('#registrationFeeInput').val() || 0),
    };
}

function applySelectedPaymentChoice() {
    const selected = $('.payment-choice-radio:checked');
    const fees = getFeeState();
    const total = fees.doctorFee + fees.regFee;
    const payNowAmount = parseFloat(selected.data('amount') || 0);
    const helpText = selected.data('help') || '';

    $('.payment-choice-card').removeClass('is-selected');
    selected.closest('.payment-choice-card').addClass('is-selected');
    $('#paymentChoiceInput').val(selected.val() || 'full_payment');
    $('#paymentChoiceHelp').text(helpText);
    setSummaryAmount('#totalPayable', total);
    setSummaryAmount('#payNowAmount', payNowAmount);
    $('#totalAmountInput').val(formatAmount(payNowAmount));
    $('#totalDueInput').val(formatAmount(total));
}

function optionLabelFromValue(value) {
    if (value === 'full_payment') return 'Pay doctor fee now';
    if (value === 'registration_only') return 'Pay registration fee now';
    if (value === 'free_booking') return 'No payment needed now';
    return 'Pay at hospital';
}

function buildPaymentChoices() {
    const fees = getFeeState();
    const total = fees.doctorFee + fees.regFee;
    const existingChoice = $('#paymentChoiceInput').val();
    let options = [];

    if (total <= 0) {
        options = [{
            value: 'free_booking',
            label: 'No payment needed now',
            amount: 0,
            help: 'Your appointment will be booked without payment.'
        }];
    } else if (fees.regFee > 0 && fees.doctorFee > 0) {
        options = [
            {
                value: 'full_payment',
                label: 'Pay full amount now',
                amount: total,
                help: 'Registration fee and doctor fee will be paid online now.'
            },
            {
                value: 'registration_only',
                label: 'Pay registration fee now',
                amount: fees.regFee,
                help: 'Registration fee will be paid online now. Doctor fee can be paid at the hospital.'
            }
        ];
    } else if (fees.regFee > 0) {
        options = [{
            value: 'registration_only',
            label: 'Pay registration fee now',
            amount: fees.regFee,
            help: 'Registration fee will be paid online now.'
        }];
    } else if (fees.doctorFee > 0) {
        options = [
            {
                value: 'full_payment',
                label: 'Pay doctor fee now',
                amount: fees.doctorFee,
                help: 'Doctor fee will be paid online now.'
            },
            {
                value: 'pay_later',
                label: 'Pay at hospital',
                amount: 0,
                help: 'Doctor fee can be paid at the hospital.'
            }
        ];
    }

    const selectedChoice = options.find(option => option.value === existingChoice)
        ? existingChoice
        : (options[0]?.value || 'full_payment');

    let html = '';

    options.forEach(function (option) {
        html += `
            <label class="payment-choice-card ${selectedChoice === option.value ? 'is-selected' : ''}">
                <input class="form-check-input me-2 payment-choice-radio" type="radio"
                    name="payment_choice_option"
                    value="${option.value}"
                    data-amount="${option.amount}"
                    data-help="${option.help}"
                    ${selectedChoice === option.value ? 'checked' : ''}>
                <span class="payment-choice-top">
                    <span class="payment-choice-title">${option.label}</span>
                    <span class="float-end fw-semibold">&#8377; ${formatAmount(option.amount)}</span>
                </span>
            </label>
        `;
    });

    $('#paymentChoiceOptions').html(html).addClass('payment-choice-list');
    $('#paymentChoiceOptions .payment-choice-card').each(function () {
        const card = $(this);
        const input = card.find('.payment-choice-radio');
        const title = input.val() === 'pay_later'
            ? 'Pay at hospital'
            : (input.val() === 'registration_only'
                ? 'Pay registration fee now'
                : (fees.regFee > 0 && fees.doctorFee <= 0
                    ? 'Pay registration fee now'
                    : optionLabelFromValue(input.val())));
        const amount = parseFloat(input.data('amount') || 0);
        const help = input.data('help') || '';

        card.find('.payment-choice-top, .payment-choice-subtitle').remove();
        card.append(`
            <span class="payment-choice-top">
                <span class="payment-choice-title">${title}</span>
                <span class="payment-choice-amount">&#8377; ${formatAmount(amount)}</span>
            </span>
            <span class="payment-choice-subtitle">${help}</span>
        `);
    });

    applySelectedPaymentChoice();
}

function checkDoctorFollowupFee() {
    let patientId = $('#patient_id').val();
    let doctorId  = "{{ $doctorId }}";

    if (!otpVerified || !patientId) return;

    $.get("{{ url('/check-followup-fee') }}", {
        doctor_id: doctorId,
        patient_id: patientId
    })
    .done(function(res) {
        let doctorFee = parseFloat(res.doctor_fee || 0);

        setSummaryAmount('#doctorFee', doctorFee);
        $('input[name="doctor_fee"]').val(doctorFee);
        $('#followupMessage').html('');

        if (res.is_followup) {
            let extraInfo = '';

            if (res.followup_count > 0) {
                extraInfo = `
                    Previous Follow-up Visit: ${res.last_followup}<br>
                    Total Free Visits Used: ${res.followup_count}<br>
                `;
            }

            $('#followupMessage').html(`
                <div class="alert alert-success p-2 mt-2">
                    <strong>Follow-up Visit</strong><br>
                    Main Visit Date: ${res.last_visit}<br>
                    Valid Till: ${res.valid_till}<br>
                    ${extraInfo}
                    <strong>Doctor fee not applicable.</strong>
                </div>
            `);
        } else if (res.last_visit) {
            $('#followupMessage').html(`
                <div class="alert alert-warning p-2 mt-2">
                    <strong>Follow-up Expired</strong><br>
                    Main Visit Date: ${res.last_visit}<br>
                    Valid Till: ${res.valid_till}<br>
                    Total Free Visits Used: ${res.followup_count || 0}<br>
                    <strong>Doctor fee applicable.</strong>
                </div>
            `);
        }

        buildPaymentChoices();
    });
}

$(document).on('click', '.patient-select, .patient-rel-btn', function () {
    setTimeout(function () {
        checkDoctorFollowupFee();
    }, 300);
});

$(document).ajaxComplete(function(event, xhr, settings) {
    if (settings.url.includes('/verify-otp')) {
        setTimeout(function () {
            checkDoctorFollowupFee();
        }, 500);
    }
});
</script>
<script>
let otpVerified = false;
let otpAutoVerified = false;
let cachedPatients = [];
let isAutoPrefill = false;

function resetPatientFormFields() {
    $('input[name=name], input[name=email], input[name=age]').val('');
    $('input[name=gender]').prop('checked', false);
    $('#other_reason').val('').addClass('d-none').removeAttr('required');
}

var input = document.querySelector("#phone");
var iti = window.intlTelInput(input, {
    separateDialCode: true,
    preferredCountries: ["in", "us", "ae"],
});

function showPhoneError(message) {
    $('#phone').addClass('is-invalid');
    $('#phoneError').text(message);
    return false;
}

$(document).on("change", ".bookingfor", function () {
    if ($(this).val() === "Others") {
        $("#other_reason").show().attr("required", true);
    } else {
        $("#other_reason").hide().attr("required", false);
    }
});
</script>
<script>
let phoneLookupTimer = null;
let lastPhoneChecked = null;

var phoneInput = document.querySelector("#phone");
var iti = window.intlTelInput(phoneInput, {
    separateDialCode: true,
    preferredCountries: ["in", "us", "ae"],
});

function validatePhoneIntl() {
    $('#phone').removeClass('is-invalid');
    $('#phoneError').text('');

    if (!phoneInput.value.trim()) {
        return showPhoneError('Phone number is required');
    }

    if (!iti.isValidNumber()) {
        let errorCode = iti.getValidationError();
        let message = 'Invalid phone number';

        switch (errorCode) {
            case intlTelInputUtils.validationError.TOO_SHORT:
            case intlTelInputUtils.validationError.TOO_LONG:
                message = 'Phone number is wrong';
                break;
            case intlTelInputUtils.validationError.INVALID_COUNTRY_CODE:
                message = 'Invalid country code';
                break;
            case intlTelInputUtils.validationError.NOT_A_NUMBER:
                message = 'Invalid phone number';
                break;
        }

        return showPhoneError(message);
    }

    return true;
}

$('#phone').on('keyup change blur', function () {
    $('#phone').removeClass('is-invalid');
    $('#phoneError').text('');
    clearTimeout(phoneLookupTimer);
    phoneLookupTimer = setTimeout(fetchPatientsByPhone, 600);
});

phoneInput.addEventListener('countrychange', function () {
    clearTimeout(phoneLookupTimer);
    phoneLookupTimer = setTimeout(fetchPatientsByPhone, 600);
});

function updateHiddenPhoneFields() {
    let number = phoneInput.value.replace(/\D/g, '');
    let code = iti.getSelectedCountryData().dialCode;

    $('#phone_number').val(number);
    $('#clean_phone').val(number);
    $('#country_code').val(code);
}

function fetchPatientsByPhone() {
    if (!otpVerified) {
        $('#patientPicker').modal('hide');
        $('#patientRelationSelector').addClass('d-none');
        return;
    }

    updateHiddenPhoneFields();

    let phone = $('#phone_number').val();
    let countryCode = $('#country_code').val();

    if (!phone || phone.length < 6 || !countryCode) {
        $('#patientPicker').modal('hide');
        return;
    }

    let key = countryCode + phone;
    if (key === lastPhoneChecked) return;
    lastPhoneChecked = key;

    $.get("{{ url('/patients/by-phone') }}", {
        phone: phone,
        country_code: countryCode
    })
    .done(function (res) {
        cachedPatients = res.patients || [];

        if (res.count === 0) {
            $('#patientPicker').modal('hide');
            return;
        }

        if (res.count === 1) {
            resetPatientFormFields();
            prefillPatient(res.patients[0]);
            checkRegistrationFee();
            $('#patientPicker').modal('hide');
            return;
        }

        renderPatientRelations(res.patients);

        let html = '';

        if (res.count >= 4) {
            html += `
                <div class="alert alert-warning mb-2">
                    This mobile number allows only <strong>4</strong> members.
                </div>
            `;
        }

        res.patients.slice(0, 4).forEach(p => {
            let bookingForText =
                p.bookingfor === 'Others' && p.other_reason
                    ? p.other_reason
                    : p.bookingfor;

            html += `
                <div class="border rounded p-3 mb-2 patient-select"
                     style="cursor:pointer"
                     data-patient='${JSON.stringify(p)}'>
                    <strong>${p.name}</strong><br>
                    <small class="text-muted">
                        ${p.gender} - ${p.age} / ${bookingForText}
                    </small>
                </div>
            `;
        });

        $('#patientPickerBody').html(html);
        $('#patientPicker').modal('show');
    });
}

function renderPatientRelations(patients) {
    if (!otpVerified || patients.length === 0) {
        $('#patientRelationSelector').addClass('d-none');
        return;
    }

    $('#patientRelationButtons').html('');
    $('#patientRelationSelector').removeClass('d-none');

    patients.forEach((p, i) => {
        let label = p.bookingfor === 'Child'
            ? 'Child ' + (i + 1)
            : p.bookingfor === 'Parent'
                ? (p.gender === 'F' ? 'Mother' : 'Father')
                : p.bookingfor;

        $('#patientRelationButtons').append(`
            <button type="button"
                class="btn btn-outline-primary btn-sm patient-rel-btn"
                data-patient='${JSON.stringify(p)}'>
                ${label}
            </button>
        `);
    });
}

$(document).on('click', '.patient-rel-btn', function () {
    resetPatientFormFields();
    let patient = $(this).data('patient');
    prefillPatient(patient);

    if (otpVerified) {
        checkRegistrationFee();
    }
});

function prefillPatient(p) {
    $('#patient_id').val('');

    if (p && p.id) {
        $('#patient_id').val(p.id);
    }

    $('input[name=name]').val(p.name || '');
    $('input[name=email]').val(p.email || '');
    $('input[name=age]').val(p.age || '');

    if (p.gender) {
        $(`input[name=gender][value="${p.gender}"]`).prop('checked', true);
    }

    if (p.bookingfor) {
        isAutoPrefill = true;

        $(`input[name=bookingfor][value="${p.bookingfor}"]`)
            .prop('checked', true)
            .trigger('change');

        isAutoPrefill = false;

        if (p.bookingfor === 'Others') {
            $('#other_reason')
                .val(p.other_reason || '')
                .show()
                .attr('required', true);
        }
    }

    if (otpVerified) {
        $('#submitBtn').prop('disabled', false);
        checkRegistrationFee();
    }
}

$(document).ready(function () {
    $('#submitBtn').prop('disabled', true);
    setSummaryAmount('#doctorFee', $('#doctorFee').text());
    setSummaryAmount('#registrationFeeAmount', $('#registrationFeeAmount').text());
    setSummaryAmount('#totalPayable', $('#totalPayable').text());
    setSummaryAmount('#payNowAmount', $('#payNowAmount').text());
    buildPaymentChoices();
});

$(document).on('click', '.patient-select', function () {
    let patient = $(this).data('patient');
    prefillPatient(patient);
    $('#patientPicker').modal('hide');
});
</script>
<script>
$(document).on('change', '.bookingfor', function () {
    if (isAutoPrefill) return;

    let bookingFor = $(this).val();
    let match = cachedPatients.find(
        p => p.bookingfor?.toLowerCase() === bookingFor.toLowerCase()
    );

    if (!match) {
        $('#patient_id').val('');
        resetPatientFormFields();
    }

    $('#nameLabel').html(
        bookingFor === 'Self'
            ? 'Your Name <span class="text-danger">*</span>'
            : 'Patient Name <span class="text-danger">*</span>'
    );

    if (bookingFor === 'Others') {
        $('#other_reason').show().attr('required', true);
    } else {
        $('#other_reason').hide().removeAttr('required').val('');
    }

    if (match) {
        prefillPatient(match);
    }

    if (otpVerified) {
        checkRegistrationFee();
    }
});

$('.bookingfor:checked').trigger('change');

$('input[name="age"]').on('input', function () {
    let val = parseInt(this.value, 10);
    if (val < 0) this.value = 0;
    if (val > 120) this.value = 120;
});
</script>

<script>
$('#sendOtpBtn').on('click', function () {
    $("#submitBtn").prop("disabled", true);
    if (!validatePhoneIntl()) return;

    updateHiddenPhoneFields();

    let phone = $('#clean_phone').val();
    let countryCode = $('#country_code').val();

    $('#sendOtpBtn').prop('disabled', true).text('Sending...');

    $.post("{{ url('/send-otp') }}", {
        phone: phone,
        country_code: countryCode,
        _token: "{{ csrf_token() }}"
    })
    .done(function (res) {
        otpVerified = false;
        otpAutoVerified = false;

        $('#otp').val('').prop('disabled', false).focus();
        $('#verifyOtpBtn').prop('disabled', false).text('Verify');

        $('#registrationFeeRow').addClass('d-none');
        setSummaryAmount('#registrationFeeAmount', 0);
        $('#registrationValidity').text('');
        $('#registrationFeeInput').val(0);

        let doctorFee = parseFloat($('#doctorFee').text());
        setSummaryAmount('#doctorFee', doctorFee);
        setSummaryAmount('#totalPayable', doctorFee);
        $('#totalAmountInput').val(formatAmount(doctorFee));
        $('#totalDueInput').val(formatAmount(doctorFee));
        setSummaryAmount('#payNowAmount', doctorFee);
        buildPaymentChoices();

        $('#otpStatus')
            .removeClass('text-danger')
            .addClass('text-success')
            .text(res.message);
    })
    .fail(function (xhr) {
        $('#otpStatus')
            .removeClass('text-success')
            .addClass('text-danger')
            .text(xhr.responseJSON?.message || 'Failed to send OTP');
    })
    .always(function () {
        $('#sendOtpBtn').prop('disabled', false).text('Send OTP');
    });
});
</script>

<script>
$('#verifyOtpBtn').on('click', function () {
    if (otpVerified) return;

    let otp = $('#otp').val().trim();
    let phone = $('#clean_phone').val();

    $("#submitBtn").prop("disabled", true);

    if (otp.length !== 6) {
        $('#otpStatus')
            .removeClass('text-success')
            .addClass('text-danger')
            .text('Enter valid OTP');
        return;
    }

    $('#verifyOtpBtn').prop('disabled', true).text('Verifying...');

    $.post("{{ url('/verify-otp') }}", {
        phone: phone,
        otp: otp,
        _token: "{{ csrf_token() }}"
    })
    .done(function(res) {
        if (res.status === 'success') {
            otpVerified = true;
            otpAutoVerified = true;

            $('#otpStatus')
                .removeClass('text-danger')
                .addClass('text-success')
                .text('✔ ' + res.message);

            $('#submitBtn').prop('disabled', false);
            $('#verifyOtpBtn').prop('disabled', true).text('Verified');

            fetchPatientsByPhone();
            checkRegistrationFee();
        } else {
            otpVerified = false;
            otpAutoVerified = false;

            $('#otpStatus')
                .removeClass('text-success')
                .addClass('text-danger')
                .text(res.message || 'OTP verification failed');

            $('#verifyOtpBtn').prop('disabled', false).text('Verify');
            $('#submitBtn').prop('disabled', true);
        }
    })
    .fail(function (xhr) {
        otpVerified = false;
        otpAutoVerified = false;

        $('#otpStatus')
            .removeClass('text-success')
            .addClass('text-danger')
            .text(xhr.responseJSON?.message || 'OTP verification failed');

        $('#verifyOtpBtn').prop('disabled', false).text('Verify');
    });
});
</script>

<script>
$('#otp').on('input', function () {
    if (otpVerified || otpAutoVerified) return;

    if (this.value.length === 6) {
        otpAutoVerified = true;
        $('#verifyOtpBtn').click();
    }
});
</script>

<script>
function checkRegistrationFee() {
    let phone      = $('#clean_phone').val();
    let bookingFor = $('input[name="bookingfor"]:checked').val();
    let patientId  = $('#patient_id').val();

    if (!otpVerified || !phone || !bookingFor) return;

    $.get("{{ url('/check-registration-fee') }}", {
        phone: phone,
        bookingfor: bookingFor,
        patient_id: patientId
    })
    .done(function(res) {
        let doctorFee = parseFloat($('#doctorFee').text());
        let regFee = 0;

        if (res.apply === true) {
            regFee = parseFloat(res.amount);
            setSummaryAmount('#registrationFeeAmount', regFee);
            $('#registrationValidity').text('Valid till ' + res.valid_till);
            $('#registrationFeeRow').removeClass('d-none');
            $('#registrationFeeInput').val(regFee);
        } else {
            $('#registrationFeeRow').addClass('d-none');
            $('#registrationFeeInput').val(0);
            setSummaryAmount('#registrationFeeAmount', 0);
        }

        setSummaryAmount('#doctorFee', doctorFee);
        setSummaryAmount('#totalPayable', doctorFee + regFee);
        buildPaymentChoices();
    });
}

$(document).on('change', '.payment-choice-radio', function () {
    applySelectedPaymentChoice();
});
</script>

@endpush
