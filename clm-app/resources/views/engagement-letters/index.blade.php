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
                                    <th>{{ __('app.contract_number') }}</th>
                                    <th>{{ __('app.client') }}</th>
                                    <th>{{ __('app.issue_date') }}</th>
                                    <th>{{ __('app.expiry_date') }}</th>
                                    <th>{{ __('app.status') }}</th>
                                    <th>{{ __('app.created_at') }}</th>
                                    <th class="text-end">{{ __('app.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($engagementLetters as $letter)
                                <tr>
                                    <td>
                                        <strong>{{ $letter->contract_number }}</strong>
                                    </td>
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
                                    <td>{{ $letter->issue_date?->format('Y-m-d') }}</td>
                                    <td>
                                        <span class="{{ $letter->expiry_date < now() ? 'text-danger' : ($letter->expiry_date < now()->addDays(30) ? 'text-warning' : '') }}">
                                            {{ $letter->expiry_date?->format('Y-m-d') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($letter->is_active)
                                        <span class="badge bg-success">{{ __('app.active') }}</span>
                                        @else
                                        <span class="badge bg-secondary">{{ __('app.inactive') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $letter->created_at->format('Y-m-d H:i') }}</td>
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
