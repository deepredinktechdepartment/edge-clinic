<?php

namespace App\Http\Controllers;

use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SourceController extends Controller
{
    public function index()
    {
        $sources = Source::orderBy('name')->get();
        $pageTitle = 'Sources';
        $addlink = route('admin.sources.create');

        return view('sources.index', compact('sources', 'pageTitle', 'addlink'));
    }

    public function create()
    {
        $pageTitle = 'Add Source';

        return view('sources.create', compact('pageTitle'));
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

        return view('sources.create', compact('source', 'pageTitle'));
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
            'status' => 'nullable|boolean',
        ]);

        $validated['status'] = $request->boolean('status');

        return $validated;
    }
}
