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
                                    <th width="40%">{{ __('app.poa_number') }}:</th>
                                    <td><strong>{{ $powerOfAttorney->poa_number }}</strong></td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.client') }}:</th>
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
                                    <th>{{ __('app.poa_type') }}:</th>
                                    <td>
                                        <span class="badge bg-info">{{ $powerOfAttorney->poa_type }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.issue_date') }}:</th>
                                    <td>{{ $powerOfAttorney->issue_date?->format('Y-m-d') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.expiry_date') }}:</th>
                                    <td>
                                        <span class="{{ $powerOfAttorney->expiry_date < now() ? 'text-danger' : ($powerOfAttorney->expiry_date < now()->addDays(30) ? 'text-warning' : '') }}">
                                            {{ $powerOfAttorney->expiry_date?->format('Y-m-d') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.status') }}:</th>
                                    <td>
                                        @if($powerOfAttorney->is_active)
                                            <span class="badge bg-success">{{ __('app.active') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('app.inactive') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>{{ __('app.system_information') }}</h5>
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

                    @if($powerOfAttorney->expiry_date && $powerOfAttorney->expiry_date < now())
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle"></i>
                            {{ __('app.power_of_attorney_expired') }}
                        </div>
                    @elseif($powerOfAttorney->expiry_date && $powerOfAttorney->expiry_date < now()->addDays(30))
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle"></i>
                            {{ __('app.power_of_attorney_expiring_soon', ['days' => $powerOfAttorney->expiry_date->diffInDays(now())]) }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
