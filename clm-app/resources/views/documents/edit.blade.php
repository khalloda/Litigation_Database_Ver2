@extends('layouts.app')

@section('title', 'Edit Document')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">✏️ Edit Document Metadata</h1>
                <div>
                    <a href="{{ route('documents.show', $document) }}" class="btn btn-outline-secondary">
                        ← Back to Document
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">📄 Document Information</h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('documents.update', $document) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Document Name (Read-only) --}}
                        <div class="mb-3">
                            <label for="document_name" class="form-label">Document Name</label>
                            <input type="text" class="form-control" id="document_name" 
                                   value="{{ $document->document_name }}" readonly>
                            <div class="form-text">
                                Document name cannot be changed after upload.
                            </div>
                        </div>

                        {{-- Client Selection --}}
                        <div class="mb-3">
                            <label for="client_id" class="form-label">Client <span class="text-danger">*</span></label>
                            <select class="form-select @error('client_id') is-invalid @enderror" 
                                    id="client_id" name="client_id" required>
                                <option value="">Select a client...</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" 
                                            {{ old('client_id', $document->client_id) == $client->id ? 'selected' : '' }}>
                                        {{ $client->client_name_ar ?? $client->client_name_en }}
                                    </option>
                                @endforeach
                            </select>
                            @error('client_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Matter Selection --}}
                        <div class="mb-3">
                            <label for="matter_id" class="form-label">Matter (Optional)</label>
                            <select class="form-select @error('matter_id') is-invalid @enderror" 
                                    id="matter_id" name="matter_id">
                                <option value="">Select a matter...</option>
                                @foreach($cases as $case)
                                    <option value="{{ $case->id }}" 
                                            {{ old('matter_id', $document->matter_id) == $case->id ? 'selected' : '' }}>
                                        {{ $case->matter_name_ar ?? $case->matter_name_en }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                Select a specific matter/case if this document is related to one.
                            </div>
                            @error('matter_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Document Type --}}
                        <div class="mb-3">
                            <label for="document_type" class="form-label">Document Type <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('document_type') is-invalid @enderror" 
                                   id="document_type" name="document_type" 
                                   value="{{ old('document_type', $document->document_type) }}" 
                                   placeholder="e.g., Contract, Invoice, Court Filing, etc." required>
                            @error('document_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div class="mb-4">
                            <label for="description" class="form-label">Description (Optional)</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Brief description of the document...">{{ old('description', $document->description) }}</textarea>
                            <div class="form-text">
                                Maximum 1000 characters
                            </div>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Submit Buttons --}}
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('documents.show', $document) }}" class="btn btn-outline-secondary me-md-2">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                💾 Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Document Information Summary --}}
    <div class="row justify-content-center mt-4">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">ℹ️ Document Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>📊 File Information</h6>
                            <ul class="list-unstyled">
                                <li><strong>Size:</strong> {{ number_format($document->file_size / 1024, 1) }} KB</li>
                                <li><strong>Type:</strong> {{ $document->mime_type }}</li>
                                <li><strong>Uploaded:</strong> {{ $document->created_at->format('M d, Y H:i') }}</li>
                                <li><strong>Last Updated:</strong> {{ $document->updated_at->format('M d, Y H:i') }}</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>👤 Upload Information</h6>
                            <ul class="list-unstyled">
                                <li><strong>Uploaded By:</strong> 
                                    @if($document->createdBy)
                                        {{ $document->createdBy->name }}
                                    @else
                                        System
                                    @endif
                                </li>
                                <li><strong>Current Client:</strong> {{ $document->client->client_name_ar ?? $document->client->client_name_en }}</li>
                                <li><strong>Current Matter:</strong> 
                                    @if($document->case)
                                        {{ $document->case->matter_name_ar ?? $document->case->matter_name_en }}
                                    @else
                                        None assigned
                                    @endif
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const clientSelect = document.getElementById('client_id');
    const matterSelect = document.getElementById('matter_id');

    clientSelect.addEventListener('change', function() {
        const clientId = this.value;
        
        // Clear matter options
        matterSelect.innerHTML = '<option value="">Select a matter...</option>';
        
        if (clientId) {
            // Fetch matters for selected client
            fetch(`/documents/client-cases?client_id=${clientId}`)
                .then(response => response.json())
                .then(cases => {
                    cases.forEach(caseItem => {
                        const option = document.createElement('option');
                        option.value = caseItem.id;
                        option.textContent = caseItem.matter_name_ar || caseItem.matter_name_en;
                        matterSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error fetching cases:', error);
                });
        }
    });

    // Set initial matters if client is already selected
    if (clientSelect.value) {
        clientSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection
