

<?php $__env->startSection('content'); ?>
<style>
    .summary-section {
        margin-bottom: 16px;
    }
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
        margin-bottom: 12px;
    }
    .summary-item {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        padding: 8px;
        text-align: center;
        border-radius: 4px;
    }
    .summary-item-number {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
    }
    .summary-item-label {
        font-size: 10px;
        color: #6b7280;
    }
</style>

<div class="summary-section">
    <div class="summary-grid">
        <div class="summary-item">
            <div class="summary-item-number"><?php echo e($stats['total']); ?></div>
            <div class="summary-item-label"><?php echo e(__('reports.document_inventory.total_documents')); ?></div>
        </div>
        <div class="summary-item">
            <div class="summary-item-number"><?php echo e($stats['physical']); ?></div>
            <div class="summary-item-label"><?php echo e(__('reports.document_inventory.physical')); ?></div>
        </div>
        <div class="summary-item">
            <div class="summary-item-number"><?php echo e($stats['digital']); ?></div>
            <div class="summary-item-label"><?php echo e(__('reports.document_inventory.digital')); ?></div>
        </div>
        <div class="summary-item">
            <div class="summary-item-number"><?php echo e($stats['both']); ?></div>
            <div class="summary-item-label"><?php echo e(__('reports.document_inventory.both')); ?></div>
        </div>
    </div>
</div>

<?php if($documents->isEmpty()): ?>
    <div class="no-data"><?php echo e(__('reports.no_data_available')); ?></div>
<?php else: ?>
    <table class="report-table">
        <thead>
            <tr>
                <th>#</th>
                <th><?php echo e(__('reports.document_inventory.client_name')); ?></th>
                <th><?php echo e(__('reports.document_inventory.case_name')); ?></th>
                <th><?php echo e(__('reports.document_inventory.document_type')); ?></th>
                <th><?php echo e(__('reports.document_inventory.description')); ?></th>
                <th><?php echo e(__('reports.document_inventory.location')); ?></th>
                <th><?php echo e(__('reports.document_inventory.deposit_date')); ?></th>
                <th><?php echo e(__('reports.document_inventory.storage_type')); ?></th>
                <th><?php echo e(__('reports.document_inventory.movement_card')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($index + 1); ?></td>
                    <td><?php echo e($document->client?->client_name_ar ?? $document->client?->client_name_en ?? $document->client_name ?? '—'); ?></td>
                    <td><?php echo e($document->case?->matter_name_ar ?? $document->case?->matter_name_en ?? $document->legacy_matter_name ?? '—'); ?></td>
                    <td><?php echo e($document->document_type ?? '—'); ?></td>
                    <td><?php echo e($document->document_description ?? $document->description ?? '—'); ?></td>
                    <td><?php echo e($document->document_location ?? '—'); ?></td>
                    <td><?php echo e($document->deposit_date?->format('Y-m-d') ?? '—'); ?></td>
                    <td>
                        <?php if($document->document_storage_type): ?>
                            <?php echo e(__('reports.document_inventory.storage_type_' . $document->document_storage_type)); ?>

                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($document->movement_card): ?>
                            <span class="badge badge-success"><?php echo e(__('common.yes')); ?></span>
                        <?php else: ?>
                            <span class="badge badge-secondary"><?php echo e(__('common.no')); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
<?php endif; ?>

<?php if($missingDocuments->isNotEmpty()): ?>
    <div class="summary-section" style="margin-top: 20px;">
        <div class="summary-title"><?php echo e(__('reports.document_inventory.missing_documents')); ?></div>
        <table class="report-table compact-table">
            <thead>
                <tr>
                    <th style="width: 50%;"><?php echo e(__('reports.document_inventory.case_name')); ?></th>
                    <th style="width: 50%;"><?php echo e(__('reports.document_inventory.client_name')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $missingDocuments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $missing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($missing['case']); ?></td>
                        <td><?php echo e($missing['client']); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('reports.layouts.base_pdf', [
    'reportTitle' => __('reports.document_inventory.title'),
    'reportSubtitle' => '',
    'generatedAt' => $generatedAt,
    'locale' => App::getLocale(),
    'showHeader' => true,
    'showFooter' => true,
], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/reports/document_inventory_pdf.blade.php ENDPATH**/ ?>