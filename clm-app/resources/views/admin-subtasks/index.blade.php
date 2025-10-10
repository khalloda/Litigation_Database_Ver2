@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('app.admin_subtasks') }}</h5>
                    @can('create', App\Models\AdminSubtask::class)
                    <a href="{{ route('admin-subtasks.create') }}" class="btn btn-primary btn-sm">
                        {{ __('app.new_admin_subtask') }}
                    </a>
                    @endcan
                </div>

                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('app.id') }}</th>
                                    <th>{{ __('app.task') }}</th>
                                    <th>{{ __('app.lawyer') }}</th>
                                    <th>{{ __('app.performer') }}</th>
                                    <th>{{ __('app.next_date') }}</th>
                                    <th>{{ __('app.procedure_date') }}</th>
                                    <th>{{ __('app.report') }}</th>
                                    <th class="{{ app()->getLocale() === 'ar' ? 'text-start' : 'text-end' }}">{{ __('app.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subtasks as $subtask)
                                <tr>
                                    <td>{{ $subtask->id }}</td>
                                    <td>
                                        @if($subtask->task)
                                        <a href="{{ route('admin-tasks.show', $subtask->task) }}">
                                            {{ __('app.task') }} #{{ $subtask->task->id }}
                                            @if($subtask->task->case)
                                            - {{ app()->getLocale() === 'ar' ? $subtask->task->case->matter_name_ar : $subtask->task->case->matter_name_en }}
                                            @endif
                                        </a>
                                        @else
                                        <span class="text-muted">{{ __('app.not_specified') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($subtask->lawyer)
                                        {{ app()->getLocale() === 'ar' ? $subtask->lawyer->lawyer_name_ar : $subtask->lawyer->lawyer_name_en }}
                                        @else
                                        <span class="text-muted">{{ __('app.not_assigned') }}</span>
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
                                    <td class="{{ app()->getLocale() === 'ar' ? 'text-start' : 'text-end' }}">
                                        <div class="btn-group btn-group-sm" role="group">
                                            @can('view', $subtask)
                                            <a href="{{ route('admin-subtasks.show', $subtask) }}" class="btn btn-info btn-sm" title="{{ __('app.view') }}">
                                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                                </svg>
                                            </a>
                                            @endcan
                                            @can('update', $subtask)
                                            <a href="{{ route('admin-subtasks.edit', $subtask) }}" class="btn btn-warning btn-sm" title="{{ __('app.edit') }}">
                                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                                </svg>
                                            </a>
                                            @endcan
                                            @can('delete', $subtask)
                                            <form action="{{ route('admin-subtasks.destroy', $subtask) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="{{ __('app.delete') }}">
                                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">{{ __('app.no_admin_subtasks_found') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center">
                        {{ $subtasks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
