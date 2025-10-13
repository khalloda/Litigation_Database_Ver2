<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><?php echo e(__('app.manage_option_sets')); ?></h4>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\OptionSet::class)): ?>
                    <a href="<?php echo e(route('admin.options.create')); ?>" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> <?php echo e(__('app.create_option_set')); ?>

                    </a>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <?php if(session('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo e(session('success')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <?php if(session('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo e(session('error')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('app.key')); ?></th>
                                    <th><?php echo e(__('app.name')); ?></th>
                                    <th><?php echo e(__('app.description')); ?></th>
                                    <th><?php echo e(__('app.values_count')); ?></th>
                                    <th><?php echo e(__('app.status')); ?></th>
                                    <th><?php echo e(__('app.actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $optionSets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $set): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><code><?php echo e($set->key); ?></code></td>
                                    <td><?php echo e($set->name); ?></td>
                                    <td><?php echo e(Str::limit($set->description ?? '', 50)); ?></td>
                                    <td>
                                        <span class="badge bg-info"><?php echo e($set->optionValues->count()); ?></span>
                                    </td>
                                    <td>
                                        <?php if($set->is_active): ?>
                                        <span class="badge bg-success"><?php echo e(__('app.active')); ?></span>
                                        <?php else: ?>
                                        <span class="badge bg-secondary"><?php echo e(__('app.inactive')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view', $set)): ?>
                                            <a href="<?php echo e(route('admin.options.show', $set)); ?>"
                                                class="btn btn-sm btn-info"
                                                title="<?php echo e(__('app.view')); ?>">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $set)): ?>
                                            <a href="<?php echo e(route('admin.options.edit', $set)); ?>"
                                                class="btn btn-sm btn-warning"
                                                title="<?php echo e(__('app.edit')); ?>">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $set)): ?>
                                            <form action="<?php echo e(route('admin.options.destroy', $set)); ?>"
                                                method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('<?php echo e(__('app.confirm_delete_option_set')); ?>');">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit"
                                                    class="btn btn-sm btn-danger"
                                                    title="<?php echo e(__('app.delete')); ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <?php echo e(__('app.no_option_sets_found')); ?>

                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/admin/options/index.blade.php ENDPATH**/ ?>