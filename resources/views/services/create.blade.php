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

                        {{-- Billing Type --}}
                        <div class="mb-3">
                            <label class="d-block">Billing Type</label>

                            @php
                                $billing = old('billing_type', $service->billing_type ?? '');
                            @endphp

                            <div class="form-check form-check-inline">
                                <input class="form-check-input billing-radio"
                                    type="radio"
                                    name="billing_type"
                                    value="billable"
                                    {{ $billing == 'billable' ? 'checked' : '' }}>
                                <label class="form-check-label">Billable</label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input billing-radio"
                                    type="radio"
                                    name="billing_type"
                                    value="non_billable"
                                    {{ $billing == 'non_billable' ? 'checked' : '' }}>
                                <label class="form-check-label">Non Billable</label>
                            </div>
                        </div>

                        {{-- Amount --}}
                        <div class="mb-3" id="amountDiv">
                            <label>Amount</label>
                            <input type="number"
                                step="0.01"
                                name="amount"
                                id="amount"
                                class="form-control"
                                value="{{ old('amount', $service->amount ?? '') }}">
                        </div>

                        {{-- GST --}}
                        <div class="mb-3">
                            <label class="d-block">GST Applicable?</label>

                            @php
                                $gst = old('gst_applicable', $service->gst_applicable ?? 0);
                            @endphp

                            <div class="form-check form-check-inline">
                                <input class="form-check-input gst-radio"
                                    type="radio"
                                    name="gst_applicable"
                                    value="1"
                                    {{ $gst == 1 ? 'checked' : '' }}>
                                <label class="form-check-label">Yes</label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input gst-radio"
                                    type="radio"
                                    name="gst_applicable"
                                    value="0"
                                    {{ $gst == 0 ? 'checked' : '' }}>
                                <label class="form-check-label">No</label>
                            </div>
                        </div>

                        {{-- GST Percentage --}}
                        <div class="mb-3" id="gstDiv">
                            <label>GST Percentage</label>
                            <input type="number"
                                step="0.01"
                                name="gst_percentage"
                                id="gst_percentage"
                                class="form-control"
                                value="{{ old('gst_percentage', $service->gst_percentage ?? '') }}">
                        </div>

                        {{-- Terms --}}
                        <div class="mb-3">
                            <label>Service Terms</label>
                            <textarea name="service_terms"
                                    class="form-control">{{ old('service_terms', $service->service_terms ?? '') }}</textarea>
                        </div>

                    </div>

                <button type="submit" class="btn btn-primary">
                    {{ $isEdit ? 'Update' : 'Save' }}
                </button>

            </div>
        </div>
    </div>
</div>

</form>

@endsection

@push('scripts')

@push('scripts')

<script>
$(document).ready(function() {

    function toggleServiceFields() {
        let parent = $('select[name="parent_id"]').val();

        if (parent === "") {
            // Main Category
            $('#serviceFields').hide();
            clearServiceFields();
        } else {
            // Service
            $('#serviceFields').show();
        }
    }

    function toggleAmount() {
        if ($('input[name="billing_type"]:checked').val() === 'billable') {
            $('#amountDiv').show();
        } else {
            $('#amountDiv').hide();
            $('#amount').val('');
        }
    }

    function toggleGST() {
        if ($('input[name="gst_applicable"]:checked').val() === '1') {
            $('#gstDiv').show();
        } else {
            $('#gstDiv').hide();
            $('#gst_percentage').val('');
        }
    }

    function clearServiceFields() {
        $('input[name="billing_type"]').prop('checked', false);
        $('input[name="gst_applicable"]').prop('checked', false);
        $('#amount').val('');
        $('#gst_percentage').val('');
    }

    // Run on load
    toggleServiceFields();
    toggleAmount();
    toggleGST();

    // Events
    $('select[name="parent_id"]').change(function() {
        toggleServiceFields();
    });

    $('.billing-radio').change(toggleAmount);
    $('.gst-radio').change(toggleGST);

    // jQuery Validation
    $("#serviceForm").validate({

        ignore: [],

        rules: {
            name: {
                required: true,
                maxlength: 255
            },
            billing_type: {
                required: function() {
                    return $('select[name="parent_id"]').val() !== "";
                }
            },
            amount: {
                required: function() {
                    return $('select[name="parent_id"]').val() !== "" &&
                           $('input[name="billing_type"]:checked').val() === 'billable';
                },
                number: true,
                min: 0
            },
            gst_applicable: {
                required: function() {
                    return $('select[name="parent_id"]').val() !== "";
                }
            },
            gst_percentage: {
                required: function() {
                    return $('select[name="parent_id"]').val() !== "" &&
                           $('input[name="gst_applicable"]:checked').val() === '1';
                },
                number: true,
                min: 0,
                max: 100
            }
        },

        messages: {
            name: "Service name is required",
            billing_type: "Select billing type",
            amount: "Enter valid amount",
            gst_applicable: "Select GST option",
            gst_percentage: "Enter valid GST %"
        },

        errorElement: 'span',
        errorClass: 'text-danger',

        highlight: function(element) {
            $(element).addClass('is-invalid');
        },

        unhighlight: function(element) {
            $(element).removeClass('is-invalid');
        },

        errorPlacement: function(error, element) {
            if (element.attr("type") === "radio") {
                error.insertAfter(element.closest('.mb-3'));
            } else {
                error.insertAfter(element);
            }
        }

    });

});
</script>

@endpush

@endpush