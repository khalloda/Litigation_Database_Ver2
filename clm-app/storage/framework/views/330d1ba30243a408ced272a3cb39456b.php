<!DOCTYPE html>
<html lang="<?php echo e($locale ?? 'ar'); ?>" dir="<?php echo e(($locale ?? 'ar') === 'ar' ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo e($reportTitle ?? __('reports.default_title')); ?></title>
    <style>
        @page {
            margin: <?php echo e($marginTop ?? '40mm'); ?> <?php echo e($marginRight ?? '15mm'); ?> <?php echo e($marginBottom ?? '25mm'); ?> <?php echo e($marginLeft ?? '15mm'); ?>;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Cairo', 'Noto Kufi Arabic', 'Tahoma', 'Arial', sans-serif;
            color: #1f2937;
            font-size: <?php echo e($fontSize ?? '12px'); ?>;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .report-title {
            text-align: center;
            font-size: <?php echo e($titleSize ?? '18px'); ?>;
            font-weight: 700;
            margin: 0 0 16px;
            color: #111827;
        }
        .report-subtitle {
            text-align: center;
            font-size: <?php echo e($subtitleSize ?? '14px'); ?>;
            color: #6b7280;
            margin: 0 0 20px;
        }
        .report-meta {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 16px;
            text-align: <?php echo e(($locale ?? 'ar') === 'ar' ? 'right' : 'left'); ?>;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            page-break-inside: auto;
        }
        .report-table thead {
            display: table-header-group;
        }
        .report-table tbody {
            display: table-row-group;
        }
        .report-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        .report-table th,
        .report-table td {
            border: 1px solid #d1d5db;
            padding: <?php echo e($cellPadding ?? '8px'); ?>;
            vertical-align: top;
            word-wrap: break-word;
        }
        .report-table th {
            background: #f3f4f6;
            font-weight: 600;
            font-size: <?php echo e($headerFontSize ?? '11px'); ?>;
            text-align: <?php echo e(($locale ?? 'ar') === 'ar' ? 'right' : 'left'); ?>;
        }
        .report-table td {
            font-size: <?php echo e($cellFontSize ?? '11px'); ?>;
        }
        .report-table tbody tr:nth-child(even) {
            background: #fafafa;
        }
        .report-table tbody tr:hover {
            background: #f3f4f6;
        }
        .text-center {
            text-align: center !important;
        }
        .text-right {
            text-align: right !important;
        }
        .text-left {
            text-align: left !important;
        }
        .text-muted {
            color: #6b7280;
        }
        .text-bold {
            font-weight: 700;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
        }
        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }
        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }
        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }
        .summary-section {
            margin-top: 20px;
            padding: 12px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
        }
        .summary-row {
            margin: 8px 0;
            font-weight: 600;
        }
        .summary-label {
            display: inline-block;
            min-width: 150px;
            color: #6b7280;
        }
        .footer {
            margin-top: 20px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #6b7280;
            text-align: center;
        }
        .page-break {
            page-break-before: always;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #9ca3af;
            font-style: italic;
        }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
    <?php if(isset($showHeader) && $showHeader): ?>
        <?php echo $__env->make('reports.partials.header', [
            'firmLogoPath' => $firmLogoPath ?? null,
            'clientLogoPath' => $clientLogoPath ?? null,
            'clientName' => $clientName ?? null,
            'locale' => $locale ?? 'ar'
        ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php endif; ?>

    <?php if(isset($reportTitle) && $reportTitle): ?>
        <h1 class="report-title"><?php echo e($reportTitle); ?></h1>
    <?php endif; ?>

    <?php if(isset($reportSubtitle) && $reportSubtitle): ?>
        <div class="report-subtitle"><?php echo e($reportSubtitle); ?></div>
    <?php endif; ?>

    <?php if(isset($reportMeta) && is_array($reportMeta) && count($reportMeta) > 0): ?>
        <div class="report-meta">
            <?php $__currentLoopData = $reportMeta; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div><strong><?php echo e($key); ?>:</strong> <?php echo e($value); ?></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <?php echo $__env->yieldContent('content'); ?>

    <?php if(isset($showFooter) && $showFooter): ?>
        <?php echo $__env->make('reports.partials.footer', [
            'generatedAt' => $generatedAt ?? now(),
            'locale' => $locale ?? 'ar'
        ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php endif; ?>
</body>
</html>

<?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/reports/layouts/base_pdf.blade.php ENDPATH**/ ?>