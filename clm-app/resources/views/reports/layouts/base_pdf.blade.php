<!DOCTYPE html>
<html lang="{{ $locale ?? 'ar' }}" dir="{{ ($locale ?? 'ar') === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $reportTitle ?? __('reports.default_title') }}</title>
    <style>
        @page {
            margin: {{ $marginTop ?? '40mm' }} {{ $marginRight ?? '15mm' }} {{ $marginBottom ?? '25mm' }} {{ $marginLeft ?? '15mm' }};
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Cairo', 'Noto Kufi Arabic', 'Tahoma', 'Arial', sans-serif;
            color: #1f2937;
            font-size: {{ $fontSize ?? '12px' }};
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .report-title {
            text-align: center;
            font-size: {{ $titleSize ?? '18px' }};
            font-weight: 700;
            margin: 0 0 16px;
            color: #111827;
        }
        .report-subtitle {
            text-align: center;
            font-size: {{ $subtitleSize ?? '14px' }};
            color: #6b7280;
            margin: 0 0 20px;
        }
        .report-meta {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 16px;
            text-align: {{ ($locale ?? 'ar') === 'ar' ? 'right' : 'left' }};
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
            padding: {{ $cellPadding ?? '8px' }};
            vertical-align: top;
            word-wrap: break-word;
        }
        .report-table th {
            background: #f3f4f6;
            font-weight: 600;
            font-size: {{ $headerFontSize ?? '11px' }};
            text-align: {{ ($locale ?? 'ar') === 'ar' ? 'right' : 'left' }};
        }
        .report-table td {
            font-size: {{ $cellFontSize ?? '11px' }};
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
    @stack('styles')
</head>
<body>
    @if(isset($showHeader) && $showHeader)
        @include('reports.partials.header', [
            'firmLogoPath' => $firmLogoPath ?? null,
            'clientLogoPath' => $clientLogoPath ?? null,
            'clientName' => $clientName ?? null,
            'locale' => $locale ?? 'ar'
        ])
    @endif

    @if(isset($reportTitle) && $reportTitle)
        <h1 class="report-title">{{ $reportTitle }}</h1>
    @endif

    @if(isset($reportSubtitle) && $reportSubtitle)
        <div class="report-subtitle">{{ $reportSubtitle }}</div>
    @endif

    @if(isset($reportMeta) && is_array($reportMeta) && count($reportMeta) > 0)
        <div class="report-meta">
            @foreach($reportMeta as $key => $value)
                <div><strong>{{ $key }}:</strong> {{ $value }}</div>
            @endforeach
        </div>
    @endif

    @yield('content')

    @if(isset($showFooter) && $showFooter)
        @include('reports.partials.footer', [
            'generatedAt' => $generatedAt ?? now(),
            'locale' => $locale ?? 'ar'
        ])
    @endif
</body>
</html>

