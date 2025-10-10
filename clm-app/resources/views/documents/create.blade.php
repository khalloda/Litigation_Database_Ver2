@extends('layouts.app')

@section('title', 'Upload Document')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">📤 Upload Document</h1>
                <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
                    ← Back to Documents
                </a>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">📄 Document Upload Form</h5>
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

                    <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- File Upload --}}
                        <div class="mb-4">
                            <label for="document" class="form-label">Document File <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('document') is-invalid @enderror"
                                id="document" name="document" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif">
                            <div class="form-text">
                                <strong>Supported formats:</strong> PDF, Word, Excel, PowerPoint, Text, Images<br>
                                <strong>Maximum size:</strong> 10MB
                            </div>
                            @error('document')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Client Selection --}}
                        <div class="mb-3">
                            <label for="client_id" class="form-label">Client <span class="text-danger">*</span></label>
                            <select class="form-select @error('client_id') is-invalid @enderror"
                                id="client_id" name="client_id" required>
                                <option value="">Select a client...</option>
                                @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
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
                                <!-- Options will be populated via AJAX based on client selection -->
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
                                id="document_type" name="document_type" value="{{ old('document_type') }}"
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
                                placeholder="Brief description of the document...">{{ old('description') }}</textarea>
                            <div class="form-text">
                                Maximum 1000 characters
                            </div>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Submit Button --}}
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary me-md-2">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                📤 Upload Document
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Upload Guidelines --}}
    <div class="row justify-content-center mt-4">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">ℹ️ Upload Guidelines</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>📋 File Requirements</h6>
                            <ul class="list-unstyled">
                                <li>✅ Maximum file size: 10MB</li>
                                <li>✅ Supported formats: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT</li>
                                <li>✅ Image formats: JPG, JPEG, PNG, GIF</li>
                                <li>✅ Files are stored securely</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>🔒 Security Features</h6>
                            <ul class="list-unstyled">
                                <li>🔐 Files stored in secure directory</li>
                                <li>🔐 Access controlled by permissions</li>
                                <li>🔐 Signed URLs for temporary access</li>
                                <li>🔐 All uploads are logged and audited</li>
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
                fetch(`/documents/client-cases?client_id=${clientId}`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
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
                        // Show user-friendly error message
                        matterSelect.innerHTML = '<option value="">Error loading cases</option>';
                    });
            }
        });

        // File size validation
        const fileInput = document.getElementById('document');
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const maxSize = 10 * 1024 * 1024; // 10MB
                if (file.size > maxSize) {
                    alert('File size cannot exceed 10MB.');
                    this.value = '';
                }
            }
        });
    });
</script>
@endsection
