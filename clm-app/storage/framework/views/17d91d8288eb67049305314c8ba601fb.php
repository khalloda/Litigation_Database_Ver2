<?php $__env->startSection('title', 'Edit Document'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">✏️ Edit Document Metadata</h1>
                <div>
                    <a href="<?php echo e(route('documents.show', $document)); ?>" class="btn btn-outline-secondary">
                        ← Back to Document
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">📄 Document Information</h5>
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

                    <form action="<?php echo e(route('documents.update', $document)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        
                        <div class="mb-3">
                            <label for="document_name" class="form-label">Document Name</label>
                            <input type="text" class="form-control" id="document_name" 
                                   value="<?php echo e($document->document_name); ?>" readonly>
                            <div class="form-text">
                                Document name cannot be changed after upload.
                            </div>
                        </div>

                        
                        <div class="mb-3">
                            <label for="client_id" class="form-label">Client <span class="text-danger">*</span></label>
                            <select class="form-select <?php $__errorArgs = ['client_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                    id="client_id" name="client_id" required>
                                <option value="">Select a client...</option>
                                <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($client->id); ?>" 
                                            <?php echo e(old('client_id', $document->client_id) == $client->id ? 'selected' : ''); ?>>
                                        <?php echo e($client->client_name_ar ?? $client->client_name_en); ?>

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

                        
                        <div class="mb-3">
                            <label for="matter_id" class="form-label">Matter (Optional)</label>
                            <select class="form-select <?php $__errorArgs = ['matter_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                    id="matter_id" name="matter_id">
                                <option value="">Select a matter...</option>
                                <?php $__currentLoopData = $cases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $case): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($case->id); ?>" 
                                            <?php echo e(old('matter_id', $document->matter_id) == $case->id ? 'selected' : ''); ?>>
                                        <?php echo e($case->matter_name_ar ?? $case->matter_name_en); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <div class="form-text">
                                Select a specific matter/case if this document is related to one.
                            </div>
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

                        
                        <div class="mb-3">
                            <label for="document_type" class="form-label">Document Type <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php $__errorArgs = ['document_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="document_type" name="document_type" 
                                   value="<?php echo e(old('document_type', $document->document_type)); ?>" 
                                   placeholder="e.g., Contract, Invoice, Court Filing, etc." required>
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

                        
                        <div class="mb-4">
                            <label for="description" class="form-label">Description (Optional)</label>
                            <textarea class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Brief description of the document..."><?php echo e(old('description', $document->description)); ?></textarea>
                            <div class="form-text">
                                Maximum 1000 characters
                            </div>
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

                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?php echo e(route('documents.show', $document)); ?>" class="btn btn-outline-secondary me-md-2">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                💾 Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row justify-content-center mt-4">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">ℹ️ Document Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>📊 File Information</h6>
                            <ul class="list-unstyled">
                                <li><strong>Size:</strong> <?php echo e(number_format($document->file_size / 1024, 1)); ?> KB</li>
                                <li><strong>Type:</strong> <?php echo e($document->mime_type); ?></li>
                                <li><strong>Uploaded:</strong> <?php echo e($document->created_at->format('M d, Y H:i')); ?></li>
                                <li><strong>Last Updated:</strong> <?php echo e($document->updated_at->format('M d, Y H:i')); ?></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>👤 Upload Information</h6>
                            <ul class="list-unstyled">
                                <li><strong>Uploaded By:</strong> 
                                    <?php if($document->createdBy): ?>
                                        <?php echo e($document->createdBy->name); ?>

                                    <?php else: ?>
                                        System
                                    <?php endif; ?>
                                </li>
                                <li><strong>Current Client:</strong> <?php echo e($document->client->client_name_ar ?? $document->client->client_name_en); ?></li>
                                <li><strong>Current Matter:</strong> 
                                    <?php if($document->case): ?>
                                        <?php echo e($document->case->matter_name_ar ?? $document->case->matter_name_en); ?>

                                    <?php else: ?>
                                        None assigned
                                    <?php endif; ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const clientSelect = document.getElementById('client_id');
    const matterSelect = document.getElementById('matter_id');

    clientSelect.addEventListener('change', function() {
        const clientId = this.value;
        
        // Clear matter options
        matterSelect.innerHTML = '<option value="">Select a matter...</option>';
        
        if (clientId) {
            // Fetch matters for selected client
            fetch(`/documents/client-cases?client_id=${clientId}`)
                .then(response => response.json())
                .then(cases => {
                    cases.forEach(caseItem => {
                        const option = document.createElement('option');
                        option.value = caseItem.id;
                        option.textContent = caseItem.matter_name_ar || caseItem.matter_name_en;
                        matterSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error fetching cases:', error);
                });
        }
    });

    // Set initial matters if client is already selected
    if (clientSelect.value) {
        clientSelect.dispatchEvent(new Event('change'));
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views\documents\edit.blade.php ENDPATH**/ ?>