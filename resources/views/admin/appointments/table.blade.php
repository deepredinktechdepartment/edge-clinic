@if(count($list) > 0)
<div class="t-job-sheet container-fluid g-0">
    <div class="t-table table-responsive">
        <table class="table table-borderless table-hover" id="default-datatable" style="width: 100%;">
        <thead>
        <tr>
            <th>#</th> <!-- Serial Number -->
            <th>Appointment No </th>
            <th>Time Slot</th>
            <th>Doctor</th>
            <th>Patient Details</th>
            <th>Amount</th>
            <th>Payment Status</th> <!-- New column -->
        </tr>
        </thead>
        <tbody>
            @forelse($list as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td> <!-- Serial Number -->

                    <!-- Appointment Details -->
                    <td>
                        <div>{{ $row['appointment_no'] ??'' }}</div>
                    </td>
                   <td>
    @if(!empty($row['appointment_date']) && !empty($row['appointment_time']))
        <div>{{ \GeneralFunctions::formatDate($row['appointment_date']) }}, {{ $row['appointment_time'] }}</div>
    @endif
</td>
                    <!-- Doctor -->
                    <td>{{ Str::title($row['doctor_name']) ??'' }}</td>

                    <!-- Patient Details -->
                    <td>
                        {{ Str::title($row['patient_name'])??'' }}<br>
                        {{ $row['patient_phone'] ?? '-' }}
                    </td>

                    <!-- Fee -->
                    <td>₹ {{ number_format($row['amount'], 2) ?? '' }}</td>

                    <!-- Payment Details -->
                    <td>

                        <div>

                            @if($row['status'] === 'Authorized')
                                Payment is successful
                            @else
                                Payment failed
                            @endif
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
