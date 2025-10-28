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
                                <?php if(!isset($error['resolved']) || !$error['resolved']): ?>
                                <tr class="fuzzy-error-row"
                                    data-field="<?php echo e($error['column']); ?>"
                                    data-value="<?php echo e($error['value'] ?? ''); ?>"
                                    data-row="<?php echo e($error['row']); ?>"
                                    style="cursor: pointer;">
                                    <td><?php echo e($error['row']); ?></td>
                                    <td><code><?php echo e($error['column']); ?></code></td>
                                    <td><?php echo e(Str::limit($error['value'] ?? 'NULL', 30)); ?></td>
                                    <td>
                                        <?php echo e($error['message']); ?>

                                        <?php if(isset($error['suggestions']) && !empty($error['suggestions'])): ?>
                                            <br><small class="text-info">
                                                <i class="fas fa-lightbulb"></i> <?php echo e(__('app.suggestions')); ?>: <?php echo e(implode(', ', $error['suggestions'])); ?>

                                            </small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <tr class="fuzzy-resolved-row" style="background-color: #d4edda; opacity: 0.7;">
                                    <td><?php echo e($error['row']); ?></td>
                                    <td><code><?php echo e($error['column']); ?></code></td>
                                    <td><?php echo e(Str::limit($error['value'] ?? 'NULL', 30)); ?></td>
                                    <td>
                                        <span class="text-success">
                                            <i class="fas fa-check-circle"></i> 
                                            <?php echo e(__('app.resolved')); ?> (ID: <?php echo e($error['resolved_id'] ?? 'N/A'); ?>)
                                        </span>
                                    </td>
                                </tr>
                                <?php endif; ?>
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


<?php echo $__env->make('import.partials._fuzzy-matching-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Debug: Check if Bootstrap is loaded
    console.log('Bootstrap available:', typeof bootstrap !== 'undefined');
    console.log('jQuery available:', typeof $ !== 'undefined');

    // Add click handlers to fuzzy error rows
    document.querySelectorAll('.fuzzy-error-row').forEach(row => {
        row.addEventListener('click', function() {
            const field = this.dataset.field;
            const value = this.dataset.value;
            const rowNum = this.dataset.row;

            console.log('Clicked fuzzy error row:', { field, value, rowNum });

            // Check if this is a field that supports fuzzy matching
            const fuzzyFields = [
                'matter_partner_id', 'circuit_secretary', 'court_id',
                'client_capacity_id', 'opponent_capacity_id', 'circuit_name_id'
            ];

            if (fuzzyFields.includes(field) && value && !value.match(/^\d+$/)) {
                console.log('Opening fuzzy matching modal for:', field, value);
                // Open fuzzy matching modal
                if (typeof window.initFuzzyMatchingModal === 'function') {
                    window.initFuzzyMatchingModal(field, value, <?php echo e($session->id); ?>);
                } else {
                    console.error('initFuzzyMatchingModal function not found');
                    alert('Fuzzy matching modal not available. Please refresh the page.');
                }
            } else {
                // Show info message for non-fuzzy fields
                alert('<?php echo e(__("app.field_does_not_support_fuzzy_matching")); ?>: ' + field);
            }
        });

        // Add hover effect
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8f9fa';
        });

        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });

    // Add refresh function for validation results
    window.refreshValidationResults = function() {
        console.log('refreshValidationResults called - reloading page...');
        // Add a small delay to ensure the modal closes first
        setTimeout(function() {
            location.reload();
        }, 100);
    };
});
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/import/preflight.blade.php ENDPATH**/ ?>