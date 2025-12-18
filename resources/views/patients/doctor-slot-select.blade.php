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

    {{-- Amount --}}
    <div class="mb-3">
        <label class="form-label">Amount</label>
        <input type="number"
               name="amount"
               id="amount"
               class="form-control"
               min="0"
               step="0.01"
               placeholder="Enter amount"
               required>
    </div>

    {{-- Payment Mode --}}
    <div class="mb-3">
        <label class="form-label">Payment Mode</label>
        <select name="payment_mode" id="paymentMode" class="form-select" required>
            <option value="">-- Select --</option>
            <option value="cash">Cash</option>
            <option value="upi">UPI</option>
        </select>
    </div>

    {{-- UPI Reference --}}
    <div class="mb-3 d-none" id="upiRefDiv">
        <label class="form-label">UPI Reference No</label>
        <input type="text"
               name="upi_ref"
               id="upiRef"
               class="form-control"
               placeholder="12-digit UPI reference">
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
$('#doctorSelect').on('change', function () {
    let doctorId = $(this).val();

    $('#doctor_id').val(doctorId);
    $('#slotsSection, #paymentSection').addClass('d-none');
    $('#dateContainer, #timeContainer').html('');
    $('#selectedDate, #selectedTime').val('');

    if (!doctorId) return;

    $('#slotsSection').removeClass('d-none');
    $('#dateContainer').html('<div>Loading dates...</div>');

    $.get("{{ url('manualappointment/ajax-slots') }}/" + doctorId, function (res) {

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

            let d = new Date(dateKey.substr(0,4), dateKey.substr(4,2)-1, dateKey.substr(6,2));
            let btn = $(`<button type="button" class="btn btn-outline-primary btn-sm">${d.toDateString()}</button>`)
                .data('date', dateKey);

            if (dateKey === firstDate) btn.addClass('active');
            $('#dateContainer').append(btn);
        });

        if (firstDate) {
            $('#selectedDate').val(firstDate);
            loadTimes(firstDate);
        }

        $('#dateContainer button').click(function () {
            $('#dateContainer button').removeClass('active');
            $(this).addClass('active');

            $('#selectedDate').val($(this).data('date'));
            $('#selectedTime').val('');
            $('#paymentSection').addClass('d-none');

            loadTimes($(this).data('date'));
        });

        function loadTimes(dateKey) {
            $('#timeContainer').html('');
            $('#timeLoading').removeClass('d-none');

            setTimeout(() => {
                $('#timeLoading').addClass('d-none');
                let slots = slotsData[dateKey] || [];

                slots.filter(s => s !== 'weeklyoff').forEach(t => {
                    let btn = $(`<button type="button" class="btn btn-outline-primary btn-sm">${t}</button>`)
                        .data('time', t);
                    $('#timeContainer').append(btn);
                });
            }, 300);
        }
    });
});

$(document).on('click', '#timeContainer button', function () {
    $('#timeContainer button').removeClass('active');
    $(this).addClass('active');

    $('#selectedTime').val($(this).data('time'));
    $('#paymentSection').removeClass('d-none');
});

$('#paymentMode').change(function () {
    $(this).val() === 'upi'
        ? $('#upiRefDiv').removeClass('d-none')
        : $('#upiRefDiv').addClass('d-none').find('input').val('');
});

$('#appointmentForm').on('submit', function (e) {

    if (!$('#doctor_id').val() ||
        !$('#selectedDate').val() ||
        !$('#selectedTime').val() ||
        !$('#paymentMode').val()) {
        e.preventDefault();
        alert('Please complete all required fields');
    }

    if ($('#paymentMode').val() === 'upi' && !$('#upiRef').val()) {
        e.preventDefault();
        alert('Enter UPI reference number');
    }
});
</script>
@endpush
