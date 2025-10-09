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
                            <th>{{ __('app.actions') }}</th>
                            <th>ID</th>
                            <th>{{ __('app.client_name_ar') }} / {{ __('app.client_name_en') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clients as $client)
                        <tr>
                            <td>
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
                            <td><a href="{{ route('clients.show', $client) }}">{{ $client->id }}</a></td>
                            <td><a href="{{ route('clients.show', $client) }}">{{ $client->client_name_ar ?? $client->client_name_en }}</a></td>
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
