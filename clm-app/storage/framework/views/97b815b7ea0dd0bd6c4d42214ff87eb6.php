<?php $__env->startSection('title', __('app.lawyers')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4"><?php echo e(__('app.lawyers')); ?></h1>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('admin.users.manage')): ?>
        <a href="<?php echo e(route('lawyers.create')); ?>" class="btn btn-primary"><?php echo e(__('app.new_lawyer')); ?></a>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th><?php echo e(__('app.lawyer_name_ar')); ?></th>
                            <th><?php echo e(__('app.lawyer_name_en')); ?></th>
                            <th><?php echo e(__('app.lawyer_email')); ?></th>
                            <th><?php echo e(__('app.lawyer_title')); ?></th>
                            <?php if(app()->getLocale() == 'ar'): ?>
                            <th><?php echo e(__('app.actions')); ?></th>
                            <?php else: ?>
                            <th class="text-end"><?php echo e(__('app.actions')); ?></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $lawyers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lawyer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><a href="<?php echo e(route('lawyers.show', $lawyer)); ?>"><?php echo e($lawyer->id); ?></a></td>
                            <td><?php echo e($lawyer->lawyer_name_ar); ?></td>
                            <td><?php echo e($lawyer->lawyer_name_en); ?></td>
                            <td><?php echo e($lawyer->lawyer_email); ?></td>
                            <td>
                                <?php if($lawyer->title): ?>
                                    <?php echo e(app()->getLocale()==='ar' ? $lawyer->title->label_ar : $lawyer->title->label_en); ?>

                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="<?php echo e(app()->getLocale() == 'ar' ? 'text-start' : 'text-end'); ?>">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?php echo e(route('lawyers.show', $lawyer)); ?>" class="btn btn-outline-primary btn-sm"><?php echo e(__('app.view')); ?></a>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('admin.users.manage')): ?>
                                    <a href="<?php echo e(route('lawyers.edit', $lawyer)); ?>" class="btn btn-outline-secondary btn-sm"><?php echo e(__('app.edit')); ?></a>
                                    <form action="<?php echo e(route('lawyers.destroy', $lawyer)); ?>" method="POST" class="d-inline" onsubmit="return confirm('<?php echo e(__('app.confirm_delete_lawyer')); ?>')">
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

            <?php echo e($lawyers->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/lawyers/index.blade.php ENDPATH**/ ?>