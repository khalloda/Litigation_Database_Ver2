

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?php echo e(__('app.import_sessions')); ?></h2>
        <a href="<?php echo e(route('import.upload')); ?>" class="btn btn-primary">
            <i class="fas fa-upload"></i> <?php echo e(__('app.new_import')); ?>

        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('import.index')); ?>" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label"><?php echo e(__('app.status')); ?></label>
                    <select name="status" id="status" class="form-select">
                        <option value=""><?php echo e(__('app.all_statuses')); ?></option>
                        <option value="uploaded" <?php echo e(request('status') == 'uploaded' ? 'selected' : ''); ?>><?php echo e(__('app.uploaded')); ?></option>
                        <option value="mapped" <?php echo e(request('status') == 'mapped' ? 'selected' : ''); ?>><?php echo e(__('app.mapped')); ?></option>
                        <option value="validated" <?php echo e(request('status') == 'validated' ? 'selected' : ''); ?>><?php echo e(__('app.validated')); ?></option>
                        <option value="importing" <?php echo e(request('status') == 'importing' ? 'selected' : ''); ?>><?php echo e(__('app.importing')); ?></option>
                        <option value="completed" <?php echo e(request('status') == 'completed' ? 'selected' : ''); ?>><?php echo e(__('app.completed')); ?></option>
                        <option value="failed" <?php echo e(request('status') == 'failed' ? 'selected' : ''); ?>><?php echo e(__('app.failed')); ?></option>
                        <option value="cancelled" <?php echo e(request('status') == 'cancelled' ? 'selected' : ''); ?>><?php echo e(__('app.cancelled')); ?></option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="table" class="form-label"><?php echo e(__('app.table')); ?></label>
                    <select name="table" id="table" class="form-select">
                        <option value=""><?php echo e(__('app.all_tables')); ?></option>
                        <?php $__currentLoopData = $enabledTables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($table); ?>" <?php echo e(request('table') == $table ? 'selected' : ''); ?>>
                                <?php echo e(ucfirst(str_replace('_', ' ', $table))); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter"></i> <?php echo e(__('app.filter')); ?>

                    </button>
                    <a href="<?php echo e(route('import.index')); ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i> <?php echo e(__('app.reset')); ?>

                    </a>
                </div>
            </form>
        </div>
    </div>

    
    <div class="card">
        <div class="card-body">
            <?php if($sessions->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?php echo e(__('app.session_id')); ?></th>
                                <th><?php echo e(__('app.file')); ?></th>
                                <th><?php echo e(__('app.table')); ?></th>
                                <th><?php echo e(__('app.status')); ?></th>
                                <th><?php echo e(__('app.progress')); ?></th>
                                <th><?php echo e(__('app.user')); ?></th>
                                <th><?php echo e(__('app.created_at')); ?></th>
                                <th class="text-end"><?php echo e(__('app.actions')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <code class="text-muted"><?php echo e(Str::limit($session->session_id, 8, '')); ?></code>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo e(Str::limit($session->original_filename, 30)); ?></strong>
                                    </div>
                                    <small class="text-muted">
                                        <?php echo e($session->file_size_human); ?> • <?php echo e(strtoupper($session->file_type)); ?>

                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo e($session->table_name); ?></span>
                                </td>
                                <td>
                                    <?php
                                        $statusColors = [
                                            'uploaded' => 'secondary',
                                            'mapped' => 'info',
                                            'validated' => 'warning',
                                            'importing' => 'primary',
                                            'completed' => 'success',
                                            'failed' => 'danger',
                                            'cancelled' => 'dark'
                                        ];
                                    ?>
                                    <span class="badge bg-<?php echo e($statusColors[$session->status] ?? 'secondary'); ?>">
                                        <?php echo e(__(ucfirst($session->status))); ?>

                                    </span>
                                </td>
                                <td>
                                    <?php if($session->isCompleted()): ?>
                                        <div class="text-success">
                                            <i class="fas fa-check-circle"></i>
                                            <?php echo e($session->imported_count); ?> / <?php echo e($session->total_rows); ?>

                                        </div>
                                        <?php if($session->failed_count > 0): ?>
                                            <small class="text-danger"><?php echo e($session->failed_count); ?> <?php echo e(__('app.failed')); ?></small>
                                        <?php endif; ?>
                                    <?php elseif($session->isFailed()): ?>
                                        <div class="text-danger">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <?php echo e(__('app.failed')); ?>

                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><?php echo e($session->user->name); ?></div>
                                    <small class="text-muted"><?php echo e($session->user->email); ?></small>
                                </td>
                                <td><?php echo e($session->created_at->format('Y-m-d H:i')); ?></td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view', $session)): ?>
                                            <a href="<?php echo e(route('import.show', $session)); ?>" class="btn btn-sm btn-outline-primary" title="<?php echo e(__('app.view')); ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('cancel', $session)): ?>
                                            <?php if($session->isInProgress()): ?>
                                                <form action="<?php echo e(route('import.cancel', $session)); ?>" method="POST" class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('PUT'); ?>
                                                    <button type="submit" class="btn btn-sm btn-outline-warning" 
                                                            onclick="return confirm('<?php echo e(__('app.confirm_cancel_import')); ?>')"
                                                            title="<?php echo e(__('app.cancel')); ?>">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $session)): ?>
                                            <form action="<?php echo e(route('import.destroy', $session)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('<?php echo e(__('app.confirm_delete')); ?>')"
                                                        title="<?php echo e(__('app.delete')); ?>">
                                                    <i class="fas fa-trash"></i>
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
                    <?php echo e($sessions->links()); ?>

                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted"><?php echo e(__('app.no_import_sessions_found')); ?></p>
                    <a href="<?php echo e(route('import.upload')); ?>" class="btn btn-primary">
                        <i class="fas fa-upload"></i> <?php echo e(__('app.start_new_import')); ?>

                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/import/index.blade.php ENDPATH**/ ?>