@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">📋 Audit Logs</h1>
                <div>
                    <a href="{{ route('audit-logs.export', request()->query()) }}" class="btn btn-sm btn-outline-success me-2">
                        📄 Export CSV
                    </a>
                    <a href="#" class="btn btn-sm btn-outline-primary" onclick="window.print(); return false;">
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
                    <h5 class="mb-0">🔍 Filters</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('audit-logs.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="Search descriptions...">
                            </div>
                            <div class="col-md-2">
                                <label for="subject_type" class="form-label">Entity Type</label>
                                <select class="form-select" id="subject_type" name="subject_type">
                                    <option value="">All Types</option>
                                    @foreach($subjectTypes as $type)
                                        <option value="{{ $type['value'] }}" {{ request('subject_type') == $type['value'] ? 'selected' : '' }}>
                                            {{ $type['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="event" class="form-label">Action</label>
                                <select class="form-select" id="event" name="event">
                                    <option value="">All Actions</option>
                                    @foreach($events as $event)
                                        <option value="{{ $event }}" {{ request('event') == $event ? 'selected' : '' }}>
                                            {{ ucfirst($event) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="causer_id" class="form-label">User</label>
                                <select class="form-select" id="causer_id" name="causer_id">
                                    <option value="">All Users</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user['id'] }}" {{ request('causer_id') == $user['id'] ? 'selected' : '' }}>
                                            {{ $user['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label for="date_from" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" 
                                       value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-1">
                                <label for="date_to" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" 
                                       value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Filter</button>
                                <a href="{{ route('audit-logs.index') }}" class="btn btn-outline-secondary">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Audit Logs Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">📊 Activity Log ({{ $activities->total() }} total records)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Entity</th>
                                    <th>Description</th>
                                    <th>Changes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activities as $activity)
                                <tr>
                                    <td>
                                        <small class="text-muted">
                                            {{ $activity->created_at->format('M d, Y') }}<br>
                                            {{ $activity->created_at->format('H:i:s') }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($activity->causer)
                                            <div>
                                                <strong>{{ $activity->causer->name }}</strong><br>
                                                <small class="text-muted">{{ $activity->causer->email }}</small>
                                            </div>
                                        @else
                                            <span class="badge bg-secondary">System</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($activity->event === 'created')
                                            <span class="badge bg-success">Created</span>
                                        @elseif($activity->event === 'updated')
                                            <span class="badge bg-warning">Updated</span>
                                        @elseif($activity->event === 'deleted')
                                            <span class="badge bg-danger">Deleted</span>
                                        @else
                                            <span class="badge bg-info">{{ ucfirst($activity->event) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($activity->subject_type)
                                            <div>
                                                <strong>{{ class_basename($activity->subject_type) }}</strong><br>
                                                <small class="text-muted">ID: {{ $activity->subject_id }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="max-width: 200px; word-wrap: break-word;">
                                            {{ $activity->description }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($activity->properties && isset($activity->properties['attributes']))
                                            @php
                                                $changes = $activity->properties['attributes'];
                                                $count = count($changes);
                                            @endphp
                                            @if($count > 0)
                                                <button class="btn btn-sm btn-outline-info" type="button" 
                                                        data-bs-toggle="collapse" 
                                                        data-bs-target="#changes-{{ $activity->id }}" 
                                                        aria-expanded="false">
                                                    {{ $count }} field{{ $count > 1 ? 's' : '' }}
                                                </button>
                                                <div class="collapse mt-2" id="changes-{{ $activity->id }}">
                                                    <div class="card card-body p-2">
                                                        @foreach($changes as $field => $value)
                                                            <small>
                                                                <strong>{{ $field }}:</strong> 
                                                                <code>{{ is_array($value) ? json_encode($value) : $value }}</code>
                                                            </small><br>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">No changes</span>
                                            @endif
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('audit-logs.show', $activity) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No audit logs found matching your criteria.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($activities->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $activities->withQueryString()->links() }}
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
                    <h5 class="mb-0">📈 Activity Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <h4 class="text-success">{{ $activities->where('event', 'created')->count() }}</h4>
                            <small class="text-muted">Created</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-warning">{{ $activities->where('event', 'updated')->count() }}</h4>
                            <small class="text-muted">Updated</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-danger">{{ $activities->where('event', 'deleted')->count() }}</h4>
                            <small class="text-muted">Deleted</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-info">{{ $activities->count() }}</h4>
                            <small class="text-muted">Total Shown</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .card-header, .collapse, .pagination {
        display: none !important;
    }
}
</style>
@endsection
