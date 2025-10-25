<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><?php echo e(__('app.contacts')); ?></h3>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Contact::class)): ?>
                    <a href="<?php echo e(route('contacts.create')); ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> <?php echo e(__('app.new_contact')); ?>

                    </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if($contacts->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('app.contact_name')); ?></th>
                                    <th><?php echo e(__('app.client')); ?></th>
                                    <th><?php echo e(__('app.full_name')); ?></th>
                                    <th><?php echo e(__('app.job_title')); ?></th>
                                    <th><?php echo e(__('app.address')); ?></th>
                                    <th><?php echo e(__('app.city')); ?></th>
                                    <th><?php echo e(__('app.state')); ?></th>
                                    <th><?php echo e(__('app.country')); ?></th>
                                    <th><?php echo e(__('app.zip_code')); ?></th>
                                    <th><?php echo e(__('app.business_phone')); ?></th>
                                    <th><?php echo e(__('app.home_phone')); ?></th>
                                    <th><?php echo e(__('app.mobile_phone')); ?></th>
                                    <th><?php echo e(__('app.fax_number')); ?></th>
                                    <th><?php echo e(__('app.email')); ?></th>
                                    <th><?php echo e(__('app.web_page')); ?></th>
                                    <th><?php echo e(__('app.created_at')); ?></th>
                                    <th class="text-end"><?php echo e(__('app.actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <strong><?php echo e($contact->contact_name ?? __('app.not_set')); ?></strong>
                                    </td>
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
                                    <td><?php echo e($contact->full_name ?? __('app.not_set')); ?></td>
                                    <td><?php echo e($contact->job_title ?? __('app.not_set')); ?></td>
                                    <td><?php echo e($contact->address ?? __('app.not_set')); ?></td>
                                    <td><?php echo e($contact->city ?? __('app.not_set')); ?></td>
                                    <td><?php echo e($contact->state ?? __('app.not_set')); ?></td>
                                    <td><?php echo e($contact->country ?? __('app.not_set')); ?></td>
                                    <td><?php echo e($contact->zip_code ?? __('app.not_set')); ?></td>
                                    <td>
                                        <?php if($contact->business_phone): ?>
                                        <a href="tel:<?php echo e($contact->business_phone); ?>" class="text-decoration-none">
                                            <i class="fas fa-phone"></i> <?php echo e($contact->business_phone); ?>

                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($contact->home_phone): ?>
                                        <a href="tel:<?php echo e($contact->home_phone); ?>" class="text-decoration-none">
                                            <i class="fas fa-home"></i> <?php echo e($contact->home_phone); ?>

                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($contact->mobile_phone): ?>
                                        <a href="tel:<?php echo e($contact->mobile_phone); ?>" class="text-decoration-none">
                                            <i class="fas fa-mobile"></i> <?php echo e($contact->mobile_phone); ?>

                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($contact->fax_number ?? __('app.not_set')); ?></td>
                                    <td>
                                        <?php if($contact->email): ?>
                                        <a href="mailto:<?php echo e($contact->email); ?>" class="text-decoration-none">
                                            <i class="fas fa-envelope"></i> <?php echo e($contact->email); ?>

                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($contact->web_page): ?>
                                        <a href="<?php echo e($contact->web_page); ?>" target="_blank" class="text-decoration-none">
                                            <i class="fas fa-globe"></i> <?php echo e(Str::limit($contact->web_page, 20)); ?>

                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($contact->created_at->format('Y-m-d H:i')); ?></td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view', $contact)): ?>
                                            <a href="<?php echo e(route('contacts.show', $contact)); ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $contact)): ?>
                                            <a href="<?php echo e(route('contacts.edit', $contact)); ?>" class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $contact)): ?>
                                            <form method="POST" action="<?php echo e(route('contacts.destroy', $contact)); ?>" class="d-inline" onsubmit="return confirm('<?php echo e(__('app.confirm_delete')); ?>')">
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
                        <?php echo e($contacts->links()); ?>

                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-address-book fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted"><?php echo e(__('app.no_contacts')); ?></h5>
                        <p class="text-muted"><?php echo e(__('app.no_contacts_description')); ?></p>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Contact::class)): ?>
                        <a href="<?php echo e(route('contacts.create')); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> <?php echo e(__('app.create_first_contact')); ?>

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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/contacts/index.blade.php ENDPATH**/ ?>