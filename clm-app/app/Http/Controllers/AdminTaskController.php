<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminTaskRequest;
use App\Models\AdminTask;
use App\Models\CaseModel;
use App\Models\Lawyer;
use Illuminate\Http\Request;

class AdminTaskController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', AdminTask::class);

        $tasks = AdminTask::with(['case', 'lawyer'])
            ->orderBy('execution_date', 'desc')
            ->paginate(20);

        return view('admin-tasks.index', compact('tasks'));
    }

    public function create()
    {
        $this->authorize('create', AdminTask::class);

        $cases = CaseModel::orderBy('matter_name_en')->get();
        $lawyers = Lawyer::orderBy('lawyer_name_en')->get();

        return view('admin-tasks.create', compact('cases', 'lawyers'));
    }

    public function store(AdminTaskRequest $request)
    {
        $this->authorize('create', AdminTask::class);

        $task = AdminTask::create($request->validated());

        return redirect()
            ->route('admin-tasks.show', $task)
            ->with('success', __('app.admin_task_created_successfully'));
    }

    public function show(AdminTask $adminTask)
    {
        $this->authorize('view', $adminTask);

        $adminTask->load(['case', 'lawyer', 'subtasks.lawyer']);

        return view('admin-tasks.show', compact('adminTask'));
    }

    public function edit(AdminTask $adminTask)
    {
        $this->authorize('update', $adminTask);

        $cases = CaseModel::orderBy('matter_name_en')->get();
        $lawyers = Lawyer::orderBy('lawyer_name_en')->get();

        return view('admin-tasks.edit', compact('adminTask', 'cases', 'lawyers'));
    }

    public function update(AdminTaskRequest $request, AdminTask $adminTask)
    {
        $this->authorize('update', $adminTask);

        $adminTask->update($request->validated());

        return redirect()
            ->route('admin-tasks.show', $adminTask)
            ->with('success', __('app.admin_task_updated_successfully'));
    }

    public function destroy(AdminTask $adminTask)
    {
        $this->authorize('delete', $adminTask);

        $adminTask->delete();

        return redirect()
            ->route('admin-tasks.index')
            ->with('success', __('app.admin_task_deleted_successfully'));
    }
}

