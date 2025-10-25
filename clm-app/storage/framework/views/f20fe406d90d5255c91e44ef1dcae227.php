<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?php echo e(__('app.admin_tasks')); ?></h5>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\AdminTask::class)): ?>
                        <a href="<?php echo e(route('admin-tasks.create')); ?>" class="btn btn-primary btn-sm">
                            <?php echo e(__('app.new_admin_task')); ?>

                        </a>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <?php if(session('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo e(session('success')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('app.id')); ?></th>
                                    <th><?php echo e(__('app.case')); ?></th>
                                    <th><?php echo e(__('app.lawyer')); ?></th>
                                    <th><?php echo e(__('app.status')); ?></th>
                                    <th><?php echo e(__('app.authority')); ?></th>
                                    <th><?php echo e(__('app.execution_date')); ?></th>
                                    <th class="<?php echo e(app()->getLocale() === 'ar' ? 'text-start' : 'text-end'); ?>"><?php echo e(__('app.actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($task->id); ?></td>
                                        <td>
                                            <?php if($task->case): ?>
                                                <a href="<?php echo e(route('cases.show', $task->case)); ?>">
                                                    <?php echo e(app()->getLocale() === 'ar' ? $task->case->matter_name_ar : $task->case->matter_name_en); ?>

                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted"><?php echo e(__('app.not_specified')); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($task->lawyer): ?>
                                                <?php echo e(app()->getLocale() === 'ar' ? $task->lawyer->lawyer_name_ar : $task->lawyer->lawyer_name_en); ?>

                                            <?php else: ?>
                                                <span class="text-muted"><?php echo e(__('app.not_assigned')); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($task->status): ?>
                                                <span class="badge bg-secondary"><?php echo e($task->status); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($task->authority ?? '-'); ?></td>
                                        <td><?php echo e($task->execution_date ? $task->execution_date->format('Y-m-d') : '-'); ?></td>
                                        <td class="<?php echo e(app()->getLocale() === 'ar' ? 'text-start' : 'text-end'); ?>">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view', $task)): ?>
                                                    <a href="<?php echo e(route('admin-tasks.show', $task)); ?>" class="btn btn-info btn-sm" title="<?php echo e(__('app.view')); ?>">
                                                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $task)): ?>
                                                    <a href="<?php echo e(route('admin-tasks.edit', $task)); ?>" class="btn btn-warning btn-sm" title="<?php echo e(__('app.edit')); ?>">
                                                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                                        </svg>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $task)): ?>
                                                    <form action="<?php echo e(route('admin-tasks.destroy', $task)); ?>" method="POST" class="d-inline" onsubmit="return confirm('<?php echo e(__('app.confirm_delete')); ?>');">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <button type="submit" class="btn btn-danger btn-sm" title="<?php echo e(__('app.delete')); ?>">
                                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted"><?php echo e(__('app.no_admin_tasks_found')); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center">
                        <?php echo e($tasks->links()); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/admin-tasks/index.blade.php ENDPATH**/ ?>