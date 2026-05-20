@if(count($list) > 0)
<div class="t-job-sheet container-fluid g-0">
    <div class="t-table table-responsive">
        <table class="table table-borderless table-hover" id="default-datatable" style="width: 100%;">
        <thead>
        <tr>
            <th>#</th>
            <th>Appointment Details</th>
            <th>Doctor</th>
            <th>Patient Details</th>
            <th>Source</th>
            <th>Type</th>
            <th>Amount Details</th>
            <th>Payment Details</th>
        </tr>
        </thead>
        <tbody>
            @forelse($list as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>

                    <td>
                        @if(!empty($row['appointment_no']))
                            <div><h6 class="mb-0">Apt No: {{ $row['appointment_no'] }}</h6></div>
                        @endif

                        @if(!empty($row['appointment_date']) || !empty($row['appointment_time']))
                            <div>
                                {{ !empty($row['appointment_date']) ? \GeneralFunctions::formatDate($row['appointment_date']) : '' }}
                                {{ $row['appointment_time'] ?? '' }}
                            </div>
                        @endif
                    </td>

                    <td>{{ $row['doctor_name'] ?? '-' }}</td>

                    <td>
                        {{ $row['patient_name'] ?? '-' }}

                        @if(!empty($row['patient_email']))
                            <br>{{ $row['patient_email'] }}
                        @endif

                        @if(!empty($row['patient_phone']))
                            <br>{{ $row['patient_phone'] }}
                        @endif
                    </td>

                    <td>{{ $row['source_name'] ?? '-' }}</td>
                    <td>{{ ucfirst($row['type'] ?? '-') }}</td>

                    <td>
                        <div>Gross : Rs {{ number_format((float) ($row['gross_amount'] ?? (($row['doctor_fee'] ?? 0) + ($row['registration_fee'] ?? 0))), 2) }}</div>
                        <small>Doc.Fee : Rs {{ number_format((float) ($row['doctor_fee'] ?? 0), 2) }}</small><br>
                        <small>Reg.Fee : Rs {{ number_format((float) ($row['registration_fee'] ?? 0), 2) }}</small><br>
                        <small>Discount : {{ number_format((float) ($row['discount_percentage'] ?? 0), 2) }}% / Rs {{ number_format((float) ($row['discount_amount'] ?? 0), 2) }}</small><br>
                        <strong>Final : Rs {{ number_format((float) ($row['amount'] ?? 0), 2) }}</strong>
                    </td>

                    <td>
                        <div>{{ $row['payment_id'] ?? '-' }}</div>
                        <div>
                            @if(($row['status'] ?? '') === 'Authorized')
                                Payment is successful
                            @elseif(($row['status'] ?? '') === 'Pending')
                                Payment pending
                            @else
                                Payment failed
                            @endif
                        </div>
                        <div>{{ !empty($row['created_at']) ? \GeneralFunctions::formatDate($row['created_at']) : '-' }}</div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center">No appointments found</td></tr>
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
