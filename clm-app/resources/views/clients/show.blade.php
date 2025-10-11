@extends('layouts.app')

@section('title', 'Client Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">Client Details</h1>
        <div>
            <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary me-2">{{ __('app.back_to_clients') }}</a>
            @can('clients.edit')
            <a href="{{ route('clients.edit', $client) }}" class="btn btn-primary me-2">{{ __('app.edit_client') }}</a>
            @endcan
            @can('clients.delete')
            <form action="{{ route('clients.destroy', $client) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">{{ __('app.delete') }}</button>
            </form>
            @endcan
        </div>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Client</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>{{ __('app.id') }}</strong></td>
                            <td>{{ $client->id }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.client_name_ar') }}</strong></td>
                            <td>{{ $client->client_name_ar }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.client_name_en') }}</strong></td>
                            <td>{{ $client->client_name_en }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.client_print_name') }}</strong></td>
                            <td>{{ $client->client_print_name }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.cash_or_probono') }}</strong></td>
                            <td>
                                @if($client->cashOrProbono)
                                <span class="badge bg-info">{{ $client->cash_or_probono_label }}</span>
                                @else
                                <span class="text-muted">{{ __('app.not_set') }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.status') }}</strong></td>
                            <td>
                                @if($client->statusRef)
                                <span class="badge bg-success">{{ $client->status_label }}</span>
                                @else
                                <span class="text-muted">{{ __('app.not_set') }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.client_start') }}</strong></td>
                            <td>{{ $client->client_start ? $client->client_start->format('Y-m-d') : __('app.not_set') }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.client_end') }}</strong></td>
                            <td>{{ $client->client_end ? $client->client_end->format('Y-m-d') : __('app.not_set') }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.contact_lawyer') }}</strong></td>
                            <td>
                                @if($client->contactLawyer)
                                <span class="badge bg-primary">{{ $client->contact_lawyer_name }}</span>
                                @else
                                <span class="text-muted">{{ __('app.not_set') }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.power_of_attorney_location') }}</strong></td>
                            <td>
                                @if($client->powerOfAttorneyLocation)
                                <span class="badge bg-warning">{{ $client->power_of_attorney_location_label }}</span>
                                @else
                                <span class="text-muted">{{ __('app.not_set') }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.documents_location') }}</strong></td>
                            <td>
                                @if($client->documentsLocation)
                                <span class="badge bg-secondary">{{ $client->documents_location_label }}</span>
                                @else
                                <span class="text-muted">{{ __('app.not_set') }}</span>
                                @endif
                            </td>
                        </tr>
                        @if($client->logo)
                        <tr>
                            <td><strong>{{ __('app.logo') }}</strong></td>
                            <td>
                                @if(file_exists(public_path($client->logo)))
                                <img src="{{ asset($client->logo) }}" alt="Client Logo" class="img-thumbnail" style="max-width: 100px; max-height: 100px;">
                                @else
                                <span class="text-muted">{{ __('app.logo_file_not_found') }}</span>
                                @endif
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <td><strong>{{ __('app.created_at') }}</strong></td>
                            <td>{{ $client->created_at ? $client->created_at->format('Y-m-d H:i') : __('app.not_set') }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.updated_at') }}</strong></td>
                            <td>{{ $client->updated_at ? $client->updated_at->format('Y-m-d H:i') : __('app.not_set') }}</td>
                        </tr>
                        @if($client->createdBy)
                        <tr>
                            <td><strong>{{ __('app.created_by') }}</strong></td>
                            <td>{{ $client->createdBy->name }}</td>
                        </tr>
                        @endif
                        @if($client->updatedBy)
                        <tr>
                            <td><strong>{{ __('app.updated_by') }}</strong></td>
                            <td>{{ $client->updatedBy->name }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Cases</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Matter</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cases as $case)
                                <tr>
                                    <td>{{ $case->id }}</td>
                                    <td>{{ $case->matter_name_ar ?? $case->matter_name_en }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $cases->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Change History Section (Super Admin Only) -->
    @can('admin.users.manage')
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('app.change_history') }}</h5>
                </div>
                <div class="card-body">
                    @if($client->activities && $client->activities->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('app.date_time') }}</th>
                                    <th>{{ __('app.event') }}</th>
                                    <th>{{ __('app.user') }}</th>
                                    <th>{{ __('app.changes') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($client->activities->sortByDesc('created_at') as $activity)
                                <tr>
                                    <td>{{ $activity->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $activity->event === 'created' ? 'success' : ($activity->event === 'updated' ? 'warning' : 'danger') }}">
                                            {{ __('app.' . $activity->event) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($activity->causer)
                                        {{ $activity->causer->name }}
                                        @else
                                        <span class="text-muted">{{ __('app.system') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($activity->event === 'updated' && isset($activity->properties['old']) && isset($activity->properties['attributes']))
                                        <small>
                                            @foreach($activity->properties['old'] as $field => $oldValue)
                                            @if(isset($activity->properties['attributes'][$field]) && $activity->properties['attributes'][$field] != $oldValue)
                                            <strong>{{ $field }}:</strong>
                                            <span class="text-danger">{{ $oldValue }}</span> →
                                            <span class="text-success">{{ $activity->properties['attributes'][$field] }}</span><br>
                                            @endif
                                            @endforeach
                                        </small>
                                        @elseif($activity->event === 'created' && isset($activity->properties['attributes']))
                                        <small class="text-success">{{ __('app.client_created') }}</small>
                                        @elseif($activity->event === 'deleted')
                                        <small class="text-danger">{{ __('app.client_deleted') }}</small>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted mb-0">{{ __('app.no_change_history') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endcan
</div>
@endsection
