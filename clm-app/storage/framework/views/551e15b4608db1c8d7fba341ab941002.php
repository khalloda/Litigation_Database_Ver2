<?php
    $hearingsCount = $case->hearings->count();
    $tasksCount = $case->adminTasks()->count();
    $documentsCount = $case->documents->count();
?>

<div class="tab-pane <?php echo e(isset($active) && $active ? 'show active' : ''); ?>" id="documents-tab" role="tabpanel" aria-labelledby="documents-tab-btn" style="<?php echo e(isset($active) && $active ? '' : 'display: none;'); ?>">
    <div class="row g-3">
        
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><?php echo e(__('app.related_hearings')); ?></h6>
                    <span class="badge bg-primary"><?php echo e($hearingsCount); ?></span>
                </div>
                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $case->hearings->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hearing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="mb-2 pb-2 border-bottom">
                        <div><strong><?php echo e($hearing->hearing_date?->format('Y-m-d') ?? '-'); ?></strong></div>
                        <div class="text-muted small"><?php echo e($hearing->hearing_type ?? '-'); ?></div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-muted mb-0"><?php echo e(__('app.no_hearings_found')); ?></p>
                    <?php endif; ?>
                    <?php if($hearingsCount > 5): ?>
                    <div class="mt-2">
                        <a href="#" class="btn btn-sm btn-outline-primary"><?php echo e(__('app.view_all')); ?> (<?php echo e($hearingsCount); ?>)</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><?php echo e(__('app.related_tasks')); ?></h6>
                    <span class="badge bg-info"><?php echo e($tasksCount); ?></span>
                </div>
                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $case->adminTasks()->limit(5)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="mb-2 pb-2 border-bottom">
                        <div><strong><?php echo e($task->task_name ?? '-'); ?></strong></div>
                        <div class="text-muted small"><?php echo e($task->status ?? '-'); ?></div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-muted mb-0"><?php echo e(__('app.no_tasks_found')); ?></p>
                    <?php endif; ?>
                    <?php if($tasksCount > 5): ?>
                    <div class="mt-2">
                        <a href="#" class="btn btn-sm btn-outline-info"><?php echo e(__('app.view_all')); ?> (<?php echo e($tasksCount); ?>)</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><?php echo e(__('app.related_documents')); ?></h6>
                    <span class="badge bg-success"><?php echo e($documentsCount); ?></span>
                </div>
                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $case->documents->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="mb-2 pb-2 border-bottom">
                        <div><strong><?php echo e($document->document_name ?? '-'); ?></strong></div>
                        <div class="text-muted small"><?php echo e($document->document_type ?? '-'); ?></div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-muted mb-0"><?php echo e(__('app.no_documents_found')); ?></p>
                    <?php endif; ?>
                    <?php if($documentsCount > 5): ?>
                    <div class="mt-2">
                        <a href="#" class="btn btn-sm btn-outline-success"><?php echo e(__('app.view_all')); ?> (<?php echo e($documentsCount); ?>)</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views\cases\partials\_documents_hearings.blade.php ENDPATH**/ ?>