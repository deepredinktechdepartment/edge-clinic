@extends('template_v1')

@section('content')
@include('cabins.partials.styles')

<div class="cabin-shell">
    @include('common_pages.pagetitle')

    @if ($errors->any())
        <div class="alert alert-danger mb-0">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="cabin-panel">
        <div class="panel-head">
            <div>
                <h5 class="mb-1">{{ $pageTitle }}</h5>
                <div class="text-muted">Full day uses clinic start and end time from Cabin Settings. Half day uses the first or second half of that working window. Hourly lets you choose the time range directly.</div>
            </div>
            <a href="{{ route('admin.cabins.bookings.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Bookings</a>
        </div>
    </div>

    <div class="cabin-panel">
        <div class="panel-body">
            <form id="bookingForm" method="POST" action="{{ $booking->exists ? route('admin.cabins.bookings.update', $booking->id) : route('admin.cabins.bookings.store') }}">
                @csrf
                @if($booking->exists)
                    @method('PUT')
                @endif

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Doctor <span class="text-danger">*</span></label>
                                <select name="doctor_id" class="form-select" required>
                                    <option value="">Select doctor</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}" {{ (string) old('doctor_id', $booking->doctor_id) === (string) $doctor->id ? 'selected' : '' }}>{{ $doctor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cabin <span class="text-danger">*</span></label>
                                <select name="cabin_id" id="cabin_id" class="form-select" required>
                                    <option value="">Select cabin</option>
                                    @foreach($cabins as $cabin)
                                        @php
                                            $isCurrentCabin = (string) old('cabin_id', $booking->cabin_id) === (string) $cabin->id;
                                            $bookingBlocked = in_array($cabin->status, ['occupied', 'maintenance', 'inactive'], true) || $cabin->booking_mode === 'monthly';
                                            $bookingReason = $cabin->booking_mode === 'monthly'
                                                ? 'Monthly Only'
                                                : ucfirst($cabin->status);
                                        @endphp
                                        <option
                                            value="{{ $cabin->id }}"
                                            data-base-label="{{ $cabin->cabin_code }} - {{ $cabin->name }}"
                                            data-hourly="{{ $cabin->hourly_rate }}"
                                            data-type="{{ $cabin->cabin_type }}"
                                            data-mode="{{ $cabin->booking_mode }}"
                                            data-status="{{ $cabin->status }}"
                                            data-static-blocked="{{ $bookingBlocked && ! $isCurrentCabin ? '1' : '0' }}"
                                            data-static-reason="{{ $bookingBlocked && ! $isCurrentCabin ? $bookingReason : '' }}"
                                            @if($bookingBlocked && ! $isCurrentCabin) style="color: #8a97a8; background-color: #f3f5f7;" aria-disabled="true" @endif
                                            @if($bookingBlocked && ! $isCurrentCabin) disabled @endif
                                            {{ (string) old('cabin_id', $booking->cabin_id) === (string) $cabin->id ? 'selected' : '' }}>
                                            {{ $cabin->cabin_code }} - {{ $cabin->name }}@if($bookingBlocked && ! $isCurrentCabin) ({{ $bookingReason }})@endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Booking Type <span class="text-danger">*</span></label>
                                <select name="booking_type" id="booking_type" class="form-select" required>
                                    @foreach(['hourly' => 'Hourly', 'half_day' => 'Half Day', 'full_day' => 'Full Day'] as $value => $label)
                                        <option value="{{ $value }}" {{ old('booking_type', $booking->booking_type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Booking Date <span class="text-danger">*</span></label>
                                <input type="date" name="booking_date" id="booking_date" class="form-control" value="{{ old('booking_date', optional($booking->booking_date)->format('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    @foreach(['booked' => 'Booked', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
                                        <option value="{{ $value }}" {{ old('status', $booking->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 d-none" id="half_day_slot_wrap">
                                <label class="form-label">Half Day Slot <span class="text-danger">*</span></label>
                                <select name="half_day_slot" id="half_day_slot" class="form-select">
                                    <option value="">Select half</option>
                                    <option value="first_half" {{ old('half_day_slot') === 'first_half' ? 'selected' : '' }}>First Half</option>
                                    <option value="second_half" {{ old('half_day_slot') === 'second_half' ? 'selected' : '' }}>Second Half</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Start Time <span class="text-danger">*</span></label>
                                <input type="time" name="start_time" id="start_time" class="form-control" value="{{ old('start_time', $booking->start_time ? substr($booking->start_time, 0, 5) : '') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">End Time <span class="text-danger">*</span></label>
                                <input type="time" name="end_time" id="end_time" class="form-control" value="{{ old('end_time', $booking->end_time ? substr($booking->end_time, 0, 5) : '') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Duration</label>
                                <input type="text" id="duration_label" class="form-control bg-light" readonly>
                            </div>
                            <div class="col-12">
                                <div class="booking-availability-panel" id="booking_availability_panel">
                                    <div class="booking-availability-head">
                                        <div>
                                            <div class="booking-availability-title">Available Hours on Selected Date</div>
                                            <div class="booking-availability-sub">Green means available. Red means already booked or blocked by active subscription for that cabin on the selected date.</div>
                                        </div>
                                        <div class="booking-availability-window" id="booking_availability_window">Select cabin and date</div>
                                    </div>
                                    <div class="booking-availability-legend">
                                        <span class="booking-availability-legend-item"><span class="booking-availability-swatch booking-availability-swatch-available"></span> Available</span>
                                        <span class="booking-availability-legend-item"><span class="booking-availability-swatch booking-availability-swatch-blocked"></span> Booked / Subscription</span>
                                    </div>
                                    <div class="booking-availability-grid" id="booking_availability_grid">
                                        <div class="booking-availability-empty">Choose a cabin and booking date to see the time availability.</div>
                                    </div>
                                </div>
                                <div class="alert alert-info mt-3 mb-0 py-2 d-none" id="booking_time_hint" role="alert">Click any green available time block to auto-fill the booking time.</div>
                                <div class="alert alert-danger mt-3 d-none" id="booking_time_conflict" role="alert"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">GST %</label>
                                <input type="number" name="gst_percent" id="gst_percent" step="0.01" min="0" max="100" class="form-control" value="{{ old('gst_percent', $booking->gst_percent ?? $settings->default_gst_percent) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estimated Total</label>
                                <input type="text" id="estimated_total" class="form-control bg-light" value="Rs {{ number_format((float) ($booking->total_amount ?? 0), 2) }}" readonly>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="4">{{ old('notes', $booking->notes) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="border rounded-4 p-3 bg-light h-100">
                            <h6 class="mb-3">Payment</h6>
                            <div class="mb-3">
                                <label class="form-label">Payment Choice <span class="text-danger">*</span></label>
                                <select name="payment_choice" id="payment_choice" class="form-select" required>
                                    @foreach(['pay_now' => 'Pay Now', 'pay_later' => 'Pay Later', 'free_booking' => 'Free Booking', 'no_payment_required' => 'No Payment Required'] as $value => $label)
                                        <option value="{{ $value }}" {{ old('payment_choice', $booking->payment_choice ?: 'pay_later') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3" id="payment_mode_wrap">
                                <label class="form-label">Payment Mode</label>
                                <select name="payment_mode" id="payment_mode" class="form-select">
                                    <option value="">Select mode</option>
                                    @foreach(['cash' => 'Cash', 'upi' => 'UPI', 'card' => 'Card'] as $value => $label)
                                        <option value="{{ $value }}" {{ old('payment_mode', $booking->payment_mode) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3" id="transaction_reference_wrap">
                                <label class="form-label">Reference No.</label>
                                <input type="text" name="transaction_reference" id="transaction_reference" class="form-control" value="{{ old('transaction_reference', $booking->transaction_reference) }}">
                            </div>
                            <div class="small text-muted" id="payment_hint"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 cabin-form-actions">
                    <button type="submit" class="btn btn-brand btn-sm">{{ $booking->exists ? 'Update Booking' : 'Save Booking' }}</button>
                    <a href="{{ route('admin.cabins.bookings.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const cabinRates = {
    consultation: {{ (float) $settings->standard_hourly_rate }},
    premium: {{ (float) $settings->premium_hourly_rate }},
    procedure: {{ (float) $settings->procedure_hourly_rate }},
    other: {{ (float) $settings->standard_hourly_rate }}
};
const clinicOpen = '{{ substr($settings->clinic_open_time, 0, 5) }}';
const clinicClose = '{{ substr($settings->clinic_close_time, 0, 5) }}';
const bookingAvailabilityUrl = '{{ route('admin.cabins.bookings.availability') }}';
const editingBookingId = '{{ $booking->exists ? $booking->id : '' }}';
let bookingAvailabilityData = null;

function timeToMinutes(value) {
    const parts = value.split(':');
    return (parseInt(parts[0], 10) * 60) + parseInt(parts[1], 10);
}

function minutesToTime(value) {
    const hours = Math.floor(value / 60);
    const minutes = value % 60;
    return String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
}

function setBookingCabinOptionState($option, disabled, reason) {
    const baseLabel = $option.data('base-label') || $option.text().replace(/\s+\(([^)]+)\)\s*$/, '');
    const label = disabled && reason ? baseLabel + ' (' + reason + ')' : baseLabel;

    $option.text(label);

    if (disabled) {
        $option.prop('disabled', true).attr('aria-disabled', 'true').attr('style', 'color: #8a97a8; background-color: #f3f5f7;');
    } else {
        $option.prop('disabled', false).removeAttr('aria-disabled').removeAttr('style');
    }
}

function refreshBookingCabinOptions() {
    const bookingDate = $('#booking_date').val();
    const requests = [];

    $('#cabin_id option[value!=""]').each(function () {
        const $option = $(this);
        const staticBlocked = String($option.data('static-blocked')) === '1';
        const staticReason = $option.data('static-reason');

        if (staticBlocked) {
            setBookingCabinOptionState($option, true, staticReason);
            return;
        }

        if (!bookingDate) {
            setBookingCabinOptionState($option, false, '');
            return;
        }

        requests.push(
            $.get(bookingAvailabilityUrl, {
                cabin_id: $option.val(),
                booking_date: bookingDate,
                booking_id: editingBookingId || ''
            }).done(function (response) {
                const hasAvailable = Array.isArray(response.segments) && response.segments.some(function (segment) {
                    return segment.status === 'available';
                });

                setBookingCabinOptionState($option, !hasAvailable, hasAvailable ? '' : 'Booked');
            }).fail(function () {
                setBookingCabinOptionState($option, false, '');
            })
        );
    });

    return $.when.apply($, requests);
}

function syncBookingWindow() {
    const type = $('#booking_type').val();
    const clinicStart = timeToMinutes(clinicOpen);
    const clinicEnd = timeToMinutes(clinicClose);
    const mid = clinicStart + Math.floor((clinicEnd - clinicStart) / 2);
    const isHourly = type === 'hourly';
    const isHalfDay = type === 'half_day';

    $('#half_day_slot_wrap').toggleClass('d-none', !isHalfDay);
    $('#start_time, #end_time').prop('readonly', !isHourly);
    $('#start_time, #end_time').toggleClass('bg-light', !isHourly);

    if (type === 'full_day') {
        $('#start_time').val(clinicOpen);
        $('#end_time').val(clinicClose);
    } else if (type === 'half_day') {
        const half = $('#half_day_slot').val();
        if (half === 'second_half') {
            $('#start_time').val(minutesToTime(mid));
            $('#end_time').val(clinicClose);
        } else {
            $('#start_time').val(clinicOpen);
            $('#end_time').val(minutesToTime(mid));
        }
    }
}

function syncPaymentFields() {
    const choice = $('#payment_choice').val();
    const payNow = choice === 'pay_now';
    const freeBooking = choice === 'free_booking';
    const noPayment = choice === 'no_payment_required';

    $('#payment_mode').prop('disabled', !payNow);
    $('#transaction_reference').prop('disabled', !payNow);
    $('#payment_mode_wrap, #transaction_reference_wrap').toggleClass('opacity-50', !payNow);

    if (!payNow) {
        $('#payment_mode').val('');
        $('#transaction_reference').val('');
    }

    if (noPayment) {
        $('#payment_hint').text('This booking will save with no payable amount.');
    } else if (freeBooking) {
        $('#payment_hint').text('This booking will save as free booking with zero amount.');
    } else if (payNow) {
        $('#payment_hint').text('Choose the payment mode and add reference for UPI or card.');
    } else {
        $('#payment_hint').text('Amount will stay pending for later collection.');
    }
}

function refreshBookingEstimate() {
    const selected = $('#cabin_id option:selected');
    const start = $('#start_time').val();
    const end = $('#end_time').val();
    const gst = parseFloat($('#gst_percent').val() || 0);
    const choice = $('#payment_choice').val();

    if (!selected.val() || !start || !end) {
        $('#estimated_total').val('Rs 0.00');
        $('#duration_label').val('');
        return;
    }

    const hourlyRate = parseFloat(selected.data('hourly') || 0) || cabinRates[selected.data('type')] || 0;
    const startDate = new Date('2000-01-01T' + start + ':00');
    const endDate = new Date('2000-01-01T' + end + ':00');
    const durationHours = (endDate - startDate) / 3600000;

    if (durationHours <= 0) {
        $('#estimated_total').val('Rs 0.00');
        $('#duration_label').val('');
        return;
    }

    let base = durationHours * hourlyRate;
    let total = base + ((base * gst) / 100);

    if (choice === 'free_booking' || choice === 'no_payment_required') {
        base = 0;
        total = 0;
    }

    $('#duration_label').val(durationHours.toFixed(2) + ' hours');
    $('#estimated_total').val('Rs ' + total.toFixed(2));
}

function renderBookingAvailabilityEmpty(message) {
    bookingAvailabilityData = null;
    $('#booking_availability_window').text('Select cabin and date');
    $('#booking_availability_grid').html('<div class="booking-availability-empty">' + message + '</div>');
}

function renderBookingAvailability(data) {
    bookingAvailabilityData = data || null;
    $('#booking_availability_window').text((data.window_start || '--:--') + ' - ' + (data.window_end || '--:--'));

    if (!data.segments || !data.segments.length) {
        $('#booking_time_hint').addClass('d-none');
        $('#booking_availability_grid').html('<div class="booking-availability-empty">No operating hours available for this date.</div>');
        return;
    }

    const html = data.segments.map(function (segment) {
        const statusClass = segment.status === 'blocked'
            ? 'booking-availability-slot-blocked'
            : 'booking-availability-slot-available';
        const interactiveAttrs = segment.status === 'available'
            ? ' role="button" tabindex="0" data-start="' + segment.start + '" data-end="' + segment.end + '"'
            : '';

        return '<div class="booking-availability-slot ' + statusClass + '"' + interactiveAttrs + '>' +
            '<div class="booking-availability-slot-time">' + segment.label + '</div>' +
            '<div class="booking-availability-slot-note">' + segment.note + '</div>' +
        '</div>';
    }).join('');

    $('#booking_availability_grid').html(html);
    $('#booking_time_hint').toggleClass('d-none', !data.segments.some(function (segment) {
        return segment.status === 'available';
    }));
    syncBookingAvailabilitySelection();
}

function clearBookingConflict() {
    $('#booking_time_conflict').addClass('d-none').text('');
    $('#bookingForm').find('button[type="submit"], input[type="submit"]').prop('disabled', false).removeClass('disabled');
}

function showBookingConflict(message) {
    $('#booking_time_conflict').removeClass('d-none').text(message);
    $('#bookingForm').find('button[type="submit"], input[type="submit"]').prop('disabled', true).addClass('disabled');
}

function validateSelectedBookingWindow() {
    const cabinId = $('#cabin_id').val();
    const bookingDate = $('#booking_date').val();
    const start = $('#start_time').val();
    const end = $('#end_time').val();

    if (!cabinId || !bookingDate || !start || !end) {
        clearBookingConflict();
        return true;
    }

    if (!bookingAvailabilityData || !Array.isArray(bookingAvailabilityData.segments)) {
        clearBookingConflict();
        return true;
    }

    const startMinutes = timeToMinutes(start);
    const endMinutes = timeToMinutes(end);

    if (endMinutes <= startMinutes) {
        showBookingConflict('End time must be after start time.');
        return false;
    }

    const blockedSegments = bookingAvailabilityData.segments.filter(function (segment) {
        if (segment.status !== 'blocked') {
            return false;
        }

        const segmentStart = timeToMinutes(segment.start);
        const segmentEnd = timeToMinutes(segment.end);

        return startMinutes < segmentEnd && endMinutes > segmentStart;
    });

    if (blockedSegments.length) {
        const firstBlocked = blockedSegments[0];
        showBookingConflict('Selected time overlaps with ' + firstBlocked.note + ' (' + firstBlocked.label + '). Please choose one of the green available ranges.');
        return false;
    }

    clearBookingConflict();
    return true;
}

function syncBookingAvailabilitySelection() {
    const start = $('#start_time').val();
    const end = $('#end_time').val();

    $('#booking_availability_grid .booking-availability-slot-available').removeClass('booking-availability-slot-selected');

    if (!start || !end) {
        return;
    }

    $('#booking_availability_grid .booking-availability-slot-available').each(function () {
        const $slot = $(this);
        if ($slot.data('start') === start && $slot.data('end') === end) {
            $slot.addClass('booking-availability-slot-selected');
        }
    });
}

function refreshBookingAvailability() {
    const cabinId = $('#cabin_id').val();
    const bookingDate = $('#booking_date').val();

    if (!cabinId || !bookingDate) {
        renderBookingAvailabilityEmpty('Choose a cabin and booking date to see the time availability.');
        $('#booking_time_hint').addClass('d-none');
        clearBookingConflict();
        return;
    }

    $('#booking_availability_window').text('Loading...');
    $('#booking_availability_grid').html('<div class="booking-availability-empty">Checking cabin availability...</div>');

    $.get(bookingAvailabilityUrl, {
        cabin_id: cabinId,
        booking_date: bookingDate,
        booking_id: editingBookingId || ''
    }).done(function (response) {
        renderBookingAvailability(response);
        validateSelectedBookingWindow();
    }).fail(function () {
        bookingAvailabilityData = null;
        $('#booking_availability_window').text('Unavailable');
        $('#booking_availability_grid').html('<div class="booking-availability-empty">Could not load cabin availability right now.</div>');
        $('#booking_time_hint').addClass('d-none');
        clearBookingConflict();
    });
}

$(function () {
    function lockCabinSubmit(form) {
        const $form = $(form);
        if ($form.data('submitting')) {
            return false;
        }

        $form.data('submitting', true);

        const $submit = $form.find('button[type="submit"], input[type="submit"]').first();
        $submit.prop('disabled', true).addClass('disabled');

        if ($submit.is('button')) {
            $submit.html('{{ $booking->exists ? 'Updating...' : 'Saving...' }}');
        }

        form.submit();
        return true;
    }

    $('#bookingForm').validate({
        rules: {
            doctor_id: { required: true },
            cabin_id: { required: true },
            booking_date: { required: true },
            start_time: { required: true },
            end_time: { required: true },
            payment_choice: { required: true },
            payment_mode: {
                required: function () {
                    return $('#payment_choice').val() === 'pay_now';
                }
            },
            transaction_reference: {
                required: function () {
                    return $('#payment_choice').val() === 'pay_now' && ['upi', 'card'].includes($('#payment_mode').val());
                }
            },
            half_day_slot: {
                required: function () {
                    return $('#booking_type').val() === 'half_day';
                }
            }
        },
        errorClass: 'text-danger',
        errorElement: 'small',
        submitHandler: function (form) {
            if (!validateSelectedBookingWindow()) {
                return false;
            }
            return lockCabinSubmit(form);
        }
    });

    $('#booking_type, #half_day_slot').on('change', function () {
        syncBookingWindow();
        refreshBookingEstimate();
        validateSelectedBookingWindow();
        syncBookingAvailabilitySelection();
    });
    $('#cabin_id, #start_time, #end_time, #gst_percent, #payment_choice, #payment_mode').on('change keyup', function () {
        syncPaymentFields();
        refreshBookingEstimate();
        validateSelectedBookingWindow();
        syncBookingAvailabilitySelection();
    });
    $('#cabin_id, #booking_date').on('change', refreshBookingAvailability);
    $('#booking_date').on('change', refreshBookingCabinOptions);
    $('#booking_availability_grid').on('click keydown', '.booking-availability-slot-available', function (event) {
        if (event.type === 'keydown' && !['Enter', ' '].includes(event.key)) {
            return;
        }

        event.preventDefault();

        const $slot = $(this);
        $('#booking_type').val('hourly');
        $('#half_day_slot').val('');
        syncBookingWindow();
        $('#start_time').val($slot.data('start'));
        $('#end_time').val($slot.data('end'));
        refreshBookingEstimate();
        validateSelectedBookingWindow();
        syncBookingAvailabilitySelection();
    });

    syncBookingWindow();
    syncPaymentFields();
    refreshBookingEstimate();
    refreshBookingAvailability();
    refreshBookingCabinOptions();
    validateSelectedBookingWindow();
});
</script>
@endpush
