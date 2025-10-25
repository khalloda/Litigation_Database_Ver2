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
                            <td>
                                @if($case->matterStatus)
                                    {{ app()->getLocale() === 'ar' ? $case->matterStatus->label_ar : $case->matterStatus->label_en }}
                                @else
                                    {{ $case->matter_status ?? '-' }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.matter_category') }}</strong></td>
                            <td>
                                @if($case->matterCategory)
                                    {{ app()->getLocale() === 'ar' ? $case->matterCategory->label_ar : $case->matterCategory->label_en }}
                                @else
                                    {{ $case->matter_category ?? '-' }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.matter_degree') }}</strong></td>
                            <td>
                                @if($case->matterDegree)
                                    {{ app()->getLocale() === 'ar' ? $case->matterDegree->label_ar : $case->matterDegree->label_en }}
                                @else
                                    {{ $case->matter_degree ?? '-' }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.matter_importance') }}</strong></td>
                            <td>
                                @if($case->matterImportance)
                                    {{ app()->getLocale() === 'ar' ? $case->matterImportance->label_ar : $case->matterImportance->label_en }}
                                @else
                                    {{ $case->matter_importance ?? '-' }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.client_branch') }}</strong></td>
                            <td>
                                @if($case->matterBranch)
                                    {{ app()->getLocale() === 'ar' ? $case->matterBranch->label_ar : $case->matterBranch->label_en }}
                                @else
                                    {{ $case->client_branch ?? '-' }}
                                @endif
                            </td>
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
                            <td><strong>{{ __('app.matter_destination') }}</strong></td>
                            <td>
                                @if($case->matterDestinationRef)
                                    <a href="{{ route('courts.show', $case->matterDestinationRef) }}">
                                        {{ app()->getLocale() === 'ar' ? $case->matterDestinationRef->court_name_ar : $case->matterDestinationRef->court_name_en }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.circuit') }}</strong></td>
                            <td>
                                @if($case->circuitName || $case->circuitSerial || $case->circuitShift)
                                    @php
                                        $name = $case->circuitName ? (app()->getLocale() === 'ar' ? $case->circuitName->label_ar : $case->circuitName->label_en) : '';
                                        $serial = $case->circuitSerial ? (app()->getLocale() === 'ar' ? $case->circuitSerial->label_ar : $case->circuitSerial->label_en) : '';
                                        $shift = $case->circuitShift ? (app()->getLocale() === 'ar' ? $case->circuitShift->label_ar : $case->circuitShift->label_en) : '';

                                        $result = $name;
                                        if ($serial) $result .= " {$serial}";
                                        if ($shift && $shift !== 'Morning') $result .= " ({$shift})";
                                    @endphp
                                    {{ $result }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
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
                        <tr>
                            <td><strong>{{ __('app.client_capacity') }}</strong></td>
                            <td>
                                @php
                                    $clientName = $case->client_in_case_name ?: ($case->client?->client_name_ar ?? $case->client?->client_name_en);
                                    $clientCap = $case->clientCapacity ? (app()->getLocale()==='ar' ? $case->clientCapacity->label_ar : $case->clientCapacity->label_en) : null;
                                    $parts = array_filter([$clientName, $clientCap, $case->client_capacity_note]);
                                @endphp
                                {{ !empty($parts) ? implode(' - ', $parts) : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.opponent_capacity') }}</strong></td>
                            <td>
                                @php
                                    $oppName = $case->opponent_in_case_name ?: ($case->opponent ? (app()->getLocale()==='ar' ? $case->opponent->opponent_name_ar : $case->opponent->opponent_name_en) : null);
                                    $oppCap = $case->opponentCapacity ? (app()->getLocale()==='ar' ? $case->opponentCapacity->label_ar : $case->opponentCapacity->label_en) : null;
                                    $oparts = array_filter([$oppName, $oppCap, $case->opponent_capacity_note]);
                                @endphp
                                {{ !empty($oparts) ? implode(' - ', $oparts) : '-' }}
                            </td>
                        </tr>
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

