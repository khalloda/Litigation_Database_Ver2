@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ __('app.create_power_of_attorney') }}</h3>
                    <a href="{{ route('power-of-attorneys.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> {{ __('app.back_to_list') }}
                    </a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('power-of-attorneys.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="client_id" class="form-label">{{ __('app.client') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" required>
                                        <option value="">{{ __('app.select_client') }}</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                                {{ $client->client_name_ar }} {{ $client->client_name_en ? '- ' . $client->client_name_en : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('client_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="poa_number" class="form-label">{{ __('app.poa_number') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('poa_number') is-invalid @enderror" 
                                           id="poa_number" name="poa_number" 
                                           value="{{ old('poa_number') }}" required>
                                    @error('poa_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="poa_type" class="form-label">{{ __('app.poa_type') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('poa_type') is-invalid @enderror" id="poa_type" name="poa_type" required>
                                        <option value="">{{ __('app.select_poa_type') }}</option>
                                        <option value="General" {{ old('poa_type') == 'General' ? 'selected' : '' }}>{{ __('app.general_poa') }}</option>
                                        <option value="Special" {{ old('poa_type') == 'Special' ? 'selected' : '' }}>{{ __('app.special_poa') }}</option>
                                        <option value="Limited" {{ old('poa_type') == 'Limited' ? 'selected' : '' }}>{{ __('app.limited_poa') }}</option>
                                        <option value="Durable" {{ old('poa_type') == 'Durable' ? 'selected' : '' }}>{{ __('app.durable_poa') }}</option>
                                        <option value="Healthcare" {{ old('poa_type') == 'Healthcare' ? 'selected' : '' }}>{{ __('app.healthcare_poa') }}</option>
                                        <option value="Financial" {{ old('poa_type') == 'Financial' ? 'selected' : '' }}>{{ __('app.financial_poa') }}</option>
                                        <option value="Legal" {{ old('poa_type') == 'Legal' ? 'selected' : '' }}>{{ __('app.legal_poa') }}</option>
                                        <option value="Other" {{ old('poa_type') == 'Other' ? 'selected' : '' }}>{{ __('app.other') }}</option>
                                    </select>
                                    @error('poa_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="issue_date" class="form-label">{{ __('app.issue_date') }} <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('issue_date') is-invalid @enderror" 
                                           id="issue_date" name="issue_date" 
                                           value="{{ old('issue_date') }}" required>
                                    @error('issue_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="expiry_date" class="form-label">{{ __('app.expiry_date') }} <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                           id="expiry_date" name="expiry_date" 
                                           value="{{ old('expiry_date') }}" required>
                                    @error('expiry_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            {{ __('app.active') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('power-of-attorneys.index') }}" class="btn btn-secondary">
                                {{ __('app.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ __('app.create_power_of_attorney') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-fill expiry date when issue date changes (default to 1 year)
    const issueDateInput = document.getElementById('issue_date');
    const expiryDateInput = document.getElementById('expiry_date');
    
    issueDateInput.addEventListener('change', function() {
        if (this.value && !expiryDateInput.value) {
            const issueDate = new Date(this.value);
            const expiryDate = new Date(issueDate);
            expiryDate.setFullYear(expiryDate.getFullYear() + 1);
            expiryDateInput.value = expiryDate.toISOString().split('T')[0];
        }
    });
});
</script>
@endsection
