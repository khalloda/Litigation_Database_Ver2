@extends('layouts.app')

@section('title', 'Clients')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">Clients</h1>
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
                            <td>{{ $client->id }}</td>
                            <td>{{ $client->client_name_ar ?? $client->client_name_en }}</td>
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
