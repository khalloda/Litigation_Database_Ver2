@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Import Profiles</h2>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-auto">
            <input type="text" class="form-control" name="table" value="{{ request('table') }}" placeholder="Table name">
        </div>
        <div class="col-auto">
            <select class="form-select" name="active">
                <option value="">Active: Any</option>
                <option value="1" @selected(request('active')==='1')>Active</option>
                <option value="0" @selected(request('active')==='0')>Inactive</option>
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-primary">Filter</button>
        </div>
    </form>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Table</th>
                        <th>Header Hash</th>
                        <th>Active</th>
                        <th>Updated</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($profiles as $p)
                    <tr>
                        <td>{{ $p->name }}</td>
                        <td><code>{{ $p->table_name }}</code></td>
                        <td><code>{{ $p->header_hash ?? '—' }}</code></td>
                        <td>{{ $p->is_active ? 'Yes' : 'No' }}</td>
                        <td>{{ $p->updated_at }}</td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.import.choices.index', $p) }}">Choices</a>
                            <a class="btn btn-sm btn-outline-info" href="{{ route('admin.import.profiles.export', $p) }}">Export</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $profiles->withQueryString()->links() }}
        </div>
    </div>

    <hr>
    <h5>Create Profile</h5>
    <form method="POST" action="{{ route('admin.import.profiles.store') }}" class="row g-2">
        @csrf
        <div class="col-3">
            <input class="form-control" name="name" placeholder="Name" required>
        </div>
        <div class="col-3">
            <input class="form-control" name="table_name" placeholder="Table" required>
        </div>
        <div class="col-4">
            <input class="form-control" name="header_hash" placeholder="Header Hash (optional)">
        </div>
        <div class="col-2">
            <button class="btn btn-primary w-100">Save</button>
        </div>
    </form>
</div>
@endsection


