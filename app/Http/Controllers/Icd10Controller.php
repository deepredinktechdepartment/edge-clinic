<?php

namespace App\Http\Controllers;

use App\Models\Icd10Code;
use Illuminate\Http\Request;

class Icd10Controller extends Controller
{
    public function index(Request $request)
    {
        $query = Icd10Code::query();

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where('full_code', 'like', $search)
                  ->orWhere('short_description', 'like', $search)
                  ->orWhere('long_description', 'like', $search);
        }

        $codes = $query->orderBy('full_code')
                       ->paginate(20)
                       ->withQueryString();

        return view('icd10.index', compact('codes'));
    }
}
