@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ __('app.manage_option_sets') }}</h4>
                    @can('create', App\Models\OptionSet::class)
                    <a href="{{ route('admin.options.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> {{ __('app.create_option_set') }}
                    </a>
                    @endcan
                </div>

                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('app.key') }}</th>
                                    <th>{{ __('app.name') }}</th>
                                    <th>{{ __('app.description') }}</th>
                                    <th>{{ __('app.values_count') }}</th>
                                    <th>{{ __('app.status') }}</th>
                                    <th>{{ __('app.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($optionSets as $set)
                                <tr>
                                    <td><code>{{ $set->key }}</code></td>
                                    <td>{{ $set->name }}</td>
                                    <td>{{ Str::limit($set->description ?? '', 50) }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $set->optionValues->count() }}</span>
                                    </td>
                                    <td>
                                        @if($set->is_active)
                                        <span class="badge bg-success">{{ __('app.active') }}</span>
                                        @else
                                        <span class="badge bg-secondary">{{ __('app.inactive') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @can('view', $set)
                                            <a href="{{ route('admin.options.show', $set) }}"
                                                class="btn btn-sm btn-info"
                                                title="{{ __('app.view') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @endcan

                                            @can('update', $set)
                                            <a href="{{ route('admin.options.edit', $set) }}"
                                                class="btn btn-sm btn-warning"
                                                title="{{ __('app.edit') }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @endcan

                                            @can('delete', $set)
                                            <form action="{{ route('admin.options.destroy', $set) }}"
                                                method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('{{ __('app.confirm_delete_option_set') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="btn btn-sm btn-danger"
                                                    title="{{ __('app.delete') }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        {{ __('app.no_option_sets_found') }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
