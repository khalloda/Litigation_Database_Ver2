<?php $__env->startSection('title', __('app.edit_court')); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4"><?php echo e(__('app.edit_court')); ?></h1>
        <a href="<?php echo e(route('courts.show', $court)); ?>" class="btn btn-outline-secondary"><?php echo e(__('app.cancel')); ?></a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="<?php echo e(route('courts.update', $court)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="court_name_ar" class="form-label"><?php echo e(__('app.court_name_ar')); ?> *</label>
                        <input type="text" class="form-control <?php $__errorArgs = ['court_name_ar'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               id="court_name_ar" name="court_name_ar" value="<?php echo e(old('court_name_ar', $court->court_name_ar)); ?>">
                        <?php $__errorArgs = ['court_name_ar'];
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
                        <label for="court_name_en" class="form-label"><?php echo e(__('app.court_name_en')); ?> *</label>
                        <input type="text" class="form-control <?php $__errorArgs = ['court_name_en'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               id="court_name_en" name="court_name_en" value="<?php echo e(old('court_name_en', $court->court_name_en)); ?>">
                        <?php $__errorArgs = ['court_name_en'];
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

                <!-- Circuit Rows Container -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0"><?php echo e(__('app.court_circuits')); ?></h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="add-circuit-row">
                                <i class="fas fa-plus"></i> <?php echo e(__('app.add_circuit')); ?>

                            </button>
                        </div>

                        <div id="circuit-rows-container">
                            <?php if($court->circuits->count() > 0): ?>
                                <?php $__currentLoopData = $court->circuits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $circuit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="circuit-row card border-secondary mb-2">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="form-label"><?php echo e(__('app.circuit_name')); ?></label>
                                                <select class="form-select circuit-name-select" name="court_circuits[<?php echo e($index); ?>][name_id]">
                                                    <option value=""><?php echo e(__('app.select_circuit_name')); ?></option>
                                                    <?php $__currentLoopData = $circuitNames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $circuitName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($circuitName->id); ?>" <?php echo e($circuit->circuit_name_id == $circuitName->id ? 'selected' : ''); ?>>
                                                        <?php echo e(app()->getLocale() === 'ar' ? $circuitName->label_ar : $circuitName->label_en); ?>

                                                    </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label"><?php echo e(__('app.circuit_serial')); ?></label>
                                                <select class="form-select circuit-serial-select" name="court_circuits[<?php echo e($index); ?>][serial_id]">
                                                    <option value=""><?php echo e(__('app.select_circuit_serial')); ?></option>
                                                    <?php $__currentLoopData = $circuitSerials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $circuitSerial): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($circuitSerial->id); ?>" <?php echo e($circuit->circuit_serial_id == $circuitSerial->id ? 'selected' : ''); ?>>
                                                        <?php echo e(app()->getLocale() === 'ar' ? $circuitSerial->label_ar : $circuitSerial->label_en); ?>

                                                    </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label"><?php echo e(__('app.circuit_shift')); ?></label>
                                                <select class="form-select circuit-shift-select" name="court_circuits[<?php echo e($index); ?>][shift_id]">
                                                    <option value=""><?php echo e(__('app.select_circuit_shift')); ?></option>
                                                    <?php $__currentLoopData = $circuitShifts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $circuitShift): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($circuitShift->id); ?>" <?php echo e($circuit->circuit_shift_id == $circuitShift->id ? 'selected' : ''); ?>>
                                                        <?php echo e(app()->getLocale() === 'ar' ? $circuitShift->label_ar : $circuitShift->label_en); ?>

                                                    </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3 d-flex align-items-end">
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-circuit-row">
                                                    <i class="fas fa-trash"></i> <?php echo e(__('app.remove_circuit')); ?>

                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php else: ?>
                                <!-- Default empty row -->
                                <div class="circuit-row card border-secondary mb-2">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="form-label"><?php echo e(__('app.circuit_name')); ?></label>
                                                <select class="form-select circuit-name-select" name="court_circuits[0][name_id]">
                                                    <option value=""><?php echo e(__('app.select_circuit_name')); ?></option>
                                                    <?php $__currentLoopData = $circuitNames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $circuitName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($circuitName->id); ?>">
                                                        <?php echo e(app()->getLocale() === 'ar' ? $circuitName->label_ar : $circuitName->label_en); ?>

                                                    </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label"><?php echo e(__('app.circuit_serial')); ?></label>
                                                <select class="form-select circuit-serial-select" name="court_circuits[0][serial_id]">
                                                    <option value=""><?php echo e(__('app.select_circuit_serial')); ?></option>
                                                    <?php $__currentLoopData = $circuitSerials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $circuitSerial): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($circuitSerial->id); ?>">
                                                        <?php echo e(app()->getLocale() === 'ar' ? $circuitSerial->label_ar : $circuitSerial->label_en); ?>

                                                    </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label"><?php echo e(__('app.circuit_shift')); ?></label>
                                                <select class="form-select circuit-shift-select" name="court_circuits[0][shift_id]">
                                                    <option value=""><?php echo e(__('app.select_circuit_shift')); ?></option>
                                                    <?php $__currentLoopData = $circuitShifts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $circuitShift): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($circuitShift->id); ?>">
                                                        <?php echo e(app()->getLocale() === 'ar' ? $circuitShift->label_ar : $circuitShift->label_en); ?>

                                                    </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3 d-flex align-items-end">
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-circuit-row">
                                                    <i class="fas fa-trash"></i> <?php echo e(__('app.remove_circuit')); ?>

                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php $__errorArgs = ['court_circuits'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="court_secretaries" class="form-label"><?php echo e(__('app.court_secretaries')); ?></label>
                        <select class="form-select select2-multi <?php $__errorArgs = ['court_secretaries'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="court_secretaries" name="court_secretaries[]" multiple>
                            <?php $__currentLoopData = $secretaryOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($option->id); ?>"
                                <?php echo e(in_array($option->id, old('court_secretaries', $court->secretaries->pluck('id')->toArray())) ? 'selected' : ''); ?>>
                                <?php echo e(app()->getLocale() === 'ar' ? $option->label_ar : $option->label_en); ?>

                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['court_secretaries'];
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
                        <label for="court_floors" class="form-label"><?php echo e(__('app.court_floors')); ?></label>
                        <select class="form-select select2-multi <?php $__errorArgs = ['court_floors'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="court_floors" name="court_floors[]" multiple>
                            <?php $__currentLoopData = $floorOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($option->id); ?>"
                                <?php echo e(in_array($option->id, old('court_floors', $court->floors->pluck('id')->toArray())) ? 'selected' : ''); ?>>
                                <?php echo e(app()->getLocale() === 'ar' ? $option->label_ar : $option->label_en); ?>

                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['court_floors'];
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
                        <label for="court_halls" class="form-label"><?php echo e(__('app.court_halls')); ?></label>
                        <select class="form-select select2-multi <?php $__errorArgs = ['court_halls'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="court_halls" name="court_halls[]" multiple>
                            <?php $__currentLoopData = $hallOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($option->id); ?>"
                                <?php echo e(in_array($option->id, old('court_halls', $court->halls->pluck('id')->toArray())) ? 'selected' : ''); ?>>
                                <?php echo e(app()->getLocale() === 'ar' ? $option->label_ar : $option->label_en); ?>

                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['court_halls'];
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
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                               value="1" <?php echo e(old('is_active', $court->is_active) ? 'checked' : ''); ?>>
                        <label class="form-check-label" for="is_active">
                            <?php echo e(__('app.active')); ?>

                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="<?php echo e(route('courts.show', $court)); ?>" class="btn btn-secondary me-2"><?php echo e(__('app.cancel')); ?></a>
                    <button type="submit" class="btn btn-primary"><?php echo e(__('app.save')); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function() {
    $('.select2-multi').select2({
        theme: 'bootstrap-5',
        multiple: true,
        allowClear: true,
        placeholder: '<?php echo e(__("app.select_multiple")); ?>',
        width: '100%'
    });

    let circuitRowIndex = <?php echo e($court->circuits->count()); ?>;

    // Add circuit row
    $('#add-circuit-row').on('click', function() {
        const template = `
            <div class="circuit-row card border-secondary mb-2">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label"><?php echo e(__('app.circuit_name')); ?></label>
                            <select class="form-select circuit-name-select" name="court_circuits[${circuitRowIndex}][name_id]">
                                <option value=""><?php echo e(__('app.select_circuit_name')); ?></option>
                                <?php $__currentLoopData = $circuitNames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $circuitName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($circuitName->id); ?>">
                                    <?php echo e(app()->getLocale() === 'ar' ? $circuitName->label_ar : $circuitName->label_en); ?>

                                </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><?php echo e(__('app.circuit_serial')); ?></label>
                            <select class="form-select circuit-serial-select" name="court_circuits[${circuitRowIndex}][serial_id]">
                                <option value=""><?php echo e(__('app.select_circuit_serial')); ?></option>
                                <?php $__currentLoopData = $circuitSerials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $circuitSerial): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($circuitSerial->id); ?>">
                                    <?php echo e(app()->getLocale() === 'ar' ? $circuitSerial->label_ar : $circuitSerial->label_en); ?>

                                </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><?php echo e(__('app.circuit_shift')); ?></label>
                            <select class="form-select circuit-shift-select" name="court_circuits[${circuitRowIndex}][shift_id]">
                                <option value=""><?php echo e(__('app.select_circuit_shift')); ?></option>
                                <?php $__currentLoopData = $circuitShifts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $circuitShift): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($circuitShift->id); ?>">
                                    <?php echo e(app()->getLocale() === 'ar' ? $circuitShift->label_ar : $circuitShift->label_en); ?>

                                </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-circuit-row">
                                <i class="fas fa-trash"></i> <?php echo e(__('app.remove_circuit')); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#circuit-rows-container').append(template);
        circuitRowIndex++;
    });

    // Remove circuit row
    $(document).on('click', '.remove-circuit-row', function() {
        $(this).closest('.circuit-row').remove();
    });
});
</script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/courts/edit.blade.php ENDPATH**/ ?>