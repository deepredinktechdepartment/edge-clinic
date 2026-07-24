@extends('template_v1')

@section('content')
@include('cabins.partials.styles')

<div class="cabin-shell">
    @include('cabins.partials.page-header', [
        'title' => $pageTitle,
        'subtitle' => 'System-wide rates, clinic hours, GST, invoice day, and billing delivery for cabin management.',
        'actions' => [
            ['url' => route('admin.cabins.reports'), 'label' => 'Reports', 'icon' => 'bi bi-bar-chart', 'class' => 'btn-outline-secondary'],
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
            <form id="cabinSettingsForm" method="POST" action="{{ route('admin.cabins.settings.update') }}">
                @csrf

                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="form-label">Clinic Opens</label>
                        <input type="time" name="clinic_open_time" class="form-control" value="{{ old('clinic_open_time', substr($settings->clinic_open_time, 0, 5)) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Clinic Closes</label>
                        <input type="time" name="clinic_close_time" class="form-control" value="{{ old('clinic_close_time', substr($settings->clinic_close_time, 0, 5)) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Minimum Booking Duration (mins)</label>
                        <input type="number" name="min_booking_duration_minutes" class="form-control" min="15" value="{{ old('min_booking_duration_minutes', $settings->min_booking_duration_minutes) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Buffer Between Sessions (mins)</label>
                        <input type="number" name="buffer_minutes" class="form-control" min="0" value="{{ old('buffer_minutes', $settings->buffer_minutes) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Default GST %</label>
                        <input type="number" step="0.01" name="default_gst_percent" class="form-control" min="0" max="100" value="{{ old('default_gst_percent', $settings->default_gst_percent) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Monthly Invoice Day</label>
                        <input type="number" name="monthly_invoice_day" class="form-control" min="1" max="31" value="{{ old('monthly_invoice_day', $settings->monthly_invoice_day) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Payment Due Days</label>
                        <input type="number" name="payment_due_days" class="form-control" min="0" max="90" value="{{ old('payment_due_days', $settings->payment_due_days) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Send Invoice Via</label>
                        <select name="invoice_delivery_mode" class="form-select" required>
                            @foreach(['email' => 'Email', 'whatsapp' => 'WhatsApp', 'both' => 'Both'] as $value => $label)
                                <option value="{{ $value }}" {{ old('invoice_delivery_mode', $settings->invoice_delivery_mode) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Clinic GSTIN</label>
                        <input type="text" name="clinic_gstin" class="form-control" value="{{ old('clinic_gstin', $settings->clinic_gstin) }}">
                    </div>
                    <div class="col-12"><hr class="my-1"><div class="fw-semibold">Booking Shifts</div><div class="small text-muted">These are used when a Shift booking is selected instead of manual hourly timing.</div></div>
                    @foreach($bookingShifts as $index => $shift)
                        <div class="col-md-4"><label class="form-label">Shift {{ $index + 1 }} Name</label><input type="hidden" name="booking_shifts[{{ $index }}][key]" value="{{ $shift['key'] }}"><input type="text" class="form-control" name="booking_shifts[{{ $index }}][label]" value="{{ old('booking_shifts.' . $index . '.label', $shift['label']) }}" required></div>
                        <div class="col-md-4"><label class="form-label">Shift {{ $index + 1 }} Start</label><input type="time" class="form-control" name="booking_shifts[{{ $index }}][start]" value="{{ old('booking_shifts.' . $index . '.start', $shift['start']) }}" required></div>
                        <div class="col-md-4"><label class="form-label">Shift {{ $index + 1 }} End</label><input type="time" class="form-control" name="booking_shifts[{{ $index }}][end]" value="{{ old('booking_shifts.' . $index . '.end', $shift['end']) }}" required></div>
                    @endforeach
                    <div class="col-md-4">
                        <label class="form-label">Standard Hourly Rate</label>
                        <input type="number" step="0.01" min="0" name="standard_hourly_rate" class="form-control" value="{{ old('standard_hourly_rate', $settings->standard_hourly_rate) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Premium Hourly Rate</label>
                        <input type="number" step="0.01" min="0" name="premium_hourly_rate" class="form-control" value="{{ old('premium_hourly_rate', $settings->premium_hourly_rate) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Procedure Hourly Rate</label>
                        <input type="number" step="0.01" min="0" name="procedure_hourly_rate" class="form-control" value="{{ old('procedure_hourly_rate', $settings->procedure_hourly_rate) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Standard Monthly Rate</label>
                        <input type="number" step="0.01" min="0" name="standard_monthly_rate" class="form-control" value="{{ old('standard_monthly_rate', $settings->standard_monthly_rate) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Premium Monthly Rate</label>
                        <input type="number" step="0.01" min="0" name="premium_monthly_rate" class="form-control" value="{{ old('premium_monthly_rate', $settings->premium_monthly_rate) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Procedure Monthly Rate</label>
                        <input type="number" step="0.01" min="0" name="procedure_monthly_rate" class="form-control" value="{{ old('procedure_monthly_rate', $settings->procedure_monthly_rate) }}" required>
                    </div>
                </div>

                <div class="mt-4 cabin-form-actions">
                    <button type="submit" class="btn btn-brand btn-sm">Save Settings</button>
                    <a href="{{ route('admin.cabins.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
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
            $submit.html('Saving...');
        }

        form.submit();
        return true;
    }

    $('#cabinSettingsForm').validate({
        errorClass: 'text-danger',
        errorElement: 'small',
        submitHandler: function (form) {
            return lockCabinSubmit(form);
        }
    });
});
</script>
@endpush
