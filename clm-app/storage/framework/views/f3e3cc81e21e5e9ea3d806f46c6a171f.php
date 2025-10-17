<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><?php echo e(__('app.power_of_attorneys')); ?></h3>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\PowerOfAttorney::class)): ?>
                    <a href="<?php echo e(route('power-of-attorneys.create')); ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> <?php echo e(__('app.new_power_of_attorney')); ?>

                    </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if($powerOfAttorneys->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('app.client')); ?></th>
                                    <th><?php echo e(__('app.client_print_name')); ?></th>
                                    <th><?php echo e(__('app.principal_name')); ?></th>
                                    <th><?php echo e(__('app.poa_number')); ?></th>
                                    <th><?php echo e(__('app.issue_date')); ?></th>
                                    <th><?php echo e(__('app.issuing_authority')); ?></th>
                                    <th><?php echo e(__('app.capacity')); ?></th>
                                    <th><?php echo e(__('app.authorized_lawyers')); ?></th>
                                    <th><?php echo e(__('app.year')); ?></th>
                                    <th><?php echo e(__('app.serial')); ?></th>
                                    <th class="text-end"><?php echo e(__('app.actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $powerOfAttorneys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $poa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <?php if($poa->client): ?>
                                        <div>
                                            <div><?php echo e($poa->client->client_name_ar); ?></div>
                                            <small class="text-muted"><?php echo e($poa->client->client_name_en); ?></small>
                                        </div>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.no_client')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($poa->client_print_name ?? __('app.not_set')); ?></td>
                                    <td>
                                        <strong><?php echo e($poa->principal_name ?? __('app.not_set')); ?></strong>
                                    </td>
                                    <td>
                                        <?php if($poa->poa_number): ?>
                                        <span class="badge bg-info"><?php echo e($poa->poa_number); ?></span>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($poa->issue_date?->format('Y-m-d') ?? __('app.not_set')); ?></td>
                                    <td><?php echo e($poa->issuing_authority ?? __('app.not_set')); ?></td>
                                    <td>
                                        <?php if($poa->capacity): ?>
                                        <span class="text-truncate d-inline-block" style="max-width: 200px;" title="<?php echo e($poa->capacity); ?>">
                                            <?php echo e(Str::limit($poa->capacity, 30)); ?>

                                        </span>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($poa->authorized_lawyers): ?>
                                        <span class="text-truncate d-inline-block" style="max-width: 150px;" title="<?php echo e($poa->authorized_lawyers); ?>">
                                            <?php echo e(Str::limit($poa->authorized_lawyers, 20)); ?>

                                        </span>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($poa->year ?? __('app.not_set')); ?></td>
                                    <td>
                                        <?php if($poa->serial): ?>
                                        <span class="badge bg-info"><?php echo e($poa->serial); ?></span>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view', $poa)): ?>
                                            <a href="<?php echo e(route('power-of-attorneys.show', $poa)); ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $poa)): ?>
                                            <a href="<?php echo e(route('power-of-attorneys.edit', $poa)); ?>" class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $poa)): ?>
                                            <form method="POST" action="<?php echo e(route('power-of-attorneys.destroy', $poa)); ?>" class="d-inline" onsubmit="return confirm('<?php echo e(__('app.confirm_delete')); ?>')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center">
                        <?php echo e($powerOfAttorneys->links()); ?>

                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-file-signature fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted"><?php echo e(__('app.no_power_of_attorneys')); ?></h5>
                        <p class="text-muted"><?php echo e(__('app.no_power_of_attorneys_description')); ?></p>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\PowerOfAttorney::class)): ?>
                        <a href="<?php echo e(route('power-of-attorneys.create')); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> <?php echo e(__('app.create_first_power_of_attorney')); ?>

                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/power-of-attorneys/index.blade.php ENDPATH**/ ?>