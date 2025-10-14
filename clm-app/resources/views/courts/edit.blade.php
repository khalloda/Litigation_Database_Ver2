@extends('layouts.app')

@section('title', __('app.edit_court'))

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">{{ __('app.edit_court') }}</h1>
        <a href="{{ route('courts.show', $court) }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('courts.update', $court) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="court_name_ar" class="form-label">{{ __('app.court_name_ar') }} *</label>
                        <input type="text" class="form-control @error('court_name_ar') is-invalid @enderror"
                               id="court_name_ar" name="court_name_ar" value="{{ old('court_name_ar', $court->court_name_ar) }}">
                        @error('court_name_ar')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="court_name_en" class="form-label">{{ __('app.court_name_en') }} *</label>
                        <input type="text" class="form-control @error('court_name_en') is-invalid @enderror"
                               id="court_name_en" name="court_name_en" value="{{ old('court_name_en', $court->court_name_en) }}">
                        @error('court_name_en')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="court_circuits" class="form-label">{{ __('app.court_circuits') }}</label>
                        <select class="form-select select2-multi @error('court_circuits') is-invalid @enderror" 
                                id="court_circuits" name="court_circuits[]" multiple>
                            @foreach($circuitOptions as $option)
                            <option value="{{ $option->id }}" 
                                {{ in_array($option->id, old('court_circuits', $court->circuits->pluck('id')->toArray())) ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? $option->label_ar : $option->label_en }}
                            </option>
                            @endforeach
                        </select>
                        @error('court_circuits')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="court_secretaries" class="form-label">{{ __('app.court_secretaries') }}</label>
                        <select class="form-select select2-multi @error('court_secretaries') is-invalid @enderror" 
                                id="court_secretaries" name="court_secretaries[]" multiple>
                            @foreach($secretaryOptions as $option)
                            <option value="{{ $option->id }}" 
                                {{ in_array($option->id, old('court_secretaries', $court->secretaries->pluck('id')->toArray())) ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? $option->label_ar : $option->label_en }}
                            </option>
                            @endforeach
                        </select>
                        @error('court_secretaries')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="court_floors" class="form-label">{{ __('app.court_floors') }}</label>
                        <select class="form-select select2-multi @error('court_floors') is-invalid @enderror" 
                                id="court_floors" name="court_floors[]" multiple>
                            @foreach($floorOptions as $option)
                            <option value="{{ $option->id }}" 
                                {{ in_array($option->id, old('court_floors', $court->floors->pluck('id')->toArray())) ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? $option->label_ar : $option->label_en }}
                            </option>
                            @endforeach
                        </select>
                        @error('court_floors')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="court_halls" class="form-label">{{ __('app.court_halls') }}</label>
                        <select class="form-select select2-multi @error('court_halls') is-invalid @enderror" 
                                id="court_halls" name="court_halls[]" multiple>
                            @foreach($hallOptions as $option)
                            <option value="{{ $option->id }}" 
                                {{ in_array($option->id, old('court_halls', $court->halls->pluck('id')->toArray())) ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? $option->label_ar : $option->label_en }}
                            </option>
                            @endforeach
                        </select>
                        @error('court_halls')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                               value="1" {{ old('is_active', $court->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            {{ __('app.active') }}
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('courts.show', $court) }}" class="btn btn-secondary me-2">{{ __('app.cancel') }}</a>
                    <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2-multi').select2({
        theme: 'bootstrap-5',
        multiple: true,
        allowClear: true,
        placeholder: '{{ __("app.select_multiple") }}',
        width: '100%'
    });
});
</script>
@endpush

