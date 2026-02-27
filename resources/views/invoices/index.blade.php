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
                    <th>Grand Total</th>
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
                        <td>₹ {{ number_format($invoice->grand_total,2) }}</td>
                        <td>{{ ucfirst($invoice->status) }}</td>
                        <td>
                                @if($invoice->payments->count() > 0)

                                    @foreach($invoice->payments as $payment)
                                        <div>
                                            <strong>{{ $payment->payment_id }}</strong><br>
                                            {{ strtoupper($payment->payment_mode) }}<br>
                                            ₹ {{ number_format($payment->amount,2) }}
                                        </div>
                                    @endforeach

                                @else
                                    <span class="text-danger">Unpaid</span>
                                @endif
                            </td>
                        <td>
                            <a href="{{ route('admin.invoices.show',$invoice->id) }}"
                            class="btn btn-sm btn-info">
                                View
                            </a>

                            @if($invoice->status == 'draft')
                                <a href="{{ route('admin.invoices.edit',$invoice->id) }}"
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
<!-- Payment Modal -->
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
                </select>
            </div>

            <div class="mb-3 d-none" id="upiField">
                <label>Transaction Number</label>
                <input type="text"
                       name="transaction_number"
                       class="form-control">
            </div>

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

function openPaymentModal(invoiceId, balance){

    $('#modal_invoice_id').val(invoiceId);
    $('#modal_amount').val(balance);

    var myModal = new bootstrap.Modal(document.getElementById('paymentModal'));
    myModal.show();
}

$('#payment_mode').on('change', function(){

    if($(this).val() === 'upi'){
        $('#upiField').removeClass('d-none');
    }else{
        $('#upiField').addClass('d-none');
    }

});

</script>
<script>
$(document).ready(function(){

    $('#invoice-datatable').DataTable({
        order: [[2, 'desc']], // Date column
        responsive: true
    });

});
</script>
@endpush