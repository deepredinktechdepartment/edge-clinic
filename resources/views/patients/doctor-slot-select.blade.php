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
    <div class="col-6">
        <div class="card shadow-sm">
            <div class="card-body">
                {{-- Doctor selection --}}
                <div class="mb-4">
                    <label for="doctorSelect" class="form-label">Select Doctor</label>
                    <select id="doctorSelect" class="form-select form-select-lg">
                        <option value="">-- Choose Doctor --</option>
                        @foreach($doctors as $doc)
                            <option value="{{ $doc->id }}">
                                {{ $doc->name }} ({{ $doc->department->dept_name ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>

              {{-- Dates & Time Slots --}}
<div id="slotsSection" class="row g-3 d-none">
    <div class="col-md-6">
        <div class="card shadow-sm p-3">
            <h6 class="mb-3">Select Date</h6>
            <div id="dateContainer" class="d-flex flex-wrap gap-2"></div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm p-3 position-relative">
            <h6 class="mb-3">Select Time Slot</h6>
            <div id="timeContainer" class="d-flex flex-wrap gap-2"></div>
            <div id="timeLoading" class="text-center my-2 d-none">Loading time slots...</div>
            <p id="noSlotsMsg" class="text-danger fw-bold mt-2 d-none">No slots available for this date.</p>
        </div>
    </div>
</div>

                {{-- Hidden inputs --}}
                <input type="hidden" id="selectedDate" name="selected_date">
                <input type="hidden" id="selectedTime" name="selected_time">
                <input type="hidden" id="timeInterval" name="time_interval">

                {{-- Continue Button --}}
                <button id="continueBtn" class="btn btn-brand mt-3">Continue to Payment</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#doctorSelect').on('change', function() {
    var doctorId = $(this).val();
    var slotsSection = $('#slotsSection');

    // Hide by default
    slotsSection.addClass('d-none');

    if(!doctorId) {
        return; // no doctor selected
    }

    // Show section once doctor selected
    slotsSection.removeClass('d-none');

    var dateContainer = $('#dateContainer');
    var timeContainer = $('#timeContainer');
    var timeLoading = $('#timeLoading');
    var noSlotsMsg = $('#noSlotsMsg');

    // Clear previous selections
    $('#selectedDate, #selectedTime, #timeInterval').val('');

    // Show loading card for dates
    dateContainer.html('<div class="card p-3 text-center">Loading dates...</div>');
    timeContainer.html('');
    timeLoading.addClass('d-none');
    noSlotsMsg.addClass('d-none');

    if(!doctorId) {
        dateContainer.html('');
        return;
    }

    $.get("{{ url('manualappointment/ajax-slots') }}/" + doctorId)
        .done(function(res) {
            if(!res.dates || !res.dates.slots || !res.dates.slots.location1) {
                dateContainer.html('<div class="card p-3 text-center text-danger">No slots available for this doctor.</div>');
                return;
            }

            var slotsData = res.dates.slots.location1;
            dateContainer.html('');

            // Sort dates ascending
            var sortedDates = Object.keys(slotsData).sort();

            // Render date buttons
            sortedDates.forEach(function(dateKey, idx) {
                var validSlots = slotsData[dateKey].filter(s => s !== 'weeklyoff');
                if(validSlots.length === 0) return;

                var dateObj = new Date(dateKey.substring(0,4), dateKey.substring(4,6)-1, dateKey.substring(6,8));
                var dateBtn = $('<button type="button" class="btn btn-outline-primary btn-sm m-1"></button>')
                    .text(dateObj.toDateString())
                    .attr('data-date', dateKey);

                if(idx === 0) dateBtn.addClass('active'); // first date active
                dateContainer.append(dateBtn);
            });

            // Render time slots for first active date
            var firstDate = $('#dateContainer button.active').data('date');
            if(firstDate) renderTimeSlots(firstDate);

            // Date click
            $('#dateContainer button').on('click', function() {
                $('#dateContainer button').removeClass('active');
                $(this).addClass('active');

                var selectedDate = $(this).data('date');
                $('#selectedDate').val(selectedDate);

                renderTimeSlots(selectedDate);
            });

            function renderTimeSlots(dateKey) {
                timeContainer.html('');
                timeLoading.removeClass('d-none');
                noSlotsMsg.addClass('d-none');

                setTimeout(() => { // simulate loading
                    var slots = slotsData[dateKey] || [];
                    var validSlots = slots.filter(s => s !== 'weeklyoff');

                    timeLoading.addClass('d-none');

                    if(validSlots.length === 0) {
                        noSlotsMsg.removeClass('d-none');
                        return;
                    }

                    validSlots.forEach((slot, index) => {
                        var btn = $('<button type="button" class="btn btn-outline-primary btn-sm m-1"></button>')
                            .text(slot)
                            .attr('data-time', slot);

                        if(index < validSlots.length -1){
                            var interval = calculateInterval(slot, validSlots[index+1]);
                            btn.attr('data-interval', interval);
                        }

                        timeContainer.append(btn);
                    });
                }, 300); // small delay for user perception
            }

            // Time click
            $(document).on('click', '#timeContainer button', function() {
                $('#timeContainer button').removeClass('active');
                $(this).addClass('active');

                $('#selectedTime').val($(this).data('time'));
                $('#timeInterval').val($(this).data('interval') || '');
            });

            function calculateInterval(t1, t2) {
                var parts1 = t1.split(':');
                var parts2 = t2.split(':');
                var date1 = new Date(0,0,0,parseInt(parts1[0]), parseInt(parts1[1]));
                var date2 = new Date(0,0,0,parseInt(parts2[0]), parseInt(parts2[1]));
                return (date2 - date1)/60000; // minutes
            }

        }).fail(function(jqXHR) {
            var msg = 'Failed to load slots.';
            if(jqXHR.status === 404) msg = 'Slots not found for this doctor.';
            dateContainer.html('<div class="card p-3 text-center text-danger">'+msg+'</div>');
        });
});

// Continue to Payment click
$('#continueBtn').on('click', function() {
    var doctorId = $('#doctorSelect').val();
    var selectedDate = $('#selectedDate').val();
    var selectedTime = $('#selectedTime').val();

    if(!doctorId) return alert('Please select a doctor.');
    if(!selectedDate) return alert('Please select a date.');
    if(!selectedTime) return alert('Please select a time slot.');

    // Redirect to payment route
    var url = "{{ route('manualappointment.payment', ':appointment') }}";
    // Assuming appointment ID is not yet created, you may pass doctor/date/time via GET
    url = url.replace(':appointment', 'new') + `?doctor=${doctorId}&date=${selectedDate}&time=${selectedTime}`;
    window.location.href = url;
});
</script>
@endpush
