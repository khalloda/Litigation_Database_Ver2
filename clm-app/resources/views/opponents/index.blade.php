@extends('layouts.app')

@section('title', __('app.opponents'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">{{ __('app.opponents') }}</h1>
        <a href="{{ route('opponents.create') }}" class="btn btn-primary">{{ __('app.new_opponent') }}</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ __('app.search') }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-secondary" type="submit">{{ __('app.search') }}</button>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>{{ __('app.name_ar') }}</th>
                            <th>{{ __('app.name_en') }}</th>
                            <th>{{ __('app.is_active') }}</th>
                            <th class="text-end">{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($opponents as $opponent)
                        <tr>
                            <td><a href="{{ route('opponents.show', $opponent) }}">{{ $opponent->id }}</a></td>
                            <td>{{ $opponent->opponent_name_ar }}</td>
                            <td>{{ $opponent->opponent_name_en }}</td>
                            <td>{{ $opponent->is_active ? __('app.yes') : __('app.no') }}</td>
                            <td class="text-end">
                                <a href="{{ route('opponents.show', $opponent) }}" class="btn btn-outline-primary btn-sm">{{ __('app.view') }}</a>
                                <a href="{{ route('opponents.edit', $opponent) }}" class="btn btn-outline-secondary btn-sm">{{ __('app.edit') }}</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $opponents->links() }}
        </div>
    </div>
</div>
@endsection



