@extends('layouts.app')

@section('title', 'Clients')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">Clients</h1>
        @can('clients.create')
        <a href="{{ route('clients.create') }}" class="btn btn-primary">+ New Client</a>
        @endcan
    </div>

    <!-- Search and Filter Form -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('clients.index') }}" class="row g-3">
                <!-- Search Input -->
                <div class="col-md-3">
                    <label for="search" class="form-label">{{ __('app.search') }}</label>
                    <input type="text"
                        class="form-control"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="{{ __('app.search_clients_placeholder') }}">
                </div>

                <!-- Status Filter -->
                <div class="col-md-2">
                    <label for="status_id" class="form-label">
                        @if(app()->getLocale() == 'ar')
                        {{ __('app.status_ar') }}
                        @else
                        {{ __('app.status_en') }}
                        @endif
                    </label>
                    <select class="form-select" id="status_id" name="status_id">
                        <option value="">{{ __('app.all_statuses') }}</option>
                        @foreach($statuses as $status)
                        <option value="{{ $status->id }}" {{ $status_id == $status->id ? 'selected' : '' }}>
                            @if(app()->getLocale() == 'ar')
                            {{ $status->label_ar }}
                            @else
                            {{ $status->label_en }}
                            @endif
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Cash or Probono Filter -->
                <div class="col-md-2">
                    <label for="cash_or_probono_id" class="form-label">
                        @if(app()->getLocale() == 'ar')
                        {{ __('app.cash_or_probono_ar') }}
                        @else
                        {{ __('app.cash_or_probono_en') }}
                        @endif
                    </label>
                    <select class="form-select" id="cash_or_probono_id" name="cash_or_probono_id">
                        <option value="">{{ __('app.all_types') }}</option>
                        @foreach($cashOrProbonoOptions as $option)
                        <option value="{{ $option->id }}" {{ $cash_or_probono_id == $option->id ? 'selected' : '' }}>
                            @if(app()->getLocale() == 'ar')
                            {{ $option->label_ar }}
                            @else
                            {{ $option->label_en }}
                            @endif
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Contact Lawyer Filter -->
                <div class="col-md-3">
                    <label for="contact_lawyer_id" class="form-label">
                        @if(app()->getLocale() == 'ar')
                        {{ __('app.lawyer_name_ar') }}
                        @else
                        {{ __('app.lawyer_name_en') }}
                        @endif
                    </label>
                    <select class="form-select" id="contact_lawyer_id" name="contact_lawyer_id">
                        <option value="">{{ __('app.all_lawyers') }}</option>
                        @foreach($lawyers as $lawyer)
                        <option value="{{ $lawyer->id }}" {{ $contact_lawyer_id == $lawyer->id ? 'selected' : '' }}>
                            @if(app()->getLocale() == 'ar')
                            {{ $lawyer->lawyer_name_ar }}
                            @else
                            {{ $lawyer->lawyer_name_en }}
                            @endif
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">{{ __('app.filter') }}</button>
                        <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('app.clear') }}</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>{{ __('app.mfiles_id') }}</th>
                            <th>{{ __('app.client_code') }}</th>
                            <th>
                                @if(app()->getLocale() == 'ar')
                                {{ __('app.client_name_ar') }}
                                @else
                                {{ __('app.client_name_en') }}
                                @endif
                            </th>
                            <th>
                                @if(app()->getLocale() == 'ar')
                                {{ __('app.lawyer_name_ar') }}
                                @else
                                {{ __('app.lawyer_name_en') }}
                                @endif
                            </th>
                            <th>
                                @if(app()->getLocale() == 'ar')
                                {{ __('app.status_ar') }}
                                @else
                                {{ __('app.status_en') }}
                                @endif
                            </th>
                            <th>
                                @if(app()->getLocale() == 'ar')
                                {{ __('app.cash_or_probono_ar') }}
                                @else
                                {{ __('app.cash_or_probono_en') }}
                                @endif
                            </th>
                            <th>{{ __('app.cases_count') }}</th>
                            @if(app()->getLocale() == 'ar')
                            <th>{{ __('app.actions') }}</th>
                            @else
                            <th class="text-end">{{ __('app.actions') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clients as $client)
                        <tr>
                            <td><a href="{{ route('clients.show', $client) }}">{{ $client->id }}</a></td>
                            <td>
                                @if($client->mfiles_id)
                                    <span class="badge bg-primary">{{ $client->mfiles_id }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($client->client_code)
                                    <span class="badge bg-secondary">{{ $client->client_code }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if(app()->getLocale() == 'ar')
                                {{ $client->client_name_ar }}
                                @else
                                {{ $client->client_name_en }}
                                @endif
                            </td>
                            <td>
                                @if($client->contactLawyer)
                                @if(app()->getLocale() == 'ar')
                                {{ $client->contactLawyer->lawyer_name_ar }}
                                @else
                                {{ $client->contactLawyer->lawyer_name_en }}
                                @endif
                                @else
                                <span class="text-muted">{{ __('app.not_set') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($client->statusRef)
                                <span class="badge bg-success">
                                    @if(app()->getLocale() == 'ar')
                                    {{ $client->statusRef->label_ar }}
                                    @else
                                    {{ $client->statusRef->label_en }}
                                    @endif
                                </span>
                                @else
                                <span class="text-muted">{{ __('app.not_set') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($client->cashOrProbono)
                                <span class="badge bg-warning">
                                    @if(app()->getLocale() == 'ar')
                                    {{ $client->cashOrProbono->label_ar }}
                                    @else
                                    {{ $client->cashOrProbono->label_en }}
                                    @endif
                                </span>
                                @else
                                <span class="text-muted">{{ __('app.not_set') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $client->cases_count }}</span>
                            </td>
                            <td class="{{ app()->getLocale() == 'ar' ? 'text-start' : 'text-end' }}">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('clients.show', $client) }}" class="btn btn-outline-primary btn-sm">{{ __('app.view') }}</a>
                                    @can('clients.edit')
                                    <a href="{{ route('clients.edit', $client) }}" class="btn btn-outline-secondary btn-sm">{{ __('app.edit') }}</a>
                                    @endcan
                                    @can('clients.delete')
                                    <form action="{{ route('clients.destroy', $client) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
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

            {{ $clients->links() }}
        </div>
    </div>
</div>
@endsection
