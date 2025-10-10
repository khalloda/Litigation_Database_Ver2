@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('app.admin_task_details') }}</h5>
                    <div>
                        @can('update', $adminTask)
                            <a href="{{ route('admin-tasks.edit', $adminTask) }}" class="btn btn-warning btn-sm">
                                {{ __('app.edit') }}
                            </a>
                        @endcan
                        @can('delete', $adminTask)
                            <form action="{{ route('admin-tasks.destroy', $adminTask) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">{{ __('app.delete') }}</button>
                            </form>
                        @endcan
                        <a href="{{ route('admin-tasks.index') }}" class="btn btn-secondary btn-sm">{{ __('app.back') }}</a>
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
                                <span class="text-muted">{{ $adminTask->id }}</span>
                            </div>

                            <div class="mb-3">
                                <strong>{{ __('app.case') }}:</strong>
                                @if($adminTask->case)
                                    <a href="{{ route('cases.show', $adminTask->case) }}">
                                        {{ app()->getLocale() === 'ar' ? $adminTask->case->matter_name_ar : $adminTask->case->matter_name_en }}
                                    </a>
                                @else
                                    <span class="text-muted">{{ __('app.not_specified') }}</span>
                                @endif
                            </div>

                            <div class="mb-3">
                                <strong>{{ __('app.lawyer') }}:</strong>
                                @if($adminTask->lawyer)
                                    {{ app()->getLocale() === 'ar' ? $adminTask->lawyer->lawyer_name_ar : $adminTask->lawyer->lawyer_name_en }}
                                @else
                                    <span class="text-muted">{{ __('app.not_assigned') }}</span>
                                @endif
                            </div>

                            <div class="mb-3">
                                <strong>{{ __('app.status') }}:</strong>
                                @if($adminTask->status)
                                    <span class="badge bg-secondary">{{ $adminTask->status }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>

                            <div class="mb-3">
                                <strong>{{ __('app.authority') }}:</strong>
                                <span class="text-muted">{{ $adminTask->authority ?? '-' }}</span>
                            </div>

                            <div class="mb-3">
                                <strong>{{ __('app.court') }}:</strong>
                                <span class="text-muted">{{ $adminTask->court ?? '-' }}</span>
                            </div>

                            <div class="mb-3">
                                <strong>{{ __('app.circuit') }}:</strong>
                                <span class="text-muted">{{ $adminTask->circuit ?? '-' }}</span>
                            </div>

                            <div class="mb-3">
                                <strong>{{ __('app.performer') }}:</strong>
                                <span class="text-muted">{{ $adminTask->performer ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">{{ __('app.dates_and_tracking') }}</h6>

                            <div class="mb-3">
                                <strong>{{ __('app.creation_date') }}:</strong>
                                <span class="text-muted">{{ $adminTask->creation_date ? $adminTask->creation_date->format('Y-m-d H:i') : '-' }}</span>
                            </div>

                            <div class="mb-3">
                                <strong>{{ __('app.execution_date') }}:</strong>
                                <span class="text-muted">{{ $adminTask->execution_date ? $adminTask->execution_date->format('Y-m-d H:i') : '-' }}</span>
                            </div>

                            <div class="mb-3">
                                <strong>{{ __('app.last_date') }}:</strong>
                                <span class="text-muted">{{ $adminTask->last_date ? $adminTask->last_date->format('Y-m-d') : '-' }}</span>
                            </div>

                            <div class="mb-3">
                                <strong>{{ __('app.alert') }}:</strong>
                                @if($adminTask->alert)
                                    <span class="badge bg-warning">{{ __('app.yes') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('app.no') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($adminTask->required_work)
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 mb-3">{{ __('app.required_work') }}</h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        {{ $adminTask->required_work }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($adminTask->last_follow_up)
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 mb-3">{{ __('app.last_follow_up') }}</h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        {{ $adminTask->last_follow_up }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($adminTask->previous_decision)
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 mb-3">{{ __('app.previous_decision') }}</h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        {{ $adminTask->previous_decision }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($adminTask->result)
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 mb-3">{{ __('app.result') }}</h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        {{ $adminTask->result }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($adminTask->subtasks && $adminTask->subtasks->count() > 0)
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 mb-3">{{ __('app.subtasks') }} ({{ $adminTask->subtasks->count() }})</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>{{ __('app.id') }}</th>
                                                <th>{{ __('app.lawyer') }}</th>
                                                <th>{{ __('app.performer') }}</th>
                                                <th>{{ __('app.next_date') }}</th>
                                                <th>{{ __('app.procedure_date') }}</th>
                                                <th>{{ __('app.report') }}</th>
                                                <th>{{ __('app.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($adminTask->subtasks as $subtask)
                                                <tr>
                                                    <td>{{ $subtask->id }}</td>
                                                    <td>
                                                        @if($subtask->lawyer)
                                                            {{ app()->getLocale() === 'ar' ? $subtask->lawyer->lawyer_name_ar : $subtask->lawyer->lawyer_name_en }}
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $subtask->performer ?? '-' }}</td>
                                                    <td>{{ $subtask->next_date ? $subtask->next_date->format('Y-m-d') : '-' }}</td>
                                                    <td>{{ $subtask->procedure_date ? $subtask->procedure_date->format('Y-m-d') : '-' }}</td>
                                                    <td>
                                                        @if($subtask->report)
                                                            <span class="badge bg-success">{{ __('app.yes') }}</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ __('app.no') }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin-subtasks.show', $subtask) }}" class="btn btn-info btn-sm">{{ __('app.view') }}</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2 mb-3">{{ __('app.system_information') }}</h6>
                            
                            <div class="mb-2">
                                <strong>{{ __('app.created_at') }}:</strong>
                                <span class="text-muted">{{ $adminTask->created_at ? $adminTask->created_at->format('Y-m-d H:i:s') : '-' }}</span>
                            </div>

                            <div class="mb-2">
                                <strong>{{ __('app.updated_at') }}:</strong>
                                <span class="text-muted">{{ $adminTask->updated_at ? $adminTask->updated_at->format('Y-m-d H:i:s') : '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

