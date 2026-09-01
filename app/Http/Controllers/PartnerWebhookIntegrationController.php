<?php

namespace App\Http\Controllers;

use App\Models\PartnerWebhookIntegration;
use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PartnerWebhookIntegrationController extends Controller
{
    public function index()
    {
        return view('partner_webhooks.index', [
            'pageTitle' => 'Partner Webhooks',
            'integrations' => PartnerWebhookIntegration::with('source')->orderBy('partner_name')->get(),
        ]);
    }

    public function create()
    {
        return view('partner_webhooks.form', [
            'pageTitle' => 'Add Partner Webhook',
            'sources' => Source::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        PartnerWebhookIntegration::create($this->validated($request));

        return redirect()->route('admin.partner-webhooks.index')->with('success', 'Partner webhook saved successfully.');
    }

    public function edit(PartnerWebhookIntegration $partnerWebhook)
    {
        return view('partner_webhooks.form', [
            'pageTitle' => 'Edit Partner Webhook',
            'partnerWebhook' => $partnerWebhook,
            'sources' => Source::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function logs(PartnerWebhookIntegration $partnerWebhook)
    {
        return view('partner_webhooks.logs', [
            'pageTitle' => $partnerWebhook->partner_name . ' Webhook Deliveries',
            'partnerWebhook' => $partnerWebhook->load('source'),
            'logs' => $partnerWebhook->deliveryLogs()->latest()->paginate(50),
        ]);
    }

    public function update(Request $request, PartnerWebhookIntegration $partnerWebhook)
    {
        $data = $this->validated($request, $partnerWebhook);
        if (blank($data['basic_auth_password'] ?? null)) {
            unset($data['basic_auth_password']);
        }

        $partnerWebhook->update($data);

        return redirect()->route('admin.partner-webhooks.index')->with('success', 'Partner webhook updated successfully.');
    }

    private function validated(Request $request, ?PartnerWebhookIntegration $partnerWebhook = null): array
    {
        $validated = $request->validate([
            'source_id' => [
                'required',
                'exists:sources,id',
                Rule::unique('partner_webhook_integrations', 'source_id')->ignore($partnerWebhook?->id),
            ],
            'partner_name' => 'required|string|max:100',
            'webhook_url' => 'required|url|max:2048',
            'basic_auth_username' => 'nullable|string|max:255',
            'basic_auth_password' => $partnerWebhook ? 'nullable|string|max:255' : 'nullable|string|max:255',
            'timeout_seconds' => 'required|integer|min:3|max:60',
        ]);

        $validated['is_enabled'] = $request->boolean('is_enabled');

        return $validated;
    }
}
