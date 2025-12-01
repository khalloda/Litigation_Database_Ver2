@php
    $hearingsCount = $case->hearings->count();
    $tasksCount = $case->adminTasks()->count();
    $documentsCount = $case->documents->count();
@endphp

<div class="tab-pane {{ isset($active) && $active ? 'show active' : '' }}" id="documents-tab" role="tabpanel" aria-labelledby="documents-tab-btn" style="{{ isset($active) && $active ? '' : 'display: none;' }}">
    <div class="row g-3">
        {{-- Related Hearings --}}
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ __('app.related_hearings') }}</h6>
                    <span class="badge bg-primary">{{ $hearingsCount }}</span>
                </div>
                <div class="card-body">
                    @forelse($case->hearings->take(5) as $hearing)
                    <div class="mb-2 pb-2 border-bottom">
                        <div><strong>{{ $hearing->hearing_date?->format('Y-m-d') ?? '-' }}</strong></div>
                        <div class="text-muted small">{{ $hearing->hearing_type ?? '-' }}</div>
                    </div>
                    @empty
                    <p class="text-muted mb-0">{{ __('app.no_hearings_found') }}</p>
                    @endforelse
                    @if($hearingsCount > 5)
                    <div class="mt-2">
                        <a href="#" class="btn btn-sm btn-outline-primary">{{ __('app.view_all') }} ({{ $hearingsCount }})</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Related Tasks --}}
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ __('app.related_tasks') }}</h6>
                    <span class="badge bg-info">{{ $tasksCount }}</span>
                </div>
                <div class="card-body">
                    @forelse($case->adminTasks()->limit(5)->get() as $task)
                    <div class="mb-2 pb-2 border-bottom">
                        <div><strong>{{ $task->task_name ?? '-' }}</strong></div>
                        <div class="text-muted small">{{ $task->status ?? '-' }}</div>
                    </div>
                    @empty
                    <p class="text-muted mb-0">{{ __('app.no_tasks_found') }}</p>
                    @endforelse
                    @if($tasksCount > 5)
                    <div class="mt-2">
                        <a href="#" class="btn btn-sm btn-outline-info">{{ __('app.view_all') }} ({{ $tasksCount }})</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Related Documents --}}
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ __('app.related_documents') }}</h6>
                    <span class="badge bg-success">{{ $documentsCount }}</span>
                </div>
                <div class="card-body">
                    @forelse($case->documents->take(5) as $document)
                    <div class="mb-2 pb-2 border-bottom">
                        <div><strong>{{ $document->document_name ?? '-' }}</strong></div>
                        <div class="text-muted small">{{ $document->document_type ?? '-' }}</div>
                    </div>
                    @empty
                    <p class="text-muted mb-0">{{ __('app.no_documents_found') }}</p>
                    @endforelse
                    @if($documentsCount > 5)
                    <div class="mt-2">
                        <a href="#" class="btn btn-sm btn-outline-success">{{ __('app.view_all') }} ({{ $documentsCount }})</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

