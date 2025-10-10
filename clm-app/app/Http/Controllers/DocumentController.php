<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentUploadRequest;
use App\Models\ClientDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    /**
     * Display a listing of documents.
     */
    public function index(Request $request)
    {
        $query = ClientDocument::with(['client', 'case'])
            ->orderBy('created_at', 'desc');

        // Filter by client
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Filter by matter
        if ($request->filled('matter_id')) {
            $query->where('matter_id', $request->matter_id);
        }

        // Filter by document type
        if ($request->filled('document_type')) {
            $query->where('document_type', 'like', "%{$request->document_type}%");
        }

        // Search in document name or description
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('document_name', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        $documents = $query->paginate(20);

        // Get filter options
        $clients = \App\Models\Client::select('id', 'client_name_ar', 'client_name_en')
            ->orderBy('client_name_ar')
            ->get();

        $cases = \App\Models\CaseModel::select('id', 'matter_name_ar', 'matter_name_en')
            ->orderBy('matter_name_ar')
            ->get();

        $documentTypes = ClientDocument::select('document_type')
            ->distinct()
            ->whereNotNull('document_type')
            ->orderBy('document_type')
            ->pluck('document_type');

        return view('documents.index', compact('documents', 'clients', 'cases', 'documentTypes'));
    }

    /**
     * Show the form for creating a new document.
     */
    public function create()
    {
        $clients = \App\Models\Client::select('id', 'client_name_ar', 'client_name_en')
            ->orderBy('client_name_ar')
            ->get();

        $cases = \App\Models\CaseModel::select('id', 'matter_name_ar', 'matter_name_en', 'client_id')
            ->orderBy('matter_name_ar')
            ->get();

        return view('documents.create', compact('clients', 'cases'));
    }

    /**
     * Store a newly uploaded document.
     */
    public function store(DocumentUploadRequest $request)
    {
        try {
            $file = $request->file('document');

            // Generate secure filename
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $secureName = Str::uuid() . '.' . $extension;

            // Store file in secure directory
            $filePath = $file->storeAs('documents', $secureName, 'secure');

            // Create document record
            $document = ClientDocument::create([
                'client_id' => $request->client_id,
                'matter_id' => $request->matter_id,
                'document_name' => $originalName,
                'document_type' => $request->document_type,
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'description' => $request->description,
                'deposit_date' => now(),
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            return redirect()->route('documents.show', $document)
                ->with('success', 'Document uploaded successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to upload document: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified document.
     */
    public function show(ClientDocument $document)
    {
        $document->load(['client', 'case']);

        return view('documents.show', compact('document'));
    }

    /**
     * Download the specified document.
     */
    public function download(ClientDocument $document)
    {
        if (!Storage::disk('secure')->exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('secure')->download(
            $document->file_path,
            $document->document_name
        );
    }

    /**
     * Generate a signed URL for secure document access.
     */
    public function signedUrl(ClientDocument $document)
    {
        if (!Storage::disk('secure')->exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        // Local disk doesn't support temporaryUrl. Use a temporary signed route
        // that streams the file inline from the secure disk.
        $url = URL::temporarySignedRoute(
            'documents.inline',
            now()->addHour(),
            ['document' => $document->id]
        );

        // Return a redirect so iframes/images can use this endpoint directly as src
        return redirect($url);
    }

    /**
     * Stream the file inline (used by signed preview URLs).
     */
    public function inline(ClientDocument $document)
    {
        if (!Storage::disk('secure')->exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        $absolutePath = Storage::disk('secure')->path($document->file_path);
        return response()->file($absolutePath, [
            'Content-Type' => $document->mime_type,
            'Content-Disposition' => 'inline; filename="' . $document->document_name . '"',
        ]);
    }

    /**
     * Show the form for editing the specified document.
     */
    public function edit(ClientDocument $document)
    {
        $clients = \App\Models\Client::select('id', 'client_name_ar', 'client_name_en')
            ->orderBy('client_name_ar')
            ->get();

        $cases = \App\Models\CaseModel::select('id', 'matter_name_ar', 'matter_name_en', 'client_id')
            ->orderBy('matter_name_ar')
            ->get();

        return view('documents.edit', compact('document', 'clients', 'cases'));
    }

    /**
     * Update the specified document metadata.
     */
    public function update(Request $request, ClientDocument $document)
    {
        $request->validate([
            'client_id' => 'required|integer|exists:clients,id',
            'matter_id' => 'nullable|integer|exists:cases,id',
            'document_type' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $document->update([
            'client_id' => $request->client_id,
            'matter_id' => $request->matter_id,
            'document_type' => $request->document_type,
            'description' => $request->description,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('documents.show', $document)
            ->with('success', 'Document updated successfully.');
    }

    /**
     * Remove the specified document.
     */
    public function destroy(ClientDocument $document)
    {
        try {
            // Delete the physical file
            if (Storage::disk('secure')->exists($document->file_path)) {
                Storage::disk('secure')->delete($document->file_path);
            }

            // Delete the database record (will be soft deleted)
            $document->delete();

            return redirect()->route('documents.index')
                ->with('success', 'Document deleted successfully.');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to delete document: ' . $e->getMessage());
        }
    }

    /**
     * Get cases for a specific client (AJAX).
     */
    public function getClientCases(Request $request)
    {
        $clientId = $request->client_id;

        $cases = \App\Models\CaseModel::where('client_id', $clientId)
            ->select('id', 'matter_name_ar', 'matter_name_en')
            ->orderBy('matter_name_ar')
            ->get();

        return response()->json($cases);
    }
}
