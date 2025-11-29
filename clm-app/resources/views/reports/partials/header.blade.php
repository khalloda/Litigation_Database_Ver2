<div class="report-header" style="width: 100%; display: table; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; margin-bottom: 20px; font-family: 'Cairo', 'Noto Kufi Arabic', 'Tahoma', 'Arial', sans-serif;">
    <div style="display: table-cell; vertical-align: middle; width: 22%; {{ ($locale ?? 'ar') === 'ar' ? 'text-align: right;' : 'text-align: left;' }}">
        @if(!empty($firmLogoPath) && file_exists($firmLogoPath))
            <img src="{{ $firmLogoPath }}" alt="Firm Logo" style="max-height: 60px; width: auto; max-width: 100%;">
        @endif
    </div>
    <div style="display: table-cell; vertical-align: middle; text-align: center; {{ !empty($clientName) ? 'color: #b91c1c; font-weight: 700; font-size: 18px;' : '' }}">
        {{ $clientName ?? '' }}
    </div>
    <div style="display: table-cell; vertical-align: middle; width: 22%; {{ ($locale ?? 'ar') === 'ar' ? 'text-align: left;' : 'text-align: right;' }}">
        @if(!empty($clientLogoPath) && file_exists($clientLogoPath))
            <img src="{{ $clientLogoPath }}" alt="Client Logo" style="max-height: 60px; width: auto; max-width: 100%;">
        @endif
    </div>
</div>

