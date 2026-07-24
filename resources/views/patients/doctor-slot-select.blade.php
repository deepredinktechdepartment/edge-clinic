@extends('template_v1')

@section('content')
<div class="tt-posts">
    <div class="d-flex justify-content-between tt-wrap bg-white mb-3">
        <div class="p-2">
            <h5 class="mb-0">{{ $pageTitle ?? '' }}</h5>
        </div>
    </div>
</div>

<form id="appointmentForm" method="POST"
      action="{{ route('manualappointment.confirm') }}">
@csrf

@if ($errors->any())
    <div class="alert alert-danger">
        {{ $errors->first() }}
    </div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">

                <div class="mb-4">
                    <label class="form-label fw-semibold">Select Doctor</label>
                    <select id="doctorSelect" class="form-select form-select-lg">
                        <option value="">-- Choose Doctor --</option>
                        @foreach($doctors as $doc)
                            <option value="{{ $doc->id }}">
                                {{ $doc->name }} ({{ $doc->department->dept_name ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="slotsSection" class="row g-3 d-none">
                    <div class="col-md-6">
                        <div class="card p-3 shadow-sm">
                            <h6>Select Date</h6>
                            <div id="dateContainer" class="d-flex flex-wrap gap-2"></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card p-3 shadow-sm">
                            <h6>Select Time</h6>
                            <div id="timeContainer" class="d-flex flex-wrap gap-2"></div>
                            <div id="timeLoading" class="text-center d-none">Loading...</div>
                            <p id="noSlotsMsg" class="text-danger fw-bold d-none">No slots available</p>
                        </div>
                    </div>
                </div>

                <div id="afterSlotSection" class="card border-warning-subtle bg-warning-subtle p-3 mt-3 d-none">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" name="after_slot_walk_in" id="afterSlotWalkIn">
                        <label class="form-check-label fw-semibold" for="afterSlotWalkIn">
                            After-slot walk-in
                        </label>
                    </div>
                    <div class="form-text mt-1">Use only when the patient reaches reception after the doctor's configured slot end time. This booking is tracked separately.</div>

                    <div id="afterSlotFields" class="row g-3 mt-1 d-none">
                        <div class="col-md-6">
                            <label class="form-label">Walk-in date</label>
                            <input type="date" id="afterSlotDate" class="form-control" min="{{ now()->toDateString() }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Appointment time</label>
                            <input type="time" id="afterSlotTime" class="form-control" disabled>
                        </div>
                        <div class="col-12">
                            <div id="afterSlotHelp" class="small text-muted">Select a date to check the doctor's closing time.</div>
                        </div>
                    </div>
                </div>

                <div id="paymentSection" class="card shadow-sm p-3 mt-3 d-none">
                    <h6>Payment Details</h6>

                    <div class="mb-2">
                        <strong>Registration Fee:</strong>
                        Rs <span id="regFee">0</span>
                    </div>

                    <div class="mb-2">
                        <strong>Doctor Fee:</strong>
                        Rs <span id="docFee">0</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Source</label>
                        <select name="source_id" id="sourceId" class="form-select" required>
                            <option value="">-- Select Source --</option>
                            @foreach($sources as $source)
                                <option
                                    value="{{ $source->id }}"
                                    data-payment-rule="{{ $source->payment_rule ?? '' }}"
                                    data-payment-rule-label="{{ $paymentRuleLabels[$source->payment_rule] ?? '' }}"
                                >{{ $source->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="alert alert-info py-2 px-3 d-none" id="sourceRuleInfo"></div>

                    <div class="mb-3">
                        <label class="form-label">Discount (%)</label>
                        <input type="number"
                               name="discount_percentage"
                               id="discountPercentage"
                               class="form-control"
                               min="0"
                               max="100"
                               step="0.01"
                               value="0"
                               placeholder="Enter discount percentage">
                        <small class="text-muted">Only percentage discount is allowed.</small>
                    </div>

                    <div class="mb-2">
                        <strong>Discount Amount:</strong>
                        Rs <span id="discountAmount">0</span>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <strong>Final Amount:</strong>
                        Rs <span id="totalAmount">0</span>
                    </div>

                    <div id="followupMessage"></div>

                    <input type="hidden" name="amount" id="amount">

                    <div class="mb-3" id="paymentChoiceWrapper">
                        <label class="form-label">Payment Option</label>
                        <select name="payment_choice" id="paymentChoice" class="form-select">
                            <option value="pay_now">Pay Now</option>
                            <option value="pay_later">Pay Later</option>
                            <option value="no_payment_required">No Payment Required</option>
                        </select>
                    </div>

                    <div id="paymentModeWrapper">
                        <div class="mb-3">
                            <label class="form-label">Payment Mode</label>
                            <select name="payment_mode" id="paymentMode" class="form-select">
                                <option value="">-- Select --</option>
                                <option value="cash">Cash</option>
                                <option value="upi">UPI</option>
                            </select>
                        </div>

                        <div class="mb-3 d-none" id="upiRefDiv">
                            <label class="form-label">UPI Reference No</label>
                            <input type="text" name="upi_ref" id="upiRef" class="form-control">
                        </div>
                    </div>

                    <div class="alert alert-info py-2 px-3 d-none" id="payLaterInfo">
                        Appointment will be confirmed now. Payment mode and reference can be updated later after the patient pays at reception.
                    </div>

                    <div class="alert alert-secondary py-2 px-3 d-none" id="noPaymentRequiredInfo">
                        This appointment will be saved without collecting payment now, and payment update will stay disabled for it.
                    </div>
                </div>

                <input type="hidden" name="doctor_id" id="doctor_id">
                <input type="hidden" name="date" id="selectedDate">
                <input type="hidden" name="time" id="selectedTime">
                <input type="hidden" name="interval" id="timeInterval">
                <input type="hidden" name="patientId" value="{{ $patient->id ?? 0 }}">

                <button type="submit" class="btn btn-brand mt-4">
                    Confirm Appointment
                </button>

            </div>
        </div>
    </div>
</div>
</form>
@endsection
@push('scripts')
<script>
let doctorFee = 0;
let registrationFee = 0;
let afterSlotEndTime = null;

function getSelectedSourceRule() {
    const selectedSource = $('#sourceId option:selected');

    return {
        value: selectedSource.data('payment-rule') || '',
        label: selectedSource.data('payment-rule-label') || '',
        name: selectedSource.text().trim() || '',
    };
}

function updateTotal() {
    let gross = doctorFee + registrationFee;
    let discountPercentage = parseFloat($('#discountPercentage').val() || 0);
    let discountAmount = (gross * discountPercentage) / 100;
    let total = Math.max(gross - discountAmount, 0);

    $('#docFee').text(doctorFee.toFixed(2));
    $('#regFee').text(registrationFee.toFixed(2));
    $('#discountAmount').text(discountAmount.toFixed(2));
    $('#totalAmount').text(total.toFixed(2));
    $('#amount').val(total.toFixed(2));
    syncPaymentUI();
}

function renderPaymentChoices() {
    const selectedRule = getSelectedSourceRule().value;
    const currentChoice = $('#paymentChoice').val();
    const total = parseFloat($('#amount').val() || 0);
    let options = [
        { value: 'pay_now', label: 'Pay Now' },
        { value: 'pay_later', label: 'Pay Later' },
    ];

    if (selectedRule === 'no_payment_required') {
        options.push({ value: 'no_payment_required', label: 'No Payment Required' });
    }

    if (total <= 0) {
        options = [{ value: 'free_booking', label: 'No Payment Needed' }];
    }

    const nextChoice = options.some(option => option.value === currentChoice)
        ? currentChoice
        : (options[0]?.value || 'pay_now');

    $('#paymentChoice').html(
        options.map(option => `<option value="${option.value}">${option.label}</option>`).join('')
    );
    $('#paymentChoice').val(nextChoice);
}

$(document).ready(function () {
    let patientId = $('input[name="patientId"]').val();

    if (patientId && patientId > 0) {
        $.get(
            "{{ url('manualappointment/check-registration-fee') }}/" + patientId,
            function (res) {
                registrationFee = res.apply ? parseFloat(res.amount || 0) : 0;
                updateTotal();
            }
        );
    }
});

$('#doctorSelect').on('change', function () {
    let doctorId = $(this).val();
    let patientId = $('input[name="patientId"]').val();

    $('#doctor_id').val(doctorId);

    $('#slotsSection, #paymentSection').addClass('d-none');
    $('#afterSlotSection, #afterSlotFields').addClass('d-none');
    $('#dateContainer, #timeContainer').html('');
    $('#selectedDate, #selectedTime').val('');
    $('#afterSlotWalkIn').prop('checked', false);
    $('#afterSlotDate, #afterSlotTime').val('').prop('disabled', true);
    $('#afterSlotHelp').removeClass('text-danger text-success').addClass('text-muted').text('Select a date to check the doctor\'s closing time.');
    afterSlotEndTime = null;
    $('#followupMessage').html('');
    doctorFee = 0;
    updateTotal();

    if (!doctorId) return;

    $('#slotsSection').removeClass('d-none');
    $('#dateContainer').html('<div>Loading dates...</div>');

    $.get(
        "{{ url('manualappointment/ajax-slots') }}/" + doctorId,
        { patientId: patientId },
        function (res) {
            doctorFee = parseFloat(res.appointment_fee || 0);
            updateTotal();
            $('#afterSlotSection').removeClass('d-none');

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
                    <div class="alert alert-success mt-2 p-2">
                        <strong>Follow-up Visit</strong><br>
                        Main Visit Date: ${res.last_visit}<br>
                        Valid Till: ${res.valid_till}<br>
                        ${extraInfo}
                        <strong>Doctor fee not applicable.</strong>
                    </div>
                `);
            } else if (res.last_visit) {
                $('#followupMessage').html(`
                    <div class="alert alert-warning mt-2 p-2">
                        <strong>Follow-up Expired</strong><br>
                        Main Visit Date: ${res.last_visit}<br>
                        Valid Till: ${res.valid_till}<br>
                        Total Free Visits Used: ${res.followup_count || 0}<br>
                        <strong>Doctor fee applicable.</strong>
                    </div>
                `);
            }

            let slotsData = res?.dates?.slots?.location1;
            if (!slotsData) {
                $('#dateContainer').html('<div class="text-danger">No slots</div>');
                return;
            }

            let firstDate = null;
            $('#dateContainer').html('');

            Object.keys(slotsData).sort().forEach(dateKey => {
                let valid = slotsData[dateKey].filter(s => s !== 'weeklyoff');
                if (!valid.length) return;

                if (!firstDate) firstDate = dateKey;

                let d = new Date(
                    dateKey.substr(0, 4),
                    dateKey.substr(4, 2) - 1,
                    dateKey.substr(6, 2)
                );

                let btn = $(`
                    <button type="button" class="btn btn-outline-primary btn-sm">
                        ${d.toDateString()}
                    </button>
                `).data('date', dateKey);

                if (dateKey === firstDate) btn.addClass('active');

                $('#dateContainer').append(btn);
            });

            if (firstDate) {
                $('#selectedDate').val(firstDate);
                loadTimes(firstDate, slotsData);
            }
        }
    );
});

function loadTimes(dateKey, slotsData) {
    $('#timeContainer').html('');
    $('#timeLoading').removeClass('d-none');

    setTimeout(() => {
        $('#timeLoading').addClass('d-none');

        let slots = slotsData[dateKey] || [];

        slots.filter(s => s !== 'weeklyoff').forEach(t => {
            let btn = $(`
                <button type="button" class="btn btn-outline-primary btn-sm">
                    ${t}
                </button>
            `).data('time', t);

            $('#timeContainer').append(btn);
        });
    }, 300);
}

$(document).on('click', '#dateContainer button', function () {
    $('#afterSlotWalkIn').prop('checked', false);
    $('#afterSlotFields').addClass('d-none');
    $('#dateContainer button').removeClass('active');
    $(this).addClass('active');

    $('#selectedDate').val($(this).data('date'));
    $('#selectedTime').val('');
    $('#paymentSection').addClass('d-none');
});

$(document).on('click', '#timeContainer button', function () {
    $('#afterSlotWalkIn').prop('checked', false);
    $('#afterSlotFields').addClass('d-none');
    $('#timeContainer button').removeClass('active');
    $(this).addClass('active');

    $('#selectedTime').val($(this).data('time'));
    $('#paymentSection').removeClass('d-none');
});

$('#afterSlotWalkIn').on('change', function () {
    const enabled = $(this).is(':checked');
    $('#afterSlotFields').toggleClass('d-none', !enabled);
    $('#slotsSection').toggleClass('d-none', enabled);
    $('#paymentSection').addClass('d-none');
    $('#selectedDate, #selectedTime').val('');
    $('#timeContainer button').removeClass('active');

    if (!enabled) {
        afterSlotEndTime = null;
        $('#afterSlotDate, #afterSlotTime').val('').prop('disabled', true);
        $('#afterSlotHelp').removeClass('text-danger text-success').addClass('text-muted').text('Select a date to check the doctor\'s closing time.');
        return;
    }

    $('#afterSlotDate').val('{{ now()->toDateString() }}').trigger('change');
});

$('#afterSlotDate').on('change', function () {
    const doctorId = $('#doctor_id').val();
    const date = $(this).val();
    afterSlotEndTime = null;
    $('#afterSlotTime').val('').prop('disabled', true);
    $('#selectedDate, #selectedTime').val('');
    $('#paymentSection').addClass('d-none');

    if (!doctorId || !date) return;

    $('#afterSlotHelp').removeClass('text-danger text-success text-muted').text('Checking doctor hours...');
    $.get("{{ url('manualappointment/after-slot-window') }}/" + doctorId, { date: date })
        .done(function (res) {
            if (!res.available || !res.end_time) {
                $('#afterSlotHelp').addClass('text-danger').text(res.message || 'After-slot booking is not available for this date.');
                return;
            }

            afterSlotEndTime = res.end_time;
            $('#afterSlotTime').prop('disabled', false).attr('min', res.end_time);
            $('#afterSlotHelp').addClass('text-success').text('Doctor slots end at ' + formatTime(res.end_time) + '. Choose this time or later.');
        })
        .fail(function (xhr) {
            $('#afterSlotHelp').addClass('text-danger').text(xhr.responseJSON?.message || 'Could not check doctor hours. Please try again.');
        });
});

$('#afterSlotTime').on('change', function () {
    const date = $('#afterSlotDate').val();
    const time = $(this).val();
    const selected = time ? time.slice(0, 5) : '';

    $('#paymentSection').addClass('d-none');
    $('#selectedDate, #selectedTime').val('');

    if (!date || !selected || !afterSlotEndTime) return;

    if (selected < afterSlotEndTime) {
        $('#afterSlotHelp').removeClass('text-success text-muted').addClass('text-danger').text('Choose ' + formatTime(afterSlotEndTime) + ' or a later time.');
        return;
    }

    const today = '{{ now()->toDateString() }}';
    const now = new Date();
    const currentTime = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
    if (date === today && selected < currentTime) {
        $('#afterSlotHelp').removeClass('text-success text-muted').addClass('text-danger').text('A past time cannot be used for today.');
        return;
    }

    $('#selectedDate').val(date.replaceAll('-', ''));
    $('#selectedTime').val(selected);
    $('#afterSlotHelp').removeClass('text-danger text-muted').addClass('text-success').text('After-slot walk-in will be tracked separately.');
    $('#paymentSection').removeClass('d-none');
});

function formatTime(time) {
    const [hour, minute] = time.split(':');
    const h = Number(hour);
    return `${String(((h + 11) % 12) + 1).padStart(2, '0')}:${minute} ${h >= 12 ? 'PM' : 'AM'}`;
}

function syncPaymentUI() {
    const total = parseFloat($('#amount').val() || 0);
    const selectedRule = getSelectedSourceRule();
    const isFreeBooking = total <= 0;

    renderPaymentChoices();

    const isPayLater = $('#paymentChoice').val() === 'pay_later';
    const isNoPaymentRequired = $('#paymentChoice').val() === 'no_payment_required';

    $('#paymentChoiceWrapper').toggleClass('d-none', isFreeBooking);
    $('#paymentModeWrapper').toggleClass('d-none', isPayLater || isFreeBooking || isNoPaymentRequired);
    $('#payLaterInfo').toggleClass('d-none', !isPayLater || isFreeBooking);
    $('#noPaymentRequiredInfo').toggleClass('d-none', !isNoPaymentRequired || isFreeBooking);

    if (selectedRule.value && !isFreeBooking) {
        $('#sourceRuleInfo')
            .removeClass('d-none')
            .text(
                selectedRule.value === 'no_payment_required'
                    ? `${selectedRule.name} source allows "${selectedRule.label}" as an extra payment option for this appointment.`
                    : `${selectedRule.name} source has "${selectedRule.label}" configured.`
            );
    } else {
        $('#sourceRuleInfo').addClass('d-none').text('');
    }

    if (isFreeBooking) {
        $('#paymentChoice').val('free_booking');
        $('#paymentMode').val('');
        $('#upiRefDiv').addClass('d-none');
        $('#upiRef').val('');
        return;
    }

    if ($('#paymentChoice').val() === 'free_booking') {
        $('#paymentChoice').val('pay_now');
    }

    if (isPayLater || isNoPaymentRequired) {
        $('#paymentMode').val('');
        $('#upiRefDiv').addClass('d-none');
        $('#upiRef').val('');
    }
}

$('#paymentChoice').change(function () {
    syncPaymentUI();
});

$('#sourceId').change(function () {
    syncPaymentUI();
});

$('#paymentMode').change(function () {
    $(this).val() === 'upi'
        ? $('#upiRefDiv').removeClass('d-none')
        : $('#upiRefDiv').addClass('d-none').find('input').val('');
});

$('#discountPercentage').on('input change', function () {
    let value = parseFloat($(this).val() || 0);

    if (value < 0) {
        value = 0;
    }

    if (value > 100) {
        value = 100;
    }

    $(this).val(value);
    updateTotal();
});

$('#appointmentForm').on('submit', function (e) {
    if (!$('#doctor_id').val() ||
        !$('#selectedDate').val() ||
        !$('#selectedTime').val() ||
        !$('#sourceId').val()) {

        e.preventDefault();
        alert('Please complete all required fields');
        return;
    }

    const total = parseFloat($('#amount').val() || 0);
    const discountPercentage = parseFloat($('#discountPercentage').val() || 0);

    if (discountPercentage < 0 || discountPercentage > 100) {
        e.preventDefault();
        alert('Discount must be between 0 and 100 percent');
        return;
    }

    if (
        total > 0 &&
        $('#paymentChoice').val() === 'pay_now' &&
        !$('#paymentMode').val()
    ) {
        e.preventDefault();
        alert('Please select a payment mode');
        return;
    }

    if (
        total > 0 &&
        $('#paymentChoice').val() === 'pay_now' &&
        $('#paymentMode').val() === 'upi' &&
        !$('#upiRef').val()
    ) {
        e.preventDefault();
        alert('Enter UPI reference number');
        return;
    }

    $(this).find('button[type="submit"]').prop('disabled', true).text('Confirming...');
});

syncPaymentUI();
updateTotal();
</script>
@endpush
