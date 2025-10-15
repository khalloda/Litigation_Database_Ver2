@extends('layouts.app')

@section('title', __('app.opponent_details'))

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">{{ __('app.opponent_details') }}</h1>
        <div>
            <a href="{{ route('opponents.index') }}" class="btn btn-outline-secondary me-2">{{ __('app.back') }}</a>
            <a href="{{ route('opponents.edit', $opponent) }}" class="btn btn-primary">{{ __('app.edit') }}</a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0">{{ __('app.opponent_details') }}</h5>
        </div>
        <div class="card-body">
            <table class="table table-borderless">
                <tr><td><strong>ID</strong></td><td>{{ $opponent->id }}</td></tr>
                <tr><td><strong>{{ __('app.name_ar') }}</strong></td><td>{{ $opponent->opponent_name_ar }}</td></tr>
                <tr><td><strong>{{ __('app.name_en') }}</strong></td><td>{{ $opponent->opponent_name_en }}</td></tr>
                <tr><td><strong>{{ __('app.description') }}</strong></td><td>{{ $opponent->description }}</td></tr>
                <tr><td><strong>{{ __('app.notes') }}</strong></td><td>{{ $opponent->notes }}</td></tr>
                <tr><td><strong>{{ __('app.is_active') }}</strong></td><td>{{ $opponent->is_active ? __('app.yes') : __('app.no') }}</td></tr>
            </table>
        </div>
    </div>
</div>
@endsection


