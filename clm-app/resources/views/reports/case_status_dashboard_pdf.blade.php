@extends('reports.layouts.base_pdf', [
    'reportTitle' => __('reports.case_status_dashboard.title'),
    'reportSubtitle' => '',
    'generatedAt' => $generatedAt,
    'locale' => App::getLocale(),
    'showHeader' => true,
    'showFooter' => true,
    'marginTop' => '15mm',
    'marginBottom' => '15mm',
])

@section('content')
<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 20px;
    }
    .stat-card {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px;
        text-align: center;
    }
    .stat-card-number {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 4px;
    }
    .stat-card-label {
        font-size: 11px;
        color: #6b7280;
    }
    .stat-card.active {
        background: #dcfce7;
        border-color: #86efac;
    }
    .stat-card.closed {
        background: #f3f4f6;
        border-color: #d1d5db;
    }
    .summary-section {
        margin-bottom: 16px;
    }
    .summary-title {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 8px;
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 4px;
    }
    .summary-list {
        font-size: 11px;
        margin: 0;
        padding-{{ App::getLocale() === 'ar' ? 'right' : 'left' }}: 20px;
    }
    .compact-table {
        font-size: 10px;
    }
    .compact-table th,
    .compact-table td {
        padding: 4px 6px;
    }
    .attention-item {
        padding: 4px;
        margin-bottom: 2px;
    }
    .reason-badge {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 9px;
        font-weight: 600;
        background: #fef3c7;
        color: #92400e;
    }
</style>

<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-card-number">{{ $stats['total'] }}</div>
        <div class="stat-card-label">{{ __('reports.case_status_dashboard.total_cases') }}</div>
    </div>
    <div class="stat-card active">
        <div class="stat-card-number">{{ $stats['active'] }}</div>
        <div class="stat-card-label">{{ __('reports.case_status_dashboard.active_cases') }}</div>
    </div>
    <div class="stat-card closed">
        <div class="stat-card-number">{{ $stats['closed'] }}</div>
        <div class="stat-card-label">{{ __('reports.case_status_dashboard.closed_cases') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-number">{{ $attentionRequired->count() }}</div>
        <div class="stat-card-label">{{ __('reports.case_status_dashboard.attention_required') }}</div>
    </div>
</div>

<div class="summary-section">
    <div class="summary-title">{{ __('reports.case_status_dashboard.by_category') }}</div>
    <ul class="summary-list">
        @foreach($stats['by_category']->take(5) as $item)
            <li>{{ $item['category'] }}: {{ $item['count'] }}</li>
        @endforeach
    </ul>
</div>

<div class="summary-section">
    <div class="summary-title">{{ __('reports.case_status_dashboard.by_court') }}</div>
    <ul class="summary-list">
        @foreach($stats['by_court']->take(5) as $item)
            <li>{{ $item['court'] }}: {{ $item['count'] }}</li>
        @endforeach
    </ul>
</div>

@if($attentionRequired->isNotEmpty())
    <div class="summary-section">
        <div class="summary-title">{{ __('reports.case_status_dashboard.attention_required') }}</div>
        <table class="report-table compact-table">
            <thead>
                <tr>
                    <th style="width: 35%;">{{ __('reports.case_status_dashboard.case_name') }}</th>
                    <th style="width: 35%;">{{ __('reports.case_status_dashboard.client_name') }}</th>
                    <th style="width: 30%;">{{ __('reports.case_status_dashboard.reason') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attentionRequired->take(15) as $item)
                    <tr>
                        <td>{{ $item['case'] }}</td>
                        <td>{{ $item['client'] }}</td>
                        <td>
                            <span class="reason-badge">
                                {{ __('reports.case_status_dashboard.reason_' . $item['reason']) }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@if($recentActivity->isNotEmpty())
    <div class="summary-section">
        <div class="summary-title">{{ __('reports.case_status_dashboard.recent_activity') }}</div>
        <table class="report-table compact-table">
            <thead>
                <tr>
                    <th style="width: 35%;">{{ __('reports.case_status_dashboard.case_name') }}</th>
                    <th style="width: 35%;">{{ __('reports.case_status_dashboard.client_name') }}</th>
                    <th style="width: 15%;">{{ __('reports.case_status_dashboard.last_hearing') }}</th>
                    <th style="width: 15%;">{{ __('reports.case_status_dashboard.last_task') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentActivity->take(10) as $item)
                    <tr>
                        <td>{{ $item['case'] }}</td>
                        <td>{{ $item['client'] }}</td>
                        <td>{{ $item['last_hearing'] ?? '—' }}</td>
                        <td>{{ $item['last_task'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection

