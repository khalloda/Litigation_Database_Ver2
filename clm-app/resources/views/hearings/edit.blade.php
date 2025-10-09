@extends('layouts.app')

@section('title', __('app.edit_hearing'))

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">{{ __('app.edit_hearing') }}</h1>
        <a href="{{ route('hearings.show', $hearing) }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('hearings.update', $hearing) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="matter_id" class="form-label">{{ __('app.case') }} *</label>
                    <select class="form-select @error('matter_id') is-invalid @enderror" id="matter_id" name="matter_id" required>
                        <option value="">{{ __('app.select_case') }}</option>
                        @foreach($cases as $case)
                        <option value="{{ $case->id }}" {{ (old('matter_id', $hearing->matter_id) == $case->id) ? 'selected' : '' }}>
                            {{ $case->matter_name_ar ?? $case->matter_name_en }} (ID: {{ $case->id }})
                        </option>
                        @endforeach
                    </select>
                    @error('matter_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="date" class="form-label">{{ __('app.hearing_date') }} *</label>
                        <input type="date" class="form-control @error('date') is-invalid @enderror" id="date" name="date" value="{{ old('date', $hearing->date?->format('Y-m-d')) }}" required>
                        @error('date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="time" class="form-label">{{ __('app.hearing_time') }}</label>
                        <input type="time" class="form-control @error('time') is-invalid @enderror" id="time" name="time" value="{{ old('time', $hearing->time) }}">
                        @error('time')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="court" class="form-label">{{ __('app.hearing_court') }}</label>
                        <input type="text" class="form-control @error('court') is-invalid @enderror" id="court" name="court" value="{{ old('court', $hearing->court) }}">
                        @error('court')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="judge" class="form-label">{{ __('app.hearing_judge') }}</label>
                        <input type="text" class="form-control @error('judge') is-invalid @enderror" id="judge" name="judge" value="{{ old('judge', $hearing->judge) }}">
                        @error('judge')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="status" class="form-label">{{ __('app.hearing_status') }}</label>
                        <input type="text" class="form-control @error('status') is-invalid @enderror" id="status" name="status" value="{{ old('status', $hearing->status) }}">
                        @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="next_hearing" class="form-label">{{ __('app.next_hearing_date') }}</label>
                        <input type="date" class="form-control @error('next_hearing') is-invalid @enderror" id="next_hearing" name="next_hearing" value="{{ old('next_hearing', $hearing->next_hearing?->format('Y-m-d')) }}">
                        @error('next_hearing')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">{{ __('app.hearing_notes') }}</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="4">{{ old('notes', $hearing->notes) }}</textarea>
                    @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('hearings.show', $hearing) }}" class="btn btn-secondary me-2">{{ __('app.cancel') }}</a>
                    <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

