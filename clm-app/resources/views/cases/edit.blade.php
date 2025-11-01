@extends('layouts.app')

@section('title', __('app.edit_case'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">{{ __('app.edit_case') }}</h1>
        <a href="{{ route('cases.show', $case) }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('cases.update', $case) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="accordion" id="editCaseAccordion">
                    {{-- Overview Section --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-overview">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-overview" aria-expanded="true" aria-controls="collapse-overview">
                                {{ __('app.overview') }}
                            </button>
                        </h2>
                        <div id="collapse-overview" class="accordion-collapse collapse show" aria-labelledby="heading-overview" data-bs-parent="#editCaseAccordion">
                            <div class="accordion-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="matter_name_ar" class="form-label">{{ __('app.matter_name_ar') }}</label>
                                        <input type="text" class="form-control @error('matter_name_ar') is-invalid @enderror" id="matter_name_ar" name="matter_name_ar" value="{{ old('matter_name_ar', $case->matter_name_ar) }}" dir="auto">
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
                                    <div class="col-md-12">
                                        <label for="matter_description" class="form-label">{{ __('app.matter_description') }}</label>
                                        <textarea class="form-control @error('matter_description') is-invalid @enderror" id="matter_description" name="matter_description" rows="3" dir="auto">{{ old('matter_description', $case->matter_description) }}</textarea>
                                        @error('matter_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Parties Section --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-parties">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-parties" aria-expanded="false" aria-controls="collapse-parties">
                                {{ __('app.parties') }}
                            </button>
                        </h2>
                        <div id="collapse-parties" class="accordion-collapse collapse" aria-labelledby="heading-parties" data-bs-parent="#editCaseAccordion">
                            <div class="accordion-body">
                                <div class="row mb-3">
                                    <div class="col-md-4">
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
                                    <div class="col-md-4">
                                        <label for="client_in_case_name" class="form-label">{{ __('app.client_in_case_name') }}</label>
                                        <input type="text" class="form-control @error('client_in_case_name') is-invalid @enderror" id="client_in_case_name" name="client_in_case_name" value="{{ old('client_in_case_name', $case->client_in_case_name) }}" dir="auto">
                                        @error('client_in_case_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="client_capacity_id" class="form-label">{{ __('app.client_capacity') }}</label>
                                        <select class="form-select @error('client_capacity_id') is-invalid @enderror" id="client_capacity_id" name="client_capacity_id">
                                            <option value="">{{ __('app.select_option') }}</option>
                                            @foreach($capacityTypes as $ov)
                                            <option value="{{ $ov->id }}" {{ (old('client_capacity_id', $case->client_capacity_id) == $ov->id) ? 'selected' : '' }}>
                                                {{ app()->getLocale() === 'ar' ? $ov->label_ar : $ov->label_en }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('client_capacity_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="client_capacity_note" class="form-label">{{ __('app.capacity_note') }}</label>
                                        <input type="text" class="form-control @error('client_capacity_note') is-invalid @enderror" id="client_capacity_note" name="client_capacity_note" value="{{ old('client_capacity_note', $case->client_capacity_note) }}" dir="auto">
                                        @error('client_capacity_note')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="lawyer_a" class="form-label">{{ __('app.lawyer_a') }}</label>
                                        <input type="text" class="form-control @error('lawyer_a') is-invalid @enderror" id="lawyer_a" name="lawyer_a" value="{{ old('lawyer_a', $case->lawyer_a) }}" dir="auto">
                                        @error('lawyer_a')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="lawyer_b" class="form-label">{{ __('app.lawyer_b') }}</label>
                                        <input type="text" class="form-control @error('lawyer_b') is-invalid @enderror" id="lawyer_b" name="lawyer_b" value="{{ old('lawyer_b', $case->lawyer_b) }}" dir="auto">
                                        @error('lawyer_b')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="opponent_id" class="form-label">{{ __('app.opponent') }}</label>
                                        <select class="form-select @error('opponent_id') is-invalid @enderror" id="opponent_id" name="opponent_id">
                                            <option value="">{{ __('app.select_option') }}</option>
                                            @foreach($opponents as $opp)
                                            <option value="{{ $opp->id }}"
                                                    {{ (old('opponent_id', $case->opponent_id) == $opp->id) ? 'selected' : '' }}
                                                    data-arabic-name="{{ $opp->opponent_name_ar }}"
                                                    data-english-name="{{ $opp->opponent_name_en }}">
                                                @if(app()->getLocale() === 'ar')
                                                    {{ $opp->opponent_name_ar }}
                                                @else
                                                    {{ $opp->opponent_name_en ?: $opp->opponent_name_ar }}
                                                @endif
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('opponent_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="opponent_in_case_name" class="form-label">{{ __('app.opponent_in_case_name') }}</label>
                                        <input type="text" class="form-control @error('opponent_in_case_name') is-invalid @enderror" id="opponent_in_case_name" name="opponent_in_case_name" value="{{ old('opponent_in_case_name', $case->opponent_in_case_name) }}" dir="auto">
                                        @error('opponent_in_case_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="opponent_capacity_id" class="form-label">{{ __('app.opponent_capacity') }}</label>
                                        <select class="form-select @error('opponent_capacity_id') is-invalid @enderror" id="opponent_capacity_id" name="opponent_capacity_id">
                                            <option value="">{{ __('app.select_option') }}</option>
                                            @foreach($capacityTypes as $ov)
                                            <option value="{{ $ov->id }}" {{ (old('opponent_capacity_id', $case->opponent_capacity_id) == $ov->id) ? 'selected' : '' }}>
                                                {{ app()->getLocale() === 'ar' ? $ov->label_ar : $ov->label_en }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('opponent_capacity_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="opponent_capacity_note" class="form-label">{{ __('app.capacity_note') }}</label>
                                        <input type="text" class="form-control @error('opponent_capacity_note') is-invalid @enderror" id="opponent_capacity_note" name="opponent_capacity_note" value="{{ old('opponent_capacity_note', $case->opponent_capacity_note) }}" dir="auto">
                                        @error('opponent_capacity_note')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Court & Circuit Section --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-court">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-court" aria-expanded="false" aria-controls="collapse-court">
                                {{ __('app.court_circuit') }}
                            </button>
                        </h2>
                        <div id="collapse-court" class="accordion-collapse collapse" aria-labelledby="heading-court" data-bs-parent="#editCaseAccordion">
                            <div class="accordion-body">
                                <div class="row mb-3">
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
                                    <div class="col-md-6">
                                        <label for="matter_destination_id" class="form-label">{{ __('app.matter_destination') }}</label>
                                        <select class="form-select @error('matter_destination_id') is-invalid @enderror" id="matter_destination_id" name="matter_destination_id">
                                            <option value="">{{ __('app.select_court') }}</option>
                                            @foreach($courts as $court)
                                            <option value="{{ $court->id }}" {{ (old('matter_destination_id', $case->matter_destination_id) == $court->id) ? 'selected' : '' }}>
                                                {{ app()->getLocale() === 'ar' ? $court->court_name_ar : $court->court_name_en }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('matter_destination_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="card border-primary mb-3">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0">{{ __('app.circuit_container') }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="circuit_name_id" class="form-label">{{ __('app.circuit_name') }}</label>
                                                <select class="form-select select2-cascade @error('circuit_name_id') is-invalid @enderror" id="circuit_name_id" name="circuit_name_id" {{ $case->court_id ? '' : 'disabled' }}>
                                                    <option value="">{{ __('app.select_court_first') }}</option>
                                                    @foreach($circuitNames as $circuitName)
                                                    <option value="{{ $circuitName->id }}" {{ (old('circuit_name_id', $case->circuit_name_id) == $circuitName->id) ? 'selected' : '' }}>
                                                        {{ app()->getLocale() === 'ar' ? $circuitName->label_ar : $circuitName->label_en }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                @error('circuit_name_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="circuit_serial_id" class="form-label">{{ __('app.circuit_serial') }}</label>
                                                <select class="form-select select2-cascade @error('circuit_serial_id') is-invalid @enderror" id="circuit_serial_id" name="circuit_serial_id" {{ $case->court_id ? '' : 'disabled' }}>
                                                    <option value="">{{ __('app.select_court_first') }}</option>
                                                    @foreach($circuitSerials as $circuitSerial)
                                                    <option value="{{ $circuitSerial->id }}" {{ (old('circuit_serial_id', $case->circuit_serial_id) == $circuitSerial->id) ? 'selected' : '' }}>
                                                        {{ app()->getLocale() === 'ar' ? $circuitSerial->label_ar : $circuitSerial->label_en }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                @error('circuit_serial_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="circuit_shift_id" class="form-label">{{ __('app.circuit_shift') }}</label>
                                                <select class="form-select select2-cascade @error('circuit_shift_id') is-invalid @enderror" id="circuit_shift_id" name="circuit_shift_id" {{ $case->court_id ? '' : 'disabled' }}>
                                                    <option value="">{{ __('app.select_court_first') }}</option>
                                                    @foreach($circuitShifts as $circuitShift)
                                                    <option value="{{ $circuitShift->id }}" {{ (old('circuit_shift_id', $case->circuit_shift_id) == $circuitShift->id) ? 'selected' : '' }}>
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
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="circuit_secretary" class="form-label">{{ __('app.circuit_secretary') }}</label>
                                        <select class="form-select select2-cascade @error('circuit_secretary') is-invalid @enderror" id="circuit_secretary" name="circuit_secretary" {{ $case->court_id ? '' : 'disabled' }}>
                                            <option value="">{{ __('app.select_court_first') }}</option>
                                        </select>
                                        @error('circuit_secretary')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="court_floor" class="form-label">{{ __('app.court_floor') }}</label>
                                        <select class="form-select select2-cascade @error('court_floor') is-invalid @enderror" id="court_floor" name="court_floor" {{ $case->court_id ? '' : 'disabled' }}>
                                            <option value="">{{ __('app.select_court_first') }}</option>
                                        </select>
                                        @error('court_floor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="court_hall" class="form-label">{{ __('app.court_hall') }}</label>
                                        <select class="form-select select2-cascade @error('court_hall') is-invalid @enderror" id="court_hall" name="court_hall" {{ $case->court_id ? '' : 'disabled' }}>
                                            <option value="">{{ __('app.select_court_first') }}</option>
                                        </select>
                                        @error('court_hall')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Status & Progress Section --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-status">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-status" aria-expanded="false" aria-controls="collapse-status">
                                {{ __('app.status_progress') }}
                            </button>
                        </h2>
                        <div id="collapse-status" class="accordion-collapse collapse" aria-labelledby="heading-status" data-bs-parent="#editCaseAccordion">
                            <div class="accordion-body">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label for="matter_status_id" class="form-label">{{ __('app.matter_status') }}</label>
                                        <select class="form-select @error('matter_status_id') is-invalid @enderror" id="matter_status_id" name="matter_status_id">
                                            <option value="">{{ __('app.select_option') }}</option>
                                            @foreach($caseStatuses as $ov)
                                            <option value="{{ $ov->id }}" {{ (old('matter_status_id', $case->matter_status_id) == $ov->id) ? 'selected' : '' }}>
                                                {{ app()->getLocale() === 'ar' ? $ov->label_ar : $ov->label_en }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('matter_status_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label for="matter_category_id" class="form-label">{{ __('app.matter_category') }}</label>
                                        <select class="form-select @error('matter_category_id') is-invalid @enderror" id="matter_category_id" name="matter_category_id">
                                            <option value="">{{ __('app.select_option') }}</option>
                                            @foreach($caseCategories as $ov)
                                            <option value="{{ $ov->id }}" {{ (old('matter_category_id', $case->matter_category_id) == $ov->id) ? 'selected' : '' }}>
                                                {{ app()->getLocale() === 'ar' ? $ov->label_ar : $ov->label_en }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('matter_category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label for="matter_degree_id" class="form-label">{{ __('app.matter_degree') }}</label>
                                        <select class="form-select @error('matter_degree_id') is-invalid @enderror" id="matter_degree_id" name="matter_degree_id">
                                            <option value="">{{ __('app.select_option') }}</option>
                                            @foreach($caseDegrees as $ov)
                                            <option value="{{ $ov->id }}" {{ (old('matter_degree_id', $case->matter_degree_id) == $ov->id) ? 'selected' : '' }}>
                                                {{ app()->getLocale() === 'ar' ? $ov->label_ar : $ov->label_en }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('matter_degree_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label for="matter_importance_id" class="form-label">{{ __('app.matter_importance') }}</label>
                                        <select class="form-select @error('matter_importance_id') is-invalid @enderror" id="matter_importance_id" name="matter_importance_id">
                                            <option value="">{{ __('app.select_option') }}</option>
                                            @foreach($caseImportance as $ov)
                                            <option value="{{ $ov->id }}" {{ (old('matter_importance_id', $case->matter_importance_id) == $ov->id) ? 'selected' : '' }}>
                                                {{ app()->getLocale() === 'ar' ? $ov->label_ar : $ov->label_en }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('matter_importance_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="current_status" class="form-label">{{ __('app.current_status') }}</label>
                                        <textarea class="form-control @error('current_status') is-invalid @enderror" id="current_status" name="current_status" rows="3" dir="auto">{{ old('current_status', $case->current_status) }}</textarea>
                                        @error('current_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="matter_evaluation" class="form-label">{{ __('app.matter_evaluation') }}</label>
                                        <textarea class="form-control @error('matter_evaluation') is-invalid @enderror" id="matter_evaluation" name="matter_evaluation" rows="3" dir="auto">{{ old('matter_evaluation', $case->matter_evaluation) }}</textarea>
                                        @error('matter_evaluation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Financials Section --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-financials">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-financials" aria-expanded="false" aria-controls="collapse-financials">
                                {{ __('app.financials') }}
                            </button>
                        </h2>
                        <div id="collapse-financials" class="accordion-collapse collapse" aria-labelledby="heading-financials" data-bs-parent="#editCaseAccordion">
                            <div class="accordion-body">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label for="client_type_id" class="form-label">{{ __('app.client_type') }}</label>
                                        <select class="form-select @error('client_type_id') is-invalid @enderror" id="client_type_id" name="client_type_id">
                                            <option value="">{{ __('app.select_option') }}</option>
                                            @php
                                                $clientTypes = \App\Models\OptionValue::whereHas('optionSet', fn($q) => $q->where('key', 'client.cash_or_probono'))->where('is_active', true)->orderBy('id')->get();
                                            @endphp
                                            @foreach($clientTypes as $ov)
                                            <option value="{{ $ov->id }}" {{ (old('client_type_id', $case->client_type_id) == $ov->id) ? 'selected' : '' }}>
                                                {{ app()->getLocale() === 'ar' ? $ov->label_ar : $ov->label_en }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('client_type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label for="matter_asked_amount" class="form-label">{{ __('app.matter_asked_amount') }}</label>
                                        <input type="number" step="0.01" class="form-control @error('matter_asked_amount') is-invalid @enderror" id="matter_asked_amount" name="matter_asked_amount" value="{{ old('matter_asked_amount', $case->matter_asked_amount) }}" dir="ltr">
                                        @error('matter_asked_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label for="matter_judged_amount" class="form-label">{{ __('app.matter_judged_amount') }}</label>
                                        <input type="number" step="0.01" class="form-control @error('matter_judged_amount') is-invalid @enderror" id="matter_judged_amount" name="matter_judged_amount" value="{{ old('matter_judged_amount', $case->matter_judged_amount) }}" dir="ltr">
                                        @error('matter_judged_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label for="fee_letter" class="form-label">{{ __('app.fee_letter') }}</label>
                                        <input type="number" step="0.01" class="form-control @error('fee_letter') is-invalid @enderror" id="fee_letter" name="fee_letter" value="{{ old('fee_letter', $case->fee_letter) }}" dir="ltr">
                                        @error('fee_letter')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="allocated_budget" class="form-label">{{ __('app.allocated_budget') }}</label>
                                        <textarea class="form-control @error('allocated_budget') is-invalid @enderror" id="allocated_budget" name="allocated_budget" rows="2" dir="auto">{{ old('allocated_budget', $case->allocated_budget) }}</textarea>
                                        @error('allocated_budget')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="financial_provision" class="form-label">{{ __('app.financial_provision') }}</label>
                                        <textarea class="form-control @error('financial_provision') is-invalid @enderror" id="financial_provision" name="financial_provision" rows="2" dir="auto">{{ old('financial_provision', $case->financial_provision) }}</textarea>
                                        @error('financial_provision')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="contract_id" class="form-label">{{ __('app.contract') }}</label>
                                        <select class="form-select @error('contract_id') is-invalid @enderror" id="contract_id" name="contract_id">
                                            <option value="">{{ __('app.select_option') }}</option>
                                            @php
                                                $contracts = \App\Models\EngagementLetter::orderBy('contract_date', 'desc')->orderBy('id', 'desc')->get();
                                            @endphp
                                            @foreach($contracts as $contract)
                                            <option value="{{ $contract->id }}" {{ (old('contract_id', $case->contract_id) == $contract->id) ? 'selected' : '' }}>
                                                {{ $contract->client_name ?: 'Client ID: ' . $contract->client_id }} @if($contract->contract_date) ({{ \Carbon\Carbon::parse($contract->contract_date)->format('Y-m-d') }}) @endif
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('contract_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="engagement_letter_no" class="form-label">{{ __('app.engagement_letter_no') }}</label>
                                        <input type="text" class="form-control @error('engagement_letter_no') is-invalid @enderror" id="engagement_letter_no" name="engagement_letter_no" value="{{ old('engagement_letter_no', $case->engagement_letter_no) }}">
                                        @error('engagement_letter_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Meta & Audit Section --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-meta">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-meta" aria-expanded="false" aria-controls="collapse-meta">
                                {{ __('app.meta_audit') }}
                            </button>
                        </h2>
                        <div id="collapse-meta" class="accordion-collapse collapse" aria-labelledby="heading-meta" data-bs-parent="#editCaseAccordion">
                            <div class="accordion-body">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label for="matter_shelf" class="form-label">{{ __('app.matter_shelf') }}</label>
                                        <input type="text" maxlength="10" class="form-control @error('matter_shelf') is-invalid @enderror" id="matter_shelf" name="matter_shelf" value="{{ old('matter_shelf', $case->matter_shelf) }}">
                                        @error('matter_shelf')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label for="matter_branch_id" class="form-label">{{ __('app.client_branch') }}</label>
                                        <select class="form-select @error('matter_branch_id') is-invalid @enderror" id="matter_branch_id" name="matter_branch_id">
                                            <option value="">{{ __('app.select_option') }}</option>
                                            @foreach($caseBranches as $ov)
                                            <option value="{{ $ov->id }}" {{ (old('matter_branch_id', $case->matter_branch_id) == $ov->id) ? 'selected' : '' }}>
                                                {{ app()->getLocale() === 'ar' ? $ov->label_ar : $ov->label_en }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('matter_branch_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label for="client_branch" class="form-label">{{ __('app.client_branch') }} (text)</label>
                                        <input type="text" class="form-control @error('client_branch') is-invalid @enderror" id="client_branch" name="client_branch" value="{{ old('client_branch', $case->client_branch) }}" dir="auto">
                                        @error('client_branch')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label for="matter_partner_id" class="form-label">{{ __('app.matter_partner') }}</label>
                                        <select class="form-select @error('matter_partner_id') is-invalid @enderror" id="matter_partner_id" name="matter_partner_id">
                                            <option value="">{{ __('app.select_option') }}</option>
                                            @foreach($partnerLawyers as $lawyer)
                                            <option value="{{ $lawyer->id }}" {{ (old('matter_partner_id', $case->matter_partner_id) == $lawyer->id) ? 'selected' : '' }}>
                                                {{ $lawyer->lawyer_name_ar ?? $lawyer->lawyer_name_en }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('matter_partner_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="legal_opinion" class="form-label">{{ __('app.legal_opinion') }}</label>
                                        <textarea class="form-control @error('legal_opinion') is-invalid @enderror" id="legal_opinion" name="legal_opinion" rows="3" dir="auto">{{ old('legal_opinion', $case->legal_opinion) }}</textarea>
                                        @error('legal_opinion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="notes_1" class="form-label">{{ __('app.notes') }} 1</label>
                                        <textarea class="form-control @error('notes_1') is-invalid @enderror" id="notes_1" name="notes_1" rows="3" dir="auto">{{ old('notes_1', $case->notes_1) }}</textarea>
                                        @error('notes_1')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="notes_2" class="form-label">{{ __('app.notes') }} 2</label>
                                        <textarea class="form-control @error('notes_2') is-invalid @enderror" id="notes_2" name="notes_2" rows="3" dir="auto">{{ old('notes_2', $case->notes_2) }}</textarea>
                                        @error('notes_2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
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

    // Initialize Select2 for opponent dropdown
    const opponentSelect = $('#opponent_id');
    if (opponentSelect.length > 0) {
        opponentSelect.select2({
            theme: 'bootstrap-5',
            placeholder: '{{ __("app.select_option") }}',
            allowClear: true,
            width: '100%',
            dropdownParent: $('body'),
            templateResult: function(data) {
                if (!data.id) return data.text;
                var displayText = data.text || data.element.getAttribute('data-arabic-name') || 'Unknown';
                return $('<span style="color: #212529;">' + displayText + '</span>');
            },
            templateSelection: function(data) {
                if (!data.id) return data.text;
                var displayText = data.text || data.element.getAttribute('data-arabic-name') || 'Unknown';
                return $('<span style="color: #212529;">' + displayText + '</span>');
            }
        });
        opponentSelect.trigger('change');
        setTimeout(function() {
            $('.select2-container .select2-selection__rendered').css('color', '#212529');
            $('.select2-dropdown .select2-results__option').css('color', '#212529');
        }, 100);
    }

    // Load existing court details on page load if court is selected
    const initialCourtId = $('#court_id').val();
    if (initialCourtId) {
        loadCourtDetails(initialCourtId, {
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
            $('#circuit_name_id, #circuit_serial_id, #circuit_shift_id, #circuit_secretary, #court_floor, #court_hall')
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
                $('#circuit_name_id, #circuit_serial_id, #circuit_shift_id').prop('disabled', false);

                $('#circuit_secretary').empty().prop('disabled', false);
                $('#circuit_secretary').append(new Option('{{ __("app.select_option") }}', ''));
                if (data.secretaries && data.secretaries.length > 0) {
                    data.secretaries.forEach(function(secretary) {
                        const isSelected = selectedValues.secretary == secretary.id;
                        $('#circuit_secretary').append(new Option(secretary.label, secretary.id, isSelected, isSelected));
                    });
                }
                $('#circuit_secretary').trigger('change');

                $('#court_floor').empty().prop('disabled', false);
                $('#court_floor').append(new Option('{{ __("app.select_option") }}', ''));
                if (data.floors && data.floors.length > 0) {
                    data.floors.forEach(function(floor) {
                        const isSelected = selectedValues.floor == floor.id;
                        $('#court_floor').append(new Option(floor.label, floor.id, isSelected, isSelected));
                    });
                }
                $('#court_floor').trigger('change');

                $('#court_hall').empty().prop('disabled', false);
                $('#court_hall').append(new Option('{{ __("app.select_option") }}', ''));
                if (data.halls && data.halls.length > 0) {
                    data.halls.forEach(function(hall) {
                        const isSelected = selectedValues.hall == hall.id;
                        $('#court_hall').append(new Option(hall.label, hall.id, isSelected, isSelected));
                    });
                }
                $('#court_hall').trigger('change');
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error, xhr.responseText);
                alert('{{ __("app.error_loading_court_details") }}');
            }
        });
    }
});
</script>
@endpush
