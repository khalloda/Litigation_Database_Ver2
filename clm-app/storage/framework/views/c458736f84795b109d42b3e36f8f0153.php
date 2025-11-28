<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><?php echo e(__('app.power_of_attorney_details')); ?></h3>
                    <div class="btn-group" role="group">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $powerOfAttorney)): ?>
                        <a href="<?php echo e(route('power-of-attorneys.edit', $powerOfAttorney)); ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i> <?php echo e(__('app.edit')); ?>

                        </a>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $powerOfAttorney)): ?>
                        <form method="POST" action="<?php echo e(route('power-of-attorneys.destroy', $powerOfAttorney)); ?>" class="d-inline" onsubmit="return confirm('<?php echo e(__('app.confirm_delete')); ?>')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> <?php echo e(__('app.delete')); ?>

                            </button>
                        </form>
                        <?php endif; ?>
                        <a href="<?php echo e(route('power-of-attorneys.index')); ?>" class="btn btn-secondary">
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
                                        <?php if($powerOfAttorney->client): ?>
                                        <div>
                                            <div><?php echo e($powerOfAttorney->client->client_name_ar); ?></div>
                                            <small class="text-muted"><?php echo e($powerOfAttorney->client->client_name_en); ?></small>
                                        </div>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.no_client')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.client_print_name')); ?>:</th>
                                    <td><strong><?php echo e($powerOfAttorney->client_print_name ?? __('app.not_set')); ?></strong></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.principal_name')); ?>:</th>
                                    <td><strong><?php echo e($powerOfAttorney->principal_name ?? __('app.not_set')); ?></strong></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.poa_number')); ?>:</th>
                                    <td>
                                        <?php if($powerOfAttorney->poa_number): ?>
                                        <span class="badge bg-info"><?php echo e($powerOfAttorney->poa_number); ?></span>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.issue_date')); ?>:</th>
                                    <td><?php echo e($powerOfAttorney->issue_date?->format('Y-m-d') ?? __('app.not_set')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.year')); ?>:</th>
                                    <td><?php echo e($powerOfAttorney->year ?? __('app.not_set')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.serial')); ?>:</th>
                                    <td>
                                        <?php if($powerOfAttorney->serial): ?>
                                        <span class="badge bg-secondary"><?php echo e($powerOfAttorney->serial); ?></span>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('app.not_set')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.copies_count')); ?>:</th>
                                    <td><?php echo e($powerOfAttorney->copies_count ?? __('app.not_set')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.inventory')); ?>:</th>
                                    <td>
                                        <?php if($powerOfAttorney->inventory): ?>
                                        <span class="badge bg-success"><?php echo e(__('app.yes')); ?></span>
                                        <?php else: ?>
                                        <span class="badge bg-secondary"><?php echo e(__('app.no')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5><?php echo e(__('app.authority_information')); ?></h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%"><?php echo e(__('app.issuing_authority')); ?>:</th>
                                    <td><?php echo e($powerOfAttorney->issuing_authority ?? __('app.not_set')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.capacity')); ?>:</th>
                                    <td><?php echo e($powerOfAttorney->capacity ?? __('app.not_set')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.principal_capacity')); ?>:</th>
                                    <td><?php echo e($powerOfAttorney->principal_capacity ?? __('app.not_set')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.letter')); ?>:</th>
                                    <td><?php echo e($powerOfAttorney->letter ?? __('app.not_set')); ?></td>
                                </tr>
                            </table>
                            
                            <h5 class="mt-4"><?php echo e(__('app.system_information')); ?></h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%"><?php echo e(__('app.created_at')); ?>:</th>
                                    <td><?php echo e($powerOfAttorney->created_at->format('Y-m-d H:i:s')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.updated_at')); ?>:</th>
                                    <td><?php echo e($powerOfAttorney->updated_at->format('Y-m-d H:i:s')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.created_by')); ?>:</th>
                                    <td><?php echo e($powerOfAttorney->created_by ?? __('app.system')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('app.updated_by')); ?>:</th>
                                    <td><?php echo e($powerOfAttorney->updated_by ?? __('app.system')); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5><?php echo e(__('app.authorized_lawyers')); ?></h5>
                            <div class="card">
                                <div class="card-body">
                                    <?php if($powerOfAttorney->authorized_lawyers): ?>
                                    <p class="mb-3"><?php echo e($powerOfAttorney->authorized_lawyers); ?></p>
                                    <?php else: ?>
                                    <p class="text-muted mb-3"><?php echo e(__('app.not_set')); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5><?php echo e(__('app.notes')); ?></h5>
                            <div class="card">
                                <div class="card-body">
                                    <?php if($powerOfAttorney->notes): ?>
                                    <p class="mb-3"><?php echo e($powerOfAttorney->notes); ?></p>
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

    
    <?php if(isset($schemaData)): ?>
        <?php if (isset($component)) { $__componentOriginal2fb075f3fd550917b20c60bb108248e8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2fb075f3fd550917b20c60bb108248e8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.all-fields-table','data' => ['record' => $powerOfAttorney,'columns' => $schemaData['columns'],'types' => $schemaData['types'],'fkHints' => $schemaData['fkHints'],'title' => 'All Power of Attorney Fields']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('admin.all-fields-table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['record' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($powerOfAttorney),'columns' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($schemaData['columns']),'types' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($schemaData['types']),'fkHints' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($schemaData['fkHints']),'title' => 'All Power of Attorney Fields']); ?>
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
</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views\power-of-attorneys\show.blade.php ENDPATH**/ ?>