<!DOCTYPE html>
<html lang="{{ $locale ?? 'ar' }}" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>بطاقة حركة توكيل</title>
    <style>
        @page {
            margin: 15mm 15mm 25mm 15mm;
        }
        body {
            font-family: 'Cairo', 'Noto Kufi Arabic', 'Tahoma', 'Arial', sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: #000;
            margin: 0;
            padding: 0 0 35mm 0;
        }
        .container {
            width: 100%;
        }
        .header-row {
            width: 100%;
            margin-bottom: 10px;
        }
        .header-row table {
            width: 100%;
            border-collapse: collapse;
        }
        .logo-cell {
            text-align: left;
        }
        .logo-cell img {
            max-height: 60px;
            width: auto;
        }
        .title-cell {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .meta-table th,
        .meta-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 15px;
        }
        .meta-table th {
            background: #f5f5f5;
            font-weight: 600;
        }
        .section-title {
            margin-top: 10px;
            margin-bottom: 4px;
            font-weight: bold;
            font-size: 17px;
        }
        .movements-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .movements-table th,
        .movements-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 15px;
            vertical-align: top;
        }
        .movements-table th {
            background: #f5f5f5;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            position: fixed;
            bottom: 10mm;
            left: 15mm;
            right: 15mm;
            font-size: 15px;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-row">
            <table dir="ltr">
                <tr>
                    <td class="logo-cell">
                        @if(!empty($firmLogoPath) && file_exists($firmLogoPath))
                            <img src="{{ $firmLogoPath }}" alt="Logo">
                        @endif
                    </td>
                    <td class="title-cell">بطاقة حركة توكيل</td>
                    <td style="width: 25%;"></td>
                </tr>
            </table>
        </div>

        <table class="meta-table">
            <tr>
                <th style="width: 25%;">اسم العميل</th>
                <td style="width: 25%;">{{ optional($poa->client)->client_name_ar ?? optional($poa->client)->client_name_en ?? '' }}</td>
                <th style="width: 25%;">رقم التوكيل</th>
                <td style="width: 25%;">{{ $poa->poa_number ?? '' }}</td>
            </tr>
            <tr>
                <th>اسم الموكل</th>
                <td>{{ $poa->principal_name }}</td>
                <th>اسم العميل للطباعة</th>
                <td>{{ $poa->client_print_name }}</td>
            </tr>
            <tr>
                <th>تاريخ إصدار التوكيل</th>
                <td>{{ optional($poa->issue_date)->format('Y-m-d') }}</td>
                <th>السنة / المسلسل</th>
                <td>{{ $poa->year }} / {{ $poa->serial }}</td>
            </tr>
            <tr>
                <th>الصفة</th>
                <td>{{ $poa->capacity }}</td>
                <th>معرف M-Files</th>
                <td>{{ $poa->mfiles_id }}</td>
            </tr>
            <tr>
                <th>ملاحظات التوكيل</th>
                <td colspan="3">{{ $poa->notes }}</td>
            </tr>
        </table>

        <div class="section-title">حركة التوكيل</div>

        <table class="movements-table">
            <thead>
            <tr>
                <th style="width: 6%;">م</th>
                <th style="width: 14%;">التاريخ</th>
                <th style="width: 20%;">من</th>
                <th style="width: 20%;">إلى</th>
                <th style="width: 14%;">الحالة</th>
                <th style="width: 26%;">ملاحظات</th>
            </tr>
            </thead>
            <tbody>
            @forelse($movements as $index => $movement)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ optional($movement->date)->format('Y-m-d') }}</td>
                    <td>{{ $movement->from_location }}</td>
                    <td>{{ $movement->to_location }}</td>
                    <td class="text-center">{{ $movement->status }}</td>
                    <td>{!! $movement->notes ? nl2br(e($movement->notes)) : '' !!}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">لا يوجد سجل حركة لهذا التوكيل.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        <div>
            إجمالي عدد الحركات: {{ $totalMovements }}
        </div>
        <div>
            الصفحة 1 من 1
        </div>
    </div>
</body>
</html>


