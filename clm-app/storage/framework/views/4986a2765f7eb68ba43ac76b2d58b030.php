<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><?php echo e(__('app.engagement_letters')); ?></h3>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\EngagementLetter::class)): ?>
                    <a href="<?php echo e(route('engagement-letters.create')); ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> <?php echo e(__('app.new_engagement_letter')); ?>

                    </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if($engagementLetters->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('app.client')); ?></th>
                                    <th><?php echo e(__('app.client_name')); ?></th>
                                    <th><?php echo e(__('app.contract_type')); ?></th>
                                    <th><?php echo e(__('app.contract_date')); ?></th>
                                    <th><?php echo e(__('app.contract_details')); ?></th>
                                    <th><?php echo e(__('app.matters')); ?></th>
                                    <th><?php echo e(__('app.status')); ?></th>
                                    <th><?php echo e(__('app.mfiles_id')); ?></th>
                                    <th class="text-end"><?php echo e(__('app.actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $engagementLetters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $letter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <?php if($letter->client): ?>
                                        <div>
                                            <div><?php echo e($letter->client->client_name_ar); ?></div>
                                            <small class="text-muted"><?php echo e($letter->client->client_name_en); ?></small>
                                        </div>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.no_client')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo e($letter->client_name ?? __('app.not_set')); ?></strong>
                                    </td>
                                    <td>
                                        <?php if($letter->contract_type): ?>
                                        <span class="badge bg-info"><?php echo e($letter->contract_type); ?></span>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($letter->contract_date?->format('Y-m-d') ?? __('app.not_set')); ?></td>
                                    <td>
                                        <?php if($letter->contract_details): ?>
                                        <span class="text-truncate d-inline-block" style="max-width: 200px;" title="<?php echo e($letter->contract_details); ?>">
                                            <?php echo e(Str::limit($letter->contract_details, 50)); ?>

                                        </span>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($letter->matters): ?>
                                        <span class="text-truncate d-inline-block" style="max-width: 200px;" title="<?php echo e($letter->matters); ?>">
                                            <?php echo e(Str::limit($letter->matters, 50)); ?>

                                        </span>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($letter->status): ?>
                                        <span class="badge bg-success"><?php echo e($letter->status); ?></span>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($letter->mfiles_id): ?>
                                        <span class="badge bg-secondary"><?php echo e($letter->mfiles_id); ?></span>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view', $letter)): ?>
                                            <a href="<?php echo e(route('engagement-letters.show', $letter)); ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $letter)): ?>
                                            <a href="<?php echo e(route('engagement-letters.edit', $letter)); ?>" class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $letter)): ?>
                                            <form method="POST" action="<?php echo e(route('engagement-letters.destroy', $letter)); ?>" class="d-inline" onsubmit="return confirm('<?php echo e(__('app.confirm_delete')); ?>')">
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
                        <?php echo e($engagementLetters->links()); ?>

                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted"><?php echo e(__('app.no_engagement_letters')); ?></h5>
                        <p class="text-muted"><?php echo e(__('app.no_engagement_letters_description')); ?></p>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\EngagementLetter::class)): ?>
                        <a href="<?php echo e(route('engagement-letters.create')); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> <?php echo e(__('app.create_first_engagement_letter')); ?>

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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views\engagement-letters\index.blade.php ENDPATH**/ ?>