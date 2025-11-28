<?php $__env->startSection('title', __('app.upload_document')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">📤 <?php echo e(__('app.upload_document')); ?></h1>
                <a href="<?php echo e(route('documents.index')); ?>" class="btn btn-outline-secondary">
                    ← <?php echo e(__('app.back_to_documents')); ?>

                </a>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">📄 <?php echo e(__('app.document_upload_form')); ?></h5>
                </div>
                <div class="card-body">
                    <?php if($errors->any()): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <form action="<?php echo e(route('documents.store')); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>

                        
                        <div class="mb-4">
                            <label for="document_storage_type" class="form-label"><?php echo e(__('app.document_storage_type')); ?> <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="document_storage_type" id="physical" value="physical" checked>
                                <label class="form-check-label" for="physical">
                                    <strong><?php echo e(__('app.storage_type_physical')); ?></strong> - <?php echo e(__('app.physical_document')); ?>

                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="document_storage_type" id="digital" value="digital">
                                <label class="form-check-label" for="digital">
                                    <strong><?php echo e(__('app.storage_type_digital')); ?></strong> - <?php echo e(__('app.digital_document')); ?>

                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="document_storage_type" id="both" value="both">
                                <label class="form-check-label" for="both">
                                    <strong><?php echo e(__('app.storage_type_both')); ?></strong> - <?php echo e(__('app.both_types')); ?>

                                </label>
                            </div>
                            <?php $__errorArgs = ['document_storage_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-danger mt-1"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        
                        <div class="mb-4" id="file-upload-section">
                            <label for="document" class="form-label"><?php echo e(__('app.document')); ?> <?php echo e(__('app.file')); ?> <span class="text-danger" id="file-required">*</span></label>
                            <input type="file" class="form-control <?php $__errorArgs = ['document'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="document" name="document" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif">
                            <div class="form-text">
                                <strong><?php echo e(__('app.supported_formats')); ?>:</strong> PDF, Word, Excel, PowerPoint, Text, Images<br>
                                <strong><?php echo e(__('app.max_file_size')); ?>:</strong> 10MB<br>
                                <span id="file-optional-text" class="text-muted d-none"><?php echo e(__('app.file_upload_optional')); ?></span>
                            </div>
                            <?php $__errorArgs = ['document'];
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

                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="client_id" class="form-label"><?php echo e(__('app.client')); ?> <span class="text-danger">*</span></label>
                                <select class="form-select <?php $__errorArgs = ['client_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="client_id" name="client_id" required>
                                    <option value=""><?php echo e(__('app.select_client')); ?></option>
                                    <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($client->id); ?>" <?php echo e(old('client_id') == $client->id ? 'selected' : ''); ?>>
                                        <?php echo e($client->client_name_ar); ?> (<?php echo e($client->client_name_en); ?>)
                                    </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['client_id'];
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
                                <label for="matter_id" class="form-label"><?php echo e(__('app.case')); ?></label>
                                <select class="form-select <?php $__errorArgs = ['matter_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="matter_id" name="matter_id">
                                    <option value=""><?php echo e(__('app.select_case')); ?></option>
                                    <?php $__currentLoopData = $cases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $case): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($case->id); ?>" <?php echo e(old('matter_id') == $case->id ? 'selected' : ''); ?>>
                                        <?php echo e($case->matter_name_ar); ?> (<?php echo e($case->matter_name_en); ?>)
                                    </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['matter_id'];
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

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="document_type" class="form-label"><?php echo e(__('app.document_type')); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php $__errorArgs = ['document_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    id="document_type" name="document_type" value="<?php echo e(old('document_type')); ?>" required>
                                <?php $__errorArgs = ['document_type'];
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
                                <label for="case_number" class="form-label"><?php echo e(__('app.case_number')); ?></label>
                                <input type="text" class="form-control <?php $__errorArgs = ['case_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    id="case_number" name="case_number" value="<?php echo e(old('case_number')); ?>">
                                <?php $__errorArgs = ['case_number'];
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

                        
                        <div class="mb-4">
                            <label for="description" class="form-label"><?php echo e(__('app.description')); ?></label>
                            <textarea class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="description" name="description" rows="3"><?php echo e(old('description')); ?></textarea>
                            <?php $__errorArgs = ['description'];
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

                        
                        <div class="mb-4" id="physical-fields">
                            <h6 class="border-bottom pb-2 mb-3">📄 <?php echo e(__('app.physical_document')); ?> <?php echo e(__('app.details')); ?></h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="pages_count" class="form-label"><?php echo e(__('app.pages_count')); ?></label>
                                    <input type="number" class="form-control <?php $__errorArgs = ['pages_count'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        id="pages_count" name="pages_count" value="<?php echo e(old('pages_count')); ?>" min="0">
                                    <?php $__errorArgs = ['pages_count'];
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
                                <div class="col-md-4">
                                    <label for="document_date" class="form-label"><?php echo e(__('app.document_date')); ?></label>
                                    <input type="date" class="form-control <?php $__errorArgs = ['document_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        id="document_date" name="document_date" value="<?php echo e(old('document_date')); ?>">
                                    <?php $__errorArgs = ['document_date'];
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
                                <div class="col-md-4">
                                    <label for="deposit_date" class="form-label"><?php echo e(__('app.deposit_date')); ?></label>
                                    <input type="date" class="form-control <?php $__errorArgs = ['deposit_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        id="deposit_date" name="deposit_date" value="<?php echo e(old('deposit_date')); ?>">
                                    <?php $__errorArgs = ['deposit_date'];
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
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="responsible_lawyer" class="form-label"><?php echo e(__('app.responsible_lawyer')); ?></label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['responsible_lawyer'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        id="responsible_lawyer" name="responsible_lawyer" value="<?php echo e(old('responsible_lawyer')); ?>">
                                    <?php $__errorArgs = ['responsible_lawyer'];
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
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="movement_card" name="movement_card" value="1" <?php echo e(old('movement_card') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="movement_card">
                                            <?php echo e(__('app.movement_card')); ?>

                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2 mb-3">🔗 <?php echo e(__('app.mfiles_integration')); ?></h6>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="mfiles_uploaded" name="mfiles_uploaded" value="1" <?php echo e(old('mfiles_uploaded') ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="mfiles_uploaded">
                                    <?php echo e(__('app.mfiles_uploaded')); ?>

                                </label>
                            </div>
                            <div class="mb-3" id="mfiles-id-section" style="display: none;">
                                <label for="mfiles_id" class="form-label"><?php echo e(__('app.mfiles_id')); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php $__errorArgs = ['mfiles_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    id="mfiles_id" name="mfiles_id" value="<?php echo e(old('mfiles_id')); ?>">
                                <div class="form-text"><?php echo e(__('app.mfiles_id_required')); ?></div>
                                <?php $__errorArgs = ['mfiles_id'];
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

                        
                        <div class="mb-4">
                            <label for="notes" class="form-label"><?php echo e(__('app.notes')); ?></label>
                            <textarea class="form-control <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="notes" name="notes" rows="3"><?php echo e(old('notes')); ?></textarea>
                            <?php $__errorArgs = ['notes'];
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

                        
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?php echo e(route('documents.index')); ?>" class="btn btn-outline-secondary">
                                <?php echo e(__('app.cancel')); ?>

                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> <?php echo e(__('app.save')); ?> <?php echo e(__('app.document')); ?>

                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const storageTypeRadios = document.querySelectorAll('input[name="document_storage_type"]');
        const fileUploadSection = document.getElementById('file-upload-section');
        const fileRequired = document.getElementById('file-required');
        const fileOptionalText = document.getElementById('file-optional-text');
        const mfilesCheckbox = document.getElementById('mfiles_uploaded');
        const mfilesIdSection = document.getElementById('mfiles-id-section');
        const mfilesIdInput = document.getElementById('mfiles_id');

        function updateFileUploadRequirement() {
            const selectedType = document.querySelector('input[name="document_storage_type"]:checked').value;

            if (selectedType === 'physical') {
                fileRequired.style.display = 'none';
                fileOptionalText.classList.remove('d-none');
                fileUploadSection.querySelector('input[type="file"]').removeAttribute('required');
            } else {
                fileRequired.style.display = 'inline';
                fileOptionalText.classList.add('d-none');
                fileUploadSection.querySelector('input[type="file"]').setAttribute('required', 'required');
            }
        }

        function updateMfilesRequirement() {
            if (mfilesCheckbox.checked) {
                mfilesIdSection.style.display = 'block';
                mfilesIdInput.setAttribute('required', 'required');
            } else {
                mfilesIdSection.style.display = 'none';
                mfilesIdInput.removeAttribute('required');
                mfilesIdInput.value = '';
            }
        }

        // Add event listeners
        storageTypeRadios.forEach(radio => {
            radio.addEventListener('change', updateFileUploadRequirement);
        });

        mfilesCheckbox.addEventListener('change', updateMfilesRequirement);

        // Initialize on page load
        updateFileUploadRequirement();
        updateMfilesRequirement();
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views\documents\create.blade.php ENDPATH**/ ?>