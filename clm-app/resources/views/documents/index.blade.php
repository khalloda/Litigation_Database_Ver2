@extends('layouts.app')

@section('title', 'Documents')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">📁 Document Management</h1>
                <div>
                    @can('documents.upload')
                        <a href="{{ route('documents.create') }}" class="btn btn-primary">
                            📤 Upload Document
                        </a>
                    @endcan
                    <a href="#" class="btn btn-outline-primary ms-2" onclick="window.print(); return false;">
                        🖨️ Print
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">🔍 Filter Documents</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('documents.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="Search documents...">
                            </div>
                            <div class="col-md-2">
                                <label for="client_id" class="form-label">Client</label>
                                <select class="form-select" id="client_id" name="client_id">
                                    <option value="">All Clients</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                            {{ $client->client_name_ar ?? $client->client_name_en }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="matter_id" class="form-label">Matter</label>
                                <select class="form-select" id="matter_id" name="matter_id">
                                    <option value="">All Matters</option>
                                    @foreach($cases as $case)
                                        <option value="{{ $case->id }}" {{ request('matter_id') == $case->id ? 'selected' : '' }}>
                                            {{ $case->matter_name_ar ?? $case->matter_name_en }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="document_type" class="form-label">Document Type</label>
                                <select class="form-select" id="document_type" name="document_type">
                                    <option value="">All Types</option>
                                    @foreach($documentTypes as $type)
                                        <option value="{{ $type }}" {{ request('document_type') == $type ? 'selected' : '' }}>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="storage_type" class="form-label">Storage Type</label>
                                <select class="form-select" id="storage_type" name="storage_type">
                                    <option value="">All Types</option>
                                    <option value="physical" {{ request('storage_type') == 'physical' ? 'selected' : '' }}>Physical</option>
                                    <option value="digital" {{ request('storage_type') == 'digital' ? 'selected' : '' }}>Digital</option>
                                    <option value="both" {{ request('storage_type') == 'both' ? 'selected' : '' }}>Both</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Filter</button>
                                <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Documents Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">📊 Documents ({{ $documents->total() }} total)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Document</th>
                                    <th>Storage Type</th>
                                    <th>Client</th>
                                    <th>Matter</th>
                                    <th>Type</th>
                                    <th>Size</th>
                                    <th>Pages</th>
                                    <th>M-Files</th>
                                    <th>Upload Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($documents as $document)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $document->document_name ?? 'No file' }}</strong>
                                            @if($document->description)
                                                <br><small class="text-muted">{{ Str::limit($document->description, 50) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = match($document->document_storage_type) {
                                                'physical' => 'bg-warning',
                                                'digital' => 'bg-success',
                                                'both' => 'bg-info',
                                                default => 'bg-secondary'
                                            };
                                            $typeText = match($document->document_storage_type) {
                                                'physical' => 'Physical',
                                                'digital' => 'Digital',
                                                'both' => 'Both',
                                                default => 'Unknown'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $typeText }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $document->client->client_name_ar ?? $document->client->client_name_en }}</strong>
                                    </td>
                                    <td>
                                        @if($document->case)
                                            <strong>{{ $document->case->matter_name_ar ?? $document->case->matter_name_en }}</strong>
                                        @else
                                            <span class="text-muted">No matter assigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $document->document_type }}</span>
                                    </td>
                                    <td>
                                        @if($document->file_size)
                                            <small class="text-muted">{{ number_format($document->file_size / 1024, 1) }} KB</small>
                                        @else
                                            <small class="text-muted">N/A</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($document->pages_count)
                                            <span class="badge bg-light text-dark">{{ $document->pages_count }}</span>
                                        @else
                                            <small class="text-muted">N/A</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($document->mfiles_uploaded && $document->mfiles_id)
                                            <span class="badge bg-primary" title="M-Files ID: {{ $document->mfiles_id }}">
                                                ✓ M-Files
                                            </span>
                                        @else
                                            <small class="text-muted">-</small>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $document->created_at->format('M d, Y') }}<br>
                                            {{ $document->created_at->format('H:i') }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('documents.show', $document) }}" 
                                               class="btn btn-outline-primary" title="View Details">
                                                👁️
                                            </a>
                                            @if($document->isDigitalDocument() && $document->file_path)
                                                <a href="{{ route('documents.download', $document) }}" 
                                                   class="btn btn-outline-success" title="Download">
                                                    📥
                                                </a>
                                            @endif
                                            @can('documents.delete')
                                                <form action="{{ route('documents.destroy', $document) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this document?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                        🗑️
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        No documents found matching your criteria.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($documents->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $documents->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">📈 Document Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <h4 class="text-primary">{{ $documents->total() }}</h4>
                            <small class="text-muted">Total Documents</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-success">{{ $documents->where('matter_id', '!=', null)->count() }}</h4>
                            <small class="text-muted">With Matter</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-info">{{ number_format($documents->sum('file_size') / 1024 / 1024, 1) }} MB</h4>
                            <small class="text-muted">Total Size</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-warning">{{ $documentTypes->count() }}</h4>
                            <small class="text-muted">Document Types</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .card-header, .pagination {
        display: none !important;
    }
}
</style>
@endsection
