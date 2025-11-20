<?php $__env->startSection('title', 'Audit Logs'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">📋 Audit Logs</h1>
                <div>
                    <a href="<?php echo e(route('audit-logs.export', request()->query())); ?>" class="btn btn-sm btn-outline-success me-2">
                        📄 Export CSV
                    </a>
                    <a href="#" class="btn btn-sm btn-outline-primary" onclick="window.print(); return false;">
                        🖨️ Print
                    </a>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">🔍 Filters</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?php echo e(route('audit-logs.index')); ?>">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?php echo e(request('search')); ?>" placeholder="Search descriptions...">
                            </div>
                            <div class="col-md-2">
                                <label for="subject_type" class="form-label">Entity Type</label>
                                <select class="form-select" id="subject_type" name="subject_type">
                                    <option value="">All Types</option>
                                    <?php $__currentLoopData = $subjectTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($type['value']); ?>" <?php echo e(request('subject_type') == $type['value'] ? 'selected' : ''); ?>>
                                            <?php echo e($type['label']); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="event" class="form-label">Action</label>
                                <select class="form-select" id="event" name="event">
                                    <option value="">All Actions</option>
                                    <?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($event); ?>" <?php echo e(request('event') == $event ? 'selected' : ''); ?>>
                                            <?php echo e(ucfirst($event)); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="causer_id" class="form-label">User</label>
                                <select class="form-select" id="causer_id" name="causer_id">
                                    <option value="">All Users</option>
                                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($user['id']); ?>" <?php echo e(request('causer_id') == $user['id'] ? 'selected' : ''); ?>>
                                            <?php echo e($user['name']); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label for="date_from" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" 
                                       value="<?php echo e(request('date_from')); ?>">
                            </div>
                            <div class="col-md-1">
                                <label for="date_to" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" 
                                       value="<?php echo e(request('date_to')); ?>">
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Filter</button>
                                <a href="<?php echo e(route('audit-logs.index')); ?>" class="btn btn-outline-secondary">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">📊 Activity Log (<?php echo e($activities->total()); ?> total records)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Entity</th>
                                    <th>Description</th>
                                    <th>Changes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo e($activity->created_at->format('M d, Y')); ?><br>
                                            <?php echo e($activity->created_at->format('H:i:s')); ?>

                                        </small>
                                    </td>
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
                                    <td>
                                        <?php if($activity->event === 'created'): ?>
                                            <span class="badge bg-success">Created</span>
                                        <?php elseif($activity->event === 'updated'): ?>
                                            <span class="badge bg-warning">Updated</span>
                                        <?php elseif($activity->event === 'deleted'): ?>
                                            <span class="badge bg-danger">Deleted</span>
                                        <?php else: ?>
                                            <span class="badge bg-info"><?php echo e(ucfirst($activity->event)); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($activity->subject_type): ?>
                                            <div>
                                                <strong><?php echo e(class_basename($activity->subject_type)); ?></strong><br>
                                                <small class="text-muted">ID: <?php echo e($activity->subject_id); ?></small>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="max-width: 200px; word-wrap: break-word;">
                                            <?php echo e($activity->description); ?>

                                        </div>
                                    </td>
                                    <td>
                                        <?php if($activity->properties && isset($activity->properties['attributes'])): ?>
                                            <?php
                                                $changes = $activity->properties['attributes'];
                                                $count = count($changes);
                                            ?>
                                            <?php if($count > 0): ?>
                                                <button class="btn btn-sm btn-outline-info" type="button" 
                                                        data-bs-toggle="collapse" 
                                                        data-bs-target="#changes-<?php echo e($activity->id); ?>" 
                                                        aria-expanded="false">
                                                    <?php echo e($count); ?> field<?php echo e($count > 1 ? 's' : ''); ?>

                                                </button>
                                                <div class="collapse mt-2" id="changes-<?php echo e($activity->id); ?>">
                                                    <div class="card card-body p-2">
                                                        <?php $__currentLoopData = $changes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <small>
                                                                <strong><?php echo e($field); ?>:</strong> 
                                                                <code><?php echo e(is_array($value) ? json_encode($value) : $value); ?></code>
                                                            </small><br>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">No changes</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo e(route('audit-logs.show', $activity)); ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No audit logs found matching your criteria.
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    
                    <?php if($activities->hasPages()): ?>
                        <div class="d-flex justify-content-center mt-4">
                            <?php echo e($activities->withQueryString()->links()); ?>

                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">📈 Activity Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <h4 class="text-success"><?php echo e($activities->where('event', 'created')->count()); ?></h4>
                            <small class="text-muted">Created</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-warning"><?php echo e($activities->where('event', 'updated')->count()); ?></h4>
                            <small class="text-muted">Updated</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-danger"><?php echo e($activities->where('event', 'deleted')->count()); ?></h4>
                            <small class="text-muted">Deleted</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-info"><?php echo e($activities->count()); ?></h4>
                            <small class="text-muted">Total Shown</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .card-header, .collapse, .pagination {
        display: none !important;
    }
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views\audit-logs\index.blade.php ENDPATH**/ ?>