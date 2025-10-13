<?php $__env->startSection('title', __('app.courts')); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4"><?php echo e(__('app.courts')); ?></h1>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Court::class)): ?>
        <a href="<?php echo e(route('courts.create')); ?>" class="btn btn-primary"><?php echo e(__('app.create_court')); ?></a>
        <?php endif; ?>
    </div>

    <?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo e(session('success')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo e(session('error')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Search and Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('courts.index')); ?>" class="row g-3">
                <div class="col-md-6">
                    <label for="search" class="form-label"><?php echo e(__('app.search')); ?></label>
                    <input type="text" class="form-control" id="search" name="search" value="<?php echo e(request('search')); ?>" placeholder="<?php echo e(__('app.search_courts')); ?>">
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label"><?php echo e(__('app.status')); ?></label>
                    <select class="form-select" id="status" name="status">
                        <option value=""><?php echo e(__('app.all_statuses')); ?></option>
                        <option value="active" <?php echo e(request('status') === 'active' ? 'selected' : ''); ?>><?php echo e(__('app.active')); ?></option>
                        <option value="inactive" <?php echo e(request('status') === 'inactive' ? 'selected' : ''); ?>><?php echo e(__('app.inactive')); ?></option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary w-100"><?php echo e(__('app.filter')); ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Courts List -->
    <div class="card shadow-sm">
        <div class="card-body">
            <?php if($courts->count() > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><?php echo e(__('app.court_name_ar')); ?></th>
                            <th><?php echo e(__('app.court_name_en')); ?></th>
                            <th><?php echo e(__('app.status')); ?></th>
                            <th><?php echo e(__('app.actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $courts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $court): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($court->court_name_ar); ?></td>
                            <td><?php echo e($court->court_name_en); ?></td>
                            <td>
                                <span class="badge <?php echo e($court->is_active ? 'bg-success' : 'bg-secondary'); ?>">
                                    <?php echo e($court->is_active ? __('app.active') : __('app.inactive')); ?>

                                </span>
                            </td>
                            <td>
                                <a href="<?php echo e(route('courts.show', $court)); ?>" class="btn btn-sm btn-outline-primary"><?php echo e(__('app.view')); ?></a>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $court)): ?>
                                <a href="<?php echo e(route('courts.edit', $court)); ?>" class="btn btn-sm btn-outline-secondary"><?php echo e(__('app.edit')); ?></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                <?php echo e($courts->links()); ?>

            </div>
            <?php else: ?>
            <p class="text-muted text-center mb-0"><?php echo e(__('app.no_courts_found')); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/courts/index.blade.php ENDPATH**/ ?>