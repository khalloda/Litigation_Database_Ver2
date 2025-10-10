@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ __('app.power_of_attorney_details') }}</h3>
                    <div class="btn-group" role="group">
                        @can('update', $powerOfAttorney)
                        <a href="{{ route('power-of-attorneys.edit', $powerOfAttorney) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> {{ __('app.edit') }}
                        </a>
                        @endcan
                        @can('delete', $powerOfAttorney)
                        <form method="POST" action="{{ route('power-of-attorneys.destroy', $powerOfAttorney) }}" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> {{ __('app.delete') }}
                            </button>
                        </form>
                        @endcan
                        <a href="{{ route('power-of-attorneys.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('app.back_to_list') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>{{ __('app.basic_information') }}</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">{{ __('app.client') }}:</th>
                                    <td>
                                        @if($powerOfAttorney->client)
                                        <div>
                                            <div>{{ $powerOfAttorney->client->client_name_ar }}</div>
                                            <small class="text-muted">{{ $powerOfAttorney->client->client_name_en }}</small>
                                        </div>
                                        @else
                                        <span class="text-muted">{{ __('app.no_client') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.client_print_name') }}:</th>
                                    <td><strong>{{ $powerOfAttorney->client_print_name ?? __('app.not_set') }}</strong></td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.principal_name') }}:</th>
                                    <td><strong>{{ $powerOfAttorney->principal_name ?? __('app.not_set') }}</strong></td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.poa_number') }}:</th>
                                    <td>
                                        @if($powerOfAttorney->poa_number)
                                        <span class="badge bg-info">{{ $powerOfAttorney->poa_number }}</span>
                                        @else
                                        <span class="text-muted">{{ __('app.not_set') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.issue_date') }}:</th>
                                    <td>{{ $powerOfAttorney->issue_date?->format('Y-m-d') ?? __('app.not_set') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.year') }}:</th>
                                    <td>{{ $powerOfAttorney->year ?? __('app.not_set') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.serial') }}:</th>
                                    <td>
                                        @if($powerOfAttorney->serial)
                                        <span class="badge bg-secondary">{{ $powerOfAttorney->serial }}</span>
                                        @else
                                        <span class="text-muted">{{ __('app.not_set') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.copies_count') }}:</th>
                                    <td>{{ $powerOfAttorney->copies_count ?? __('app.not_set') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.inventory') }}:</th>
                                    <td>
                                        @if($powerOfAttorney->inventory)
                                        <span class="badge bg-success">{{ __('app.yes') }}</span>
                                        @else
                                        <span class="badge bg-secondary">{{ __('app.no') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>{{ __('app.authority_information') }}</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">{{ __('app.issuing_authority') }}:</th>
                                    <td>{{ $powerOfAttorney->issuing_authority ?? __('app.not_set') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.capacity') }}:</th>
                                    <td>{{ $powerOfAttorney->capacity ?? __('app.not_set') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.principal_capacity') }}:</th>
                                    <td>{{ $powerOfAttorney->principal_capacity ?? __('app.not_set') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.letter') }}:</th>
                                    <td>{{ $powerOfAttorney->letter ?? __('app.not_set') }}</td>
                                </tr>
                            </table>
                            
                            <h5 class="mt-4">{{ __('app.system_information') }}</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">{{ __('app.created_at') }}:</th>
                                    <td>{{ $powerOfAttorney->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.updated_at') }}:</th>
                                    <td>{{ $powerOfAttorney->updated_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.created_by') }}:</th>
                                    <td>{{ $powerOfAttorney->created_by ?? __('app.system') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.updated_by') }}:</th>
                                    <td>{{ $powerOfAttorney->updated_by ?? __('app.system') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>{{ __('app.authorized_lawyers') }}</h5>
                            <div class="card">
                                <div class="card-body">
                                    @if($powerOfAttorney->authorized_lawyers)
                                    <p class="mb-3">{{ $powerOfAttorney->authorized_lawyers }}</p>
                                    @else
                                    <p class="text-muted mb-3">{{ __('app.not_set') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>{{ __('app.notes') }}</h5>
                            <div class="card">
                                <div class="card-body">
                                    @if($powerOfAttorney->notes)
                                    <p class="mb-3">{{ $powerOfAttorney->notes }}</p>
                                    @else
                                    <p class="text-muted mb-3">{{ __('app.not_set') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
