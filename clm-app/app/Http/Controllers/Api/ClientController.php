<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\SchemaDrivenFields;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    use SchemaDrivenFields;
    /**
     * Display a listing of clients
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $this->authorize('viewAny', Client::class);

            $query = Client::with([
                'contactLawyer:id,lawyer_name_ar,lawyer_name_en',
                'statusRef:id,label_ar,label_en',
                'cashOrProbono:id,label_ar,label_en',
            ])->withCount('cases');

            // Apply filters
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('client_name_ar', 'LIKE', "%{$search}%")
                      ->orWhere('client_name_en', 'LIKE', "%{$search}%")
                      ->orWhere('client_print_name', 'LIKE', "%{$search}%");
                });
            }

            if ($request->has('status_id')) {
                $query->where('status_id', $request->status_id);
            }

            if ($request->has('client_id')) {
                $query->where('id', $request->client_id);
            }

            $clients = $query->orderBy(app()->getLocale() == 'ar' ? 'client_name_ar' : 'client_name_en')
                ->paginate($request->get('per_page', 25));

            return response()->json($clients);
        } catch (\Exception $e) {
            \Log::error('ClientController@index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Failed to fetch clients',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created client
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Client::class);

        $validated = $request->validate([
            'client_name_en' => 'required|string|max:255',
            'client_name_ar' => 'required|string|max:255',
            'status' => 'nullable|string',
            'client_code' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
        ]);

        $validated['status_id'] = $request->status_id ?? null;
        $validated['client_start'] = $validated['start_date'] ?? null;
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $client = Client::create($validated);
        $client->load(['contactLawyer', 'statusRef', 'cashOrProbono']);

        return response()->json([
            'data' => $client,
            'message' => 'Client created successfully',
        ], 201);
    }

    /**
     * Display the specified client
     */
    public function show(Client $client): JsonResponse
    {
        $this->authorize('view', $client);

        \Log::info('ApiClientController@show invoked via API route', ['client_id' => $client->id]);

        $client->load([
            'cashOrProbono',
            'statusRef',
            'contactLawyer',
            'cases:id,client_id,matter_name_ar,matter_name_en',
        ]);

        // Get schema-driven field metadata
        $schemaData = $this->getSchemaFields('clients', $client);

        // Match CaseController structure exactly - use response()->json() with {data: ..., schema: ...}
        // Convert model to array to avoid Laravel's automatic model serialization unwrapping
        return response()->json([
            'data' => $client->toArray(),
            'schema' => $schemaData,
        ]);
    }

    /**
     * Update the specified client
     */
    public function update(Request $request, Client $client): JsonResponse
    {
        $this->authorize('update', $client);

        $validated = $request->validate([
            'client_name_en' => 'sometimes|required|string|max:255',
            'client_name_ar' => 'sometimes|required|string|max:255',
            'status' => 'nullable|string',
            'client_code' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
        ]);

        if (isset($validated['start_date'])) {
            $validated['client_start'] = $validated['start_date'];
        }
        $validated['updated_by'] = auth()->id();

        $client->update($validated);
        $client->load(['contactLawyer', 'statusRef', 'cashOrProbono']);

        return response()->json([
            'data' => $client,
            'message' => 'Client updated successfully',
        ]);
    }

    /**
     * Remove the specified client
     */
    public function destroy(Client $client): JsonResponse
    {
        $this->authorize('delete', $client);

        $client->delete();

        return response()->json([
            'message' => 'Client deleted successfully',
        ]);
    }
}

