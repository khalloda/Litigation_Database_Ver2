<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><?php echo e($optionSet->name); ?></h2>
                <a href="<?php echo e(route('admin.options.index')); ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> <?php echo e(__('app.back')); ?>

                </a>
            </div>
        </div>
    </div>

    <?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo e(session('success')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo e(session('error')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Option Set Details -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?php echo e(__('app.option_set_details')); ?></h5>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $optionSet)): ?>
                    <a href="<?php echo e(route('admin.options.edit', $optionSet)); ?>" class="btn btn-sm btn-warning">
                        <i class="bi bi-pencil"></i> <?php echo e(__('app.edit')); ?>

                    </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-5"><?php echo e(__('app.key')); ?>:</dt>
                        <dd class="col-sm-7"><code><?php echo e($optionSet->key); ?></code></dd>

                        <dt class="col-sm-5"><?php echo e(__('app.name_en')); ?>:</dt>
                        <dd class="col-sm-7"><?php echo e($optionSet->name_en); ?></dd>

                        <dt class="col-sm-5"><?php echo e(__('app.name_ar')); ?>:</dt>
                        <dd class="col-sm-7"><?php echo e($optionSet->name_ar); ?></dd>

                        <dt class="col-sm-5"><?php echo e(__('app.status')); ?>:</dt>
                        <dd class="col-sm-7">
                            <?php if($optionSet->is_active): ?>
                            <span class="badge bg-success"><?php echo e(__('app.active')); ?></span>
                            <?php else: ?>
                            <span class="badge bg-secondary"><?php echo e(__('app.inactive')); ?></span>
                            <?php endif; ?>
                        </dd>

                        <?php if($optionSet->description_en): ?>
                        <dt class="col-sm-5"><?php echo e(__('app.description_en')); ?>:</dt>
                        <dd class="col-sm-7"><?php echo e($optionSet->description_en); ?></dd>
                        <?php endif; ?>

                        <?php if($optionSet->description_ar): ?>
                        <dt class="col-sm-5"><?php echo e(__('app.description_ar')); ?>:</dt>
                        <dd class="col-sm-7"><?php echo e($optionSet->description_ar); ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <!-- Add New Value Form -->
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\OptionValue::class)): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo e(__('app.add_new_value')); ?></h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('admin.options.values.store', $optionSet)); ?>" method="POST">
                        <?php echo csrf_field(); ?>

                        <div class="mb-3">
                            <label for="code" class="form-label"><?php echo e(__('app.code')); ?> <span class="text-danger">*</span></label>
                            <input type="text"
                                class="form-control <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="code"
                                name="code"
                                value="<?php echo e(old('code')); ?>"
                                required>
                            <?php $__errorArgs = ['code'];
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
                            <label for="label_en" class="form-label"><?php echo e(__('app.label_en')); ?> <span class="text-danger">*</span></label>
                            <input type="text"
                                class="form-control <?php $__errorArgs = ['label_en'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="label_en"
                                name="label_en"
                                value="<?php echo e(old('label_en')); ?>"
                                required>
                            <?php $__errorArgs = ['label_en'];
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
                            <label for="label_ar" class="form-label"><?php echo e(__('app.label_ar')); ?> <span class="text-danger">*</span></label>
                            <input type="text"
                                class="form-control <?php $__errorArgs = ['label_ar'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="label_ar"
                                name="label_ar"
                                value="<?php echo e(old('label_ar')); ?>"
                                required>
                            <?php $__errorArgs = ['label_ar'];
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
                            <label for="position" class="form-label"><?php echo e(__('app.position')); ?></label>
                            <input type="number"
                                class="form-control <?php $__errorArgs = ['position'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="position"
                                name="position"
                                value="<?php echo e(old('position', $optionSet->optionValues->count() + 1)); ?>"
                                min="0">
                            <?php $__errorArgs = ['position'];
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

                        <div class="mb-3 form-check">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox"
                                class="form-check-input"
                                id="is_active"
                                name="is_active"
                                value="1"
                                <?php echo e(old('is_active', true) ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="is_active">
                                <?php echo e(__('app.is_active')); ?>

                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle"></i> <?php echo e(__('app.add_value')); ?>

                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Option Values List -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo e(__('app.option_values')); ?> (<?php echo e($optionSet->optionValues->count()); ?>)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('app.position')); ?></th>
                                    <th><?php echo e(__('app.code')); ?></th>
                                    <th><?php echo e(__('app.label_en')); ?></th>
                                    <th><?php echo e(__('app.label_ar')); ?></th>
                                    <th><?php echo e(__('app.status')); ?></th>
                                    <th><?php echo e(__('app.actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $optionSet->optionValues->sortBy('position'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($value->position); ?></td>
                                    <td><code><?php echo e($value->code); ?></code></td>
                                    <td><?php echo e($value->label_en); ?></td>
                                    <td><?php echo e($value->label_ar); ?></td>
                                    <td>
                                        <?php if($value->is_active): ?>
                                        <span class="badge bg-success"><?php echo e(__('app.active')); ?></span>
                                        <?php else: ?>
                                        <span class="badge bg-secondary"><?php echo e(__('app.inactive')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $value)): ?>
                                            <button type="button"
                                                class="btn btn-sm btn-warning"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editValueModal<?php echo e($value->id); ?>"
                                                title="<?php echo e(__('app.edit')); ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $value)): ?>
                                            <form action="<?php echo e(route('admin.options.values.destroy', $value)); ?>"
                                                method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('<?php echo e(__('app.confirm_delete_option_value')); ?>');">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit"
                                                    class="btn btn-sm btn-danger"
                                                    title="<?php echo e(__('app.delete')); ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Edit Value Modal -->
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $value)): ?>
                                <div class="modal fade" id="editValueModal<?php echo e($value->id); ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="<?php echo e(route('admin.options.values.update', $value)); ?>" method="POST">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PUT'); ?>

                                                <div class="modal-header">
                                                    <h5 class="modal-title"><?php echo e(__('app.edit_value')); ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>

                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label for="edit_code_<?php echo e($value->id); ?>" class="form-label"><?php echo e(__('app.code')); ?> <span class="text-danger">*</span></label>
                                                        <input type="text"
                                                            class="form-control"
                                                            id="edit_code_<?php echo e($value->id); ?>"
                                                            name="code"
                                                            value="<?php echo e($value->code); ?>"
                                                            required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="edit_label_en_<?php echo e($value->id); ?>" class="form-label"><?php echo e(__('app.label_en')); ?> <span class="text-danger">*</span></label>
                                                        <input type="text"
                                                            class="form-control"
                                                            id="edit_label_en_<?php echo e($value->id); ?>"
                                                            name="label_en"
                                                            value="<?php echo e($value->label_en); ?>"
                                                            required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="edit_label_ar_<?php echo e($value->id); ?>" class="form-label"><?php echo e(__('app.label_ar')); ?> <span class="text-danger">*</span></label>
                                                        <input type="text"
                                                            class="form-control"
                                                            id="edit_label_ar_<?php echo e($value->id); ?>"
                                                            name="label_ar"
                                                            value="<?php echo e($value->label_ar); ?>"
                                                            required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="edit_position_<?php echo e($value->id); ?>" class="form-label"><?php echo e(__('app.position')); ?></label>
                                                        <input type="number"
                                                            class="form-control"
                                                            id="edit_position_<?php echo e($value->id); ?>"
                                                            name="position"
                                                            value="<?php echo e($value->position); ?>"
                                                            min="0">
                                                    </div>

                                                    <div class="mb-3 form-check">
                                                        <input type="hidden" name="is_active" value="0">
                                                        <input type="checkbox"
                                                            class="form-check-input"
                                                            id="edit_is_active_<?php echo e($value->id); ?>"
                                                            name="is_active"
                                                            value="1"
                                                            <?php echo e($value->is_active ? 'checked' : ''); ?>>
                                                        <label class="form-check-label" for="edit_is_active_<?php echo e($value->id); ?>">
                                                            <?php echo e(__('app.is_active')); ?>

                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__('app.cancel')); ?></button>
                                                    <button type="submit" class="btn btn-primary"><?php echo e(__('app.save_changes')); ?></button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <?php echo e(__('app.no_option_values_found')); ?>

                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/admin/options/show.blade.php ENDPATH**/ ?>