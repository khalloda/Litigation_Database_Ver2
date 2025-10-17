

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?php echo e(__('app.upload_import_file')); ?></h5>
                    <a href="<?php echo e(route('import.index')); ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-list"></i> <?php echo e(__('app.view_sessions')); ?>

                    </a>
                </div>

                <div class="card-body">
                    <?php if(session('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo e(session('error')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo e(route('import.process-upload')); ?>" method="POST" enctype="multipart/form-data" id="uploadForm">
                        <?php echo csrf_field(); ?>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="table_name" class="form-label"><?php echo e(__('app.target_table')); ?> <span class="text-danger">*</span></label>
                                <select name="table_name" id="table_name" class="form-select <?php $__errorArgs = ['table_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                    <option value=""><?php echo e(__('app.select_table')); ?></option>
                                    <?php $__currentLoopData = $enabledTables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($table); ?>" <?php echo e(old('table_name') == $table ? 'selected' : ''); ?>>
                                            <?php echo e(__(ucfirst(str_replace('_', ' ', $table)))); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['table_name'];
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
                                <label class="form-label"><?php echo e(__('app.supported_formats')); ?></label>
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle"></i>
                                    Excel (.xlsx, .xls), CSV (.csv)
                                    <br>
                                    <small><?php echo e(__('app.max_file_size')); ?>: <?php echo e(config('importer.limits.max_upload_mb')); ?>MB</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="file" class="form-label"><?php echo e(__('app.select_file')); ?> <span class="text-danger">*</span></label>
                            
                            <div class="upload-area border rounded p-5 text-center" id="uploadArea">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                <p class="mb-2"><?php echo e(__('app.drag_drop_file_here')); ?></p>
                                <p class="text-muted mb-3"><?php echo e(__('app.or')); ?></p>
                                <input type="file" name="file" id="file" class="d-none" accept=".xlsx,.xls,.csv" required>
                                <button type="button" class="btn btn-primary" onclick="document.getElementById('file').click()">
                                    <i class="fas fa-folder-open"></i> <?php echo e(__('app.browse_files')); ?>

                                </button>
                                <?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="text-danger mt-2"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div id="fileInfo" class="mt-3 d-none">
                                <div class="alert alert-success">
                                    <i class="fas fa-file-excel"></i>
                                    <span id="fileName"></span>
                                    <span id="fileSize" class="text-muted"></span>
                                    <button type="button" class="btn btn-sm btn-link float-end" onclick="clearFile()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <strong><i class="fas fa-exclamation-triangle"></i> <?php echo e(__('app.important')); ?>:</strong>
                            <ul class="mb-0 mt-2">
                                <li><?php echo e(__('app.import_warning_backup')); ?></li>
                                <li><?php echo e(__('app.import_warning_first_row')); ?></li>
                                <li><?php echo e(__('app.import_warning_review')); ?></li>
                            </ul>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-upload"></i> <?php echo e(__('app.upload_and_continue')); ?>

                            </button>
                        </div>
                    </form>
                </div>
            </div>

            
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-check-circle text-success"></i> <?php echo e(__('app.step_1')); ?></h6>
                            <p class="card-text small"><?php echo e(__('app.upload_file_description')); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-exchange-alt text-info"></i> <?php echo e(__('app.step_2')); ?></h6>
                            <p class="card-text small"><?php echo e(__('app.map_columns_description')); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-play-circle text-primary"></i> <?php echo e(__('app.step_3')); ?></h6>
                            <p class="card-text small"><?php echo e(__('app.validate_import_description')); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('file');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');

    // File input change
    fileInput.addEventListener('change', function(e) {
        if (this.files.length > 0) {
            showFileInfo(this.files[0]);
        }
    });

    // Drag and drop
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => {
            uploadArea.classList.add('border-primary', 'bg-light');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => {
            uploadArea.classList.remove('border-primary', 'bg-light');
        }, false);
    });

    uploadArea.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 0) {
            fileInput.files = files;
            showFileInfo(files[0]);
        }
    }, false);

    function showFileInfo(file) {
        fileName.textContent = file.name;
        fileSize.textContent = '(' + formatBytes(file.size) + ')';
        fileInfo.classList.remove('d-none');
        uploadArea.classList.add('d-none');
    }

    window.clearFile = function() {
        fileInput.value = '';
        fileInfo.classList.add('d-none');
        uploadArea.classList.remove('d-none');
    };

    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/import/upload.blade.php ENDPATH**/ ?>