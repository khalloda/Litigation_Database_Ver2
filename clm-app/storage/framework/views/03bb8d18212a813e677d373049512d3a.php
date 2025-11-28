<?php $__env->startSection('title', __('app.case_opponents_import')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4 class="mb-0"><?php echo e(__('app.case_opponents_import')); ?></h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <?php echo e(__('app.case_opponents_import_info')); ?>

                    </div>

                    <form action="<?php echo e(route('case-opponents.import.process-upload')); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>

                        <div class="mb-3">
                            <label for="file" class="form-label"><?php echo e(__('app.select_file')); ?></label>
                            <input type="file" class="form-control <?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   id="file" name="file" accept=".csv,.xlsx,.xls" required>
                            <?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> <?php echo e(__('app.upload_file')); ?>

                            </button>
                            <a href="<?php echo e(route('import.index')); ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> <?php echo e(__('app.back_to_imports')); ?>

                            </a>
                        </div>
                    </form>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h5><?php echo e(__('app.download_templates')); ?></h5>
                            <p class="text-muted"><?php echo e(__('app.case_opponents_template_info')); ?></p>

                            <div class="d-grid gap-2">
                                <a href="<?php echo e(route('case-opponents.template.csv')); ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-file-csv"></i> <?php echo e(__('app.download_csv_template')); ?>

                                </a>
                                <a href="<?php echo e(route('case-opponents.template.xlsx')); ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-file-excel"></i> <?php echo e(__('app.download_xlsx_template')); ?>

                                </a>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h5><?php echo e(__('app.import_instructions')); ?></h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> <?php echo e(__('app.case_opponents_instruction_1')); ?></li>
                                <li><i class="fas fa-check text-success"></i> <?php echo e(__('app.case_opponents_instruction_2')); ?></li>
                                <li><i class="fas fa-check text-success"></i> <?php echo e(__('app.case_opponents_instruction_3')); ?></li>
                                <li><i class="fas fa-check text-success"></i> <?php echo e(__('app.case_opponents_instruction_4')); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views\import\case-opponents-upload.blade.php ENDPATH**/ ?>