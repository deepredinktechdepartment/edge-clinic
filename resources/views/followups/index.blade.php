@extends('template_v1')

@section('content')
<div class="my-4">
    <div class="tt-posts"><div class="d-flex justify-content-between tt-wrap bg-white mb-3 p-2"><h5 class="mb-0 pb-0">{{ $pageTitle }}</h5><span class="small text-muted">Default view: today’s follow-ups</span></div></div>

    <div class="card shadow-sm mb-4"><div class="card-body">
        <form action="{{ route('admin.follow-ups.index') }}" method="GET" class="row gy-2 gx-3 align-items-end">
            <div class="col-xl-2 col-md-3"><label class="form-label">From date</label><input type="date" name="from_date" class="form-control form-control-sm" value="{{ $fromDate }}"></div>
            <div class="col-xl-2 col-md-3"><label class="form-label">To date</label><input type="date" name="to_date" class="form-control form-control-sm" value="{{ $toDate }}"></div>
            <div class="col-xl-2 col-md-3"><label class="form-label">Doctor</label><select name="doctor" class="form-select form-select-sm"><option value="">All doctors</option>@foreach($doctors as $doctor)<option value="{{ $doctor->id }}" @selected(request('doctor') == $doctor->id)>{{ $doctor->name }}</option>@endforeach</select></div>
            <div class="col-xl-2 col-md-3"><label class="form-label">Source</label><select name="source_id" class="form-select form-select-sm"><option value="">All sources</option>@foreach($sources as $source)<option value="{{ $source->id }}" @selected(request('source_id') == $source->id)>{{ $source->name }}</option>@endforeach</select></div>
            <div class="col-xl-auto d-flex gap-2"><button type="submit" class="btn btn-brand btn-sm px-3">Go</button><a href="{{ route('admin.follow-ups.index') }}" class="btn btn-outline-secondary btn-sm px-3">Reset</a></div>
        </form>
    </div></div>

    <div class="d-flex justify-content-between align-items-center mb-2"><span class="small text-muted fw-semibold">{{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}</span><span class="badge bg-light text-dark border">{{ $followUps->count() }} follow-up{{ $followUps->count() === 1 ? '' : 's' }}</span></div>
    <div class="t-job-sheet container-fluid g-0"><div class="t-table table-responsive"><table class="table table-borderless table-hover" id="default-datatable" style="width:100%"><thead><tr><th>#</th><th>Follow-up Date</th><th>Patient Details</th><th>Doctor</th><th>Previous Appointment</th><th>Source</th><th>Last Status</th></tr></thead><tbody>
        @forelse($followUps as $followUp)<tr>
            <td>{{ $loop->iteration }}</td>
            <td><strong>{{ \Carbon\Carbon::parse($followUp->follow_up_date)->format('d M Y') }}</strong>@if($followUp->follow_up_date === now()->toDateString())<br><span class="badge bg-warning text-dark">Due today</span>@endif</td>
            <td><strong>{{ $followUp->patient_name ?? '-' }}</strong><br><small>{{ $followUp->patient_mobile ?? '-' }}</small>@if($followUp->patient_email)<br><small>{{ $followUp->patient_email }}</small>@endif</td>
            <td>{{ $followUp->doctor_name ?? 'Not assigned' }}</td>
            <td>{{ $followUp->appointment_no ?? '-' }}<br><small>{{ $followUp->last_appointment_date ? \GeneralFunctions::formatDate($followUp->last_appointment_date) : '-' }} {{ $followUp->last_appointment_time ?? '' }}</small></td>
            <td>{{ $followUp->source_name ?? '-' }}</td>
            <td><span class="badge bg-light text-dark border">{{ $followUp->appointment_status ?? 'Scheduled' }}</span>@if($followUp->remarks)<br><small class="text-muted">{{ $followUp->remarks }}</small>@endif</td>
        </tr>@empty<tr><td colspan="7" class="text-center text-muted py-5">No follow-ups are due for the selected date range.</td></tr>@endforelse
    </tbody></table></div></div>
</div>
@endsection
