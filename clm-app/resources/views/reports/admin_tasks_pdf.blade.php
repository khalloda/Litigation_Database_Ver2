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

    @if(isset($dateRange) && $dateRange)
        <div class="report-meta">
            <strong>الفترة الزمنية:</strong> 
            {{ $dateRange['start']->format('Y-m-d') }} - {{ $dateRange['end']->format('Y-m-d') }}
        </div>
    @endif

    @if(isset($generatedAt))
        <div class="report-meta">
            <strong>تاريخ الإنشاء:</strong> {{ $generatedAt->format('Y-m-d H:i:s') }}
        </div>
    @endif

    @if(isset($rows) && $rows->count() > 0)
        <table class="report-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>المهمة المطلوبة</th>
                    <th>اسم القضية</th>
                    <th>المحامي</th>
                    <th>الحالة</th>
                    <th>المنفذ</th>
                    <th>تاريخ الإنشاء</th>
                    <th>تاريخ التنفيذ</th>
                    <th>النتيجة</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tasks as $index => $task)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $task->required_work ?? '—' }}</td>
                        <td>{{ $task->case?->matter_name_ar ?? $task->case?->matter_name_en ?? '—' }}</td>
                        <td>{{ $task->lawyer?->lawyer_name_ar ?? $task->lawyer?->lawyer_name_en ?? '—' }}</td>
                        <td>{{ $task->status ?? '—' }}</td>
                        <td>{{ $task->performer ?? '—' }}</td>
                        <td>{{ $task->creation_date?->format('Y-m-d') ?? '—' }}</td>
                        <td>{{ $task->execution_date?->format('Y-m-d') ?? '—' }}</td>
                        <td>{{ mb_substr($task->result ?? '', 0, 50) }}{{ mb_strlen($task->result ?? '') > 50 ? '...' : '' }}</td>
                        <td>
                            @php
                                $isOverdue = ($task->execution_date && $task->execution_date < now() && empty($task->result)) || $task->alert;
                            @endphp
                            @if($isOverdue)
                                <span class="badge badge-danger">متأخرة</span>
                            @elseif(!empty($task->result))
                                <span class="badge badge-success">منجزة</span>
                            @else
                                <span class="badge badge-warning">قيد التنفيذ</span>
                            @endif
                        </td>
                    </tr>
                    @if(isset($includeSubtasks) && $includeSubtasks && $task->subtasks && $task->subtasks->count() > 0)
                        @foreach($task->subtasks as $subtask)
                            <tr class="subtask-row">
                                <td></td>
                                <td colspan="8">
                                    <strong>→</strong> {{ $subtask->result ?? $subtask->performer ?? '—' }}
                                    @if($subtask->next_date)
                                        ({{ $subtask->next_date->format('Y-m-d') }})
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>

        @if(isset($totalTasks))
            <div class="summary-section">
                <div class="summary-row">
                    <strong>إجمالي المهام:</strong> {{ $totalTasks }}
                </div>
                @if(isset($completed))
                    <div class="summary-row">
                        <strong>المهام المنجزة:</strong> {{ $completed->count() }}
                    </div>
                @endif
                @if(isset($pending))
                    <div class="summary-row">
                        <strong>المهام قيد التنفيذ:</strong> {{ $pending->count() }}
                    </div>
                @endif
                @if(isset($overdue))
                    <div class="summary-row">
                        <strong>المهام المتأخرة:</strong> {{ $overdue->count() }}
                    </div>
                @endif
                @if(isset($completionRate))
                    <div class="summary-row">
                        <strong>معدل الإنجاز:</strong> {{ number_format($completionRate, 1) }}%
                    </div>
                @endif
            </div>
        @endif
    @else
        <div class="text-center text-muted" style="padding: 40px;">
            لا توجد مهام في الفترة المحددة
        </div>
    @endif
</body>
</html>

