<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminSubtaskRequest;
use App\Models\AdminSubtask;
use App\Models\AdminTask;
use App\Models\Lawyer;
use Illuminate\Http\Request;

class AdminSubtaskController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', AdminSubtask::class);

        $subtasks = AdminSubtask::with(['task.case', 'lawyer'])
            ->orderBy('next_date', 'desc')
            ->paginate(20);

        return view('admin-subtasks.index', compact('subtasks'));
    }

    public function create()
    {
        $this->authorize('create', AdminSubtask::class);

        $tasks = AdminTask::with('case')->orderBy('creation_date', 'desc')->get();
        $lawyers = Lawyer::orderBy('lawyer_name_en')->get();

        return view('admin-subtasks.create', compact('tasks', 'lawyers'));
    }

    public function store(AdminSubtaskRequest $request)
    {
        $this->authorize('create', AdminSubtask::class);

        $subtask = AdminSubtask::create($request->validated());

        return redirect()
            ->route('admin-subtasks.show', $subtask)
            ->with('success', __('app.admin_subtask_created_successfully'));
    }

    public function show(AdminSubtask $adminSubtask)
    {
        $this->authorize('view', $adminSubtask);

        $adminSubtask->load(['task.case', 'lawyer']);

        return view('admin-subtasks.show', compact('adminSubtask'));
    }

    public function edit(AdminSubtask $adminSubtask)
    {
        $this->authorize('update', $adminSubtask);

        $tasks = AdminTask::with('case')->orderBy('creation_date', 'desc')->get();
        $lawyers = Lawyer::orderBy('lawyer_name_en')->get();

        return view('admin-subtasks.edit', compact('adminSubtask', 'tasks', 'lawyers'));
    }

    public function update(AdminSubtaskRequest $request, AdminSubtask $adminSubtask)
    {
        $this->authorize('update', $adminSubtask);

        $adminSubtask->update($request->validated());

        return redirect()
            ->route('admin-subtasks.show', $adminSubtask)
            ->with('success', __('app.admin_subtask_updated_successfully'));
    }

    public function destroy(AdminSubtask $adminSubtask)
    {
        $this->authorize('delete', $adminSubtask);

        $adminSubtask->delete();

        return redirect()
            ->route('admin-subtasks.index')
            ->with('success', __('app.admin_subtask_deleted_successfully'));
    }
}

