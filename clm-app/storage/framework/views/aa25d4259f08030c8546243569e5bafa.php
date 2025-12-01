<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير جدول الجلسات</title>
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
    <h1 class="report-title">تقرير جدول الجلسات</h1>

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
                    <th>#</th>
                    <th>تاريخ الجلسة</th>
                    <th>اسم القضية</th>
                    <th>اسم العميل</th>
                    <th>المحكمة</th>
                    <th>الإجراءات</th>
                    <th>القرار</th>
                    <th>الجلسة القادمة</th>
                    <th>المحامي</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($row['serial']); ?></td>
                        <td><?php echo e($row['date']); ?></td>
                        <td><?php echo e($row['case_name']); ?></td>
                        <td><?php echo e($row['client_name']); ?></td>
                        <td><?php echo e($row['court']); ?></td>
                        <td><?php echo e($row['procedure']); ?></td>
                        <td><?php echo e(mb_substr($row['decision'], 0, 50)); ?><?php echo e(mb_strlen($row['decision']) > 50 ? '...' : ''); ?></td>
                        <td><?php echo e($row['next_hearing']); ?></td>
                        <td><?php echo e($row['lawyer']); ?></td>
                        <td>
                            <?php if($row['status'] === 'overdue'): ?>
                                <span class="badge badge-danger">متأخرة</span>
                            <?php elseif($row['status'] === 'upcoming'): ?>
                                <span class="badge badge-info">قادمة</span>
                            <?php else: ?>
                                <span class="badge badge-success">منتهية</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        <?php if(isset($totalHearings)): ?>
            <div class="summary-section">
                <div class="summary-row">
                    <strong>إجمالي الجلسات:</strong> <?php echo e($totalHearings); ?>

                </div>
                <?php if(isset($upcoming)): ?>
                    <div class="summary-row">
                        <strong>الجلسات القادمة:</strong> <?php echo e($upcoming->count()); ?>

                    </div>
                <?php endif; ?>
                <?php if(isset($past)): ?>
                    <div class="summary-row">
                        <strong>الجلسات المنتهية:</strong> <?php echo e($past->count()); ?>

                    </div>
                <?php endif; ?>
                <?php if(isset($overdue)): ?>
                    <div class="summary-row">
                        <strong>الجلسات المتأخرة:</strong> <?php echo e($overdue->count()); ?>

                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="text-center text-muted" style="padding: 40px;">
            لا توجد جلسات في الفترة المحددة
        </div>
    <?php endif; ?>
</body>
</html>

<?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/reports/hearing_schedule_pdf.blade.php ENDPATH**/ ?>