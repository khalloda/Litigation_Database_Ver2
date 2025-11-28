@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{{ __('app.import_sessions') }}</h2>
        <a href="{{ route('import.upload') }}" class="btn btn-primary">
            <i class="fas fa-upload"></i> {{ __('app.new_import') }}
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('import.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">{{ __('app.status') }}</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">{{ __('app.all_statuses') }}</option>
                        <option value="uploaded" {{ request('status') == 'uploaded' ? 'selected' : '' }}>{{ __('app.uploaded') }}</option>
                        <option value="mapped" {{ request('status') == 'mapped' ? 'selected' : '' }}>{{ __('app.mapped') }}</option>
                        <option value="validated" {{ request('status') == 'validated' ? 'selected' : '' }}>{{ __('app.validated') }}</option>
                        <option value="importing" {{ request('status') == 'importing' ? 'selected' : '' }}>{{ __('app.importing') }}</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('app.completed') }}</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>{{ __('app.failed') }}</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('app.cancelled') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="table" class="form-label">{{ __('app.table') }}</label>
                    <select name="table" id="table" class="form-select">
                        <option value="">{{ __('app.all_tables') }}</option>
                        @foreach($enabledTables as $table)
                            <option value="{{ $table }}" {{ request('table') == $table ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $table)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter"></i> {{ __('app.filter') }}
                    </button>
                    <a href="{{ route('import.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i> {{ __('app.reset') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Sessions table --}}
    <div class="card">
        <div class="card-body">
            @if($sessions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('app.session_id') }}</th>
                                <th>{{ __('app.file') }}</th>
                                <th>{{ __('app.table') }}</th>
                                <th>{{ __('app.status') }}</th>
                                <th>{{ __('app.progress') }}</th>
                                <th>{{ __('app.user') }}</th>
                                <th>{{ __('app.created_at') }}</th>
                                <th class="text-end">{{ __('app.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sessions as $session)
                            <tr>
                                <td>
                                    <code class="text-muted">{{ Str::limit($session->session_id, 8, '') }}</code>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ Str::limit($session->original_filename, 30) }}</strong>
                                    </div>
                                    <small class="text-muted">
                                        {{ $session->file_size_human }} • {{ strtoupper($session->file_type) }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $session->table_name }}</span>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'uploaded' => 'secondary',
                                            'mapped' => 'info',
                                            'validated' => 'warning',
                                            'importing' => 'primary',
                                            'completed' => 'success',
                                            'failed' => 'danger',
                                            'cancelled' => 'dark'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$session->status] ?? 'secondary' }}">
                                        {{ __(ucfirst($session->status)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($session->isCompleted())
                                        <div class="text-success">
                                            <i class="fas fa-check-circle"></i>
                                            {{ $session->imported_count }} / {{ $session->total_rows }}
                                        </div>
                                        @if($session->failed_count > 0)
                                            <small class="text-danger">{{ $session->failed_count }} {{ __('app.failed') }}</small>
                                        @endif
                                    @elseif($session->isFailed())
                                        <div class="text-danger">
                                            <i class="fas fa-exclamation-circle"></i>
                                            {{ __('app.failed') }}
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $session->user->name }}</div>
                                    <small class="text-muted">{{ $session->user->email }}</small>
                                </td>
                                <td>{{ $session->created_at->format('Y-m-d H:i') }}</td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        @can('view', $session)
                                            <a href="{{ route('import.show', $session) }}" class="btn btn-sm btn-outline-primary" title="{{ __('app.view') }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endcan
                                        
                                        @can('cancel', $session)
                                            @if($session->isInProgress())
                                                <form action="{{ route('import.cancel', $session) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-sm btn-outline-warning" 
                                                            onclick="return confirm('{{ __('app.confirm_cancel_import') }}')"
                                                            title="{{ __('app.cancel') }}">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan

                                        @can('delete', $session)
                                            <form action="{{ route('import.destroy', $session) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('{{ __('app.confirm_delete') }}')"
                                                        title="{{ __('app.delete') }}">
                                                    <i class="fas fa-trash"></i>
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

                <div class="mt-3">
                    {{ $sessions->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">{{ __('app.no_import_sessions_found') }}</p>
                    <a href="{{ route('import.upload') }}" class="btn btn-primary">
                        <i class="fas fa-upload"></i> {{ __('app.start_new_import') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

