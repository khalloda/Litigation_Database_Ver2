<?php $__env->startSection('title', __('app.hearing_details')); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4"><?php echo e(__('app.hearing_details')); ?></h1>
        <div>
            <a href="<?php echo e(route('hearings.index')); ?>" class="btn btn-outline-secondary me-2"><?php echo e(__('app.back_to_hearings')); ?></a>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('hearings.edit')): ?>
            <a href="<?php echo e(route('hearings.edit', $hearing)); ?>" class="btn btn-primary me-2"><?php echo e(__('app.edit_hearing')); ?></a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('hearings.delete')): ?>
            <form action="<?php echo e(route('hearings.destroy', $hearing)); ?>" method="POST" class="d-inline" onsubmit="return confirm('<?php echo e(__('app.confirm_delete_hearing')); ?>')">
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

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-borderless">
                <tr>
                    <td><strong>ID</strong></td>
                    <td><?php echo e($hearing->id); ?></td>
                </tr>
                <tr>
                    <td><strong><?php echo e(__('app.case')); ?></strong></td>
                    <td>
                        <?php if($hearing->case): ?>
                        <a href="<?php echo e(route('cases.show', $hearing->case)); ?>">
                            <?php echo e($hearing->case->matter_name_ar ?? $hearing->case->matter_name_en); ?>

                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><strong><?php echo e(__('app.client')); ?></strong></td>
                    <td>
                        <?php if($hearing->case?->client): ?>
                        <a href="<?php echo e(route('clients.show', $hearing->case->client)); ?>">
                            <?php echo e($hearing->case->client->client_name_ar ?? $hearing->case->client->client_name_en); ?>

                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><strong><?php echo e(__('app.hearing_date')); ?></strong></td>
                    <td><?php echo e($hearing->date?->format('Y-m-d')); ?></td>
                </tr>
                <tr>
                    <td><strong>Procedure</strong></td>
                    <td><?php echo e($hearing->procedure); ?></td>
                </tr>
                <tr>
                    <td><strong><?php echo e(__('app.hearing_court')); ?></strong></td>
                    <td><?php echo e($hearing->court); ?></td>
                </tr>
                <tr>
                    <td><strong>Decision</strong></td>
                    <td><?php echo e($hearing->decision); ?></td>
                </tr>
                <?php if($hearing->next_hearing): ?>
                <tr>
                    <td><strong><?php echo e(__('app.next_hearing_date')); ?></strong></td>
                    <td><?php echo e($hearing->next_hearing?->format('Y-m-d')); ?></td>
                </tr>
                <?php endif; ?>
                <?php if($hearing->notes): ?>
                <tr>
                    <td><strong><?php echo e(__('app.hearing_notes')); ?></strong></td>
                    <td><?php echo e($hearing->notes); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    
    <?php if(isset($schemaData)): ?>
        <?php if (isset($component)) { $__componentOriginal2fb075f3fd550917b20c60bb108248e8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2fb075f3fd550917b20c60bb108248e8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.all-fields-table','data' => ['record' => $hearing,'columns' => $schemaData['columns'],'types' => $schemaData['types'],'fkHints' => $schemaData['fkHints'],'title' => 'All Hearing Fields']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('admin.all-fields-table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['record' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hearing),'columns' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($schemaData['columns']),'types' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($schemaData['types']),'fkHints' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($schemaData['fkHints']),'title' => 'All Hearing Fields']); ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views\hearings\show.blade.php ENDPATH**/ ?>