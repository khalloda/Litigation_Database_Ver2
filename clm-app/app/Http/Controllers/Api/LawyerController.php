<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lawyer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LawyerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $this->authorize('viewAny', Lawyer::class);

            $query = Lawyer::with('title:id,label_ar,label_en');

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('lawyer_name_ar', 'like', "%{$search}%")
                      ->orWhere('lawyer_name_en', 'like', "%{$search}%");
                });
            }

            $lawyers = $query->orderBy('lawyer_name_ar')
                ->paginate($request->get('per_page', 25));

            return response()->json($lawyers);
        } catch (\Exception $e) {
            \Log::error('LawyerController@index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Failed to fetch lawyers',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Lawyer::class);

        $validated = $request->validate([
            'lawyer_name_en' => 'required|string|max:255',
            'lawyer_name_ar' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'title_id' => 'nullable|exists:option_values,id',
        ]);

        $validated['lawyer_email'] = $validated['email'] ?? null;
        $lawyer = Lawyer::create($validated + [
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $lawyer->load('title');

        return response()->json([
            'data' => $lawyer,
            'message' => 'Lawyer created successfully',
        ], 201);
    }

    public function show(Lawyer $lawyer): JsonResponse
    {
        $this->authorize('view', $lawyer);
        $lawyer->load(['title', 'casesAsLawyerA', 'casesAsLawyerB']);
        return response()->json(['data' => $lawyer]);
    }

    public function update(Request $request, Lawyer $lawyer): JsonResponse
    {
        $this->authorize('update', $lawyer);

        $validated = $request->validate([
            'lawyer_name_en' => 'sometimes|required|string|max:255',
            'lawyer_name_ar' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255',
            'title_id' => 'nullable|exists:option_values,id',
        ]);

        if (isset($validated['email'])) {
            $validated['lawyer_email'] = $validated['email'];
        }

        $lawyer->update($validated + ['updated_by' => auth()->id()]);
        $lawyer->load('title');

        return response()->json([
            'data' => $lawyer,
            'message' => 'Lawyer updated successfully',
        ]);
    }

    public function destroy(Lawyer $lawyer): JsonResponse
    {
        $this->authorize('delete', $lawyer);
        $lawyer->delete();
        return response()->json(['message' => 'Lawyer deleted successfully']);
    }
}

