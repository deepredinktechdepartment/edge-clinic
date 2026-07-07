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
                        <select name="doctor_id" class="form-select" required>
                            <option value="">Select doctor</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" {{ (string) old('doctor_id', $subscription->doctor_id) === (string) $doctor->id ? 'selected' : '' }}>{{ $doctor->name }}</option>
                            @endforeach
                        </select>
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
                    <div class="col-md-3">
                        <label class="form-label">GST %</label>
                        <input type="number" name="gst_percent" id="subscription_gst_percent" step="0.01" min="0" max="100" class="form-control" value="{{ old('gst_percent', $subscription->gst_percent ?? $settings->default_gst_percent) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Invoice Day <span class="text-danger">*</span></label>
                        <input type="number" name="invoice_day" min="1" max="31" class="form-control" value="{{ old('invoice_day', $subscription->invoice_day ?? $settings->monthly_invoice_day) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            @foreach(['active' => 'Active', 'expired' => 'Expired', 'cancelled' => 'Cancelled'] as $value => $label)
                                <option value="{{ $value }}" {{ old('status', $subscription->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Estimated Total</label>
                        <input type="text" id="subscription_total" class="form-control bg-light" value="â‚¹{{ number_format((float) ($subscription->total_amount ?? 0), 2) }}" readonly>
                    </div>
                    <div class="col-12">
                        <div class="small text-muted">Monthly subscription now blocks only the selected daily time window. Remaining hours stay open for hourly booking.</div>
                    </div>
                    <div class="col-12">
                        <div class="alert alert-info mb-0 py-2" id="subscription_availability_hint">Select cabin, dates, and daily time to check whether this monthly subscription window is available.</div>
                        <div class="alert alert-danger mt-3 mb-0 py-2 d-none" id="subscription_availability_conflict"></div>
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
const editingSubscriptionId = '{{ $subscription->exists ? $subscription->id : '' }}';
let subscriptionAvailabilityData = null;

function syncSubscriptionWindowFromCabin(forceUpdate = false) {
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

function refreshSubscriptionEstimate() {
    const selected = $('#subscription_cabin_id option:selected');
    const gst = parseFloat($('#subscription_gst_percent').val() || 0);

    if (!selected.val()) {
        $('#subscription_total').val('â‚¹0.00');
        return;
    }

    const monthlyRate = parseFloat(selected.data('monthly') || 0) || monthlyRates[selected.data('type')] || 0;
    const total = monthlyRate + ((monthlyRate * gst) / 100);
    $('#subscription_total').val('â‚¹' + total.toFixed(2));
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
                start_date: startDate,
                end_date: endDate,
                subscription_start_time: startTime,
                subscription_end_time: endTime,
                subscription_id: editingSubscriptionId || ''
            }).done(function (response) {
                const isBooked = response && response.valid === false && ['booking', 'subscription'].includes(response.conflict_type);
                setSubscriptionCabinOptionState($option, response && response.valid === false, isBooked ? 'Booked' : '');
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
    $('#subscription_availability_hint')
        .removeClass('alert-danger')
        .addClass('alert-info')
        .text('Select cabin, dates, and daily time to check whether this monthly subscription window is available.');
    $('#subscriptionForm').find('button[type="submit"], input[type="submit"]').prop('disabled', false).removeClass('disabled');
}

function showSubscriptionConflict(message) {
    $('#subscription_availability_conflict').removeClass('d-none').text(message);
    $('#subscription_availability_hint')
        .removeClass('alert-info')
        .addClass('alert-danger')
        .text('Please adjust the cabin, date range, or daily time window before saving this subscription.');
    $('#subscriptionForm').find('button[type="submit"], input[type="submit"]').prop('disabled', true).addClass('disabled');
}

function showSubscriptionAvailable(message) {
    $('#subscription_availability_conflict').addClass('d-none').text('');
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

    if (!cabinId || !startDate || !endDate || !startTime || !endTime) {
        clearSubscriptionConflict();
        return;
    }

    $('#subscription_availability_hint')
        .removeClass('alert-danger')
        .addClass('alert-info')
        .text('Checking subscription availability for the selected cabin and time window...');

    $.get(subscriptionAvailabilityUrl, {
        cabin_id: cabinId,
        start_date: startDate,
        end_date: endDate,
        subscription_start_time: startTime,
        subscription_end_time: endTime,
        subscription_id: editingSubscriptionId || ''
    }).done(function (response) {
        subscriptionAvailabilityData = response || null;

        if (response && response.valid) {
            showSubscriptionAvailable(response.message || 'This cabin is available for the selected subscription period and daily time window.');
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

    $('#subscription_cabin_id').on('change', function () {
        syncSubscriptionWindowFromCabin(true);
        refreshSubscriptionEstimate();
        refreshSubscriptionAvailability();
    });
    $('#subscription_start_date, #subscription_end_date, #subscription_start_time, #subscription_end_time').on('change', refreshSubscriptionCabinOptions);
    $('#subscription_start_date, #subscription_end_date, #subscription_start_time, #subscription_end_time').on('change', refreshSubscriptionAvailability);
    $('#subscription_gst_percent').on('change keyup', refreshSubscriptionEstimate);
    syncSubscriptionWindowFromCabin();
    refreshSubscriptionEstimate();
    refreshSubscriptionAvailability();
    refreshSubscriptionCabinOptions();
});
</script>
@endpush
