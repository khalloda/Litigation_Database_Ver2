<?php $__env->startSection('title', __('app.create_case')); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4"><?php echo e(__('app.create_case')); ?></h1>
        <a href="<?php echo e(route('cases.index')); ?>" class="btn btn-outline-secondary"><?php echo e(__('app.cancel')); ?></a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="<?php echo e(route('cases.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="client_id" class="form-label"><?php echo e(__('app.client')); ?> *</label>
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
                                <?php echo e($client->client_name_ar ?? $client->client_name_en); ?> (ID: <?php echo e($client->id); ?>)
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
                    <div class="col-md-4">
                        <label for="client_in_case_name" class="form-label"><?php echo e(__('app.client_in_case_name')); ?></label>
                        <input type="text" class="form-control <?php $__errorArgs = ['client_in_case_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="client_in_case_name" name="client_in_case_name" value="<?php echo e(old('client_in_case_name')); ?>">
                        <?php $__errorArgs = ['client_in_case_name'];
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
                        <label for="matter_status_id" class="form-label"><?php echo e(__('app.matter_status')); ?></label>
                        <select class="form-select <?php $__errorArgs = ['matter_status_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="matter_status_id" name="matter_status_id">
                            <option value=""><?php echo e(__('app.select_option')); ?></option>
                            <?php $__currentLoopData = $caseStatuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($ov->id); ?>" <?php echo e(old('matter_status_id') == $ov->id ? 'selected' : ''); ?>>
                                <?php echo e(app()->getLocale() === 'ar' ? $ov->label_ar : $ov->label_en); ?>

                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['matter_status_id'];
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
                        <label for="matter_name_ar" class="form-label"><?php echo e(__('app.matter_name_ar')); ?></label>
                        <input type="text" class="form-control <?php $__errorArgs = ['matter_name_ar'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="matter_name_ar" name="matter_name_ar" value="<?php echo e(old('matter_name_ar')); ?>">
                        <?php $__errorArgs = ['matter_name_ar'];
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
                        <label for="matter_name_en" class="form-label"><?php echo e(__('app.matter_name_en')); ?></label>
                        <input type="text" class="form-control <?php $__errorArgs = ['matter_name_en'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="matter_name_en" name="matter_name_en" value="<?php echo e(old('matter_name_en')); ?>">
                        <?php $__errorArgs = ['matter_name_en'];
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
                    <label for="matter_description" class="form-label"><?php echo e(__('app.matter_description')); ?></label>
                    <textarea class="form-control <?php $__errorArgs = ['matter_description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="matter_description" name="matter_description" rows="3"><?php echo e(old('matter_description')); ?></textarea>
                    <?php $__errorArgs = ['matter_description'];
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

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="matter_category_id" class="form-label"><?php echo e(__('app.matter_category')); ?></label>
                        <select class="form-select <?php $__errorArgs = ['matter_category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="matter_category_id" name="matter_category_id">
                            <option value=""><?php echo e(__('app.select_option')); ?></option>
                            <?php $__currentLoopData = $caseCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($ov->id); ?>" <?php echo e(old('matter_category_id') == $ov->id ? 'selected' : ''); ?>>
                                <?php echo e(app()->getLocale() === 'ar' ? $ov->label_ar : $ov->label_en); ?>

                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['matter_category_id'];
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
                    <div class="col-md-3">
                        <label for="matter_degree_id" class="form-label"><?php echo e(__('app.matter_degree')); ?></label>
                        <select class="form-select <?php $__errorArgs = ['matter_degree_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="matter_degree_id" name="matter_degree_id">
                            <option value=""><?php echo e(__('app.select_option')); ?></option>
                            <?php $__currentLoopData = $caseDegrees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($ov->id); ?>" <?php echo e(old('matter_degree_id') == $ov->id ? 'selected' : ''); ?>>
                                <?php echo e(app()->getLocale() === 'ar' ? $ov->label_ar : $ov->label_en); ?>

                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['matter_degree_id'];
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
                    <div class="col-md-3">
                        <label for="matter_importance_id" class="form-label"><?php echo e(__('app.matter_importance')); ?></label>
                        <select class="form-select <?php $__errorArgs = ['matter_importance_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="matter_importance_id" name="matter_importance_id">
                            <option value=""><?php echo e(__('app.select_option')); ?></option>
                            <?php $__currentLoopData = $caseImportance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($ov->id); ?>" <?php echo e(old('matter_importance_id') == $ov->id ? 'selected' : ''); ?>>
                                <?php echo e(app()->getLocale() === 'ar' ? $ov->label_ar : $ov->label_en); ?>

                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['matter_importance_id'];
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
                    <div class="col-md-3">
                        <label for="matter_branch_id" class="form-label"><?php echo e(__('app.client_branch')); ?></label>
                        <select class="form-select <?php $__errorArgs = ['matter_branch_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="matter_branch_id" name="matter_branch_id">
                            <option value=""><?php echo e(__('app.select_option')); ?></option>
                            <?php $__currentLoopData = $caseBranches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($ov->id); ?>" <?php echo e(old('matter_branch_id') == $ov->id ? 'selected' : ''); ?>>
                                <?php echo e(app()->getLocale() === 'ar' ? $ov->label_ar : $ov->label_en); ?>

                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['matter_branch_id'];
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
                        <label for="court_id" class="form-label"><?php echo e(__('app.matter_court')); ?></label>
                        <select class="form-select select2-court <?php $__errorArgs = ['court_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="court_id" name="court_id">
                            <option value=""><?php echo e(__('app.select_court')); ?></option>
                            <?php $__currentLoopData = $courts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $court): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($court->id); ?>" <?php echo e(old('court_id') == $court->id ? 'selected' : ''); ?>>
                                <?php echo e(app()->getLocale() === 'ar' ? $court->court_name_ar : $court->court_name_en); ?>

                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['court_id'];
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
                        <label for="matter_destination_id" class="form-label"><?php echo e(__('app.matter_destination')); ?></label>
                        <select class="form-select <?php $__errorArgs = ['matter_destination_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="matter_destination_id" name="matter_destination_id">
                            <option value=""><?php echo e(__('app.select_court')); ?></option>
                            <?php $__currentLoopData = $courts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $court): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($court->id); ?>" <?php echo e(old('matter_destination_id') == $court->id ? 'selected' : ''); ?>>
                                <?php echo e(app()->getLocale() === 'ar' ? $court->court_name_ar : $court->court_name_en); ?>

                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['matter_destination_id'];
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
                    <div class="col-md-4">
                        <label for="matter_shelf" class="form-label"><?php echo e(__('app.matter_shelf')); ?></label>
                        <input type="text" maxlength="10" class="form-control <?php $__errorArgs = ['matter_shelf'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="matter_shelf" name="matter_shelf" value="<?php echo e(old('matter_shelf')); ?>">
                        <?php $__errorArgs = ['matter_shelf'];
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

                <!-- Circuit Container -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><?php echo e(__('app.circuit_container')); ?></h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="circuit_name_id" class="form-label"><?php echo e(__('app.circuit_name')); ?></label>
                                        <select class="form-select select2-cascade <?php $__errorArgs = ['circuit_name_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                id="circuit_name_id" name="circuit_name_id" disabled>
                                            <option value=""><?php echo e(__('app.select_court_first')); ?></option>
                                            <?php $__currentLoopData = $circuitNames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $circuitName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($circuitName->id); ?>" <?php echo e(old('circuit_name_id') == $circuitName->id ? 'selected' : ''); ?>>
                                                <?php echo e(app()->getLocale() === 'ar' ? $circuitName->label_ar : $circuitName->label_en); ?>

                                            </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <?php $__errorArgs = ['circuit_name_id'];
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
                                        <label for="circuit_serial_id" class="form-label"><?php echo e(__('app.circuit_serial')); ?></label>
                                        <select class="form-select select2-cascade <?php $__errorArgs = ['circuit_serial_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                id="circuit_serial_id" name="circuit_serial_id" disabled>
                                            <option value=""><?php echo e(__('app.select_court_first')); ?></option>
                                            <?php $__currentLoopData = $circuitSerials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $circuitSerial): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($circuitSerial->id); ?>" <?php echo e(old('circuit_serial_id') == $circuitSerial->id ? 'selected' : ''); ?>>
                                                <?php echo e(app()->getLocale() === 'ar' ? $circuitSerial->label_ar : $circuitSerial->label_en); ?>

                                            </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <?php $__errorArgs = ['circuit_serial_id'];
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
                                        <label for="circuit_shift_id" class="form-label"><?php echo e(__('app.circuit_shift')); ?></label>
                                        <select class="form-select select2-cascade <?php $__errorArgs = ['circuit_shift_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                id="circuit_shift_id" name="circuit_shift_id" disabled>
                                            <option value=""><?php echo e(__('app.select_court_first')); ?></option>
                                            <?php $__currentLoopData = $circuitShifts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $circuitShift): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($circuitShift->id); ?>" <?php echo e(old('circuit_shift_id') == $circuitShift->id ? 'selected' : ''); ?>>
                                                <?php echo e(app()->getLocale() === 'ar' ? $circuitShift->label_ar : $circuitShift->label_en); ?>

                                            </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <?php $__errorArgs = ['circuit_shift_id'];
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
                        </div>
                    </div>
                </div>

                <!-- Cascading Court Details -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="circuit_secretary" class="form-label"><?php echo e(__('app.circuit_secretary')); ?></label>
                        <select class="form-select select2-cascade <?php $__errorArgs = ['circuit_secretary'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="circuit_secretary" name="circuit_secretary" disabled>
                            <option value=""><?php echo e(__('app.select_court_first')); ?></option>
                        </select>
                        <?php $__errorArgs = ['circuit_secretary'];
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
                        <label for="court_floor" class="form-label"><?php echo e(__('app.court_floor')); ?></label>
                        <select class="form-select select2-cascade <?php $__errorArgs = ['court_floor'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="court_floor" name="court_floor" disabled>
                            <option value=""><?php echo e(__('app.select_court_first')); ?></option>
                        </select>
                        <?php $__errorArgs = ['court_floor'];
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
                        <label for="court_hall" class="form-label"><?php echo e(__('app.court_hall')); ?></label>
                        <select class="form-select select2-cascade <?php $__errorArgs = ['court_hall'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="court_hall" name="court_hall" disabled>
                            <option value=""><?php echo e(__('app.select_court_first')); ?></option>
                        </select>
                        <?php $__errorArgs = ['court_hall'];
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
                        <label for="matter_start_date" class="form-label"><?php echo e(__('app.matter_start_date')); ?></label>
                        <input type="date" class="form-control <?php $__errorArgs = ['matter_start_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="matter_start_date" name="matter_start_date" value="<?php echo e(old('matter_start_date')); ?>">
                        <?php $__errorArgs = ['matter_start_date'];
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
                        <label for="matter_end_date" class="form-label"><?php echo e(__('app.matter_end_date')); ?></label>
                        <input type="date" class="form-control <?php $__errorArgs = ['matter_end_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="matter_end_date" name="matter_end_date" value="<?php echo e(old('matter_end_date')); ?>">
                        <?php $__errorArgs = ['matter_end_date'];
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

                <!-- Parties -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="client_capacity_id" class="form-label"><?php echo e(__('app.client_capacity')); ?></label>
                        <select class="form-select <?php $__errorArgs = ['client_capacity_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="client_capacity_id" name="client_capacity_id">
                            <option value=""><?php echo e(__('app.select_option')); ?></option>
                            <?php $__currentLoopData = $capacityTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($ov->id); ?>" <?php echo e(old('client_capacity_id') == $ov->id ? 'selected' : ''); ?>>
                                <?php echo e(app()->getLocale() === 'ar' ? $ov->label_ar : $ov->label_en); ?>

                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['client_capacity_id'];
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
                        <label for="client_capacity_note" class="form-label"><?php echo e(__('app.capacity_note')); ?></label>
                        <input type="text" class="form-control <?php $__errorArgs = ['client_capacity_note'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="client_capacity_note" name="client_capacity_note" value="<?php echo e(old('client_capacity_note')); ?>">
                        <?php $__errorArgs = ['client_capacity_note'];
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
                    <div class="col-md-4">
                        <label for="opponent_id" class="form-label"><?php echo e(__('app.opponent')); ?></label>
                        <select class="form-select <?php $__errorArgs = ['opponent_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="opponent_id" name="opponent_id">
                            <option value=""><?php echo e(__('app.select_option')); ?></option>
                            <?php $__currentLoopData = $opponents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($opp->id); ?>" <?php echo e(old('opponent_id') == $opp->id ? 'selected' : ''); ?>>
                                <?php echo e(app()->getLocale() === 'ar' ? $opp->opponent_name_ar : $opp->opponent_name_en); ?>

                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['opponent_id'];
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
                        <label for="opponent_in_case_name" class="form-label"><?php echo e(__('app.opponent_in_case_name')); ?></label>
                        <input type="text" class="form-control <?php $__errorArgs = ['opponent_in_case_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="opponent_in_case_name" name="opponent_in_case_name" value="<?php echo e(old('opponent_in_case_name')); ?>">
                        <?php $__errorArgs = ['opponent_in_case_name'];
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
                        <label for="opponent_capacity_id" class="form-label"><?php echo e(__('app.opponent_capacity')); ?></label>
                        <select class="form-select <?php $__errorArgs = ['opponent_capacity_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="opponent_capacity_id" name="opponent_capacity_id">
                            <option value=""><?php echo e(__('app.select_option')); ?></option>
                            <?php $__currentLoopData = $capacityTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($ov->id); ?>" <?php echo e(old('opponent_capacity_id') == $ov->id ? 'selected' : ''); ?>>
                                <?php echo e(app()->getLocale() === 'ar' ? $ov->label_ar : $ov->label_en); ?>

                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['opponent_capacity_id'];
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
                        <label for="opponent_capacity_note" class="form-label"><?php echo e(__('app.capacity_note')); ?></label>
                        <input type="text" class="form-control <?php $__errorArgs = ['opponent_capacity_note'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="opponent_capacity_note" name="opponent_capacity_note" value="<?php echo e(old('opponent_capacity_note')); ?>">
                        <?php $__errorArgs = ['opponent_capacity_note'];
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
                        <label for="matter_partner_id" class="form-label"><?php echo e(__('app.matter_partner')); ?></label>
                        <select class="form-select <?php $__errorArgs = ['matter_partner_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="matter_partner_id" name="matter_partner_id">
                            <option value=""><?php echo e(__('app.select_option')); ?></option>
                            <?php $__currentLoopData = $partnerLawyers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lawyer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($lawyer->id); ?>" <?php echo e(old('matter_partner_id') == $lawyer->id ? 'selected' : ''); ?>>
                                <?php echo e($lawyer->lawyer_name_ar ?? $lawyer->lawyer_name_en); ?>

                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['matter_partner_id'];
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
                        <label for="matter_asked_amount" class="form-label"><?php echo e(__('app.matter_asked_amount')); ?></label>
                        <input type="number" step="0.01" class="form-control <?php $__errorArgs = ['matter_asked_amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="matter_asked_amount" name="matter_asked_amount" value="<?php echo e(old('matter_asked_amount')); ?>">
                        <?php $__errorArgs = ['matter_asked_amount'];
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
                        <label for="matter_judged_amount" class="form-label"><?php echo e(__('app.matter_judged_amount')); ?></label>
                        <input type="number" step="0.01" class="form-control <?php $__errorArgs = ['matter_judged_amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="matter_judged_amount" name="matter_judged_amount" value="<?php echo e(old('matter_judged_amount')); ?>">
                        <?php $__errorArgs = ['matter_judged_amount'];
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
                        <label for="engagement_letter_no" class="form-label"><?php echo e(__('app.engagement_letter_no')); ?></label>
                        <input type="text" class="form-control <?php $__errorArgs = ['engagement_letter_no'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="engagement_letter_no" name="engagement_letter_no" value="<?php echo e(old('engagement_letter_no')); ?>">
                        <?php $__errorArgs = ['engagement_letter_no'];
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
                    <label for="notes_1" class="form-label"><?php echo e(__('app.notes')); ?></label>
                    <textarea class="form-control <?php $__errorArgs = ['notes_1'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="notes_1" name="notes_1" rows="2"><?php echo e(old('notes_1')); ?></textarea>
                    <?php $__errorArgs = ['notes_1'];
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

                <div class="d-flex justify-content-end">
                    <a href="<?php echo e(route('cases.index')); ?>" class="btn btn-secondary me-2"><?php echo e(__('app.cancel')); ?></a>
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
    // Initialize Select2 for court dropdown
    $('.select2-court').select2({
        theme: 'bootstrap-5',
        placeholder: '<?php echo e(__("app.select_court")); ?>',
        allowClear: true,
        width: '100%'
    });

    // Initialize Select2 for cascading dropdowns
    $('.select2-cascade').select2({
        theme: 'bootstrap-5',
        allowClear: true,
        width: '100%'
    });

    // Handle court selection change - cascading dropdowns
    $('#court_id').on('change', function() {
        const courtId = $(this).val();

        console.log('Court selected:', courtId);

        if (courtId) {
            // Fetch court details via AJAX
            $.ajax({
                url: `/api/courts/${courtId}/details`,
                method: 'GET',
                success: function(data) {
                    console.log('Court details received:', data);

                    // Enable circuit dropdowns and show all options
                    $('#circuit_name_id, #circuit_serial_id, #circuit_shift_id').prop('disabled', false);

                    // Note: All circuit options are already loaded in the form
                    // The court selection doesn't filter circuit options anymore
                    // Users can select any circuit combination

                    // Populate secretary dropdown with MULTIPLE options
                    $('#circuit_secretary').empty().prop('disabled', false);
                    $('#circuit_secretary').append(new Option('<?php echo e(__("app.select_option")); ?>', ''));
                    if (data.secretaries && data.secretaries.length > 0) {
                        data.secretaries.forEach(function(secretary) {
                            $('#circuit_secretary').append(new Option(secretary.label, secretary.id));
                        });
                    }
                    $('#circuit_secretary').trigger('change');

                    // Populate floor dropdown with MULTIPLE options
                    $('#court_floor').empty().prop('disabled', false);
                    $('#court_floor').append(new Option('<?php echo e(__("app.select_option")); ?>', ''));
                    if (data.floors && data.floors.length > 0) {
                        data.floors.forEach(function(floor) {
                            $('#court_floor').append(new Option(floor.label, floor.id));
                        });
                    }
                    $('#court_floor').trigger('change');

                    // Populate hall dropdown with MULTIPLE options
                    $('#court_hall').empty().prop('disabled', false);
                    $('#court_hall').append(new Option('<?php echo e(__("app.select_option")); ?>', ''));
                    if (data.halls && data.halls.length > 0) {
                        data.halls.forEach(function(hall) {
                            $('#court_hall').append(new Option(hall.label, hall.id));
                        });
                    }
                    $('#court_hall').trigger('change');

                    console.log('Dropdowns populated successfully');
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error, xhr.responseText);
                    alert('<?php echo e(__("app.error_loading_court_details")); ?>');
                }
            });
        } else {
            // Clear and disable all cascading dropdowns
            $('#circuit_name_id, #circuit_serial_id, #circuit_shift_id, #circuit_secretary, #court_floor, #court_hall')
                .empty()
                .append(new Option('<?php echo e(__("app.select_court_first")); ?>', ''))
                .prop('disabled', true)
                .trigger('change');
        }
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/cases/create.blade.php ENDPATH**/ ?>