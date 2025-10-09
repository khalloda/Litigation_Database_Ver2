@extends('layouts.app')

@section('title', __('app.hearing_details'))

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">{{ __('app.hearing_details') }}</h1>
        <div>
            <a href="{{ route('hearings.index') }}" class="btn btn-outline-secondary me-2">{{ __('app.back_to_hearings') }}</a>
            @can('hearings.edit')
            <a href="{{ route('hearings.edit', $hearing) }}" class="btn btn-primary me-2">{{ __('app.edit_hearing') }}</a>
            @endcan
            @can('hearings.delete')
            <form action="{{ route('hearings.destroy', $hearing) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete_hearing') }}')">
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

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-borderless">
                <tr>
                    <td><strong>ID</strong></td>
                    <td>{{ $hearing->id }}</td>
                </tr>
                <tr>
                    <td><strong>{{ __('app.case') }}</strong></td>
                    <td>
                        @if($hearing->case)
                        <a href="{{ route('cases.show', $hearing->case) }}">
                            {{ $hearing->case->matter_name_ar ?? $hearing->case->matter_name_en }}
                        </a>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td><strong>{{ __('app.client') }}</strong></td>
                    <td>
                        @if($hearing->case?->client)
                        <a href="{{ route('clients.show', $hearing->case->client) }}">
                            {{ $hearing->case->client->client_name_ar ?? $hearing->case->client->client_name_en }}
                        </a>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td><strong>{{ __('app.hearing_date') }}</strong></td>
                    <td>{{ $hearing->date?->format('Y-m-d') }}</td>
                </tr>
                <tr>
                    <td><strong>Procedure</strong></td>
                    <td>{{ $hearing->procedure }}</td>
                </tr>
                <tr>
                    <td><strong>{{ __('app.hearing_court') }}</strong></td>
                    <td>{{ $hearing->court }}</td>
                </tr>
                <tr>
                    <td><strong>Decision</strong></td>
                    <td>{{ $hearing->decision }}</td>
                </tr>
                @if($hearing->next_hearing)
                <tr>
                    <td><strong>{{ __('app.next_hearing_date') }}</strong></td>
                    <td>{{ $hearing->next_hearing?->format('Y-m-d') }}</td>
                </tr>
                @endif
                @if($hearing->notes)
                <tr>
                    <td><strong>{{ __('app.hearing_notes') }}</strong></td>
                    <td>{{ $hearing->notes }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection

