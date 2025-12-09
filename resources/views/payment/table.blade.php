@if(count($list) > 0)
<div class="t-job-sheet container-fluid g-0">
    <div class="t-table table-responsive">
        <table class="table table-borderless table-hover" id="default-datatable" style="width: 100%;">
        <thead>
        <tr>
            <th>#</th> <!-- Serial Number -->
            <th>Appointment Details</th>
            <th>Doctor</th>
            <th>Patient Details</th>       
            <th>Fee</th>
            <th>Payment Details</th> <!-- New column -->
        </tr>
        </thead>
        <tbody>
            @forelse($list as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td> <!-- Serial Number -->

                    <!-- Appointment Details -->
                    <td>
                        <div><strong>Appointment No:</strong> {{ $row['appointment_no'] }}</div>
                        <div><strong>Date:</strong> {{ \GeneralFunctions::formatDate($row['date']) }}</div>
                        <div><strong>Time:</strong> {{ $row['time'] ?? '-' }}</div>
                    </td>

                    <!-- Doctor -->
                    <td>{{ $row['doctor_name'] }}</td>

                    <!-- Patient Details -->
                    <td>
                        {{ $row['patient_name'] }}<br>
                        {{ $row['patient_email'] ?? '-' }}<br>
                        {{ $row['patient_phone'] ?? '-' }}
                    </td>

                    <!-- Fee -->
                    <td>₹ {{ number_format($row['fee']) }}</td>

                    <!-- Payment Details -->
                    <td>
                        <div><strong>Payment ID:</strong> {{ $row['payment_id'] ?? '-' }}</div>
                        <div>
                            <strong>Status:</strong>
                            @if($row['payment_status'] === 'success')
                                <span class="badge bg-success">Success</span>
                            @else
                                <span class="badge bg-danger">Failed</span>
                            @endif
                        <div><strong>Payment Date</strong>: {{ \GeneralFunctions::formatDate($row['payment_date']) }}</div>

                            
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">No appointments found</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
</div>
@else
<div class="text-center text-muted p-3">
    No records found
</div>
@endif
