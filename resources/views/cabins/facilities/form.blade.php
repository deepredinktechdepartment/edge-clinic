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
                <div class="text-muted">This facility list is shown inside the cabin add and edit form.</div>
            </div>
            <a href="{{ route('admin.cabins.facilities.index') }}" class="btn btn-outline-secondary btn-sm">Back to Facilities</a>
        </div>
        <div class="panel-body">
            <form id="facilityForm" method="POST" action="{{ $facility->exists ? route('admin.cabins.facilities.update', $facility->id) : route('admin.cabins.facilities.store') }}">
                @csrf
                @if($facility->exists)
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Facility Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $facility->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug', $facility->slug) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Pricing Type <span class="text-danger">*</span></label>
                        <select name="pricing_type" id="pricing_type" class="form-select" required>
                            @foreach(['free' => 'Free', 'paid' => 'Paid'] as $value => $label)
                                <option value="{{ $value }}" {{ old('pricing_type', $facility->pricing_type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4" id="rate_wrap">
                        <label class="form-label">Rate</label>
                        <input type="number" step="0.01" min="0" name="rate" id="rate" class="form-control" value="{{ old('rate', $facility->rate) }}">
                    </div>
                    <div class="col-md-4" id="charge_label_wrap">
                        <label class="form-label">Charge Label</label>
                        <input type="text" name="charge_label" id="charge_label" class="form-control" value="{{ old('charge_label', $facility->charge_label) }}" placeholder="Per Use">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            @foreach(['active' => 'Active', 'inactive' => 'Inactive'] as $value => $label)
                                <option value="{{ $value }}" {{ old('status', $facility->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" min="0" class="form-control" value="{{ old('sort_order', $facility->sort_order) }}">
                    </div>
                    <div class="col-12" id="payment_note_wrap">
                        <label class="form-label">Payment Note</label>
                        <textarea name="payment_note" id="payment_note" class="form-control" rows="3" placeholder="Optional payment instruction or usage note">{{ old('payment_note', $facility->payment_note) }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description', $facility->description) }}</textarea>
                    </div>
                </div>

                <div class="mt-4 cabin-form-actions">
                    <button type="submit" class="btn btn-brand btn-sm">{{ $facility->exists ? 'Update Facility' : 'Save Facility' }}</button>
                    <a href="{{ route('admin.cabins.facilities.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
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
            $submit.html('{{ $facility->exists ? 'Updating...' : 'Saving...' }}');
        }

        form.submit();
        return true;
    }

    function syncPricingFields() {
        const isPaid = $('#pricing_type').val() === 'paid';
        $('#rate, #charge_label, #payment_note').prop('disabled', !isPaid);
        $('#rate_wrap, #charge_label_wrap, #payment_note_wrap').toggleClass('opacity-50', !isPaid);

        if (!isPaid) {
            $('#rate').val('0');
            $('#charge_label').val('');
            $('#payment_note').val('');
        } else if (!$('#charge_label').val()) {
            $('#charge_label').val('Per Use');
        }
    }

    $('#pricing_type').on('change', syncPricingFields);
    $('#facilityForm').validate({
        rules: {
            name: { required: true, maxlength: 100 },
            pricing_type: { required: true },
            status: { required: true }
        },
        errorClass: 'text-danger',
        errorElement: 'small',
        submitHandler: function (form) {
            return lockCabinSubmit(form);
        }
    });
    syncPricingFields();
});
</script>
@endpush
