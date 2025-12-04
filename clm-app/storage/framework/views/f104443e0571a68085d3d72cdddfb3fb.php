<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير المهام الإدارية</title>
    <style>
        @page {
            margin: 20mm 15mm 20mm 15mm;
        }
        body {
            font-family: 'Cairo', 'Noto Kufi Arabic', 'Tahoma', 'Arial', sans-serif;
            color: #1f2937;
            font-size: 12px;
            line-height: 1.5;
        }
        .report-title {
            text-align: center;
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 12px;
        }
        .report-meta {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 16px;
            text-align: right;
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
        }
        .report-table th,
        .report-table td {
            border: 1px solid #d1d5db;
            padding: 8px;
            vertical-align: top;
            text-align: right;
        }
        .report-table th {
            background: #f4f4f4;
            font-size: 11px;
            font-weight: 600;
        }
        .report-table tbody tr:nth-child(even) {
            background: #fafafa;
        }
        .subtask-row {
            background: #f9fafb !important;
            padding-right: 30px;
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
        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }
        .badge-warning {
            background: #fef3c7;
            color: #92400e;
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
    <h1 class="report-title">تقرير المهام الإدارية</h1>

    <?php if(isset($dateRange) && $dateRange): ?>
        <div class="report-meta">
            <strong>الفترة الزمنية:</strong> 
            <?php echo e($dateRange['start']->format('Y-m-d')); ?> - <?php echo e($dateRange['end']->format('Y-m-d')); ?>

        </div>
    <?php endif; ?>

    <?php if(isset($generatedAt)): ?>
        <div class="report-meta">
            <strong>تاريخ الإنشاء:</strong> <?php echo e($generatedAt->format('Y-m-d H:i:s')); ?>

        </div>
    <?php endif; ?>

    <?php if(isset($rows) && $rows->count() > 0): ?>
        <table class="report-table">
            <thead>
                <tr>
                    <th>م/#</th>
                    <th>القضية / الموضوع</th>
                    <th>المحامي</th>
                    <th>المحكمة</th>
                    <th>الدائرة</th>
                    <th>الموكل وصفته</th>
                    <th>الخصم وصفته</th>
                    <th>أخر قرار</th>
                    <th>العمل المطلوب</th>
                    <th>الحالة + عمر الأيام</th>
                    <th>آخر متابعة</th>
                    <th>النتيجة</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($row['serial']); ?></td>
                        <td><?php echo e($row['case_name']); ?></td>
                        <td><?php echo e($row['lawyer_name']); ?></td>
                        <td><?php echo e($row['court']); ?></td>
                        <td><?php echo e($row['circuit']); ?></td>
                        <td><?php echo e($row['client_role']); ?></td>
                        <td><?php echo e($row['opponent_role']); ?></td>
                        <td><?php echo e($row['latest_decision']); ?></td>
                        <td><?php echo e($row['required_work']); ?></td>
                        <td>
                            <span><?php echo e($row['status']); ?></span>
                            <?php if(!empty($row['age_label'] ?? null)): ?>
                                <span class="text-muted"> (<?php echo e($row['age_label']); ?>)</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e($row['last_follow_up']); ?></td>
                        <td><?php echo e($row['result']); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        <?php if(isset($totalTasks)): ?>
            <div class="summary-section">
                <div class="summary-row">
                    <strong>إجمالي المهام:</strong> <?php echo e($totalTasks); ?>

                </div>
                <?php if(isset($completed)): ?>
                    <div class="summary-row">
                        <strong>المهام المنجزة:</strong> <?php echo e($completed->count()); ?>

                    </div>
                <?php endif; ?>
                <?php if(isset($pending)): ?>
                    <div class="summary-row">
                        <strong>المهام قيد التنفيذ:</strong> <?php echo e($pending->count()); ?>

                    </div>
                <?php endif; ?>
                <?php if(isset($overdue)): ?>
                    <div class="summary-row">
                        <strong>المهام المتأخرة:</strong> <?php echo e($overdue->count()); ?>

                    </div>
                <?php endif; ?>
                <?php if(isset($completionRate)): ?>
                    <div class="summary-row">
                        <strong>معدل الإنجاز:</strong> <?php echo e(number_format($completionRate, 1)); ?>%
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="text-center text-muted" style="padding: 40px;">
            لا توجد مهام في الفترة المحددة
        </div>
    <?php endif; ?>
</body>
</html>

<?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/reports/admin_tasks_pdf.blade.php ENDPATH**/ ?>