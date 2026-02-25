<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $categories = Service::whereNull('parent_id')
            ->orderBy('name')
            ->get();

        $query = Service::with('parent')
            ->whereNotNull('parent_id')
            ->orderBy('parent_id')
            ->orderBy('name');

        if (!empty($request->category_id)) {
            $query->where('parent_id', $request->category_id);
        }

        $services = $query->get();

        $pageTitle = "Services";
        $addlink = route('admin.services.create');

        return view('services.index', compact(
            'services',
            'categories',
            'pageTitle',
            'addlink'
        ));
    }

    public function create()
    {
        $categories = Service::whereNull('parent_id')->get();
        $pageTitle = "Add Service/Category";

        return view('services.create', compact('categories','pageTitle'));
    }

    public function store(Request $request)
{
    $isService = !empty($request->parent_id);

    $rules = [
        'name'        => 'required|string|max:255',
        'description' => 'nullable|string',
        'parent_id'   => 'nullable|exists:services,id',
    ];

    if ($isService) {
        $rules['amount'] = 'required|numeric|min:0';
        $rules['cgst']   = 'nullable|numeric|min:0|max:100';
        $rules['sgst']   = 'nullable|numeric|min:0|max:100';
        $rules['igst']   = 'nullable|numeric|min:0|max:100';
    }

    $data = $request->validate($rules);

    if ($isService) {

        $cgst = $request->cgst ?? 0;
        $sgst = $request->sgst ?? 0;
        $igst = $request->igst ?? 0;

        $totalGst = $cgst + $sgst + $igst;

        if ($totalGst > 100) {
            return back()
                ->withErrors(['cgst' => 'Total GST cannot exceed 100%.'])
                ->withInput();
        }

        $data['cgst'] = $cgst;
        $data['sgst'] = $sgst;
        $data['igst'] = $igst;

    } else {
        $data['amount'] = null;
        $data['cgst'] = 0;
        $data['sgst'] = 0;
        $data['igst'] = 0;
    }

    Service::create($data);

    return redirect()
        ->route('admin.services.index')
        ->with('success', 'Saved successfully');
}

    public function edit(Service $service)
    {
        $categories = Service::whereNull('parent_id')
            ->where('id', '!=', $service->id)
            ->get();

        $pageTitle = "Edit Service/Category";

        return view('services.create', compact('service','categories','pageTitle'));
    }

    public function update(Request $request, Service $service)
{
    $isService = !empty($request->parent_id);

    $rules = [
        'name'        => 'required|string|max:255',
        'description' => 'nullable|string',
        'parent_id'   => 'nullable|exists:services,id',
    ];

    if ($isService) {
        $rules['amount'] = 'required|numeric|min:0';
        $rules['cgst']   = 'nullable|numeric|min:0|max:100';
        $rules['sgst']   = 'nullable|numeric|min:0|max:100';
        $rules['igst']   = 'nullable|numeric|min:0|max:100';
    }

    $data = $request->validate($rules);

    if ($isService) {

        $cgst = $request->cgst ?? 0;
        $sgst = $request->sgst ?? 0;
        $igst = $request->igst ?? 0;

        $totalGst = $cgst + $sgst + $igst;

        if ($totalGst > 100) {
            return back()
                ->withErrors(['cgst' => 'Total GST cannot exceed 100%.'])
                ->withInput();
        }

        $data['cgst'] = $cgst;
        $data['sgst'] = $sgst;
        $data['igst'] = $igst;

    } else {
        $data['amount'] = null;
        $data['cgst'] = 0;
        $data['sgst'] = 0;
        $data['igst'] = 0;
    }

    $service->update($data);

    return redirect()
        ->route('admin.services.index')
        ->with('success', 'Updated successfully');
}

    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()->route('admin.services.index')
            ->with('success', 'Deleted successfully');
    }
}