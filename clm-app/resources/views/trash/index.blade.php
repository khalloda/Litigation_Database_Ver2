@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">🗑️ Recycle Bin</h4>
                        <div>
                            <span class="badge bg-secondary">{{ $stats['total'] }} Total</span>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- Statistics --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">By Type</div>
                                <div class="card-body">
                                    @foreach($stats['by_type'] as $type => $count)
                                        <span class="badge bg-info me-2">{{ $type }}: {{ $count }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">By Status</div>
                                <div class="card-body">
                                    @foreach($stats['by_status'] as $status => $count)
                                        <span class="badge bg-{{ $status === 'trashed' ? 'warning' : ($status === 'restored' ? 'success' : 'secondary') }} me-2">
                                            {{ ucfirst($status) }}: {{ $count }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Filters --}}
                    <form method="GET" class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <option value="">All Types</option>
                                <option value="Client" {{ request('type') === 'Client' ? 'selected' : '' }}>Client</option>
                                <option value="CaseModel" {{ request('type') === 'CaseModel' ? 'selected' : '' }}>Case</option>
                                <option value="ClientDocument" {{ request('type') === 'ClientDocument' ? 'selected' : '' }}>Document</option>
                                <option value="Hearing" {{ request('type') === 'Hearing' ? 'selected' : '' }}>Hearing</option>
                                <option value="AdminTask" {{ request('type') === 'AdminTask' ? 'selected' : '' }}>Admin Task</option>
                                <option value="Lawyer" {{ request('type') === 'Lawyer' ? 'selected' : '' }}>Lawyer</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="trashed" {{ request('status') === 'trashed' ? 'selected' : '' }}>Trashed</option>
                                <option value="restored" {{ request('status') === 'restored' ? 'selected' : '' }}>Restored</option>
                                <option value="purged" {{ request('status') === 'purged' ? 'selected' : '' }}>Purged</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('trash.index') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </form>

                    {{-- Bundles Table --}}
                    @if($bundles->isEmpty())
                        <div class="alert alert-info">
                            No deletion bundles found.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Label</th>
                                        <th>Items</th>
                                        <th>Deleted By</th>
                                        <th>Deleted At</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bundles as $bundle)
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary">{{ $bundle->root_type }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('trash.show', $bundle->id) }}">
                                                    {{ $bundle->root_label }}
                                                </a>
                                                @if($bundle->reason)
                                                    <br><small class="text-muted">{{ $bundle->reason }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $bundle->cascade_count }}</td>
                                            <td>{{ $bundle->deletedBy->name ?? 'Unknown' }}</td>
                                            <td>{{ $bundle->created_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $bundle->status === 'trashed' ? 'warning' : ($bundle->status === 'restored' ? 'success' : 'secondary') }}">
                                                    {{ ucfirst($bundle->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('trash.show', $bundle->id) }}" class="btn btn-info" title="View">
                                                        View
                                                    </a>
                                                    
                                                    @can('restore', $bundle)
                                                        @if($bundle->isTrashed())
                                                            <form method="POST" action="{{ route('trash.restore', $bundle->id) }}" style="display:inline;">
                                                                @csrf
                                                                <button type="submit" class="btn btn-success" 
                                                                        onclick="return confirm('Restore this bundle?')" 
                                                                        title="Restore">
                                                                    Restore
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endcan
                                                    
                                                    @can('purge', $bundle)
                                                        <form method="POST" action="{{ route('trash.purge', $bundle->id) }}" style="display:inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger" 
                                                                    onclick="return confirm('Permanently purge this bundle?')" 
                                                                    title="Purge">
                                                                Purge
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="mt-3">
                            {{ $bundles->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

