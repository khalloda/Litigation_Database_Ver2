@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">{{ __('app.preflight_validation') }}</h2>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">{{ __('app.validation_summary') }}</h5>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <h2>{{ number_format($session->total_rows) }}</h2>
                    <p class="text-muted">{{ __('app.total_rows') }}</p>
                </div>
                <div class="col-md-3">
                    <h2 class="text-danger">{{ number_format($results['error_count']) }}</h2>
                    <p class="text-muted">{{ __('app.errors') }}</p>
                </div>
                <div class="col-md-3">
                    <h2 class="text-warning">{{ number_format($results['warning_count']) }}</h2>
                    <p class="text-muted">{{ __('app.warnings') }}</p>
                </div>
                <div class="col-md-3">
                    <h2 class="text-{{ $exceedsThreshold ? 'danger' : 'success' }}">
                        {{ number_format((($session->total_rows - $results['error_count']) / $session->total_rows) * 100, 1) }}%
                    </h2>
                    <p class="text-muted">{{ __('app.success_rate') }}</p>
                </div>
            </div>

            @if($exceedsThreshold)
                <div class="alert alert-danger mt-3">
                    <strong><i class="fas fa-exclamation-triangle"></i> {{ __('app.error_threshold_exceeded') }}</strong>
                    <p class="mb-0">{{ __('app.error_threshold_message') }}</p>
                </div>
            @else
                <div class="alert alert-success mt-3">
                    <i class="fas fa-check-circle"></i> {{ __('app.validation_passed') }}
                </div>
            @endif

            @if(isset($appliedProfile) && $appliedProfile)
                <div class="alert alert-info mt-3">
                    <i class="fas fa-magic"></i>
                    {{ __('app.profile_applied') }}: <strong>{{ $appliedProfile->name }}</strong>
                    @if(isset($profileSummary))
                        — {{ __('app.hits') }}: {{ $profileSummary['hits'] ?? 0 }}, {{ __('app.misses') }}: {{ $profileSummary['misses'] ?? 0 }}
                    @endif
                </div>
            @endif
        </div>
    </div>

    @php
        $visibleErrors = array_values(array_filter($results['errors'] ?? [], function ($e) {
            return !(isset($e['resolved']) && $e['resolved'] === true);
        }));
    @endphp
    @if(!empty($visibleErrors))
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">{{ __('app.validation_errors') }} ({{ count($visibleErrors) }})</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>{{ __('app.row') }}</th>
                                <th>{{ __('app.column') }}</th>
                                <th>{{ __('app.value') }}</th>
                                <th>{{ __('app.error') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_slice($visibleErrors, 0, 50) as $error)
                                <tr class="fuzzy-error-row"
                                    data-field="{{ $error['column'] }}"
                                    data-value="{{ $error['value'] ?? '' }}"
                                    data-row="{{ $error['row'] }}"
                                    style="cursor: pointer;">
                                    <td>{{ $error['row'] }}</td>
                                    <td><code>{{ $error['column'] }}</code></td>
                                    <td>{{ Str::limit($error['value'] ?? 'NULL', 30) }}</td>
                                    <td>
                                        {{ $error['message'] }}
                                        @if(isset($error['suggestions']) && !empty($error['suggestions']))
                                            <br><small class="text-info">
                                                <i class="fas fa-lightbulb"></i> {{ __('app.suggestions') }}: {{ implode(', ', $error['suggestions']) }}
                                            </small>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if(count($visibleErrors) > 50)
                        <p class="text-muted">{{ __('app.showing_first_errors', ['count' => 50]) }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <a href="{{ route('import.map', $session) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> {{ __('app.back_to_mapping') }}
                </a>
                @if(!$exceedsThreshold)
                    <form id="preflight-run-form" action="{{ route('import.run', $session) }}" method="POST" class="w-100">
                        @csrf
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" value="1" id="rememberDecisions" name="remember_decisions">
                            <label class="form-check-label" for="rememberDecisions">
                                {{ __('app.remember_resolutions_next_time') }}
                            </label>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" value="1" id="saveAsProfile" name="save_as_profile">
                            <label class="form-check-label" for="saveAsProfile">
                                {{ __('app.save_as_named_profile') }}
                            </label>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="profile_name" placeholder="{{ __('app.profile_name_optional') }}">
                            </div>
                        </div>
                        @if(isset($session) && $session->table_name === 'cases')
                            <hr>
                            <h5 class="mb-3">@lang('app.opponent_suggestions')</h5>
                            @include('import.partials.opponent_fuzzy', [
                                'rows' => $parsed['rows'] ?? [],
                                'opponentSuggestions' => $opponentSuggestions ?? []
                            ])
                        @endif
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary btn-lg" onclick="return confirm('{{ __('app.confirm_start_import') }}')">
                                <i class="fas fa-play"></i> {{ __('app.start_import') }}
                            </button>
                        </div>
                    </form>
                @endif
                <form action="{{ route('import.save-choices', $session) }}" method="POST" class="ms-3">
                    @csrf
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="rememberDecisionsNow" name="remember_decisions" checked>
                        <label class="form-check-label" for="rememberDecisionsNow">
                            {{ __('app.remember_resolutions_next_time') }}
                        </label>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" value="1" id="saveAsProfileNow" name="save_as_profile">
                        <label class="form-check-label" for="saveAsProfileNow">
                            {{ __('app.save_as_named_profile') }}
                        </label>
                    </div>
                    <div class="mt-2">
                        <input type="text" class="form-control" name="profile_name" placeholder="{{ __('app.profile_name_optional') }}">
                    </div>
                    <div class="text-end mt-2">
                        <button type="submit" class="btn btn-outline-success">
                            <i class="fas fa-save"></i> {{ __('app.save_choices_now') }}
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

{{-- Include Fuzzy Matching Modal --}}
@include('import.partials._fuzzy-matching-modal')

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Debug: Check if Bootstrap is loaded
    console.log('Bootstrap available:', typeof bootstrap !== 'undefined');
    console.log('jQuery available:', typeof $ !== 'undefined');

    // Add click handlers to fuzzy error rows
    document.querySelectorAll('.fuzzy-error-row').forEach(row => {
        row.addEventListener('click', function() {
            const field = this.dataset.field;
            const value = this.dataset.value;
            const rowNum = this.dataset.row;

            console.log('Clicked fuzzy error row:', { field, value, rowNum });

            // Check if this is a field that supports fuzzy matching
            const fuzzyFields = [
                'matter_partner_id', 'circuit_secretary', 'court_id',
                'client_capacity_id', 'opponent_capacity_id', 'circuit_name_id'
            ];

            if (fuzzyFields.includes(field) && value && !value.match(/^\d+$/)) {
                console.log('Opening fuzzy matching modal for:', field, value);
                // Open fuzzy matching modal
                if (typeof window.initFuzzyMatchingModal === 'function') {
                    window.initFuzzyMatchingModal(field, value, {{ $session->id }});
                } else {
                    console.error('initFuzzyMatchingModal function not found');
                    alert('Fuzzy matching modal not available. Please refresh the page.');
                }
            } else {
                // Show info message for non-fuzzy fields
                alert('{{ __("app.field_does_not_support_fuzzy_matching") }}: ' + field);
            }
        });

        // Add hover effect
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8f9fa';
        });

        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });

    // Add refresh function for validation results
    window.refreshValidationResults = function() {
        console.log('refreshValidationResults called - reloading page...');
        // Add a small delay to ensure the modal closes first
        setTimeout(function() {
            location.reload();
        }, 100);
    };
});
</script>
@endsection

