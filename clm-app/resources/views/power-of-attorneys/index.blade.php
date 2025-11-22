@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ __('app.power_of_attorneys') }}</h3>
                    @can('create', App\Models\PowerOfAttorney::class)
                    <a href="{{ route('power-of-attorneys.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ __('app.new_power_of_attorney') }}
                    </a>
                    @endcan
                </div>
                <div class="card-body">
                    @if($powerOfAttorneys->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('app.client') }}</th>
                                    <th>{{ __('app.client_print_name') }}</th>
                                    <th>{{ __('app.principal_name') }}</th>
                                    <th>{{ __('app.poa_number') }}</th>
                                    <th>{{ __('app.issue_date') }}</th>
                                    <th>{{ __('app.issuing_authority') }}</th>
                                    <th>{{ __('app.capacity') }}</th>
                                    <th>{{ __('app.authorized_lawyers') }}</th>
                                    <th>{{ __('app.year') }}</th>
                                    <th>{{ __('app.serial') }}</th>
                                    <th class="text-end">{{ __('app.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($powerOfAttorneys as $poa)
                                <tr>
                                    <td>
                                        @if($poa->client)
                                        <div>
                                            <div>{{ $poa->client->client_name_ar }}</div>
                                            <small class="text-muted">{{ $poa->client->client_name_en }}</small>
                                        </div>
                                        @else
                                        <span class="text-muted">{{ __('app.no_client') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $poa->client_print_name ?? __('app.not_set') }}</td>
                                    <td>
                                        <strong>{{ $poa->principal_name ?? __('app.not_set') }}</strong>
                                    </td>
                                    <td>
                                        @if($poa->poa_number)
                                        <span class="badge bg-info">{{ $poa->poa_number }}</span>
                                        @else
                                        <span class="text-muted">{{ __('app.not_set') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $poa->issue_date?->format('Y-m-d') ?? __('app.not_set') }}</td>
                                    <td>{{ $poa->issuing_authority ?? __('app.not_set') }}</td>
                                    <td>
                                        @if($poa->capacity)
                                        <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $poa->capacity }}">
                                            {{ Str::limit($poa->capacity, 30) }}
                                        </span>
                                        @else
                                        <span class="text-muted">{{ __('app.not_set') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($poa->authorized_lawyers)
                                        <span class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $poa->authorized_lawyers }}">
                                            {{ Str::limit($poa->authorized_lawyers, 20) }}
                                        </span>
                                        @else
                                        <span class="text-muted">{{ __('app.not_set') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $poa->year ?? __('app.not_set') }}</td>
                                    <td>
                                        @if($poa->serial)
                                        <span class="badge bg-info">{{ $poa->serial }}</span>
                                        @else
                                        <span class="text-muted">{{ __('app.not_set') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            @can('view', $poa)
                                            <a href="{{ route('power-of-attorneys.show', $poa) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @endcan
                                            @can('update', $poa)
                                            <a href="{{ route('power-of-attorneys.edit', $poa) }}" class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                            @can('delete', $poa)
                                            <form method="POST" action="{{ route('power-of-attorneys.destroy', $poa) }}" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
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

                    <div class="d-flex justify-content-center">
                        {{ $powerOfAttorneys->links() }}
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-file-signature fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">{{ __('app.no_power_of_attorneys') }}</h5>
                        <p class="text-muted">{{ __('app.no_power_of_attorneys_description') }}</p>
                        @can('create', App\Models\PowerOfAttorney::class)
                        <a href="{{ route('power-of-attorneys.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> {{ __('app.create_first_power_of_attorney') }}
                        </a>
                        @endcan
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
