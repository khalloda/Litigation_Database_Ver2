@extends('layouts.app')

@section('title', __('app.edit_case'))

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">{{ __('app.edit_case') }}</h1>
        <a href="{{ route('cases.show', $case) }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('cases.update', $case) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="client_id" class="form-label">{{ __('app.client') }} *</label>
                        <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" required>
                            <option value="">{{ __('app.select_client') }}</option>
                            @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ (old('client_id', $case->client_id) == $client->id) ? 'selected' : '' }}>
                                {{ $client->client_name_ar ?? $client->client_name_en }} (ID: {{ $client->id }})
                            </option>
                            @endforeach
                        </select>
                        @error('client_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="matter_status" class="form-label">{{ __('app.matter_status') }}</label>
                        <input type="text" class="form-control @error('matter_status') is-invalid @enderror" id="matter_status" name="matter_status" value="{{ old('matter_status', $case->matter_status) }}">
                        @error('matter_status')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="matter_name_ar" class="form-label">{{ __('app.matter_name_ar') }}</label>
                        <input type="text" class="form-control @error('matter_name_ar') is-invalid @enderror" id="matter_name_ar" name="matter_name_ar" value="{{ old('matter_name_ar', $case->matter_name_ar) }}">
                        @error('matter_name_ar')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="matter_name_en" class="form-label">{{ __('app.matter_name_en') }}</label>
                        <input type="text" class="form-control @error('matter_name_en') is-invalid @enderror" id="matter_name_en" name="matter_name_en" value="{{ old('matter_name_en', $case->matter_name_en) }}">
                        @error('matter_name_en')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="matter_description" class="form-label">{{ __('app.matter_description') }}</label>
                    <textarea class="form-control @error('matter_description') is-invalid @enderror" id="matter_description" name="matter_description" rows="3">{{ old('matter_description', $case->matter_description) }}</textarea>
                    @error('matter_description')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="matter_category" class="form-label">{{ __('app.matter_category') }}</label>
                        <input type="text" class="form-control @error('matter_category') is-invalid @enderror" id="matter_category" name="matter_category" value="{{ old('matter_category', $case->matter_category) }}">
                        @error('matter_category')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="court_id" class="form-label">{{ __('app.matter_court') }}</label>
                        <select class="form-select select2-court @error('court_id') is-invalid @enderror" id="court_id" name="court_id">
                            <option value="">{{ __('app.select_court') }}</option>
                            @foreach($courts as $court)
                            <option value="{{ $court->id }}" {{ (old('court_id', $case->court_id) == $court->id) ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? $court->court_name_ar : $court->court_name_en }}
                            </option>
                            @endforeach
                        </select>
                        @error('court_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Cascading Court Details -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="matter_circuit" class="form-label">{{ __('app.matter_circuit') }}</label>
                        <select class="form-select select2-cascade @error('matter_circuit') is-invalid @enderror" 
                                id="matter_circuit" name="matter_circuit" {{ $case->court_id ? '' : 'disabled' }}>
                            <option value="">{{ __('app.select_court_first') }}</option>
                        </select>
                        @error('matter_circuit')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="circuit_secretary" class="form-label">{{ __('app.circuit_secretary') }}</label>
                        <select class="form-select select2-cascade @error('circuit_secretary') is-invalid @enderror" 
                                id="circuit_secretary" name="circuit_secretary" {{ $case->court_id ? '' : 'disabled' }}>
                            <option value="">{{ __('app.select_court_first') }}</option>
                        </select>
                        @error('circuit_secretary')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="court_floor" class="form-label">{{ __('app.court_floor') }}</label>
                        <select class="form-select select2-cascade @error('court_floor') is-invalid @enderror" 
                                id="court_floor" name="court_floor" {{ $case->court_id ? '' : 'disabled' }}>
                            <option value="">{{ __('app.select_court_first') }}</option>
                        </select>
                        @error('court_floor')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="court_hall" class="form-label">{{ __('app.court_hall') }}</label>
                        <select class="form-select select2-cascade @error('court_hall') is-invalid @enderror" 
                                id="court_hall" name="court_hall" {{ $case->court_id ? '' : 'disabled' }}>
                            <option value="">{{ __('app.select_court_first') }}</option>
                        </select>
                        @error('court_hall')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="matter_start_date" class="form-label">{{ __('app.matter_start_date') }}</label>
                        <input type="date" class="form-control @error('matter_start_date') is-invalid @enderror" id="matter_start_date" name="matter_start_date" value="{{ old('matter_start_date', $case->matter_start_date?->format('Y-m-d')) }}">
                        @error('matter_start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="matter_end_date" class="form-label">{{ __('app.matter_end_date') }}</label>
                        <input type="date" class="form-control @error('matter_end_date') is-invalid @enderror" id="matter_end_date" name="matter_end_date" value="{{ old('matter_end_date', $case->matter_end_date?->format('Y-m-d')) }}">
                        @error('matter_end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="matter_asked_amount" class="form-label">{{ __('app.matter_asked_amount') }}</label>
                        <input type="number" step="0.01" class="form-control @error('matter_asked_amount') is-invalid @enderror" id="matter_asked_amount" name="matter_asked_amount" value="{{ old('matter_asked_amount', $case->matter_asked_amount) }}">
                        @error('matter_asked_amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="matter_judged_amount" class="form-label">{{ __('app.matter_judged_amount') }}</label>
                        <input type="number" step="0.01" class="form-control @error('matter_judged_amount') is-invalid @enderror" id="matter_judged_amount" name="matter_judged_amount" value="{{ old('matter_judged_amount', $case->matter_judged_amount) }}">
                        @error('matter_judged_amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes_1" class="form-label">{{ __('app.notes') }}</label>
                    <textarea class="form-control @error('notes_1') is-invalid @enderror" id="notes_1" name="notes_1" rows="2">{{ old('notes_1', $case->notes_1) }}</textarea>
                    @error('notes_1')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('cases.show', $case) }}" class="btn btn-secondary me-2">{{ __('app.cancel') }}</a>
                    <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 for court dropdown
    $('.select2-court').select2({
        theme: 'bootstrap-5',
        placeholder: '{{ __("app.select_court") }}',
        allowClear: true,
        width: '100%'
    });

    // Initialize Select2 for cascading dropdowns
    $('.select2-cascade').select2({
        theme: 'bootstrap-5',
        allowClear: true,
        width: '100%'
    });

    // Load existing court details on page load if court is selected
    const initialCourtId = $('#court_id').val();
    if (initialCourtId) {
        loadCourtDetails(initialCourtId, {
            circuit: '{{ old("matter_circuit", $case->matter_circuit) }}',
            secretary: '{{ old("circuit_secretary", $case->circuit_secretary) }}',
            floor: '{{ old("court_floor", $case->court_floor) }}',
            hall: '{{ old("court_hall", $case->court_hall) }}'
        });
    }

    // Handle court selection change - cascading dropdowns
    $('#court_id').on('change', function() {
        const courtId = $(this).val();
        
        if (courtId) {
            loadCourtDetails(courtId);
        } else {
            // Clear and disable all cascading dropdowns
            $('#matter_circuit, #circuit_secretary, #court_floor, #court_hall')
                .empty()
                .append(new Option('{{ __("app.select_court_first") }}', ''))
                .prop('disabled', true)
                .trigger('change');
        }
    });

    function loadCourtDetails(courtId, selectedValues = {}) {
        $.ajax({
            url: `/api/courts/${courtId}/details`,
            method: 'GET',
            success: function(data) {
                // Populate circuit dropdown
                $('#matter_circuit').empty().prop('disabled', false);
                $('#matter_circuit').append(new Option('{{ __("app.select_option") }}', ''));
                if (data.circuit) {
                    const isSelected = selectedValues.circuit == data.circuit.id;
                    $('#matter_circuit').append(new Option(data.circuit.label, data.circuit.id, isSelected, isSelected));
                }

                // Populate secretary dropdown
                $('#circuit_secretary').empty().prop('disabled', false);
                $('#circuit_secretary').append(new Option('{{ __("app.select_option") }}', ''));
                if (data.secretary) {
                    const isSelected = selectedValues.secretary == data.secretary.id;
                    $('#circuit_secretary').append(new Option(data.secretary.label, data.secretary.id, isSelected, isSelected));
                }

                // Populate floor dropdown
                $('#court_floor').empty().prop('disabled', false);
                $('#court_floor').append(new Option('{{ __("app.select_option") }}', ''));
                if (data.floor) {
                    const isSelected = selectedValues.floor == data.floor.id;
                    $('#court_floor').append(new Option(data.floor.label, data.floor.id, isSelected, isSelected));
                }

                // Populate hall dropdown
                $('#court_hall').empty().prop('disabled', false);
                $('#court_hall').append(new Option('{{ __("app.select_option") }}', ''));
                if (data.hall) {
                    const isSelected = selectedValues.hall == data.hall.id;
                    $('#court_hall').append(new Option(data.hall.label, data.hall.id, isSelected, isSelected));
                }

                // Trigger change to refresh Select2
                $('.select2-cascade').trigger('change');
            },
            error: function() {
                alert('{{ __("app.error_loading_court_details") }}');
            }
        });
    }
});
</script>
@endpush

