@extends('template_v1')

@section('content')

@php
    $isEdit = isset($service);
@endphp

<div class="tt-posts">
    <div class="d-flex justify-content-between tt-wrap mb-3">
        <div class="p-2">
            <h5 class="mb-0 pb-0">{{ $pageTitle ?? '' }}</h5>
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

<form id="serviceForm"
      method="POST"
      action="{{ $isEdit
                ? route('admin.services.update', $service->id)
                : route('admin.services.store') }}">

    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

<div class="row">
    <div class="col-4">
        <div class="card shadow-sm">
            <div class="card-body">

                {{-- Name --}}
                <div class="mb-3">
                    <label>Name</label>
                    <input type="text"
                           name="name"
                           class="form-control"
                           value="{{ old('name', $service->name ?? '') }}">
                </div>

                {{-- Parent Category --}}
                <div class="mb-3">
                    <label>Parent Category</label>
                    <select name="parent_id" class="form-select">
                        <option value="">-- Main Category --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ old('parent_id', $service->parent_id ?? '') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">
                        Leave empty to create Category
                    </small>
                </div>

                {{-- Description --}}
                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description"
                              class="form-control">{{ old('description', $service->description ?? '') }}</textarea>
                </div>

                {{-- Billing Type --}}
                {{-- Service Only Fields Wrapper --}}
                    <div id="serviceFields">

                        <div class="mb-3">
                            <label>Amount</label>
                            <input type="number"
                                step="0.01"
                                name="amount"
                                id="amount"
                                class="form-control" value="{{ old('amount', $service->amount ?? 0) }}">
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="mb-3">
                                    <label>CGST %</label>
                                    <input type="number"
                                        step="0.01"
                                        name="cgst"
                                        id="cgst"
                                        class="form-control gst-input" value="{{ old('cgst', $service->cgst ?? 0) }}">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="mb-3">
                                    <label>SGST %</label>
                                    <input type="number"
                                        step="0.01"
                                        name="sgst"
                                        id="sgst"
                                        class="form-control gst-input" value="{{ old('sgst', $service->sgst ?? 0) }}">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="mb-3">
                                    <label>IGST %</label>
                                    <input type="number"
                                        step="0.01"
                                        name="igst"
                                        id="igst"
                                        class="form-control gst-input" value="{{ old('igst', $service->igst ?? 0) }}">
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">

                            {{-- Intra State --}}
                            <div class="col-sm-6">
                                <div class="card border-success">
                                    <div class="card-body p-2">
                                        <small class="text-success fw-bold">Intra-State (CGST + SGST)</small>
                                        <div>Tax Amount: ₹ <span id="intraTaxAmount">0.00</span></div>
                                        <div>Final Amount: ₹ <span id="intraFinalAmount">0.00</span></div>
                                    </div>
                                </div>
                            </div>

                            {{-- Inter State --}}
                            <div class="col-sm-6">
                                <div class="card border-primary">
                                    <div class="card-body p-2">
                                        <small class="text-primary fw-bold">Inter-State (IGST)</small>
                                        <div>Tax Amount: ₹ <span id="interTaxAmount">0.00</span></div>
                                        <div>Final Amount: ₹ <span id="interFinalAmount">0.00</span></div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                <button type="submit" class="btn btn-brand mt-3">
                    {{ $isEdit ? 'Update' : 'Save' }}
                </button>

            </div>
        </div>
    </div>
</div>

</form>

@endsection

@push('scripts')

<script>
$(document).ready(function() {

    function toggleServiceFields() {
        let parent = $('select[name="parent_id"]').val();
        if (parent === "") {
            $('#serviceFields').hide();
        } else {
            $('#serviceFields').show();
        }
    }

    function calculateAmounts() {

        let amount = parseFloat($('#amount').val()) || 0;
        let cgst   = parseFloat($('#cgst').val()) || 0;
        let sgst   = parseFloat($('#sgst').val()) || 0;
        let igst   = parseFloat($('#igst').val()) || 0;

        // Intra-State
        let intraPercent = cgst + sgst;
        let intraTax     = (amount * intraPercent) / 100;
        let intraFinal   = amount + intraTax;

        // Inter-State
        let interPercent = igst;
        let interTax     = (amount * interPercent) / 100;
        let interFinal   = amount + interTax;

        $('#intraTaxAmount').text(intraTax.toFixed(2));
        $('#intraFinalAmount').text(intraFinal.toFixed(2));

        $('#interTaxAmount').text(interTax.toFixed(2));
        $('#interFinalAmount').text(interFinal.toFixed(2));

        return intraPercent + interPercent;
    }

    function initForm() {
        toggleServiceFields();
        calculateAmounts();
    }

    // Run after everything is ready
    initForm();

    // Recalculate on changes
    $('select[name="parent_id"]').on('change', function() {
        toggleServiceFields();
    });

    $('.gst-input, #amount').on('input keyup change', function() {
        calculateAmounts();
    });

    // Validation
    $("#serviceForm").validate({

        ignore: [],

        rules: {
            name: { required: true, maxlength: 255 },

            amount: {
                required: function() {
                    return $('select[name="parent_id"]').val() !== "";
                },
                number: true,
                min: 0
            },

            cgst: { number: true, min: 0, max: 100 },
            sgst: { number: true, min: 0, max: 100 },
            igst: { number: true, min: 0, max: 100 }
        },

        submitHandler: function(form) {

            let totalPercent = calculateAmounts();

            if (totalPercent > 100) {
                alert("Total GST cannot exceed 100%");
                return false;
            }

            let btn = $(form).find('button[type="submit"]');
            btn.prop('disabled', true)
               .html('<i class="fa fa-spinner fa-spin me-1"></i> Processing...');

            form.submit();
        }

    });

});
</script>

@endpush