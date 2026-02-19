@extends('template_v1')

@section('content')

<div class="tt-posts">
    <div class="d-flex justify-content-between tt-wrap mb-3">
        <div class="p-2">
            <h5 class="mb-0">{{ $pageTitle ?? '' }}</h5>
        </div>
    </div>
</div>

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="row">
    <div class="col-4">
        <div class="card shadow-sm">
            <div class="card-body">

                <form id="registrationFeeForm" method="POST"
                    action="{{ isset($registrationFee)
                        ? route('admin.registration-fees.update', $registrationFee)
                        : route('admin.registration-fees.store') }}">

                    @csrf
                    @isset($registrationFee)
                        @method('PUT')
                    @endisset

                    {{-- Registration Fee --}}
                    <div class="mb-3">
                        <label class="form-label">Registration Fee (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount"
                               class="form-control"
                               value="{{ old('amount', $registrationFee->amount ?? '') }}"
                               required>
                    </div>

                    {{-- Validity --}}
                    <div class="mb-3">
                        <label class="form-label">Registration Validity <span class="text-danger">*</span></label>
                        <select name="validity_days" class="form-control form-select" required>
                            <option value="">Select Validity</option>
                            @foreach([30=>'1 Month',90=>'3 Months',180=>'6 Months',365=>'1 Year'] as $days=>$label)
                                <option value="{{ $days }}"
                                    {{ old('validity_days', $registrationFee->validity_days ?? '') == $days ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Discount Type --}}
                    <div class="mb-3">
                        <label class="form-label">Discount Type</label>
                        <select name="discount_type" id="discount_type" class="form-control">
                            <option value="">None</option>
                            <option value="percent"
                                {{ old('discount_type', $registrationFee->discount_type ?? '') == 'percent' ? 'selected' : '' }}>
                                Percent
                            </option>
                            <option value="fixed"
                                {{ old('discount_type', $registrationFee->discount_type ?? '') == 'fixed' ? 'selected' : '' }}>
                                Fixed
                            </option>
                        </select>
                    </div>

                    {{-- Discount Value --}}
                    <div class="mb-3">
                        <label class="form-label">Discount Value</label>
                        <input type="number" step="0.01" name="discount_value"
                               class="form-control"
                               value="{{ old('discount_value', $registrationFee->discount_value ?? '') }}">
                    </div>

                    {{-- Active --}}
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox"
                               name="is_active" value="1"
                               {{ old('is_active', $registrationFee->is_active ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label">
                            Set as Active Registration Fee
                        </label>
                    </div>
                    <div>


                        <button class="btn btn-brand">
                            {{ isset($registrationFee) ? 'Update' : 'Save' }}
                        </button>
                        <a href="{{route('admin.registration-fees.index')}}" class="btn btn-danger text-white mt-2">Back</a>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script>
$(function () {

    $('#registrationFeeForm').validate({
        rules: {
            amount: {
                required: true,
                number: true,
                min: 0
            },
            validity_days: {
                required: true
            },
            discount_value: {
                number: true,
                min: 0
            }
        },
        messages: {
            amount: "Please enter registration fee",
            validity_days: "Please select validity",
            discount_value: "Enter valid discount value"
        },
        errorClass: 'text-danger',
        errorElement: 'small'
    });

    // Disable discount value if no type
    $('#discount_type').on('change', function () {
        if ($(this).val() === '') {
            $('input[name="discount_value"]').val('').prop('disabled', true);
        } else {
            $('input[name="discount_value"]').prop('disabled', false);
        }
    }).trigger('change');

});
</script>
@endpush
