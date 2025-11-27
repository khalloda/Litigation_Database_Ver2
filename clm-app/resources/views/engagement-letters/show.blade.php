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
                                    <th width="40%">{{ __('app.client') }}:</th>
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
                                    <th>{{ __('app.client_name') }}:</th>
                                    <td><strong>{{ $engagementLetter->client_name ?? __('app.not_set') }}</strong></td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.contract_type') }}:</th>
                                    <td>
                                        @if($engagementLetter->contract_type)
                                        <span class="badge bg-info">{{ $engagementLetter->contract_type }}</span>
                                        @else
                                        <span class="text-muted">{{ __('app.not_set') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.contract_date') }}:</th>
                                    <td>{{ $engagementLetter->contract_date?->format('Y-m-d H:i:s') ?? __('app.not_set') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.status') }}:</th>
                                    <td>
                                        @if($engagementLetter->status)
                                        <span class="badge bg-success">{{ $engagementLetter->status }}</span>
                                        @else
                                        <span class="text-muted">{{ __('app.not_set') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.mfiles_id') }}:</th>
                                    <td>
                                        @if($engagementLetter->mfiles_id)
                                        <span class="badge bg-secondary">{{ $engagementLetter->mfiles_id }}</span>
                                        @else
                                        <span class="text-muted">{{ __('app.not_set') }}</span>
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
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>{{ __('app.contract_details') }}</h5>
                            <div class="card">
                                <div class="card-body">
                                    @if($engagementLetter->contract_details)
                                    <p class="mb-3">{{ $engagementLetter->contract_details }}</p>
                                    @else
                                    <p class="text-muted mb-3">{{ __('app.not_set') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>{{ __('app.contract_structure') }}</h5>
                            <div class="card">
                                <div class="card-body">
                                    @if($engagementLetter->contract_structure)
                                    <p class="mb-3">{{ $engagementLetter->contract_structure }}</p>
                                    @else
                                    <p class="text-muted mb-3">{{ __('app.not_set') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>{{ __('app.matters') }}</h5>
                            <div class="card">
                                <div class="card-body">
                                    @if($engagementLetter->matters)
                                    <p class="mb-3">{{ $engagementLetter->matters }}</p>
                                    @else
                                    <p class="text-muted mb-3">{{ __('app.not_set') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

            </div>
        </div>
    </div>
</div>
</div>
@endsection
