<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?php echo e(__('app.admin_subtask_details')); ?></h5>
                    <div>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $adminSubtask)): ?>
                            <a href="<?php echo e(route('admin-subtasks.edit', $adminSubtask)); ?>" class="btn btn-warning btn-sm">
                                <?php echo e(__('app.edit')); ?>

                            </a>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $adminSubtask)): ?>
                            <form action="<?php echo e(route('admin-subtasks.destroy', $adminSubtask)); ?>" method="POST" class="d-inline" onsubmit="return confirm('<?php echo e(__('app.confirm_delete')); ?>');">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-danger btn-sm"><?php echo e(__('app.delete')); ?></button>
                            </form>
                        <?php endif; ?>
                        <a href="<?php echo e(route('admin-subtasks.index')); ?>" class="btn btn-secondary btn-sm"><?php echo e(__('app.back')); ?></a>
                    </div>
                </div>

                <div class="card-body">
                    <?php if(session('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo e(session('success')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3"><?php echo e(__('app.basic_information')); ?></h6>
                            
                            <div class="mb-3">
                                <strong><?php echo e(__('app.id')); ?>:</strong>
                                <span class="text-muted"><?php echo e($adminSubtask->id); ?></span>
                            </div>

                            <div class="mb-3">
                                <strong><?php echo e(__('app.task')); ?>:</strong>
                                <?php if($adminSubtask->task): ?>
                                    <a href="<?php echo e(route('admin-tasks.show', $adminSubtask->task)); ?>">
                                        <?php echo e(__('app.task')); ?> #<?php echo e($adminSubtask->task->id); ?>

                                        <?php if($adminSubtask->task->case): ?>
                                            - <?php echo e(app()->getLocale() === 'ar' ? $adminSubtask->task->case->matter_name_ar : $adminSubtask->task->case->matter_name_en); ?>

                                        <?php endif; ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted"><?php echo e(__('app.not_specified')); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <strong><?php echo e(__('app.lawyer')); ?>:</strong>
                                <?php if($adminSubtask->lawyer): ?>
                                    <?php echo e(app()->getLocale() === 'ar' ? $adminSubtask->lawyer->lawyer_name_ar : $adminSubtask->lawyer->lawyer_name_en); ?>

                                <?php else: ?>
                                    <span class="text-muted"><?php echo e(__('app.not_assigned')); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <strong><?php echo e(__('app.performer')); ?>:</strong>
                                <span class="text-muted"><?php echo e($adminSubtask->performer ?? '-'); ?></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3"><?php echo e(__('app.dates_and_tracking')); ?></h6>

                            <div class="mb-3">
                                <strong><?php echo e(__('app.next_date')); ?>:</strong>
                                <span class="text-muted"><?php echo e($adminSubtask->next_date ? $adminSubtask->next_date->format('Y-m-d') : '-'); ?></span>
                            </div>

                            <div class="mb-3">
                                <strong><?php echo e(__('app.procedure_date')); ?>:</strong>
                                <span class="text-muted"><?php echo e($adminSubtask->procedure_date ? $adminSubtask->procedure_date->format('Y-m-d') : '-'); ?></span>
                            </div>

                            <div class="mb-3">
                                <strong><?php echo e(__('app.report')); ?>:</strong>
                                <?php if($adminSubtask->report): ?>
                                    <span class="badge bg-success"><?php echo e(__('app.yes')); ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?php echo e(__('app.no')); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if($adminSubtask->result): ?>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 mb-3"><?php echo e(__('app.result')); ?></h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <?php echo e($adminSubtask->result); ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2 mb-3"><?php echo e(__('app.system_information')); ?></h6>
                            
                            <div class="mb-2">
                                <strong><?php echo e(__('app.created_at')); ?>:</strong>
                                <span class="text-muted"><?php echo e($adminSubtask->created_at ? $adminSubtask->created_at->format('Y-m-d H:i:s') : '-'); ?></span>
                            </div>

                            <div class="mb-2">
                                <strong><?php echo e(__('app.updated_at')); ?>:</strong>
                                <span class="text-muted"><?php echo e($adminSubtask->updated_at ? $adminSubtask->updated_at->format('Y-m-d H:i:s') : '-'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views\admin-subtasks\show.blade.php ENDPATH**/ ?>