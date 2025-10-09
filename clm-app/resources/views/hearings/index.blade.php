@extends('layouts.app')

@section('title', __('app.hearings'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">{{ __('app.hearings') }}</h1>
        @can('hearings.create')
        <a href="{{ route('hearings.create') }}" class="btn btn-primary">{{ __('app.new_hearing') }}</a>
        @endcan
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>{{ __('app.hearing_date') }}</th>
                            <th>{{ __('app.time') }}</th>
                            <th>{{ __('app.case') }}</th>
                            <th>{{ __('app.court') }}</th>
                            <th>{{ __('app.status') }}</th>
                            @if(app()->getLocale() == 'ar')
                            <th>{{ __('app.actions') }}</th>
                            @else
                            <th class="text-end">{{ __('app.actions') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hearings as $hearing)
                        <tr>
                            <td><a href="{{ route('hearings.show', $hearing) }}">{{ $hearing->id }}</a></td>
                            <td>{{ $hearing->date?->format('Y-m-d') }}</td>
                            <td>{{ $hearing->time }}</td>
                            <td>{{ $hearing->case?->matter_name_ar ?? $hearing->case?->matter_name_en }}</td>
                            <td>{{ $hearing->court }}</td>
                            <td>{{ $hearing->status }}</td>
                            <td class="{{ app()->getLocale() == 'ar' ? 'text-start' : 'text-end' }}">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('hearings.show', $hearing) }}" class="btn btn-outline-primary btn-sm">{{ __('app.view') }}</a>
                                    @can('hearings.edit')
                                    <a href="{{ route('hearings.edit', $hearing) }}" class="btn btn-outline-secondary btn-sm">{{ __('app.edit') }}</a>
                                    @endcan
                                    @can('hearings.delete')
                                    <form action="{{ route('hearings.destroy', $hearing) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete_hearing') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">{{ __('app.delete') }}</button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $hearings->links() }}
        </div>
    </div>
</div>
@endsection

