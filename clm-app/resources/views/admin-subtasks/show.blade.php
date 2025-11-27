@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('app.admin_subtask_details') }}</h5>
                    <div>
                        @can('update', $adminSubtask)
                            <a href="{{ route('admin-subtasks.edit', $adminSubtask) }}" class="btn btn-warning btn-sm">
                                {{ __('app.edit') }}
                            </a>
                        @endcan
                        @can('delete', $adminSubtask)
                            <form action="{{ route('admin-subtasks.destroy', $adminSubtask) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">{{ __('app.delete') }}</button>
                            </form>
                        @endcan
                        <a href="{{ route('admin-subtasks.index') }}" class="btn btn-secondary btn-sm">{{ __('app.back') }}</a>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">{{ __('app.basic_information') }}</h6>
                            
                            <div class="mb-3">
                                <strong>{{ __('app.id') }}:</strong>
                                <span class="text-muted">{{ $adminSubtask->id }}</span>
                            </div>

                            <div class="mb-3">
                                <strong>{{ __('app.task') }}:</strong>
                                @if($adminSubtask->task)
                                    <a href="{{ route('admin-tasks.show', $adminSubtask->task) }}">
                                        {{ __('app.task') }} #{{ $adminSubtask->task->id }}
                                        @if($adminSubtask->task->case)
                                            - {{ app()->getLocale() === 'ar' ? $adminSubtask->task->case->matter_name_ar : $adminSubtask->task->case->matter_name_en }}
                                        @endif
                                    </a>
                                @else
                                    <span class="text-muted">{{ __('app.not_specified') }}</span>
                                @endif
                            </div>

                            <div class="mb-3">
                                <strong>{{ __('app.lawyer') }}:</strong>
                                @if($adminSubtask->lawyer)
                                    {{ app()->getLocale() === 'ar' ? $adminSubtask->lawyer->lawyer_name_ar : $adminSubtask->lawyer->lawyer_name_en }}
                                @else
                                    <span class="text-muted">{{ __('app.not_assigned') }}</span>
                                @endif
                            </div>

                            <div class="mb-3">
                                <strong>{{ __('app.performer') }}:</strong>
                                <span class="text-muted">{{ $adminSubtask->performer ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">{{ __('app.dates_and_tracking') }}</h6>

                            <div class="mb-3">
                                <strong>{{ __('app.next_date') }}:</strong>
                                <span class="text-muted">{{ $adminSubtask->next_date ? $adminSubtask->next_date->format('Y-m-d') : '-' }}</span>
                            </div>

                            <div class="mb-3">
                                <strong>{{ __('app.procedure_date') }}:</strong>
                                <span class="text-muted">{{ $adminSubtask->procedure_date ? $adminSubtask->procedure_date->format('Y-m-d') : '-' }}</span>
                            </div>

                            <div class="mb-3">
                                <strong>{{ __('app.report') }}:</strong>
                                @if($adminSubtask->report)
                                    <span class="badge bg-success">{{ __('app.yes') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('app.no') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($adminSubtask->result)
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 mb-3">{{ __('app.result') }}</h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        {{ $adminSubtask->result }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2 mb-3">{{ __('app.system_information') }}</h6>
                            
                            <div class="mb-2">
                                <strong>{{ __('app.created_at') }}:</strong>
                                <span class="text-muted">{{ $adminSubtask->created_at ? $adminSubtask->created_at->format('Y-m-d H:i:s') : '-' }}</span>
                            </div>

                            <div class="mb-2">
                                <strong>{{ __('app.updated_at') }}:</strong>
                                <span class="text-muted">{{ $adminSubtask->updated_at ? $adminSubtask->updated_at->format('Y-m-d H:i:s') : '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

