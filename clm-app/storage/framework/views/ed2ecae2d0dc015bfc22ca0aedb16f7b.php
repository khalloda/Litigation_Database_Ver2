<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <h2 class="mb-4">Import Profiles</h2>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-auto">
            <input type="text" class="form-control" name="table" value="<?php echo e(request('table')); ?>" placeholder="Table name">
        </div>
        <div class="col-auto">
            <select class="form-select" name="active">
                <option value="">Active: Any</option>
                <option value="1" <?php if(request('active')==='1'): echo 'selected'; endif; ?>>Active</option>
                <option value="0" <?php if(request('active')==='0'): echo 'selected'; endif; ?>>Inactive</option>
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-primary">Filter</button>
        </div>
    </form>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Table</th>
                        <th>Header Hash</th>
                        <th>Active</th>
                        <th>Updated</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $profiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($p->name); ?></td>
                        <td><code><?php echo e($p->table_name); ?></code></td>
                        <td><code><?php echo e($p->header_hash ?? '—'); ?></code></td>
                        <td><?php echo e($p->is_active ? 'Yes' : 'No'); ?></td>
                        <td><?php echo e($p->updated_at); ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary" href="<?php echo e(route('admin.import.choices.index', $p)); ?>">Choices</a>
                            <a class="btn btn-sm btn-outline-info" href="<?php echo e(route('admin.import.profiles.export', $p)); ?>">Export</a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
            <?php echo e($profiles->withQueryString()->links()); ?>

        </div>
    </div>

    <hr>
    <h5>Create Profile</h5>
    <form method="POST" action="<?php echo e(route('admin.import.profiles.store')); ?>" class="row g-2">
        <?php echo csrf_field(); ?>
        <div class="col-3">
            <input class="form-control" name="name" placeholder="Name" required>
        </div>
        <div class="col-3">
            <input class="form-control" name="table_name" placeholder="Table" required>
        </div>
        <div class="col-4">
            <input class="form-control" name="header_hash" placeholder="Header Hash (optional)">
        </div>
        <div class="col-2">
            <button class="btn btn-primary w-100">Save</button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/admin/import/profiles/index.blade.php ENDPATH**/ ?>