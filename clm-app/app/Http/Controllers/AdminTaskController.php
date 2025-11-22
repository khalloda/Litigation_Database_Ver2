<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SchemaDrivenFields;
use App\Http\Requests\AdminTaskRequest;
use App\Models\AdminTask;
use App\Models\CaseModel;
use App\Models\Lawyer;
use Illuminate\Http\Request;

class AdminTaskController extends Controller
{
    use SchemaDrivenFields;
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

        // Eager load relations for FK resolution
        $adminTask->load(['case', 'lawyer', 'subtasks.lawyer', 'createdBy', 'updatedBy']);

        // Get schema-driven field metadata for task
        $taskSchemaData = $this->getSchemaFields('admin_work_tasks', $adminTask);
        
        // Get schema-driven field metadata for subtasks
        $subtaskSchemaData = null;
        if ($adminTask->subtasks->isNotEmpty()) {
            $subtaskSchemaData = $this->getSchemaFields('admin_work_subtasks', $adminTask->subtasks->first());
        }

        return view('admin-tasks.show', compact('adminTask', 'taskSchemaData', 'subtaskSchemaData'));
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

