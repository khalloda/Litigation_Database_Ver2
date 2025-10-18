<?php $__env->startSection('title', __('app.case_details')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4"><?php echo e(__('app.case_details')); ?></h1>
        <div>
            <a href="<?php echo e(route('cases.index')); ?>" class="btn btn-outline-secondary me-2"><?php echo e(__('app.back_to_cases')); ?></a>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('cases.edit')): ?>
            <a href="<?php echo e(route('cases.edit', $case)); ?>" class="btn btn-primary me-2"><?php echo e(__('app.edit_case')); ?></a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('cases.delete')): ?>
            <form action="<?php echo e(route('cases.destroy', $case)); ?>" method="POST" class="d-inline" onsubmit="return confirm('<?php echo e(__('app.confirm_delete_case')); ?>')">
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

        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo e(__('app.case_details')); ?></h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>ID</strong></td>
                            <td><?php echo e($case->id); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.matter_name_ar')); ?></strong></td>
                            <td><?php echo e($case->matter_name_ar); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.matter_name_en')); ?></strong></td>
                            <td><?php echo e($case->matter_name_en); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.client')); ?></strong></td>
                            <td>
                                <a href="<?php echo e(route('clients.show', $case->client)); ?>">
                                    <?php echo e($case->client->client_name_ar ?? $case->client->client_name_en); ?>

                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.matter_status')); ?></strong></td>
                            <td>
                                <?php if($case->matterStatus): ?>
                                    <?php echo e(app()->getLocale() === 'ar' ? $case->matterStatus->label_ar : $case->matterStatus->label_en); ?>

                                <?php else: ?>
                                    <?php echo e($case->matter_status ?? '-'); ?>

                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.matter_category')); ?></strong></td>
                            <td>
                                <?php if($case->matterCategory): ?>
                                    <?php echo e(app()->getLocale() === 'ar' ? $case->matterCategory->label_ar : $case->matterCategory->label_en); ?>

                                <?php else: ?>
                                    <?php echo e($case->matter_category ?? '-'); ?>

                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.matter_degree')); ?></strong></td>
                            <td>
                                <?php if($case->matterDegree): ?>
                                    <?php echo e(app()->getLocale() === 'ar' ? $case->matterDegree->label_ar : $case->matterDegree->label_en); ?>

                                <?php else: ?>
                                    <?php echo e($case->matter_degree ?? '-'); ?>

                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.matter_importance')); ?></strong></td>
                            <td>
                                <?php if($case->matterImportance): ?>
                                    <?php echo e(app()->getLocale() === 'ar' ? $case->matterImportance->label_ar : $case->matterImportance->label_en); ?>

                                <?php else: ?>
                                    <?php echo e($case->matter_importance ?? '-'); ?>

                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.client_branch')); ?></strong></td>
                            <td>
                                <?php if($case->matterBranch): ?>
                                    <?php echo e(app()->getLocale() === 'ar' ? $case->matterBranch->label_ar : $case->matterBranch->label_en); ?>

                                <?php else: ?>
                                    <?php echo e($case->client_branch ?? '-'); ?>

                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.matter_court')); ?></strong></td>
                            <td>
                                <?php if($case->court): ?>
                                    <a href="<?php echo e(route('courts.show', $case->court)); ?>">
                                        <?php echo e(app()->getLocale() === 'ar' ? $case->court->court_name_ar : $case->court->court_name_en); ?>

                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.matter_destination')); ?></strong></td>
                            <td>
                                <?php if($case->matterDestinationRef): ?>
                                    <a href="<?php echo e(route('courts.show', $case->matterDestinationRef)); ?>">
                                        <?php echo e(app()->getLocale() === 'ar' ? $case->matterDestinationRef->court_name_ar : $case->matterDestinationRef->court_name_en); ?>

                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.circuit')); ?></strong></td>
                            <td>
                                <?php if($case->circuitName || $case->circuitSerial || $case->circuitShift): ?>
                                    <?php
                                        $name = $case->circuitName ? (app()->getLocale() === 'ar' ? $case->circuitName->label_ar : $case->circuitName->label_en) : '';
                                        $serial = $case->circuitSerial ? (app()->getLocale() === 'ar' ? $case->circuitSerial->label_ar : $case->circuitSerial->label_en) : '';
                                        $shift = $case->circuitShift ? (app()->getLocale() === 'ar' ? $case->circuitShift->label_ar : $case->circuitShift->label_en) : '';

                                        $result = $name;
                                        if ($serial) $result .= " {$serial}";
                                        if ($shift && $shift !== 'Morning') $result .= " ({$shift})";
                                    ?>
                                    <?php echo e($result); ?>

                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.circuit_secretary')); ?></strong></td>
                            <td><?php echo e($case->circuitSecretaryRef ? (app()->getLocale() === 'ar' ? $case->circuitSecretaryRef->label_ar : $case->circuitSecretaryRef->label_en) : '-'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.court_floor')); ?></strong></td>
                            <td><?php echo e($case->courtFloorRef ? (app()->getLocale() === 'ar' ? $case->courtFloorRef->label_ar : $case->courtFloorRef->label_en) : '-'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.court_hall')); ?></strong></td>
                            <td><?php echo e($case->courtHallRef ? (app()->getLocale() === 'ar' ? $case->courtHallRef->label_ar : $case->courtHallRef->label_en) : '-'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.matter_start_date')); ?></strong></td>
                            <td><?php echo e($case->matter_start_date?->format('Y-m-d')); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.matter_end_date')); ?></strong></td>
                            <td><?php echo e($case->matter_end_date?->format('Y-m-d')); ?></td>
                        </tr>
                        <?php if($case->matter_description): ?>
                        <tr>
                            <td><strong><?php echo e(__('app.matter_description')); ?></strong></td>
                            <td><?php echo e($case->matter_description); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td><strong><?php echo e(__('app.client_capacity')); ?></strong></td>
                            <td>
                                <?php
                                    $clientName = $case->client_in_case_name ?: ($case->client?->client_name_ar ?? $case->client?->client_name_en);
                                    $clientCap = $case->clientCapacity ? (app()->getLocale()==='ar' ? $case->clientCapacity->label_ar : $case->clientCapacity->label_en) : null;
                                    $parts = array_filter([$clientName, $clientCap, $case->client_capacity_note]);
                                ?>
                                <?php echo e(!empty($parts) ? implode(' - ', $parts) : '-'); ?>

                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo e(__('app.opponent_capacity')); ?></strong></td>
                            <td>
                                <?php
                                    $oppName = $case->opponent_in_case_name ?: ($case->opponent ? (app()->getLocale()==='ar' ? $case->opponent->opponent_name_ar : $case->opponent->opponent_name_en) : null);
                                    $oppCap = $case->opponentCapacity ? (app()->getLocale()==='ar' ? $case->opponentCapacity->label_ar : $case->opponentCapacity->label_en) : null;
                                    $oparts = array_filter([$oppName, $oppCap, $case->opponent_capacity_note]);
                                ?>
                                <?php echo e(!empty($oparts) ? implode(' - ', $oparts) : '-'); ?>

                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <!-- Related Hearings -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo e(__('app.related_hearings')); ?></h5>
                </div>
                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $case->hearings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hearing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="mb-2">
                        <strong><?php echo e($hearing->hearing_date?->format('Y-m-d')); ?></strong> - <?php echo e($hearing->hearing_type); ?>

                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p><?php echo e(__('app.no_hearings_found')); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Related Tasks -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo e(__('app.related_tasks')); ?></h5>
                </div>
                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $case->adminTasks()->limit(5)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="mb-2">
                        <strong><?php echo e($task->task_name); ?></strong> - <?php echo e($task->status); ?>

                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p><?php echo e(__('app.no_tasks_found')); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Related Documents -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo e(__('app.related_documents')); ?></h5>
                </div>
                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $case->documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="mb-2">
                        <strong><?php echo e($document->document_name); ?></strong> - <?php echo e($document->document_type); ?>

                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p><?php echo e(__('app.no_documents_found')); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/cases/show.blade.php ENDPATH**/ ?>