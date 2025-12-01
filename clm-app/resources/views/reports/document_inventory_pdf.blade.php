@extends('reports.layouts.base_pdf', [
    'reportTitle' => __('reports.document_inventory.title'),
    'reportSubtitle' => '',
    'generatedAt' => $generatedAt,
    'locale' => App::getLocale(),
    'showHeader' => true,
    'showFooter' => true,
])

@section('content')
<style>
    .summary-section {
        margin-bottom: 16px;
    }
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
        margin-bottom: 12px;
    }
    .summary-item {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        padding: 8px;
        text-align: center;
        border-radius: 4px;
    }
    .summary-item-number {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
    }
    .summary-item-label {
        font-size: 10px;
        color: #6b7280;
    }
</style>

<div class="summary-section">
    <div class="summary-grid">
        <div class="summary-item">
            <div class="summary-item-number">{{ $stats['total'] }}</div>
            <div class="summary-item-label">{{ __('reports.document_inventory.total_documents') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-item-number">{{ $stats['physical'] }}</div>
            <div class="summary-item-label">{{ __('reports.document_inventory.physical') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-item-number">{{ $stats['digital'] }}</div>
            <div class="summary-item-label">{{ __('reports.document_inventory.digital') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-item-number">{{ $stats['both'] }}</div>
            <div class="summary-item-label">{{ __('reports.document_inventory.both') }}</div>
        </div>
    </div>
</div>

@if($documents->isEmpty())
    <div class="no-data">{{ __('reports.no_data_available') }}</div>
@else
    <table class="report-table">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('reports.document_inventory.client_name') }}</th>
                <th>{{ __('reports.document_inventory.case_name') }}</th>
                <th>{{ __('reports.document_inventory.document_type') }}</th>
                <th>{{ __('reports.document_inventory.description') }}</th>
                <th>{{ __('reports.document_inventory.location') }}</th>
                <th>{{ __('reports.document_inventory.deposit_date') }}</th>
                <th>{{ __('reports.document_inventory.storage_type') }}</th>
                <th>{{ __('reports.document_inventory.movement_card') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($documents as $index => $document)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $document->client?->client_name_ar ?? $document->client?->client_name_en ?? $document->client_name ?? '—' }}</td>
                    <td>{{ $document->case?->matter_name_ar ?? $document->case?->matter_name_en ?? $document->legacy_matter_name ?? '—' }}</td>
                    <td>{{ $document->document_type ?? '—' }}</td>
                    <td>{{ $document->document_description ?? $document->description ?? '—' }}</td>
                    <td>{{ $document->document_location ?? '—' }}</td>
                    <td>{{ $document->deposit_date?->format('Y-m-d') ?? '—' }}</td>
                    <td>
                        @if($document->document_storage_type)
                            {{ __('reports.document_inventory.storage_type_' . $document->document_storage_type) }}
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @if($document->movement_card)
                            <span class="badge badge-success">{{ __('common.yes') }}</span>
                        @else
                            <span class="badge badge-secondary">{{ __('common.no') }}</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@if($missingDocuments->isNotEmpty())
    <div class="summary-section" style="margin-top: 20px;">
        <div class="summary-title">{{ __('reports.document_inventory.missing_documents') }}</div>
        <table class="report-table compact-table">
            <thead>
                <tr>
                    <th style="width: 50%;">{{ __('reports.document_inventory.case_name') }}</th>
                    <th style="width: 50%;">{{ __('reports.document_inventory.client_name') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($missingDocuments as $missing)
                    <tr>
                        <td>{{ $missing['case'] }}</td>
                        <td>{{ $missing['client'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection

