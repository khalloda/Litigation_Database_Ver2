<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\SchemaDrivenFields;
use App\Http\Controllers\Controller;
use App\Http\Requests\PowerOfAttorneyRequest;
use App\Models\PowerOfAttorney;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PowerOfAttorneyController extends Controller
{
    use SchemaDrivenFields;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PowerOfAttorney::class);

        $query = PowerOfAttorney::with(['client:id,client_name_ar,client_name_en']);

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('principal_name', 'like', "%{$search}%")
                    ->orWhere('client_print_name', 'like', "%{$search}%")
                    ->orWhere('poa_number', 'like', "%{$search}%")
                    ->orWhere('serial', 'like', "%{$search}%");
            });
        }

        $powerOfAttorneys = $query
            ->orderByDesc('issue_date')
            ->paginate($request->integer('per_page', 25));

        $powerOfAttorneys->getCollection()->transform(function (PowerOfAttorney $poa) {
            return $this->transformForList($poa);
        });

        return response()->json($powerOfAttorneys);
    }

    public function store(PowerOfAttorneyRequest $request): JsonResponse
    {
        $this->authorize('create', PowerOfAttorney::class);

        $payload = $request->validated();
        $payload['created_by'] = $request->user()->id ?? null;
        $payload['updated_by'] = $request->user()->id ?? null;

        $powerOfAttorney = PowerOfAttorney
            ::create($payload)
            ->load(['client', 'createdBy', 'updatedBy']);

        return response()->json([
            'data' => $powerOfAttorney,
            'message' => __('app.power_of_attorney_created_success'),
        ], 201);
    }

    public function show(PowerOfAttorney $powerOfAttorney): JsonResponse
    {
        $this->authorize('view', $powerOfAttorney);

        $powerOfAttorney->load([
            'client',
            'createdBy:id,name',
            'updatedBy:id,name',
        ]);

        $payload = $powerOfAttorney->toArray();
        $schemaData = $this->getSchemaFields('power_of_attorneys', $powerOfAttorney);

        return response()->json([
            'data' => $payload,
            'raw' => $payload,
            'schema' => $schemaData,
        ]);
    }

    public function update(PowerOfAttorneyRequest $request, PowerOfAttorney $powerOfAttorney): JsonResponse
    {
        $this->authorize('update', $powerOfAttorney);

        $payload = $request->validated();
        $payload['updated_by'] = $request->user()->id ?? null;

        $powerOfAttorney->update($payload);
        $powerOfAttorney->load(['client', 'createdBy:id,name', 'updatedBy:id,name']);

        return response()->json([
            'data' => $powerOfAttorney,
            'message' => __('app.power_of_attorney_updated_success'),
        ]);
    }

    public function destroy(PowerOfAttorney $powerOfAttorney): JsonResponse
    {
        $this->authorize('delete', $powerOfAttorney);
        $powerOfAttorney->delete();

        return response()->json([
            'message' => __('app.power_of_attorney_deleted_success'),
        ]);
    }

    public function schema(PowerOfAttorney $powerOfAttorney): JsonResponse
    {
        $this->authorize('view', $powerOfAttorney);

        return response()->json(
            $this->getSchemaFields('power_of_attorneys', $powerOfAttorney)
        );
    }

    protected function transformForList(PowerOfAttorney $powerOfAttorney): array
    {
        return [
            'id' => $powerOfAttorney->id,
            'principal_name' => $powerOfAttorney->principal_name,
            'client' => $powerOfAttorney->client ? [
                'id' => $powerOfAttorney->client->id,
                'client_name_ar' => $powerOfAttorney->client->client_name_ar,
                'client_name_en' => $powerOfAttorney->client->client_name_en,
            ] : null,
            'poa_number' => $powerOfAttorney->poa_number,
            'issue_date' => optional($powerOfAttorney->issue_date)->toDateString(),
            'inventory' => (bool) $powerOfAttorney->inventory,
            'capacity' => $powerOfAttorney->capacity,
            'serial' => $powerOfAttorney->serial,
        ];
    }
}

