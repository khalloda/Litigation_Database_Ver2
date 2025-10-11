@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ __('app.edit_option_set') }}</h4>
                    <a href="{{ route('admin.options.show', $optionSet) }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> {{ __('app.back') }}
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.options.update', $optionSet) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="key" class="form-label">{{ __('app.key') }}</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="key" 
                                   value="{{ $optionSet->key }}" 
                                   disabled>
                            <div class="form-text">{{ __('app.key_cannot_be_changed') }}</div>
                        </div>

                        <div class="mb-3">
                            <label for="name_en" class="form-label">{{ __('app.name_en') }} <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name_en') is-invalid @enderror" 
                                   id="name_en" 
                                   name="name_en" 
                                   value="{{ old('name_en', $optionSet->name_en) }}" 
                                   required>
                            @error('name_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="name_ar" class="form-label">{{ __('app.name_ar') }} <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name_ar') is-invalid @enderror" 
                                   id="name_ar" 
                                   name="name_ar" 
                                   value="{{ old('name_ar', $optionSet->name_ar) }}" 
                                   required>
                            @error('name_ar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description_en" class="form-label">{{ __('app.description_en') }}</label>
                            <textarea class="form-control @error('description_en') is-invalid @enderror" 
                                      id="description_en" 
                                      name="description_en" 
                                      rows="3">{{ old('description_en', $optionSet->description_en) }}</textarea>
                            @error('description_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description_ar" class="form-label">{{ __('app.description_ar') }}</label>
                            <textarea class="form-control @error('description_ar') is-invalid @enderror" 
                                      id="description_ar" 
                                      name="description_ar" 
                                      rows="3">{{ old('description_ar', $optionSet->description_ar) }}</textarea>
                            @error('description_ar')
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
                                   {{ old('is_active', $optionSet->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                {{ __('app.is_active') }}
                            </label>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('admin.options.show', $optionSet) }}" class="btn btn-secondary">
                                {{ __('app.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> {{ __('app.save_changes') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

