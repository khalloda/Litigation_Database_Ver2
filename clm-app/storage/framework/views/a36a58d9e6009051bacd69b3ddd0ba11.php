<?php $__env->startSection('title', __('app.court_details')); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">
            <?php echo e(app()->getLocale() === 'ar' ? $court->court_name_ar : $court->court_name_en); ?>

        </h1>
        <div>
            <a href="<?php echo e(route('courts.index')); ?>" class="btn btn-outline-secondary me-2"><?php echo e(__('app.back')); ?></a>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $court)): ?>
            <a href="<?php echo e(route('courts.edit', $court)); ?>" class="btn btn-primary me-2"><?php echo e(__('app.edit')); ?></a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $court)): ?>
            <form action="<?php echo e(route('courts.destroy', $court)); ?>" method="POST" class="d-inline"
                  onsubmit="return confirm('<?php echo e(__('app.confirm_delete')); ?>')">
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

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo e(session('error')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Court Details Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><?php echo e(__('app.court_details')); ?></h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong><?php echo e(__('app.court_name_ar')); ?>:</strong> <?php echo e($court->court_name_ar ?? '-'); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong><?php echo e(__('app.court_name_en')); ?>:</strong> <?php echo e($court->court_name_en ?? '-'); ?></p>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-12">
                    <p><strong><?php echo e(__('app.court_circuits')); ?>:</strong><br>
                        <?php $__empty_1 = true; $__currentLoopData = $court->circuits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $circuit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <span class="badge bg-primary me-1 mb-1">
                                <?php echo e(app()->getLocale() === 'ar' ? $circuit->label_ar : $circuit->label_en); ?>

                            </span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-12">
                    <p><strong><?php echo e(__('app.court_secretaries')); ?>:</strong><br>
                        <?php $__empty_1 = true; $__currentLoopData = $court->secretaries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $secretary): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <span class="badge bg-info me-1 mb-1">
                                <?php echo e(app()->getLocale() === 'ar' ? $secretary->label_ar : $secretary->label_en); ?>

                            </span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-12">
                    <p><strong><?php echo e(__('app.court_floors')); ?>:</strong><br>
                        <?php $__empty_1 = true; $__currentLoopData = $court->floors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $floor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <span class="badge bg-secondary me-1 mb-1">
                                <?php echo e(app()->getLocale() === 'ar' ? $floor->label_ar : $floor->label_en); ?>

                            </span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-12">
                    <p><strong><?php echo e(__('app.court_halls')); ?>:</strong><br>
                        <?php $__empty_1 = true; $__currentLoopData = $court->halls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hall): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <span class="badge bg-warning text-dark me-1 mb-1">
                                <?php echo e(app()->getLocale() === 'ar' ? $hall->label_ar : $hall->label_en); ?>

                            </span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <p><strong><?php echo e(__('app.status')); ?>:</strong>
                        <span class="badge <?php echo e($court->is_active ? 'bg-success' : 'bg-secondary'); ?>">
                            <?php echo e($court->is_active ? __('app.active') : __('app.inactive')); ?>

                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Cases Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">
                <i class="bi bi-folder2-open me-2"></i><?php echo e(__('app.related_cases')); ?>

                <span class="badge bg-light text-dark ms-2"><?php echo e($cases->total()); ?></span>
            </h5>
        </div>
        <div class="card-body">
            <?php if($cases->count() > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><?php echo e(__('app.matter_name_ar')); ?></th>
                            <th><?php echo e(__('app.client')); ?></th>
                            <th><?php echo e(__('app.matter_status')); ?></th>
                            <th><?php echo e(__('app.matter_start_date')); ?></th>
                            <th><?php echo e(__('app.actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $cases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $case): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($case->matter_name_ar ?? $case->matter_name_en); ?></td>
                            <td>
                                <?php if($case->client): ?>
                                <a href="<?php echo e(route('clients.show', $case->client)); ?>">
                                    <?php echo e(app()->getLocale() === 'ar' ? $case->client->client_name_ar : $case->client->client_name_en); ?>

                                </a>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($case->matter_status ?? '-'); ?></td>
                            <td><?php echo e($case->matter_start_date ? $case->matter_start_date->format('Y-m-d') : '-'); ?></td>
                            <td>
                                <a href="<?php echo e(route('cases.show', $case)); ?>" class="btn btn-sm btn-outline-primary">
                                    <?php echo e(__('app.view')); ?>

                                </a>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                <?php echo e($cases->links()); ?>

            </div>
            <?php else: ?>
            <p class="text-muted mb-0"><?php echo e(__('app.no_cases_found')); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Related Hearings Section (Placeholder) -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="bi bi-calendar3 me-2"></i><?php echo e(__('app.related_hearings')); ?>

                <span class="badge bg-warning text-dark ms-2"><?php echo e(__('app.coming_soon')); ?></span>
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-0" role="alert">
                <i class="bi bi-info-circle me-2"></i>
                <?php echo e(__('app.coming_soon')); ?> - <?php echo e(__('app.related_hearings')); ?> will be displayed here once the Hearings model is finalized.
            </div>
        </div>
    </div>

    <!-- Related Tasks Section (Placeholder) -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">
                <i class="bi bi-list-task me-2"></i><?php echo e(__('app.related_tasks')); ?>

                <span class="badge bg-secondary ms-2"><?php echo e(__('app.coming_soon')); ?></span>
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-warning mb-0" role="alert">
                <i class="bi bi-info-circle me-2"></i>
                <?php echo e(__('app.coming_soon')); ?> - <?php echo e(__('app.related_tasks')); ?> with expandable subtasks will be displayed here once the Tasks/Subtasks models are finalized.
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/courts/show.blade.php ENDPATH**/ ?>