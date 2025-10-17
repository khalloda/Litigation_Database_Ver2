<?php $__env->startSection('title', __('app.lawyer_details')); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4"><?php echo e(__('app.lawyer_details')); ?></h1>
        <div>
            <a href="<?php echo e(route('lawyers.index')); ?>" class="btn btn-outline-secondary me-2"><?php echo e(__('app.back_to_lawyers')); ?></a>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('admin.users.manage')): ?>
            <a href="<?php echo e(route('lawyers.edit', $lawyer)); ?>" class="btn btn-primary me-2"><?php echo e(__('app.edit_lawyer')); ?></a>
            <form action="<?php echo e(route('lawyers.destroy', $lawyer)); ?>" method="POST" class="d-inline" onsubmit="return confirm('<?php echo e(__('app.confirm_delete_lawyer')); ?>')">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn btn-danger"><?php echo e(__('app.delete')); ?></button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo e(session('success')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo e(__('app.lawyer_details')); ?></h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>ID</strong></td>
                            <td><?php echo e($lawyer->id); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.lawyer_name_ar')); ?></strong></td>
                            <td><?php echo e($lawyer->lawyer_name_ar); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.lawyer_name_en')); ?></strong></td>
                            <td><?php echo e($lawyer->lawyer_name_en); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.lawyer_title')); ?></strong></td>
                            <td>
                                <?php if($lawyer->title): ?>
                                    <?php echo e(app()->getLocale()==='ar' ? $lawyer->title->label_ar : $lawyer->title->label_en); ?>

                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.lawyer_email')); ?></strong></td>
                            <td><?php echo e($lawyer->lawyer_email); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.attendance_track')); ?></strong></td>
                            <td><?php echo e($lawyer->attendance_track ? __('Yes') : __('No')); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo e(__('app.related_assigned_cases')); ?></h5>
                </div>
                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $cases->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $case): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="mb-2">
                        <a href="<?php echo e(route('cases.show', $case)); ?>">
                            <strong><?php echo e($case->matter_name_ar ?? $case->matter_name_en); ?></strong>
                        </a> - <?php echo e($case->matter_status); ?>

                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p><?php echo e(__('app.no_assigned_cases_found')); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/lawyers/show.blade.php ENDPATH**/ ?>