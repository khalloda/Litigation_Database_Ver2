<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientDocument;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $this->authorize('viewAny', ClientDocument::class);

            $query = ClientDocument::with(['client', 'case']);

            if ($request->has('client_id')) {
                $query->where('client_id', $request->client_id);
            }

            if ($request->has('matter_id')) {
                $query->where('matter_id', $request->matter_id);
            }

            if ($request->has('document_type')) {
                $query->where('document_type', 'like', "%{$request->document_type}%");
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('document_name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $documents = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 20));

            return response()->json($documents);
        } catch (\Exception $e) {
            \Log::error('DocumentController@index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Failed to fetch documents',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', ClientDocument::class);

        $validated = $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'client_id' => 'nullable|exists:clients,id',
            'matter_id' => 'nullable|exists:cases,id',
            'document_name' => 'nullable|string|max:255',
            'document_type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('documents', $filename, 'public');

        $document = ClientDocument::create([
            'client_id' => $validated['client_id'] ?? null,
            'matter_id' => $validated['matter_id'] ?? null,
            'document_name' => $validated['document_name'] ?? $file->getClientOriginalName(),
            'document_type' => $validated['document_type'] ?? null,
            'description' => $validated['description'] ?? null,
            'document_storage_type' => 'local',
            'document_path' => $path,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $document->load(['client', 'case']);

        return response()->json([
            'data' => $document,
            'message' => 'Document uploaded successfully',
        ], 201);
    }

    public function show(ClientDocument $document): JsonResponse
    {
        $this->authorize('view', $document);
        $document->load(['client', 'case']);
        return response()->json(['data' => $document]);
    }

    public function update(Request $request, ClientDocument $document): JsonResponse
    {
        $this->authorize('update', $document);

        $validated = $request->validate([
            'document_name' => 'sometimes|required|string|max:255',
            'document_type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'nullable|exists:clients,id',
            'matter_id' => 'nullable|exists:cases,id',
        ]);

        $document->update($validated + ['updated_by' => auth()->id()]);
        $document->load(['client', 'case']);

        return response()->json([
            'data' => $document,
            'message' => 'Document updated successfully',
        ]);
    }

    public function destroy(ClientDocument $document): JsonResponse
    {
        $this->authorize('delete', $document);

        if ($document->document_path && Storage::disk('public')->exists($document->document_path)) {
            Storage::disk('public')->delete($document->document_path);
        }

        $document->delete();

        return response()->json(['message' => 'Document deleted successfully']);
    }
}

