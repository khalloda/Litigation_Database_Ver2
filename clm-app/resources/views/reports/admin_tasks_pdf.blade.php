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
                    <th>م/#</th>
                    <th>القضية / الموضوع</th>
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
                @foreach($rows as $row)
                    <tr>
                        <td>{{ $row['serial'] }}</td>
                        <td>{{ $row['case_name'] }}</td>
                        <td>{{ $row['court'] }}</td>
                        <td>{{ $row['circuit'] }}</td>
                        <td>{{ $row['client_role'] }}</td>
                        <td>{{ $row['opponent_role'] }}</td>
                        <td>{{ $row['latest_decision'] }}</td>
                        <td>{{ $row['required_work'] }}</td>
                        <td>
                            <span>{{ $row['status'] }}</span>
                            @if(!is_null($row['age_days'] ?? null))
                                <span class="text-muted"> ({{ $row['age_days'] }} يوم)</span>
                            @endif
                        </td>
                        <td>{{ $row['last_follow_up'] }}</td>
                        <td>{{ $row['result'] }}</td>
                    </tr>
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

