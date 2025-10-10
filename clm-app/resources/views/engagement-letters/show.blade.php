@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ __('app.engagement_letter_details') }}</h3>
                    <div class="btn-group" role="group">
                        @can('update', $engagementLetter)
                            <a href="{{ route('engagement-letters.edit', $engagementLetter) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> {{ __('app.edit') }}
                            </a>
                        @endcan
                        @can('delete', $engagementLetter)
                            <form method="POST" action="{{ route('engagement-letters.destroy', $engagementLetter) }}" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> {{ __('app.delete') }}
                                </button>
                            </form>
                        @endcan
                        <a href="{{ route('engagement-letters.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('app.back_to_list') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>{{ __('app.basic_information') }}</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">{{ __('app.contract_number') }}:</th>
                                    <td><strong>{{ $engagementLetter->contract_number }}</strong></td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.client') }}:</th>
                                    <td>
                                        @if($engagementLetter->client)
                                            <div>
                                                <div>{{ $engagementLetter->client->client_name_ar }}</div>
                                                <small class="text-muted">{{ $engagementLetter->client->client_name_en }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">{{ __('app.no_client') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.issue_date') }}:</th>
                                    <td>{{ $engagementLetter->issue_date?->format('Y-m-d') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.expiry_date') }}:</th>
                                    <td>
                                        <span class="{{ $engagementLetter->expiry_date < now() ? 'text-danger' : ($engagementLetter->expiry_date < now()->addDays(30) ? 'text-warning' : '') }}">
                                            {{ $engagementLetter->expiry_date?->format('Y-m-d') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.status') }}:</th>
                                    <td>
                                        @if($engagementLetter->is_active)
                                            <span class="badge bg-success">{{ __('app.active') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('app.inactive') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>{{ __('app.system_information') }}</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">{{ __('app.created_at') }}:</th>
                                    <td>{{ $engagementLetter->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.updated_at') }}:</th>
                                    <td>{{ $engagementLetter->updated_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.created_by') }}:</th>
                                    <td>{{ $engagementLetter->created_by ?? __('app.system') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.updated_by') }}:</th>
                                    <td>{{ $engagementLetter->updated_by ?? __('app.system') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($engagementLetter->expiry_date && $engagementLetter->expiry_date < now())
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle"></i>
                            {{ __('app.engagement_letter_expired') }}
                        </div>
                    @elseif($engagementLetter->expiry_date && $engagementLetter->expiry_date < now()->addDays(30))
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle"></i>
                            {{ __('app.engagement_letter_expiring_soon', ['days' => $engagementLetter->expiry_date->diffInDays(now())]) }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
