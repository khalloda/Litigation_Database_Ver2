<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><?php echo e(__('app.engagement_letter_details')); ?></h3>
                    <div class="btn-group" role="group">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $engagementLetter)): ?>
                        <a href="<?php echo e(route('engagement-letters.edit', $engagementLetter)); ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i> <?php echo e(__('app.edit')); ?>

                        </a>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $engagementLetter)): ?>
                        <form method="POST" action="<?php echo e(route('engagement-letters.destroy', $engagementLetter)); ?>" class="d-inline" onsubmit="return confirm('<?php echo e(__('app.confirm_delete')); ?>')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> <?php echo e(__('app.delete')); ?>

                            </button>
                        </form>
                        <?php endif; ?>
                        <a href="<?php echo e(route('engagement-letters.index')); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> <?php echo e(__('app.back_to_list')); ?>

                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><?php echo e(__('app.basic_information')); ?></h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%"><?php echo e(__('app.client')); ?>:</th>
                                    <td>
                                        <?php if($engagementLetter->client): ?>
                                        <div>
                                            <div><?php echo e($engagementLetter->client->client_name_ar); ?></div>
                                            <small class="text-muted"><?php echo e($engagementLetter->client->client_name_en); ?></small>
                                        </div>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.no_client')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.client_name')); ?>:</th>
                                    <td><strong><?php echo e($engagementLetter->client_name ?? __('app.not_set')); ?></strong></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.contract_type')); ?>:</th>
                                    <td>
                                        <?php if($engagementLetter->contract_type): ?>
                                        <span class="badge bg-info"><?php echo e($engagementLetter->contract_type); ?></span>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.contract_date')); ?>:</th>
                                    <td><?php echo e($engagementLetter->contract_date?->format('Y-m-d H:i:s') ?? __('app.not_set')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.status')); ?>:</th>
                                    <td>
                                        <?php if($engagementLetter->status): ?>
                                        <span class="badge bg-success"><?php echo e($engagementLetter->status); ?></span>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.mfiles_id')); ?>:</th>
                                    <td>
                                        <?php if($engagementLetter->mfiles_id): ?>
                                        <span class="badge bg-secondary"><?php echo e($engagementLetter->mfiles_id); ?></span>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5><?php echo e(__('app.system_information')); ?></h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%"><?php echo e(__('app.created_at')); ?>:</th>
                                    <td><?php echo e($engagementLetter->created_at->format('Y-m-d H:i:s')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.updated_at')); ?>:</th>
                                    <td><?php echo e($engagementLetter->updated_at->format('Y-m-d H:i:s')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.created_by')); ?>:</th>
                                    <td><?php echo e($engagementLetter->created_by ?? __('app.system')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.updated_by')); ?>:</th>
                                    <td><?php echo e($engagementLetter->updated_by ?? __('app.system')); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5><?php echo e(__('app.contract_details')); ?></h5>
                            <div class="card">
                                <div class="card-body">
                                    <?php if($engagementLetter->contract_details): ?>
                                    <p class="mb-3"><?php echo e($engagementLetter->contract_details); ?></p>
                                    <?php else: ?>
                                    <p class="text-muted mb-3"><?php echo e(__('app.not_set')); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5><?php echo e(__('app.contract_structure')); ?></h5>
                            <div class="card">
                                <div class="card-body">
                                    <?php if($engagementLetter->contract_structure): ?>
                                    <p class="mb-3"><?php echo e($engagementLetter->contract_structure); ?></p>
                                    <?php else: ?>
                                    <p class="text-muted mb-3"><?php echo e(__('app.not_set')); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5><?php echo e(__('app.matters')); ?></h5>
                            <div class="card">
                                <div class="card-body">
                                    <?php if($engagementLetter->matters): ?>
                                    <p class="mb-3"><?php echo e($engagementLetter->matters); ?></p>
                                    <?php else: ?>
                                    <p class="text-muted mb-3"><?php echo e(__('app.not_set')); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

            </div>
        </div>
    </div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views\engagement-letters\show.blade.php ENDPATH**/ ?>