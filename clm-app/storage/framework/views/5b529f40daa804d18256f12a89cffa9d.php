<?php $__env->startSection('title', __('app.create_lawyer')); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4"><?php echo e(__('app.create_lawyer')); ?></h1>
        <a href="<?php echo e(route('lawyers.index')); ?>" class="btn btn-outline-secondary"><?php echo e(__('app.cancel')); ?></a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="<?php echo e(route('lawyers.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="lawyer_name_ar" class="form-label"><?php echo e(__('app.lawyer_name_ar')); ?></label>
                        <input type="text" class="form-control <?php $__errorArgs = ['lawyer_name_ar'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="lawyer_name_ar" name="lawyer_name_ar" value="<?php echo e(old('lawyer_name_ar')); ?>">
                        <?php $__errorArgs = ['lawyer_name_ar'];
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
                    <div class="col-md-6">
                        <label for="lawyer_name_en" class="form-label"><?php echo e(__('app.lawyer_name_en')); ?></label>
                        <input type="text" class="form-control <?php $__errorArgs = ['lawyer_name_en'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="lawyer_name_en" name="lawyer_name_en" value="<?php echo e(old('lawyer_name_en')); ?>">
                        <?php $__errorArgs = ['lawyer_name_en'];
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
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="title_id" class="form-label"><?php echo e(__('app.lawyer_title')); ?></label>
                        <select class="form-select <?php $__errorArgs = ['title_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="title_id" name="title_id">
                            <option value=""><?php echo e(__('app.select_option')); ?></option>
                            <?php if(isset($titles)): ?>
                            <?php $__currentLoopData = $titles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($t->id); ?>" <?php echo e(old('title_id') == $t->id ? 'selected' : ''); ?>>
                                <?php echo e(app()->getLocale() === 'ar' ? $t->label_ar : $t->label_en); ?>

                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                        </select>
                        <?php $__errorArgs = ['title_id'];
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
                    <div class="col-md-6">
                        <label for="lawyer_email" class="form-label"><?php echo e(__('app.lawyer_email')); ?></label>
                        <input type="email" class="form-control <?php $__errorArgs = ['lawyer_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="lawyer_email" name="lawyer_email" value="<?php echo e(old('lawyer_email')); ?>">
                        <?php $__errorArgs = ['lawyer_email'];
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
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="attendance_track" name="attendance_track" value="1" <?php echo e(old('attendance_track') ? 'checked' : ''); ?>>
                        <label class="form-check-label" for="attendance_track">
                            <?php echo e(__('app.attendance_track')); ?>

                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="<?php echo e(route('lawyers.index')); ?>" class="btn btn-secondary me-2"><?php echo e(__('app.cancel')); ?></a>
                    <button type="submit" class="btn btn-primary"><?php echo e(__('app.save')); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/lawyers/create.blade.php ENDPATH**/ ?>