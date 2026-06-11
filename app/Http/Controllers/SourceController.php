<?php

namespace App\Http\Controllers;

use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\AppointmentPaymentStateService;

class SourceController extends Controller
{
    public function index()
    {
        $sources = Source::orderBy('name')->get();
        $pageTitle = 'Sources';
        $addlink = route('admin.sources.create');
        $paymentRuleLabels = app(AppointmentPaymentStateService::class)->ruleOptions();

        return view('sources.index', compact('sources', 'pageTitle', 'addlink', 'paymentRuleLabels'));
    }

    public function create()
    {
        $pageTitle = 'Add Source';
        $paymentRules = app(AppointmentPaymentStateService::class)->ruleOptions();

        return view('sources.create', compact('pageTitle', 'paymentRules'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        Source::create($data);

        return redirect()
            ->route('admin.sources.index')
            ->with('success', 'Source created successfully');
    }

    public function edit(Source $source)
    {
        $pageTitle = 'Edit Source';
        $paymentRules = app(AppointmentPaymentStateService::class)->ruleOptions();

        return view('sources.create', compact('source', 'pageTitle', 'paymentRules'));
    }

    public function update(Request $request, Source $source)
    {
        $data = $this->validateData($request, $source->id);
        $source->update($data);

        return redirect()
            ->route('admin.sources.index')
            ->with('success', 'Source updated successfully');
    }

    public function destroy(Source $source)
    {
        $source->delete();

        return redirect()
            ->route('admin.sources.index')
            ->with('success', 'Source deleted successfully');
    }

    private function validateData(Request $request, ?int $sourceId = null): array
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sources', 'name')->ignore($sourceId),
            ],
            'description' => 'nullable|string',
            'payment_rule' => 'nullable|in:no_payment_required,paid,pending',
            'status' => 'nullable|boolean',
        ]);

        $validated['status'] = $request->boolean('status');

        return $validated;
    }
}
