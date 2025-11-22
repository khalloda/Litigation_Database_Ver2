<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">🗑️ Recycle Bin</h4>
                        <div>
                            <span class="badge bg-secondary"><?php echo e($stats['total']); ?> Total</span>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <?php if(session('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo e(session('success')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if(session('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo e(session('error')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">By Type</div>
                                <div class="card-body">
                                    <?php $__currentLoopData = $stats['by_type']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="badge bg-info me-2"><?php echo e($type); ?>: <?php echo e($count); ?></span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">By Status</div>
                                <div class="card-body">
                                    <?php $__currentLoopData = $stats['by_status']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="badge bg-<?php echo e($status === 'trashed' ? 'warning' : ($status === 'restored' ? 'success' : 'secondary')); ?> me-2">
                                            <?php echo e(ucfirst($status)); ?>: <?php echo e($count); ?>

                                        </span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <form method="GET" class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <option value="">All Types</option>
                                <option value="Client" <?php echo e(request('type') === 'Client' ? 'selected' : ''); ?>>Client</option>
                                <option value="CaseModel" <?php echo e(request('type') === 'CaseModel' ? 'selected' : ''); ?>>Case</option>
                                <option value="ClientDocument" <?php echo e(request('type') === 'ClientDocument' ? 'selected' : ''); ?>>Document</option>
                                <option value="Hearing" <?php echo e(request('type') === 'Hearing' ? 'selected' : ''); ?>>Hearing</option>
                                <option value="AdminTask" <?php echo e(request('type') === 'AdminTask' ? 'selected' : ''); ?>>Admin Task</option>
                                <option value="Lawyer" <?php echo e(request('type') === 'Lawyer' ? 'selected' : ''); ?>>Lawyer</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="trashed" <?php echo e(request('status') === 'trashed' ? 'selected' : ''); ?>>Trashed</option>
                                <option value="restored" <?php echo e(request('status') === 'restored' ? 'selected' : ''); ?>>Restored</option>
                                <option value="purged" <?php echo e(request('status') === 'purged' ? 'selected' : ''); ?>>Purged</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="<?php echo e(route('trash.index')); ?>" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </form>

                    
                    <?php if($bundles->isEmpty()): ?>
                        <div class="alert alert-info">
                            No deletion bundles found.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Label</th>
                                        <th>Items</th>
                                        <th>Deleted By</th>
                                        <th>Deleted At</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $bundles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bundle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary"><?php echo e($bundle->root_type); ?></span>
                                            </td>
                                            <td>
                                                <a href="<?php echo e(route('trash.show', $bundle->id)); ?>">
                                                    <?php echo e($bundle->root_label); ?>

                                                </a>
                                                <?php if($bundle->reason): ?>
                                                    <br><small class="text-muted"><?php echo e($bundle->reason); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo e($bundle->cascade_count); ?></td>
                                            <td><?php echo e($bundle->deletedBy->name ?? 'Unknown'); ?></td>
                                            <td><?php echo e($bundle->created_at->format('Y-m-d H:i')); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo e($bundle->status === 'trashed' ? 'warning' : ($bundle->status === 'restored' ? 'success' : 'secondary')); ?>">
                                                    <?php echo e(ucfirst($bundle->status)); ?>

                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="<?php echo e(route('trash.show', $bundle->id)); ?>" class="btn btn-info" title="View">
                                                        View
                                                    </a>
                                                    
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('restore', $bundle)): ?>
                                                        <?php if($bundle->isTrashed()): ?>
                                                            <form method="POST" action="<?php echo e(route('trash.restore', $bundle->id)); ?>" style="display:inline;">
                                                                <?php echo csrf_field(); ?>
                                                                <button type="submit" class="btn btn-success" 
                                                                        onclick="return confirm('Restore this bundle?')" 
                                                                        title="Restore">
                                                                    Restore
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('purge', $bundle)): ?>
                                                        <form method="POST" action="<?php echo e(route('trash.purge', $bundle->id)); ?>" style="display:inline;">
                                                            <?php echo csrf_field(); ?>
                                                            <?php echo method_field('DELETE'); ?>
                                                            <button type="submit" class="btn btn-danger" 
                                                                    onclick="return confirm('Permanently purge this bundle?')" 
                                                                    title="Purge">
                                                                Purge
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>

                        
                        <div class="mt-3">
                            <?php echo e($bundles->appends(request()->query())->links()); ?>

                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views\trash\index.blade.php ENDPATH**/ ?>