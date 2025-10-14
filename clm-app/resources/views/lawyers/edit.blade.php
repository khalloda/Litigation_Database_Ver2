@extends('layouts.app')

@section('title', __('app.edit_lawyer'))

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">{{ __('app.edit_lawyer') }}</h1>
        <a href="{{ route('lawyers.show', $lawyer) }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('lawyers.update', $lawyer) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="lawyer_name_ar" class="form-label">{{ __('app.lawyer_name_ar') }}</label>
                        <input type="text" class="form-control @error('lawyer_name_ar') is-invalid @enderror" id="lawyer_name_ar" name="lawyer_name_ar" value="{{ old('lawyer_name_ar', $lawyer->lawyer_name_ar) }}">
                        @error('lawyer_name_ar')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="lawyer_name_en" class="form-label">{{ __('app.lawyer_name_en') }}</label>
                        <input type="text" class="form-control @error('lawyer_name_en') is-invalid @enderror" id="lawyer_name_en" name="lawyer_name_en" value="{{ old('lawyer_name_en', $lawyer->lawyer_name_en) }}">
                        @error('lawyer_name_en')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="lawyer_name_title" class="form-label">{{ __('app.lawyer_title') }}</label>
                        <input type="text" class="form-control @error('lawyer_name_title') is-invalid @enderror" id="lawyer_name_title" name="lawyer_name_title" value="{{ old('lawyer_name_title', $lawyer->lawyer_name_title) }}">
                        @error('lawyer_name_title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="lawyer_email" class="form-label">{{ __('app.lawyer_email') }}</label>
                        <input type="email" class="form-control @error('lawyer_email') is-invalid @enderror" id="lawyer_email" name="lawyer_email" value="{{ old('lawyer_email', $lawyer->lawyer_email) }}">
                        @error('lawyer_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="attendance_track" name="attendance_track" value="1" {{ old('attendance_track', $lawyer->attendance_track) ? 'checked' : '' }}>
                        <label class="form-check-label" for="attendance_track">
                            {{ __('app.attendance_track') }}
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('lawyers.show', $lawyer) }}" class="btn btn-secondary me-2">{{ __('app.cancel') }}</a>
                    <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
