<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <h2 class="mb-4">Choices — <?php echo e($profile->name); ?></h2>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-auto">
            <input type="text" class="form-control" name="column" value="<?php echo e(request('column')); ?>" placeholder="Column">
        </div>
        <div class="col-auto">
            <select class="form-select" name="action">
                <option value="">Action: Any</option>
                <?php $__currentLoopData = ['match','alias','capacity','ignore']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($a); ?>" <?php if(request('action')===$a): echo 'selected'; endif; ?>><?php echo e(ucfirst($a)); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="col-auto">
            <input type="text" class="form-control" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search text">
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-primary">Filter</button>
        </div>
        <div class="col-auto ms-auto">
            <a class="btn btn-outline-secondary" href="<?php echo e(route('admin.import.profiles.export', $profile)); ?>">Export JSON</a>
        </div>
    </form>

    <div class="card mb-4">
        <div class="card-body table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Original (raw)</th>
                        <th>Normalized</th>
                        <th>Action</th>
                        <th>Entity</th>
                        <th>Active</th>
                        <th>Updated</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $choices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><code><?php echo e($c->column); ?></code></td>
                        <td><?php echo e($c->raw_value ?? '—'); ?></td>
                        <td><code><?php echo e($c->normalized_value); ?></code></td>
                        <td><span class="badge bg-secondary"><?php echo e($c->action); ?></span></td>
                        <td><?php echo e($c->entity_model ? class_basename($c->entity_model)."#".$c->entity_id : '—'); ?></td>
                        <td><?php echo e($c->is_active ? 'Yes' : 'No'); ?></td>
                        <td><?php echo e($c->updated_at); ?></td>
                        <td class="text-end">
                            <form method="POST" action="<?php echo e(route('admin.import.choices.update', [$profile, $c])); ?>" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PATCH'); ?>
                                <input type="hidden" name="is_active" value="<?php echo e($c->is_active ? 0 : 1); ?>">
                                <button class="btn btn-sm btn-outline-warning"><?php echo e($c->is_active ? 'Deactivate' : 'Activate'); ?></button>
                            </form>
                            <form method="POST" action="<?php echo e(route('admin.import.choices.destroy', [$profile, $c])); ?>" class="d-inline" onsubmit="return confirm('Delete choice?')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
            <?php echo e($choices->withQueryString()->links()); ?>

        </div>
    </div>

    <h5>Add Choice</h5>
    <form method="POST" action="<?php echo e(route('admin.import.choices.store', $profile)); ?>" class="row g-2">
        <?php echo csrf_field(); ?>
        <div class="col-2"><input class="form-control" name="column" placeholder="Column" required></div>
        <div class="col-3"><input class="form-control" name="raw_value" placeholder="Raw"></div>
        <div class="col-3"><input class="form-control" name="normalized_value" placeholder="Normalized" required></div>
        <div class="col-2">
            <select class="form-select" name="action" required>
                <?php $__currentLoopData = ['match','alias','capacity','ignore']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($a); ?>"><?php echo e(ucfirst($a)); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="col-2"><button class="btn btn-primary w-100">Save</button></div>
    </form>
</div>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/admin/import/choices/index.blade.php ENDPATH**/ ?>