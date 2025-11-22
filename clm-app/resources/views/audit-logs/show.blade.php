@extends('layouts.app')

@section('title', 'Audit Log Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">🔍 Audit Log Details</h1>
                <div>
                    <a href="{{ route('audit-logs.index') }}" class="btn btn-outline-secondary">
                        ← Back to Logs
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            {{-- Activity Details --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">📋 Activity Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Date/Time:</strong></td>
                                    <td>
                                        {{ $activity->created_at->format('l, F d, Y') }}<br>
                                        <small class="text-muted">{{ $activity->created_at->format('H:i:s') }} ({{ $activity->created_at->diffForHumans() }})</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>User:</strong></td>
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
                                </tr>
                                <tr>
                                    <td><strong>Action:</strong></td>
                                    <td>
                                        @if($activity->event === 'created')
                                            <span class="badge bg-success fs-6">Created</span>
                                        @elseif($activity->event === 'updated')
                                            <span class="badge bg-warning fs-6">Updated</span>
                                        @elseif($activity->event === 'deleted')
                                            <span class="badge bg-danger fs-6">Deleted</span>
                                        @else
                                            <span class="badge bg-info fs-6">{{ ucfirst($activity->event) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Entity Type:</strong></td>
                                    <td>
                                        @if($activity->subject_type)
                                            <strong>{{ class_basename($activity->subject_type) }}</strong>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Entity ID:</strong></td>
                                    <td>
                                        @if($activity->subject_id)
                                            <code>{{ $activity->subject_id }}</code>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Description:</strong></td>
                                    <td>{{ $activity->description }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Changes Details --}}
            @if($activity->properties && isset($activity->properties['attributes']))
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">📝 Changes Made</h5>
                </div>
                <div class="card-body">
                    @php
                        $changes = $activity->properties['attributes'];
                    @endphp
                    @if(count($changes) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Field</th>
                                        <th>Value</th>
                                        <th>Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($changes as $field => $value)
                                    <tr>
                                        <td>
                                            <strong>{{ $field }}</strong>
                                        </td>
                                        <td>
                                            @if(is_array($value) || is_object($value))
                                                <pre class="mb-0"><code>{{ json_encode($value, JSON_PRETTY_PRINT) }}</code></pre>
                                            @elseif(is_bool($value))
                                                <span class="badge {{ $value ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $value ? 'True' : 'False' }}
                                                </span>
                                            @elseif(is_null($value))
                                                <span class="text-muted">NULL</span>
                                            @else
                                                <code>{{ $value }}</code>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ gettype($value) }}</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            No specific field changes recorded.
                        </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Raw Properties --}}
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">🔧 Raw Activity Data</h5>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3 rounded"><code>{{ json_encode($activity->toArray(), JSON_PRETTY_PRINT) }}</code></pre>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            {{-- Subject Information --}}
            @if($activity->subject)
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">🎯 Related Entity</h5>
                </div>
                <div class="card-body">
                    <h6>{{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}</h6>
                    
                    @if($activity->subject_type === 'App\Models\Client')
                        <p><strong>Name:</strong> {{ $activity->subject->client_name_ar ?? $activity->subject->client_name_en ?? 'N/A' }}</p>
                        <p><strong>Status:</strong> {{ $activity->subject->status ?? 'N/A' }}</p>
                    @elseif($activity->subject_type === 'App\Models\CaseModel')
                        <p><strong>Matter:</strong> {{ $activity->subject->matter_name_ar ?? $activity->subject->matter_name_en ?? 'N/A' }}</p>
                        <p><strong>Status:</strong> {{ $activity->subject->matter_status ?? 'N/A' }}</p>
                    @elseif($activity->subject_type === 'App\Models\AdminTask')
                        <p><strong>Task:</strong> {{ $activity->subject->required_work ?? 'N/A' }}</p>
                        <p><strong>Status:</strong> {{ $activity->subject->status ?? 'N/A' }}</p>
                    @endif
                    
                    <small class="text-muted">
                        Created: {{ $activity->subject->created_at->format('M d, Y H:i') }}<br>
                        Updated: {{ $activity->subject->updated_at->format('M d, Y H:i') }}
                    </small>
                </div>
            </div>
            @endif

            {{-- Activity Stats --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">📊 Quick Stats</h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <h4 class="text-primary">{{ $activity->id }}</h4>
                        <small class="text-muted">Activity ID</small>
                    </div>
                    
                    @if($activity->properties && isset($activity->properties['attributes']))
                    <div class="text-center mt-3">
                        <h4 class="text-info">{{ count($activity->properties['attributes']) }}</h4>
                        <small class="text-muted">Fields Changed</small>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">⚡ Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('audit-logs.index') }}" class="btn btn-outline-secondary">
                            ← Back to All Logs
                        </a>
                        <button class="btn btn-outline-primary" onclick="window.print(); return false;">
                            🖨️ Print This Log
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .card-header, .d-grid {
        display: none !important;
    }
}
</style>
@endsection
