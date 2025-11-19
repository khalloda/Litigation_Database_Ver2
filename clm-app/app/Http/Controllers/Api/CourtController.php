<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Court;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourtController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Court::class);

        $query = Court::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('court_name_ar', 'like', "%{$search}%")
                  ->orWhere('court_name_en', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $courts = $query->orderBy('court_name_ar')
            ->paginate($request->get('per_page', 25));

        return response()->json($courts);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Court::class);

        $validated = $request->validate([
            'court_name_en' => 'required|string|max:255',
            'court_name_ar' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;
        $court = Court::create($validated + [
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'data' => $court,
            'message' => 'Court created successfully',
        ], 201);
    }

    public function show(Court $court): JsonResponse
    {
        $this->authorize('view', $court);
        return response()->json(['data' => $court]);
    }

    public function update(Request $request, Court $court): JsonResponse
    {
        $this->authorize('update', $court);

        $validated = $request->validate([
            'court_name_en' => 'sometimes|required|string|max:255',
            'court_name_ar' => 'sometimes|required|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $court->update($validated + ['updated_by' => auth()->id()]);

        return response()->json([
            'data' => $court,
            'message' => 'Court updated successfully',
        ]);
    }

    public function destroy(Court $court): JsonResponse
    {
        $this->authorize('delete', $court);
        $court->delete();
        return response()->json(['message' => 'Court deleted successfully']);
    }
}

