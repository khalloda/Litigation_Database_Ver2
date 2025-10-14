@extends('layouts.app')

@section('title', __('app.court_details'))

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">
            {{ app()->getLocale() === 'ar' ? $court->court_name_ar : $court->court_name_en }}
        </h1>
        <div>
            <a href="{{ route('courts.index') }}" class="btn btn-outline-secondary me-2">{{ __('app.back') }}</a>
            @can('update', $court)
            <a href="{{ route('courts.edit', $court) }}" class="btn btn-primary me-2">{{ __('app.edit') }}</a>
            @endcan
            @can('delete', $court)
            <form action="{{ route('courts.destroy', $court) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">{{ __('app.delete') }}</button>
            </form>
            @endcan
        </div>
    </div>

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

    <!-- Court Details Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">{{ __('app.court_details') }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>{{ __('app.court_name_ar') }}:</strong> {{ $court->court_name_ar ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>{{ __('app.court_name_en') }}:</strong> {{ $court->court_name_en ?? '-' }}</p>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-12">
                    <p><strong>{{ __('app.court_circuits') }}:</strong><br>
                        @forelse($court->circuits as $circuit)
                            <span class="badge bg-primary me-1 mb-1">
                                {{ $circuit->full_name }}
                            </span>
                        @empty
                            <span class="text-muted">-</span>
                        @endforelse
                    </p>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-12">
                    <p><strong>{{ __('app.court_secretaries') }}:</strong><br>
                        @forelse($court->secretaries as $secretary)
                            <span class="badge bg-info me-1 mb-1">
                                {{ app()->getLocale() === 'ar' ? $secretary->label_ar : $secretary->label_en }}
                            </span>
                        @empty
                            <span class="text-muted">-</span>
                        @endforelse
                    </p>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-12">
                    <p><strong>{{ __('app.court_floors') }}:</strong><br>
                        @forelse($court->floors as $floor)
                            <span class="badge bg-secondary me-1 mb-1">
                                {{ app()->getLocale() === 'ar' ? $floor->label_ar : $floor->label_en }}
                            </span>
                        @empty
                            <span class="text-muted">-</span>
                        @endforelse
                    </p>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-12">
                    <p><strong>{{ __('app.court_halls') }}:</strong><br>
                        @forelse($court->halls as $hall)
                            <span class="badge bg-warning text-dark me-1 mb-1">
                                {{ app()->getLocale() === 'ar' ? $hall->label_ar : $hall->label_en }}
                            </span>
                        @empty
                            <span class="text-muted">-</span>
                        @endforelse
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>{{ __('app.status') }}:</strong>
                        <span class="badge {{ $court->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $court->is_active ? __('app.active') : __('app.inactive') }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Cases Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">
                <i class="bi bi-folder2-open me-2"></i>{{ __('app.related_cases') }}
                <span class="badge bg-light text-dark ms-2">{{ $cases->total() }}</span>
            </h5>
        </div>
        <div class="card-body">
            @if($cases->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('app.matter_name_ar') }}</th>
                            <th>{{ __('app.client') }}</th>
                            <th>{{ __('app.matter_status') }}</th>
                            <th>{{ __('app.matter_start_date') }}</th>
                            <th>{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cases as $case)
                        <tr>
                            <td>{{ $case->matter_name_ar ?? $case->matter_name_en }}</td>
                            <td>
                                @if($case->client)
                                <a href="{{ route('clients.show', $case->client) }}">
                                    {{ app()->getLocale() === 'ar' ? $case->client->client_name_ar : $case->client->client_name_en }}
                                </a>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $case->matter_status ?? '-' }}</td>
                            <td>{{ $case->matter_start_date ? $case->matter_start_date->format('Y-m-d') : '-' }}</td>
                            <td>
                                <a href="{{ route('cases.show', $case) }}" class="btn btn-sm btn-outline-primary">
                                    {{ __('app.view') }}
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $cases->links() }}
            </div>
            @else
            <p class="text-muted mb-0">{{ __('app.no_cases_found') }}</p>
            @endif
        </div>
    </div>

    <!-- Related Hearings Section (Placeholder) -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="bi bi-calendar3 me-2"></i>{{ __('app.related_hearings') }}
                <span class="badge bg-warning text-dark ms-2">{{ __('app.coming_soon') }}</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-0" role="alert">
                <i class="bi bi-info-circle me-2"></i>
                {{ __('app.coming_soon') }} - {{ __('app.related_hearings') }} will be displayed here once the Hearings model is finalized.
            </div>
        </div>
    </div>

    <!-- Related Tasks Section (Placeholder) -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">
                <i class="bi bi-list-task me-2"></i>{{ __('app.related_tasks') }}
                <span class="badge bg-secondary ms-2">{{ __('app.coming_soon') }}</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-warning mb-0" role="alert">
                <i class="bi bi-info-circle me-2"></i>
                {{ __('app.coming_soon') }} - {{ __('app.related_tasks') }} with expandable subtasks will be displayed here once the Tasks/Subtasks models are finalized.
            </div>
        </div>
    </div>
</div>
@endsection

