<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\SchemaDrivenFields;
use App\Models\CaseModel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CaseController extends Controller
{
    use SchemaDrivenFields;
    /**
     * Display a listing of cases
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $this->authorize('viewAny', CaseModel::class);

            $query = CaseModel::with([
                'client:id,client_name_ar,client_name_en,client_print_name',
                'opponent:id,opponent_name_ar,opponent_name_en',
                'opponents:id,opponent_name_ar,opponent_name_en',
                'partner:id,lawyer_name_ar,lawyer_name_en',
                'court:id,court_name_ar,court_name_en',
            ]);

            // Apply filters
            if ($request->has('status')) {
                $query->where('matter_status', $request->status);
            }

            if ($request->has('client_id')) {
                $query->where('client_id', $request->client_id);
            }

            if ($request->has('partner_id')) {
                $query->where('matter_partner_id', $request->partner_id);
            }

            if ($request->has('opponent_id')) {
                $query->where('opponent_id', $request->opponent_id);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('matter_name_ar', 'LIKE', "%{$search}%")
                      ->orWhere('matter_name_en', 'LIKE', "%{$search}%");
                });
            }

            $cases = $query->paginate($request->get('per_page', 25));

            // Transform data to match React expectations
            $transformedData = $cases->getCollection()->map(function ($case) {
                return $this->transformCaseForApi($case);
            });

            return response()->json([
                'data' => $transformedData,
                'current_page' => $cases->currentPage(),
                'per_page' => $cases->perPage(),
                'total' => $cases->total(),
                'last_page' => $cases->lastPage(),
            ]);
        } catch (\Exception $e) {
            Log::error('CaseController@index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Failed to fetch cases',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Transform case model to match React API expectations
     */
    private function transformCaseForApi(CaseModel $case): array
    {
        // Get opponents with pivot data
        $opponents = $case->opponents->map(function ($opponent) {
            $capacityLabel = '';
            if ($opponent->pivot->capacity_id) {
                $capacityOption = \App\Models\OptionValue::find($opponent->pivot->capacity_id);
                $capacityLabel = $capacityOption ? ($capacityOption->label_en ?? $capacityOption->label_ar ?? '') : '';
            } else {
                $capacityLabel = $opponent->pivot->alias_text ?? '';
            }
            
            return [
                'id' => $opponent->id,
                'opponent_name_ar' => $opponent->opponent_name_ar,
                'opponent_name_en' => $opponent->opponent_name_en,
                'capacity' => $capacityLabel,
                'capacity_note' => $opponent->pivot->alias_text ?? null,
            ];
        })->toArray();

        // If no opponents from relationship, try single opponent
        if (empty($opponents) && $case->opponent) {
            $opponents = [[
                'id' => $case->opponent->id,
                'opponent_name_ar' => $case->opponent->opponent_name_ar,
                'opponent_name_en' => $case->opponent->opponent_name_en,
                'capacity' => '',
            ]];
        }

        return [
            'id' => $case->id,
            'mfiles_id' => $case->mfiles_id,
            // Map matter_* to case_* for React compatibility
            'case_name_en' => $case->matter_name_en,
            'case_name_ar' => $case->matter_name_ar,
            'case_number' => (string) $case->id, // Use ID as case number for now
            'matter_name_en' => $case->matter_name_en,
            'matter_name_ar' => $case->matter_name_ar,
            'status' => $case->matter_status ?? '',
            'matter_status' => $case->matter_status,
            'case_start_date' => $case->matter_start_date?->format('Y-m-d') ?? '',
            'case_end_date' => $case->matter_end_date?->format('Y-m-d'),
            'next_hearing_date' => $case->next_hearing_date?->format('Y-m-d'),
            'last_hearing_date' => $case->last_hearing_date?->format('Y-m-d'),
            'latest_decision' => $case->latest_decision,
            'case_description' => $case->matter_description ?? '',
            'client' => $case->client ? [
                'id' => $case->client->id,
                'client_name_ar' => $case->client->client_name_ar,
                'client_name_en' => $case->client->client_name_en,
                'client_print_name' => $case->client->client_print_name ?? '',
            ] : null,
            'client_in_case_name' => $case->client_in_case_name,
            'client_capacity' => $case->clientCapacity ? ($case->clientCapacity->label_en ?? $case->clientCapacity->label_ar ?? '') : '',
            'client_capacity_note' => $case->client_capacity_note,
            'opponents' => $opponents,
            'opponent' => $case->opponent ? [
                'id' => $case->opponent->id,
                'opponent_name_ar' => $case->opponent->opponent_name_ar,
                'opponent_name_en' => $case->opponent->opponent_name_en,
            ] : null,
            'partner' => $case->partner ? [
                'id' => $case->partner->id,
                'lawyer_name_ar' => $case->partner->lawyer_name_ar,
                'lawyer_name_en' => $case->partner->lawyer_name_en,
            ] : null,
            'court' => $case->court ? [
                'id' => $case->court->id,
                'court_name_ar' => $case->court->court_name_ar,
                'court_name_en' => $case->court->court_name_en,
            ] : null,
            'case_asked_amount' => $case->matter_asked_amount,
            'case_judged_amount' => $case->matter_judged_amount,
            'fee_letter' => $case->fee_letter,
            'hearings' => $case->hearings ? $case->hearings->map(function ($hearing) {
                return [
                    'id' => $hearing->id,
                    'date' => $hearing->date?->format('Y-m-d'),
                    'procedure' => $hearing->procedure,
                    'court' => $hearing->court,
                    'circuit' => $hearing->circuit,
                    'status' => $hearing->status,
                ];
            })->toArray() : [],
            'documents' => $case->documents ? $case->documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'document_description' => $doc->document_description,
                    'document_type' => $doc->document_type,
                    'deposit_date' => $doc->deposit_date?->format('Y-m-d'),
                ];
            })->toArray() : [],
            'tasks' => $case->adminTasks ? $case->adminTasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->required_work ?? $task->title ?? '',
                    'status' => $task->status,
                    'subtasks' => $task->subtasks ? $task->subtasks->map(function ($subtask) {
                        return [
                            'id' => $subtask->id,
                            'performer' => $subtask->performer,
                            'next_date' => $subtask->next_date?->format('Y-m-d'),
                            'result' => $subtask->result,
                        ];
                    })->toArray() : [],
                ];
            })->toArray() : [],
            'created_at' => $case->created_at?->toISOString(),
            'updated_at' => $case->updated_at?->toISOString(),
        ];
    }

    /**
     * Store a newly created case
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', CaseModel::class);

        $validated = $request->validate([
            'case_name_en' => 'required|string|max:255',
            'case_name_ar' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'nullable|exists:clients,id',
            'opponent_id' => 'nullable|exists:opponents,id',
            'partner_id' => 'nullable|exists:lawyers,id',
            'court_id' => 'nullable|exists:courts,id',
            'start_date' => 'nullable|date',
            'mfiles_id' => 'nullable|string|max:255',
        ]);

        $validated['matter_name_en'] = $validated['case_name_en'];
        $validated['matter_name_ar'] = $validated['case_name_ar'];
        $validated['client_id'] = $validated['client_id'] ?? null;
        $validated['opponent_id'] = $validated['opponent_id'] ?? null;
        $validated['matter_partner_id'] = $validated['partner_id'] ?? null;
        $validated['court_id'] = $validated['court_id'] ?? null;
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $case = CaseModel::create($validated);
        $case->load(['client', 'opponent', 'partner', 'court']);

        return response()->json([
            'data' => $case,
            'message' => 'Case created successfully',
        ], 201);
    }

    /**
     * Display the specified case
     */
    public function show(CaseModel $case): JsonResponse
    {
        try {
            $this->authorize('view', $case);

            $case->load([
                'client',
                'contract',
                'opponent',
                'opponents',
                'partner',
                'court',
                'hearings',
                'documents',
                'adminTasks.subtasks',
                'matterCategory',
                'matterDegree',
                'matterStatus',
                'matterImportance',
                'matterBranch',
                'clientCapacity',
                'clientType',
                'opponentCapacity',
                'matterDestinationRef',
                'matterPartnerRef',
                'circuitName',
                'circuitSerial',
                'circuitShift',
                'circuitSecretaryRef',
                'courtFloorRef',
                'courtHallRef',
            ]);

            // Get schema-driven field metadata
            $schemaData = $this->getSchemaFields('cases', $case);

            return response()->json([
                'data' => $this->transformCaseForApi($case),
                'raw' => $case->toArray(),
                'schema' => $schemaData,
            ]);
        } catch (\Exception $e) {
            Log::error('CaseController@show error: ' . $e->getMessage(), [
                'case_id' => $case->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Failed to fetch case',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get schema metadata for the specified case.
     */
    public function schema(CaseModel $case): JsonResponse
    {
        $this->authorize('view', $case);

        $schemaData = $this->getSchemaFields('cases', $case);

        return response()->json($schemaData);
    }

    /**
     * Update the specified case
     */
    public function update(Request $request, CaseModel $case): JsonResponse
    {
        $this->authorize('update', $case);

        $validated = $request->validate([
            'case_name_en' => 'sometimes|required|string|max:255',
            'case_name_ar' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'nullable|exists:clients,id',
            'opponent_id' => 'nullable|exists:opponents,id',
            'partner_id' => 'nullable|exists:lawyers,id',
            'court_id' => 'nullable|exists:courts,id',
            'start_date' => 'nullable|date',
            'mfiles_id' => 'nullable|string|max:255',
        ]);

        if (isset($validated['case_name_en'])) {
            $validated['matter_name_en'] = $validated['case_name_en'];
        }
        if (isset($validated['case_name_ar'])) {
            $validated['matter_name_ar'] = $validated['case_name_ar'];
        }
        if (isset($validated['partner_id'])) {
            $validated['matter_partner_id'] = $validated['partner_id'];
        }
        $validated['updated_by'] = auth()->id();

        $case->update($validated);
        $case->load(['client', 'opponent', 'partner', 'court']);

        return response()->json([
            'data' => $case,
            'message' => 'Case updated successfully',
        ]);
    }

    /**
     * Remove the specified case
     */
    public function destroy(CaseModel $case): JsonResponse
    {
        $this->authorize('delete', $case);

        $case->delete();

        return response()->json([
            'message' => 'Case deleted successfully',
        ]);
    }
}

