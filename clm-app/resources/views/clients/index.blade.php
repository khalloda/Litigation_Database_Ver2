@extends('layouts.app')

@section('title', 'Clients')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">Clients</h1>
        @can('clients.create')
        <a href="{{ route('clients.create') }}" class="btn btn-primary">+ New Client</a>
        @endcan
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>
                                @if(app()->getLocale() == 'ar')
                                    {{ __('app.client_name_ar') }}
                                @else
                                    {{ __('app.client_name_en') }}
                                @endif
                            </th>
                            <th>
                                @if(app()->getLocale() == 'ar')
                                    {{ __('app.lawyer_name_ar') }}
                                @else
                                    {{ __('app.lawyer_name_en') }}
                                @endif
                            </th>
                            <th>
                                @if(app()->getLocale() == 'ar')
                                    {{ __('app.status_ar') }}
                                @else
                                    {{ __('app.status_en') }}
                                @endif
                            </th>
                            <th>
                                @if(app()->getLocale() == 'ar')
                                    {{ __('app.cash_or_probono_ar') }}
                                @else
                                    {{ __('app.cash_or_probono_en') }}
                                @endif
                            </th>
                            <th>{{ __('app.cases_count') }}</th>
                            @if(app()->getLocale() == 'ar')
                            <th>{{ __('app.actions') }}</th>
                            @else
                            <th class="text-end">{{ __('app.actions') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clients as $client)
                        <tr>
                            <td><a href="{{ route('clients.show', $client) }}">{{ $client->id }}</a></td>
                            <td>
                                @if(app()->getLocale() == 'ar')
                                    {{ $client->client_name_ar }}
                                @else
                                    {{ $client->client_name_en }}
                                @endif
                            </td>
                            <td>
                                @if($client->contactLawyer)
                                    @if(app()->getLocale() == 'ar')
                                        {{ $client->contactLawyer->lawyer_name_ar }}
                                    @else
                                        {{ $client->contactLawyer->lawyer_name_en }}
                                    @endif
                                @else
                                    <span class="text-muted">{{ __('app.not_set') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($client->statusRef)
                                    <span class="badge bg-success">
                                        @if(app()->getLocale() == 'ar')
                                            {{ $client->statusRef->label_ar }}
                                        @else
                                            {{ $client->statusRef->label_en }}
                                        @endif
                                    </span>
                                @else
                                    <span class="text-muted">{{ __('app.not_set') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($client->cashOrProbono)
                                    <span class="badge bg-warning">
                                        @if(app()->getLocale() == 'ar')
                                            {{ $client->cashOrProbono->label_ar }}
                                        @else
                                            {{ $client->cashOrProbono->label_en }}
                                        @endif
                                    </span>
                                @else
                                    <span class="text-muted">{{ __('app.not_set') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $client->cases_count }}</span>
                            </td>
                            <td class="{{ app()->getLocale() == 'ar' ? 'text-start' : 'text-end' }}">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('clients.show', $client) }}" class="btn btn-outline-primary btn-sm">{{ __('app.view') }}</a>
                                    @can('clients.edit')
                                    <a href="{{ route('clients.edit', $client) }}" class="btn btn-outline-secondary btn-sm">{{ __('app.edit') }}</a>
                                    @endcan
                                    @can('clients.delete')
                                    <form action="{{ route('clients.destroy', $client) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">{{ __('app.delete') }}</button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $clients->links() }}
        </div>
    </div>
</div>
@endsection
