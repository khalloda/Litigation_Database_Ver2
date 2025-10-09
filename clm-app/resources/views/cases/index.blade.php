@extends('layouts.app')

@section('title', __('app.cases'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">{{ __('app.cases') }}</h1>
        @can('cases.create')
        <a href="{{ route('cases.create') }}" class="btn btn-primary">{{ __('app.new_case') }}</a>
        @endcan
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>{{ __('app.matter_name_ar') }}</th>
                            <th>{{ __('app.matter_name_en') }}</th>
                            <th>{{ __('app.client_name_ar') }}</th>
                            <th>{{ __('app.client_name_en') }}</th>
                            <th>{{ __('app.matter_status') }}</th>
                            @if(app()->getLocale() == 'ar')
                            <th>{{ __('app.actions') }}</th>
                            @else
                            <th class="text-end">{{ __('app.actions') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cases as $case)
                        <tr>
                            <td><a href="{{ route('cases.show', $case) }}">{{ $case->id }}</a></td>
                            <td>{{ $case->matter_name_ar }}</td>
                            <td>{{ $case->matter_name_en }}</td>
                            <td>{{ $case->client?->client_name_ar }}</td>
                            <td>{{ $case->client?->client_name_en }}</td>
                            <td>{{ $case->matter_status }}</td>
                            <td class="{{ app()->getLocale() == 'ar' ? 'text-start' : 'text-end' }}">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('cases.show', $case) }}" class="btn btn-outline-primary btn-sm">{{ __('app.view') }}</a>
                                    @can('cases.edit')
                                    <a href="{{ route('cases.edit', $case) }}" class="btn btn-outline-secondary btn-sm">{{ __('app.edit') }}</a>
                                    @endcan
                                    @can('cases.delete')
                                    <form action="{{ route('cases.destroy', $case) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete_case') }}')">
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

            {{ $cases->links() }}
        </div>
    </div>
</div>
@endsection
