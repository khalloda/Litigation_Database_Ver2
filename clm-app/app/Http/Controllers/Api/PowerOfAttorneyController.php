<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\SchemaDrivenFields;
use App\Http\Controllers\Controller;
use App\Http\Requests\PowerOfAttorneyRequest;
use App\Models\PowerOfAttorney;
use App\Models\PowerOfAttorneyMovement;
use Barryvdh\Snappy\Facades\SnappyPdf;
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
            'movements.lawyer:id,lawyer_name_ar,lawyer_name_en',
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

    public function createMovement(Request $request, PowerOfAttorney $powerOfAttorney): JsonResponse
    {
        $this->authorize('update', $powerOfAttorney);

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'from_location' => ['required', 'string', 'max:255'],
            'to_location' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:32'],
            'lawyer_id' => ['nullable', 'integer', 'exists:lawyers,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $movement = PowerOfAttorneyMovement::create([
            'power_of_attorney_id' => $powerOfAttorney->id,
            'date' => $validated['date'],
            'from_location' => $validated['from_location'] ?? null,
            'to_location' => $validated['to_location'] ?? null,
            'status' => $validated['status'],
            'lawyer_id' => $validated['lawyer_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => $request->user()->id ?? null,
            'updated_by' => $request->user()->id ?? null,
        ]);

        $movement->load('lawyer:id,lawyer_name_ar,lawyer_name_en');

        return response()->json([
            'data' => $movement,
        ], 201);
    }

    public function updateMovement(Request $request, PowerOfAttorney $powerOfAttorney, PowerOfAttorneyMovement $movement): JsonResponse
    {
        $this->authorize('update', $powerOfAttorney);

        if ($movement->power_of_attorney_id !== $powerOfAttorney->id) {
            abort(404);
        }

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'from_location' => ['required', 'string', 'max:255'],
            'to_location' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:32'],
            'lawyer_id' => ['nullable', 'integer', 'exists:lawyers,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $movement->update([
            'date' => $validated['date'],
            'from_location' => $validated['from_location'] ?? null,
            'to_location' => $validated['to_location'] ?? null,
            'status' => $validated['status'],
            'lawyer_id' => $validated['lawyer_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'updated_by' => $request->user()->id ?? null,
        ]);

        $movement->load('lawyer:id,lawyer_name_ar,lawyer_name_en');

        return response()->json([
            'data' => $movement,
        ]);
    }

    public function movementCardPdf(Request $request, PowerOfAttorney $powerOfAttorney)
    {
        $this->authorize('view', $powerOfAttorney);

        $locale = $request->input('locale', app()->getLocale() ?? 'ar');

        $powerOfAttorney->load(['client', 'movements.lawyer']);

        $firmLogoPath = public_path('assets/logo-BU5yR0AT.png');

        $pdf = SnappyPdf::loadView('reports.poa_movement_card', [
            'locale' => $locale,
            'poa' => $powerOfAttorney,
            'movements' => $powerOfAttorney->movements,
            'firmLogoPath' => is_file($firmLogoPath) ? $firmLogoPath : null,
            'generatedAt' => now(),
            'totalMovements' => $powerOfAttorney->movements->count(),
        ])->setPaper('a4', 'portrait')
          ->setOption('encoding', 'UTF-8');

        $fileName = 'poa-movement-card-' . $powerOfAttorney->id . '-' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($fileName);
    }

    protected function transformForList(PowerOfAttorney $powerOfAttorney): array
    {
        return [
            'id' => $powerOfAttorney->id,
            'mfiles_id' => $powerOfAttorney->mfiles_id,
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

