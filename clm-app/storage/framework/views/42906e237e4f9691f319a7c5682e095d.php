

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <h2 class="mb-4"><?php echo e(__('app.column_mapping')); ?></h2>

    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> <?php echo e(__('app.mapping_instructions')); ?>

    </div>

    <form action="<?php echo e(route('import.save-mapping', $session)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><?php echo e(__('app.map_columns')); ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?php echo e(__('app.source_column')); ?></th>
                                <th><?php echo e(__('app.sample_data')); ?></th>
                                <th><?php echo e(__('app.target_column')); ?></th>
                                <th><?php echo e(__('app.confidence')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $parsed['headers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sourceCol): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><strong><?php echo e($sourceCol); ?></strong></td>
                                <td>
                                    <?php if(isset($columnStats[$sourceCol]['sample_values'])): ?>
                                        <small class="text-muted">
                                            <?php echo e(implode(', ', array_slice($columnStats[$sourceCol]['sample_values'], 0, 3))); ?>...
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <select name="mapping[<?php echo e($sourceCol); ?>]" class="form-select">
                                        <option value=""><?php echo e(__('app.skip_column')); ?></option>
                                        <?php $__currentLoopData = $dbColumns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dbCol): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($dbCol); ?>" 
                                                <?php echo e(isset($autoMapping['mappings'][$sourceCol]) && $autoMapping['mappings'][$sourceCol] == $dbCol ? 'selected' : ''); ?>>
                                                <?php echo e($dbCol); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </td>
                                <td>
                                    <?php if(isset($autoMapping['confidence'][$sourceCol])): ?>
                                        <span class="badge bg-<?php echo e($autoMapping['confidence'][$sourceCol] >= 80 ? 'success' : ($autoMapping['confidence'][$sourceCol] >= 65 ? 'warning' : 'secondary')); ?>">
                                            <?php echo e($autoMapping['confidence'][$sourceCol]); ?>%
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <?php if(!empty($autoMapping['unmapped'])): ?>
                    <div class="alert alert-warning">
                        <strong><?php echo e(__('app.unmapped_columns')); ?>:</strong>
                        <?php echo e(implode(', ', $autoMapping['unmapped'])); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <a href="<?php echo e(route('import.index')); ?>" class="btn btn-secondary">
                <i class="fas fa-times"></i> <?php echo e(__('app.cancel')); ?>

            </a>
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-arrow-right"></i> <?php echo e(__('app.continue_to_validation')); ?>

            </button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/import/map.blade.php ENDPATH**/ ?>