@extends('template_v1')

@section('content')
@php $role = auth()->user()?->role; @endphp
<div class="my-4">
    <style>
        .appointments-filter-form .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #4a5a6a;
        }
        .appointments-filter-actions {
            display: flex;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: 10px;
        }
        .appointments-filter-actions .btn {
            min-width: 76px;
            margin-top: 0;
            border-radius: 10px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 0.48rem 0.85rem;
        }
        .appointments-filter-actions .btn-icon {
            min-width: 42px;
            padding-inline: 0.7rem;
        }
    </style>

    <div class="tt-posts">
        <div class="d-flex justify-content-between tt-wrap mb-3">
            <div class="p-2 bd-highlight"><h5 class="mb-0 pb-0">{{ $pageTitle ?? '' }}</h5></div>
            @if($role != 5)
            <a href="{{ url('manualappointment/patientcreate?action=appointment') }}" title="Book an appointment">
                <i class="fa-solid fa-calendar-plus"></i> Book Appointment
            </a>
            @endif
        </div>
    </div>

    @if(!isset($doctorId))
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('admin.appointments.report') }}" method="GET" class="row gy-2 gx-3 align-items-end appointments-filter-form">
                    @if(in_array($role, [1,3]))
                    <div class="col-md-2">
                        <label class="form-label">Doctor</label>
                        <select name="doctor" class="form-select form-select-sm">
                            <option value="">--All--</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc['id'] }}" {{ request('doctor') == $doc['id'] ? 'selected' : '' }}>
                                    {{ $doc['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="col-xxl-1 col-sm-2">
                        <label class="form-label">From</label>
                        <input type="date"
                               name="from_date"
                               class="form-control form-control-sm"
                               value="{{ request('from_date', $fromDate ?? now()->toDateString()) }}">
                    </div>

                    <div class="col-xxl-1 col-sm-2">
                        <label class="form-label">To</label>
                        <input type="date"
                               name="to_date"
                               class="form-control form-control-sm"
                               value="{{ request('to_date', $toDate ?? now()->toDateString()) }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Payment Status</label>
                        <select name="payment_status" class="form-select form-select-sm">
                            <option value="">--All--</option>
                            <option value="initiated" {{ request('payment_status') == 'initiated' ? 'selected' : '' }}>Initiated</option>
                            <option value="success" {{ request('payment_status') == 'success' ? 'selected' : '' }}>Success</option>
                            <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Mode</label>
                        <select name="payment_mode" class="form-select form-select-sm">
                            <option value="">--All--</option>
                            <option value="online" {{ request('payment_mode') == 'online' ? 'selected' : '' }}>Online</option>
                            <option value="offline" {{ request('payment_mode') == 'offline' ? 'selected' : '' }}>Offline</option>
                        </select>
                    </div>

                    <div class="col-xl-3 col-lg-4">
                        <div class="appointments-filter-actions">
                            <button class="btn btn-brand btn-sm" type="submit">
                                <i class="fa-solid fa-magnifying-glass" style="color:#fff !important"></i>
                                Go
                            </button>
                            <a href="{{ route('admin.appointments.report') }}" class="btn btn-brand btn-sm">
                                <i class="fa-solid fa-rotate-left" style="color:#fff !important"></i>
                                Reset
                            </a>
                            <a href="{{ route('admin.appointments.report.pdf', request()->all()) }}" class="btn btn-brand btn-sm btn-icon" title="Download PDF">
                                <i class="fa-solid fa-download" style="color:#fff !important"></i>
                            </a>
                            <a href="{{ route('admin.appointments.report.print', request()->all()) }}" target="_blank" class="btn btn-brand btn-sm btn-icon" title="Print">
                                <i class="fa-solid fa-print" style="color:#fff !important"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <x-card-today-month
            title="Total Appointments"
            :today="$cardData['total_appointments']['today']"
            :month="$cardData['total_appointments']['month']"
            route="#"
        />

        <x-card-today-month
            title="Paid Appointments"
            :today="$cardData['paid_appointments']['today']"
            :month="$cardData['paid_appointments']['month']"
            route="#"
        />

        <x-card-today-month
            title="Pending / Failed"
            :today="$cardData['failed_appointments']['today']"
            :month="$cardData['failed_appointments']['month']"
            route="#"
        />

        <x-card-today-month
            title="Total Revenue"
            :today="'Rs '.number_format($cardData['total_revenue']['today'], 2)"
            :month="'Rs '.number_format($cardData['total_revenue']['month'], 2)"
            route="#"
        />
    </div>

    @include('admin.appointments.table', ['list' => $appointments])
</div>

<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-semibold">
                    Update Patient Status
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="appointmentId">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Appointment Status</label>
                    <select class="form-select" id="appointmentStatus">
                        <option value="Scheduled">Scheduled</option>
                        <option value="Checked-In">Checked-In</option>
                        <option value="In-Consultation">In-Consultation</option>
                        <option value="Checked-Out">Checked-Out</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Remarks</label>
                    <textarea class="form-control" id="statusRemarks"
                              rows="3"
                              placeholder="Optional notes..."></textarea>
                </div>
            </div>

            <div class="modal-footer border-0">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-brand px-4" id="saveStatusBtn">
                    Update
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="appointmentLogModal" tabindex="-1" aria-labelledby="appointmentLogModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="appointmentLogModalLabel">Appointment Status Log</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <ul class="list-group" id="appointmentLogList"></ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-semibold">Update Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="paymentAppointmentId">
                <input type="hidden" id="paymentTotalAmount">

                <div class="alert alert-light border mb-3">
                    <div class="small text-muted">Actual Payment To Be Paid</div>
                    <div class="fw-bold fs-5" id="paymentAmountDisplay">Rs 0.00</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Payment Mode</label>
                    <select class="form-select" id="manualPaymentMode">
                        <option value="cash">Cash</option>
                        <option value="upi">UPI</option>
                        <option value="card">Card</option>
                        <option value="split">Split</option>
                    </select>
                </div>

                <div class="mb-3" id="manualReferenceWrap">
                    <label class="form-label fw-semibold">Reference No</label>
                    <input type="text" class="form-control" id="manualReferenceNo" placeholder="UPI ref or manual receipt no">
                </div>

                <div class="mb-3" id="singleAmountWrap">
                    <label class="form-label fw-semibold">Entered Amount</label>
                    <input type="number" class="form-control" id="manualEnteredAmount" min="0" step="0.01">
                </div>

                <div class="border rounded-3 p-3 mb-3 d-none" id="splitPaymentWrap">
                    <div class="small text-muted mb-2">Split total must match the full payment amount.</div>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Cash Amount</label>
                            <input type="number" class="form-control split-amount" id="splitCashAmount" min="0" step="0.01" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">UPI Amount</label>
                            <input type="number" class="form-control split-amount" id="splitUpiAmount" min="0" step="0.01" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Card Amount</label>
                            <input type="number" class="form-control split-amount" id="splitCardAmount" min="0" step="0.01" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">UPI Reference No</label>
                            <input type="text" class="form-control" id="splitUpiReference" placeholder="Enter UPI ref no">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Card Reference No</label>
                            <input type="text" class="form-control" id="splitCardReference" placeholder="Enter card ref no">
                        </div>
                    </div>
                    <div class="alert alert-info py-2 mt-3 mb-0">
                        <div class="d-flex justify-content-between flex-wrap gap-2">
                            <span>Split Total: <strong id="splitEnteredTotal">Rs 0.00</strong></span>
                            <span>Payable: <strong id="splitPayableTotal">Rs 0.00</strong></span>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Remarks</label>
                    <textarea class="form-control" id="paymentRemarks" rows="3" placeholder="Optional note about manual payment"></textarea>
                </div>

                <div class="alert alert-danger d-none py-2 mb-0" id="paymentValidationError"></div>
            </div>

            <div class="modal-footer border-0">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-brand px-4" id="savePaymentBtn">Save Payment</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="splitDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-semibold">Split Bill Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="splitDetailsList" class="list-group list-group-flush"></div>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script>
function showPaymentValidationError(message) {
    $('#paymentValidationError').text(message).removeClass('d-none');
}

function clearPaymentValidationError() {
    $('#paymentValidationError').addClass('d-none').text('');
}

function updateSplitSummary() {
    const totalAmount = parseFloat($('#paymentTotalAmount').val() || 0);
    const cashAmount = parseFloat($('#splitCashAmount').val() || 0);
    const upiAmount = parseFloat($('#splitUpiAmount').val() || 0);
    const cardAmount = parseFloat($('#splitCardAmount').val() || 0);
    const splitTotal = +(cashAmount + upiAmount + cardAmount).toFixed(2);

    $('#splitEnteredTotal').text('Rs ' + splitTotal.toFixed(2));
    $('#splitPayableTotal').text('Rs ' + totalAmount.toFixed(2));
}

function toggleManualReference() {
    const mode = $('#manualPaymentMode').val();
    const isSplit = mode === 'split';

    $('#splitPaymentWrap').toggleClass('d-none', !isSplit);
    $('#manualReferenceWrap').toggleClass('d-none', isSplit);
    $('#singleAmountWrap').toggleClass('d-none', isSplit);

    if (isSplit) {
        updateSplitSummary();
        return;
    }

    if (mode === 'cash') {
        $('#manualReferenceWrap label').text('Receipt / Reference No');
        $('#manualReferenceNo').attr('placeholder', 'Optional cash receipt no');
    } else if (mode === 'card') {
        $('#manualReferenceWrap label').text('Card Reference No');
        $('#manualReferenceNo').attr('placeholder', 'Enter card ref no');
    } else {
        $('#manualReferenceWrap label').text('UPI Reference No');
        $('#manualReferenceNo').attr('placeholder', 'Enter UPI ref no');
    }
}

$(document).on('click', '.open-status-modal', function () {
    let id = $(this).data('id');
    let status = $(this).data('status');

    $('#appointmentId').val(id);
    $('#appointmentStatus').val(status);
    $('#statusRemarks').val('');

    $('#statusModal').modal('show');
});

$(document).on('click', '.open-payment-modal', function () {
    let id = $(this).data('id');
    let paymentMode = $(this).data('payment-mode');
    let referenceNo = $(this).data('reference-no');
    let amount = parseFloat($(this).data('amount') || 0);

    $('#paymentAppointmentId').val(id);
    $('#paymentTotalAmount').val(amount.toFixed(2));
    $('#paymentAmountDisplay').text('Rs ' + amount.toFixed(2));
    $('#manualPaymentMode').val(['upi', 'card', 'split'].includes(paymentMode) ? paymentMode : 'cash');
    $('#manualReferenceNo').val(referenceNo || '');
    $('#manualEnteredAmount').val(amount.toFixed(2));
    $('#paymentRemarks').val('');
    $('#splitCashAmount, #splitUpiAmount, #splitCardAmount').val('0');
    $('#splitUpiReference, #splitCardReference').val('');
    updateSplitSummary();
    clearPaymentValidationError();
    toggleManualReference();

    $('#paymentModal').modal('show');
});

$('#manualPaymentMode').on('change', function () {
    clearPaymentValidationError();
    toggleManualReference();
});

$('#manualEnteredAmount, #splitCashAmount, #splitUpiAmount, #splitCardAmount, #manualReferenceNo, #splitUpiReference, #splitCardReference').on('input', function () {
    clearPaymentValidationError();
    updateSplitSummary();
});

$(document).on('click', '.show-split-details', function () {
    const rawDetails = $(this).data('split-details') || '';
    const parts = String(rawDetails).split('|').map(part => part.trim()).filter(Boolean);
    let html = '';

    parts.forEach(function (part) {
        const segments = part.split(':');
        const mode = (segments[0] || '').trim();
        const amount = parseFloat((segments[1] || '0').trim() || 0);
        const reference = (segments.slice(2).join(':') || '').trim();
        const safeReference = reference && !reference.includes('_MANUAL_') ? reference : '-';

        html += `
            <div class="list-group-item px-0">
                <div class="fw-semibold">${mode}</div>
                <div>Amount: Rs ${amount.toFixed(2)}</div>
                <div class="text-muted small">Reference: ${safeReference}</div>
            </div>
        `;
    });

    $('#splitDetailsList').html(html || '<div class="text-muted">No split bill details available.</div>');
    $('#splitDetailsModal').modal('show');
});

$('#saveStatusBtn').on('click', function () {
    let id = $('#appointmentId').val();
    let status = $('#appointmentStatus').val();
    let remarks = $('#statusRemarks').val();

    $.ajax({
        url: "{{ route('appointments.updateStatus') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            id: id,
            status: status,
            remarks: remarks
        },
        success: function (res) {
            if (res.success) {
                let newStatus = res.status;
                let statusColor = '';
                switch (newStatus) {
                    case 'Scheduled': statusColor = '#6c757d'; break;
                    case 'Checked-In': statusColor = '#0dcaf0'; break;
                    case 'In-Consultation': statusColor = '#0d6efd'; break;
                    case 'Checked-Out': statusColor = '#ffc107'; break;
                    case 'Completed': statusColor = '#198754'; break;
                    case 'Cancelled': statusColor = '#dc3545'; break;
                    default: statusColor = '#e0e0e0';
                }

                $('#status-' + id)
                    .text(newStatus)
                    .css('color', statusColor);

                $('.open-status-modal[data-id="' + id + '"]').data('status', newStatus);

                $('#statusModal').modal('hide');
            } else {
                alert('Status update failed!');
            }
        },
        error: function () {
            alert('Something went wrong! Please try again.');
        }
    });
});

$('#savePaymentBtn').on('click', function () {
    let id = $('#paymentAppointmentId').val();
    let paymentMode = $('#manualPaymentMode').val();
    let referenceNo = $('#manualReferenceNo').val();
    let remarks = $('#paymentRemarks').val();
    let totalAmount = parseFloat($('#paymentTotalAmount').val() || 0);
    let enteredAmount = parseFloat($('#manualEnteredAmount').val() || 0);
    let cashAmount = parseFloat($('#splitCashAmount').val() || 0);
    let upiAmount = parseFloat($('#splitUpiAmount').val() || 0);
    let cardAmount = parseFloat($('#splitCardAmount').val() || 0);
    let upiReference = $('#splitUpiReference').val();
    let cardReference = $('#splitCardReference').val();

    clearPaymentValidationError();

    if (paymentMode === 'upi' && !referenceNo) {
        showPaymentValidationError('Enter UPI reference number.');
        return;
    }

    if (paymentMode === 'card' && !referenceNo) {
        showPaymentValidationError('Enter card reference number.');
        return;
    }

    if (paymentMode !== 'split') {
        if (enteredAmount <= 0) {
            showPaymentValidationError('Enter payment amount.');
            return;
        }

        if (+enteredAmount.toFixed(2) !== +totalAmount.toFixed(2)) {
            showPaymentValidationError('Entered amount must be exactly Rs ' + totalAmount.toFixed(2) + '.');
            return;
        }
    }

    if (paymentMode === 'split') {
        const splitTotal = +(cashAmount + upiAmount + cardAmount).toFixed(2);
        const activeModes = [cashAmount, upiAmount, cardAmount].filter(amount => amount > 0).length;

        if (activeModes < 2) {
            showPaymentValidationError('Please enter at least two split payment amounts.');
            return;
        }

        if (splitTotal !== +totalAmount.toFixed(2)) {
            showPaymentValidationError('Split payment total must match Rs ' + totalAmount.toFixed(2) + '.');
            return;
        }

        if (upiAmount > 0 && !upiReference) {
            showPaymentValidationError('Enter UPI reference number for split payment.');
            return;
        }

        if (cardAmount > 0 && !cardReference) {
            showPaymentValidationError('Enter card reference number for split payment.');
            return;
        }
    }

    $.ajax({
        url: "{{ route('appointments.updatePayment') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            id: id,
            payment_mode: paymentMode,
            reference_no: referenceNo,
            remarks: remarks,
            cash_amount: paymentMode === 'cash' ? enteredAmount : cashAmount,
            upi_amount: paymentMode === 'upi' ? enteredAmount : upiAmount,
            card_amount: paymentMode === 'card' ? enteredAmount : cardAmount,
            upi_reference: upiReference,
            card_reference: cardReference
        },
        success: function (res) {
            if (res.success) {
                window.location.reload();
            }
        },
        error: function (xhr) {
            let message = 'Unable to update payment.';

            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }

            showPaymentValidationError(message);
        }
    });
});
</script>

<script>
$(document).ready(function() {
    $('.appointment-log-link').on('click', function() {
        let appointmentId = $(this).data('id');

        $('#appointmentLogList').html('<li class="list-group-item text-center">Loading...</li>');

        let requestUrl = "{{ url('appointments') }}/" + appointmentId + "/status-log";

        $.get(requestUrl, function(res) {
            if (res.success) {
                let logs = res.logs;

                logs.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

                let html = '';
                logs.forEach(log => {
                    let statusColor = '#6c757d';
                    switch(log.to_status) {
                        case 'Scheduled': statusColor = '#6c757d'; break;
                        case 'Checked-In': statusColor = '#0dcaf0'; break;
                        case 'In-Consultation': statusColor = '#0d6efd'; break;
                        case 'Checked-Out': statusColor = '#ffc107'; break;
                        case 'Completed': statusColor = '#198754'; break;
                        case 'Cancelled': statusColor = '#dc3545'; break;
                    }

                    html += `
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${log.from_status || '-'} -> ${log.to_status}</strong>
                                <div class="text-muted small">${log.remarks || ''}</div>
                            </div>
                            <span class="badge rounded-pill" style="background-color: ${statusColor}; color:white;">
                                ${new Date(log.created_at).toLocaleString()}
                            </span>
                        </li>
                    `;
                });

                $('#appointmentLogList').html(html);
                $('#appointmentLogModal').modal('show');
            } else {
                $('#appointmentLogList').html('<li class="list-group-item text-danger">No logs found.</li>');
                $('#appointmentLogModal').modal('show');
            }
        }).fail(function() {
            $('#appointmentLogList').html('<li class="list-group-item text-danger">Failed to fetch logs.</li>');
            $('#appointmentLogModal').modal('show');
        });
    });
});
</script>

<script>
$(document).on('click', '.send-appointment-sms', function() {
    let btn = $(this);
    let appointmentId = btn.data('id');

    btn.prop('disabled', true).text('Sending...');

    $.ajax({
        url: "{{ route('appointments.send.sms') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            id: appointmentId
        },
        success: function(response) {
            if (response.status) {
                btn.removeClass('btn-outline-success')
                   .addClass('btn-success')
                   .text('SMS Sent');
            } else {
                btn.prop('disabled', false)
                   .text('Send SMS');
                alert("SMS failed");
            }
        },
        error: function() {
            btn.prop('disabled', false)
               .text('Send SMS');
            alert("Something went wrong");
        }
    });
});
</script>
@endpush
