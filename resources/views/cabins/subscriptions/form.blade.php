@extends('template_v1')

@section('content')
@include('cabins.partials.styles')

<div class="cabin-shell">
    @include('cabins.partials.page-header', [
        'title' => $pageTitle,
        'subtitle' => 'Monthly subscriptions block hourly bookings for the same cabin during the selected period.',
        'actions' => [
            ['url' => route('admin.cabins.subscriptions.index'), 'label' => 'Back to Subscriptions', 'icon' => 'bi bi-arrow-left', 'class' => 'btn-outline-secondary'],
        ],
    ])

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
        <div class="panel-body">
            <form id="subscriptionForm" method="POST" action="{{ $subscription->exists ? route('admin.cabins.subscriptions.update', $subscription->id) : route('admin.cabins.subscriptions.store') }}">
                @csrf
                @if($subscription->exists)
                    @method('PUT')
                @endif

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Doctor <span class="text-danger">*</span></label>
                        <select name="doctor_id" id="subscription_doctor_id" class="form-select" required>
                            <option value="">Select doctor</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" {{ (string) old('doctor_id', $subscription->doctor_id) === (string) $doctor->id ? 'selected' : '' }}>{{ $doctor->name }}</option>
                            @endforeach
                        </select>
                        <div class="alert alert-warning mt-3 mb-0 py-3 d-none" id="doctor_existing_subscriptions_box">
                            <div class="fw-semibold mb-2">Existing active subscriptions for this doctor</div>
                            <div id="doctor_existing_subscriptions_list" class="d-flex flex-column gap-2"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cabin <span class="text-danger">*</span></label>
                        <select name="cabin_id" id="subscription_cabin_id" class="form-select" required>
                            <option value="">Select cabin</option>
                            @foreach($cabins as $cabin)
                                @php
                                    $isCurrentCabin = (string) old('cabin_id', $subscription->cabin_id) === (string) $cabin->id;
                                    $subscriptionBlocked = in_array($cabin->status, ['occupied', 'maintenance', 'inactive'], true) || $cabin->booking_mode === 'hourly';
                                    $subscriptionReason = $cabin->booking_mode === 'hourly'
                                        ? 'Hourly Only'
                                        : ucfirst($cabin->status);
                                @endphp
                                <option value="{{ $cabin->id }}"
                                        data-base-label="{{ $cabin->cabin_code }} - {{ $cabin->name }}"
                                        data-monthly="{{ $cabin->monthly_rate }}"
                                        data-type="{{ $cabin->cabin_type }}"
                                        data-start="{{ $cabin->operating_start_time ? substr($cabin->operating_start_time, 0, 5) : substr($settings->clinic_open_time, 0, 5) }}"
                                        data-end="{{ $cabin->operating_end_time ? substr($cabin->operating_end_time, 0, 5) : substr($settings->clinic_close_time, 0, 5) }}"
                                        data-mode="{{ $cabin->booking_mode }}"
                                        data-status="{{ $cabin->status }}"
                                        data-static-blocked="{{ $subscriptionBlocked && ! $isCurrentCabin ? '1' : '0' }}"
                                        data-static-reason="{{ $subscriptionBlocked && ! $isCurrentCabin ? $subscriptionReason : '' }}"
                                        @if($subscriptionBlocked && ! $isCurrentCabin) style="color: #8a97a8; background-color: #f3f5f7;" aria-disabled="true" @endif
                                        @if($subscriptionBlocked && ! $isCurrentCabin) disabled @endif
                                        {{ (string) old('cabin_id', $subscription->cabin_id) === (string) $cabin->id ? 'selected' : '' }}>
                                    {{ $cabin->cabin_code }} - {{ $cabin->name }}@if($subscriptionBlocked && ! $isCurrentCabin) ({{ $subscriptionReason }})@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" id="subscription_start_date" class="form-control" value="{{ old('start_date', optional($subscription->start_date)->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" id="subscription_end_date" class="form-control" value="{{ old('end_date', optional($subscription->end_date)->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Daily Start Time <span class="text-danger">*</span></label>
                        <input type="time" name="subscription_start_time" id="subscription_start_time" class="form-control" value="{{ old('subscription_start_time', $subscription->subscription_start_time ? substr($subscription->subscription_start_time, 0, 5) : substr($settings->clinic_open_time, 0, 5)) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Daily End Time <span class="text-danger">*</span></label>
                        <input type="time" name="subscription_end_time" id="subscription_end_time" class="form-control" value="{{ old('subscription_end_time', $subscription->subscription_end_time ? substr($subscription->subscription_end_time, 0, 5) : substr($settings->clinic_close_time, 0, 5)) }}" required>
                    </div>
                    @php $selectedDays = old('subscription_days', $subscription->subscription_days ?: [0, 1, 2, 3, 4, 5, 6]); @endphp
                    <div class="col-12">
                        <label class="form-label mb-2">Subscription Days <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach([0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'] as $dayValue => $dayLabel)
                                <label class="form-check form-check-inline m-0"><input class="form-check-input" type="checkbox" name="subscription_days[]" value="{{ $dayValue }}" {{ in_array($dayValue, array_map('intval', $selectedDays), true) ? 'checked' : '' }}> <span class="form-check-label">{{ $dayLabel }}</span></label>
                            @endforeach
                        </div>
                        <div class="small text-muted mt-2">Only these weekdays are reserved. All other days remain available for hourly or another monthly subscription.</div>
                    </div>
                    <div class="col-12">
                        <div class="small text-muted" id="subscription_schedule_hint">Pick a doctor to load subscription timing from that doctor's appointment configuration.</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">GST %</label>
                        <input type="number" name="gst_percent" id="subscription_gst_percent" step="0.01" min="0" max="100" class="form-control" value="{{ old('gst_percent', $subscription->gst_percent ?? $settings->default_gst_percent) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Monthly Rate</label>
                        <input type="number" name="monthly_rate" id="subscription_monthly_rate" step="0.01" min="0" class="form-control" value="{{ old('monthly_rate', $subscription->monthly_rate) }}" placeholder="Uses cabin default if left blank">
                        <div class="small text-muted mt-1">You can keep the cabin default rate or set a doctor-specific subscription amount here.</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Invoice Day <span class="text-danger">*</span></label>
                        <input type="number" name="invoice_day" min="1" max="31" class="form-control" value="{{ old('invoice_day', $subscription->invoice_day ?? $settings->monthly_invoice_day) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            @foreach(['active' => 'Active', 'expired' => 'Expired', 'cancelled' => 'Cancelled'] as $value => $label)
                                <option value="{{ $value }}" {{ old('status', $subscription->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4"><label class="form-label">Payment Choice <span class="text-danger">*</span></label><select name="payment_choice" id="subscription_payment_choice" class="form-select" required>@foreach(['pay_now'=>'Pay Now','pay_later'=>'Pay Later','free_booking'=>'Free','no_payment_required'=>'No Payment Required'] as $value=>$label)<option value="{{ $value }}" {{ old('payment_choice', $subscription->payment_choice ?: 'pay_later') === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label">Payment Mode</label><select name="payment_mode" id="subscription_payment_mode" class="form-select"><option value="">Select mode</option>@foreach(['cash'=>'Cash','upi'=>'UPI','card'=>'Card'] as $value=>$label)<option value="{{ $value }}" {{ old('payment_mode', $subscription->payment_mode) === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label">Reference No.</label><input type="text" name="transaction_reference" id="subscription_transaction_reference" class="form-control" value="{{ old('transaction_reference', $subscription->transaction_reference) }}"></div>
                    <div class="col-md-4">
                        <label class="form-label">Estimated Total</label>
                        <input type="text" id="subscription_total" class="form-control bg-light" value="&#8377;{{ number_format((float) ($subscription->total_amount ?? 0), 2) }}" readonly>
                    </div>
                    <div class="col-12">
                        <div class="small text-muted">Monthly subscription blocks only its selected weekdays and daily time window. Remaining hours and days stay open.</div>
                    </div>
                    <div class="col-12">
                        <div class="alert alert-info mb-0 py-2" id="subscription_availability_hint">Select cabin, dates, and daily time to check whether this monthly subscription window is available.</div>
                        <div class="alert alert-danger mt-3 mb-0 py-2 d-none" id="subscription_availability_conflict"></div>
                        <div class="alert alert-warning mt-3 mb-0 py-3 d-none" id="subscription_doctor_conflict_box">
                            <div class="fw-semibold mb-2">This doctor already has a subscription.</div>
                            <div class="small mb-3" id="subscription_doctor_conflict_text"></div>
                            <a href="#" class="btn btn-outline-secondary btn-sm" id="subscription_doctor_conflict_link">Edit Existing Subscription</a>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="4">{{ old('notes', $subscription->notes) }}</textarea>
                    </div>
                </div>

                <div class="mt-4 cabin-form-actions">
                    <button type="submit" class="btn btn-brand btn-sm">{{ $subscription->exists ? 'Update Subscription' : 'Save Subscription' }}</button>
                    <a href="{{ route('admin.cabins.subscriptions.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const monthlyRates = {
    consultation: {{ (float) $settings->standard_monthly_rate }},
    premium: {{ (float) $settings->premium_monthly_rate }},
    procedure: {{ (float) $settings->procedure_monthly_rate }},
    other: {{ (float) $settings->standard_monthly_rate }}
};
const subscriptionExists = {{ $subscription->exists ? 'true' : 'false' }};
const subscriptionAvailabilityUrl = '{{ route('admin.cabins.subscriptions.availability') }}';
const subscriptionDoctorWindowUrl = '{{ route('admin.cabins.subscriptions.doctor-window') }}';
const doctorSubscriptionsUrl = '{{ route('admin.cabins.subscriptions.doctor-subscriptions') }}';
const editingSubscriptionId = '{{ $subscription->exists ? $subscription->id : '' }}';
let subscriptionAvailabilityData = null;
let subscriptionUsesDoctorSchedule = false;

function getSelectedSubscriptionCabinMeta(key) {
    const selected = $('#subscription_cabin_id option:selected');

    if (!selected.length) {
        return '';
    }

    return selected.attr('data-' + key) || '';
}

function formatSubscriptionCurrency(amount) {
    return '\u20B9' + Number(amount || 0).toFixed(2);
}

function syncSubscriptionWindowFromCabin(forceUpdate = false) {
    if (subscriptionUsesDoctorSchedule) {
        return;
    }

    const selected = $('#subscription_cabin_id option:selected');
    const start = selected.data('start');
    const end = selected.data('end');

    if (!selected.val()) {
        return;
    }

    if (start && (forceUpdate || !subscriptionExists || !$('#subscription_start_time').val())) {
        $('#subscription_start_time').val(start);
    }

    if (end && (forceUpdate || !subscriptionExists || !$('#subscription_end_time').val())) {
        $('#subscription_end_time').val(end);
    }
}

function setSubscriptionTimeLock(locked) {
    subscriptionUsesDoctorSchedule = locked;
    $('#subscription_start_time, #subscription_end_time').prop('readonly', locked);
    $('#subscription_start_time, #subscription_end_time').toggleClass('bg-light', locked);
}

function updateSubscriptionScheduleHint(message, isDoctorBased) {
    const fallbackMessage = 'Pick a doctor to load subscription timing from that doctor\'s appointment configuration.';
    const text = message || fallbackMessage;

    $('#subscription_schedule_hint')
        .text(text)
        .toggleClass('text-success', !!isDoctorBased)
        .toggleClass('text-muted', !isDoctorBased);
}

function clearDoctorSubscriptionsList() {
    $('#doctor_existing_subscriptions_box').addClass('d-none');
    $('#doctor_existing_subscriptions_list').html('');
}

function renderDoctorSubscriptionsList(items) {
    if (!items || !items.length) {
        clearDoctorSubscriptionsList();
        return;
    }

    const rows = items.map(function (item) {
        const parts = [item.cabin_label, item.period, item.time_window].filter(Boolean).join(' | ');

        return '<div class="border rounded-3 p-2 bg-white d-flex justify-content-between align-items-start gap-3">'
            + '<div><div class="small fw-semibold text-dark">Subscription #' + item.id + '</div><div class="small text-muted">' + parts + '</div></div>'
            + '<div class="d-flex gap-2 flex-shrink-0">'
            + '<a href="' + item.show_url + '" class="btn btn-outline-secondary btn-sm">View</a>'
            + '<a href="' + item.edit_url + '" class="btn btn-outline-secondary btn-sm">Edit</a>'
            + '</div></div>';
    }).join('');

    $('#doctor_existing_subscriptions_list').html(rows);
    $('#doctor_existing_subscriptions_box').removeClass('d-none');
}

function refreshDoctorSubscriptionsList() {
    const doctorId = $('#subscription_doctor_id').val();

    if (!doctorId) {
        clearDoctorSubscriptionsList();
        return $.Deferred().resolve().promise();
    }

    return $.get(doctorSubscriptionsUrl, {
        doctor_id: doctorId,
        subscription_id: editingSubscriptionId || ''
    }).done(function (response) {
        renderDoctorSubscriptionsList(response ? response.subscriptions : []);
    }).fail(function () {
        clearDoctorSubscriptionsList();
    });
}

function syncSubscriptionWindowFromDoctor() {
    const doctorId = $('#subscription_doctor_id').val();
    const cabinId = $('#subscription_cabin_id').val();

    if (!doctorId) {
        clearDoctorSubscriptionsList();
        setSubscriptionTimeLock(false);
        updateSubscriptionScheduleHint('', false);
        syncSubscriptionWindowFromCabin(true);
        refreshSubscriptionEstimate();
        refreshSubscriptionCabinOptions();
        refreshSubscriptionAvailability();
        return $.Deferred().resolve().promise();
    }

    return $.get(subscriptionDoctorWindowUrl, {
        doctor_id: doctorId,
        cabin_id: cabinId || ''
    }).done(function (response) {
        refreshDoctorSubscriptionsList();
        if (response && response.start_time) {
            $('#subscription_start_time').val(response.start_time);
        }

        if (response && response.end_time) {
            $('#subscription_end_time').val(response.end_time);
        }

        if (response && Array.isArray(response.available_days) && response.available_days.length) {
            $('input[name="subscription_days[]"]').each(function () {
                $(this).prop('checked', response.available_days.map(Number).includes(Number($(this).val())));
            });
        }

        setSubscriptionTimeLock(!!(response && response.uses_doctor_schedule));
        updateSubscriptionScheduleHint(response ? response.message : '', !!(response && response.uses_doctor_schedule));
        refreshSubscriptionEstimate();
        refreshSubscriptionCabinOptions();
        refreshSubscriptionAvailability();
    }).fail(function () {
        refreshDoctorSubscriptionsList();
        setSubscriptionTimeLock(false);
        updateSubscriptionScheduleHint('Could not load doctor appointment timing right now. You can continue with cabin working hours.', false);
        syncSubscriptionWindowFromCabin(true);
        refreshSubscriptionEstimate();
        refreshSubscriptionCabinOptions();
        refreshSubscriptionAvailability();
    });
}

function refreshSubscriptionEstimate() {
    const gst = parseFloat($('#subscription_gst_percent').val() || 0);
    const selectedValue = $('#subscription_cabin_id').val();

    if (!selectedValue) {
        $('#subscription_total').val(formatSubscriptionCurrency(0));
        return;
    }

    const cabinMonthlyRate = parseFloat(getSelectedSubscriptionCabinMeta('monthly') || 0);
    const cabinType = getSelectedSubscriptionCabinMeta('type');
    const fallbackMonthlyRate = parseFloat(monthlyRates[cabinType] || 0);
    const customMonthlyRate = parseFloat($('#subscription_monthly_rate').val());
    const defaultMonthlyRate = cabinMonthlyRate > 0 ? cabinMonthlyRate : fallbackMonthlyRate;
    const monthlyRate = Number.isFinite(customMonthlyRate) ? customMonthlyRate : defaultMonthlyRate;
    const total = monthlyRate + ((monthlyRate * gst) / 100);
    $('#subscription_total').val(formatSubscriptionCurrency(total));
}

function setSubscriptionCabinOptionState($option, disabled, reason) {
    const baseLabel = $option.data('base-label') || $option.text().replace(/\s+\(([^)]+)\)\s*$/, '');
    const label = disabled && reason ? baseLabel + ' (' + reason + ')' : baseLabel;

    $option.text(label);

    if (disabled) {
        $option.prop('disabled', true).attr('aria-disabled', 'true').attr('style', 'color: #8a97a8; background-color: #f3f5f7;');
    } else {
        $option.prop('disabled', false).removeAttr('aria-disabled').removeAttr('style');
    }
}

function refreshSubscriptionCabinOptions() {
    const startDate = $('#subscription_start_date').val();
    const endDate = $('#subscription_end_date').val();
    const startTime = $('#subscription_start_time').val();
    const endTime = $('#subscription_end_time').val();
    const subscriptionDays = $('input[name="subscription_days[]"]:checked').map(function () { return $(this).val(); }).get();
    const requests = [];

    $('#subscription_cabin_id option[value!=""]').each(function () {
        const $option = $(this);
        const staticBlocked = String($option.data('static-blocked')) === '1';
        const staticReason = $option.data('static-reason');

        if (staticBlocked) {
            setSubscriptionCabinOptionState($option, true, staticReason);
            return;
        }

        if (!startDate || !endDate || !startTime || !endTime) {
            setSubscriptionCabinOptionState($option, false, '');
            return;
        }

        requests.push(
            $.get(subscriptionAvailabilityUrl, {
                cabin_id: $option.val(),
                doctor_id: $('#subscription_doctor_id').val(),
                start_date: startDate,
                end_date: endDate,
                subscription_start_time: startTime,
                subscription_end_time: endTime,
                subscription_days: subscriptionDays,
                subscription_id: editingSubscriptionId || ''
            }).done(function (response) {
                const isCabinBlocked = response && response.valid === false && ['booking', 'subscription', 'status', 'mode', 'available_from'].includes(response.conflict_type);
                const isBooked = response && ['booking', 'subscription'].includes(response.conflict_type);
                setSubscriptionCabinOptionState($option, isCabinBlocked, isBooked ? 'Booked' : '');
            }).fail(function () {
                setSubscriptionCabinOptionState($option, false, '');
            })
        );
    });

    return $.when.apply($, requests);
}

function clearSubscriptionConflict() {
    subscriptionAvailabilityData = null;
    $('#subscription_availability_conflict').addClass('d-none').text('');
    $('#subscription_doctor_conflict_box').addClass('d-none');
    $('#subscription_doctor_conflict_text').text('');
    $('#subscription_doctor_conflict_link').attr('href', '#');
    $('#subscription_availability_hint')
        .removeClass('alert-danger')
        .addClass('alert-info')
        .text('Select cabin, dates, and daily time to check whether this monthly subscription window is available.');
    $('#subscriptionForm').find('button[type="submit"], input[type="submit"]').prop('disabled', false).removeClass('disabled');
}

function showSubscriptionConflict(message) {
    $('#subscription_doctor_conflict_box').addClass('d-none');
    $('#subscription_doctor_conflict_text').text('');
    $('#subscription_doctor_conflict_link').attr('href', '#');
    $('#subscription_availability_conflict').removeClass('d-none').text(message);
    $('#subscription_availability_hint')
        .removeClass('alert-info')
        .addClass('alert-danger')
        .text('Please adjust the cabin, date range, or daily time window before saving this subscription.');
    $('#subscriptionForm').find('button[type="submit"], input[type="submit"]').prop('disabled', true).addClass('disabled');
}

function showDoctorSubscriptionConflict(response) {
    const detailParts = [];

    if (response && response.conflict_cabin_label) {
        detailParts.push(response.conflict_cabin_label);
    }

    if (response && response.conflict_period) {
        detailParts.push(response.conflict_period);
    }

    if (response && response.conflict_time_window) {
        detailParts.push(response.conflict_time_window);
    }

    $('#subscription_availability_conflict').removeClass('d-none').text((response && response.message) ? response.message : 'This doctor already has an active subscription.');
    $('#subscription_doctor_conflict_text').text(detailParts.join(' | '));
    $('#subscription_doctor_conflict_link').attr('href', (response && response.conflict_edit_url) ? response.conflict_edit_url : '#');
    $('#subscription_doctor_conflict_box').removeClass('d-none');
    $('#subscription_availability_hint')
        .removeClass('alert-info')
        .addClass('alert-danger')
        .text('This doctor already has a matching subscription. Open it and update the timing if needed.');
    $('#subscriptionForm').find('button[type="submit"], input[type="submit"]').prop('disabled', true).addClass('disabled');
}

function showSubscriptionAvailable(message) {
    $('#subscription_availability_conflict').addClass('d-none').text('');
    $('#subscription_doctor_conflict_box').addClass('d-none');
    $('#subscription_doctor_conflict_text').text('');
    $('#subscription_doctor_conflict_link').attr('href', '#');
    $('#subscription_availability_hint')
        .removeClass('alert-danger')
        .addClass('alert-info')
        .text(message);
    $('#subscriptionForm').find('button[type="submit"], input[type="submit"]').prop('disabled', false).removeClass('disabled');
}

function refreshSubscriptionAvailability() {
    const cabinId = $('#subscription_cabin_id').val();
    const startDate = $('#subscription_start_date').val();
    const endDate = $('#subscription_end_date').val();
    const startTime = $('#subscription_start_time').val();
    const endTime = $('#subscription_end_time').val();
    const subscriptionDays = $('input[name="subscription_days[]"]:checked').map(function () { return $(this).val(); }).get();

    if (!cabinId || !startDate || !endDate || !startTime || !endTime || !subscriptionDays.length) {
        clearSubscriptionConflict();
        return;
    }

    $('#subscription_availability_hint')
        .removeClass('alert-danger')
        .addClass('alert-info')
        .text('Checking subscription availability for the selected cabin and time window...');

    $.get(subscriptionAvailabilityUrl, {
        cabin_id: cabinId,
        doctor_id: $('#subscription_doctor_id').val(),
        start_date: startDate,
        end_date: endDate,
          subscription_start_time: startTime,
          subscription_end_time: endTime,
          subscription_days: subscriptionDays,
        subscription_id: editingSubscriptionId || ''
    }).done(function (response) {
        subscriptionAvailabilityData = response || null;

        if (response && response.valid) {
            showSubscriptionAvailable(response.message || 'This cabin is available for the selected subscription period and daily time window.');
            return;
        }

        if (response && response.conflict_type === 'doctor_subscription') {
            showDoctorSubscriptionConflict(response);
            return;
        }

        showSubscriptionConflict((response && response.message) ? response.message : 'This subscription window is not available.');
    }).fail(function (xhr) {
        subscriptionAvailabilityData = null;

        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
            const firstField = Object.keys(xhr.responseJSON.errors)[0];
            const firstMessage = xhr.responseJSON.errors[firstField] ? xhr.responseJSON.errors[firstField][0] : 'Please check the selected subscription period and time window.';
            showSubscriptionConflict(firstMessage);
            return;
        }

        showSubscriptionConflict('Could not verify subscription availability right now. Please try again.');
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
            $submit.html('{{ $subscription->exists ? 'Updating...' : 'Saving...' }}');
        }

        form.submit();
        return true;
    }

    $('#subscriptionForm').validate({
        rules: {
            doctor_id: { required: true },
            cabin_id: { required: true },
            start_date: { required: true },
            end_date: { required: true },
            subscription_start_time: { required: true },
            subscription_end_time: { required: true },
            invoice_day: { required: true, min: 1, max: 31 }
        },
        errorClass: 'text-danger',
        errorElement: 'small',
        submitHandler: function (form) {
            if (subscriptionAvailabilityData && subscriptionAvailabilityData.valid === false) {
                return false;
            }

            return lockCabinSubmit(form);
        }
    });

    $('#subscription_doctor_id').on('change', function () {
        syncSubscriptionWindowFromDoctor();
    });
    $('#subscription_cabin_id').on('change', function () {
        if (subscriptionUsesDoctorSchedule && $('#subscription_doctor_id').val()) {
            syncSubscriptionWindowFromDoctor();
            return;
        }

        syncSubscriptionWindowFromCabin(true);
        refreshSubscriptionEstimate();
        refreshSubscriptionAvailability();
    });
    $('#subscription_start_date, #subscription_end_date, #subscription_start_time, #subscription_end_time, input[name="subscription_days[]"]').on('change', refreshSubscriptionCabinOptions);
    $('#subscription_start_date, #subscription_end_date, #subscription_start_time, #subscription_end_time, input[name="subscription_days[]"]').on('change', refreshSubscriptionAvailability);
    $('#subscription_monthly_rate').on('change keyup', refreshSubscriptionEstimate);
    $('#subscription_gst_percent').on('change keyup', refreshSubscriptionEstimate);
    refreshDoctorSubscriptionsList();
    syncSubscriptionWindowFromDoctor();
    syncSubscriptionWindowFromCabin();
    refreshSubscriptionEstimate();
    refreshSubscriptionAvailability();
    refreshSubscriptionCabinOptions();
});
</script>
@endpush
