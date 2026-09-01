@extends('template_v1')

@section('content')
<div class="tt-posts"><div class="d-flex justify-content-between tt-wrap mb-3"><div class="p-2"><h5 class="mb-0 pb-0">{{ $pageTitle }}</h5></div></div></div>

@if ($errors->any())
<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="row"><div class="col-lg-7"><div class="card shadow-sm"><div class="card-body">
    <form method="POST" action="{{ isset($partnerWebhook) ? route('admin.partner-webhooks.update', $partnerWebhook) : route('admin.partner-webhooks.store') }}">
        @csrf
        @if(isset($partnerWebhook)) @method('PUT') @endif
        <div class="mb-3">
            <label class="form-label">Partner Name <span class="text-danger">*</span></label>
            <input class="form-control" name="partner_name" value="{{ old('partner_name', $partnerWebhook->partner_name ?? '') }}" placeholder="Example: MediBuddy" required maxlength="100">
        </div>
        <div class="mb-3">
            <label class="form-label">Appointment Source <span class="text-danger">*</span></label>
            <select class="form-select" name="source_id" required>
                <option value="">Select source</option>
                @foreach($sources as $source)<option value="{{ $source->id }}" @selected((string) old('source_id', $partnerWebhook->source_id ?? '') === (string) $source->id)>{{ $source->name }}</option>@endforeach
            </select>
            <small class="text-muted">Only appointments with this exact source can send this webhook.</small>
        </div>
        <div class="mb-3">
            <label class="form-label">Webhook URL <span class="text-danger">*</span></label>
            <input type="url" class="form-control" name="webhook_url" value="{{ old('webhook_url', $partnerWebhook->webhook_url ?? '') }}" required maxlength="2048">
        </div>
        <div class="mb-3">
            <label class="form-label">Authentication <span class="text-danger">*</span></label>
            <select class="form-select" name="auth_type" id="authType" required>
                <option value="none" @selected(old('auth_type', $partnerWebhook->auth_type ?? 'basic') === 'none')>No Authentication</option>
                <option value="basic" @selected(old('auth_type', $partnerWebhook->auth_type ?? 'basic') === 'basic')>Basic Authentication</option>
                <option value="bearer" @selected(old('auth_type', $partnerWebhook->auth_type ?? 'basic') === 'bearer')>Bearer Token</option>
            </select>
        </div>
        <div class="row" id="basicAuthFields">
            <div class="col-md-6 mb-3"><label class="form-label">Basic Auth Username</label><input class="form-control" name="basic_auth_username" value="{{ old('basic_auth_username', $partnerWebhook->basic_auth_username ?? '') }}" maxlength="255"></div>
            <div class="col-md-6 mb-3"><label class="form-label">Basic Auth Password</label><input type="password" class="form-control" name="basic_auth_password" autocomplete="new-password" placeholder="{{ isset($partnerWebhook) ? 'Leave blank to keep existing password' : '' }}" maxlength="255">@if(isset($partnerWebhook) && filled($partnerWebhook->basic_auth_password))<small class="text-muted">Saved password: ••••••••. Leave this blank to keep it, or enter a new password to replace it.</small>@endif</div>
        </div>
        <div class="mb-3 d-none" id="bearerTokenField"><label class="form-label">Bearer Token</label><input type="password" class="form-control" name="bearer_token" autocomplete="new-password" placeholder="{{ isset($partnerWebhook) ? 'Leave blank to keep existing token' : '' }}" maxlength="2000">@if(isset($partnerWebhook) && filled($partnerWebhook->bearer_token))<small class="text-muted">Saved token: ••••••••. Leave this blank to keep it, or enter a new token to replace it.</small>@endif</div>
        <div class="mb-3"><label class="form-label">Timeout (seconds)</label><input type="number" class="form-control" name="timeout_seconds" value="{{ old('timeout_seconds', $partnerWebhook->timeout_seconds ?? 15) }}" min="3" max="60" required></div>
        <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_enabled" value="1" id="isEnabled" @checked(old('is_enabled', $partnerWebhook->is_enabled ?? false))><label class="form-check-label" for="isEnabled">Enable outgoing appointment-status webhooks</label></div>
        <button class="btn btn-brand">{{ isset($partnerWebhook) ? 'Update Configuration' : 'Save Configuration' }}</button>
        <a href="{{ route('admin.partner-webhooks.index') }}" class="btn btn-danger text-white">Back</a>
    </form>
</div></div></div></div>
@endsection

@push('scripts')
<script>
function toggleWebhookAuthFields() {
    const type = $('#authType').val();
    $('#basicAuthFields').toggleClass('d-none', type !== 'basic');
    $('#bearerTokenField').toggleClass('d-none', type !== 'bearer');
}
$(function () { $('#authType').on('change', toggleWebhookAuthFields); toggleWebhookAuthFields(); });
</script>
@endpush
