# Report Templates Documentation

This directory contains shared Blade templates for generating PDF reports.

## Structure

```
reports/
├── layouts/
│   └── base_pdf.blade.php      # Base layout for all PDF reports
├── partials/
│   ├── header.blade.php        # Report header with logos
│   ├── footer.blade.php        # Report footer with generation date
│   ├── table.blade.php         # Reusable table component
│   ├── summary.blade.php       # Summary section component
│   └── badge.blade.php         # Badge/alert component
├── client_cases_pdf.blade.php  # Existing client cases report
└── README.md                   # This file
```

## Usage Examples

### Example 1: Simple Report Using Base Layout

```php
// In ReportController
$pdf = SnappyPdf::loadView('reports.simple_report', [
    'reportTitle' => 'My Report Title',
    'reportSubtitle' => 'Optional subtitle',
    'locale' => 'ar',
    'showHeader' => true,
    'showFooter' => true,
    'generatedAt' => now(),
    'data' => $reportData,
])
->setPaper('a4', 'portrait');
```

```blade
{{-- resources/views/reports/simple_report.blade.php --}}
@extends('reports.layouts.base_pdf')

@section('content')
    @include('reports.partials.table', [
        'headers' => ['Column 1', 'Column 2', 'Column 3'],
        'rows' => $data,
        'locale' => 'ar'
    ])
@endsection
```

### Example 2: Report with Summary

```blade
@extends('reports.layouts.base_pdf')

@section('content')
    @include('reports.partials.table', [
        'headers' => $headers,
        'rows' => $rows,
        'locale' => $locale
    ])

    @include('reports.partials.summary', [
        'items' => [
            'Total Records' => count($rows),
            'Date Range' => $dateRange,
        ],
        'locale' => $locale
    ])
@endsection
```

### Example 3: Custom Report (Standalone)

For reports that don't use the base layout:

```blade
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        /* Your custom styles */
    </style>
</head>
<body>
    @include('reports.partials.header', [
        'firmLogoPath' => $firmLogoPath,
        'clientLogoPath' => $clientLogoPath,
        'clientName' => $clientName,
        'locale' => 'ar'
    ])

    <h1>Report Title</h1>

    @include('reports.partials.table', [
        'headers' => $headers,
        'rows' => $rows,
        'locale' => 'ar'
    ])

    @include('reports.partials.footer', [
        'generatedAt' => now(),
        'locale' => 'ar'
    ])
</body>
</html>
```

## Available Variables

### Base Layout Variables

- `$locale` (string): Language locale ('ar' or 'en')
- `$reportTitle` (string): Main report title
- `$reportSubtitle` (string): Optional subtitle
- `$reportMeta` (array): Key-value pairs for metadata display
- `$showHeader` (bool): Show header partial (default: false)
- `$showFooter` (bool): Show footer partial (default: false)
- `$firmLogoPath` (string|null): Path to firm logo image
- `$clientLogoPath` (string|null): Path to client logo image
- `$clientName` (string|null): Client name to display
- `$generatedAt` (Carbon): Generation timestamp
- `$marginTop`, `$marginBottom`, `$marginLeft`, `$marginRight`: Page margins
- `$fontSize`, `$titleSize`, `$subtitleSize`: Font sizes

### Table Partial Variables

- `$headers` (array): Column headers
- `$rows` (array): Data rows
- `$columns` (array): Column visibility flags (optional)
- `$locale` (string): Locale for RTL/LTR
- `$emptyMessage` (string|null): Custom empty message

### Summary Partial Variables

- `$items` (array): Key-value pairs for summary items
- `$locale` (string): Locale

### Badge Partial Variables

- `$type` (string): Badge type ('success', 'warning', 'danger', 'info')
- `$text` (string): Badge text

## Styling

All components use inline styles optimized for PDF generation with wkhtmltopdf. The base layout includes:
- RTL/LTR support
- Print-friendly table styling
- Responsive font sizes
- Page break handling
- Color-coded badges

## Notes

- All dates should use Carbon instances
- Arabic fonts (Cairo, Noto Kufi Arabic) should be installed on the server
- Logo paths should be absolute file system paths
- Use `nl2br(e($text))` for text fields that may contain line breaks

