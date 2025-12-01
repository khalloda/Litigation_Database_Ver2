@extends('layouts.app')

@section('title', __('app.courts'))

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">{{ __('app.courts') }}</h1>
        @can('create', App\Models\Court::class)
        <a href="{{ route('courts.create') }}" class="btn btn-primary">{{ __('app.create_court') }}</a>
        @endcan
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Search and Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('courts.index') }}" class="row g-3">
                <div class="col-md-6">
                    <label for="search" class="form-label">{{ __('app.search') }}</label>
                    <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="{{ __('app.search_courts') }}">
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label">{{ __('app.status') }}</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">{{ __('app.all_statuses') }}</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('app.active') }}</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('app.inactive') }}</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary w-100">{{ __('app.filter') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Courts List -->
    <div class="card shadow-sm">
        <div class="card-body">
            @if($courts->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('app.court_name_ar') }}</th>
                            <th>{{ __('app.court_name_en') }}</th>
                            <th>{{ __('app.status') }}</th>
                            <th>{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($courts as $court)
                        <tr>
                            <td>{{ $court->court_name_ar }}</td>
                            <td>{{ $court->court_name_en }}</td>
                            <td>
                                <span class="badge {{ $court->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $court->is_active ? __('app.active') : __('app.inactive') }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('courts.show', $court) }}" class="btn btn-sm btn-outline-primary">{{ __('app.view') }}</a>
                                @can('update', $court)
                                <a href="{{ route('courts.edit', $court) }}" class="btn btn-sm btn-outline-secondary">{{ __('app.edit') }}</a>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $courts->links() }}
            </div>
            @else
            <p class="text-muted text-center mb-0">{{ __('app.no_courts_found') }}</p>
            @endif
        </div>
    </div>
</div>
@endsection

