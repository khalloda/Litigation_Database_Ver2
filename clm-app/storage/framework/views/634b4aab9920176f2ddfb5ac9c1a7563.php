<?php $__env->startSection('title', __('app.edit_case')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4"><?php echo e(__('app.edit_case')); ?></h1>
        <a href="<?php echo e(route('cases.show', $case)); ?>" class="btn btn-outline-secondary"><?php echo e(__('app.cancel')); ?></a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="<?php echo e(route('cases.update', $case)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div class="accordion" id="editCaseAccordion">
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-overview">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-overview" aria-expanded="true" aria-controls="collapse-overview">
                                <?php echo e(__('app.overview')); ?>

                            </button>
                        </h2>
                        <div id="collapse-overview" class="accordion-collapse collapse show" aria-labelledby="heading-overview" data-bs-parent="#editCaseAccordion">
                            <div class="accordion-body">
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
unset($__errorArgs, $__bag); ?>" id="matter_name_ar" name="matter_name_ar" value="<?php echo e(old('matter_name_ar', $case->matter_name_ar)); ?>" dir="auto">
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
unset($__errorArgs, $__bag); ?>" id="matter_name_en" name="matter_name_en" value="<?php echo e(old('matter_name_en', $case->matter_name_en)); ?>">
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
unset($__errorArgs, $__bag); ?>" id="matter_start_date" name="matter_start_date" value="<?php echo e(old('matter_start_date', $case->matter_start_date?->format('Y-m-d'))); ?>">
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
unset($__errorArgs, $__bag); ?>" id="matter_end_date" name="matter_end_date" value="<?php echo e(old('matter_end_date', $case->matter_end_date?->format('Y-m-d'))); ?>">
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
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="matter_description" class="form-label"><?php echo e(__('app.matter_description')); ?></label>
                                        <textarea class="form-control <?php $__errorArgs = ['matter_description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="matter_description" name="matter_description" rows="3" dir="auto"><?php echo e(old('matter_description', $case->matter_description)); ?></textarea>
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
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-parties">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-parties" aria-expanded="false" aria-controls="collapse-parties">
                                <?php echo e(__('app.parties')); ?>

                            </button>
                        </h2>
                        <div id="collapse-parties" class="accordion-collapse collapse" aria-labelledby="heading-parties" data-bs-parent="#editCaseAccordion">
                            <div class="accordion-body">
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
                                            <option value="<?php echo e($client->id); ?>" <?php echo e((old('client_id', $case->client_id) == $client->id) ? 'selected' : ''); ?>>
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
unset($__errorArgs, $__bag); ?>" id="client_in_case_name" name="client_in_case_name" value="<?php echo e(old('client_in_case_name', $case->client_in_case_name)); ?>" dir="auto">
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
                                            <option value="<?php echo e($ov->id); ?>" <?php echo e((old('client_capacity_id', $case->client_capacity_id) == $ov->id) ? 'selected' : ''); ?>>
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
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="client_capacity_note" class="form-label"><?php echo e(__('app.capacity_note')); ?></label>
                                        <input type="text" class="form-control <?php $__errorArgs = ['client_capacity_note'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="client_capacity_note" name="client_capacity_note" value="<?php echo e(old('client_capacity_note', $case->client_capacity_note)); ?>" dir="auto">
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
                                    <div class="col-md-6">
                                        <label for="lawyer_a" class="form-label"><?php echo e(__('app.lawyer_a')); ?></label>
                                        <input type="text" class="form-control <?php $__errorArgs = ['lawyer_a'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="lawyer_a" name="lawyer_a" value="<?php echo e(old('lawyer_a', $case->lawyer_a)); ?>" dir="auto">
                                        <?php $__errorArgs = ['lawyer_a'];
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
                                        <label for="lawyer_b" class="form-label"><?php echo e(__('app.lawyer_b')); ?></label>
                                        <input type="text" class="form-control <?php $__errorArgs = ['lawyer_b'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="lawyer_b" name="lawyer_b" value="<?php echo e(old('lawyer_b', $case->lawyer_b)); ?>" dir="auto">
                                        <?php $__errorArgs = ['lawyer_b'];
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
                                            <option value="<?php echo e($opp->id); ?>"
                                                    <?php echo e((old('opponent_id', $case->opponent_id) == $opp->id) ? 'selected' : ''); ?>

                                                    data-arabic-name="<?php echo e($opp->opponent_name_ar); ?>"
                                                    data-english-name="<?php echo e($opp->opponent_name_en); ?>">
                                                <?php if(app()->getLocale() === 'ar'): ?>
                                                    <?php echo e($opp->opponent_name_ar); ?>

                                                <?php else: ?>
                                                    <?php echo e($opp->opponent_name_en ?: $opp->opponent_name_ar); ?>

                                                <?php endif; ?>
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
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="opponent_in_case_name" class="form-label"><?php echo e(__('app.opponent_in_case_name')); ?></label>
                                        <input type="text" class="form-control <?php $__errorArgs = ['opponent_in_case_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="opponent_in_case_name" name="opponent_in_case_name" value="<?php echo e(old('opponent_in_case_name', $case->opponent_in_case_name)); ?>" dir="auto">
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
                                            <option value="<?php echo e($ov->id); ?>" <?php echo e((old('opponent_capacity_id', $case->opponent_capacity_id) == $ov->id) ? 'selected' : ''); ?>>
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
                                    <div class="col-md-4">
                                        <label for="opponent_capacity_note" class="form-label"><?php echo e(__('app.capacity_note')); ?></label>
                                        <input type="text" class="form-control <?php $__errorArgs = ['opponent_capacity_note'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="opponent_capacity_note" name="opponent_capacity_note" value="<?php echo e(old('opponent_capacity_note', $case->opponent_capacity_note)); ?>" dir="auto">
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
                        <div id="collapse-court" class="accordion-collapse collapse" aria-labelledby="heading-court" data-bs-parent="#editCaseAccordion">
                            <div class="accordion-body">
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
                                            <option value="<?php echo e($court->id); ?>" <?php echo e((old('court_id', $case->court_id) == $court->id) ? 'selected' : ''); ?>>
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
                                            <option value="<?php echo e($court->id); ?>" <?php echo e((old('matter_destination_id', $case->matter_destination_id) == $court->id) ? 'selected' : ''); ?>>
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
                                <div class="card border-primary mb-3">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><?php echo e(__('app.circuit_container')); ?></h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="circuit_name_id" class="form-label"><?php echo e(__('app.circuit_name')); ?></label>
                                                <select class="form-select select2-cascade <?php $__errorArgs = ['circuit_name_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="circuit_name_id" name="circuit_name_id" <?php echo e($case->court_id ? '' : 'disabled'); ?>>
                                                    <option value=""><?php echo e(__('app.select_court_first')); ?></option>
                                                    <?php $__currentLoopData = $circuitNames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $circuitName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($circuitName->id); ?>" <?php echo e((old('circuit_name_id', $case->circuit_name_id) == $circuitName->id) ? 'selected' : ''); ?>>
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
                                            <div class="col-md-4 mb-3">
                                                <label for="circuit_serial_id" class="form-label"><?php echo e(__('app.circuit_serial')); ?></label>
                                                <select class="form-select select2-cascade <?php $__errorArgs = ['circuit_serial_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="circuit_serial_id" name="circuit_serial_id" <?php echo e($case->court_id ? '' : 'disabled'); ?>>
                                                    <option value=""><?php echo e(__('app.select_court_first')); ?></option>
                                                    <?php $__currentLoopData = $circuitSerials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $circuitSerial): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($circuitSerial->id); ?>" <?php echo e((old('circuit_serial_id', $case->circuit_serial_id) == $circuitSerial->id) ? 'selected' : ''); ?>>
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
                                            <div class="col-md-4 mb-3">
                                                <label for="circuit_shift_id" class="form-label"><?php echo e(__('app.circuit_shift')); ?></label>
                                                <select class="form-select select2-cascade <?php $__errorArgs = ['circuit_shift_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="circuit_shift_id" name="circuit_shift_id" <?php echo e($case->court_id ? '' : 'disabled'); ?>>
                                                    <option value=""><?php echo e(__('app.select_court_first')); ?></option>
                                                    <?php $__currentLoopData = $circuitShifts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $circuitShift): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($circuitShift->id); ?>" <?php echo e((old('circuit_shift_id', $case->circuit_shift_id) == $circuitShift->id) ? 'selected' : ''); ?>>
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
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="circuit_secretary" class="form-label"><?php echo e(__('app.circuit_secretary')); ?></label>
                                        <select class="form-select select2-cascade <?php $__errorArgs = ['circuit_secretary'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="circuit_secretary" name="circuit_secretary" <?php echo e($case->court_id ? '' : 'disabled'); ?>>
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
                                    <div class="col-md-4">
                                        <label for="court_floor" class="form-label"><?php echo e(__('app.court_floor')); ?></label>
                                        <select class="form-select select2-cascade <?php $__errorArgs = ['court_floor'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="court_floor" name="court_floor" <?php echo e($case->court_id ? '' : 'disabled'); ?>>
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
                                    <div class="col-md-4">
                                        <label for="court_hall" class="form-label"><?php echo e(__('app.court_hall')); ?></label>
                                        <select class="form-select select2-cascade <?php $__errorArgs = ['court_hall'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="court_hall" name="court_hall" <?php echo e($case->court_id ? '' : 'disabled'); ?>>
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
                            </div>
                        </div>
                    </div>

                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-status">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-status" aria-expanded="false" aria-controls="collapse-status">
                                <?php echo e(__('app.status_progress')); ?>

                            </button>
                        </h2>
                        <div id="collapse-status" class="accordion-collapse collapse" aria-labelledby="heading-status" data-bs-parent="#editCaseAccordion">
                            <div class="accordion-body">
                                <div class="row mb-3">
                                    <div class="col-md-3">
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
                                            <option value="<?php echo e($ov->id); ?>" <?php echo e((old('matter_status_id', $case->matter_status_id) == $ov->id) ? 'selected' : ''); ?>>
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
                                            <option value="<?php echo e($ov->id); ?>" <?php echo e((old('matter_category_id', $case->matter_category_id) == $ov->id) ? 'selected' : ''); ?>>
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
                                            <option value="<?php echo e($ov->id); ?>" <?php echo e((old('matter_degree_id', $case->matter_degree_id) == $ov->id) ? 'selected' : ''); ?>>
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
                                            <option value="<?php echo e($ov->id); ?>" <?php echo e((old('matter_importance_id', $case->matter_importance_id) == $ov->id) ? 'selected' : ''); ?>>
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
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="current_status" class="form-label"><?php echo e(__('app.current_status')); ?></label>
                                        <textarea class="form-control <?php $__errorArgs = ['current_status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="current_status" name="current_status" rows="3" dir="auto"><?php echo e(old('current_status', $case->current_status)); ?></textarea>
                                        <?php $__errorArgs = ['current_status'];
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
                                        <label for="matter_evaluation" class="form-label"><?php echo e(__('app.matter_evaluation')); ?></label>
                                        <textarea class="form-control <?php $__errorArgs = ['matter_evaluation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="matter_evaluation" name="matter_evaluation" rows="3" dir="auto"><?php echo e(old('matter_evaluation', $case->matter_evaluation)); ?></textarea>
                                        <?php $__errorArgs = ['matter_evaluation'];
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

                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-financials">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-financials" aria-expanded="false" aria-controls="collapse-financials">
                                <?php echo e(__('app.financials')); ?>

                            </button>
                        </h2>
                        <div id="collapse-financials" class="accordion-collapse collapse" aria-labelledby="heading-financials" data-bs-parent="#editCaseAccordion">
                            <div class="accordion-body">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label for="client_type_id" class="form-label"><?php echo e(__('app.client_type')); ?></label>
                                        <select class="form-select <?php $__errorArgs = ['client_type_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="client_type_id" name="client_type_id">
                                            <option value=""><?php echo e(__('app.select_option')); ?></option>
                                            <?php
                                                $clientTypes = \App\Models\OptionValue::whereHas('optionSet', fn($q) => $q->where('key', 'client.cash_or_probono'))->where('is_active', true)->orderBy('id')->get();
                                            ?>
                                            <?php $__currentLoopData = $clientTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($ov->id); ?>" <?php echo e((old('client_type_id', $case->client_type_id) == $ov->id) ? 'selected' : ''); ?>>
                                                <?php echo e(app()->getLocale() === 'ar' ? $ov->label_ar : $ov->label_en); ?>

                                            </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <?php $__errorArgs = ['client_type_id'];
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
                                        <label for="matter_asked_amount" class="form-label"><?php echo e(__('app.matter_asked_amount')); ?></label>
                                        <input type="number" step="0.01" class="form-control <?php $__errorArgs = ['matter_asked_amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="matter_asked_amount" name="matter_asked_amount" value="<?php echo e(old('matter_asked_amount', $case->matter_asked_amount)); ?>" dir="ltr">
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
                                    <div class="col-md-3">
                                        <label for="matter_judged_amount" class="form-label"><?php echo e(__('app.matter_judged_amount')); ?></label>
                                        <input type="number" step="0.01" class="form-control <?php $__errorArgs = ['matter_judged_amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="matter_judged_amount" name="matter_judged_amount" value="<?php echo e(old('matter_judged_amount', $case->matter_judged_amount)); ?>" dir="ltr">
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
                                    <div class="col-md-3">
                                        <label for="fee_letter" class="form-label"><?php echo e(__('app.fee_letter')); ?></label>
                                        <input type="number" step="0.01" class="form-control <?php $__errorArgs = ['fee_letter'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="fee_letter" name="fee_letter" value="<?php echo e(old('fee_letter', $case->fee_letter)); ?>" dir="ltr">
                                        <?php $__errorArgs = ['fee_letter'];
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
                                        <label for="allocated_budget" class="form-label"><?php echo e(__('app.allocated_budget')); ?></label>
                                        <textarea class="form-control <?php $__errorArgs = ['allocated_budget'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="allocated_budget" name="allocated_budget" rows="2" dir="auto"><?php echo e(old('allocated_budget', $case->allocated_budget)); ?></textarea>
                                        <?php $__errorArgs = ['allocated_budget'];
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
                                        <label for="financial_provision" class="form-label"><?php echo e(__('app.financial_provision')); ?></label>
                                        <textarea class="form-control <?php $__errorArgs = ['financial_provision'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="financial_provision" name="financial_provision" rows="2" dir="auto"><?php echo e(old('financial_provision', $case->financial_provision)); ?></textarea>
                                        <?php $__errorArgs = ['financial_provision'];
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
                                        <label for="contract_id" class="form-label"><?php echo e(__('app.contract')); ?></label>
                                        <select class="form-select <?php $__errorArgs = ['contract_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="contract_id" name="contract_id">
                                            <option value=""><?php echo e(__('app.select_option')); ?></option>
                                            <?php
                                                $contracts = \App\Models\EngagementLetter::orderBy('contract_date', 'desc')->orderBy('id', 'desc')->get();
                                            ?>
                                            <?php $__currentLoopData = $contracts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contract): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($contract->id); ?>" <?php echo e((old('contract_id', $case->contract_id) == $contract->id) ? 'selected' : ''); ?>>
                                                <?php echo e($contract->client_name ?: 'Client ID: ' . $contract->client_id); ?> <?php if($contract->contract_date): ?> (<?php echo e(\Carbon\Carbon::parse($contract->contract_date)->format('Y-m-d')); ?>) <?php endif; ?>
                                            </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <?php $__errorArgs = ['contract_id'];
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
                                        <label for="engagement_letter_no" class="form-label"><?php echo e(__('app.engagement_letter_no')); ?></label>
                                        <input type="text" class="form-control <?php $__errorArgs = ['engagement_letter_no'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="engagement_letter_no" name="engagement_letter_no" value="<?php echo e(old('engagement_letter_no', $case->engagement_letter_no)); ?>">
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
                            </div>
                        </div>
                    </div>

                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-meta">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-meta" aria-expanded="false" aria-controls="collapse-meta">
                                <?php echo e(__('app.meta_audit')); ?>

                            </button>
                        </h2>
                        <div id="collapse-meta" class="accordion-collapse collapse" aria-labelledby="heading-meta" data-bs-parent="#editCaseAccordion">
                            <div class="accordion-body">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label for="matter_shelf" class="form-label"><?php echo e(__('app.matter_shelf')); ?></label>
                                        <input type="text" maxlength="10" class="form-control <?php $__errorArgs = ['matter_shelf'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="matter_shelf" name="matter_shelf" value="<?php echo e(old('matter_shelf', $case->matter_shelf)); ?>">
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
                                            <option value="<?php echo e($ov->id); ?>" <?php echo e((old('matter_branch_id', $case->matter_branch_id) == $ov->id) ? 'selected' : ''); ?>>
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
                                    <div class="col-md-3">
                                        <label for="client_branch" class="form-label"><?php echo e(__('app.client_branch')); ?> (text)</label>
                                        <input type="text" class="form-control <?php $__errorArgs = ['client_branch'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="client_branch" name="client_branch" value="<?php echo e(old('client_branch', $case->client_branch)); ?>" dir="auto">
                                        <?php $__errorArgs = ['client_branch'];
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
                                            <option value="<?php echo e($lawyer->id); ?>" <?php echo e((old('matter_partner_id', $case->matter_partner_id) == $lawyer->id) ? 'selected' : ''); ?>>
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
                                        <label for="legal_opinion" class="form-label"><?php echo e(__('app.legal_opinion')); ?></label>
                                        <textarea class="form-control <?php $__errorArgs = ['legal_opinion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="legal_opinion" name="legal_opinion" rows="3" dir="auto"><?php echo e(old('legal_opinion', $case->legal_opinion)); ?></textarea>
                                        <?php $__errorArgs = ['legal_opinion'];
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
                                        <label for="notes_1" class="form-label"><?php echo e(__('app.notes')); ?> 1</label>
                                        <textarea class="form-control <?php $__errorArgs = ['notes_1'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="notes_1" name="notes_1" rows="3" dir="auto"><?php echo e(old('notes_1', $case->notes_1)); ?></textarea>
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
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="notes_2" class="form-label"><?php echo e(__('app.notes')); ?> 2</label>
                                        <textarea class="form-control <?php $__errorArgs = ['notes_2'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="notes_2" name="notes_2" rows="3" dir="auto"><?php echo e(old('notes_2', $case->notes_2)); ?></textarea>
                                        <?php $__errorArgs = ['notes_2'];
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

                <div class="d-flex justify-content-end mt-4">
                    <a href="<?php echo e(route('cases.show', $case)); ?>" class="btn btn-secondary me-2"><?php echo e(__('app.cancel')); ?></a>
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

    // Initialize Select2 for opponent dropdown
    const opponentSelect = $('#opponent_id');
    if (opponentSelect.length > 0) {
        opponentSelect.select2({
            theme: 'bootstrap-5',
            placeholder: '<?php echo e(__("app.select_option")); ?>',
            allowClear: true,
            width: '100%',
            dropdownParent: $('body'),
            templateResult: function(data) {
                if (!data.id) return data.text;
                var displayText = data.text || data.element.getAttribute('data-arabic-name') || 'Unknown';
                return $('<span style="color: #212529;">' + displayText + '</span>');
            },
            templateSelection: function(data) {
                if (!data.id) return data.text;
                var displayText = data.text || data.element.getAttribute('data-arabic-name') || 'Unknown';
                return $('<span style="color: #212529;">' + displayText + '</span>');
            }
        });
        opponentSelect.trigger('change');
        setTimeout(function() {
            $('.select2-container .select2-selection__rendered').css('color', '#212529');
            $('.select2-dropdown .select2-results__option').css('color', '#212529');
        }, 100);
    }

    // Load existing court details on page load if court is selected
    const initialCourtId = $('#court_id').val();
    if (initialCourtId) {
        loadCourtDetails(initialCourtId, {
            secretary: '<?php echo e(old("circuit_secretary", $case->circuit_secretary)); ?>',
            floor: '<?php echo e(old("court_floor", $case->court_floor)); ?>',
            hall: '<?php echo e(old("court_hall", $case->court_hall)); ?>'
        });
    }

    // Handle court selection change - cascading dropdowns
    $('#court_id').on('change', function() {
        const courtId = $(this).val();
        if (courtId) {
            loadCourtDetails(courtId);
        } else {
            $('#circuit_name_id, #circuit_serial_id, #circuit_shift_id, #circuit_secretary, #court_floor, #court_hall')
                .empty()
                .append(new Option('<?php echo e(__("app.select_court_first")); ?>', ''))
                .prop('disabled', true)
                .trigger('change');
        }
    });

    function loadCourtDetails(courtId, selectedValues = {}) {
        $.ajax({
            url: `/api/courts/${courtId}/details`,
            method: 'GET',
            success: function(data) {
                $('#circuit_name_id, #circuit_serial_id, #circuit_shift_id').prop('disabled', false);

                $('#circuit_secretary').empty().prop('disabled', false);
                $('#circuit_secretary').append(new Option('<?php echo e(__("app.select_option")); ?>', ''));
                if (data.secretaries && data.secretaries.length > 0) {
                    data.secretaries.forEach(function(secretary) {
                        const isSelected = selectedValues.secretary == secretary.id;
                        $('#circuit_secretary').append(new Option(secretary.label, secretary.id, isSelected, isSelected));
                    });
                }
                $('#circuit_secretary').trigger('change');

                $('#court_floor').empty().prop('disabled', false);
                $('#court_floor').append(new Option('<?php echo e(__("app.select_option")); ?>', ''));
                if (data.floors && data.floors.length > 0) {
                    data.floors.forEach(function(floor) {
                        const isSelected = selectedValues.floor == floor.id;
                        $('#court_floor').append(new Option(floor.label, floor.id, isSelected, isSelected));
                    });
                }
                $('#court_floor').trigger('change');

                $('#court_hall').empty().prop('disabled', false);
                $('#court_hall').append(new Option('<?php echo e(__("app.select_option")); ?>', ''));
                if (data.halls && data.halls.length > 0) {
                    data.halls.forEach(function(hall) {
                        const isSelected = selectedValues.hall == hall.id;
                        $('#court_hall').append(new Option(hall.label, hall.id, isSelected, isSelected));
                    });
                }
                $('#court_hall').trigger('change');
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error, xhr.responseText);
                alert('<?php echo e(__("app.error_loading_court_details")); ?>');
            }
        });
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views\cases\edit.blade.php ENDPATH**/ ?>