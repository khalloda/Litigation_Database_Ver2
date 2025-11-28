<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?php echo e(__('app.admin_task_details')); ?></h5>
                    <div>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $adminTask)): ?>
                            <a href="<?php echo e(route('admin-tasks.edit', $adminTask)); ?>" class="btn btn-warning btn-sm">
                                <?php echo e(__('app.edit')); ?>

                            </a>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $adminTask)): ?>
                            <form action="<?php echo e(route('admin-tasks.destroy', $adminTask)); ?>" method="POST" class="d-inline" onsubmit="return confirm('<?php echo e(__('app.confirm_delete')); ?>');">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-danger btn-sm"><?php echo e(__('app.delete')); ?></button>
                            </form>
                        <?php endif; ?>
                        <a href="<?php echo e(route('admin-tasks.index')); ?>" class="btn btn-secondary btn-sm"><?php echo e(__('app.back')); ?></a>
                    </div>
                </div>

                <div class="card-body">
                    <?php if(session('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo e(session('success')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3"><?php echo e(__('app.basic_information')); ?></h6>
                            
                            <div class="mb-3">
                                <strong><?php echo e(__('app.id')); ?>:</strong>
                                <span class="text-muted"><?php echo e($adminTask->id); ?></span>
                            </div>

                            <div class="mb-3">
                                <strong><?php echo e(__('app.case')); ?>:</strong>
                                <?php if($adminTask->case): ?>
                                    <a href="<?php echo e(route('cases.show', $adminTask->case)); ?>">
                                        <?php echo e(app()->getLocale() === 'ar' ? $adminTask->case->matter_name_ar : $adminTask->case->matter_name_en); ?>

                                    </a>
                                <?php else: ?>
                                    <span class="text-muted"><?php echo e(__('app.not_specified')); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <strong><?php echo e(__('app.lawyer')); ?>:</strong>
                                <?php if($adminTask->lawyer): ?>
                                    <?php echo e(app()->getLocale() === 'ar' ? $adminTask->lawyer->lawyer_name_ar : $adminTask->lawyer->lawyer_name_en); ?>

                                <?php else: ?>
                                    <span class="text-muted"><?php echo e(__('app.not_assigned')); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <strong><?php echo e(__('app.status')); ?>:</strong>
                                <?php if($adminTask->status): ?>
                                    <span class="badge bg-secondary"><?php echo e($adminTask->status); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <strong><?php echo e(__('app.authority')); ?>:</strong>
                                <span class="text-muted"><?php echo e($adminTask->authority ?? '-'); ?></span>
                            </div>

                            <div class="mb-3">
                                <strong><?php echo e(__('app.court')); ?>:</strong>
                                <span class="text-muted"><?php echo e($adminTask->court ?? '-'); ?></span>
                            </div>

                            <div class="mb-3">
                                <strong><?php echo e(__('app.circuit')); ?>:</strong>
                                <span class="text-muted"><?php echo e($adminTask->circuit ?? '-'); ?></span>
                            </div>

                            <div class="mb-3">
                                <strong><?php echo e(__('app.performer')); ?>:</strong>
                                <span class="text-muted"><?php echo e($adminTask->performer ?? '-'); ?></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3"><?php echo e(__('app.dates_and_tracking')); ?></h6>

                            <div class="mb-3">
                                <strong><?php echo e(__('app.creation_date')); ?>:</strong>
                                <span class="text-muted"><?php echo e($adminTask->creation_date ? $adminTask->creation_date->format('Y-m-d H:i') : '-'); ?></span>
                            </div>

                            <div class="mb-3">
                                <strong><?php echo e(__('app.execution_date')); ?>:</strong>
                                <span class="text-muted"><?php echo e($adminTask->execution_date ? $adminTask->execution_date->format('Y-m-d H:i') : '-'); ?></span>
                            </div>

                            <div class="mb-3">
                                <strong><?php echo e(__('app.last_date')); ?>:</strong>
                                <span class="text-muted"><?php echo e($adminTask->last_date ? $adminTask->last_date->format('Y-m-d') : '-'); ?></span>
                            </div>

                            <div class="mb-3">
                                <strong><?php echo e(__('app.alert')); ?>:</strong>
                                <?php if($adminTask->alert): ?>
                                    <span class="badge bg-warning"><?php echo e(__('app.yes')); ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?php echo e(__('app.no')); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if($adminTask->required_work): ?>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 mb-3"><?php echo e(__('app.required_work')); ?></h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <?php echo e($adminTask->required_work); ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if($adminTask->last_follow_up): ?>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 mb-3"><?php echo e(__('app.last_follow_up')); ?></h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <?php echo e($adminTask->last_follow_up); ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if($adminTask->previous_decision): ?>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 mb-3"><?php echo e(__('app.previous_decision')); ?></h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <?php echo e($adminTask->previous_decision); ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if($adminTask->result): ?>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 mb-3"><?php echo e(__('app.result')); ?></h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <?php echo e($adminTask->result); ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if($adminTask->subtasks && $adminTask->subtasks->count() > 0): ?>
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 mb-3"><?php echo e(__('app.subtasks')); ?> (<?php echo e($adminTask->subtasks->count()); ?>)</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th><?php echo e(__('app.id')); ?></th>
                                                <th><?php echo e(__('app.lawyer')); ?></th>
                                                <th><?php echo e(__('app.performer')); ?></th>
                                                <th><?php echo e(__('app.next_date')); ?></th>
                                                <th><?php echo e(__('app.procedure_date')); ?></th>
                                                <th><?php echo e(__('app.report')); ?></th>
                                                <th><?php echo e(__('app.actions')); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $adminTask->subtasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subtask): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr>
                                                    <td><?php echo e($subtask->id); ?></td>
                                                    <td>
                                                        <?php if($subtask->lawyer): ?>
                                                            <?php echo e(app()->getLocale() === 'ar' ? $subtask->lawyer->lawyer_name_ar : $subtask->lawyer->lawyer_name_en); ?>

                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo e($subtask->performer ?? '-'); ?></td>
                                                    <td><?php echo e($subtask->next_date ? $subtask->next_date->format('Y-m-d') : '-'); ?></td>
                                                    <td><?php echo e($subtask->procedure_date ? $subtask->procedure_date->format('Y-m-d') : '-'); ?></td>
                                                    <td>
                                                        <?php if($subtask->report): ?>
                                                            <span class="badge bg-success"><?php echo e(__('app.yes')); ?></span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary"><?php echo e(__('app.no')); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="<?php echo e(route('admin-subtasks.show', $subtask)); ?>" class="btn btn-info btn-sm"><?php echo e(__('app.view')); ?></a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2 mb-3"><?php echo e(__('app.system_information')); ?></h6>
                            
                            <div class="mb-2">
                                <strong><?php echo e(__('app.created_at')); ?>:</strong>
                                <span class="text-muted"><?php echo e($adminTask->created_at ? $adminTask->created_at->format('Y-m-d H:i:s') : '-'); ?></span>
                            </div>

                            <div class="mb-2">
                                <strong><?php echo e(__('app.updated_at')); ?>:</strong>
                                <span class="text-muted"><?php echo e($adminTask->updated_at ? $adminTask->updated_at->format('Y-m-d H:i:s') : '-'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <?php if(isset($taskSchemaData)): ?>
        <?php if (isset($component)) { $__componentOriginal2fb075f3fd550917b20c60bb108248e8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2fb075f3fd550917b20c60bb108248e8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.all-fields-table','data' => ['record' => $adminTask,'columns' => $taskSchemaData['columns'],'types' => $taskSchemaData['types'],'fkHints' => $taskSchemaData['fkHints'],'title' => 'All Task Fields']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('admin.all-fields-table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['record' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($adminTask),'columns' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($taskSchemaData['columns']),'types' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($taskSchemaData['types']),'fkHints' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($taskSchemaData['fkHints']),'title' => 'All Task Fields']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2fb075f3fd550917b20c60bb108248e8)): ?>
<?php $attributes = $__attributesOriginal2fb075f3fd550917b20c60bb108248e8; ?>
<?php unset($__attributesOriginal2fb075f3fd550917b20c60bb108248e8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2fb075f3fd550917b20c60bb108248e8)): ?>
<?php $component = $__componentOriginal2fb075f3fd550917b20c60bb108248e8; ?>
<?php unset($__componentOriginal2fb075f3fd550917b20c60bb108248e8); ?>
<?php endif; ?>
    <?php endif; ?>

    
    <?php if(isset($subtaskSchemaData) && $adminTask->subtasks->isNotEmpty()): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">All Subtask Fields</h5>
            </div>
            <div class="card-body p-0">
                <?php $__currentLoopData = $adminTask->subtasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subtask): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="border-bottom p-3">
                        <h6 class="mb-3">Subtask #<?php echo e($subtask->id); ?></h6>
                        <?php if (isset($component)) { $__componentOriginal2fb075f3fd550917b20c60bb108248e8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2fb075f3fd550917b20c60bb108248e8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.all-fields-table','data' => ['record' => $subtask,'columns' => $subtaskSchemaData['columns'],'types' => $subtaskSchemaData['types'],'fkHints' => $subtaskSchemaData['fkHints'],'title' => 'Subtask #' . $subtask->id]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('admin.all-fields-table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['record' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($subtask),'columns' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($subtaskSchemaData['columns']),'types' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($subtaskSchemaData['types']),'fkHints' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($subtaskSchemaData['fkHints']),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Subtask #' . $subtask->id)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2fb075f3fd550917b20c60bb108248e8)): ?>
<?php $attributes = $__attributesOriginal2fb075f3fd550917b20c60bb108248e8; ?>
<?php unset($__attributesOriginal2fb075f3fd550917b20c60bb108248e8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2fb075f3fd550917b20c60bb108248e8)): ?>
<?php $component = $__componentOriginal2fb075f3fd550917b20c60bb108248e8; ?>
<?php unset($__componentOriginal2fb075f3fd550917b20c60bb108248e8); ?>
<?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views\admin-tasks\show.blade.php ENDPATH**/ ?>