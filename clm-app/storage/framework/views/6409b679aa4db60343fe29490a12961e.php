<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><?php echo e(__('app.create_contact')); ?></h3>
                    <a href="<?php echo e(route('contacts.index')); ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> <?php echo e(__('app.back_to_list')); ?>

                    </a>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo e(route('contacts.store')); ?>">
                        <?php echo csrf_field(); ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
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
                                            <?php echo e($client->client_name_ar); ?> <?php echo e($client->client_name_en ? '- ' . $client->client_name_en : ''); ?>

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
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contact_name" class="form-label"><?php echo e(__('app.contact_name')); ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['contact_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        id="contact_name" name="contact_name"
                                        value="<?php echo e(old('contact_name')); ?>" required>
                                    <?php $__errorArgs = ['contact_name'];
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
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contact_type" class="form-label"><?php echo e(__('app.contact_type')); ?> <span class="text-danger">*</span></label>
                                    <select class="form-select <?php $__errorArgs = ['contact_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="contact_type" name="contact_type" required>
                                        <option value=""><?php echo e(__('app.select_contact_type')); ?></option>
                                        <option value="phone" <?php echo e(old('contact_type') == 'phone' ? 'selected' : ''); ?>><?php echo e(__('app.phone')); ?></option>
                                        <option value="mobile" <?php echo e(old('contact_type') == 'mobile' ? 'selected' : ''); ?>><?php echo e(__('app.mobile')); ?></option>
                                        <option value="email" <?php echo e(old('contact_type') == 'email' ? 'selected' : ''); ?>><?php echo e(__('app.email')); ?></option>
                                        <option value="fax" <?php echo e(old('contact_type') == 'fax' ? 'selected' : ''); ?>><?php echo e(__('app.fax')); ?></option>
                                        <option value="website" <?php echo e(old('contact_type') == 'website' ? 'selected' : ''); ?>><?php echo e(__('app.website')); ?></option>
                                        <option value="address" <?php echo e(old('contact_type') == 'address' ? 'selected' : ''); ?>><?php echo e(__('app.address')); ?></option>
                                        <option value="other" <?php echo e(old('contact_type') == 'other' ? 'selected' : ''); ?>><?php echo e(__('app.other')); ?></option>
                                    </select>
                                    <?php $__errorArgs = ['contact_type'];
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

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contact_value" class="form-label"><?php echo e(__('app.contact_value')); ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['contact_value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        id="contact_value" name="contact_value"
                                        value="<?php echo e(old('contact_value')); ?>" required>
                                    <?php $__errorArgs = ['contact_value'];
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
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_primary" name="is_primary" value="1"
                                            <?php echo e(old('is_primary') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_primary">
                                            <?php echo e(__('app.primary_contact')); ?>

                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?php echo e(route('contacts.index')); ?>" class="btn btn-secondary">
                                <?php echo e(__('app.cancel')); ?>

                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo e(__('app.create_contact')); ?>

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
        // Add input formatting based on contact type
        const contactTypeSelect = document.getElementById('contact_type');
        const contactValueInput = document.getElementById('contact_value');

        contactTypeSelect.addEventListener('change', function() {
            const type = this.value;

            // Set placeholder and input type based on contact type
            switch (type) {
                case 'email':
                    contactValueInput.type = 'email';
                    contactValueInput.placeholder = 'example@domain.com';
                    break;
                case 'phone':
                case 'mobile':
                case 'fax':
                    contactValueInput.type = 'tel';
                    contactValueInput.placeholder = '+1234567890';
                    break;
                case 'website':
                    contactValueInput.type = 'url';
                    contactValueInput.placeholder = 'https://www.example.com';
                    break;
                default:
                    contactValueInput.type = 'text';
                    contactValueInput.placeholder = '';
                    break;
            }
        });
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views\contacts\create.blade.php ENDPATH**/ ?>