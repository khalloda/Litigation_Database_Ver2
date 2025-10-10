@extends('layouts.app')

@section('title', __('app.create_client'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">{{ __('app.create_client') }}</h1>
        <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">← {{ __('app.back') }}</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('clients.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Basic Information --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">{{ __('app.basic_information') }}</h5>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">{{ __('app.client_name_ar') }} <span class="text-danger">*</span></label>
                        <input type="text" name="client_name_ar" class="form-control @error('client_name_ar') is-invalid @enderror" value="{{ old('client_name_ar') }}" required>
                        @error('client_name_ar')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('app.client_name_en') }}</label>
                        <input type="text" name="client_name_en" class="form-control @error('client_name_en') is-invalid @enderror" value="{{ old('client_name_en') }}">
                        @error('client_name_en')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('app.client_print_name') }}</label>
                        <input type="text" name="client_print_name" class="form-control @error('client_print_name') is-invalid @enderror" value="{{ old('client_print_name') }}">
                        <small class="text-muted">{{ __('app.defaults_to_english_name') }}</small>
                        @error('client_print_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">{{ __('app.cash_or_probono') }}</label>
                        <select name="cash_or_probono_id" class="form-select @error('cash_or_probono_id') is-invalid @enderror">
                            <option value="">{{ __('app.select_option') }}</option>
                            @foreach($cashOrProbonoOptions as $option)
                            <option value="{{ $option->id }}" {{ old('cash_or_probono_id') == $option->id ? 'selected' : '' }}>
                                {{ $option->label }}
                            </option>
                            @endforeach
                        </select>
                        @error('cash_or_probono_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('app.status') }}</label>
                        <select name="status_id" class="form-select @error('status_id') is-invalid @enderror">
                            <option value="">{{ __('app.select_option') }}</option>
                            @foreach($statusOptions as $option)
                            <option value="{{ $option->id }}" {{ old('status_id') == $option->id ? 'selected' : '' }}>
                                {{ $option->label }}
                            </option>
                            @endforeach
                        </select>
                        @error('status_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('app.client_start') }}</label>
                        <input type="date" name="client_start" class="form-control @error('client_start') is-invalid @enderror" value="{{ old('client_start') }}">
                        @error('client_start')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('app.client_end') }}</label>
                        <input type="date" name="client_end" class="form-control @error('client_end') is-invalid @enderror" value="{{ old('client_end') }}">
                        @error('client_end')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('app.contact_lawyer') }}</label>
                        <select name="contact_lawyer_id" class="form-select @error('contact_lawyer_id') is-invalid @enderror">
                            <option value="">{{ __('app.select_lawyer') }}</option>
                            @foreach($lawyers as $lawyer)
                            <option value="{{ $lawyer->id }}" {{ old('contact_lawyer_id') == $lawyer->id ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? $lawyer->lawyer_name_ar : $lawyer->lawyer_name_en }}
                            </option>
                            @endforeach
                        </select>
                        @error('contact_lawyer_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('app.logo') }}</label>
                        <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*">
                        <small class="text-muted">{{ __('app.supported_formats') }}: JPG, PNG, GIF (max 2MB)</small>
                        @error('logo')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Document Locations --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">{{ __('app.document_locations') }}</h5>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('app.power_of_attorney_location') }}</label>
                        <select name="power_of_attorney_location_id" class="form-select @error('power_of_attorney_location_id') is-invalid @enderror">
                            <option value="">{{ __('app.select_option') }}</option>
                            @foreach($powerOfAttorneyLocationOptions as $option)
                            <option value="{{ $option->id }}" {{ old('power_of_attorney_location_id') == $option->id ? 'selected' : '' }}>
                                {{ $option->label }}
                            </option>
                            @endforeach
                        </select>
                        @error('power_of_attorney_location_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('app.documents_location') }}</label>
                        <select name="documents_location_id" class="form-select @error('documents_location_id') is-invalid @enderror">
                            <option value="">{{ __('app.select_option') }}</option>
                            @foreach($documentsLocationOptions as $option)
                            <option value="{{ $option->id }}" {{ old('documents_location_id') == $option->id ? 'selected' : '' }}>
                                {{ $option->label }}
                            </option>
                            @endforeach
                        </select>
                        @error('documents_location_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-3 d-flex justify-content-end gap-2">
                    <a href="{{ route('clients.index') }}" class="btn btn-secondary">{{ __('app.cancel') }}</a>
                    <button type="submit" class="btn btn-primary">{{ __('app.create_client') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
