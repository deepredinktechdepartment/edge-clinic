@extends('template_v1')

@section('content')
@include('cabins.partials.styles')

@php
    $selectedType = old('cabin_type', $cabin->cabin_type ?: 'consultation');
    $selectedMode = old('booking_mode', $cabin->booking_mode ?: 'both');
    $selectedFacilities = collect(old('facility_ids', $cabin->exists ? $cabin->facilities->pluck('id')->all() : []))->map(fn ($id) => (string) $id)->all();
    $freeFacilities = $facilities->where('pricing_type', 'free')->values();
    $paidFacilities = $facilities->where('pricing_type', 'paid')->values();
@endphp

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

    <form id="cabinForm" method="POST" action="{{ $cabin->exists ? route('admin.cabins.update', $cabin->id) : route('admin.cabins.store') }}">
        @csrf
        @if($cabin->exists)
            @method('PUT')
        @endif

        <div class="row g-4 align-items-start">
            <div class="col-xl-8">
                <div class="d-flex flex-column gap-4">
                    <div class="cabin-panel">
                        <div class="panel-head">
                            <div>
                                <h5 class="mb-1">Cabin Identity</h5>
                                <div class="text-muted">Basic identification and location</div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Cabin Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $cabin->name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Cabin Code <span class="text-danger">*</span></label>
                                    <input type="text" name="cabin_code" id="cabin_code" class="form-control" value="{{ old('cabin_code', $cabin->cabin_code) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Floor / Location</label>
                                    <input type="text" name="floor_name" id="floor_name" class="form-control" value="{{ old('floor_name', $cabin->floor_name) }}" placeholder="Ground Floor">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Room Number</label>
                                    <input type="text" name="room_number" class="form-control" value="{{ old('room_number', $cabin->room_number) }}" placeholder="103">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description / Notes</label>
                                    <textarea name="notes" class="form-control" rows="4" placeholder="Equipment, special use, access restrictions">{{ old('notes', $cabin->notes) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="cabin-panel">
                        <div class="panel-head">
                            <div>
                                <h5 class="mb-1">Cabin Type & Rates</h5>
                                <div class="text-muted">Type decides the default rates from Cabin Settings</div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="row g-3 mb-4 cabin-type-row">
                                <div class="col-md-3">
                                    <label class="w-100">
                                        <input type="radio" class="btn-check" name="cabin_type" value="consultation" {{ $selectedType === 'consultation' ? 'checked' : '' }}>
                                        <span class="cabin-select-card d-block">
                                            <strong>Standard</strong>
                                            <small>Consultation</small>
                                            <em>Rs {{ number_format((float) $settings->standard_hourly_rate, 2) }}/hr · Rs {{ number_format((float) $settings->standard_monthly_rate, 2) }}/month</em>
                                        </span>
                                    </label>
                                </div>
                                <div class="col-md-3">
                                    <label class="w-100">
                                        <input type="radio" class="btn-check" name="cabin_type" value="premium" {{ $selectedType === 'premium' ? 'checked' : '' }}>
                                        <span class="cabin-select-card d-block">
                                            <strong>Premium</strong>
                                            <small>Larger suite-style</small>
                                            <em>Rs {{ number_format((float) $settings->premium_hourly_rate, 2) }}/hr · Rs {{ number_format((float) $settings->premium_monthly_rate, 2) }}/month</em>
                                        </span>
                                    </label>
                                </div>
                                <div class="col-md-3">
                                    <label class="w-100">
                                        <input type="radio" class="btn-check" name="cabin_type" value="procedure" {{ $selectedType === 'procedure' ? 'checked' : '' }}>
                                        <span class="cabin-select-card d-block">
                                            <strong>Procedure</strong>
                                            <small>Minor procedures</small>
                                            <em>Rs {{ number_format((float) $settings->procedure_hourly_rate, 2) }}/hr · Rs {{ number_format((float) $settings->procedure_monthly_rate, 2) }}/month</em>
                                        </span>
                                    </label>
                                </div>
                                <div class="col-md-3">
                                    <label class="w-100">
                                        <input type="radio" class="btn-check" name="cabin_type" value="other" {{ $selectedType === 'other' ? 'checked' : '' }}>
                                        <span class="cabin-select-card d-block">
                                            <strong>Other</strong>
                                            <small>Custom cabin setup</small>
                                            <em>Uses standard default rates until you override them below</em>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Booking Mode <span class="text-danger">*</span></label>
                                    <select name="booking_mode" id="booking_mode" class="form-select" required>
                                        @foreach(['hourly' => 'Hourly', 'monthly' => 'Monthly', 'both' => 'Both'] as $value => $label)
                                            <option value="{{ $value }}" {{ $selectedMode === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4" id="hourly_rate_wrap">
                                    <label class="form-label">Hourly Rate</label>
                                    <input type="number" step="0.01" min="0" name="hourly_rate" id="hourly_rate" class="form-control" value="{{ old('hourly_rate', $cabin->hourly_rate) }}">
                                </div>
                                <div class="col-md-4" id="monthly_rate_wrap">
                                    <label class="form-label">Monthly Rate</label>
                                    <input type="number" step="0.01" min="0" name="monthly_rate" id="monthly_rate" class="form-control" value="{{ old('monthly_rate', $cabin->monthly_rate) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Capacity <span class="text-danger">*</span></label>
                                    <input type="number" name="capacity" min="1" class="form-control" value="{{ old('capacity', $cabin->capacity) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Default Working Start</label>
                                    <input type="time" name="operating_start_time" class="form-control" value="{{ old('operating_start_time', $cabin->operating_start_time ? substr($cabin->operating_start_time, 0, 5) : substr($settings->clinic_open_time, 0, 5)) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Default Working End</label>
                                    <input type="time" name="operating_end_time" class="form-control" value="{{ old('operating_end_time', $cabin->operating_end_time ? substr($cabin->operating_end_time, 0, 5) : substr($settings->clinic_close_time, 0, 5)) }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="cabin-panel">
                        <div class="panel-head">
                            <div>
                                <h5 class="mb-1">Facilities & Equipment</h5>
                                <div class="text-muted">Choose the facilities available in this cabin</div>
                            </div>
                            <a href="{{ route('admin.cabins.facilities.index') }}" class="btn btn-outline-secondary btn-sm">Manage Facilities</a>
                        </div>
                        <div class="panel-body">
                            @if($facilities->isEmpty())
                                <div class="empty-note w-100">No facility master records yet. Add them from the Facilities menu.</div>
                            @else
                                @if($freeFacilities->isNotEmpty())
                                    <div class="facility-group">
                                        <div class="facility-group-head">
                                            <div>
                                                <div class="facility-group-title">Free Facilities</div>
                                                <div class="facility-group-sub">Included without extra cabin charges</div>
                                            </div>
                                            <span class="facility-group-count">{{ $freeFacilities->count() }}</span>
                                        </div>
                                        <div class="facility-grid">
                                            @foreach($freeFacilities as $facility)
                                                <label class="facility-item facility-item-free">
                                                    <input type="checkbox" name="facility_ids[]" value="{{ $facility->id }}" {{ in_array((string) $facility->id, $selectedFacilities, true) ? 'checked' : '' }}>
                                                    <span class="facility-choice">
                                                        <span class="facility-mark"></span>
                                                        <span class="facility-copy">
                                                            <span class="facility-title-row">
                                                                <span class="facility-title">{{ $facility->name }}</span>
                                                                <span class="facility-badge facility-badge-free">Free</span>
                                                            </span>
                                                            <small class="facility-meta">Included with cabin</small>
                                                        </span>
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if($paidFacilities->isNotEmpty())
                                    <div class="facility-group">
                                        <div class="facility-group-head">
                                            <div>
                                                <div class="facility-group-title">Paid Facilities</div>
                                                <div class="facility-group-sub">Extra charge items billed per use or service</div>
                                            </div>
                                            <span class="facility-group-count facility-group-count-paid">{{ $paidFacilities->count() }}</span>
                                        </div>
                                        <div class="facility-grid">
                                            @foreach($paidFacilities as $facility)
                                                <label class="facility-item facility-item-paid">
                                                    <input type="checkbox" name="facility_ids[]" value="{{ $facility->id }}" {{ in_array((string) $facility->id, $selectedFacilities, true) ? 'checked' : '' }}>
                                                    <span class="facility-choice">
                                                        <span class="facility-mark"></span>
                                                        <span class="facility-copy">
                                                            <span class="facility-title-row">
                                                                <span class="facility-title">{{ $facility->name }}</span>
                                                                <span class="facility-badge facility-badge-paid">Paid</span>
                                                            </span>
                                                            <small class="facility-meta">Rs {{ number_format((float) $facility->rate, 2) }} {{ $facility->charge_label ?: 'Per Use' }}</small>
                                                        </span>
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endif
                            @if(false)
                                @forelse($facilities as $facility)
                                    <label class="facility-item facility-item-{{ $facility->pricing_type === 'paid' ? 'paid' : 'free' }}">
                                        <input type="checkbox" name="facility_ids[]" value="{{ $facility->id }}" {{ in_array((string) $facility->id, $selectedFacilities, true) ? 'checked' : '' }}>
                                        <span class="facility-choice">
                                            <span class="facility-mark"></span>
                                            <span class="facility-copy">
                                                <span class="facility-title-row">
                                                    <span class="facility-title">{{ $facility->name }}</span>
                                                    @if($facility->pricing_type === 'paid')
                                                        <span class="facility-badge facility-badge-paid">Paid</span>
                                                    @else
                                                        <span class="facility-badge facility-badge-free">Free</span>
                                                    @endif
                                                </span>
                                                @if($facility->pricing_type === 'paid')
                                                    <small class="facility-meta">Paid · Rs {{ number_format((float) $facility->rate, 2) }} {{ $facility->charge_label ?: 'Per Use' }}</small>
                                                @else
                                                    <small class="facility-meta">Free</small>
                                                @endif
                                            </span>
                                        </span>
                                    </label>
                                @empty
                                    <div class="empty-note w-100">No facility master records yet. Add them from the Facilities menu.</div>
                                @endforelse
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="d-flex flex-column gap-4">
                    <div class="cabin-panel">
                        <div class="panel-head">
                            <div>
                                <h5 class="mb-1">Status & Availability</h5>
                                <div class="text-muted">Starting availability for this cabin</div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Initial Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-select" required>
                                        @foreach(['available' => 'Available', 'occupied' => 'Occupied', 'maintenance' => 'Maintenance', 'inactive' => 'Inactive'] as $value => $label)
                                            <option value="{{ $value }}" {{ old('status', $cabin->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Available From</label>
                                    <input type="date" name="available_from" class="form-control" value="{{ old('available_from', optional($cabin->available_from)->format('Y-m-d')) }}">
                                </div>
                                <div class="col-12">
                                    <div class="border rounded-4 p-3 bg-light">
                                        <div class="mini-label">Current Setting Rates</div>
                                        <div class="small text-muted mt-2">Standard: Rs {{ number_format((float) $settings->standard_hourly_rate, 2) }}/hr, Rs {{ number_format((float) $settings->standard_monthly_rate, 2) }}/month</div>
                                        <div class="small text-muted">Premium: Rs {{ number_format((float) $settings->premium_hourly_rate, 2) }}/hr, Rs {{ number_format((float) $settings->premium_monthly_rate, 2) }}/month</div>
                                        <div class="small text-muted">Procedure: Rs {{ number_format((float) $settings->procedure_hourly_rate, 2) }}/hr, Rs {{ number_format((float) $settings->procedure_monthly_rate, 2) }}/month</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="cabin-panel">
                        <div class="panel-head">
                            <div>
                                <h5 class="mb-1">Preview Card</h5>
                                <div class="text-muted">This is how the cabin appears to users</div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="cabin-card">
                                <div class="status-bar" id="preview_status_bar"></div>
                                <div class="body">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div>
                                            <div class="cabin-code" id="preview_code">{{ old('cabin_code', $cabin->cabin_code ?: 'CABIN') }}</div>
                                            <h5 id="preview_name">{{ old('name', $cabin->name ?: 'New Cabin') }}</h5>
                                            <div class="cabin-meta" id="preview_meta">{{ ucfirst($selectedType) }} · {{ old('floor_name', $cabin->floor_name ?: 'Floor not set') }}</div>
                                        </div>
                                        <span class="status-chip" id="preview_status">{{ ucfirst(old('status', $cabin->status ?: 'available')) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="cabin-panel">
                        <div class="panel-body cabin-form-actions">
                            <button type="submit" class="btn btn-brand btn-sm">{{ $cabin->exists ? 'Update Cabin' : 'Save Cabin' }}</button>
                            <a href="{{ route('admin.cabins.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
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
            $submit.data('original-html', $submit.html());
            $submit.html('{{ $cabin->exists ? 'Updating...' : 'Saving...' }}');
        }

        form.submit();
        return true;
    }

    const defaults = {
        consultation: { hourly: {{ (float) $settings->standard_hourly_rate }}, monthly: {{ (float) $settings->standard_monthly_rate }} },
        premium: { hourly: {{ (float) $settings->premium_hourly_rate }}, monthly: {{ (float) $settings->premium_monthly_rate }} },
        procedure: { hourly: {{ (float) $settings->procedure_hourly_rate }}, monthly: {{ (float) $settings->procedure_monthly_rate }} },
        other: { hourly: {{ (float) $settings->standard_hourly_rate }}, monthly: {{ (float) $settings->standard_monthly_rate }} }
    };

    function selectedType() {
        return $('input[name="cabin_type"]:checked').val() || 'consultation';
    }

    function syncRateFields() {
        const type = selectedType();
        const mode = $('#booking_mode').val();
        const hourlyEnabled = mode === 'hourly' || mode === 'both';
        const monthlyEnabled = mode === 'monthly' || mode === 'both';

        $('#hourly_rate').prop('disabled', !hourlyEnabled);
        $('#monthly_rate').prop('disabled', !monthlyEnabled);
        $('#hourly_rate_wrap').toggleClass('opacity-50', !hourlyEnabled);
        $('#monthly_rate_wrap').toggleClass('opacity-50', !monthlyEnabled);

        if (hourlyEnabled && !$('#hourly_rate').val()) {
            $('#hourly_rate').val(defaults[type].hourly);
        }
        if (monthlyEnabled && !$('#monthly_rate').val()) {
            $('#monthly_rate').val(defaults[type].monthly);
        }
        if (!hourlyEnabled) {
            $('#hourly_rate').val('');
        }
        if (!monthlyEnabled) {
            $('#monthly_rate').val('');
        }
    }

    function refreshPreview() {
        const status = $('#status').val() || 'available';
        $('#preview_code').text($('#cabin_code').val() || 'CABIN');
        $('#preview_name').text($('#name').val() || 'New Cabin');
        $('#preview_meta').text((selectedType().charAt(0).toUpperCase() + selectedType().slice(1)) + ' · ' + ($('#floor_name').val() || 'Floor not set'));
        $('#preview_status').text(status.charAt(0).toUpperCase() + status.slice(1));
        $('#preview_status_bar')
            .removeClass('status-available status-booked status-monthly status-maintenance')
            .addClass(status === 'available' ? 'status-available' : (status === 'occupied' ? 'status-booked' : 'status-maintenance'));
    }

    $('input[name="cabin_type"]').on('change', function () {
        $('#hourly_rate').val(defaults[selectedType()].hourly);
        $('#monthly_rate').val(defaults[selectedType()].monthly);
        syncRateFields();
        refreshPreview();
    });

    $('#booking_mode').on('change', syncRateFields);
    $('#name, #cabin_code, #floor_name, #status').on('keyup change', refreshPreview);

    $('#cabinForm').validate({
        rules: {
            cabin_code: { required: true, maxlength: 50 },
            name: { required: true, maxlength: 255 },
            capacity: { required: true, min: 1 }
        },
        errorClass: 'text-danger',
        errorElement: 'small',
        submitHandler: function (form) {
            return lockCabinSubmit(form);
        }
    });

    syncRateFields();
    refreshPreview();
});
</script>
@endpush
