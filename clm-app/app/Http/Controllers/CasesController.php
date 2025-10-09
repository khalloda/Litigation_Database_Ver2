<?php

namespace App\Http\Controllers;

use App\Http\Requests\CaseRequest;
use App\Models\CaseModel;
use App\Models\Client;
use Illuminate\Http\Request;

class CasesController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', CaseModel::class);
        $cases = CaseModel::with('client:id,client_name_ar,client_name_en')
            ->select('id', 'client_id', 'matter_name_ar', 'matter_name_en', 'matter_status', 'created_at', 'updated_at')
            ->orderBy('matter_name_ar')
            ->paginate(25);
        return view('cases.index', compact('cases'));
    }

    public function create()
    {
        $this->authorize('create', CaseModel::class);
        $clients = Client::select('id', 'client_name_ar', 'client_name_en')
            ->orderBy('client_name_ar')
            ->get();
        return view('cases.create', compact('clients'));
    }

    public function store(CaseRequest $request)
    {
        $this->authorize('create', CaseModel::class);
        $case = CaseModel::create($request->validated() + [
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);
        return redirect()->route('cases.show', $case)
            ->with('success', __('app.case_created_success'));
    }

    public function show(CaseModel $case)
    {
        $this->authorize('view', $case);
        $case->load('client', 'hearings', 'adminTasks', 'documents');
        return view('cases.show', compact('case'));
    }

    public function edit(CaseModel $case)
    {
        $this->authorize('update', $case);
        $clients = Client::select('id', 'client_name_ar', 'client_name_en')
            ->orderBy('client_name_ar')
            ->get();
        return view('cases.edit', compact('case', 'clients'));
    }

    public function update(CaseRequest $request, CaseModel $case)
    {
        $this->authorize('update', $case);
        $case->update($request->validated() + [
            'updated_by' => auth()->id(),
        ]);
        return redirect()->route('cases.show', $case)
            ->with('success', __('app.case_updated_success'));
    }

    public function destroy(CaseModel $case)
    {
        $this->authorize('delete', $case);
        $case->delete();
        return redirect()->route('cases.index')->with('success', __('app.case_deleted_success'));
    }
}
