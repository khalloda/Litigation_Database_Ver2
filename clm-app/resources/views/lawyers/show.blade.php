@extends('layouts.app')

@section('title', __('app.lawyer_details'))

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">{{ __('app.lawyer_details') }}</h1>
        <div>
            <a href="{{ route('lawyers.index') }}" class="btn btn-outline-secondary me-2">{{ __('app.back_to_lawyers') }}</a>
            @can('admin.users.manage')
            <a href="{{ route('lawyers.edit', $lawyer) }}" class="btn btn-primary me-2">{{ __('app.edit_lawyer') }}</a>
            <form action="{{ route('lawyers.destroy', $lawyer) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete_lawyer') }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">{{ __('app.delete') }}</button>
            </form>
            @endcan
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('app.lawyer_details') }}</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>ID</strong></td>
                            <td>{{ $lawyer->id }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.lawyer_name_ar') }}</strong></td>
                            <td>{{ $lawyer->lawyer_name_ar }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.lawyer_name_en') }}</strong></td>
                            <td>{{ $lawyer->lawyer_name_en }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.lawyer_title') }}</strong></td>
                            <td>{{ $lawyer->lawyer_name_title }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.lawyer_email') }}</strong></td>
                            <td>{{ $lawyer->lawyer_email }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('app.attendance_track') }}</strong></td>
                            <td>{{ $lawyer->attendance_track ? __('Yes') : __('No') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('app.related_assigned_cases') }}</h5>
                </div>
                <div class="card-body">
                    @forelse($cases->take(10) as $case)
                    <div class="mb-2">
                        <a href="{{ route('cases.show', $case) }}">
                            <strong>{{ $case->matter_name_ar ?? $case->matter_name_en }}</strong>
                        </a> - {{ $case->matter_status }}
                    </div>
                    @empty
                    <p>{{ __('app.no_assigned_cases_found') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
