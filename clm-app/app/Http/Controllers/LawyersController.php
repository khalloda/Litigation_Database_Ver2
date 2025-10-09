<?php

namespace App\Http\Controllers;

use App\Http\Requests\LawyerRequest;
use App\Models\Lawyer;
use Illuminate\Http\Request;

class LawyersController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Lawyer::class, auth()->user());
        $lawyers = Lawyer::select('id', 'lawyer_name_ar', 'lawyer_name_en', 'lawyer_email', 'created_at', 'updated_at')
            ->orderBy('lawyer_name_ar')
            ->paginate(25);
        return view('lawyers.index', compact('lawyers'));
    }

    public function create()
    {
        $this->authorize('create', Lawyer::class, auth()->user());
        return view('lawyers.create');
    }

    public function store(LawyerRequest $request)
    {
        $this->authorize('create', Lawyer::class, auth()->user());
        $lawyer = Lawyer::create($request->validated() + [
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);
        return redirect()->route('lawyers.show', $lawyer)
            ->with('success', __('app.lawyer_created_success'));
    }

    public function show(Lawyer $lawyer)
    {
        $this->authorize('view', $lawyer, auth()->user());
        $lawyer->load('cases', 'adminTasks');
        return view('lawyers.show', compact('lawyer'));
    }

    public function edit(Lawyer $lawyer)
    {
        $this->authorize('update', $lawyer, auth()->user());
        return view('lawyers.edit', compact('lawyer'));
    }

    public function update(LawyerRequest $request, Lawyer $lawyer)
    {
        $this->authorize('update', $lawyer, auth()->user());
        $lawyer->update($request->validated() + [
            'updated_by' => auth()->id(),
        ]);
        return redirect()->route('lawyers.show', $lawyer)
            ->with('success', __('app.lawyer_updated_success'));
    }

    public function destroy(Lawyer $lawyer)
    {
        $this->authorize('delete', $lawyer, auth()->user());
        $lawyer->delete();
        return redirect()->route('lawyers.index')->with('success', __('app.lawyer_deleted_success'));
    }
}
