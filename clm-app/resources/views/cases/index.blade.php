@extends('layouts.app')

@section('title', 'Cases')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">Cases</h1>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Matter</th>
                            <th>Client</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cases as $case)
                        <tr>
                            <td>{{ $case->id }}</td>
                            <td>{{ $case->matter_name_ar ?? $case->matter_name_en }}</td>
                            <td>{{ $case->client?->client_name_ar ?? $case->client?->client_name_en }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $cases->links() }}
        </div>
    </div>
</div>
@endsection
