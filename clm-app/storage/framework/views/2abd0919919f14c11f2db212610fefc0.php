<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقويم الجلسات</title>
    <style>
        @page {
            margin: 15mm;
        }
        body {
            font-family: 'Cairo', 'Noto Kufi Arabic', 'Tahoma', 'Arial', sans-serif;
            color: #1f2937;
            font-size: 11px;
            line-height: 1.4;
        }
        .report-title {
            text-align: center;
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 8px;
        }
        .report-meta {
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 12px;
            text-align: center;
        }
        .calendar-container {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0;
            border: 2px solid #1f2937;
            margin-bottom: 15px;
        }
        .calendar-day-header {
            font-weight: 700;
            background: #4F81BD;
            color: #fff;
            text-align: center;
            padding: 6px 4px;
            font-size: 11px;
            border: 1px solid #1f2937;
        }
        .calendar-day {
            min-height: 90px;
            border: 1px solid #d1d5db;
            padding: 3px;
            background: #fff;
            vertical-align: top;
        }
        .calendar-day.other-month {
            background: #f9fafb;
            color: #9ca3af;
        }
        .day-number {
            font-weight: 600;
            font-size: 11px;
            margin-bottom: 3px;
            padding: 2px;
        }
        .hearing-item {
            font-size: 9px;
            padding: 2px 3px;
            margin: 1px 0;
            background: #dbeafe;
            border-right: 3px solid #3b82f6;
            border-radius: 2px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .hearing-item.overdue {
            background: #fee2e2;
            border-right-color: #ef4444;
        }
        .hearing-item.upcoming {
            background: #dcfce7;
            border-right-color: #22c55e;
        }
        .calendar-sidebar {
            margin-top: 15px;
            padding: 10px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
        }
        .sidebar-title {
            font-weight: 700;
            font-size: 12px;
            margin-bottom: 8px;
        }
        .hearing-list-item {
            font-size: 10px;
            padding: 4px;
            margin: 2px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .text-center {
            text-align: center;
        }
        .text-muted {
            color: #6b7280;
        }
        .legend {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 10px 0;
            font-size: 10px;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .legend-color {
            width: 15px;
            height: 15px;
            border-radius: 2px;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>
    <h1 class="report-title">تقويم الجلسات</h1>
    
    <?php if(isset($dateRange) && $dateRange): ?>
        <div class="report-meta">
            <strong>الفترة:</strong> <?php echo e($dateRange['start']->format('Y-m-d')); ?> - <?php echo e($dateRange['end']->format('Y-m-d')); ?>

        </div>
    <?php endif; ?>

    <?php
        // Determine which month to display (use first hearing date or current month)
        $firstDate = $hearings->first()?->date ?? now('Africa/Cairo');
        $monthStart = $firstDate->copy()->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        
        // Get all hearings for this month
        $monthHearings = $hearings->filter(function($h) use ($monthStart, $monthEnd) {
            return $h->date >= $monthStart && $h->date <= $monthEnd;
        })->groupBy(function($h) {
            return $h->date->format('Y-m-d');
        });
        
        // Get first day of week (0 = Sunday, 6 = Saturday)
        $firstDayOfWeek = $monthStart->dayOfWeek;
        $daysInMonth = $monthStart->daysInMonth;
        
        // Arabic day names
        $dayNames = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
        
        // Get all days including empty cells at start
        $totalCells = 42; // 6 weeks * 7 days
        $days = [];
        
        // Add empty cells before first day
        for ($i = 0; $i < $firstDayOfWeek; $i++) {
            $days[] = null;
        }
        
        // Add all days of the month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $days[] = $day;
        }
    ?>

    <div class="legend">
        <div class="legend-item">
            <div class="legend-color" style="background: #dcfce7; border-color: #22c55e;"></div>
            <span>قادمة</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background: #fee2e2; border-color: #ef4444;"></div>
            <span>متأخرة</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background: #dbeafe; border-color: #3b82f6;"></div>
            <span>منتهية</span>
        </div>
    </div>

    <div class="calendar-container">
        <?php $__currentLoopData = $dayNames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="calendar-day-header"><?php echo e($dayName); ?></div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $isOtherMonth = $day === null;
                $dateString = $day ? $monthStart->copy()->setDay($day)->format('Y-m-d') : null;
                $dayHearings = $dateString ? ($monthHearings[$dateString] ?? collect()) : collect();
                $now = now('Africa/Cairo')->startOfDay();
                $dayDate = $day ? $monthStart->copy()->setDay($day) : null;
            ?>
            <div class="calendar-day <?php echo e($isOtherMonth ? 'other-month' : ''); ?>">
                <?php if($day): ?>
                    <div class="day-number"><?php echo e($day); ?></div>
                    <?php $__currentLoopData = $dayHearings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hearing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $isOverdue = $hearing->date < $now && (empty($hearing->decision) && empty($hearing->short_decision));
                            $isUpcoming = $hearing->date >= $now;
                            $statusClass = $isOverdue ? 'overdue' : ($isUpcoming ? 'upcoming' : '');
                        ?>
                        <div class="hearing-item <?php echo e($statusClass); ?>" title="<?php echo e($hearing->case?->matter_name_ar ?? $hearing->case?->matter_name_en ?? '—'); ?>">
                            <?php echo e(mb_substr($hearing->case?->matter_name_ar ?? $hearing->case?->matter_name_en ?? '—', 0, 20)); ?>

                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <?php if($monthHearings->count() > 0): ?>
        <div class="calendar-sidebar">
            <div class="sidebar-title">تفاصيل الجلسات</div>
            <?php $__currentLoopData = $monthHearings->flatten()->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hearing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="hearing-list-item">
                    <strong><?php echo e($hearing->date->format('Y-m-d')); ?></strong>: 
                    <?php echo e($hearing->case?->matter_name_ar ?? $hearing->case?->matter_name_en ?? '—'); ?>

                    <?php if($hearing->procedure): ?>
                        - <?php echo e(mb_substr($hearing->procedure, 0, 30)); ?>

                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($monthHearings->flatten()->count() > 10): ?>
                <div class="text-muted text-center" style="margin-top: 8px;">
                    و <?php echo e($monthHearings->flatten()->count() - 10); ?> جلسة أخرى
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</body>
</html>

<?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/reports/hearing_schedule_calendar_pdf.blade.php ENDPATH**/ ?>