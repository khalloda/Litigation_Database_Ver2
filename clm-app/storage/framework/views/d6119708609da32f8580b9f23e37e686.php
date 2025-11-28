<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><?php echo e(__('app.contact_details')); ?></h3>
                    <div class="btn-group" role="group">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $contact)): ?>
                        <a href="<?php echo e(route('contacts.edit', $contact)); ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i> <?php echo e(__('app.edit')); ?>

                        </a>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $contact)): ?>
                        <form method="POST" action="<?php echo e(route('contacts.destroy', $contact)); ?>" class="d-inline" onsubmit="return confirm('<?php echo e(__('app.confirm_delete')); ?>')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> <?php echo e(__('app.delete')); ?>

                            </button>
                        </form>
                        <?php endif; ?>
                        <a href="<?php echo e(route('contacts.index')); ?>" class="btn btn-secondary">
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
                                    <th width="40%"><?php echo e(__('app.contact_name')); ?>:</th>
                                    <td><strong><?php echo e($contact->contact_name ?? __('app.not_set')); ?></strong></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.full_name')); ?>:</th>
                                    <td><?php echo e($contact->full_name ?? __('app.not_set')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.job_title')); ?>:</th>
                                    <td><?php echo e($contact->job_title ?? __('app.not_set')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.client')); ?>:</th>
                                    <td>
                                        <?php if($contact->client): ?>
                                        <div>
                                            <div><?php echo e($contact->client->client_name_ar); ?></div>
                                            <small class="text-muted"><?php echo e($contact->client->client_name_en); ?></small>
                                        </div>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.no_client')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.email')); ?>:</th>
                                    <td>
                                        <?php if($contact->email): ?>
                                        <a href="mailto:<?php echo e($contact->email); ?>" class="text-decoration-none">
                                            <i class="fas fa-envelope"></i> <?php echo e($contact->email); ?>

                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.business_phone')); ?>:</th>
                                    <td>
                                        <?php if($contact->business_phone): ?>
                                        <a href="tel:<?php echo e($contact->business_phone); ?>" class="text-decoration-none">
                                            <i class="fas fa-phone"></i> <?php echo e($contact->business_phone); ?>

                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.home_phone')); ?>:</th>
                                    <td>
                                        <?php if($contact->home_phone): ?>
                                        <a href="tel:<?php echo e($contact->home_phone); ?>" class="text-decoration-none">
                                            <i class="fas fa-home"></i> <?php echo e($contact->home_phone); ?>

                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.mobile_phone')); ?>:</th>
                                    <td>
                                        <?php if($contact->mobile_phone): ?>
                                        <a href="tel:<?php echo e($contact->mobile_phone); ?>" class="text-decoration-none">
                                            <i class="fas fa-mobile"></i> <?php echo e($contact->mobile_phone); ?>

                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.fax_number')); ?>:</th>
                                    <td><?php echo e($contact->fax_number ?? __('app.not_set')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.web_page')); ?>:</th>
                                    <td>
                                        <?php if($contact->web_page): ?>
                                        <a href="<?php echo e($contact->web_page); ?>" target="_blank" class="text-decoration-none">
                                            <i class="fas fa-globe"></i> <?php echo e($contact->web_page); ?>

                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5><?php echo e(__('app.address_information')); ?></h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%"><?php echo e(__('app.address')); ?>:</th>
                                    <td><?php echo e($contact->address ?? __('app.not_set')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.city')); ?>:</th>
                                    <td><?php echo e($contact->city ?? __('app.not_set')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.state')); ?>:</th>
                                    <td><?php echo e($contact->state ?? __('app.not_set')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.country')); ?>:</th>
                                    <td><?php echo e($contact->country ?? __('app.not_set')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.zip_code')); ?>:</th>
                                    <td><?php echo e($contact->zip_code ?? __('app.not_set')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.attachments')); ?>:</th>
                                    <td><?php echo e($contact->attachments ?? __('app.not_set')); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5><?php echo e(__('app.system_information')); ?></h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="20%"><?php echo e(__('app.created_at')); ?>:</th>
                                    <td><?php echo e($contact->created_at->format('Y-m-d H:i:s')); ?></td>
                                    <th width="20%"><?php echo e(__('app.updated_at')); ?>:</th>
                                    <td><?php echo e($contact->updated_at->format('Y-m-d H:i:s')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.created_by')); ?>:</th>
                                    <td><?php echo e($contact->created_by ?? __('app.system')); ?></td>
                                    <th><?php echo e(__('app.updated_by')); ?>:</th>
                                    <td><?php echo e($contact->updated_by ?? __('app.system')); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views\contacts\show.blade.php ENDPATH**/ ?>