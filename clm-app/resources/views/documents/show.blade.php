@extends('layouts.app')

@section('title', 'Document Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">📄 Document Details</h1>
                <div>
                    <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
                        ← Back to Documents
                    </a>
                    <a href="{{ route('documents.download', $document) }}" class="btn btn-success ms-2">
                        📥 Download
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            {{-- Document Information --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">📋 Document Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Document Name:</strong></td>
                                    <td>{{ $document->document_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Document Type:</strong></td>
                                    <td><span class="badge bg-info">{{ $document->document_type }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>File Size:</strong></td>
                                    <td>{{ number_format($document->file_size / 1024, 1) }} KB</td>
                                </tr>
                                <tr>
                                    <td><strong>MIME Type:</strong></td>
                                    <td><code>{{ $document->mime_type }}</code></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Upload Date:</strong></td>
                                    <td>{{ $document->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>{{ $document->updated_at->format('M d, Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Uploaded By:</strong></td>
                                    <td>
                                        @if($document->createdBy)
                                            {{ $document->createdBy->name }}
                                        @else
                                            <span class="text-muted">System</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>File Path:</strong></td>
                                    <td><code>{{ $document->file_path }}</code></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($document->description)
                    <div class="mt-3">
                        <strong>Description:</strong>
                        <p class="mt-2">{{ $document->description }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Related Information --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">🔗 Related Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>👤 Client</h6>
                            <p>
                                <strong>{{ $document->client->client_name_ar ?? $document->client->client_name_en }}</strong><br>
                                <small class="text-muted">ID: {{ $document->client->id }}</small>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>⚖️ Matter/Case</h6>
                            @if($document->case)
                                <p>
                                    <strong>{{ $document->case->matter_name_ar ?? $document->case->matter_name_en }}</strong><br>
                                    <small class="text-muted">ID: {{ $document->case->id }} | Status: {{ $document->case->matter_status }}</small>
                                </p>
                            @else
                                <p class="text-muted">No matter assigned to this document.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- File Preview (if supported) --}}
            @if(in_array($document->mime_type, ['application/pdf', 'image/jpeg', 'image/png', 'image/gif']))
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">👁️ Document Preview</h5>
                </div>
                <div class="card-body">
                    <div id="preview-container">
                        @if($document->mime_type === 'application/pdf')
                            <iframe src="{{ route('documents.signed-url', $document) }}" 
                                    width="100%" height="600" style="border: none;">
                                Your browser does not support PDF preview.
                            </iframe>
                        @elseif(str_starts_with($document->mime_type, 'image/'))
                            <img src="{{ route('documents.signed-url', $document) }}" 
                                 alt="{{ $document->document_name }}" 
                                 class="img-fluid" 
                                 style="max-height: 600px;">
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-4">
            {{-- Actions --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">⚡ Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('documents.download', $document) }}" class="btn btn-success">
                            📥 Download Document
                        </a>
                        
                        @can('documents.edit')
                            <a href="{{ route('documents.edit', $document) }}" class="btn btn-outline-primary">
                                ✏️ Edit Metadata
                            </a>
                        @endcan
                        
                        <button class="btn btn-outline-info" onclick="copyToClipboard('{{ route('documents.download', $document) }}')">
                            📋 Copy Download Link
                        </button>
                        
                        @can('documents.delete')
                            <form action="{{ route('documents.destroy', $document) }}" method="POST" 
                                  onsubmit="return confirm('Are you sure you want to delete this document? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">
                                    🗑️ Delete Document
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>

            {{-- File Information --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">📊 File Details</h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="mb-3">
                            @if($document->mime_type === 'application/pdf')
                                <i class="fas fa-file-pdf fa-3x text-danger"></i>
                            @elseif(str_contains($document->mime_type, 'word'))
                                <i class="fas fa-file-word fa-3x text-primary"></i>
                            @elseif(str_contains($document->mime_type, 'excel'))
                                <i class="fas fa-file-excel fa-3x text-success"></i>
                            @elseif(str_contains($document->mime_type, 'powerpoint'))
                                <i class="fas fa-file-powerpoint fa-3x text-warning"></i>
                            @elseif(str_starts_with($document->mime_type, 'image/'))
                                <i class="fas fa-file-image fa-3x text-info"></i>
                            @else
                                <i class="fas fa-file fa-3x text-secondary"></i>
                            @endif
                        </div>
                        
                        <h6>{{ $document->document_name }}</h6>
                        <p class="text-muted">{{ number_format($document->file_size / 1024, 1) }} KB</p>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <strong>Format:</strong> {{ strtoupper(pathinfo($document->document_name, PATHINFO_EXTENSION)) }}<br>
                                <strong>Type:</strong> {{ $document->mime_type }}<br>
                                <strong>Uploaded:</strong> {{ $document->created_at->diffForHumans() }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Security Information --}}
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">🔒 Security</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <small>
                            <strong>Secure Storage:</strong> This document is stored in a secure directory with restricted access.<br><br>
                            <strong>Access Control:</strong> Only authorized users can view or download this document.<br><br>
                            <strong>Audit Trail:</strong> All access to this document is logged and tracked.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Create a temporary alert to show success
        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible fade show position-fixed';
        alert.style.top = '20px';
        alert.style.right = '20px';
        alert.style.zIndex = '9999';
        alert.innerHTML = `
            <strong>Success!</strong> Download link copied to clipboard.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alert);
        
        // Remove alert after 3 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 3000);
    }).catch(function(err) {
        alert('Failed to copy link to clipboard');
    });
}
</script>
@endsection
