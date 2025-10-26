{{-- Opponents Management Partial --}}
{{-- Displays opponents table with reorder, remove, and set primary functionality --}}

@php
    try {
        // Try to load opponents with a simpler approach
        $opponents = $case->opponents()->get();
        $canManage = auth()->user()->can('cases.opponents.edit', $case);
    } catch (\Exception $e) {
        $opponents = collect();
        $canManage = false;
        \Log::error('Error loading opponents: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
    }
@endphp

<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-users me-2"></i>
            {{ __('app.opponents') }}
            <span class="badge bg-secondary ms-2">{{ $opponents->count() }}</span>
        </h5>
        @if($canManage)
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addOpponentModal">
            <i class="fas fa-plus me-1"></i>
            {{ __('app.add_opponent') }}
        </button>
        @endif
    </div>
    <div class="card-body">
        @if($opponents->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover" id="opponentsTable">
                    <thead>
                        <tr>
                            <th width="5%">{{ __('app.order') }}</th>
                            <th width="30%">{{ __('app.opponent_name') }}</th>
                            <th width="20%">{{ __('app.capacity') }}</th>
                            <th width="15%">{{ __('app.alias') }}</th>
                            <th width="10%">{{ __('app.primary') }}</th>
                            <th width="20%">{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($opponents as $opponent)
                            @php
                                $pivot = $opponent->pivot;
                                $capacity = $pivot->capacity_id ? \App\Models\OptionValue::find($pivot->capacity_id) : null;
                            @endphp
                            <tr data-opponent-id="{{ $opponent->id }}" data-order="{{ $pivot->display_order }}">
                                <td>
                                    @if($canManage)
                                    <div class="btn-group-vertical btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary btn-sm move-up"
                                                title="{{ __('app.move_up') }}"
                                                {{ $loop->first ? 'disabled' : '' }}>
                                            <i class="fas fa-chevron-up"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm move-down"
                                                title="{{ __('app.move_down') }}"
                                                {{ $loop->last ? 'disabled' : '' }}>
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                    </div>
                                    @else
                                    <span class="badge bg-light text-dark">{{ $pivot->display_order }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-2">
                                            @if($pivot->is_primary)
                                                <i class="fas fa-crown text-warning" title="{{ __('app.primary_opponent') }}"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <div dir="auto" class="fw-bold">{{ $opponent->opponent_name_en }}</div>
                                            @if($opponent->opponent_name_ar && $opponent->opponent_name_ar !== $opponent->opponent_name_en)
                                                <div dir="auto" class="text-muted small">{{ $opponent->opponent_name_ar }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($capacity)
                                        <span class="badge bg-info" dir="auto">
                                            {{ $capacity->label_en }}
                                        </span>
                                        @if($capacity->label_ar && $capacity->label_ar !== $capacity->label_en)
                                            <br><small class="text-muted" dir="auto">{{ $capacity->label_ar }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">{{ __('app.no_capacity') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($pivot->alias_text)
                                        <span class="badge bg-light text-dark" dir="auto">{{ $pivot->alias_text }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($pivot->is_primary)
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-crown me-1"></i>
                                            {{ __('app.primary') }}
                                        </span>
                                    @elseif($canManage)
                                        <button type="button" class="btn btn-outline-warning btn-sm set-primary"
                                                data-opponent-id="{{ $opponent->id }}"
                                                title="{{ __('app.set_as_primary') }}">
                                            <i class="fas fa-crown"></i>
                                        </button>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($canManage)
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('opponents.show', $opponent) }}"
                                           class="btn btn-outline-info btn-sm"
                                           title="{{ __('app.view_opponent') }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-opponent"
                                                data-opponent-id="{{ $opponent->id }}"
                                                data-opponent-name="{{ $opponent->opponent_name_en }}"
                                                title="{{ __('app.remove_opponent') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    @else
                                    <a href="{{ route('opponents.show', $opponent) }}"
                                       class="btn btn-outline-info btn-sm"
                                       title="{{ __('app.view_opponent') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h6 class="text-muted">{{ __('app.no_opponents_found') }}</h6>
                @if($canManage)
                <p class="text-muted">{{ __('app.add_first_opponent') }}</p>
                @endif
            </div>
        @endif
    </div>
</div>

@if($canManage)
{{-- Add Opponent Modal --}}
@include('cases.partials._add_opponent', ['case' => $case])
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const opponentsTable = document.getElementById('opponentsTable');
    if (!opponentsTable) return;

    // Move up functionality
    document.querySelectorAll('.move-up').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const prevRow = row.previousElementSibling;
            if (prevRow) {
                swapRows(row, prevRow);
                updateOrder();
            }
        });
    });

    // Move down functionality
    document.querySelectorAll('.move-down').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const nextRow = row.nextElementSibling;
            if (nextRow) {
                swapRows(row, nextRow);
                updateOrder();
            }
        });
    });

    // Set primary functionality
    document.querySelectorAll('.set-primary').forEach(btn => {
        btn.addEventListener('click', function() {
            const opponentId = this.dataset.opponentId;
            setPrimaryOpponent(opponentId);
        });
    });

    // Remove opponent functionality
    document.querySelectorAll('.remove-opponent').forEach(btn => {
        btn.addEventListener('click', function() {
            const opponentId = this.dataset.opponentId;
            const opponentName = this.dataset.opponentName;
            removeOpponent(opponentId, opponentName);
        });
    });

    function swapRows(row1, row2) {
        const parent = row1.parentNode;
        const next1 = row1.nextSibling;
        const next2 = row2.nextSibling;

        parent.insertBefore(row1, next2);
        parent.insertBefore(row2, next1);
    }

    function updateOrder() {
        const rows = opponentsTable.querySelectorAll('tbody tr');
        const order = [];

        rows.forEach((row, index) => {
            const opponentId = row.dataset.opponentId;
            order.push(opponentId);

            // Update move buttons
            const moveUp = row.querySelector('.move-up');
            const moveDown = row.querySelector('.move-down');

            if (moveUp) moveUp.disabled = index === 0;
            if (moveDown) moveDown.disabled = index === rows.length - 1;
        });

        // Send AJAX request to update order
        fetch('{{ route("case-opponents.reorder", $case) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ opponent_ids: order })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', '{{ __("app.opponents_reordered_successfully") }}');
            } else {
                showAlert('danger', data.message || '{{ __("app.error_reordering_opponents") }}');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', '{{ __("app.error_reordering_opponents") }}');
        });
    }

    function setPrimaryOpponent(opponentId) {
        if (!confirm('{{ __("app.confirm_set_primary_opponent") }}')) return;

        fetch('{{ route("case-opponents.set-primary", $case) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ opponent_id: opponentId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Reload to show updated primary status
            } else {
                showAlert('danger', data.message || '{{ __("app.error_setting_primary_opponent") }}');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', '{{ __("app.error_setting_primary_opponent") }}');
        });
    }

    function removeOpponent(opponentId, opponentName) {
        if (!confirm(`{{ __("app.confirm_remove_opponent") }} "${opponentName}"?`)) return;

        fetch('{{ route("case-opponents.destroy", $case) }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ opponent_id: opponentId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Reload to show updated opponents list
            } else {
                showAlert('danger', data.message || '{{ __("app.error_removing_opponent") }}');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', '{{ __("app.error_removing_opponent") }}');
        });
    }

    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        const container = document.querySelector('.container-fluid');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);

            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
    }
});
</script>
@endpush
