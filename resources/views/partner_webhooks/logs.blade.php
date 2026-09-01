@extends('template_v1')

@section('content')
<div class="tt-posts"><div class="d-flex justify-content-between tt-wrap mb-3"><div class="p-2"><h5 class="mb-0 pb-0">{{ $pageTitle }}</h5><small class="text-muted">Source: {{ $partnerWebhook->source?->name }}</small></div><div class="p-2"><a href="{{ route('admin.partner-webhooks.index') }}" class="btn btn-danger text-white btn-sm">Back</a></div></div></div>
<div class="t-job-sheet container-fluid g-0"><div class="t-table table-responsive"><table class="table table-borderless table-hover">
    <thead><tr><th>Time</th><th>Event</th><th>Appointment ID</th><th>Result</th><th>Response / Error</th></tr></thead>
    <tbody>
    @forelse($logs as $log)
        <tr>
            <td>{{ $log->created_at?->format('d M Y h:i A') }}</td>
            <td>{{ $log->event }}</td>
            <td>{{ $log->appointment_id ?? '-' }}</td>
            <td><span class="badge bg-{{ $log->delivered_at ? 'success' : 'danger' }}">{{ $log->delivered_at ? 'Delivered' : 'Failed' }}</span>@if($log->response_status)<div class="small text-muted">HTTP {{ $log->response_status }}</div>@endif</td>
            <td class="text-break">{{ $log->error_message ?: ($log->response_body ?: '-') }}</td>
        </tr>
    @empty
        <tr><td colspan="5" class="text-center text-muted py-4">No delivery attempts yet.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
{{ $logs->links() }}
@endsection
