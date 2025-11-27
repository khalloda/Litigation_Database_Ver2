<?php $__env->startSection('title', 'Audit Log Details'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">🔍 Audit Log Details</h1>
                <div>
                    <a href="<?php echo e(route('audit-logs.index')); ?>" class="btn btn-outline-secondary">
                        ← Back to Logs
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">📋 Activity Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Date/Time:</strong></td>
                                    <td>
                                        <?php echo e($activity->created_at->format('l, F d, Y')); ?><br>
                                        <small class="text-muted"><?php echo e($activity->created_at->format('H:i:s')); ?> (<?php echo e($activity->created_at->diffForHumans()); ?>)</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>User:</strong></td>
                                    <td>
                                        <?php if($activity->causer): ?>
                                            <div>
                                                <strong><?php echo e($activity->causer->name); ?></strong><br>
                                                <small class="text-muted"><?php echo e($activity->causer->email); ?></small>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">System</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Action:</strong></td>
                                    <td>
                                        <?php if($activity->event === 'created'): ?>
                                            <span class="badge bg-success fs-6">Created</span>
                                        <?php elseif($activity->event === 'updated'): ?>
                                            <span class="badge bg-warning fs-6">Updated</span>
                                        <?php elseif($activity->event === 'deleted'): ?>
                                            <span class="badge bg-danger fs-6">Deleted</span>
                                        <?php else: ?>
                                            <span class="badge bg-info fs-6"><?php echo e(ucfirst($activity->event)); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Entity Type:</strong></td>
                                    <td>
                                        <?php if($activity->subject_type): ?>
                                            <strong><?php echo e(class_basename($activity->subject_type)); ?></strong>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Entity ID:</strong></td>
                                    <td>
                                        <?php if($activity->subject_id): ?>
                                            <code><?php echo e($activity->subject_id); ?></code>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Description:</strong></td>
                                    <td><?php echo e($activity->description); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            
            <?php if($activity->properties && isset($activity->properties['attributes'])): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">📝 Changes Made</h5>
                </div>
                <div class="card-body">
                    <?php
                        $changes = $activity->properties['attributes'];
                    ?>
                    <?php if(count($changes) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Field</th>
                                        <th>Value</th>
                                        <th>Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $changes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo e($field); ?></strong>
                                        </td>
                                        <td>
                                            <?php if(is_array($value) || is_object($value)): ?>
                                                <pre class="mb-0"><code><?php echo e(json_encode($value, JSON_PRETTY_PRINT)); ?></code></pre>
                                            <?php elseif(is_bool($value)): ?>
                                                <span class="badge <?php echo e($value ? 'bg-success' : 'bg-danger'); ?>">
                                                    <?php echo e($value ? 'True' : 'False'); ?>

                                                </span>
                                            <?php elseif(is_null($value)): ?>
                                                <span class="text-muted">NULL</span>
                                            <?php else: ?>
                                                <code><?php echo e($value); ?></code>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?php echo e(gettype($value)); ?></small>
                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            No specific field changes recorded.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">🔧 Raw Activity Data</h5>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3 rounded"><code><?php echo e(json_encode($activity->toArray(), JSON_PRETTY_PRINT)); ?></code></pre>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            
            <?php if($activity->subject): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">🎯 Related Entity</h5>
                </div>
                <div class="card-body">
                    <h6><?php echo e(class_basename($activity->subject_type)); ?> #<?php echo e($activity->subject_id); ?></h6>
                    
                    <?php if($activity->subject_type === 'App\Models\Client'): ?>
                        <p><strong>Name:</strong> <?php echo e($activity->subject->client_name_ar ?? $activity->subject->client_name_en ?? 'N/A'); ?></p>
                        <p><strong>Status:</strong> <?php echo e($activity->subject->status ?? 'N/A'); ?></p>
                    <?php elseif($activity->subject_type === 'App\Models\CaseModel'): ?>
                        <p><strong>Matter:</strong> <?php echo e($activity->subject->matter_name_ar ?? $activity->subject->matter_name_en ?? 'N/A'); ?></p>
                        <p><strong>Status:</strong> <?php echo e($activity->subject->matter_status ?? 'N/A'); ?></p>
                    <?php elseif($activity->subject_type === 'App\Models\AdminTask'): ?>
                        <p><strong>Task:</strong> <?php echo e($activity->subject->required_work ?? 'N/A'); ?></p>
                        <p><strong>Status:</strong> <?php echo e($activity->subject->status ?? 'N/A'); ?></p>
                    <?php endif; ?>
                    
                    <small class="text-muted">
                        Created: <?php echo e($activity->subject->created_at->format('M d, Y H:i')); ?><br>
                        Updated: <?php echo e($activity->subject->updated_at->format('M d, Y H:i')); ?>

                    </small>
                </div>
            </div>
            <?php endif; ?>

            
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">📊 Quick Stats</h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <h4 class="text-primary"><?php echo e($activity->id); ?></h4>
                        <small class="text-muted">Activity ID</small>
                    </div>
                    
                    <?php if($activity->properties && isset($activity->properties['attributes'])): ?>
                    <div class="text-center mt-3">
                        <h4 class="text-info"><?php echo e(count($activity->properties['attributes'])); ?></h4>
                        <small class="text-muted">Fields Changed</small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">⚡ Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo e(route('audit-logs.index')); ?>" class="btn btn-outline-secondary">
                            ← Back to All Logs
                        </a>
                        <button class="btn btn-outline-primary" onclick="window.print(); return false;">
                            🖨️ Print This Log
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .card-header, .d-grid {
        display: none !important;
    }
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views\audit-logs\show.blade.php ENDPATH**/ ?>