@extends('layouts.app')

@section('title', __('app.edit_court'))

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">{{ __('app.edit_court') }}</h1>
        <a href="{{ route('courts.show', $court) }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('courts.update', $court) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="court_name_ar" class="form-label">{{ __('app.court_name_ar') }} *</label>
                        <input type="text" class="form-control @error('court_name_ar') is-invalid @enderror"
                               id="court_name_ar" name="court_name_ar" value="{{ old('court_name_ar', $court->court_name_ar) }}">
                        @error('court_name_ar')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="court_name_en" class="form-label">{{ __('app.court_name_en') }} *</label>
                        <input type="text" class="form-control @error('court_name_en') is-invalid @enderror"
                               id="court_name_en" name="court_name_en" value="{{ old('court_name_en', $court->court_name_en) }}">
                        @error('court_name_en')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Circuit Rows Container -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">{{ __('app.court_circuits') }}</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="add-circuit-row">
                                <i class="fas fa-plus"></i> {{ __('app.add_circuit') }}
                            </button>
                        </div>
                        
                        <div id="circuit-rows-container">
                            @if($court->circuits->count() > 0)
                                @foreach($court->circuits as $index => $circuit)
                                <div class="circuit-row card border-secondary mb-2">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="form-label">{{ __('app.circuit_name') }}</label>
                                                <select class="form-select circuit-name-select" name="court_circuits[{{ $index }}][name_id]">
                                                    <option value="">{{ __('app.select_circuit_name') }}</option>
                                                    @foreach($circuitNames as $circuitName)
                                                    <option value="{{ $circuitName->id }}" {{ $circuit->circuit_name_id == $circuitName->id ? 'selected' : '' }}>
                                                        {{ app()->getLocale() === 'ar' ? $circuitName->label_ar : $circuitName->label_en }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">{{ __('app.circuit_serial') }}</label>
                                                <select class="form-select circuit-serial-select" name="court_circuits[{{ $index }}][serial_id]">
                                                    <option value="">{{ __('app.select_circuit_serial') }}</option>
                                                    @foreach($circuitSerials as $circuitSerial)
                                                    <option value="{{ $circuitSerial->id }}" {{ $circuit->circuit_serial_id == $circuitSerial->id ? 'selected' : '' }}>
                                                        {{ app()->getLocale() === 'ar' ? $circuitSerial->label_ar : $circuitSerial->label_en }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">{{ __('app.circuit_shift') }}</label>
                                                <select class="form-select circuit-shift-select" name="court_circuits[{{ $index }}][shift_id]">
                                                    <option value="">{{ __('app.select_circuit_shift') }}</option>
                                                    @foreach($circuitShifts as $circuitShift)
                                                    <option value="{{ $circuitShift->id }}" {{ $circuit->circuit_shift_id == $circuitShift->id ? 'selected' : '' }}>
                                                        {{ app()->getLocale() === 'ar' ? $circuitShift->label_ar : $circuitShift->label_en }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3 d-flex align-items-end">
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-circuit-row">
                                                    <i class="fas fa-trash"></i> {{ __('app.remove_circuit') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <!-- Default empty row -->
                                <div class="circuit-row card border-secondary mb-2">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="form-label">{{ __('app.circuit_name') }}</label>
                                                <select class="form-select circuit-name-select" name="court_circuits[0][name_id]">
                                                    <option value="">{{ __('app.select_circuit_name') }}</option>
                                                    @foreach($circuitNames as $circuitName)
                                                    <option value="{{ $circuitName->id }}">
                                                        {{ app()->getLocale() === 'ar' ? $circuitName->label_ar : $circuitName->label_en }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">{{ __('app.circuit_serial') }}</label>
                                                <select class="form-select circuit-serial-select" name="court_circuits[0][serial_id]">
                                                    <option value="">{{ __('app.select_circuit_serial') }}</option>
                                                    @foreach($circuitSerials as $circuitSerial)
                                                    <option value="{{ $circuitSerial->id }}">
                                                        {{ app()->getLocale() === 'ar' ? $circuitSerial->label_ar : $circuitSerial->label_en }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">{{ __('app.circuit_shift') }}</label>
                                                <select class="form-select circuit-shift-select" name="court_circuits[0][shift_id]">
                                                    <option value="">{{ __('app.select_circuit_shift') }}</option>
                                                    @foreach($circuitShifts as $circuitShift)
                                                    <option value="{{ $circuitShift->id }}">
                                                        {{ app()->getLocale() === 'ar' ? $circuitShift->label_ar : $circuitShift->label_en }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3 d-flex align-items-end">
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-circuit-row">
                                                    <i class="fas fa-trash"></i> {{ __('app.remove_circuit') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        @error('court_circuits')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="court_secretaries" class="form-label">{{ __('app.court_secretaries') }}</label>
                        <select class="form-select select2-multi @error('court_secretaries') is-invalid @enderror" 
                                id="court_secretaries" name="court_secretaries[]" multiple>
                            @foreach($secretaryOptions as $option)
                            <option value="{{ $option->id }}" 
                                {{ in_array($option->id, old('court_secretaries', $court->secretaries->pluck('id')->toArray())) ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? $option->label_ar : $option->label_en }}
                            </option>
                            @endforeach
                        </select>
                        @error('court_secretaries')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="court_floors" class="form-label">{{ __('app.court_floors') }}</label>
                        <select class="form-select select2-multi @error('court_floors') is-invalid @enderror" 
                                id="court_floors" name="court_floors[]" multiple>
                            @foreach($floorOptions as $option)
                            <option value="{{ $option->id }}" 
                                {{ in_array($option->id, old('court_floors', $court->floors->pluck('id')->toArray())) ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? $option->label_ar : $option->label_en }}
                            </option>
                            @endforeach
                        </select>
                        @error('court_floors')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="court_halls" class="form-label">{{ __('app.court_halls') }}</label>
                        <select class="form-select select2-multi @error('court_halls') is-invalid @enderror" 
                                id="court_halls" name="court_halls[]" multiple>
                            @foreach($hallOptions as $option)
                            <option value="{{ $option->id }}" 
                                {{ in_array($option->id, old('court_halls', $court->halls->pluck('id')->toArray())) ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? $option->label_ar : $option->label_en }}
                            </option>
                            @endforeach
                        </select>
                        @error('court_halls')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                               value="1" {{ old('is_active', $court->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            {{ __('app.active') }}
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('courts.show', $court) }}" class="btn btn-secondary me-2">{{ __('app.cancel') }}</a>
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
    $('.select2-multi').select2({
        theme: 'bootstrap-5',
        multiple: true,
        allowClear: true,
        placeholder: '{{ __("app.select_multiple") }}',
        width: '100%'
    });

    let circuitRowIndex = {{ $court->circuits->count() }};

    // Add circuit row
    $('#add-circuit-row').on('click', function() {
        const template = `
            <div class="circuit-row card border-secondary mb-2">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">{{ __('app.circuit_name') }}</label>
                            <select class="form-select circuit-name-select" name="court_circuits[${circuitRowIndex}][name_id]">
                                <option value="">{{ __('app.select_circuit_name') }}</option>
                                @foreach($circuitNames as $circuitName)
                                <option value="{{ $circuitName->id }}">
                                    {{ app()->getLocale() === 'ar' ? $circuitName->label_ar : $circuitName->label_en }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('app.circuit_serial') }}</label>
                            <select class="form-select circuit-serial-select" name="court_circuits[${circuitRowIndex}][serial_id]">
                                <option value="">{{ __('app.select_circuit_serial') }}</option>
                                @foreach($circuitSerials as $circuitSerial)
                                <option value="{{ $circuitSerial->id }}">
                                    {{ app()->getLocale() === 'ar' ? $circuitSerial->label_ar : $circuitSerial->label_en }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('app.circuit_shift') }}</label>
                            <select class="form-select circuit-shift-select" name="court_circuits[${circuitRowIndex}][shift_id]">
                                <option value="">{{ __('app.select_circuit_shift') }}</option>
                                @foreach($circuitShifts as $circuitShift)
                                <option value="{{ $circuitShift->id }}">
                                    {{ app()->getLocale() === 'ar' ? $circuitShift->label_ar : $circuitShift->label_en }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-circuit-row">
                                <i class="fas fa-trash"></i> {{ __('app.remove_circuit') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#circuit-rows-container').append(template);
        circuitRowIndex++;
    });

    // Remove circuit row
    $(document).on('click', '.remove-circuit-row', function() {
        $(this).closest('.circuit-row').remove();
    });
});
</script>
@endpush

