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
            <th>Amount</th>
            <th>Payment Details</th> <!-- New column -->
        </tr>
        </thead>
        <tbody>
            @forelse($list as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td> <!-- Serial Number -->

                    <!-- Appointment Details -->
                    <td>
                        <div><h6 class="mb-0">Apt No: {{ $row['appointment_no'] }}</h6></div>
                        <div>{{ \GeneralFunctions::formatDate($row['appointment_date']) ??'' }}, {{ $row['appointment_time'] ??'' }}</div>
                    
                    </td>

                    <!-- Doctor -->
                    <td>{{ $row['doctor_name'] }}</td>

                    <!-- Patient Details -->
                    <td>
    {{-- Name is required, so always show --}}
    {{ $row['patient_name'] }}

    {{-- Email: show only if exists --}}
    @if(!empty($row['patient_email']))
        <br>{{ $row['patient_email'] }}
    @endif

    {{-- Phone: show only if exists --}}
    @if(!empty($row['patient_phone']))
        <br>{{ $row['patient_phone'] }}
    @endif
</td>

                    <!-- Fee -->
                    <td>₹ {{ number_format($row['amount'], 2) }}</td>

                    <!-- Payment Details -->
                    <td>
                        <div>{{ $row['payment_id'] ?? '-' }}</div>
                        <div>
                          
                            @if($row['status'] === 'Authorized')
                                <span class="badge bg-success">Success</span>
                            @else
                                <span class="badge bg-danger">Failed</span>
                            @endif
                        <div>{{ \GeneralFunctions::formatDate($row['created_at']) }}</div>


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
