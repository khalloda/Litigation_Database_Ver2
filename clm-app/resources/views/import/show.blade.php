@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{{ __('app.import_session_details') }}</h2>
        <a href="{{ route('import.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> {{ __('app.back_to_list') }}
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- Session info --}}
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('app.session_information') }}</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <th width="40%">{{ __('app.session_id') }}:</th>
                            <td><code>{{ $session->session_id }}</code></td>
                        </tr>
                        <tr>
                            <th>{{ __('app.status') }}:</th>
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
                        </tr>
                        <tr>
                            <th>{{ __('app.table') }}:</th>
                            <td><span class="badge bg-secondary">{{ $session->table_name }}</span></td>
                        </tr>
                        <tr>
                            <th>{{ __('app.file') }}:</th>
                            <td>{{ $session->original_filename }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('app.file_size') }}:</th>
                            <td>{{ $session->file_size_human }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('app.file_type') }}:</th>
                            <td>{{ strtoupper($session->file_type) }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('app.total_rows') }}:</th>
                            <td><strong>{{ number_format($session->total_rows) }}</strong></td>
                        </tr>
                        <tr>
                            <th>{{ __('app.user') }}:</th>
                            <td>
                                {{ $session->user->name }}
                                <br><small class="text-muted">{{ $session->user->email }}</small>
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('app.created_at') }}:</th>
                            <td>{{ $session->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        @if($session->started_at)
                        <tr>
                            <th>{{ __('app.started_at') }}:</th>
                            <td>{{ $session->started_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        @endif
                        @if($session->completed_at)
                        <tr>
                            <th>{{ __('app.completed_at') }}:</th>
                            <td>{{ $session->completed_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('app.duration') }}:</th>
                            <td>{{ $session->duration_seconds }} {{ __('app.seconds') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- Progress/Results --}}
        <div class="col-md-8">
            @if($session->isCompleted())
                {{-- Completed import stats --}}
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-check-circle"></i> {{ __('app.import_completed') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="p-3 border rounded">
                                    <h2 class="text-success mb-0">{{ number_format($session->imported_count) }}</h2>
                                    <p class="text-muted mb-0">{{ __('app.imported') }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded">
                                    <h2 class="text-danger mb-0">{{ number_format($session->failed_count) }}</h2>
                                    <p class="text-muted mb-0">{{ __('app.failed') }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded">
                                    <h2 class="text-warning mb-0">{{ number_format($session->skipped_count) }}</h2>
                                    <p class="text-muted mb-0">{{ __('app.skipped') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="progress" style="height: 30px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: {{ $session->success_rate }}%"
                                     aria-valuenow="{{ $session->success_rate }}" aria-valuemin="0" aria-valuemax="100">
                                    {{ $session->success_rate }}% {{ __('app.success') }}
                                </div>
                            </div>
                        </div>

                        @if(!empty($session->import_errors))
                            <div class="alert alert-danger mt-3">
                                <h6>{{ __('app.errors') }} ({{ count($session->import_errors) }})</h6>
                                <ul class="mb-0">
                                    @foreach(array_slice($session->import_errors, 0, 10) as $error)
                                        <li>{{ __('app.row') }} {{ $error['row'] ?? 'N/A' }}: {{ $error['message'] ?? 'Unknown error' }}</li>
                                    @endforeach
                                    @if(count($session->import_errors) > 10)
                                        <li class="text-muted">{{ __('app.and_more_errors', ['count' => count($session->import_errors) - 10]) }}</li>
                                    @endif
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            @elseif($session->isFailed())
                {{-- Failed import --}}
                <div class="card mb-4">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fas fa-exclamation-circle"></i> {{ __('app.import_failed') }}</h5>
                    </div>
                    <div class="card-body">
                        @if(!empty($session->import_errors))
                            <div class="alert alert-danger">
                                <pre>{{ json_encode($session->import_errors, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                {{-- In progress or validation --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('app.validation_results') }}</h5>
                    </div>
                    <div class="card-body">
                        @if($session->preflight_error_count > 0 || $session->preflight_warning_count > 0)
                            <div class="row text-center mb-3">
                                <div class="col-md-6">
                                    <div class="p-3 border rounded">
                                        <h3 class="text-danger mb-0">{{ number_format($session->preflight_error_count) }}</h3>
                                        <p class="text-muted mb-0">{{ __('app.errors') }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 border rounded">
                                        <h3 class="text-warning mb-0">{{ number_format($session->preflight_warning_count) }}</h3>
                                        <p class="text-muted mb-0">{{ __('app.warnings') }}</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <p class="text-success"><i class="fas fa-check-circle"></i> {{ __('app.no_validation_errors') }}</p>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Backup info --}}
            @if($session->backup_file)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-database"></i> {{ __('app.backup_information') }}</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th width="30%">{{ __('app.backup_file') }}:</th>
                                <td><code>{{ $session->backup_file }}</code></td>
                            </tr>
                            <tr>
                                <th>{{ __('app.backup_size') }}:</th>
                                <td>{{ number_format($session->backup_size / 1024 / 1024, 2) }} MB</td>
                            </tr>
                            <tr>
                                <th>{{ __('app.created_at') }}:</th>
                                <td>{{ $session->backup_created_at ? $session->backup_created_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Column mapping --}}
            @if(!empty($session->column_mapping))
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-exchange-alt"></i> {{ __('app.column_mapping') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __('app.source_column') }}</th>
                                        <th>{{ __('app.target_column') }}</th>
                                        <th>{{ __('app.transforms') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($session->column_mapping as $source => $target)
                                    <tr>
                                        <td><code>{{ $source }}</code></td>
                                        <td><code>{{ $target }}</code></td>
                                        <td>
                                            @if(isset($session->transforms[$source]))
                                                @foreach($session->transforms[$source] as $transform)
                                                    <span class="badge bg-info">{{ $transform }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Actions --}}
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div>
                    @can('cancel', $session)
                        @if($session->isInProgress())
                            <form action="{{ route('import.cancel', $session) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-warning" onclick="return confirm('{{ __('app.confirm_cancel_import') }}')">
                                    <i class="fas fa-ban"></i> {{ __('app.cancel_import') }}
                                </button>
                            </form>
                        @endif
                    @endcan
                </div>
                <div>
                    @can('delete', $session)
                        <form action="{{ route('import.destroy', $session) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('{{ __('app.confirm_delete') }}')">
                                <i class="fas fa-trash"></i> {{ __('app.delete_session') }}
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

