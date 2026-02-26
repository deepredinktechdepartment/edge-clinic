<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::with('parent')
            ->orderByRaw('ISNULL(parent_id) DESC') // categories first
            ->orderBy('parent_id')
            ->orderBy('name');

        if (!empty($request->category_id)) {
            $query->where('parent_id', $request->category_id);
        }

        $services = $query->get();

        $categories = Service::whereNull('parent_id')
            ->orderBy('name')
            ->get();

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



public function destroy(Request $request, Service $service)
{
    DB::beginTransaction();

    try {

        // -------------------------
        // IF CATEGORY
        // -------------------------
        if (is_null($service->parent_id)) {

            $children = Service::where('parent_id', $service->id)->get();

            // If category has services & no confirmation yet
            if ($children->count() > 0 && !$request->has('confirm_delete')) {

                return back()->with('warning',
                    'This category has services. Click delete again to confirm.'
                );
            }

            // Check if any child service has invoices
            foreach ($children as $child) {

                if ($child->invoiceItems()->exists()) {
                    DB::rollBack();

                    return back()->with('error',
                        'Cannot delete. Some services have invoices.'
                    );
                }
            }

            // Safe → delete children
            foreach ($children as $child) {
                $child->delete();
            }
        }

        // -------------------------
        // IF SERVICE
        // -------------------------
        if ($service->invoiceItems()->exists()) {

            DB::rollBack();

            return back()->with('error',
                'This Service cannot be deleted because there are Invoices against this service.'
            );
        }

        $service->delete();

        DB::commit();

        return redirect()->route('admin.services.index')
            ->with('success', 'Deleted successfully');

    } catch (\Exception $e) {

        DB::rollBack();

        return back()->with('error', 'Something went wrong.');
    }
}
}