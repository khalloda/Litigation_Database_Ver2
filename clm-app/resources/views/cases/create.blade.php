@extends('layouts.app')

@section('title', __('app.create_case'))

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">{{ __('app.create_case') }}</h1>
        <a href="{{ route('cases.index') }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('cases.store') }}" method="POST">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="client_id" class="form-label">{{ __('app.client') }} *</label>
                        <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" required>
                            <option value="">{{ __('app.select_client') }}</option>
                            @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
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
                        <input type="text" class="form-control @error('matter_status') is-invalid @enderror" id="matter_status" name="matter_status" value="{{ old('matter_status') }}">
                        @error('matter_status')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="matter_name_ar" class="form-label">{{ __('app.matter_name_ar') }}</label>
                        <input type="text" class="form-control @error('matter_name_ar') is-invalid @enderror" id="matter_name_ar" name="matter_name_ar" value="{{ old('matter_name_ar') }}">
                        @error('matter_name_ar')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="matter_name_en" class="form-label">{{ __('app.matter_name_en') }}</label>
                        <input type="text" class="form-control @error('matter_name_en') is-invalid @enderror" id="matter_name_en" name="matter_name_en" value="{{ old('matter_name_en') }}">
                        @error('matter_name_en')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="matter_description" class="form-label">{{ __('app.matter_description') }}</label>
                    <textarea class="form-control @error('matter_description') is-invalid @enderror" id="matter_description" name="matter_description" rows="3">{{ old('matter_description') }}</textarea>
                    @error('matter_description')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="matter_category" class="form-label">{{ __('app.matter_category') }}</label>
                        <input type="text" class="form-control @error('matter_category') is-invalid @enderror" id="matter_category" name="matter_category" value="{{ old('matter_category') }}">
                        @error('matter_category')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="court_id" class="form-label">{{ __('app.matter_court') }}</label>
                        <select class="form-select select2-court @error('court_id') is-invalid @enderror" id="court_id" name="court_id">
                            <option value="">{{ __('app.select_court') }}</option>
                            @foreach($courts as $court)
                            <option value="{{ $court->id }}" {{ old('court_id') == $court->id ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? $court->court_name_ar : $court->court_name_en }}
                            </option>
                            @endforeach
                        </select>
                        @error('court_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Circuit Container -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">{{ __('app.circuit_container') }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="circuit_name_id" class="form-label">{{ __('app.circuit_name') }}</label>
                                        <select class="form-select select2-cascade @error('circuit_name_id') is-invalid @enderror"
                                                id="circuit_name_id" name="circuit_name_id" disabled>
                                            <option value="">{{ __('app.select_court_first') }}</option>
                                            @foreach($circuitNames as $circuitName)
                                            <option value="{{ $circuitName->id }}" {{ old('circuit_name_id') == $circuitName->id ? 'selected' : '' }}>
                                                {{ app()->getLocale() === 'ar' ? $circuitName->label_ar : $circuitName->label_en }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('circuit_name_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="circuit_serial_id" class="form-label">{{ __('app.circuit_serial') }}</label>
                                        <select class="form-select select2-cascade @error('circuit_serial_id') is-invalid @enderror"
                                                id="circuit_serial_id" name="circuit_serial_id" disabled>
                                            <option value="">{{ __('app.select_court_first') }}</option>
                                            @foreach($circuitSerials as $circuitSerial)
                                            <option value="{{ $circuitSerial->id }}" {{ old('circuit_serial_id') == $circuitSerial->id ? 'selected' : '' }}>
                                                {{ app()->getLocale() === 'ar' ? $circuitSerial->label_ar : $circuitSerial->label_en }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('circuit_serial_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="circuit_shift_id" class="form-label">{{ __('app.circuit_shift') }}</label>
                                        <select class="form-select select2-cascade @error('circuit_shift_id') is-invalid @enderror"
                                                id="circuit_shift_id" name="circuit_shift_id" disabled>
                                            <option value="">{{ __('app.select_court_first') }}</option>
                                            @foreach($circuitShifts as $circuitShift)
                                            <option value="{{ $circuitShift->id }}" {{ old('circuit_shift_id') == $circuitShift->id ? 'selected' : '' }}>
                                                {{ app()->getLocale() === 'ar' ? $circuitShift->label_ar : $circuitShift->label_en }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('circuit_shift_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cascading Court Details -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="circuit_secretary" class="form-label">{{ __('app.circuit_secretary') }}</label>
                        <select class="form-select select2-cascade @error('circuit_secretary') is-invalid @enderror"
                                id="circuit_secretary" name="circuit_secretary" disabled>
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
                                id="court_floor" name="court_floor" disabled>
                            <option value="">{{ __('app.select_court_first') }}</option>
                        </select>
                        @error('court_floor')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="court_hall" class="form-label">{{ __('app.court_hall') }}</label>
                        <select class="form-select select2-cascade @error('court_hall') is-invalid @enderror"
                                id="court_hall" name="court_hall" disabled>
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
                        <input type="date" class="form-control @error('matter_start_date') is-invalid @enderror" id="matter_start_date" name="matter_start_date" value="{{ old('matter_start_date') }}">
                        @error('matter_start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="matter_end_date" class="form-label">{{ __('app.matter_end_date') }}</label>
                        <input type="date" class="form-control @error('matter_end_date') is-invalid @enderror" id="matter_end_date" name="matter_end_date" value="{{ old('matter_end_date') }}">
                        @error('matter_end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="matter_asked_amount" class="form-label">{{ __('app.matter_asked_amount') }}</label>
                        <input type="number" step="0.01" class="form-control @error('matter_asked_amount') is-invalid @enderror" id="matter_asked_amount" name="matter_asked_amount" value="{{ old('matter_asked_amount') }}">
                        @error('matter_asked_amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="matter_judged_amount" class="form-label">{{ __('app.matter_judged_amount') }}</label>
                        <input type="number" step="0.01" class="form-control @error('matter_judged_amount') is-invalid @enderror" id="matter_judged_amount" name="matter_judged_amount" value="{{ old('matter_judged_amount') }}">
                        @error('matter_judged_amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes_1" class="form-label">{{ __('app.notes') }}</label>
                    <textarea class="form-control @error('notes_1') is-invalid @enderror" id="notes_1" name="notes_1" rows="2">{{ old('notes_1') }}</textarea>
                    @error('notes_1')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('cases.index') }}" class="btn btn-secondary me-2">{{ __('app.cancel') }}</a>
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

    // Handle court selection change - cascading dropdowns
    $('#court_id').on('change', function() {
        const courtId = $(this).val();

        console.log('Court selected:', courtId);

        if (courtId) {
            // Fetch court details via AJAX
            $.ajax({
                url: `/api/courts/${courtId}/details`,
                method: 'GET',
                success: function(data) {
                    console.log('Court details received:', data);

                    // Enable circuit dropdowns and show all options
                    $('#circuit_name_id, #circuit_serial_id, #circuit_shift_id').prop('disabled', false);
                    
                    // Note: All circuit options are already loaded in the form
                    // The court selection doesn't filter circuit options anymore
                    // Users can select any circuit combination

                    // Populate secretary dropdown with MULTIPLE options
                    $('#circuit_secretary').empty().prop('disabled', false);
                    $('#circuit_secretary').append(new Option('{{ __("app.select_option") }}', ''));
                    if (data.secretaries && data.secretaries.length > 0) {
                        data.secretaries.forEach(function(secretary) {
                            $('#circuit_secretary').append(new Option(secretary.label, secretary.id));
                        });
                    }
                    $('#circuit_secretary').trigger('change');

                    // Populate floor dropdown with MULTIPLE options
                    $('#court_floor').empty().prop('disabled', false);
                    $('#court_floor').append(new Option('{{ __("app.select_option") }}', ''));
                    if (data.floors && data.floors.length > 0) {
                        data.floors.forEach(function(floor) {
                            $('#court_floor').append(new Option(floor.label, floor.id));
                        });
                    }
                    $('#court_floor').trigger('change');

                    // Populate hall dropdown with MULTIPLE options
                    $('#court_hall').empty().prop('disabled', false);
                    $('#court_hall').append(new Option('{{ __("app.select_option") }}', ''));
                    if (data.halls && data.halls.length > 0) {
                        data.halls.forEach(function(hall) {
                            $('#court_hall').append(new Option(hall.label, hall.id));
                        });
                    }
                    $('#court_hall').trigger('change');

                    console.log('Dropdowns populated successfully');
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error, xhr.responseText);
                    alert('{{ __("app.error_loading_court_details") }}');
                }
            });
        } else {
            // Clear and disable all cascading dropdowns
            $('#circuit_name_id, #circuit_serial_id, #circuit_shift_id, #circuit_secretary, #court_floor, #court_hall')
                .empty()
                .append(new Option('{{ __("app.select_court_first") }}', ''))
                .prop('disabled', true)
                .trigger('change');
        }
    });
});
</script>
@endpush
