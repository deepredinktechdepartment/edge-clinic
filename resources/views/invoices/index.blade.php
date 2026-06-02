@extends('template_v1')

@section('content')

<div class="tt-posts">
    <div class="d-flex justify-content-between tt-wrap mb-3">
        <div class="p-2 bd-highlight">
            <h5 class="mb-0 pb-0">{{ $pageTitle ?? '' }}</h5>
        </div>

        <div class="p-2 bd-highlight">
            @if(isset($addlink) && !empty($addlink))
                <a href="{{ $addlink }}">
                    <i class="fa-solid fa-circle-plus"></i>
                </a>
            @endif
        </div>
    </div>
</div>

<div class="t-job-sheet container-fluid g-0">
    <div class="t-table table-responsive">
        <table class="table table-borderless table-hover"
            id="invoice-datatable"
            style="width:100%;">
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>Patient</th>
                    <th>Date</th>
                    <th>Actual Amount</th>
                    <th>Discount</th>
                    <th>Final Amount</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @foreach($invoices as $invoice)
                    <tr>
                        <td>{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->patient->name ?? '-' }}</td>
                        <td>{{ $invoice->invoice_date }}</td>
                        <td>Rs {{ number_format((float) ($invoice->sub_total ?? 0), 2) }}</td>
                        <td>
                            {{ number_format((float) ($invoice->discount_percentage ?? 0), 2) }}%
                            <br>
                            <small>Rs {{ number_format((float) ($invoice->discount_amount ?? 0), 2) }}</small>
                        </td>
                        <td>Rs {{ number_format((float) ($invoice->grand_total ?? 0), 2) }}</td>
                        <td>{{ ucfirst($invoice->status) }}</td>
                        <td>
                            @if($invoice->payments->count() > 0)
                                @foreach($invoice->payments as $payment)
                                    <div>
                                        <strong>{{ $payment->payment_id }}</strong><br>
                                        {{ strtoupper($payment->payment_mode) }}:
                                        Rs {{ number_format((float) ($payment->amount ?? 0), 2) }}
                                        @if(!empty($payment->transaction_number))
                                            <br><small class="text-muted">{{ $payment->transaction_number }}</small>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <span class="text-danger">Unpaid</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.invoices.show', $invoice->id) }}"
                            class="btn btn-sm btn-info">
                                View
                            </a>
                            <button class="btn btn-sm btn-success send-invoice-sms"
                                    data-id="{{ $invoice->id }}">
                                Send Invoice SMS
                            </button>

                            @if($invoice->status == 'draft')
                                <a href="{{ route('admin.invoices.edit', $invoice->id) }}"
                                class="btn btn-sm btn-warning">
                                    Edit
                                </a>
                            @endif

                            @if($invoice->balance_amount > 0)
                                <button class="btn btn-sm btn-success"
                                        onclick="openPaymentModal({{ $invoice->id }}, {{ $invoice->balance_amount }})">
                                    Add Payment
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="paymentModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <form method="POST" action="{{ route('admin.invoice.pay') }}">
        @csrf

        <div class="modal-header">
          <h5 class="modal-title">Add Payment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

            <input type="hidden" name="invoice_id" id="modal_invoice_id">

            <div class="mb-3">
                <label>Amount</label>
                <input type="number" name="amount"
                       id="modal_amount"
                       class="form-control" readonly>
            </div>

            <div class="mb-3">
                <label>Payment Mode</label>
                <select name="payment_mode"
                        id="payment_mode"
                        class="form-control" required>
                    <option value="">Select</option>
                    <option value="cash">Cash</option>
                    <option value="upi">UPI</option>
                    <option value="card">Card</option>
                    <option value="split">Split</option>
                </select>
            </div>

            <div class="mb-3 d-none" id="upiField">
                <label>Transaction Number</label>
                <input type="text"
                       name="transaction_number"
                       class="form-control">
            </div>

            <div class="border rounded-3 p-3 d-none" id="splitField">
                <div class="small text-muted mb-2">Split total must match the payment amount.</div>
                <div class="row g-2">
                    <div class="col-md-4">
                        <label>Cash Amount</label>
                        <input type="number" name="cash_amount" id="split_cash_amount" class="form-control" min="0" step="0.01" value="0">
                    </div>
                    <div class="col-md-4">
                        <label>UPI Amount</label>
                        <input type="number" name="upi_amount" id="split_upi_amount" class="form-control" min="0" step="0.01" value="0">
                    </div>
                    <div class="col-md-4">
                        <label>Card Amount</label>
                        <input type="number" name="card_amount" id="split_card_amount" class="form-control" min="0" step="0.01" value="0">
                    </div>
                    <div class="col-md-6">
                        <label>UPI Transaction Number</label>
                        <input type="text" name="upi_transaction_number" id="split_upi_transaction_number" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>Card Transaction Number</label>
                        <input type="text" name="card_transaction_number" id="split_card_transaction_number" class="form-control">
                    </div>
                </div>
                <div class="alert alert-info py-2 mt-3 mb-0">
                    <div class="d-flex justify-content-between flex-wrap gap-2">
                        <span>Split Total: <strong id="invoiceSplitEnteredTotal">Rs 0.00</strong></span>
                        <span>Payable: <strong id="invoiceSplitPayableTotal">Rs 0.00</strong></span>
                    </div>
                </div>
            </div>

            <div class="alert alert-danger d-none py-2 mb-0" id="invoicePaymentValidationError"></div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save Payment</button>
        </div>

      </form>

    </div>
  </div>
</div>

@endsection
@push('scripts')
<script>
function showInvoicePaymentValidationError(message) {
    $('#invoicePaymentValidationError').text(message).removeClass('d-none');
}

function clearInvoicePaymentValidationError() {
    $('#invoicePaymentValidationError').addClass('d-none').text('');
}

function updateInvoiceSplitSummary() {
    const totalAmount = parseFloat($('#modal_amount').val() || 0);
    const cashAmount = parseFloat($('#split_cash_amount').val() || 0);
    const upiAmount = parseFloat($('#split_upi_amount').val() || 0);
    const cardAmount = parseFloat($('#split_card_amount').val() || 0);
    const splitTotal = +(cashAmount + upiAmount + cardAmount).toFixed(2);

    $('#invoiceSplitEnteredTotal').text('Rs ' + splitTotal.toFixed(2));
    $('#invoiceSplitPayableTotal').text('Rs ' + totalAmount.toFixed(2));
}

function openPaymentModal(invoiceId, balance){

    $('#modal_invoice_id').val(invoiceId);
    $('#modal_amount').val(balance);
    $('#split_cash_amount, #split_upi_amount, #split_card_amount').val('0');
    $('#split_upi_transaction_number, #split_card_transaction_number').val('');
    updateInvoiceSplitSummary();
    clearInvoicePaymentValidationError();

    var myModal = new bootstrap.Modal(document.getElementById('paymentModal'));
    myModal.show();
}

$('#payment_mode').on('change', function(){
    const mode = $(this).val();
    clearInvoicePaymentValidationError();

    if(mode === 'upi' || mode === 'card'){
        $('#upiField').removeClass('d-none');
        $('#upiField label').text(mode === 'card' ? 'Card Transaction Number' : 'Transaction Number');
        $('#upiField input').attr('placeholder', mode === 'card' ? 'Enter card ref no' : 'Enter UPI ref no');
    }else{
        $('#upiField').addClass('d-none');
    }

    if (mode === 'split') {
        $('#splitField').removeClass('d-none');
        $('#upiField').addClass('d-none');
        updateInvoiceSplitSummary();
    } else {
        $('#splitField').addClass('d-none');
    }

});

$('#split_cash_amount, #split_upi_amount, #split_card_amount, #split_upi_transaction_number, #split_card_transaction_number').on('input', function () {
    clearInvoicePaymentValidationError();
    updateInvoiceSplitSummary();
});

$('form[action="{{ route('admin.invoice.pay') }}"]').on('submit', function (e) {
    const mode = $('#payment_mode').val();
    clearInvoicePaymentValidationError();

    if (mode !== 'split') {
        return;
    }

    const totalAmount = parseFloat($('#modal_amount').val() || 0);
    const cashAmount = parseFloat($('#split_cash_amount').val() || 0);
    const upiAmount = parseFloat($('#split_upi_amount').val() || 0);
    const cardAmount = parseFloat($('#split_card_amount').val() || 0);
    const splitTotal = +(cashAmount + upiAmount + cardAmount).toFixed(2);
    const activeModes = [cashAmount, upiAmount, cardAmount].filter(amount => amount > 0).length;

    if (activeModes < 2) {
        e.preventDefault();
        showInvoicePaymentValidationError('Please enter at least two split payment amounts.');
        return;
    }

    if (splitTotal !== +totalAmount.toFixed(2)) {
        e.preventDefault();
        showInvoicePaymentValidationError('Split payment total must match the payment amount.');
        return;
    }

    if (upiAmount > 0 && !$('#split_upi_transaction_number').val()) {
        e.preventDefault();
        showInvoicePaymentValidationError('Enter UPI transaction number for split payment.');
        return;
    }

    if (cardAmount > 0 && !$('#split_card_transaction_number').val()) {
        e.preventDefault();
        showInvoicePaymentValidationError('Enter card transaction number for split payment.');
        return;
    }
});

</script>
<script>
$(document).ready(function(){

    $('#invoice-datatable').DataTable({
        order: [[2, 'desc']],
        responsive: true
    });

});
</script>
<script>

$(document).on('click','.send-invoice-sms',function(){

    let btn = $(this);
    let invoiceId = btn.data('id');

    if(!confirm("Send invoice SMS to patient?")){
        return;
    }

    btn.prop('disabled',true).text('Sending...');

    $.ajax({
        url: "{{ route('admin.invoices.send.sms') }}",
        type: "POST",
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        data:{
            id: invoiceId
        },

        success:function(response){

            if(response.status){

                btn.removeClass('btn-success')
                   .addClass('btn-secondary')
                   .text('SMS Sent');

            }else{

                btn.prop('disabled',false)
                   .text('Send Invoice SMS');

                alert("SMS failed to send.");

            }

        },

        error:function(xhr){

            btn.prop('disabled',false)
               .text('Send Invoice SMS');

            alert("Server error occurred.");

            console.log(xhr.responseText);

        }

    });

});

</script>
@endpush
