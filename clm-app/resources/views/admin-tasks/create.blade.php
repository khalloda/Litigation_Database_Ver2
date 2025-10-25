@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('app.new_admin_task') }}</h5>
                    <a href="{{ route('admin-tasks.index') }}" class="btn btn-secondary btn-sm">{{ __('app.back') }}</a>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin-tasks.store') }}" method="POST">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="matter_id" class="form-label">{{ __('app.case') }} <span class="text-danger">*</span></label>
                                <select name="matter_id" id="matter_id" class="form-select @error('matter_id') is-invalid @enderror" required>
                                    <option value="">{{ __('app.select_case') }}</option>
                                    @foreach($cases as $case)
                                        <option value="{{ $case->id }}" {{ old('matter_id') == $case->id ? 'selected' : '' }}>
                                            {{ app()->getLocale() === 'ar' ? $case->matter_name_ar : $case->matter_name_en }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('matter_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="lawyer_id" class="form-label">{{ __('app.lawyer') }}</label>
                                <select name="lawyer_id" id="lawyer_id" class="form-select @error('lawyer_id') is-invalid @enderror">
                                    <option value="">{{ __('app.select_lawyer') }}</option>
                                    @foreach($lawyers as $lawyer)
                                        <option value="{{ $lawyer->id }}" {{ old('lawyer_id') == $lawyer->id ? 'selected' : '' }}>
                                            {{ app()->getLocale() === 'ar' ? $lawyer->lawyer_name_ar : $lawyer->lawyer_name_en }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('lawyer_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="status" class="form-label">{{ __('app.status') }}</label>
                                <input type="text" name="status" id="status" class="form-control @error('status') is-invalid @enderror" value="{{ old('status') }}">
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="authority" class="form-label">{{ __('app.authority') }}</label>
                                <input type="text" name="authority" id="authority" class="form-control @error('authority') is-invalid @enderror" value="{{ old('authority') }}">
                                @error('authority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="court" class="form-label">{{ __('app.court') }}</label>
                                <input type="text" name="court" id="court" class="form-control @error('court') is-invalid @enderror" value="{{ old('court') }}">
                                @error('court')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="circuit" class="form-label">{{ __('app.circuit') }}</label>
                                <input type="text" name="circuit" id="circuit" class="form-control @error('circuit') is-invalid @enderror" value="{{ old('circuit') }}">
                                @error('circuit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="performer" class="form-label">{{ __('app.performer') }}</label>
                                <input type="text" name="performer" id="performer" class="form-control @error('performer') is-invalid @enderror" value="{{ old('performer') }}">
                                @error('performer')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="alert" class="form-label">{{ __('app.alert') }}</label>
                                <div class="form-check form-switch">
                                    <input type="checkbox" name="alert" id="alert" class="form-check-input" value="1" {{ old('alert') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="alert">{{ __('app.enable_alert') }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="creation_date" class="form-label">{{ __('app.creation_date') }}</label>
                                <input type="datetime-local" name="creation_date" id="creation_date" class="form-control @error('creation_date') is-invalid @enderror" value="{{ old('creation_date') }}">
                                @error('creation_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="execution_date" class="form-label">{{ __('app.execution_date') }}</label>
                                <input type="datetime-local" name="execution_date" id="execution_date" class="form-control @error('execution_date') is-invalid @enderror" value="{{ old('execution_date') }}">
                                @error('execution_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="last_date" class="form-label">{{ __('app.last_date') }}</label>
                                <input type="date" name="last_date" id="last_date" class="form-control @error('last_date') is-invalid @enderror" value="{{ old('last_date') }}">
                                @error('last_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="required_work" class="form-label">{{ __('app.required_work') }}</label>
                            <textarea name="required_work" id="required_work" rows="3" class="form-control @error('required_work') is-invalid @enderror">{{ old('required_work') }}</textarea>
                            @error('required_work')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="last_follow_up" class="form-label">{{ __('app.last_follow_up') }}</label>
                            <textarea name="last_follow_up" id="last_follow_up" rows="3" class="form-control @error('last_follow_up') is-invalid @enderror">{{ old('last_follow_up') }}</textarea>
                            @error('last_follow_up')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="previous_decision" class="form-label">{{ __('app.previous_decision') }}</label>
                            <textarea name="previous_decision" id="previous_decision" rows="3" class="form-control @error('previous_decision') is-invalid @enderror">{{ old('previous_decision') }}</textarea>
                            @error('previous_decision')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="result" class="form-label">{{ __('app.result') }}</label>
                            <textarea name="result" id="result" rows="3" class="form-control @error('result') is-invalid @enderror">{{ old('result') }}</textarea>
                            @error('result')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('admin-tasks.index') }}" class="btn btn-secondary">{{ __('app.cancel') }}</a>
                            <button type="submit" class="btn btn-primary">{{ __('app.create') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

