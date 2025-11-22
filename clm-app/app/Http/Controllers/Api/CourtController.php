<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\SchemaDrivenFields;
use App\Models\Court;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourtController extends Controller
{
    use SchemaDrivenFields;
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
        try {
            $this->authorize('view', $court);

            $court->load([
                'circuits',
                'secretaries',
                'floors',
                'halls',
                'createdBy:id,name',
                'updatedBy:id,name',
                'cases.client:id,client_name_ar,client_name_en',
            ]);

            $cases = $court->cases->map(function ($case) {
                return [
                    'id' => $case->id,
                    'case_number' => (string) $case->id,
                    'case_name_ar' => $case->matter_name_ar,
                    'case_name_en' => $case->matter_name_en,
                    'status' => $case->matter_status ?? '',
                    'client' => $case->client ? [
                        'id' => $case->client->id,
                        'client_name_ar' => $case->client->client_name_ar,
                        'client_name_en' => $case->client->client_name_en,
                    ] : null,
                ];
            })->values()->toArray();

            $rawData = $court->toArray();
            $rawData['cases'] = $cases;

            $schemaData = $this->getSchemaFields('courts', $court);

            return response()->json([
                'data' => array_merge($rawData, ['cases' => $cases]),
                'raw' => $rawData,
                'schema' => $schemaData,
            ]);
        } catch (\Exception $e) {
            \Log::error('CourtController@show error: ' . $e->getMessage(), [
                'court_id' => $court->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to fetch court',
                'message' => $e->getMessage(),
            ], 500);
        }
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

    public function schema(Court $court): JsonResponse
    {
        $this->authorize('view', $court);

        $schemaData = $this->getSchemaFields('courts', $court);

        return response()->json($schemaData);
    }
}

