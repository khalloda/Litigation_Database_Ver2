@extends('layouts.app')

@section('title', __('app.case_details'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">{{ __('app.case_details') }}</h1>
        <div>
            <a href="{{ route('cases.index') }}" class="btn btn-outline-secondary me-2">{{ __('app.back_to_cases') }}</a>
            @can('cases.edit')
            <a href="{{ route('cases.edit', $case) }}" class="btn btn-primary me-2">{{ __('app.edit_case') }}</a>
            @endcan
            @can('cases.delete')
            <form action="{{ route('cases.destroy', $case) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete_case') }}')">
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
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('app.case_details') }}</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>ID</strong></td>
                            <td>{{ $case->id }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.matter_name_ar') }}</strong></td>
                            <td>{{ $case->matter_name_ar }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.matter_name_en') }}</strong></td>
                            <td>{{ $case->matter_name_en }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.client') }}</strong></td>
                            <td>
                                <a href="{{ route('clients.show', $case->client) }}">
                                    {{ $case->client->client_name_ar ?? $case->client->client_name_en }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.matter_status') }}</strong></td>
                            <td>{{ $case->matter_status }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.matter_category') }}</strong></td>
                            <td>{{ $case->matter_category }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.matter_court') }}</strong></td>
                            <td>
                                @if($case->court)
                                    <a href="{{ route('courts.show', $case->court) }}">
                                        {{ app()->getLocale() === 'ar' ? $case->court->court_name_ar : $case->court->court_name_en }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.matter_circuit') }}</strong></td>
                            <td>{{ $case->matterCircuit ? (app()->getLocale() === 'ar' ? $case->matterCircuit->label_ar : $case->matterCircuit->label_en) : '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.circuit_secretary') }}</strong></td>
                            <td>{{ $case->circuitSecretaryRef ? (app()->getLocale() === 'ar' ? $case->circuitSecretaryRef->label_ar : $case->circuitSecretaryRef->label_en) : '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.court_floor') }}</strong></td>
                            <td>{{ $case->courtFloorRef ? (app()->getLocale() === 'ar' ? $case->courtFloorRef->label_ar : $case->courtFloorRef->label_en) : '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.court_hall') }}</strong></td>
                            <td>{{ $case->courtHallRef ? (app()->getLocale() === 'ar' ? $case->courtHallRef->label_ar : $case->courtHallRef->label_en) : '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.matter_start_date') }}</strong></td>
                            <td>{{ $case->matter_start_date?->format('Y-m-d') }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.matter_end_date') }}</strong></td>
                            <td>{{ $case->matter_end_date?->format('Y-m-d') }}</td>
                        </tr>
                        @if($case->matter_description)
                        <tr>
                            <td><strong>{{ __('app.matter_description') }}</strong></td>
                            <td>{{ $case->matter_description }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <!-- Related Hearings -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('app.related_hearings') }}</h5>
                </div>
                <div class="card-body">
                    @forelse($case->hearings as $hearing)
                    <div class="mb-2">
                        <strong>{{ $hearing->hearing_date?->format('Y-m-d') }}</strong> - {{ $hearing->hearing_type }}
                    </div>
                    @empty
                    <p>{{ __('app.no_hearings_found') }}</p>
                    @endforelse
                </div>
            </div>

            <!-- Related Tasks -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('app.related_tasks') }}</h5>
                </div>
                <div class="card-body">
                    @forelse($case->adminTasks()->limit(5)->get() as $task)
                    <div class="mb-2">
                        <strong>{{ $task->task_name }}</strong> - {{ $task->status }}
                    </div>
                    @empty
                    <p>{{ __('app.no_tasks_found') }}</p>
                    @endforelse
                </div>
            </div>

            <!-- Related Documents -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('app.related_documents') }}</h5>
                </div>
                <div class="card-body">
                    @forelse($case->documents as $document)
                    <div class="mb-2">
                        <strong>{{ $document->document_name }}</strong> - {{ $document->document_type }}
                    </div>
                    @empty
                    <p>{{ __('app.no_documents_found') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

