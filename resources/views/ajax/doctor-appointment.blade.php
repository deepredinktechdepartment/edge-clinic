@php
    // Extract dates from the array
    $dates = $slots['slots']['location1'] ?? [];
    $firstDate = null;
@endphp



<section class="pt-0 pb-5">
    

    <div class="container">
        <h4 class="mb-3">Book Appointment with {{ $doctor->name }}</h4>
        <form id="schedule-appointment-modal" action="{{ route('appointments.store') }}" method="POST">
            @csrf

            <input type="hidden" name="appointment_date" id="m_appointment_date">
            <input type="hidden" name="appointment_time" id="m_appointment_time">
            <input type="hidden" name="doctor_id" value="{{ $doctor->id }}">

            {{-- DATE & SLOT SECTION --}}
            <fieldset class="mb-4">
                <div class="schedule-appointment-card shadow-sm rounded p-4 bg-white">

                    {{-- DATE SECTION --}}
                    <div class="sch-appo-header mb-3">
                        <!-- <h5>Select Date</h5> -->
                        <ul id="dynamic-date-list" class="dr-appo-date-slider mb-0 list-unstyled d-flex gap-2">

                            @foreach($dates as $dateKey => $timeSlots)
                                @php
                                    if(!$firstDate && !empty($timeSlots) && $timeSlots[0] !== 'weeklyoff'){
                                        $firstDate = $dateKey;
                                    }
                                    $humanDate = \Carbon\Carbon::createFromFormat('Ymd', $dateKey)->format('D d-m-Y');
                                @endphp
                                <li>
                                    <a href="#"
                                       class="date-item {{ $loop->first ? 'active' : '' }}"
                                       data-date="{{ $dateKey }}">
                                       {{ $humanDate }}
                                    </a>
                                </li>
                            @endforeach

                        </ul>
                    </div>

                    {{-- TIME SECTION --}}
                    <div class="sch-appo-content mt-3">
                        <!-- <h5>Select Time Slot</h5> -->

                        <ul class="dr-appo-time-slots-slider mb-0 list-unstyled d-flex gap-2 flex-wrap">
                            @if($firstDate)
                                @foreach($dates[$firstDate] as $slot)
                                    @if($slot !== 'weeklyoff')
                                        <li>
                                            <a href="#"
                                               class="m-slot-item btn btn-outline-primary btn-sm px-3"
                                               data-time="{{ $slot }}">
                                               {{ $slot }}
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            @endif
                        </ul>

                        <p class="m-no-slots-msg text-center text-danger fw-bold mt-2 {{ $firstDate ? 'd-none' : '' }}">
                            No slots available for this date.
                        </p>
                    </div>

                </div>
            </fieldset>

            {{-- DOCTOR + TERMS + CONFIRM --}}
          <fieldset class="border p-4 rounded shadow-sm bg-white">

    {{-- Single Alert --}}
    <div class="alert alert-info shadow-sm mb-4" id="m_selected-slot-msg">
        <strong>Selected Appointment Details</strong><br>
        <span id="m_selected-slot-text">Please select a date & time</span><br><br>

        <strong>Location:</strong>
        Edge Clinic at HITEC City, Hyderabad<br><br>

        <div class="form-check">
            <input 
                type="checkbox" 
                id="m_terms_agree" 
                class="form-check-input" 
                required
            >
            <label class="form-check-label" for="m_terms_agree">
                I agree to the
                <a href="{{ url('terms-of-use') }}" target="_blank" class="text-secondary">
                    Terms & Conditions
                </a>
            </label>
        </div>
    </div>

    {{-- Confirm Button --}}
    <div class="text-end">
      <button type="submit" class="btn btn-book">
         Confirm Appointment
       </button>
        <p class="m-no-slots-msg text-center text-danger fw-bold mt-2">Online Booking Is currently Unavailable</p>
    </div>

</fieldset>

<input type="hidden" name="doctor" value="{{$doctor??''}}" />
        </form>
    </div>
</section>



<script>
  
function initAppointmentModal() {
    // Pass PHP variables to JS
    let modalSelectedDate = "{{ $firstDate ?? '' }}";
    let modalSelectedTime = '';
    let dates = @json($dates ?? []);

    if(modalSelectedDate){
        $('#m_appointment_date').val(modalSelectedDate);
    }

 // Date click
$(document).off('click', '.date-item').on('click', '.date-item', function(e){
    e.preventDefault();

    $('.date-item').removeClass('active');
    $(this).addClass('active');

    modalSelectedDate = $(this).data('date');
    $('#m_appointment_date').val(modalSelectedDate);

    let dateSlots = dates[modalSelectedDate] ?? [];
    let timeList = $('.dr-appo-time-slots-slider');

    /* ----------------------------------------------------------
       1) SHOW LOADING (NO RAW UL ITEMS)
    ----------------------------------------------------------- */
    timeList.hide(); 
    timeList.html(`
        <li class="text-center w-100 py-3 fw-bold">Loading...</li>
    `).fadeIn(120);

    /* ----------------------------------------------------------
       2) SMALL TIMEOUT TO ALLOW "LOADING" TO RENDER
    ----------------------------------------------------------- */
    setTimeout(() => {

        /* Destroy slick BEFORE modifying HTML */
        if (timeList.hasClass('slick-initialized')) {
            timeList.slick('unslick');
        }

        /* Hide before rendering real slot items */
        timeList.hide().html("");

      
        if(dateSlots.length === 0 || dateSlots[0] === 'weeklyoff'){
            $('.m-no-slots-msg').removeClass('d-none');
            return;
        }

        $('.m-no-slots-msg').addClass('d-none');

        // Build new slot list (still hidden)
        dateSlots.forEach(function(t){
            if(t !== 'weeklyoff'){
                timeList.append(
                    `<li>
                        <a href="#" class="m-slot-item btn btn-outline-primary btn-sm px-3"
                           data-time="${t}">
                           ${t}
                        </a>
                    </li>`
                );

                
            }
        });

        /* ----------------------------------------------------------
           3) INIT SLICK WHILE LIST IS HIDDEN
        ----------------------------------------------------------- */
timeList.not('.slick-initialized').slick({
    slidesToShow: 6,
    slidesToScroll: 1,
    arrows: true,
    dots: false,
    infinite: false, // ðŸ”´ no loop
    centerMode: false,
    centerPadding: '0px',

    responsive: [
        {
            breakpoint: 1024, // tablets
            settings: {
                slidesToShow: 4,
                centerMode: false,
                infinite: false
            }
        },
        {
            breakpoint: 576, // mobile
            settings: {
                slidesToShow: 2,
                arrows: true,
                centerMode: false,
                infinite: false
            }
        },
        {
            breakpoint: 380, // small mobile
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
                arrows: true,
                centerMode: false,
                infinite: false
            }
        }
    ]
});

        /* ----------------------------------------------------------
           4) SHOW FULLY READY SLIDER
        ----------------------------------------------------------- */
        timeList.fadeIn(180);

    }, 120);  // feels instant but smooth

});

    // Time slot click
    $(document).off('click', '.m-slot-item').on('click', '.m-slot-item', function(e){
        e.preventDefault();

        $('.m-slot-item').removeClass('active');
        $(this).addClass('active');

        modalSelectedTime = $(this).data('time');
        $('#m_appointment_time').val(modalSelectedTime);

        let readable = moment(modalSelectedDate, "YYYYMMDD").format("DD MMMM YYYY");

        $('#m_selected-slot-text').text(readable + " at " + formatTime(modalSelectedTime));
    });

    // Form validation
    $(document).off('submit', '#schedule-appointment-modal').on('submit', '#schedule-appointment-modal', function(e){
        if(!modalSelectedDate){
            alert("Please select a date");
            e.preventDefault();
            return false;
        }

        if(!modalSelectedTime){
            alert("Please select a time slot");
            e.preventDefault();
            return false;
        }

        if(!$('#m_terms_agree').is(':checked')){
            alert("You must accept our Terms & Conditions");
            e.preventDefault();
            return false;
        }
    });
}
</script>


