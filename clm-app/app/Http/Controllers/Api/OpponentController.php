<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\SchemaDrivenFields;
use App\Models\Opponent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OpponentController extends Controller
{
    use SchemaDrivenFields;
    public function index(Request $request): JsonResponse
    {
        try {
            $this->authorize('viewAny', Opponent::class);

            $query = Opponent::query();

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('opponent_name_ar', 'like', "%{$search}%")
                      ->orWhere('opponent_name_en', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $opponents = $query->orderBy('opponent_name_ar')
                ->paginate($request->get('per_page', 25));

            return response()->json($opponents);
        } catch (\Exception $e) {
            \Log::error('OpponentController@index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Failed to fetch opponents',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Opponent::class);

        $validated = $request->validate([
            'opponent_name_en' => 'required|string|max:255',
            'opponent_name_ar' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $opponent = Opponent::create($validated + [
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'data' => $opponent,
            'message' => 'Opponent created successfully',
        ], 201);
    }

    public function show(Opponent $opponent): JsonResponse
    {
        try {
            $this->authorize('view', $opponent);
            $opponent->load(['createdBy', 'updatedBy']);
            
            // Get cases from both pivot table and legacy opponent_id field
            // First, get cases from pivot table (many-to-many relationship)
            $casesFromPivot = $opponent->cases()->with([
                'client:id,client_name_ar,client_name_en',
                'opponents:id,opponent_name_ar,opponent_name_en',
            ])->get();
            
            // Also get cases from legacy opponent_id field (one-to-many)
            $casesFromLegacy = \App\Models\CaseModel::where('opponent_id', $opponent->id)
                ->with([
                    'client:id,client_name_ar,client_name_en',
                    'opponents:id,opponent_name_ar,opponent_name_en',
                ])
                ->get();
            
            // Merge both collections, avoiding duplicates
            $allCases = $casesFromPivot->merge($casesFromLegacy)->unique('id');
            
            // Transform cases to match React expectations
            $transformedCases = $allCases->map(function ($case) use ($opponent) {
                // Get capacity from pivot if available
                $capacity = '';
                if ($case->pivot && isset($case->pivot->capacity_id) && $case->pivot->capacity_id) {
                    $capacityOption = \App\Models\OptionValue::find($case->pivot->capacity_id);
                    if ($capacityOption) {
                        $capacity = $capacityOption->label_en ?? $capacityOption->label_ar ?? '';
                    }
                } elseif ($case->pivot && isset($case->pivot->alias_text) && $case->pivot->alias_text) {
                    $capacity = $case->pivot->alias_text;
                } elseif ($case->opponent_capacity_id) {
                    // Try legacy capacity field
                    $capacityOption = \App\Models\OptionValue::find($case->opponent_capacity_id);
                    if ($capacityOption) {
                        $capacity = $capacityOption->label_en ?? $capacityOption->label_ar ?? '';
                    }
                }
                
                // Build opponents array with this opponent included with capacity
                $opponents = $case->opponents->map(function ($opp) {
                    return [
                        'id' => $opp->id,
                        'opponent_name_ar' => $opp->opponent_name_ar,
                        'opponent_name_en' => $opp->opponent_name_en,
                    ];
                })->toArray();
                
                // Ensure this opponent is in the opponents array with capacity
                $opponentFound = false;
                foreach ($opponents as &$opp) {
                    if ($opp['id'] == $opponent->id) {
                        $opp['capacity'] = $capacity;
                        $opponentFound = true;
                        break;
                    }
                }
                // If opponent not found in opponents array, add it
                if (!$opponentFound) {
                    $opponents[] = [
                        'id' => $opponent->id,
                        'opponent_name_ar' => $opponent->opponent_name_ar,
                        'opponent_name_en' => $opponent->opponent_name_en,
                        'capacity' => $capacity,
                    ];
                }
                
                return [
                    'id' => $case->id,
                    'case_name_en' => $case->matter_name_en ?? '',
                    'case_name_ar' => $case->matter_name_ar ?? '',
                    'status' => $case->matter_status ?? '',
                    'client' => $case->client ? [
                        'id' => $case->client->id,
                        'client_name_ar' => $case->client->client_name_ar,
                        'client_name_en' => $case->client->client_name_en,
                    ] : null,
                    'opponents' => $opponents,
                ];
            })->values()->toArray();
            
            // Build opponent data with cases
            $rawData = $opponent->toArray();
            $opponentData = $rawData;
            $opponentData['cases'] = $transformedCases;
            
            // Get schema-driven field metadata
            $schemaData = $this->getSchemaFields('opponents', $opponent);
            
            return response()->json([
                'data' => $opponentData,
                'raw' => $rawData,
                'schema' => $schemaData,
            ]);
        } catch (\Exception $e) {
            \Log::error('OpponentController@show error: ' . $e->getMessage(), [
                'opponent_id' => $opponent->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Failed to fetch opponent',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, Opponent $opponent): JsonResponse
    {
        $this->authorize('update', $opponent);

        $validated = $request->validate([
            'opponent_name_en' => 'sometimes|required|string|max:255',
            'opponent_name_ar' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $opponent->update($validated + ['updated_by' => auth()->id()]);

        return response()->json([
            'data' => $opponent,
            'message' => 'Opponent updated successfully',
        ]);
    }

    public function destroy(Opponent $opponent): JsonResponse
    {
        $this->authorize('delete', $opponent);
        $opponent->delete();
        return response()->json(['message' => 'Opponent deleted successfully']);
    }

    public function schema(Opponent $opponent): JsonResponse
    {
        $this->authorize('view', $opponent);

        $schemaData = $this->getSchemaFields('opponents', $opponent);

        return response()->json($schemaData);
    }
}

