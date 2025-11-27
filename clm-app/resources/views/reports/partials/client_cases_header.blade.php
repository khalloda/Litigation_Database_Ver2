<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <style>
        .report-header {
            width: 100%;
            display: table;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 6px;
            font-family: 'Cairo', 'Noto Kufi Arabic', 'Tahoma', 'Arial', sans-serif;
            color: #1f2937;
            table-layout: fixed;
        }
        .header-cell {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }
        .logo-cell {
            width: 22%;
        }
        .logo-left { text-align: left; }
        .logo-right { text-align: right; }
        .logo-cell img {
            max-height: 52px;
            width: auto;
            max-width: 100%;
        }
        .client-brand {
            color: #b91c1c;
            font-weight: 700;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="report-header">
        <div class="header-cell logo-cell logo-left">
            @if(!empty($firmLogoPath) && file_exists($firmLogoPath))
                <img src="{{ $firmLogoPath }}" alt="Firm Logo">
            @endif
        </div>
        <div class="header-cell client-brand">
            {{ $clientName }}
        </div>
        <div class="header-cell logo-cell logo-right">
            @if(!empty($clientLogoPath) && file_exists($clientLogoPath))
                <img src="{{ $clientLogoPath }}" alt="Client Logo">
            @endif
        </div>
    </div>
</body>
</html>

