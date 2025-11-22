<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\SchemaDrivenFields;
use App\Http\Controllers\Controller;
use App\Models\Lawyer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LawyerController extends Controller
{
    use SchemaDrivenFields;

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
        try {
            $this->authorize('view', $lawyer);
            $lawyer->load([
                'title',
                'casesAsLawyerA:id,lawyer_a,lawyer_b,matter_name_ar,matter_name_en,matter_status',
                'casesAsLawyerB:id,lawyer_a,lawyer_b,matter_name_ar,matter_name_en,matter_status',
                'createdBy:id,name',
                'updatedBy:id,name',
            ]);

            $cases = $this->buildCaseCollection($lawyer);
            $rawData = $lawyer->toArray();
            $rawData['cases'] = $cases;

            $schemaData = $this->getSchemaFields('lawyers', $lawyer);

            return response()->json([
                'data' => array_merge($rawData, ['cases' => $cases]),
                'raw' => $rawData,
                'schema' => $schemaData,
            ]);
        } catch (\Exception $e) {
            \Log::error('LawyerController@show error: ' . $e->getMessage(), [
                'lawyer_id' => $lawyer->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to fetch lawyer',
                'message' => $e->getMessage(),
            ], 500);
        }
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

    public function schema(Lawyer $lawyer): JsonResponse
    {
        $this->authorize('view', $lawyer);

        $schemaData = $this->getSchemaFields('lawyers', $lawyer);

        return response()->json($schemaData);
    }

    protected function buildCaseCollection(Lawyer $lawyer): array
    {
        $casesA = $lawyer->casesAsLawyerA->map(fn($case) => $this->transformCaseForLawyer($case));
        $casesB = $lawyer->casesAsLawyerB->map(fn($case) => $this->transformCaseForLawyer($case));

        return $casesA->merge($casesB)
            ->unique('id')
            ->values()
            ->toArray();
    }

    protected function transformCaseForLawyer($case): array
    {
        return [
            'id' => $case->id,
            'case_number' => (string) $case->id,
            'case_name_ar' => $case->matter_name_ar,
            'case_name_en' => $case->matter_name_en,
            'status' => $case->matter_status ?? '',
        ];
    }
}

