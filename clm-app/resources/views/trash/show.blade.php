@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Bundle Details</h4>
                        <a href="{{ route('trash.index') }}" class="btn btn-sm btn-secondary">← Back to List</a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- Bundle Information --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Bundle Information</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th width="150">ID:</th>
                                    <td><code>{{ $bundle->id }}</code></td>
                                </tr>
                                <tr>
                                    <th>Type:</th>
                                    <td><span class="badge bg-primary">{{ $bundle->root_type }}</span></td>
                                </tr>
                                <tr>
                                    <th>Label:</th>
                                    <td><strong>{{ $bundle->root_label }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Total Items:</th>
                                    <td>{{ $bundle->cascade_count }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge bg-{{ $bundle->status === 'trashed' ? 'warning' : ($bundle->status === 'restored' ? 'success' : 'secondary') }}">
                                            {{ ucfirst($bundle->status) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h5>Deletion Metadata</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th width="150">Deleted By:</th>
                                    <td>{{ $bundle->deletedBy->name ?? 'Unknown' }}</td>
                                </tr>
                                <tr>
                                    <th>Deleted At:</th>
                                    <td>{{ $bundle->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Reason:</th>
                                    <td>{{ $bundle->reason ?? 'Not specified' }}</td>
                                </tr>
                                @if($bundle->restored_at)
                                    <tr>
                                        <th>Restored At:</th>
                                        <td>{{ $bundle->restored_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                @endif
                                @if($bundle->ttl_at)
                                    <tr>
                                        <th>Auto-Purge:</th>
                                        <td>
                                            {{ $bundle->ttl_at->format('Y-m-d') }}
                                            @if($bundle->isExpired())
                                                <span class="badge bg-danger">Expired</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    {{-- Items Breakdown --}}
                    <h5>Bundle Contents</h5>
                    <div class="accordion mb-4" id="itemsAccordion">
                        @foreach($itemsByType as $modelType => $items)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading{{ $loop->index }}">
                                    <button class="accordion-button {{ $loop->index === 0 ? '' : 'collapsed' }}" 
                                            type="button" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#collapse{{ $loop->index }}">
                                        {{ $modelType }} <span class="badge bg-secondary ms-2">{{ $items->count() }}</span>
                                    </button>
                                </h2>
                                <div id="collapse{{ $loop->index }}" 
                                     class="accordion-collapse collapse {{ $loop->index === 0 ? 'show' : '' }}" 
                                     data-bs-parent="#itemsAccordion">
                                    <div class="accordion-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Model ID</th>
                                                        <th>Data Preview</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($items as $item)
                                                        <tr>
                                                            <td><code>{{ $item->model_id ?? 'N/A' }}</code></td>
                                                            <td>
                                                                <small class="text-muted">
                                                                    {{ json_encode(array_slice($item->payload_json, 0, 3)) }}
                                                                </small>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex gap-2">
                        @can('restore', $bundle)
                            @if($bundle->isTrashed())
                                <button type="button" 
                                        class="btn btn-warning" 
                                        id="dryRunBtn"
                                        data-bundle-id="{{ $bundle->id }}">
                                    🔍 Dry Run Restore
                                </button>
                                
                                <form method="POST" action="{{ route('trash.restore', $bundle->id) }}" style="display:inline;">
                                    @csrf
                                    <select name="conflict_strategy" class="form-select form-select-sm d-inline-block w-auto">
                                        <option value="skip">Skip Conflicts</option>
                                        <option value="overwrite">Overwrite</option>
                                        <option value="new_copy">New Copy</option>
                                    </select>
                                    <button type="submit" 
                                            class="btn btn-success" 
                                            onclick="return confirm('Restore this bundle?')">
                                        ♻️ Restore Bundle
                                    </button>
                                </form>
                            @endif
                        @endcan

                        @can('purge', $bundle)
                            <form method="POST" action="{{ route('trash.purge', $bundle->id) }}" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="btn btn-danger" 
                                        onclick="return confirm('Permanently purge this bundle? This cannot be undone!')">
                                    🔥 Purge Bundle
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Dry Run Modal --}}
<div class="modal fade" id="dryRunModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Dry Run Restore Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="dryRunResults">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('dryRunBtn')?.addEventListener('click', function() {
    const bundleId = this.dataset.bundleId;
    const modal = new bootstrap.Modal(document.getElementById('dryRunModal'));
    modal.show();
    
    fetch(`/trash/${bundleId}/dry-run`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ conflict_strategy: 'skip' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const report = data.report;
            document.getElementById('dryRunResults').innerHTML = `
                <div class="alert alert-info">
                    <h6>Simulation Complete</h6>
                    <p>Root: ${report.root_type} - ${report.root_label}</p>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-success">${report.restored.length}</h3>
                                <p class="mb-0">Would Restore</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-warning">${report.skipped.length}</h3>
                                <p class="mb-0">Would Skip</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-danger">${report.errors.length}</h3>
                                <p class="mb-0">Errors</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        } else {
            document.getElementById('dryRunResults').innerHTML = `
                <div class="alert alert-danger">Error: ${data.error}</div>
            `;
        }
    })
    .catch(error => {
        document.getElementById('dryRunResults').innerHTML = `
            <div class="alert alert-danger">Request failed: ${error.message}</div>
        `;
    });
});
</script>
@endpush
@endsection

