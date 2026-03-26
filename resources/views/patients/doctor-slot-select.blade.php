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

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">

                {{-- Doctor --}}
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

                {{-- Dates & Slots --}}
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

                {{-- Payment --}}
               <div id="paymentSection" class="card shadow-sm p-3 mt-3 d-none">
                <h6>Payment Details</h6>

                <div class="mb-2">
                    <strong>Registration Fee:</strong>
                    ₹<span id="regFee">0</span>
                </div>

                <div class="mb-2">
                    <strong>Doctor Fee:</strong>
                    ₹<span id="docFee">0</span>
                </div>

                <hr>

                <div class="mb-3">
                    <strong>Total Amount:</strong>
                    ₹<span id="totalAmount">0</span>
                </div>

                <div id="followupMessage"></div>

                <input type="hidden" name="amount" id="amount">

                {{-- Payment Mode --}}
                <div class="mb-3">
                    <label class="form-label">Payment Mode</label>
                    <select name="payment_mode" id="paymentMode" class="form-select" required>
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



                {{-- Hidden --}}
                <input type="hidden" name="doctor_id" id="doctor_id">
                <input type="hidden" name="date" id="selectedDate">
                <input type="hidden" name="time" id="selectedTime">
                <input type="hidden" name="interval" id="timeInterval">
                <input type="hidden"   name="patientId" value="{{$patient->id??0}}">

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

function updateTotal() {
    let total = doctorFee + registrationFee;

    $('#docFee').text(doctorFee.toFixed(2));
    $('#regFee').text(registrationFee.toFixed(2));
    $('#totalAmount').text(total.toFixed(2));
    $('#amount').val(total.toFixed(2));

    // ✅ NEW LOGIC
    if (total == 0) {
        $('#paymentMode').closest('.mb-3').hide();
        $('#paymentMode').removeAttr('required').val('');
        $('#upiRefDiv').addClass('d-none');
    } else {
        $('#paymentMode').closest('.mb-3').show();
        $('#paymentMode').attr('required', true);
    }
}

$(document).ready(function () {

    let patientId = $('input[name="patientId"]').val();

    // Registration fee check
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


// ================= DOCTOR CHANGE =================
$('#doctorSelect').on('change', function () {

    let doctorId = $(this).val();
    let patientId = $('input[name="patientId"]').val();

    $('#doctor_id').val(doctorId);

    // Reset UI
    $('#slotsSection, #paymentSection').addClass('d-none');
    $('#dateContainer, #timeContainer').html('');
    $('#selectedDate, #selectedTime').val('');
    $('#followupMessage').html('');
    doctorFee = 0;
    updateTotal();

    if (!doctorId) return;

    $('#slotsSection').removeClass('d-none');
    $('#dateContainer').html('<div>Loading dates...</div>');

    $.get(
        "{{ url('manualappointment/ajax-slots') }}/" + doctorId,
        { patientId: patientId }, // ✅ PASS PATIENT ID
        function (res) {

            doctorFee = parseFloat(res.appointment_fee || 0);
            updateTotal();

            // ✅ FOLLOW-UP MESSAGE
            // ================= FOLLOW-UP MESSAGE =================
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

            }
            else if (res.last_visit) {

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
                    dateKey.substr(0,4),
                    dateKey.substr(4,2)-1,
                    dateKey.substr(6,2)
                );

                let btn = $(`
                    <button type="button"
                        class="btn btn-outline-primary btn-sm">
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


// ================= LOAD TIMES =================
function loadTimes(dateKey, slotsData) {

    $('#timeContainer').html('');
    $('#timeLoading').removeClass('d-none');

    setTimeout(() => {

        $('#timeLoading').addClass('d-none');

        let slots = slotsData[dateKey] || [];

        slots.filter(s => s !== 'weeklyoff').forEach(t => {

            let btn = $(`
                <button type="button"
                    class="btn btn-outline-primary btn-sm">
                    ${t}
                </button>
            `).data('time', t);

            $('#timeContainer').append(btn);
        });

    }, 300);
}


// ================= DATE CLICK =================
$(document).on('click', '#dateContainer button', function () {

    $('#dateContainer button').removeClass('active');
    $(this).addClass('active');

    $('#selectedDate').val($(this).data('date'));
    $('#selectedTime').val('');
    $('#paymentSection').addClass('d-none');
});


// ================= TIME CLICK =================
$(document).on('click', '#timeContainer button', function () {

    $('#timeContainer button').removeClass('active');
    $(this).addClass('active');

    $('#selectedTime').val($(this).data('time'));
    $('#paymentSection').removeClass('d-none');
});


// ================= PAYMENT MODE =================
$('#paymentMode').change(function () {
    $(this).val() === 'upi'
        ? $('#upiRefDiv').removeClass('d-none')
        : $('#upiRefDiv').addClass('d-none').find('input').val('');
});


// ================= FORM SUBMIT =================
$('#appointmentForm').on('submit', function (e) {

    let total = parseFloat($('#amount').val()) || 0;

    // ✅ Basic required fields
    if (!$('#doctor_id').val() ||
        !$('#selectedDate').val() ||
        !$('#selectedTime').val() ||
        (total > 0 && !$('#paymentMode').val())) {

        e.preventDefault();
        alert('Please complete all required fields');
        return;
    }

    // ✅ UPI validation (only if needed)
    if (total > 0 && $('#paymentMode').val() === 'upi' && !$('#upiRef').val()) {
        e.preventDefault();
        alert('Enter UPI reference number');
        return;
    }

});
</script>
@endpush
