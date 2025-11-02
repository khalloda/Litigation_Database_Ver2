<?php $__env->startSection('title', 'Clients'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">Clients</h1>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('clients.create')): ?>
        <a href="<?php echo e(route('clients.create')); ?>" class="btn btn-primary">+ New Client</a>
        <?php endif; ?>
    </div>

    <!-- Search and Filter Form -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('clients.index')); ?>" class="row g-3">
                <!-- Search Input -->
                <div class="col-md-3">
                    <label for="search" class="form-label"><?php echo e(__('app.search')); ?></label>
                    <input type="text"
                        class="form-control"
                        id="search"
                        name="search"
                        value="<?php echo e($search); ?>"
                        placeholder="<?php echo e(__('app.search_clients_placeholder')); ?>">
                </div>

                <!-- Status Filter -->
                <div class="col-md-2">
                    <label for="status_id" class="form-label">
                        <?php if(app()->getLocale() == 'ar'): ?>
                        <?php echo e(__('app.status_ar')); ?>

                        <?php else: ?>
                        <?php echo e(__('app.status_en')); ?>

                        <?php endif; ?>
                    </label>
                    <select class="form-select" id="status_id" name="status_id">
                        <option value=""><?php echo e(__('app.all_statuses')); ?></option>
                        <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($status->id); ?>" <?php echo e($status_id == $status->id ? 'selected' : ''); ?>>
                            <?php if(app()->getLocale() == 'ar'): ?>
                            <?php echo e($status->label_ar); ?>

                            <?php else: ?>
                            <?php echo e($status->label_en); ?>

                            <?php endif; ?>
                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <!-- Cash or Probono Filter -->
                <div class="col-md-2">
                    <label for="cash_or_probono_id" class="form-label">
                        <?php if(app()->getLocale() == 'ar'): ?>
                        <?php echo e(__('app.cash_or_probono_ar')); ?>

                        <?php else: ?>
                        <?php echo e(__('app.cash_or_probono_en')); ?>

                        <?php endif; ?>
                    </label>
                    <select class="form-select" id="cash_or_probono_id" name="cash_or_probono_id">
                        <option value=""><?php echo e(__('app.all_types')); ?></option>
                        <?php $__currentLoopData = $cashOrProbonoOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($option->id); ?>" <?php echo e($cash_or_probono_id == $option->id ? 'selected' : ''); ?>>
                            <?php if(app()->getLocale() == 'ar'): ?>
                            <?php echo e($option->label_ar); ?>

                            <?php else: ?>
                            <?php echo e($option->label_en); ?>

                            <?php endif; ?>
                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <!-- Contact Lawyer Filter -->
                <div class="col-md-3">
                    <label for="contact_lawyer_id" class="form-label">
                        <?php if(app()->getLocale() == 'ar'): ?>
                        <?php echo e(__('app.lawyer_name_ar')); ?>

                        <?php else: ?>
                        <?php echo e(__('app.lawyer_name_en')); ?>

                        <?php endif; ?>
                    </label>
                    <select class="form-select" id="contact_lawyer_id" name="contact_lawyer_id">
                        <option value=""><?php echo e(__('app.all_lawyers')); ?></option>
                        <?php $__currentLoopData = $lawyers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lawyer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($lawyer->id); ?>" <?php echo e($contact_lawyer_id == $lawyer->id ? 'selected' : ''); ?>>
                            <?php if(app()->getLocale() == 'ar'): ?>
                            <?php echo e($lawyer->lawyer_name_ar); ?>

                            <?php else: ?>
                            <?php echo e($lawyer->lawyer_name_en); ?>

                            <?php endif; ?>
                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm"><?php echo e(__('app.filter')); ?></button>
                        <a href="<?php echo e(route('clients.index')); ?>" class="btn btn-outline-secondary btn-sm"><?php echo e(__('app.clear')); ?></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th><?php echo e(__('app.mfiles_id')); ?></th>
                            <th><?php echo e(__('app.client_code')); ?></th>
                            <th>
                                <?php if(app()->getLocale() == 'ar'): ?>
                                <?php echo e(__('app.client_name_ar')); ?>

                                <?php else: ?>
                                <?php echo e(__('app.client_name_en')); ?>

                                <?php endif; ?>
                            </th>
                            <th>
                                <?php if(app()->getLocale() == 'ar'): ?>
                                <?php echo e(__('app.lawyer_name_ar')); ?>

                                <?php else: ?>
                                <?php echo e(__('app.lawyer_name_en')); ?>

                                <?php endif; ?>
                            </th>
                            <th>
                                <?php if(app()->getLocale() == 'ar'): ?>
                                <?php echo e(__('app.status_ar')); ?>

                                <?php else: ?>
                                <?php echo e(__('app.status_en')); ?>

                                <?php endif; ?>
                            </th>
                            <th>
                                <?php if(app()->getLocale() == 'ar'): ?>
                                <?php echo e(__('app.cash_or_probono_ar')); ?>

                                <?php else: ?>
                                <?php echo e(__('app.cash_or_probono_en')); ?>

                                <?php endif; ?>
                            </th>
                            <th><?php echo e(__('app.cases_count')); ?></th>
                            <?php if(app()->getLocale() == 'ar'): ?>
                            <th><?php echo e(__('app.actions')); ?></th>
                            <?php else: ?>
                            <th class="text-end"><?php echo e(__('app.actions')); ?></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><a href="<?php echo e(route('clients.show', $client)); ?>"><?php echo e($client->id); ?></a></td>
                            <td>
                                <?php if($client->mfiles_id && $client->mfiles_id != ''): ?>
                                    <span class="badge bg-primary"><?php echo e($client->mfiles_id); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($client->client_code && $client->client_code != ''): ?>
                                    <span class="badge bg-secondary"><?php echo e($client->client_code); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(app()->getLocale() == 'ar'): ?>
                                <?php echo e($client->client_name_ar); ?>

                                <?php else: ?>
                                <?php echo e($client->client_name_en); ?>

                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($client->contactLawyer): ?>
                                <?php if(app()->getLocale() == 'ar'): ?>
                                <?php echo e($client->contactLawyer->lawyer_name_ar); ?>

                                <?php else: ?>
                                <?php echo e($client->contactLawyer->lawyer_name_en); ?>

                                <?php endif; ?>
                                <?php else: ?>
                                <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($client->statusRef): ?>
                                <span class="badge bg-success">
                                    <?php if(app()->getLocale() == 'ar'): ?>
                                    <?php echo e($client->statusRef->label_ar); ?>

                                    <?php else: ?>
                                    <?php echo e($client->statusRef->label_en); ?>

                                    <?php endif; ?>
                                </span>
                                <?php else: ?>
                                <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($client->cashOrProbono): ?>
                                <span class="badge bg-warning">
                                    <?php if(app()->getLocale() == 'ar'): ?>
                                    <?php echo e($client->cashOrProbono->label_ar); ?>

                                    <?php else: ?>
                                    <?php echo e($client->cashOrProbono->label_en); ?>

                                    <?php endif; ?>
                                </span>
                                <?php else: ?>
                                <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-info"><?php echo e($client->cases_count); ?></span>
                            </td>
                            <td class="<?php echo e(app()->getLocale() == 'ar' ? 'text-start' : 'text-end'); ?>">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?php echo e(route('clients.show', $client)); ?>" class="btn btn-outline-primary btn-sm"><?php echo e(__('app.view')); ?></a>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('clients.edit')): ?>
                                    <a href="<?php echo e(route('clients.edit', $client)); ?>" class="btn btn-outline-secondary btn-sm"><?php echo e(__('app.edit')); ?></a>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('clients.delete')): ?>
                                    <form action="<?php echo e(route('clients.destroy', $client)); ?>" method="POST" class="d-inline" onsubmit="return confirm('<?php echo e(__('app.confirm_delete')); ?>')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-outline-danger btn-sm"><?php echo e(__('app.delete')); ?></button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <?php echo e($clients->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/clients/index.blade.php ENDPATH**/ ?>