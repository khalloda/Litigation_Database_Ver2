<?php $__env->startSection('title', 'Documents'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">📁 Document Management</h1>
                <div>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('documents.upload')): ?>
                    <a href="<?php echo e(route('documents.create')); ?>" class="btn btn-primary">
                        📤 Upload Document
                    </a>
                    <?php endif; ?>
                    <a href="#" class="btn btn-outline-primary ms-2" onclick="window.print(); return false;">
                        🖨️ Print
                    </a>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">🔍 Filter Documents</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?php echo e(route('documents.index')); ?>">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search"
                                    value="<?php echo e(request('search')); ?>" placeholder="Search documents...">
                            </div>
                            <div class="col-md-2">
                                <label for="client_id" class="form-label">Client</label>
                                <select class="form-select" id="client_id" name="client_id">
                                    <option value="">All Clients</option>
                                    <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($client->id); ?>" <?php echo e(request('client_id') == $client->id ? 'selected' : ''); ?>>
                                        <?php echo e($client->client_name_ar ?? $client->client_name_en); ?>

                                    </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="matter_id" class="form-label">Matter</label>
                                <select class="form-select" id="matter_id" name="matter_id">
                                    <option value="">All Matters</option>
                                    <?php $__currentLoopData = $cases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $case): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($case->id); ?>" <?php echo e(request('matter_id') == $case->id ? 'selected' : ''); ?>>
                                        <?php echo e($case->matter_name_ar ?? $case->matter_name_en); ?>

                                    </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="document_type" class="form-label">Document Type</label>
                                <select class="form-select" id="document_type" name="document_type">
                                    <option value="">All Types</option>
                                    <?php $__currentLoopData = $documentTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($type); ?>" <?php echo e(request('document_type') == $type ? 'selected' : ''); ?>>
                                        <?php echo e($type); ?>

                                    </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="storage_type" class="form-label">Storage Type</label>
                                <select class="form-select" id="storage_type" name="storage_type">
                                    <option value="">All Types</option>
                                    <option value="physical" <?php echo e(request('storage_type') == 'physical' ? 'selected' : ''); ?>>Physical</option>
                                    <option value="digital" <?php echo e(request('storage_type') == 'digital' ? 'selected' : ''); ?>>Digital</option>
                                    <option value="both" <?php echo e(request('storage_type') == 'both' ? 'selected' : ''); ?>>Both</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Filter</button>
                                <a href="<?php echo e(route('documents.index')); ?>" class="btn btn-outline-secondary">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">📊 Documents (<?php echo e($documents->total()); ?> total)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Document</th>
                                    <th>Storage Type</th>
                                    <th>Client</th>
                                    <th>Matter</th>
                                    <th>Type</th>
                                    <th>Size</th>
                                    <th>Pages</th>
                                    <th>M-Files</th>
                                    <th>Upload Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <div>
                                            <strong><?php echo e($document->document_name ?? 'No file'); ?></strong>
                                            <?php if($document->description): ?>
                                            <br><small class="text-muted"><?php echo e(Str::limit($document->description, 50)); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $badgeClass = match($document->document_storage_type) {
                                        'physical' => 'bg-warning',
                                        'digital' => 'bg-success',
                                        'both' => 'bg-info',
                                        default => 'bg-secondary'
                                        };
                                        $typeText = match($document->document_storage_type) {
                                        'physical' => 'Physical',
                                        'digital' => 'Digital',
                                        'both' => 'Both',
                                        default => 'Unknown'
                                        };
                                        ?>
                                        <span class="badge <?php echo e($badgeClass); ?>"><?php echo e($typeText); ?></span>
                                    </td>
                                    <td>
                                        <strong><?php echo e($document->client->client_name_ar ?? $document->client->client_name_en); ?></strong>
                                    </td>
                                    <td>
                                        <?php if($document->case): ?>
                                        <strong><?php echo e($document->case->matter_name_ar ?? $document->case->matter_name_en); ?></strong>
                                        <?php else: ?>
                                        <span class="text-muted">No matter assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo e($document->document_type); ?></span>
                                    </td>
                                    <td>
                                        <?php if($document->file_size): ?>
                                        <small class="text-muted"><?php echo e(number_format($document->file_size / 1024, 1)); ?> KB</small>
                                        <?php else: ?>
                                        <small class="text-muted">N/A</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($document->pages_count): ?>
                                        <span class="badge bg-light text-dark"><?php echo e($document->pages_count); ?></span>
                                        <?php else: ?>
                                        <small class="text-muted">N/A</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($document->mfiles_uploaded && $document->mfiles_id): ?>
                                        <span class="badge bg-primary" title="M-Files ID: <?php echo e($document->mfiles_id); ?>">
                                            ✓ M-Files
                                        </span>
                                        <?php else: ?>
                                        <small class="text-muted">-</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo e($document->created_at->format('M d, Y')); ?><br>
                                            <?php echo e($document->created_at->format('H:i')); ?>

                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo e(route('documents.show', $document)); ?>"
                                                class="btn btn-outline-primary" title="View Details">
                                                👁️
                                            </a>
                                            <?php if($document->isDigitalDocument() && $document->file_path): ?>
                                            <a href="<?php echo e(route('documents.download', $document)); ?>"
                                                class="btn btn-outline-success" title="Download">
                                                📥
                                            </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('documents.delete')): ?>
                                            <form action="<?php echo e(route('documents.destroy', $document)); ?>"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this document?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                    🗑️
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        No documents found matching your criteria.
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    
                    <?php if($documents->hasPages()): ?>
                    <div class="d-flex justify-content-center mt-4">
                        <?php echo e($documents->withQueryString()->links()); ?>

                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">📈 Document Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <h4 class="text-primary"><?php echo e($documents->total()); ?></h4>
                            <small class="text-muted">Total Documents</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-success"><?php echo e($documents->where('matter_id', '!=', null)->count()); ?></h4>
                            <small class="text-muted">With Matter</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-info"><?php echo e(number_format($documents->sum('file_size') / 1024 / 1024, 1)); ?> MB</h4>
                            <small class="text-muted">Total Size</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-warning"><?php echo e($documentTypes->count()); ?></h4>
                            <small class="text-muted">Document Types</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {

        .btn,
        .card-header,
        .pagination {
            display: none !important;
        }
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views\documents\index.blade.php ENDPATH**/ ?>