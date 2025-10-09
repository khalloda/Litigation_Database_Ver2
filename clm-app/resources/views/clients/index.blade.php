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
                            <th>Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clients as $client)
                        <tr>
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
