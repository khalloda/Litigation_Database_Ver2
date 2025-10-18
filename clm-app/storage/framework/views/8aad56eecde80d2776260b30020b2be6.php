

<?php $__env->startSection('title', __('app.opponents')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4"><?php echo e(__('app.opponents')); ?></h1>
        <a href="<?php echo e(route('opponents.create')); ?>" class="btn btn-primary"><?php echo e(__('app.new_opponent')); ?></a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="q" value="<?php echo e(request('q')); ?>" placeholder="<?php echo e(__('app.search')); ?>">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-secondary" type="submit"><?php echo e(__('app.search')); ?></button>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th><?php echo e(__('app.name_ar')); ?></th>
                            <th><?php echo e(__('app.name_en')); ?></th>
                            <th><?php echo e(__('app.is_active')); ?></th>
                            <th class="text-end"><?php echo e(__('app.actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $opponents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opponent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><a href="<?php echo e(route('opponents.show', $opponent)); ?>"><?php echo e($opponent->id); ?></a></td>
                            <td><?php echo e($opponent->opponent_name_ar); ?></td>
                            <td><?php echo e($opponent->opponent_name_en); ?></td>
                            <td><?php echo e($opponent->is_active ? __('app.yes') : __('app.no')); ?></td>
                            <td class="text-end">
                                <a href="<?php echo e(route('opponents.show', $opponent)); ?>" class="btn btn-outline-primary btn-sm"><?php echo e(__('app.view')); ?></a>
                                <a href="<?php echo e(route('opponents.edit', $opponent)); ?>" class="btn btn-outline-secondary btn-sm"><?php echo e(__('app.edit')); ?></a>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <?php echo e($opponents->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>




<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/opponents/index.blade.php ENDPATH**/ ?>