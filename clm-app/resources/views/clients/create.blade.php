@extends('layouts.app')

@section('title', 'Create Client')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">Create Client</h1>
        <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">← Back</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('clients.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name (Arabic)</label>
                        <input type="text" name="client_name_ar" class="form-control" value="{{ old('client_name_ar') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Name (English)</label>
                        <input type="text" name="client_name_en" class="form-control" value="{{ old('client_name_en') }}">
                    </div>
                </div>
                <p class="text-muted mt-2">At least one name is required.</p>
                <div class="mt-3 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
