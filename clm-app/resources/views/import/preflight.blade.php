@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">{{ __('app.preflight_validation') }}</h2>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">{{ __('app.validation_summary') }}</h5>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <h2>{{ number_format($session->total_rows) }}</h2>
                    <p class="text-muted">{{ __('app.total_rows') }}</p>
                </div>
                <div class="col-md-3">
                    <h2 class="text-danger">{{ number_format($results['error_count']) }}</h2>
                    <p class="text-muted">{{ __('app.errors') }}</p>
                </div>
                <div class="col-md-3">
                    <h2 class="text-warning">{{ number_format($results['warning_count']) }}</h2>
                    <p class="text-muted">{{ __('app.warnings') }}</p>
                </div>
                <div class="col-md-3">
                    <h2 class="text-{{ $exceedsThreshold ? 'danger' : 'success' }}">
                        {{ number_format((($session->total_rows - $results['error_count']) / $session->total_rows) * 100, 1) }}%
                    </h2>
                    <p class="text-muted">{{ __('app.success_rate') }}</p>
                </div>
            </div>

            @if($exceedsThreshold)
                <div class="alert alert-danger mt-3">
                    <strong><i class="fas fa-exclamation-triangle"></i> {{ __('app.error_threshold_exceeded') }}</strong>
                    <p class="mb-0">{{ __('app.error_threshold_message') }}</p>
                </div>
            @else
                <div class="alert alert-success mt-3">
                    <i class="fas fa-check-circle"></i> {{ __('app.validation_passed') }}
                </div>
            @endif
        </div>
    </div>

    @if(!empty($results['errors']))
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">{{ __('app.validation_errors') }} ({{ count($results['errors']) }})</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>{{ __('app.row') }}</th>
                                <th>{{ __('app.column') }}</th>
                                <th>{{ __('app.value') }}</th>
                                <th>{{ __('app.error') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_slice($results['errors'], 0, 50) as $error)
                            <tr>
                                <td>{{ $error['row'] }}</td>
                                <td><code>{{ $error['column'] }}</code></td>
                                <td>{{ Str::limit($error['value'] ?? 'NULL', 30) }}</td>
                                <td>{{ $error['message'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if(count($results['errors']) > 50)
                        <p class="text-muted">{{ __('app.showing_first_errors', ['count' => 50]) }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <a href="{{ route('import.map', $session) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> {{ __('app.back_to_mapping') }}
                </a>
                @if(!$exceedsThreshold)
                    <form action="{{ route('import.run', $session) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg" onclick="return confirm('{{ __('app.confirm_start_import') }}')">
                            <i class="fas fa-play"></i> {{ __('app.start_import') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

