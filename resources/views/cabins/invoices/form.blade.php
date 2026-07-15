@extends('template_v1')

@section('content')
@include('cabins.partials.styles')

<div class="cabin-shell">
    @include('cabins.partials.page-header', [
        'title' => $pageTitle,
        'subtitle' => 'Generate hourly or monthly cabin invoices directly from doctor usage within a date range.',
        'actions' => [
            ['url' => route('admin.cabins.invoices.index'), 'label' => 'Back to Invoices', 'icon' => 'bi bi-arrow-left', 'class' => 'btn-outline-secondary'],
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
            <form id="cabinInvoiceForm" method="POST" action="{{ route('admin.cabins.invoices.store') }}">
                @csrf

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Doctor <span class="text-danger">*</span></label>
                        <select name="doctor_id" class="form-select" required>
                            <option value="">Select doctor</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" {{ (string) old('doctor_id', request('doctor_id')) === (string) $doctor->id ? 'selected' : '' }}>{{ $doctor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cabin</label>
                        <select name="cabin_id" class="form-select">
                            <option value="">All cabins for this doctor</option>
                            @foreach($cabins as $cabin)
                                <option value="{{ $cabin->id }}" {{ (string) old('cabin_id') === (string) $cabin->id ? 'selected' : '' }}>{{ $cabin->cabin_code }} - {{ $cabin->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Invoice Type <span class="text-danger">*</span></label>
                        <select name="billing_type" class="form-select" required>
                            <option value="hourly" {{ old('billing_type') === 'hourly' ? 'selected' : '' }}>Hourly</option>
                            <option value="monthly" {{ old('billing_type') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Period Start <span class="text-danger">*</span></label>
                        <input type="date" name="period_start" class="form-control" value="{{ old('period_start', now()->startOfMonth()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Period End <span class="text-danger">*</span></label>
                        <input type="date" name="period_end" class="form-control" value="{{ old('period_end', now()->endOfMonth()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">GST %</label>
                        <input type="number" step="0.01" name="gst_percent" class="form-control" value="{{ old('gst_percent', $settings->default_gst_percent) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                        <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Due Date <span class="text-danger">*</span></label>
                        <input type="date" name="due_date" class="form-control" value="{{ old('due_date', now()->addDays((int) $settings->payment_due_days)->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            @foreach(['draft' => 'Draft', 'sent' => 'Sent', 'paid' => 'Paid', 'overdue' => 'Overdue'] as $value => $label)
                                <option value="{{ $value }}" {{ old('status', 'draft') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Send Via</label>
                        <select name="sent_via" class="form-select">
                            <option value="">Not sent yet</option>
                            <option value="email" {{ old('sent_via') === 'email' ? 'selected' : '' }}>Email</option>
                            <option value="whatsapp" {{ old('sent_via') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                            <option value="both" {{ old('sent_via', $settings->invoice_delivery_mode) === 'both' ? 'selected' : '' }}>Both</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="4">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="mt-4 cabin-form-actions">
                    <button type="submit" class="btn btn-brand btn-sm">Generate Invoice</button>
                    <a href="{{ route('admin.cabins.invoices.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
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
            $submit.html('Generating...');
        }

        form.submit();
        return true;
    }

    $('#cabinInvoiceForm').validate({
        rules: {
            doctor_id: { required: true },
            billing_type: { required: true },
            period_start: { required: true },
            period_end: { required: true },
            invoice_date: { required: true },
            due_date: { required: true }
        },
        errorClass: 'text-danger',
        errorElement: 'small',
        submitHandler: function (form) {
            return lockCabinSubmit(form);
        }
    });
});
</script>
@endpush
