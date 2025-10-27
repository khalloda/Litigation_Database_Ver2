

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?php echo e(__('app.import_session_details')); ?></h2>
        <a href="<?php echo e(route('import.index')); ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> <?php echo e(__('app.back_to_list')); ?>

        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo e(__('app.session_information')); ?></h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <th width="40%"><?php echo e(__('app.session_id')); ?>:</th>
                            <td><code><?php echo e($session->session_id); ?></code></td>
                        </tr>
                        <tr>
                            <th><?php echo e(__('app.status')); ?>:</th>
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
                        </tr>
                        <tr>
                            <th><?php echo e(__('app.table')); ?>:</th>
                            <td><span class="badge bg-secondary"><?php echo e($session->table_name); ?></span></td>
                        </tr>
                        <tr>
                            <th><?php echo e(__('app.file')); ?>:</th>
                            <td><?php echo e($session->original_filename); ?></td>
                        </tr>
                        <tr>
                            <th><?php echo e(__('app.file_size')); ?>:</th>
                            <td><?php echo e($session->file_size_human); ?></td>
                        </tr>
                        <tr>
                            <th><?php echo e(__('app.file_type')); ?>:</th>
                            <td><?php echo e(strtoupper($session->file_type)); ?></td>
                        </tr>
                        <tr>
                            <th><?php echo e(__('app.total_rows')); ?>:</th>
                            <td><strong><?php echo e(number_format($session->total_rows)); ?></strong></td>
                        </tr>
                        <tr>
                            <th><?php echo e(__('app.user')); ?>:</th>
                            <td>
                                <?php echo e($session->user->name); ?>

                                <br><small class="text-muted"><?php echo e($session->user->email); ?></small>
                            </td>
                        </tr>
                        <tr>
                            <th><?php echo e(__('app.created_at')); ?>:</th>
                            <td><?php echo e($session->created_at->format('Y-m-d H:i:s')); ?></td>
                        </tr>
                        <?php if($session->started_at): ?>
                        <tr>
                            <th><?php echo e(__('app.started_at')); ?>:</th>
                            <td><?php echo e($session->started_at->format('Y-m-d H:i:s')); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if($session->completed_at): ?>
                        <tr>
                            <th><?php echo e(__('app.completed_at')); ?>:</th>
                            <td><?php echo e($session->completed_at->format('Y-m-d H:i:s')); ?></td>
                        </tr>
                        <tr>
                            <th><?php echo e(__('app.duration')); ?>:</th>
                            <td><?php echo e($session->duration_seconds); ?> <?php echo e(__('app.seconds')); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

        
        <div class="col-md-8">
            <?php if($session->isCompleted()): ?>
                
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-check-circle"></i> <?php echo e(__('app.import_completed')); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="p-3 border rounded">
                                    <h2 class="text-success mb-0"><?php echo e(number_format($session->imported_count)); ?></h2>
                                    <p class="text-muted mb-0"><?php echo e(__('app.imported')); ?></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded">
                                    <h2 class="text-danger mb-0"><?php echo e(number_format($session->failed_count)); ?></h2>
                                    <p class="text-muted mb-0"><?php echo e(__('app.failed')); ?></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded">
                                    <h2 class="text-warning mb-0"><?php echo e(number_format($session->skipped_count)); ?></h2>
                                    <p class="text-muted mb-0"><?php echo e(__('app.skipped')); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="progress" style="height: 30px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: <?php echo e($session->success_rate); ?>%"
                                     aria-valuenow="<?php echo e($session->success_rate); ?>" aria-valuemin="0" aria-valuemax="100">
                                    <?php echo e($session->success_rate); ?>% <?php echo e(__('app.success')); ?>

                                </div>
                            </div>
                        </div>

                        <?php if(!empty($session->import_errors)): ?>
                            <div class="alert alert-danger mt-3">
                                <h6><?php echo e(__('app.errors')); ?> (<?php echo e(count($session->import_errors)); ?>)</h6>
                                <ul class="mb-0">
                                    <?php $__currentLoopData = array_slice($session->import_errors, 0, 10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e(__('app.row')); ?> <?php echo e($error['row'] ?? 'N/A'); ?>: <?php echo e($error['message'] ?? 'Unknown error'); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(count($session->import_errors) > 10): ?>
                                        <li class="text-muted"><?php echo e(__('app.and_more_errors', ['count' => count($session->import_errors) - 10])); ?></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif($session->isFailed()): ?>
                
                <div class="card mb-4">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fas fa-exclamation-circle"></i> <?php echo e(__('app.import_failed')); ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if(!empty($session->import_errors)): ?>
                            <div class="alert alert-danger">
                                <pre><?php echo e(json_encode($session->import_errors, JSON_PRETTY_PRINT)); ?></pre>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?php echo e(__('app.validation_results')); ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if($session->preflight_error_count > 0 || $session->preflight_warning_count > 0): ?>
                            <div class="row text-center mb-3">
                                <div class="col-md-6">
                                    <div class="p-3 border rounded">
                                        <h3 class="text-danger mb-0"><?php echo e(number_format($session->preflight_error_count)); ?></h3>
                                        <p class="text-muted mb-0"><?php echo e(__('app.errors')); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 border rounded">
                                        <h3 class="text-warning mb-0"><?php echo e(number_format($session->preflight_warning_count)); ?></h3>
                                        <p class="text-muted mb-0"><?php echo e(__('app.warnings')); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-success"><i class="fas fa-check-circle"></i> <?php echo e(__('app.no_validation_errors')); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            
            <?php if($session->backup_file): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-database"></i> <?php echo e(__('app.backup_information')); ?></h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th width="30%"><?php echo e(__('app.backup_file')); ?>:</th>
                                <td><code><?php echo e($session->backup_file); ?></code></td>
                            </tr>
                            <tr>
                                <th><?php echo e(__('app.backup_size')); ?>:</th>
                                <td><?php echo e(number_format($session->backup_size / 1024 / 1024, 2)); ?> MB</td>
                            </tr>
                            <tr>
                                <th><?php echo e(__('app.created_at')); ?>:</th>
                                <td><?php echo e($session->backup_created_at ? $session->backup_created_at->format('Y-m-d H:i:s') : 'N/A'); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            
            <?php if(!empty($session->column_mapping)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-exchange-alt"></i> <?php echo e(__('app.column_mapping')); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th><?php echo e(__('app.source_column')); ?></th>
                                        <th><?php echo e(__('app.target_column')); ?></th>
                                        <th><?php echo e(__('app.transforms')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $session->column_mapping; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $source => $target): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><code><?php echo e($source); ?></code></td>
                                        <td><code><?php echo e($target); ?></code></td>
                                        <td>
                                            <?php if(isset($session->transforms[$source])): ?>
                                                <?php $__currentLoopData = $session->transforms[$source]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transform): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <span class="badge bg-info"><?php echo e($transform); ?></span>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('cancel', $session)): ?>
                        <?php if($session->isInProgress()): ?>
                            <form action="<?php echo e(route('import.cancel', $session)); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>
                                <button type="submit" class="btn btn-warning" onclick="return confirm('<?php echo e(__('app.confirm_cancel_import')); ?>')">
                                    <i class="fas fa-ban"></i> <?php echo e(__('app.cancel_import')); ?>

                                </button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $session)): ?>
                        <form action="<?php echo e(route('import.destroy', $session)); ?>" method="POST" class="d-inline">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-danger" onclick="return confirm('<?php echo e(__('app.confirm_delete')); ?>')">
                                <i class="fas fa-trash"></i> <?php echo e(__('app.delete_session')); ?>

                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/import/show.blade.php ENDPATH**/ ?>