@extends('layouts.app')

@section('title', 'Data Quality Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">📊 Data Quality Dashboard</h1>
                <div>
                    <a href="#" class="btn btn-sm btn-outline-primary" onclick="window.print(); return false;">
                        🖨️ Print
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Record Counts --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">📊 Record Counts</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3 col-sm-6">
                            <div class="border rounded p-3 text-center">
                                <h6 class="text-muted small mb-1">Lawyers</h6>
                                <h3 class="mb-0">{{ number_format($counts['lawyers']) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="border rounded p-3 text-center">
                                <h6 class="text-muted small mb-1">Clients</h6>
                                <h3 class="mb-0">{{ number_format($counts['clients']) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="border rounded p-3 text-center">
                                <h6 class="text-muted small mb-1">Cases</h6>
                                <h3 class="mb-0">{{ number_format($counts['cases']) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="border rounded p-3 text-center">
                                <h6 class="text-muted small mb-1">Hearings</h6>
                                <h3 class="mb-0">{{ number_format($counts['hearings']) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="border rounded p-3 text-center">
                                <h6 class="text-muted small mb-1">Admin Tasks</h6>
                                <h3 class="mb-0">{{ number_format($counts['admin_tasks']) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="border rounded p-3 text-center">
                                <h6 class="text-muted small mb-1">Admin Subtasks</h6>
                                <h3 class="mb-0">{{ number_format($counts['admin_subtasks']) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="border rounded p-3 text-center">
                                <h6 class="text-muted small mb-1">Documents</h6>
                                <h3 class="mb-0">{{ number_format($counts['documents']) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="border rounded p-3 text-center">
                                <h6 class="text-muted small mb-1">Contacts</h6>
                                <h3 class="mb-0">{{ number_format($counts['contacts']) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="border rounded p-3 text-center">
                                <h6 class="text-muted small mb-1">Power of Attorneys</h6>
                                <h3 class="mb-0">{{ number_format($counts['power_of_attorneys']) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="border rounded p-3 text-center">
                                <h6 class="text-muted small mb-1">Engagement Letters</h6>
                                <h3 class="mb-0">{{ number_format($counts['engagement_letters']) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="bg-success bg-opacity-10 border border-success rounded p-3 text-center">
                                <h6 class="text-success small mb-1">TOTAL RECORDS</h6>
                                <h2 class="mb-0 text-success">{{ number_format($counts['total']) }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Referential Integrity --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">🔗 Referential Integrity</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Relationship</th>
                                    <th>Status</th>
                                    <th>Valid</th>
                                    <th>Total</th>
                                    <th>Orphans</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($integrity as $key => $item)
                                <tr>
                                    <td>{{ ucwords(str_replace('_', ' → ', $key)) }}</td>
                                    <td>
                                        @if($item['percentage'] >= 95)
                                        <span class="badge bg-success">✓ Excellent</span>
                                        @elseif($item['percentage'] >= 50)
                                        <span class="badge bg-warning">! Needs Review</span>
                                        @else
                                        <span class="badge bg-danger">✗ Critical</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($item['valid']) }}</td>
                                    <td>{{ number_format($item['total']) }}</td>
                                    <td>
                                        @if($item['orphans'] > 0)
                                        <span class="badge bg-warning">{{ number_format($item['orphans']) }}</span>
                                        @else
                                        <span class="text-success">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar {{ $item['percentage'] >= 95 ? 'bg-success' : ($item['percentage'] >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                                role="progressbar"
                                                style="width: {{ $item['percentage'] }}%">
                                                {{ $item['percentage'] }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Completeness --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">✅ Data Completeness (Key Fields)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Field</th>
                                    <th>Status</th>
                                    <th>Filled</th>
                                    <th>Total</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($completeness as $key => $item)
                                <tr>
                                    <td>{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                                    <td>
                                        @if($item['percentage'] >= 90)
                                        <span class="badge bg-success">✓ Excellent</span>
                                        @elseif($item['percentage'] >= 50)
                                        <span class="badge bg-warning">! Needs Review</span>
                                        @else
                                        <span class="badge bg-danger">✗ Critical</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($item['filled']) }}</td>
                                    <td>{{ number_format($item['total']) }}</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar {{ $item['percentage'] >= 90 ? 'bg-success' : ($item['percentage'] >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                                role="progressbar"
                                                style="width: {{ $item['percentage'] }}%">
                                                {{ $item['percentage'] }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Relationship Statistics & Top Clients --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">📈 Relationship Statistics</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Avg. cases per client
                            <span class="badge bg-primary rounded-pill">{{ $stats['avg_cases_per_client'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Avg. hearings per case
                            <span class="badge bg-primary rounded-pill">{{ $stats['avg_hearings_per_case'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Avg. tasks per case
                            <span class="badge bg-primary rounded-pill">{{ $stats['avg_tasks_per_case'] }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">🏆 Top 10 Clients by Case Count</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Client Name</th>
                                    <th class="text-end">Cases</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topClients as $index => $client)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $client->client_name_ar ?? $client->client_name_en }}</td>
                                    <td class="text-end">
                                        <span class="badge bg-success">{{ number_format($client->cases_count) }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer Info --}}
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <strong>ℹ️ About This Dashboard:</strong> This dashboard provides real-time data quality metrics for the Central Litigation Management system.
                Metrics are calculated on-demand from the live database.
                <br><small class="text-muted">Last refreshed: {{ now()->format('Y-m-d H:i:s') }}</small>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {

        .btn,
        nav,
        .alert {
            display: none !important;
        }
    }
</style>
@endsection

