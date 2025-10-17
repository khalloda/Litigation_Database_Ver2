<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <h2 class="mb-4"><?php echo e(__('app.preflight_validation')); ?></h2>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><?php echo e(__('app.validation_summary')); ?></h5>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <h2><?php echo e(number_format($session->total_rows)); ?></h2>
                    <p class="text-muted"><?php echo e(__('app.total_rows')); ?></p>
                </div>
                <div class="col-md-3">
                    <h2 class="text-danger"><?php echo e(number_format($results['error_count'])); ?></h2>
                    <p class="text-muted"><?php echo e(__('app.errors')); ?></p>
                </div>
                <div class="col-md-3">
                    <h2 class="text-warning"><?php echo e(number_format($results['warning_count'])); ?></h2>
                    <p class="text-muted"><?php echo e(__('app.warnings')); ?></p>
                </div>
                <div class="col-md-3">
                    <h2 class="text-<?php echo e($exceedsThreshold ? 'danger' : 'success'); ?>">
                        <?php echo e(number_format((($session->total_rows - $results['error_count']) / $session->total_rows) * 100, 1)); ?>%
                    </h2>
                    <p class="text-muted"><?php echo e(__('app.success_rate')); ?></p>
                </div>
            </div>

            <?php if($exceedsThreshold): ?>
                <div class="alert alert-danger mt-3">
                    <strong><i class="fas fa-exclamation-triangle"></i> <?php echo e(__('app.error_threshold_exceeded')); ?></strong>
                    <p class="mb-0"><?php echo e(__('app.error_threshold_message')); ?></p>
                </div>
            <?php else: ?>
                <div class="alert alert-success mt-3">
                    <i class="fas fa-check-circle"></i> <?php echo e(__('app.validation_passed')); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if(!empty($results['errors'])): ?>
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><?php echo e(__('app.validation_errors')); ?> (<?php echo e(count($results['errors'])); ?>)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th><?php echo e(__('app.row')); ?></th>
                                <th><?php echo e(__('app.column')); ?></th>
                                <th><?php echo e(__('app.value')); ?></th>
                                <th><?php echo e(__('app.error')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = array_slice($results['errors'], 0, 50); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($error['row']); ?></td>
                                <td><code><?php echo e($error['column']); ?></code></td>
                                <td><?php echo e(Str::limit($error['value'] ?? 'NULL', 30)); ?></td>
                                <td><?php echo e($error['message']); ?></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                    <?php if(count($results['errors']) > 50): ?>
                        <p class="text-muted"><?php echo e(__('app.showing_first_errors', ['count' => 50])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <a href="<?php echo e(route('import.map', $session)); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> <?php echo e(__('app.back_to_mapping')); ?>

                </a>
                <?php if(!$exceedsThreshold): ?>
                    <form id="preflight-run-form" action="<?php echo e(route('import.run', $session)); ?>" method="POST" class="w-100">
                        <?php echo csrf_field(); ?>
                        <?php if(isset($session) && $session->table_name === 'cases'): ?>
                            <hr>
                            <h5 class="mb-3"><?php echo app('translator')->get('app.opponent_suggestions'); ?></h5>
                            <?php echo $__env->make('import.partials.opponent_fuzzy', [
                                'rows' => $parsed['rows'] ?? [],
                                'opponentSuggestions' => $opponentSuggestions ?? []
                            ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        <?php endif; ?>
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary btn-lg" onclick="return confirm('<?php echo e(__('app.confirm_start_import')); ?>')">
                                <i class="fas fa-play"></i> <?php echo e(__('app.start_import')); ?>

                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/import/preflight.blade.php ENDPATH**/ ?>