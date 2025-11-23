<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>بيان بموقف دعاوى العميل</title>
    <style>
        @page {
            margin: 25mm 15mm 20mm 15mm;
        }
        body {
            font-family: 'Cairo', 'Noto Kufi Arabic', 'Tahoma', 'Arial', sans-serif;
            color: #1f2937;
            font-size: 12px;
            line-height: 1.5;
        }
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }
        .firm-brand {
            color: #256b3f;
            font-weight: 700;
        }
        .firm-brand small {
            display: block;
            font-size: 10px;
            color: #1c4532;
        }
        .client-brand {
            text-align: left;
            color: #b91c1c;
            font-weight: 700;
            min-width: 180px;
        }
        .report-title {
            text-align: center;
            font-size: 16px;
            margin: 0 0 12px;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .report-table th,
        .report-table td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            vertical-align: top;
        }
        .report-table th {
            background: #f4f4f4;
            font-size: 12px;
        }
        .report-table tbody tr:nth-child(even) {
            background: #fafafa;
        }
        .summary-row {
            margin-top: 10px;
            font-weight: 600;
        }
        .text-center {
            text-align: center;
        }
        .text-muted {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <header class="report-header">
        <div class="firm-brand">
            Sarie Eldin & Partners
            <small>Attorneys at Law & Legal Consultants</small>
        </div>
        <div class="client-brand">
            <?php echo e($client->client_name_ar ?? $client->client_name_en); ?>

        </div>
    </header>

    <h2 class="report-title">بيان بموقف دعاوى العميل</h2>

    <table class="report-table">
        <thead>
        <tr>
            <?php $__currentLoopData = $columnLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <th><?php echo e($label); ?></th>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tr>
        </thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <?php if($columns['serial']): ?><td class="text-center"><?php echo e($row['serial']); ?></td><?php endif; ?>
                <?php if($columns['matter']): ?><td><?php echo e($row['matter']); ?></td><?php endif; ?>
                <?php if($columns['court']): ?><td><?php echo e($row['court']); ?></td><?php endif; ?>
                <?php if($columns['clientRole']): ?><td><?php echo e($row['clientRole']); ?></td><?php endif; ?>
                <?php if($columns['opponentRole']): ?><td><?php echo e($row['opponentRole']); ?></td><?php endif; ?>
                <?php if($columns['subject']): ?><td><?php echo nl2br(e($row['subject'])); ?></td><?php endif; ?>
                <?php if($columns['latestDecision']): ?><td><?php echo e($row['latestDecision']); ?></td><?php endif; ?>
                <?php if($columns['evaluation']): ?><td><?php echo e($row['evaluation']); ?></td><?php endif; ?>
                <?php if($columns['financialProvision']): ?><td><?php echo e($row['financialProvision']); ?></td><?php endif; ?>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="<?php echo e(count($columnLabels)); ?>" class="text-center text-muted">لا توجد قضايا مسجلة لهذا العميل</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

    <div class="summary-row">
        إجمالي عدد دعاوى العميل: <?php echo e($totalCases); ?>

    </div>
    <div class="text-muted">
        تاريخ الإصدار: <?php echo e($generatedAt->format('Y-m-d H:i')); ?>

    </div>
</body>
</html>

<?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/reports/client_cases_pdf.blade.php ENDPATH**/ ?>