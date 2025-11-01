@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Choices — {{ $profile->name }}</h2>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-auto">
            <input type="text" class="form-control" name="column" value="{{ request('column') }}" placeholder="Column">
        </div>
        <div class="col-auto">
            <select class="form-select" name="action">
                <option value="">Action: Any</option>
                @foreach(['match','alias','capacity','ignore'] as $a)
                    <option value="{{ $a }}" @selected(request('action')===$a)>{{ ucfirst($a) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search text">
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-primary">Filter</button>
        </div>
        <div class="col-auto ms-auto">
            <a class="btn btn-outline-secondary" href="{{ route('admin.import.profiles.export', $profile) }}">Export JSON</a>
        </div>
    </form>

    <div class="card mb-4">
        <div class="card-body table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Original (raw)</th>
                        <th>Normalized</th>
                        <th>Action</th>
                        <th>Entity</th>
                        <th>Active</th>
                        <th>Updated</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($choices as $c)
                    <tr>
                        <td><code>{{ $c->column }}</code></td>
                        <td>{{ $c->raw_value ?? '—' }}</td>
                        <td><code>{{ $c->normalized_value }}</code></td>
                        <td><span class="badge bg-secondary">{{ $c->action }}</span></td>
                        <td>{{ $c->entity_model ? class_basename($c->entity_model)."#".$c->entity_id : '—' }}</td>
                        <td>{{ $c->is_active ? 'Yes' : 'No' }}</td>
                        <td>{{ $c->updated_at }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.import.choices.update', [$profile, $c]) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="is_active" value="{{ $c->is_active ? 0 : 1 }}">
                                <button class="btn btn-sm btn-outline-warning">{{ $c->is_active ? 'Deactivate' : 'Activate' }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.import.choices.destroy', [$profile, $c]) }}" class="d-inline" onsubmit="return confirm('Delete choice?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $choices->withQueryString()->links() }}
        </div>
    </div>

    <h5>Add Choice</h5>
    <form method="POST" action="{{ route('admin.import.choices.store', $profile) }}" class="row g-2">
        @csrf
        <div class="col-2"><input class="form-control" name="column" placeholder="Column" required></div>
        <div class="col-3"><input class="form-control" name="raw_value" placeholder="Raw"></div>
        <div class="col-3"><input class="form-control" name="normalized_value" placeholder="Normalized" required></div>
        <div class="col-2">
            <select class="form-select" name="action" required>
                @foreach(['match','alias','capacity','ignore'] as $a)
                    <option value="{{ $a }}">{{ ucfirst($a) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-2"><button class="btn btn-primary w-100">Save</button></div>
    </form>
</div>
@endsection



