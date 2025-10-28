@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">{{ __('app.column_mapping') }}</h2>

    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> {{ __('app.mapping_instructions') }}
    </div>

    <form action="{{ route('import.save-mapping', $session) }}" method="POST">
        @csrf
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">{{ __('app.map_columns') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('app.source_column') }}</th>
                                <th>{{ __('app.sample_data') }}</th>
                                <th>{{ __('app.target_column') }}</th>
                                <th>{{ __('app.confidence') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($parsed['headers'] as $sourceCol)
                            <tr>
                                <td><strong>{{ $sourceCol }}</strong></td>
                                <td>
                                    @if(isset($columnStats[$sourceCol]['sample_values']))
                                        <small class="text-muted">
                                            {{ implode(', ', array_slice($columnStats[$sourceCol]['sample_values'], 0, 3)) }}...
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <select name="mapping[{{ $sourceCol }}]" class="form-select">
                                        <option value="">{{ __('app.skip_column') }}</option>
                                        @foreach($dbColumns as $dbCol)
                                            <option value="{{ $dbCol }}" 
                                                {{ isset($autoMapping['mappings'][$sourceCol]) && $autoMapping['mappings'][$sourceCol] == $dbCol ? 'selected' : '' }}>
                                                {{ $dbCol }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    @if(isset($autoMapping['confidence'][$sourceCol]))
                                        <span class="badge bg-{{ $autoMapping['confidence'][$sourceCol] >= 80 ? 'success' : ($autoMapping['confidence'][$sourceCol] >= 65 ? 'warning' : 'secondary') }}">
                                            {{ $autoMapping['confidence'][$sourceCol] }}%
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(!empty($autoMapping['unmapped']))
                    <div class="alert alert-warning">
                        <strong>{{ __('app.unmapped_columns') }}:</strong>
                        {{ implode(', ', $autoMapping['unmapped']) }}
                    </div>
                @endif
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('import.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> {{ __('app.cancel') }}
            </a>
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-arrow-right"></i> {{ __('app.continue_to_validation') }}
            </button>
        </div>
    </form>
</div>
@endsection

