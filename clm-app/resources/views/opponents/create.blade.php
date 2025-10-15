@extends('layouts.app')

@section('title', __('app.new_opponent'))

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">{{ __('app.new_opponent') }}</h1>
        <a href="{{ route('opponents.index') }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('opponents.store') }}" method="POST">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="opponent_name_ar" class="form-label">{{ __('app.name_ar') }}</label>
                        <input type="text" class="form-control @error('opponent_name_ar') is-invalid @enderror" id="opponent_name_ar" name="opponent_name_ar" value="{{ old('opponent_name_ar') }}">
                        @error('opponent_name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="opponent_name_en" class="form-label">{{ __('app.name_en') }}</label>
                        <input type="text" class="form-control @error('opponent_name_en') is-invalid @enderror" id="opponent_name_en" name="opponent_name_en" value="{{ old('opponent_name_en') }}">
                        @error('opponent_name_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">{{ __('app.description') }}</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">{{ __('app.notes') }}</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">{{ __('app.is_active') }}</label>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('opponents.index') }}" class="btn btn-secondary me-2">{{ __('app.cancel') }}</a>
                    <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


