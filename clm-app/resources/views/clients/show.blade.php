@extends('layouts.app')

@section('title', 'Client Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">Client Details</h1>
        <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">← Back to Clients</a>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header"><h5 class="mb-0">Client</h5></div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr><td><strong>ID</strong></td><td>{{ $client->id }}</td></tr>
                        <tr><td><strong>Name (AR)</strong></td><td>{{ $client->client_name_ar }}</td></tr>
                        <tr><td><strong>Name (EN)</strong></td><td>{{ $client->client_name_en }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header"><h5 class="mb-0">Cases</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr><th>ID</th><th>Matter</th></tr>
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
</div>
@endsection


