<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminTask;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $this->authorize('viewAny', AdminTask::class);

            $query = AdminTask::with(['case:id,matter_name_ar,matter_name_en', 'lawyer:id,lawyer_name_ar,lawyer_name_en']);

            if ($request->has('case_id')) {
                $query->where('matter_id', $request->case_id);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('lawyer_id')) {
                $query->where('lawyer_id', $request->lawyer_id);
            }

            $tasks = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 25));

            return response()->json($tasks);
        } catch (\Exception $e) {
            \Log::error('TaskController@index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Failed to fetch tasks',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', AdminTask::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'case_id' => 'nullable|exists:cases,id',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high',
            'status' => 'nullable|in:todo,in-progress,completed',
            'parent_id' => 'nullable|exists:admin_tasks,id',
        ]);

        $validated['matter_id'] = $validated['case_id'] ?? null;
        $validated['required_work'] = $validated['title'];
        $validated['status'] = $validated['status'] ?? 'todo';
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $task = AdminTask::create($validated);
        $task->load(['case', 'lawyer']);

        return response()->json([
            'data' => $task,
            'message' => 'Task created successfully',
        ], 201);
    }

    public function show(AdminTask $task): JsonResponse
    {
        $this->authorize('view', $task);
        $task->load(['case', 'lawyer']);
        return response()->json(['data' => $task]);
    }

    public function update(Request $request, AdminTask $task): JsonResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'case_id' => 'nullable|exists:cases,id',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high',
            'status' => 'nullable|in:todo,in-progress,completed',
        ]);

        if (isset($validated['case_id'])) {
            $validated['matter_id'] = $validated['case_id'];
        }
        if (isset($validated['title'])) {
            $validated['required_work'] = $validated['title'];
        }
        $validated['updated_by'] = auth()->id();

        $task->update($validated);
        $task->load(['case', 'lawyer']);

        return response()->json([
            'data' => $task,
            'message' => 'Task updated successfully',
        ]);
    }

    public function destroy(AdminTask $task): JsonResponse
    {
        $this->authorize('delete', $task);
        $task->delete();
        return response()->json(['message' => 'Task deleted successfully']);
    }
}

