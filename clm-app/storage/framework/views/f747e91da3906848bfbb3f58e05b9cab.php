

<?php $__env->startSection('title', __('app.opponent_details')); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4"><?php echo e(__('app.opponent_details')); ?></h1>
        <div>
            <a href="<?php echo e(route('opponents.index')); ?>" class="btn btn-outline-secondary me-2"><?php echo e(__('app.back')); ?></a>
            <a href="<?php echo e(route('opponents.edit', $opponent)); ?>" class="btn btn-primary"><?php echo e(__('app.edit')); ?></a>
        </div>
    </div>

    <?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo e(session('success')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0"><?php echo e(__('app.opponent_details')); ?></h5>
        </div>
        <div class="card-body">
            <table class="table table-borderless">
                <tr><td><strong>ID</strong></td><td><?php echo e($opponent->id); ?></td></tr>
                <tr><td><strong><?php echo e(__('app.name_ar')); ?></strong></td><td><?php echo e($opponent->opponent_name_ar); ?></td></tr>
                <tr><td><strong><?php echo e(__('app.name_en')); ?></strong></td><td><?php echo e($opponent->opponent_name_en); ?></td></tr>
                <tr><td><strong><?php echo e(__('app.description')); ?></strong></td><td><?php echo e($opponent->description); ?></td></tr>
                <tr><td><strong><?php echo e(__('app.notes')); ?></strong></td><td><?php echo e($opponent->notes); ?></td></tr>
                <tr><td><strong><?php echo e(__('app.is_active')); ?></strong></td><td><?php echo e($opponent->is_active ? __('app.yes') : __('app.no')); ?></td></tr>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>




<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/opponents/show.blade.php ENDPATH**/ ?>