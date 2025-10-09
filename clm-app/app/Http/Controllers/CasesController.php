<?php

namespace App\Http\Controllers;

use App\Models\CaseModel;
use Illuminate\Http\Request;

class CasesController extends Controller
{
    public function index(Request $request)
    {
        $cases = CaseModel::with('client:id,client_name_ar,client_name_en')
            ->select('id', 'client_id', 'matter_name_ar', 'matter_name_en')
            ->orderBy('matter_name_ar')
            ->paginate(25);
        return view('cases.index', compact('cases'));
    }
}
