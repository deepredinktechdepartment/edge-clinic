<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use Illuminate\Validation\Rule;

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

    // Apply filter only if category_id is not empty
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
        $pageTitle ="Add Service/Category";
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

        $rules['billing_type'] = 'required|in:billable,non_billable';
        $rules['gst_applicable'] = 'required|boolean';

        $rules['amount'] = [
            'nullable',
            'numeric',
            'min:0',
            Rule::requiredIf(function () use ($request) {
                return $request->billing_type === 'billable';
            }),
        ];

        $rules['gst_percentage'] = [
            'nullable',
            'numeric',
            'min:0',
            'max:100',
            Rule::requiredIf(function () use ($request) {
                return $request->gst_applicable == 1;
            }),
        ];
    }

    $data = $request->validate($rules);

    // If Category
    if (!$isService) {
        $data['billing_type'] = null;
        $data['amount'] = null;
        $data['gst_applicable'] = 0;
        $data['gst_percentage'] = null;
        $data['service_terms'] = null;
        $data['currency'] = 'INR';
    }

    Service::create($data);

    return redirect()->route('admin.services.index')
        ->with('success', 'Saved successfully');
}

    public function edit(Service $service)
    {
        $categories = Service::whereNull('parent_id')
            ->where('id', '!=', $service->id)
            ->get();

        $pageTitle ="Edit Service/Category";

        return view('services.create', compact('service', 'categories','pageTitle'));
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

        $rules['billing_type'] = 'required|in:billable,non_billable';
        $rules['gst_applicable'] = 'required|boolean';

        $rules['amount'] = [
            'nullable',
            'numeric',
            'min:0',
            Rule::requiredIf(function () use ($request) {
                return $request->billing_type === 'billable';
            }),
        ];

        $rules['gst_percentage'] = [
            'nullable',
            'numeric',
            'min:0',
            'max:100',
            Rule::requiredIf(function () use ($request) {
                return $request->gst_applicable == 1;
            }),
        ];
    }

    $data = $request->validate($rules);

    // If Category
    if (!$isService) {
        $data['billing_type'] = null;
        $data['amount'] = null;
        $data['gst_applicable'] = 0;
        $data['gst_percentage'] = null;
        $data['service_terms'] = null;
        $data['currency'] = 'INR';
    }

    $service->update($data);

    return redirect()->route('admin.services.index')
        ->with('success', 'Updated successfully');
}

    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()->route('services.index')
            ->with('success', 'Deleted successfully');
    }
}