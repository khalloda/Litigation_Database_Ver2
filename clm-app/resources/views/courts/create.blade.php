@extends('layouts.app')

@section('title', __('app.create_court'))

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">{{ __('app.create_court') }}</h1>
        <a href="{{ route('courts.index') }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('courts.store') }}" method="POST">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="court_name_ar" class="form-label">{{ __('app.court_name_ar') }} *</label>
                        <input type="text" class="form-control @error('court_name_ar') is-invalid @enderror" 
                               id="court_name_ar" name="court_name_ar" value="{{ old('court_name_ar') }}">
                        @error('court_name_ar')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="court_name_en" class="form-label">{{ __('app.court_name_en') }} *</label>
                        <input type="text" class="form-control @error('court_name_en') is-invalid @enderror" 
                               id="court_name_en" name="court_name_en" value="{{ old('court_name_en') }}">
                        @error('court_name_en')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="court_circuit" class="form-label">{{ __('app.court_circuit') }}</label>
                        <select class="form-select select2 @error('court_circuit') is-invalid @enderror" 
                                id="court_circuit" name="court_circuit">
                            <option value="">{{ __('app.select_option') }}</option>
                            @foreach($circuitOptions as $option)
                            <option value="{{ $option->id }}" {{ old('court_circuit') == $option->id ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? $option->label_ar : $option->label_en }}
                            </option>
                            @endforeach
                        </select>
                        @error('court_circuit')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="court_circuit_secretary" class="form-label">{{ __('app.court_circuit_secretary') }}</label>
                        <select class="form-select select2 @error('court_circuit_secretary') is-invalid @enderror" 
                                id="court_circuit_secretary" name="court_circuit_secretary">
                            <option value="">{{ __('app.select_option') }}</option>
                            @foreach($secretaryOptions as $option)
                            <option value="{{ $option->id }}" {{ old('court_circuit_secretary') == $option->id ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? $option->label_ar : $option->label_en }}
                            </option>
                            @endforeach
                        </select>
                        @error('court_circuit_secretary')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="court_floor" class="form-label">{{ __('app.court_floor') }}</label>
                        <select class="form-select select2 @error('court_floor') is-invalid @enderror" 
                                id="court_floor" name="court_floor">
                            <option value="">{{ __('app.select_option') }}</option>
                            @foreach($floorOptions as $option)
                            <option value="{{ $option->id }}" {{ old('court_floor') == $option->id ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? $option->label_ar : $option->label_en }}
                            </option>
                            @endforeach
                        </select>
                        @error('court_floor')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="court_hall" class="form-label">{{ __('app.court_hall') }}</label>
                        <select class="form-select select2 @error('court_hall') is-invalid @enderror" 
                                id="court_hall" name="court_hall">
                            <option value="">{{ __('app.select_option') }}</option>
                            @foreach($hallOptions as $option)
                            <option value="{{ $option->id }}" {{ old('court_hall') == $option->id ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? $option->label_ar : $option->label_en }}
                            </option>
                            @endforeach
                        </select>
                        @error('court_hall')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                               value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            {{ __('app.active') }}
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('courts.index') }}" class="btn btn-secondary me-2">{{ __('app.cancel') }}</a>
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
    $('.select2').select2({
        theme: 'bootstrap-5',
        allowClear: true,
        width: '100%'
    });
});
</script>
@endpush

