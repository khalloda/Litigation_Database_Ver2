@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('app.new_admin_subtask') }}</h5>
                    <a href="{{ route('admin-subtasks.index') }}" class="btn btn-secondary btn-sm">{{ __('app.back') }}</a>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin-subtasks.store') }}" method="POST">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="task_id" class="form-label">{{ __('app.task') }} <span class="text-danger">*</span></label>
                                <select name="task_id" id="task_id" class="form-select @error('task_id') is-invalid @enderror" required>
                                    <option value="">{{ __('app.select_task') }}</option>
                                    @foreach($tasks as $task)
                                    <option value="{{ $task->id }}" {{ old('task_id') == $task->id ? 'selected' : '' }}>
                                        {{ __('app.task') }} #{{ $task->id }}
                                        @if($task->case)
                                        - {{ app()->getLocale() === 'ar' ? $task->case->matter_name_ar : $task->case->matter_name_en }}
                                        @endif
                                    </option>
                                    @endforeach
                                </select>
                                @error('task_id')
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
                                <label for="performer" class="form-label">{{ __('app.performer') }}</label>
                                <input type="text" name="performer" id="performer" class="form-control @error('performer') is-invalid @enderror" value="{{ old('performer') }}">
                                @error('performer')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="report" class="form-label">{{ __('app.report') }}</label>
                                <div class="form-check form-switch">
                                    <input type="checkbox" name="report" id="report" class="form-check-input" value="1" {{ old('report') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="report">{{ __('app.enable_report') }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="next_date" class="form-label">{{ __('app.next_date') }}</label>
                                <input type="date" name="next_date" id="next_date" class="form-control @error('next_date') is-invalid @enderror" value="{{ old('next_date') }}">
                                @error('next_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="procedure_date" class="form-label">{{ __('app.procedure_date') }}</label>
                                <input type="date" name="procedure_date" id="procedure_date" class="form-control @error('procedure_date') is-invalid @enderror" value="{{ old('procedure_date') }}">
                                @error('procedure_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="result" class="form-label">{{ __('app.result') }}</label>
                            <textarea name="result" id="result" rows="4" class="form-control @error('result') is-invalid @enderror">{{ old('result') }}</textarea>
                            @error('result')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('admin-subtasks.index') }}" class="btn btn-secondary">{{ __('app.cancel') }}</a>
                            <button type="submit" class="btn btn-primary">{{ __('app.create') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
