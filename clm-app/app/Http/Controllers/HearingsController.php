<?php

namespace App\Http\Controllers;

use App\Http\Requests\HearingRequest;
use App\Models\Hearing;
use App\Models\CaseModel;
use Illuminate\Http\Request;

class HearingsController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Hearing::class, auth()->user());
        $hearings = Hearing::with('case:id,matter_name_ar,matter_name_en')
            ->select('id', 'matter_id', 'date', 'time', 'court', 'status', 'created_at', 'updated_at')
            ->orderBy('date', 'desc')
            ->paginate(25);
        return view('hearings.index', compact('hearings'));
    }

    public function create()
    {
        $this->authorize('create', Hearing::class, auth()->user());
        $cases = CaseModel::with('client:id,client_name_ar,client_name_en')
            ->select('id', 'client_id', 'matter_name_ar', 'matter_name_en')
            ->orderBy('matter_name_ar')
            ->get();
        return view('hearings.create', compact('cases'));
    }

    public function store(HearingRequest $request)
    {
        $this->authorize('create', Hearing::class, auth()->user());
        $hearing = Hearing::create($request->validated() + [
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);
        return redirect()->route('hearings.show', $hearing)
            ->with('success', __('app.hearing_created_success'));
    }

    public function show(Hearing $hearing)
    {
        $this->authorize('view', $hearing, auth()->user());
        $hearing->load('case.client');
        return view('hearings.show', compact('hearing'));
    }

    public function edit(Hearing $hearing)
    {
        $this->authorize('update', $hearing, auth()->user());
        $cases = CaseModel::with('client:id,client_name_ar,client_name_en')
            ->select('id', 'client_id', 'matter_name_ar', 'matter_name_en')
            ->orderBy('matter_name_ar')
            ->get();
        return view('hearings.edit', compact('hearing', 'cases'));
    }

    public function update(HearingRequest $request, Hearing $hearing)
    {
        $this->authorize('update', $hearing, auth()->user());
        $hearing->update($request->validated() + [
            'updated_by' => auth()->id(),
        ]);
        return redirect()->route('hearings.show', $hearing)
            ->with('success', __('app.hearing_updated_success'));
    }

    public function destroy(Hearing $hearing)
    {
        $this->authorize('delete', $hearing, auth()->user());
        $hearing->delete();
        return redirect()->route('hearings.index')->with('success', __('app.hearing_deleted_success'));
    }
}

