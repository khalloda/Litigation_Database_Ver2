<?php $__env->startSection('title', 'Document Details'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">📄 Document Details</h1>
                <div>
                    <a href="<?php echo e(route('documents.index')); ?>" class="btn btn-outline-secondary">
                        ← Back to Documents
                    </a>
                    <a href="<?php echo e(route('documents.download', $document)); ?>" class="btn btn-success ms-2">
                        📥 Download
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">📋 Document Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Document Name:</strong></td>
                                    <td><?php echo e($document->document_name); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Document Type:</strong></td>
                                    <td><span class="badge bg-info"><?php echo e($document->document_type); ?></span></td>
                                </tr>
                                <tr>
                                    <td><strong>File Size:</strong></td>
                                    <td><?php echo e(number_format($document->file_size / 1024, 1)); ?> KB</td>
                                </tr>
                                <tr>
                                    <td><strong>MIME Type:</strong></td>
                                    <td><code><?php echo e($document->mime_type); ?></code></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Upload Date:</strong></td>
                                    <td><?php echo e($document->created_at->format('M d, Y H:i')); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td><?php echo e($document->updated_at->format('M d, Y H:i')); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Uploaded By:</strong></td>
                                    <td>
                                        <?php if($document->createdBy): ?>
                                        <?php echo e($document->createdBy->name); ?>

                                        <?php else: ?>
                                        <span class="text-muted">System</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>File Path:</strong></td>
                                    <td><code><?php echo e($document->file_path); ?></code></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <?php if($document->description): ?>
                    <div class="mt-3">
                        <strong>Description:</strong>
                        <p class="mt-2"><?php echo e($document->description); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">🔗 Related Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>👤 Client</h6>
                            <p>
                                <strong><?php echo e($document->client->client_name_ar ?? $document->client->client_name_en); ?></strong><br>
                                <small class="text-muted">ID: <?php echo e($document->client->id); ?></small>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>⚖️ Matter/Case</h6>
                            <?php if($document->case): ?>
                            <p>
                                <strong><?php echo e($document->case->matter_name_ar ?? $document->case->matter_name_en); ?></strong><br>
                                <small class="text-muted">ID: <?php echo e($document->case->id); ?> | Status: <?php echo e($document->case->matter_status); ?></small>
                            </p>
                            <?php else: ?>
                            <p class="text-muted">No matter assigned to this document.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">👁️ Document Preview</h5>
                </div>
                <div class="card-body">
                    <?php if($document->mime_type === 'application/pdf'): ?>
                    <iframe src="<?php echo e(route('documents.signed-url', $document)); ?>"
                        width="100%" height="600" style="border: none;">
                        Your browser does not support PDF preview.
                    </iframe>
                    <?php elseif(str_starts_with($document->mime_type, 'image/')): ?>
                    <img src="<?php echo e(route('documents.signed-url', $document)); ?>"
                        alt="<?php echo e($document->document_name); ?>"
                        class="img-fluid"
                        style="max-height: 600px;">
                    <?php elseif(str_contains($document->mime_type, 'word') || str_contains($document->mime_type, 'spreadsheet') || str_contains($document->mime_type, 'presentation')): ?>
                    <div class="alert alert-info mb-0">
                        <div class="d-flex align-items-center">
                            <div class="me-2">ℹ️</div>
                            <div>
                                Inline preview is not supported for Office files (DOCX/XLSX/PPTX) in the browser.
                                Use the Download button to open in Microsoft Office, or convert to PDF to enable inline preview.
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-secondary mb-0">
                        Preview is not available for this file type.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">⚡ Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo e(route('documents.download', $document)); ?>" class="btn btn-success">
                            📥 Download Document
                        </a>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('documents.edit')): ?>
                        <a href="<?php echo e(route('documents.edit', $document)); ?>" class="btn btn-outline-primary">
                            ✏️ Edit Metadata
                        </a>
                        <?php endif; ?>

                        <button class="btn btn-outline-info" onclick="copyToClipboard('<?php echo e(route('documents.download', $document)); ?>')">
                            📋 Copy Download Link
                        </button>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('documents.delete')): ?>
                        <form action="<?php echo e(route('documents.destroy', $document)); ?>" method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this document? This action cannot be undone.')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-outline-danger">
                                🗑️ Delete Document
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">📊 File Details</h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="mb-3">
                            <?php if($document->mime_type === 'application/pdf'): ?>
                            <i class="fas fa-file-pdf fa-3x text-danger"></i>
                            <?php elseif(str_contains($document->mime_type, 'word')): ?>
                            <i class="fas fa-file-word fa-3x text-primary"></i>
                            <?php elseif(str_contains($document->mime_type, 'excel')): ?>
                            <i class="fas fa-file-excel fa-3x text-success"></i>
                            <?php elseif(str_contains($document->mime_type, 'powerpoint')): ?>
                            <i class="fas fa-file-powerpoint fa-3x text-warning"></i>
                            <?php elseif(str_starts_with($document->mime_type, 'image/')): ?>
                            <i class="fas fa-file-image fa-3x text-info"></i>
                            <?php else: ?>
                            <i class="fas fa-file fa-3x text-secondary"></i>
                            <?php endif; ?>
                        </div>

                        <h6><?php echo e($document->document_name); ?></h6>
                        <p class="text-muted"><?php echo e(number_format($document->file_size / 1024, 1)); ?> KB</p>

                        <div class="mt-3">
                            <small class="text-muted">
                                <strong>Format:</strong> <?php echo e(strtoupper(pathinfo($document->document_name, PATHINFO_EXTENSION))); ?><br>
                                <strong>Type:</strong> <?php echo e($document->mime_type); ?><br>
                                <strong>Uploaded:</strong> <?php echo e($document->created_at->diffForHumans()); ?>

                            </small>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">🔒 Security</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <small>
                            <strong>Secure Storage:</strong> This document is stored in a secure directory with restricted access.<br><br>
                            <strong>Access Control:</strong> Only authorized users can view or download this document.<br><br>
                            <strong>Audit Trail:</strong> All access to this document is logged and tracked.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            // Create a temporary alert to show success
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show position-fixed';
            alert.style.top = '20px';
            alert.style.right = '20px';
            alert.style.zIndex = '9999';
            alert.innerHTML = `
            <strong>Success!</strong> Download link copied to clipboard.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
            document.body.appendChild(alert);

            // Remove alert after 3 seconds
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 3000);
        }).catch(function(err) {
            alert('Failed to copy link to clipboard');
        });
    }
</script>


<?php if(isset($schemaData)): ?>
    <?php if (isset($component)) { $__componentOriginal2fb075f3fd550917b20c60bb108248e8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2fb075f3fd550917b20c60bb108248e8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.all-fields-table','data' => ['record' => $document,'columns' => $schemaData['columns'],'types' => $schemaData['types'],'fkHints' => $schemaData['fkHints'],'title' => 'All Document Fields']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('admin.all-fields-table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['record' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($document),'columns' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($schemaData['columns']),'types' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($schemaData['types']),'fkHints' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($schemaData['fkHints']),'title' => 'All Document Fields']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2fb075f3fd550917b20c60bb108248e8)): ?>
<?php $attributes = $__attributesOriginal2fb075f3fd550917b20c60bb108248e8; ?>
<?php unset($__attributesOriginal2fb075f3fd550917b20c60bb108248e8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2fb075f3fd550917b20c60bb108248e8)): ?>
<?php $component = $__componentOriginal2fb075f3fd550917b20c60bb108248e8; ?>
<?php unset($__componentOriginal2fb075f3fd550917b20c60bb108248e8); ?>
<?php endif; ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views\documents\show.blade.php ENDPATH**/ ?>