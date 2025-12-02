<!DOCTYPE html>
<html lang="{{ $locale ?? 'ar' }}" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>بطاقة حركة مستند</title>
    <style>
        @page {
            margin: 15mm 15mm 25mm 15mm;
        }
        body {
            font-family: 'Cairo', 'Noto Kufi Arabic', 'Tahoma', 'Arial', sans-serif;
            font-size: 14px; /* was 12px, + ~15% for better print readability */
            line-height: 1.5;
            color: #000;
            margin: 0;
            padding: 0 0 35mm 0; /* extra bottom padding so content does not overlap fixed footer */
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
        .header-row td {
            vertical-align: middle;
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
            font-size: 21px; /* was 18px */
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
            font-size: 13px; /* was 11px */
        }
        .meta-table th {
            background: #f5f5f5;
            font-weight: 600;
        }
        .section-title {
            margin-top: 10px;
            margin-bottom: 4px;
            font-weight: bold;
            font-size: 15px; /* was 13px */
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
            font-size: 13px; /* was 11px */
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
            font-size: 13px; /* was 11px */
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
                    <td class="title-cell">بطاقة حركة مستند</td>
                    <td style="width: 25%;"></td>
                </tr>
            </table>
        </div>

        <table class="meta-table">
            <tr>
                <th style="width: 25%;">اسم العميل</th>
                <td style="width: 25%;">{{ optional($document->client)->client_name_ar ?? optional($document->client)->client_name_en ?? '' }}</td>
                <th style="width: 25%;">رقم الدعوى</th>
                <td style="width: 25%;">{{ $document->case_number ?? '' }}</td>
            </tr>
            <tr>
                <th>رقم ملف العميل</th>
                <td>{{ $document->client_id ?? '' }}</td>
                <th>مسلسل المستند ID</th>
                <td>{{ $document->id }}</td>
            </tr>
            <tr>
                <th>تاريخ الإيداع بالخزينة</th>
                <td>{{ optional($document->deposit_date)->format('Y-m-d') }}</td>
                <th>نوع المستند</th>
                <td>{{ $document->document_type }}</td>
            </tr>
            <tr>
                <th>المحامي</th>
                <td>{{ $document->lawyer ?? $document->responsible_lawyer }}</td>
                <th>الموظف الإداري</th>
                <td>{{ $document->admin_staff }}</td>
            </tr>
            <tr>
                <th>القسم</th>
                <td>{{ $document->department }}</td>
                <th>اسم الدعوى (قديم)</th>
                <td>{{ $document->legacy_matter_name }}</td>
            </tr>
            <tr>
                <th>وصف المستند</th>
                <td colspan="3">
                    {{ $document->document_description ?? $document->description ?? '' }}
                </td>
            </tr>
        </table>

        <div class="section-title">حركة المستند</div>

        <table class="movements-table">
            <thead>
            <tr>
                <th style="width: 6%;">م</th>
                <th style="width: 14%;">التاريخ</th>
                <th style="width: 18%;">من</th>
                <th style="width: 18%;">إلى</th>
                <th style="width: 14%;">الحالة</th>
                <th style="width: 18%;">ملاحظات</th>
                <th style="width: 12%;">التوقيع</th>
            </tr>
            </thead>
            <tbody>
            @forelse($movements as $index => $movement)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">
                        @if(!empty($movement['date']))
                            {{ \Carbon\Carbon::parse($movement['date'])->format('Y-m-d') }}
                        @endif
                    </td>
                    <td>{{ $movement['from_location'] ?? '' }}</td>
                    <td>{{ $movement['to_location'] ?? '' }}</td>
                    <td class="text-center">
                        {{ $movement['status'] ?? '' }}
                    </td>
                    <td>{!! isset($movement['notes']) ? nl2br(e($movement['notes'])) : '' !!}</td>
                    <td></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">لا يوجد سجل حركة لهذا المستند.</td>
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
            {{-- wkhtmltopdf does not reliably support total page counters in this standalone template --}}
            {{-- For now, movement card is a single page, so we render a static label --}}
            الصفحة 1 من 1
        </div>
    </div>
</body>
</html>


