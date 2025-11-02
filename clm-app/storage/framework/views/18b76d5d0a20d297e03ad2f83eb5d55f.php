<?php $__env->startSection('title', 'Client Details'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">Client Details</h1>
        <div>
            <a href="<?php echo e(route('clients.index')); ?>" class="btn btn-outline-secondary me-2"><?php echo e(__('app.back_to_clients')); ?></a>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('clients.edit')): ?>
            <a href="<?php echo e(route('clients.edit', $client)); ?>" class="btn btn-primary me-2"><?php echo e(__('app.edit_client')); ?></a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('clients.delete')): ?>
            <form action="<?php echo e(route('clients.destroy', $client)); ?>" method="POST" class="d-inline" onsubmit="return confirm('<?php echo e(__('app.confirm_delete')); ?>')">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn btn-danger"><?php echo e(__('app.delete')); ?></button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Client</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong><?php echo e(__('app.id')); ?></strong></td>
                            <td><?php echo e($client->id); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.mfiles_id')); ?></strong></td>
                            <td>
                                <?php if($client->mfiles_id): ?>
                                    <span class="badge bg-primary"><?php echo e($client->mfiles_id); ?></span>
                                <?php else: ?>
                                    <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.client_code')); ?></strong></td>
                            <td>
                                <?php if($client->client_code): ?>
                                    <span class="badge bg-secondary"><?php echo e($client->client_code); ?></span>
                                <?php else: ?>
                                    <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.client_name_ar')); ?></strong></td>
                            <td><?php echo e($client->client_name_ar); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.client_name_en')); ?></strong></td>
                            <td><?php echo e($client->client_name_en); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.client_print_name')); ?></strong></td>
                            <td><?php echo e($client->client_print_name); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.cash_or_probono')); ?></strong></td>
                            <td>
                                <?php if($client->cashOrProbono): ?>
                                <span class="badge bg-info"><?php echo e($client->cash_or_probono_label); ?></span>
                                <?php else: ?>
                                <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.status')); ?></strong></td>
                            <td>
                                <?php if($client->statusRef): ?>
                                <span class="badge bg-success"><?php echo e($client->status_label); ?></span>
                                <?php else: ?>
                                <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.client_start')); ?></strong></td>
                            <td><?php echo e($client->client_start ? $client->client_start->format('Y-m-d') : __('app.not_set')); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.client_end')); ?></strong></td>
                            <td><?php echo e($client->client_end ? $client->client_end->format('Y-m-d') : __('app.not_set')); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.contact_lawyer')); ?></strong></td>
                            <td>
                                <?php if($client->contactLawyer): ?>
                                <span class="badge bg-primary"><?php echo e($client->contact_lawyer_name); ?></span>
                                <?php else: ?>
                                <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.power_of_attorney_location')); ?></strong></td>
                            <td>
                                <?php if($client->powerOfAttorneyLocation): ?>
                                <span class="badge bg-warning"><?php echo e($client->power_of_attorney_location_label); ?></span>
                                <?php else: ?>
                                <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.documents_location')); ?></strong></td>
                            <td>
                                <?php if($client->documentsLocation): ?>
                                <span class="badge bg-secondary"><?php echo e($client->documents_location_label); ?></span>
                                <?php else: ?>
                                <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if($client->logo): ?>
                        <tr>
                            <td><strong><?php echo e(__('app.logo')); ?></strong></td>
                            <td>
                                <?php if(file_exists(public_path($client->logo))): ?>
                                <img src="<?php echo e(asset($client->logo)); ?>" alt="Client Logo" class="img-thumbnail" style="max-width: 100px; max-height: 100px;">
                                <?php else: ?>
                                <span class="text-muted"><?php echo e(__('app.logo_file_not_found')); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td><strong><?php echo e(__('app.created_at')); ?></strong></td>
                            <td><?php 
                $value = $client->created_at;
                if ($value && is_object($value) && method_exists($value, 'format')) {
                    echo $value->format('Y-m-d H:i');
                } else {
                    echo __('app.not_set');
                }
            ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.updated_at')); ?></strong></td>
                            <td><?php 
                $value = $client->updated_at;
                if ($value && is_object($value) && method_exists($value, 'format')) {
                    echo $value->format('Y-m-d H:i');
                } else {
                    echo __('app.not_set');
                }
            ?></td>
                        </tr>
                        <?php if($client->createdBy): ?>
                        <tr>
                            <td><strong><?php echo e(__('app.created_by')); ?></strong></td>
                            <td><?php echo e($client->createdBy->name); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if($client->updatedBy): ?>
                        <tr>
                            <td><strong><?php echo e(__('app.updated_by')); ?></strong></td>
                            <td><?php echo e($client->updatedBy->name); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Cases</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Matter</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $cases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $case): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($case->id); ?></td>
                                    <td><?php echo e($case->matter_name_ar ?? $case->matter_name_en); ?></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <?php echo e($cases->links()); ?>

                </div>
            </div>
        </div>
    </div>

    <!-- Change History Section (Super Admin Only) -->
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('admin.users.manage')): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo e(__('app.change_history')); ?></h5>
                </div>
                <div class="card-body">
                    <?php if($client->activities && $client->activities->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('app.date_time')); ?></th>
                                    <th><?php echo e(__('app.event')); ?></th>
                                    <th><?php echo e(__('app.user')); ?></th>
                                    <th><?php echo e(__('app.changes')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $client->activities->sortByDesc('created_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($activity->created_at->format('Y-m-d H:i:s')); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo e($activity->event === 'created' ? 'success' : ($activity->event === 'updated' ? 'warning' : 'danger')); ?>">
                                            <?php echo e(__('app.' . $activity->event)); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <?php if($activity->causer): ?>
                                        <?php echo e($activity->causer->name); ?>

                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.system')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($activity->event === 'updated' && isset($activity->properties['old']) && isset($activity->properties['attributes'])): ?>
                                        <small>
                                            <?php $__currentLoopData = $activity->properties['old']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $oldValue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if(isset($activity->properties['attributes'][$field]) && $activity->properties['attributes'][$field] != $oldValue): ?>
                                            <strong><?php echo e($field); ?>:</strong>
                                            <span class="text-danger"><?php echo e($oldValue); ?></span> →
                                            <span class="text-success"><?php echo e($activity->properties['attributes'][$field]); ?></span><br>
                                            <?php endif; ?>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </small>
                                        <?php elseif($activity->event === 'created' && isset($activity->properties['attributes'])): ?>
                                        <small class="text-success"><?php echo e(__('app.client_created')); ?></small>
                                        <?php elseif($activity->event === 'deleted'): ?>
                                        <small class="text-danger"><?php echo e(__('app.client_deleted')); ?></small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-muted mb-0"><?php echo e(__('app.no_change_history')); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/clients/show.blade.php ENDPATH**/ ?>