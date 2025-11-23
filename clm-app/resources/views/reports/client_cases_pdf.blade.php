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
            {{ $client->client_name_ar ?? $client->client_name_en }}
        </div>
    </header>

    <h2 class="report-title">بيان بموقف دعاوى العميل</h2>

    <table class="report-table">
        <thead>
        <tr>
            @foreach($columnLabels as $label)
                <th>{{ $label }}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @forelse($rows as $row)
            <tr>
                @if($columns['serial'])<td class="text-center">{{ $row['serial'] }}</td>@endif
                @if($columns['matter'])<td>{{ $row['matter'] }}</td>@endif
                @if($columns['court'])<td>{{ $row['court'] }}</td>@endif
                @if($columns['clientRole'])<td>{{ $row['clientRole'] }}</td>@endif
                @if($columns['opponentRole'])<td>{{ $row['opponentRole'] }}</td>@endif
                @if($columns['subject'])<td>{!! nl2br(e($row['subject'])) !!}</td>@endif
                @if($columns['latestDecision'])<td>{{ $row['latestDecision'] }}</td>@endif
                @if($columns['evaluation'])<td>{{ $row['evaluation'] }}</td>@endif
                @if($columns['financialProvision'])<td>{{ $row['financialProvision'] }}</td>@endif
            </tr>
        @empty
            <tr>
                <td colspan="{{ count($columnLabels) }}" class="text-center text-muted">لا توجد قضايا مسجلة لهذا العميل</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="summary-row">
        إجمالي عدد دعاوى العميل: {{ $totalCases }}
    </div>
    <div class="text-muted">
        تاريخ الإصدار: {{ $generatedAt->format('Y-m-d H:i') }}
    </div>
</body>
</html>

