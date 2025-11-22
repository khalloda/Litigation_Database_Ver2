<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\SchemaDrivenFields;
use App\Models\Hearing;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class HearingController extends Controller
{
    use SchemaDrivenFields;
    public function index(Request $request): JsonResponse
    {
        try {
            $this->authorize('viewAny', Hearing::class);

            $query = Hearing::with([
                'case:id,matter_name_ar,matter_name_en,client_id',
                'lawyer:id,lawyer_name_ar,lawyer_name_en',
            ]);

            if ($request->has('case_id')) {
                $query->where('matter_id', $request->case_id);
            }

            if ($request->has('start_date')) {
                $query->where('date', '>=', $request->start_date);
            }

            if ($request->has('end_date')) {
                $query->where('date', '<=', $request->end_date);
            }

            $hearings = $query->orderBy('date', 'desc')
                ->paginate($request->get('per_page', 25));

            return response()->json($hearings);
        } catch (\Exception $e) {
            \Log::error('HearingController@index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Failed to fetch hearings',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Hearing::class);

        $validated = $request->validate([
            'case_id' => 'required|exists:cases,id',
            'hearing_date' => 'required|date',
            'procedure' => 'nullable|string',
            'court_id' => 'nullable|exists:courts,id',
            'circuit' => 'nullable|string',
            'decision' => 'nullable|string',
            'next_hearing_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'attending_lawyer_id' => 'nullable|exists:lawyers,id',
        ]);

        $validated['matter_id'] = $validated['case_id'];
        $validated['date'] = $validated['hearing_date'];
        $validated['lawyer_id'] = $validated['attending_lawyer_id'] ?? null;
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $hearing = Hearing::create($validated);
        $hearing->load(['case', 'lawyer']);

        return response()->json([
            'data' => $hearing,
            'message' => 'Hearing created successfully',
        ], 201);
    }

    public function show(Hearing $hearing): JsonResponse
    {
        try {
            $this->authorize('view', $hearing);
            $hearing->load([
                'case.client:id,client_name_ar,client_name_en',
                'lawyer:id,lawyer_name_ar,lawyer_name_en',
                'createdBy:id,name',
                'updatedBy:id,name',
            ]);

            $caseData = $hearing->case ? [
                'id' => $hearing->case->id,
                'case_name_ar' => $hearing->case->matter_name_ar,
                'case_name_en' => $hearing->case->matter_name_en,
                'client' => $hearing->case->client ? [
                    'id' => $hearing->case->client->id,
                    'client_name_ar' => $hearing->case->client->client_name_ar,
                    'client_name_en' => $hearing->case->client->client_name_en,
                ] : null,
            ] : null;

            $lawyerData = $hearing->lawyer ? [
                'id' => $hearing->lawyer->id,
                'lawyer_name_ar' => $hearing->lawyer->lawyer_name_ar,
                'lawyer_name_en' => $hearing->lawyer->lawyer_name_en,
            ] : null;

            $rawData = $hearing->toArray();
            $rawData['case'] = $caseData;
            $rawData['lawyer'] = $lawyerData;

            $schemaData = $this->getSchemaFields('hearings', $hearing);

            return response()->json([
                'data' => array_merge($rawData, [
                    'case' => $caseData,
                    'lawyer' => $lawyerData,
                ]),
                'raw' => $rawData,
                'schema' => $schemaData,
            ]);
        } catch (\Exception $e) {
            \Log::error('HearingController@show error: ' . $e->getMessage(), [
                'hearing_id' => $hearing->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to fetch hearing',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, Hearing $hearing): JsonResponse
    {
        $this->authorize('update', $hearing);

        $validated = $request->validate([
            'case_id' => 'sometimes|required|exists:cases,id',
            'hearing_date' => 'sometimes|required|date',
            'procedure' => 'nullable|string',
            'court_id' => 'nullable|exists:courts,id',
            'circuit' => 'nullable|string',
            'decision' => 'nullable|string',
            'next_hearing_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'attending_lawyer_id' => 'nullable|exists:lawyers,id',
        ]);

        if (isset($validated['case_id'])) {
            $validated['matter_id'] = $validated['case_id'];
        }
        if (isset($validated['hearing_date'])) {
            $validated['date'] = $validated['hearing_date'];
        }
        if (isset($validated['attending_lawyer_id'])) {
            $validated['lawyer_id'] = $validated['attending_lawyer_id'];
        }
        $validated['updated_by'] = auth()->id();

        $hearing->update($validated);
        $hearing->load(['case', 'lawyer']);

        return response()->json([
            'data' => $hearing,
            'message' => 'Hearing updated successfully',
        ]);
    }

    public function destroy(Hearing $hearing): JsonResponse
    {
        $this->authorize('delete', $hearing);
        $hearing->delete();
        return response()->json(['message' => 'Hearing deleted successfully']);
    }

    public function schema(Hearing $hearing): JsonResponse
    {
        $this->authorize('view', $hearing);

        $schemaData = $this->getSchemaFields('hearings', $hearing);

        return response()->json($schemaData);
    }
}

