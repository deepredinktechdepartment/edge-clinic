@extends('template_v1')

@section('content')
<div class="tt-posts">
    <div class="d-flex justify-content-between tt-wrap mb-3">
        <div class="p-2"><h5 class="mb-0 pb-0">{{ $pageTitle }}</h5></div>
        <div class="p-2"><a href="{{ route('admin.partner-webhooks.create') }}" class="btn btn-brand btn-sm">Add Partner Webhook</a></div>
    </div>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="t-job-sheet container-fluid g-0">
    <div class="t-table table-responsive">
        <table class="table table-borderless table-hover" style="width:100%;">
            <thead><tr><th>Partner</th><th>Source</th><th>Webhook URL</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            @forelse($integrations as $integration)
                <tr>
                    <td>{{ $integration->partner_name }}</td>
                    <td>{{ $integration->source?->name }}</td>
                    <td class="text-break">{{ $integration->webhook_url }}</td>
                    <td><span class="badge bg-{{ $integration->is_enabled ? 'success' : 'secondary' }}">{{ $integration->is_enabled ? 'Enabled' : 'Disabled' }}</span></td>
                    <td>
                        <a href="{{ route('admin.partner-webhooks.edit', $integration) }}" class="text-warning me-3"><i class="fa fa-edit"></i> Configure</a>
                        <a href="{{ route('admin.partner-webhooks.logs', $integration) }}" class="text-primary"><i class="fa fa-list"></i> Deliveries</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No partner webhook has been configured.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
