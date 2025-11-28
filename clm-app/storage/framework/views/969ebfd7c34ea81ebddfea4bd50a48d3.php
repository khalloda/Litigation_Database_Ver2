<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Bundle Details</h4>
                        <a href="<?php echo e(route('trash.index')); ?>" class="btn btn-sm btn-secondary">← Back to List</a>
                    </div>
                </div>

                <div class="card-body">
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Bundle Information</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th width="150">ID:</th>
                                    <td><code><?php echo e($bundle->id); ?></code></td>
                                </tr>
                                <tr>
                                    <th>Type:</th>
                                    <td><span class="badge bg-primary"><?php echo e($bundle->root_type); ?></span></td>
                                </tr>
                                <tr>
                                    <th>Label:</th>
                                    <td><strong><?php echo e($bundle->root_label); ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Total Items:</th>
                                    <td><?php echo e($bundle->cascade_count); ?></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge bg-<?php echo e($bundle->status === 'trashed' ? 'warning' : ($bundle->status === 'restored' ? 'success' : 'secondary')); ?>">
                                            <?php echo e(ucfirst($bundle->status)); ?>

                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h5>Deletion Metadata</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th width="150">Deleted By:</th>
                                    <td><?php echo e($bundle->deletedBy->name ?? 'Unknown'); ?></td>
                                </tr>
                                <tr>
                                    <th>Deleted At:</th>
                                    <td><?php echo e($bundle->created_at->format('Y-m-d H:i:s')); ?></td>
                                </tr>
                                <tr>
                                    <th>Reason:</th>
                                    <td><?php echo e($bundle->reason ?? 'Not specified'); ?></td>
                                </tr>
                                <?php if($bundle->restored_at): ?>
                                    <tr>
                                        <th>Restored At:</th>
                                        <td><?php echo e($bundle->restored_at->format('Y-m-d H:i:s')); ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if($bundle->ttl_at): ?>
                                    <tr>
                                        <th>Auto-Purge:</th>
                                        <td>
                                            <?php echo e($bundle->ttl_at->format('Y-m-d')); ?>

                                            <?php if($bundle->isExpired()): ?>
                                                <span class="badge bg-danger">Expired</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>

                    
                    <h5>Bundle Contents</h5>
                    <div class="accordion mb-4" id="itemsAccordion">
                        <?php $__currentLoopData = $itemsByType; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modelType => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading<?php echo e($loop->index); ?>">
                                    <button class="accordion-button <?php echo e($loop->index === 0 ? '' : 'collapsed'); ?>" 
                                            type="button" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#collapse<?php echo e($loop->index); ?>">
                                        <?php echo e($modelType); ?> <span class="badge bg-secondary ms-2"><?php echo e($items->count()); ?></span>
                                    </button>
                                </h2>
                                <div id="collapse<?php echo e($loop->index); ?>" 
                                     class="accordion-collapse collapse <?php echo e($loop->index === 0 ? 'show' : ''); ?>" 
                                     data-bs-parent="#itemsAccordion">
                                    <div class="accordion-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Model ID</th>
                                                        <th>Data Preview</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <tr>
                                                            <td><code><?php echo e($item->model_id ?? 'N/A'); ?></code></td>
                                                            <td>
                                                                <small class="text-muted">
                                                                    <?php echo e(json_encode(array_slice($item->payload_json, 0, 3))); ?>

                                                                </small>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    
                    <div class="d-flex gap-2">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('restore', $bundle)): ?>
                            <?php if($bundle->isTrashed()): ?>
                                <button type="button" 
                                        class="btn btn-warning" 
                                        id="dryRunBtn"
                                        data-bundle-id="<?php echo e($bundle->id); ?>">
                                    🔍 Dry Run Restore
                                </button>
                                
                                <form method="POST" action="<?php echo e(route('trash.restore', $bundle->id)); ?>" style="display:inline;">
                                    <?php echo csrf_field(); ?>
                                    <select name="conflict_strategy" class="form-select form-select-sm d-inline-block w-auto">
                                        <option value="skip">Skip Conflicts</option>
                                        <option value="overwrite">Overwrite</option>
                                        <option value="new_copy">New Copy</option>
                                    </select>
                                    <button type="submit" 
                                            class="btn btn-success" 
                                            onclick="return confirm('Restore this bundle?')">
                                        ♻️ Restore Bundle
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('purge', $bundle)): ?>
                            <form method="POST" action="<?php echo e(route('trash.purge', $bundle->id)); ?>" style="display:inline;">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" 
                                        class="btn btn-danger" 
                                        onclick="return confirm('Permanently purge this bundle? This cannot be undone!')">
                                    🔥 Purge Bundle
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="dryRunModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Dry Run Restore Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="dryRunResults">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.getElementById('dryRunBtn')?.addEventListener('click', function() {
    const bundleId = this.dataset.bundleId;
    const modal = new bootstrap.Modal(document.getElementById('dryRunModal'));
    modal.show();
    
    fetch(`/trash/${bundleId}/dry-run`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        },
        body: JSON.stringify({ conflict_strategy: 'skip' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const report = data.report;
            document.getElementById('dryRunResults').innerHTML = `
                <div class="alert alert-info">
                    <h6>Simulation Complete</h6>
                    <p>Root: ${report.root_type} - ${report.root_label}</p>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-success">${report.restored.length}</h3>
                                <p class="mb-0">Would Restore</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-warning">${report.skipped.length}</h3>
                                <p class="mb-0">Would Skip</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-danger">${report.errors.length}</h3>
                                <p class="mb-0">Errors</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        } else {
            document.getElementById('dryRunResults').innerHTML = `
                <div class="alert alert-danger">Error: ${data.error}</div>
            `;
        }
    })
    .catch(error => {
        document.getElementById('dryRunResults').innerHTML = `
            <div class="alert alert-danger">Request failed: ${error.message}</div>
        `;
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views\trash\show.blade.php ENDPATH**/ ?>