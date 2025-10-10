@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ __('app.engagement_letters') }}</h3>
                    @can('create', App\Models\EngagementLetter::class)
                    <a href="{{ route('engagement-letters.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ __('app.new_engagement_letter') }}
                    </a>
                    @endcan
                </div>
                <div class="card-body">
                    @if($engagementLetters->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('app.client') }}</th>
                                    <th>{{ __('app.client_name') }}</th>
                                    <th>{{ __('app.contract_type') }}</th>
                                    <th>{{ __('app.contract_date') }}</th>
                                    <th>{{ __('app.status') }}</th>
                                    <th>{{ __('app.mfiles_id') }}</th>
                                    <th class="text-end">{{ __('app.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($engagementLetters as $letter)
                                <tr>
                                    <td>
                                        @if($letter->client)
                                        <div>
                                            <div>{{ $letter->client->client_name_ar }}</div>
                                            <small class="text-muted">{{ $letter->client->client_name_en }}</small>
                                        </div>
                                        @else
                                        <span class="text-muted">{{ __('app.no_client') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $letter->client_name ?? __('app.not_set') }}</strong>
                                    </td>
                                    <td>
                                        @if($letter->contract_type)
                                        <span class="badge bg-info">{{ $letter->contract_type }}</span>
                                        @else
                                        <span class="text-muted">{{ __('app.not_set') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $letter->contract_date?->format('Y-m-d') ?? __('app.not_set') }}</td>
                                    <td>
                                        @if($letter->status)
                                        <span class="badge bg-success">{{ $letter->status }}</span>
                                        @else
                                        <span class="text-muted">{{ __('app.not_set') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($letter->mfiles_id)
                                        <span class="badge bg-secondary">{{ $letter->mfiles_id }}</span>
                                        @else
                                        <span class="text-muted">{{ __('app.not_set') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            @can('view', $letter)
                                            <a href="{{ route('engagement-letters.show', $letter) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @endcan
                                            @can('update', $letter)
                                            <a href="{{ route('engagement-letters.edit', $letter) }}" class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                            @can('delete', $letter)
                                            <form method="POST" action="{{ route('engagement-letters.destroy', $letter) }}" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center">
                        {{ $engagementLetters->links() }}
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">{{ __('app.no_engagement_letters') }}</h5>
                        <p class="text-muted">{{ __('app.no_engagement_letters_description') }}</p>
                        @can('create', App\Models\EngagementLetter::class)
                        <a href="{{ route('engagement-letters.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> {{ __('app.create_first_engagement_letter') }}
                        </a>
                        @endcan
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
