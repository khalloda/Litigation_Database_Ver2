<?php $__env->startSection('title', __('app.cases')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4"><?php echo e(__('app.cases')); ?></h1>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('cases.create')): ?>
        <a href="<?php echo e(route('cases.create')); ?>" class="btn btn-primary"><?php echo e(__('app.new_case')); ?></a>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th><?php echo e(__('app.matter_name_ar')); ?></th>
                            <th><?php echo e(__('app.matter_name_en')); ?></th>
                            <th><?php echo e(__('app.client_name_ar')); ?></th>
                            <th><?php echo e(__('app.client_name_en')); ?></th>
                            <th><?php echo e(__('app.matter_status')); ?></th>
                            <?php if(app()->getLocale() == 'ar'): ?>
                            <th><?php echo e(__('app.actions')); ?></th>
                            <?php else: ?>
                            <th class="text-end"><?php echo e(__('app.actions')); ?></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $cases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $case): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><a href="<?php echo e(route('cases.show', $case)); ?>"><?php echo e($case->id); ?></a></td>
                            <td><?php echo e($case->matter_name_ar); ?></td>
                            <td><?php echo e($case->matter_name_en); ?></td>
                            <td><?php echo e($case->client?->client_name_ar); ?></td>
                            <td><?php echo e($case->client?->client_name_en); ?></td>
                            <td><?php echo e($case->matter_status); ?></td>
                            <td class="<?php echo e(app()->getLocale() == 'ar' ? 'text-start' : 'text-end'); ?>">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?php echo e(route('cases.show', $case)); ?>" class="btn btn-outline-primary btn-sm"><?php echo e(__('app.view')); ?></a>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('cases.edit')): ?>
                                    <a href="<?php echo e(route('cases.edit', $case)); ?>" class="btn btn-outline-secondary btn-sm"><?php echo e(__('app.edit')); ?></a>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('cases.delete')): ?>
                                    <form action="<?php echo e(route('cases.destroy', $case)); ?>" method="POST" class="d-inline" onsubmit="return confirm('<?php echo e(__('app.confirm_delete_case')); ?>')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-outline-danger btn-sm"><?php echo e(__('app.delete')); ?></button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <?php echo e($cases->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/cases/index.blade.php ENDPATH**/ ?>