@extends('layouts.app')

@section('title', __('app.lawyers'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">{{ __('app.lawyers') }}</h1>
        @can('admin.users.manage')
        <a href="{{ route('lawyers.create') }}" class="btn btn-primary">{{ __('app.new_lawyer') }}</a>
        @endcan
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>{{ __('app.lawyer_name_ar') }}</th>
                            <th>{{ __('app.lawyer_name_en') }}</th>
                            <th>{{ __('app.lawyer_email') }}</th>
                            @if(app()->getLocale() == 'ar')
                            <th>{{ __('app.actions') }}</th>
                            @else
                            <th class="text-end">{{ __('app.actions') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lawyers as $lawyer)
                        <tr>
                            <td><a href="{{ route('lawyers.show', $lawyer) }}">{{ $lawyer->id }}</a></td>
                            <td>{{ $lawyer->lawyer_name_ar }}</td>
                            <td>{{ $lawyer->lawyer_name_en }}</td>
                            <td>{{ $lawyer->lawyer_email }}</td>
                            <td class="{{ app()->getLocale() == 'ar' ? 'text-start' : 'text-end' }}">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('lawyers.show', $lawyer) }}" class="btn btn-outline-primary btn-sm">{{ __('app.view') }}</a>
                                    @can('admin.users.manage')
                                    <a href="{{ route('lawyers.edit', $lawyer) }}" class="btn btn-outline-secondary btn-sm">{{ __('app.edit') }}</a>
                                    <form action="{{ route('lawyers.destroy', $lawyer) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete_lawyer') }}')">
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

            {{ $lawyers->links() }}
        </div>
    </div>
</div>
@endsection
