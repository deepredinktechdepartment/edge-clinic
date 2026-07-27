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

    <style>
        .cabin-settings-intro { color: #66788a; font-size: .88rem; margin: 0; }
        .cabin-settings-section { border: 1px solid #e4ebf3; border-radius: 18px; background: #fff; padding: 1.15rem; }
        .cabin-settings-section + .cabin-settings-section { margin-top: 1rem; }
        .cabin-settings-title { display: flex; align-items: center; gap: .7rem; color: #10314f; font-weight: 700; font-size: 1rem; }
        .cabin-settings-step { width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; background: #eaf4ff; color: #1769aa; font-size: .8rem; }
        .cabin-settings-help { margin: .35rem 0 1rem 2.4rem; color: #6f7f90; font-size: .8rem; }
        .cabin-settings-section .form-label { color: #31465b; font-size: .78rem; font-weight: 700; margin-bottom: .38rem; }
        .cabin-settings-section .form-control, .cabin-settings-section .form-select { min-height: 42px; }
        .cabin-shift-editor { border: 1px solid #dfe8f2; border-radius: 16px; overflow: hidden; background: #fff; }
        .cabin-shift-editor + .cabin-shift-editor { margin-top: 1rem; }
        .cabin-shift-editor-head { display: flex; align-items: center; justify-content: space-between; gap: .75rem; padding: .8rem 1rem; background: linear-gradient(90deg, #f4f9ff, #fbfdff); border-bottom: 1px solid #e4ebf3; }
        .cabin-shift-editor-head strong { color: #10314f; }
        .cabin-shift-editor-head span { font-size: .78rem; color: #687b8e; }
        .cabin-shift-block { padding: 1rem; }
        .cabin-settings-group-label { color: #1769aa; font-size: .76rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; margin-bottom: .7rem; }
        .cabin-rate-note { background: #f6faff; border: 1px solid #e3eef9; border-radius: 10px; padding: .65rem .75rem; color: #607386; font-size: .78rem; }
        .cabin-settings-advanced { border: 1px dashed #ccd9e7; border-radius: 14px; overflow: hidden; }
        .cabin-settings-advanced summary { cursor: pointer; padding: .85rem 1rem; color: #38536d; font-weight: 700; font-size: .88rem; }
        .cabin-settings-advanced summary span { color: #718397; font-weight: 400; font-size: .78rem; margin-left: .35rem; }
        .cabin-settings-advanced-body { border-top: 1px dashed #d8e2ec; padding: 1rem; }
        @media (max-width: 767.98px) { .cabin-settings-help { margin-left: 0; } }
    </style>

    <div class="cabin-panel">
        <div class="panel-body">
            <form id="cabinSettingsForm" method="POST" action="{{ route('admin.cabins.settings.update') }}">
                @csrf

                <div class="cabin-settings-section">
                    <div class="cabin-settings-title"><span class="cabin-settings-step">1</span>Booking rules & billing</div>
                    <p class="cabin-settings-help">These are the clinic-wide rules used for every cabin booking and invoice.</p>
                    <div class="row g-3">
                        <div class="col-md-3"><label class="form-label">Minimum booking duration</label><div class="input-group"><input type="number" name="min_booking_duration_minutes" class="form-control" min="15" value="{{ old('min_booking_duration_minutes', $settings->min_booking_duration_minutes) }}" required><span class="input-group-text">mins</span></div></div>
                        <div class="col-md-3"><label class="form-label">Buffer between bookings</label><div class="input-group"><input type="number" name="buffer_minutes" class="form-control" min="0" value="{{ old('buffer_minutes', $settings->buffer_minutes) }}" required><span class="input-group-text">mins</span></div></div>
                        <div class="col-md-3"><label class="form-label">Default GST</label><div class="input-group"><input type="number" step="0.01" name="default_gst_percent" class="form-control" min="0" max="100" value="{{ old('default_gst_percent', $settings->default_gst_percent) }}" required><span class="input-group-text">%</span></div></div>
                        <div class="col-md-3"><label class="form-label">Payment due after</label><div class="input-group"><input type="number" name="payment_due_days" class="form-control" min="0" max="90" value="{{ old('payment_due_days', $settings->payment_due_days) }}" required><span class="input-group-text">days</span></div></div>
                        <div class="col-md-4"><label class="form-label">Monthly invoice day</label><input type="number" name="monthly_invoice_day" class="form-control" min="1" max="31" value="{{ old('monthly_invoice_day', $settings->monthly_invoice_day) }}" required></div>
                        <div class="col-md-4"><label class="form-label">Send invoice through</label><select name="invoice_delivery_mode" class="form-select" required>@foreach(['email' => 'Email', 'whatsapp' => 'WhatsApp', 'both' => 'Email + WhatsApp'] as $value => $label)<option value="{{ $value }}" {{ old('invoice_delivery_mode', $settings->invoice_delivery_mode) === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
                        <div class="col-md-4"><label class="form-label">Clinic GSTIN <span class="text-muted fw-normal">(optional)</span></label><input type="text" name="clinic_gstin" class="form-control" value="{{ old('clinic_gstin', $settings->clinic_gstin) }}" placeholder="Enter GSTIN if applicable"></div>
                    </div>
                </div>

                <div class="cabin-settings-section">
                    <div class="cabin-settings-title"><span class="cabin-settings-step">2</span>Shift timings, hourly rates & monthly plans</div>
                    <p class="cabin-settings-help">Set when each shift runs first. Then add the hourly price for each cabin type and the two monthly rental plans.</p>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6"><div class="cabin-rate-note"><strong>Clinic opens:</strong> <span id="clinicOpenPreview">{{ substr($settings->clinic_open_time, 0, 5) }}</span> &nbsp; <strong>Closes:</strong> <span id="clinicClosePreview">{{ substr($settings->clinic_close_time, 0, 5) }}</span><br>These are calculated automatically from the first and last shift.</div><input type="hidden" name="clinic_open_time" id="clinic_open_time" value="{{ old('clinic_open_time', substr($settings->clinic_open_time, 0, 5)) }}"><input type="hidden" name="clinic_close_time" id="clinic_close_time" value="{{ old('clinic_close_time', substr($settings->clinic_close_time, 0, 5)) }}"></div>
                        <div class="col-md-6"><div class="cabin-rate-note"><strong>Tip:</strong> Standard, Premium, Procedure and Custom are cabin types. Enter only the rate for this particular shift; booking totals calculate automatically.</div></div>
                    </div>
                    @foreach($bookingShifts as $index => $shift)
                        <div class="cabin-shift-editor">
                            <div class="cabin-shift-editor-head"><strong>Shift {{ $index + 1 }}</strong><span>Set timing, hourly price and monthly plans</span></div>
                            <div class="cabin-shift-block">
                                <input type="hidden" name="booking_shifts[{{ $index }}][key]" value="{{ $shift['key'] }}">
                                <div class="cabin-settings-group-label">A. Shift timing</div>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4"><label class="form-label">Shift name</label><input type="text" class="form-control" name="booking_shifts[{{ $index }}][label]" value="{{ old('booking_shifts.' . $index . '.label', $shift['label']) }}" required></div>
                                    <div class="col-md-4"><label class="form-label">Starts at</label><input type="time" class="form-control" name="booking_shifts[{{ $index }}][start]" value="{{ old('booking_shifts.' . $index . '.start', $shift['start']) }}" required></div>
                                    <div class="col-md-4"><label class="form-label">Ends at</label><input type="time" class="form-control" name="booking_shifts[{{ $index }}][end]" value="{{ old('booking_shifts.' . $index . '.end', $shift['end']) }}" required></div>
                                </div>
                                <div class="cabin-settings-group-label">B. Hourly price by cabin type</div>
                                <div class="row g-3 mb-4">
                                    @foreach(['consultation' => 'Standard', 'premium' => 'Premium', 'procedure' => 'Procedure', 'other' => 'Custom'] as $rateKey => $rateLabel)
                                        <div class="col-lg-3 col-md-6"><label class="form-label">{{ $rateLabel }}</label><div class="input-group"><span class="input-group-text">Rs</span><input type="number" step="0.01" min="0" class="form-control" name="booking_shifts[{{ $index }}][hourly_rates][{{ $rateKey }}]" value="{{ old('booking_shifts.' . $index . '.hourly_rates.' . $rateKey, $shift['hourly_rates'][$rateKey] ?? $shift['hourly_rate']) }}" required><span class="input-group-text">/ hr</span></div></div>
                                    @endforeach
                                </div>
                                <div class="cabin-settings-group-label">C. Monthly rental plan</div>
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">3 days per week</label><div class="input-group"><span class="input-group-text">Rs</span><input type="number" step="0.01" min="0" class="form-control" name="booking_shifts[{{ $index }}][three_day_rate]" value="{{ old('booking_shifts.' . $index . '.three_day_rate', $shift['three_day_rate']) }}" required><span class="input-group-text">/ month</span></div></div>
                                    <div class="col-md-6"><label class="form-label">6 days per week</label><div class="input-group"><span class="input-group-text">Rs</span><input type="number" step="0.01" min="0" class="form-control" name="booking_shifts[{{ $index }}][six_day_rate]" value="{{ old('booking_shifts.' . $index . '.six_day_rate', $shift['six_day_rate']) }}" required><span class="input-group-text">/ month</span></div></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="cabin-settings-section">
                    <div class="cabin-settings-title"><span class="cabin-settings-step">3</span>Advanced fallback rates</div>
                    <p class="cabin-settings-help">These are used only if a booking falls outside the configured shifts or a cabin uses its own override.</p>
                    <details class="cabin-settings-advanced">
                        <summary>Open fallback rates <span>Usually you do not need to change these.</span></summary>
                        <div class="cabin-settings-advanced-body"><div class="row g-3">
                            <div class="col-md-4"><label class="form-label">Standard hourly rate</label><div class="input-group"><span class="input-group-text">Rs</span><input type="number" step="0.01" min="0" name="standard_hourly_rate" class="form-control" value="{{ old('standard_hourly_rate', $settings->standard_hourly_rate) }}" required></div></div>
                            <div class="col-md-4"><label class="form-label">Premium hourly rate</label><div class="input-group"><span class="input-group-text">Rs</span><input type="number" step="0.01" min="0" name="premium_hourly_rate" class="form-control" value="{{ old('premium_hourly_rate', $settings->premium_hourly_rate) }}" required></div></div>
                            <div class="col-md-4"><label class="form-label">Procedure hourly rate</label><div class="input-group"><span class="input-group-text">Rs</span><input type="number" step="0.01" min="0" name="procedure_hourly_rate" class="form-control" value="{{ old('procedure_hourly_rate', $settings->procedure_hourly_rate) }}" required></div></div>
                            <div class="col-md-4"><label class="form-label">Standard monthly rate</label><div class="input-group"><span class="input-group-text">Rs</span><input type="number" step="0.01" min="0" name="standard_monthly_rate" class="form-control" value="{{ old('standard_monthly_rate', $settings->standard_monthly_rate) }}" required></div></div>
                            <div class="col-md-4"><label class="form-label">Premium monthly rate</label><div class="input-group"><span class="input-group-text">Rs</span><input type="number" step="0.01" min="0" name="premium_monthly_rate" class="form-control" value="{{ old('premium_monthly_rate', $settings->premium_monthly_rate) }}" required></div></div>
                            <div class="col-md-4"><label class="form-label">Procedure monthly rate</label><div class="input-group"><span class="input-group-text">Rs</span><input type="number" step="0.01" min="0" name="procedure_monthly_rate" class="form-control" value="{{ old('procedure_monthly_rate', $settings->procedure_monthly_rate) }}" required></div></div>
                        </div></div>
                    </details>
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
    function syncClinicHoursFromShifts() {
        const starts = $('input[name$="[start]"]').map(function () { return $(this).val(); }).get().filter(Boolean).sort();
        const ends = $('input[name$="[end]"]').map(function () { return $(this).val(); }).get().filter(Boolean).sort();
        if (starts.length) {
            $('#clinic_open_time').val(starts[0]);
            $('#clinicOpenPreview').text(starts[0]);
        }
        if (ends.length) {
            $('#clinic_close_time').val(ends[ends.length - 1]);
            $('#clinicClosePreview').text(ends[ends.length - 1]);
        }
    }

    $('input[name$="[start]"], input[name$="[end]"]').on('change input', syncClinicHoursFromShifts);
    syncClinicHoursFromShifts();

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
