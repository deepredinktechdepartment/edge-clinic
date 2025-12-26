@extends('template_v1')

@section('content')

<div class="my-4">



    <div class="tt-posts">
   	<div class="d-flex justify-content-between tt-wrap mb-3">
	  	<div class="p-2 bd-highlight"><h5 class="mb-0 pb-0">{{$pageTitle??''}}</h5></div>
 {{-- New action link: Book an appointment --}}
            <a href="{{ url('manualappointment/patientcreate?action=appointment') }}"  title="Book an appointment">
                <i class="fa-solid fa-calendar-plus"></i> Book Appointment
            </a>
	</div>
</div>

    @if(!isset($doctorId))
        <div class="card shadow-sm mb-4">
            <div class="card-body">
               <form action="{{ route('admin.appointments.report') }}" method="GET" class="row gy-2 gx-3 align-items-end">

    <!-- Doctor Filter -->
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

    <!-- From Date -->
<div class="col-xxl-1 col-sm-2">
    <label class="form-label">From</label>
    <input type="date"
           name="from_date"
           class="form-control form-control-sm"
           value="{{ request('from_date', $fromDate ?? now()->toDateString()) }}">
</div>

<!-- To Date -->
<div class="col-xxl-1 col-sm-2">
    <label class="form-label">To</label>
    <input type="date"
           name="to_date"
           class="form-control form-control-sm"
           value="{{ request('to_date', $toDate ?? now()->toDateString()) }}">
</div>

    <!-- Payment Status -->
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
    <!-- Filter & Export Buttons -->
    <div class="col-sm-4 d-flex align-items-end">
        <div class="me-2">
            <button class="btn btn-brand btn-sm">
                Go
            </button>
        </div>
        <div class="me-2">

            <a href="{{ route('admin.appointments.report') }}" class="btn btn-brand btn-sm">
                Reset
            </a>
        </div>
        <div class="me-2">
            <a href="{{ route('admin.appointments.report.pdf', request()->all()) }}" class="btn btn-brand btn-sm">
                <i class="fa-solid fa-download" style="color:#fff !important"></i>&nbsp; pdf
            </a>
        </div>
        <div>
            <a href="{{ route('admin.appointments.report.print', request()->all()) }}" target="_blank" class="btn btn-brand btn-sm">
                <i class="fa-solid fa-print" style="color:#fff !important"></i>
            </a>
        </div>
    </div>

</form>

            </div>
        </div>
    @endif

    <!-- Summary Cards -->


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
        :today="'₹ '.number_format($cardData['total_revenue']['today'], 2)"
        :month="'₹ '.number_format($cardData['total_revenue']['month'], 2)"
        route="#"
    />

</div>

 @include('admin.appointments.table', ['list' =>  $appointments])


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

                <!-- Status -->
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


                <!-- Remarks -->
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
        <ul class="list-group" id="appointmentLogList">
          <!-- Logs will be injected here -->
        </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>



@endsection
@push('scripts')
<script>
$(document).on('click', '.open-status-modal', function () {
    let id = $(this).data('id');
    let status = $(this).data('status');

    $('#appointmentId').val(id);
    $('#appointmentStatus').val(status);
    $('#statusRemarks').val('');

    $('#statusModal').modal('show');
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
            if(res.success){
                // Get returned status from response
                let newStatus = res.status;

                // Determine status color
                let statusColor = '';
                switch(newStatus){
                    case 'Scheduled': statusColor = '#6c757d'; break;       // grey
                    case 'Checked-In': statusColor = '#0dcaf0'; break;      // blue
                    case 'In-Consultation': statusColor = '#0d6efd'; break; // darker blue
                    case 'Checked-Out': statusColor = '#ffc107'; break;     // yellow
                    case 'Completed': statusColor = '#198754'; break;       // green
                    case 'Cancelled': statusColor = '#dc3545'; break;       // red
                    default: statusColor = '#e0e0e0';                        // light grey
                }

                // Update plain text status with color
                $('#status-' + id)
                    .text(newStatus)
                    .css('color', statusColor);

                // Update button data-status for modal next open
                $('.open-status-modal[data-id="'+id+'"]').data('status', newStatus);

                // Close modal
                $('#statusModal').modal('hide');
            } else {
                alert('Status update failed!');
            }
        },
        error: function(xhr){
            alert('Something went wrong! Please try again.');
        }
    });
});
</script>
<script>
$(document).ready(function() {
    $('.appointment-log-link').on('click', function() {
        let appointmentId = $(this).data('id');

        // Clear previous logs
        $('#appointmentLogList').html('<li class="list-group-item text-center">Loading...</li>');

        // Use full URL with Laravel url() helper
        let requestUrl = "{{ url('appointments') }}/" + appointmentId + "/status-log";

        $.get(requestUrl, function(res) {
            if(res.success) {
                let logs = res.logs;

                // Sort logs ascending by timestamp
                logs.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

                let html = '';
                logs.forEach(log => {
                    // Determine color based on to_status
                    let statusColor = '#6c757d'; // default grey
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
                                <strong>${log.from_status || '—'} → ${log.to_status}</strong>
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


@endpush