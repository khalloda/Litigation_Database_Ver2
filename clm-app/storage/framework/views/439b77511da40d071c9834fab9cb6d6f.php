<?php $__env->startSection('title', __('app.case_details')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid" data-case-id="<?php echo e($case->id); ?>">
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

    
    <div class="accordion mb-3" id="caseAccordion">
        
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading-overview">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-overview" aria-expanded="true" aria-controls="collapse-overview">
                    <?php echo e(__('app.overview')); ?>

                </button>
            </h2>
            <div id="collapse-overview" class="accordion-collapse collapse show" aria-labelledby="heading-overview" data-bs-parent="#caseAccordion">
                <div class="accordion-body">
                    <?php echo $__env->make('cases.partials._section_content', ['section' => 'overview'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div>
            </div>
        </div>

        
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading-parties">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-parties" aria-expanded="false" aria-controls="collapse-parties">
                    <?php echo e(__('app.parties')); ?>

                </button>
            </h2>
            <div id="collapse-parties" class="accordion-collapse collapse" aria-labelledby="heading-parties" data-bs-parent="#caseAccordion">
                <div class="accordion-body">
                    <?php echo $__env->make('cases.partials._section_content', ['section' => 'parties'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    
                    <div class="mt-4">
                        <?php echo $__env->make('cases.partials._opponents', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading-court">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-court" aria-expanded="false" aria-controls="collapse-court">
                    <?php echo e(__('app.court_circuit')); ?>

                </button>
            </h2>
            <div id="collapse-court" class="accordion-collapse collapse" aria-labelledby="heading-court" data-bs-parent="#caseAccordion">
                <div class="accordion-body">
                    <?php echo $__env->make('cases.partials._section_content', ['section' => 'court'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div>
            </div>
        </div>

        
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading-status">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-status" aria-expanded="false" aria-controls="collapse-status">
                    <?php echo e(__('app.status_progress')); ?>

                </button>
            </h2>
            <div id="collapse-status" class="accordion-collapse collapse" aria-labelledby="heading-status" data-bs-parent="#caseAccordion">
                <div class="accordion-body">
                    <?php echo $__env->make('cases.partials._section_content', ['section' => 'status'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div>
            </div>
        </div>

        
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading-financials">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-financials" aria-expanded="false" aria-controls="collapse-financials">
                    <?php echo e(__('app.financials')); ?>

                </button>
            </h2>
            <div id="collapse-financials" class="accordion-collapse collapse" aria-labelledby="heading-financials" data-bs-parent="#caseAccordion">
                <div class="accordion-body">
                    <?php echo $__env->make('cases.partials._section_content', ['section' => 'financials'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div>
            </div>
        </div>

        
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading-documents">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-documents" aria-expanded="false" aria-controls="collapse-documents">
                    <?php echo e(__('app.documents_hearings')); ?>

                </button>
            </h2>
            <div id="collapse-documents" class="accordion-collapse collapse" aria-labelledby="heading-documents" data-bs-parent="#caseAccordion">
                <div class="accordion-body">
                    <?php echo $__env->make('cases.partials._documents_hearings_content', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div>
            </div>
        </div>

        
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading-meta">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-meta" aria-expanded="false" aria-controls="collapse-meta">
                    <?php echo e(__('app.meta_audit')); ?>

                </button>
            </h2>
            <div id="collapse-meta" class="accordion-collapse collapse" aria-labelledby="heading-meta" data-bs-parent="#caseAccordion">
                <div class="accordion-body">
                    <?php echo $__env->make('cases.partials._section_content', ['section' => 'meta'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/case-opponents.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/cases/show.blade.php ENDPATH**/ ?>