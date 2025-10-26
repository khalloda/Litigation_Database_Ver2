


<?php
    $opponents = $case->opponents()->with('pivot.capacity')->orderBy('display_order')->get();
    $canManage = auth()->user()->can('cases.opponents.edit', $case);
?>

<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-users me-2"></i>
            <?php echo e(__('app.opponents')); ?>

            <span class="badge bg-secondary ms-2"><?php echo e($opponents->count()); ?></span>
        </h5>
        <?php if($canManage): ?>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addOpponentModal">
            <i class="fas fa-plus me-1"></i>
            <?php echo e(__('app.add_opponent')); ?>

        </button>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if($opponents->count() > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover" id="opponentsTable">
                    <thead>
                        <tr>
                            <th width="5%"><?php echo e(__('app.order')); ?></th>
                            <th width="30%"><?php echo e(__('app.opponent_name')); ?></th>
                            <th width="20%"><?php echo e(__('app.capacity')); ?></th>
                            <th width="15%"><?php echo e(__('app.alias')); ?></th>
                            <th width="10%"><?php echo e(__('app.primary')); ?></th>
                            <th width="20%"><?php echo e(__('app.actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $opponents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opponent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $pivot = $opponent->pivot;
                                $capacity = $pivot->capacity;
                            ?>
                            <tr data-opponent-id="<?php echo e($opponent->id); ?>" data-order="<?php echo e($pivot->display_order); ?>">
                                <td>
                                    <?php if($canManage): ?>
                                    <div class="btn-group-vertical btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary btn-sm move-up"
                                                title="<?php echo e(__('app.move_up')); ?>"
                                                <?php echo e($loop->first ? 'disabled' : ''); ?>>
                                            <i class="fas fa-chevron-up"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm move-down"
                                                title="<?php echo e(__('app.move_down')); ?>"
                                                <?php echo e($loop->last ? 'disabled' : ''); ?>>
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                    </div>
                                    <?php else: ?>
                                    <span class="badge bg-light text-dark"><?php echo e($pivot->display_order); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-2">
                                            <?php if($pivot->is_primary): ?>
                                                <i class="fas fa-crown text-warning" title="<?php echo e(__('app.primary_opponent')); ?>"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <div dir="auto" class="fw-bold"><?php echo e($opponent->opponent_name_en); ?></div>
                                            <?php if($opponent->opponent_name_ar && $opponent->opponent_name_ar !== $opponent->opponent_name_en): ?>
                                                <div dir="auto" class="text-muted small"><?php echo e($opponent->opponent_name_ar); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if($capacity): ?>
                                        <span class="badge bg-info" dir="auto">
                                            <?php echo e($capacity->label_en); ?>

                                        </span>
                                        <?php if($capacity->label_ar && $capacity->label_ar !== $capacity->label_en): ?>
                                            <br><small class="text-muted" dir="auto"><?php echo e($capacity->label_ar); ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.no_capacity')); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($pivot->alias_text): ?>
                                        <span class="badge bg-light text-dark" dir="auto"><?php echo e($pivot->alias_text); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($pivot->is_primary): ?>
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-crown me-1"></i>
                                            <?php echo e(__('app.primary')); ?>

                                        </span>
                                    <?php elseif($canManage): ?>
                                        <button type="button" class="btn btn-outline-warning btn-sm set-primary"
                                                data-opponent-id="<?php echo e($opponent->id); ?>"
                                                title="<?php echo e(__('app.set_as_primary')); ?>">
                                            <i class="fas fa-crown"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($canManage): ?>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo e(route('opponents.show', $opponent)); ?>"
                                           class="btn btn-outline-info btn-sm"
                                           title="<?php echo e(__('app.view_opponent')); ?>">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-opponent"
                                                data-opponent-id="<?php echo e($opponent->id); ?>"
                                                data-opponent-name="<?php echo e($opponent->opponent_name_en); ?>"
                                                title="<?php echo e(__('app.remove_opponent')); ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <?php else: ?>
                                    <a href="<?php echo e(route('opponents.show', $opponent)); ?>"
                                       class="btn btn-outline-info btn-sm"
                                       title="<?php echo e(__('app.view_opponent')); ?>">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h6 class="text-muted"><?php echo e(__('app.no_opponents_found')); ?></h6>
                <?php if($canManage): ?>
                <p class="text-muted"><?php echo e(__('app.add_first_opponent')); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if($canManage): ?>

<?php echo $__env->make('cases.partials._add_opponent', ['case' => $case], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
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
        fetch('<?php echo e(route("case-opponents.reorder", $case)); ?>', {
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
                showAlert('success', '<?php echo e(__("app.opponents_reordered_successfully")); ?>');
            } else {
                showAlert('danger', data.message || '<?php echo e(__("app.error_reordering_opponents")); ?>');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', '<?php echo e(__("app.error_reordering_opponents")); ?>');
        });
    }

    function setPrimaryOpponent(opponentId) {
        if (!confirm('<?php echo e(__("app.confirm_set_primary_opponent")); ?>')) return;

        fetch('<?php echo e(route("case-opponents.set-primary", $case)); ?>', {
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
                showAlert('danger', data.message || '<?php echo e(__("app.error_setting_primary_opponent")); ?>');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', '<?php echo e(__("app.error_setting_primary_opponent")); ?>');
        });
    }

    function removeOpponent(opponentId, opponentName) {
        if (!confirm(`<?php echo e(__("app.confirm_remove_opponent")); ?> "${opponentName}"?`)) return;

        fetch('<?php echo e(route("case-opponents.destroy", $case)); ?>', {
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
                showAlert('danger', data.message || '<?php echo e(__("app.error_removing_opponent")); ?>');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', '<?php echo e(__("app.error_removing_opponent")); ?>');
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
<?php $__env->stopPush(); ?>
<?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/cases/partials/_opponents.blade.php ENDPATH**/ ?>