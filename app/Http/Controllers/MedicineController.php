<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    public function index(Request $request)
    {
        $query = Medicine::query();

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where('name', 'like', $search)
                  ->orWhere('manufacturer_name', 'like', $search)
                  ->orWhere('short_composition1', 'like', $search)
                  ->orWhere('therapeutic_class', 'like', $search);
        }

        if ($request->filled('type')) {
            $query->where('therapeutic_class', $request->type);
        }

        $medicines = $query->orderBy('name')
                           ->paginate(20)
                           ->withQueryString();

        $therapeuticClasses = Medicine::select('therapeutic_class')
                                      ->distinct()
                                      ->whereNotNull('therapeutic_class')
                                      ->orderBy('therapeutic_class')
                                      ->pluck('therapeutic_class');

        $pageTitle = "Medicines";

        return view('medicines.index', compact('medicines', 'therapeuticClasses','pageTitle'));
    }
}