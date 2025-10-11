@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>{{ $optionSet->name }}</h2>
                <a href="{{ route('admin.options.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> {{ __('app.back') }}
                </a>
            </div>
        </div>
    </div>

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

    <div class="row">
        <!-- Option Set Details -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('app.option_set_details') }}</h5>
                    @can('update', $optionSet)
                        <a href="{{ route('admin.options.edit', $optionSet) }}" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i> {{ __('app.edit') }}
                        </a>
                    @endcan
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-5">{{ __('app.key') }}:</dt>
                        <dd class="col-sm-7"><code>{{ $optionSet->key }}</code></dd>

                        <dt class="col-sm-5">{{ __('app.name_en') }}:</dt>
                        <dd class="col-sm-7">{{ $optionSet->name_en }}</dd>

                        <dt class="col-sm-5">{{ __('app.name_ar') }}:</dt>
                        <dd class="col-sm-7">{{ $optionSet->name_ar }}</dd>

                        <dt class="col-sm-5">{{ __('app.status') }}:</dt>
                        <dd class="col-sm-7">
                            @if($optionSet->is_active)
                                <span class="badge bg-success">{{ __('app.active') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('app.inactive') }}</span>
                            @endif
                        </dd>

                        @if($optionSet->description_en)
                            <dt class="col-sm-5">{{ __('app.description_en') }}:</dt>
                            <dd class="col-sm-7">{{ $optionSet->description_en }}</dd>
                        @endif

                        @if($optionSet->description_ar)
                            <dt class="col-sm-5">{{ __('app.description_ar') }}:</dt>
                            <dd class="col-sm-7">{{ $optionSet->description_ar }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Add New Value Form -->
            @can('create', App\Models\OptionValue::class)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('app.add_new_value') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.options.values.store', $optionSet) }}" method="POST">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="code" class="form-label">{{ __('app.code') }} <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('code') is-invalid @enderror" 
                                       id="code" 
                                       name="code" 
                                       value="{{ old('code') }}" 
                                       required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="label_en" class="form-label">{{ __('app.label_en') }} <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('label_en') is-invalid @enderror" 
                                       id="label_en" 
                                       name="label_en" 
                                       value="{{ old('label_en') }}" 
                                       required>
                                @error('label_en')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="label_ar" class="form-label">{{ __('app.label_ar') }} <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('label_ar') is-invalid @enderror" 
                                       id="label_ar" 
                                       name="label_ar" 
                                       value="{{ old('label_ar') }}" 
                                       required>
                                @error('label_ar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="position" class="form-label">{{ __('app.position') }}</label>
                                <input type="number" 
                                       class="form-control @error('position') is-invalid @enderror" 
                                       id="position" 
                                       name="position" 
                                       value="{{ old('position', $optionSet->optionValues->count() + 1) }}" 
                                       min="0">
                                @error('position')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 form-check">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    {{ __('app.is_active') }}
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-plus-circle"></i> {{ __('app.add_value') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endcan
        </div>

        <!-- Option Values List -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('app.option_values') }} ({{ $optionSet->optionValues->count() }})</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('app.position') }}</th>
                                    <th>{{ __('app.code') }}</th>
                                    <th>{{ __('app.label_en') }}</th>
                                    <th>{{ __('app.label_ar') }}</th>
                                    <th>{{ __('app.status') }}</th>
                                    <th>{{ __('app.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($optionSet->optionValues->sortBy('position') as $value)
                                    <tr>
                                        <td>{{ $value->position }}</td>
                                        <td><code>{{ $value->code }}</code></td>
                                        <td>{{ $value->label_en }}</td>
                                        <td>{{ $value->label_ar }}</td>
                                        <td>
                                            @if($value->is_active)
                                                <span class="badge bg-success">{{ __('app.active') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('app.inactive') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @can('update', $value)
                                                    <button type="button" 
                                                            class="btn btn-sm btn-warning" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editValueModal{{ $value->id }}"
                                                            title="{{ __('app.edit') }}">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                @endcan
                                                
                                                @can('delete', $value)
                                                    <form action="{{ route('admin.options.values.destroy', $value) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('{{ __('app.confirm_delete_option_value') }}');">
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

                                    <!-- Edit Value Modal -->
                                    @can('update', $value)
                                        <div class="modal fade" id="editValueModal{{ $value->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('admin.options.values.update', $value) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">{{ __('app.edit_value') }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label for="edit_code_{{ $value->id }}" class="form-label">{{ __('app.code') }} <span class="text-danger">*</span></label>
                                                                <input type="text" 
                                                                       class="form-control" 
                                                                       id="edit_code_{{ $value->id }}" 
                                                                       name="code" 
                                                                       value="{{ $value->code }}" 
                                                                       required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="edit_label_en_{{ $value->id }}" class="form-label">{{ __('app.label_en') }} <span class="text-danger">*</span></label>
                                                                <input type="text" 
                                                                       class="form-control" 
                                                                       id="edit_label_en_{{ $value->id }}" 
                                                                       name="label_en" 
                                                                       value="{{ $value->label_en }}" 
                                                                       required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="edit_label_ar_{{ $value->id }}" class="form-label">{{ __('app.label_ar') }} <span class="text-danger">*</span></label>
                                                                <input type="text" 
                                                                       class="form-control" 
                                                                       id="edit_label_ar_{{ $value->id }}" 
                                                                       name="label_ar" 
                                                                       value="{{ $value->label_ar }}" 
                                                                       required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="edit_position_{{ $value->id }}" class="form-label">{{ __('app.position') }}</label>
                                                                <input type="number" 
                                                                       class="form-control" 
                                                                       id="edit_position_{{ $value->id }}" 
                                                                       name="position" 
                                                                       value="{{ $value->position }}" 
                                                                       min="0">
                                                            </div>

                                                            <div class="mb-3 form-check">
                                                                <input type="hidden" name="is_active" value="0">
                                                                <input type="checkbox" 
                                                                       class="form-check-input" 
                                                                       id="edit_is_active_{{ $value->id }}" 
                                                                       name="is_active" 
                                                                       value="1" 
                                                                       {{ $value->is_active ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="edit_is_active_{{ $value->id }}">
                                                                    {{ __('app.is_active') }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                                                            <button type="submit" class="btn btn-primary">{{ __('app.save_changes') }}</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endcan
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            {{ __('app.no_option_values_found') }}
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

