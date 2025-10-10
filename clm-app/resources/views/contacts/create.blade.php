@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ __('app.create_contact') }}</h3>
                    <a href="{{ route('contacts.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> {{ __('app.back_to_list') }}
                    </a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('contacts.store') }}">
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
                                    <label for="contact_name" class="form-label">{{ __('app.contact_name') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('contact_name') is-invalid @enderror" 
                                           id="contact_name" name="contact_name" 
                                           value="{{ old('contact_name') }}" required>
                                    @error('contact_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contact_type" class="form-label">{{ __('app.contact_type') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('contact_type') is-invalid @enderror" id="contact_type" name="contact_type" required>
                                        <option value="">{{ __('app.select_contact_type') }}</option>
                                        <option value="phone" {{ old('contact_type') == 'phone' ? 'selected' : '' }}>{{ __('app.phone') }}</option>
                                        <option value="mobile" {{ old('contact_type') == 'mobile' ? 'selected' : '' }}>{{ __('app.mobile') }}</option>
                                        <option value="email" {{ old('contact_type') == 'email' ? 'selected' : '' }}>{{ __('app.email') }}</option>
                                        <option value="fax" {{ old('contact_type') == 'fax' ? 'selected' : '' }}>{{ __('app.fax') }}</option>
                                        <option value="website" {{ old('contact_type') == 'website' ? 'selected' : '' }}>{{ __('app.website') }}</option>
                                        <option value="address" {{ old('contact_type') == 'address' ? 'selected' : '' }}>{{ __('app.address') }}</option>
                                        <option value="other" {{ old('contact_type') == 'other' ? 'selected' : '' }}>{{ __('app.other') }}</option>
                                    </select>
                                    @error('contact_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contact_value" class="form-label">{{ __('app.contact_value') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('contact_value') is-invalid @enderror" 
                                           id="contact_value" name="contact_value" 
                                           value="{{ old('contact_value') }}" required>
                                    @error('contact_value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_primary" name="is_primary" value="1" 
                                               {{ old('is_primary') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_primary">
                                            {{ __('app.primary_contact') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('contacts.index') }}" class="btn btn-secondary">
                                {{ __('app.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ __('app.create_contact') }}
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
    // Add input formatting based on contact type
    const contactTypeSelect = document.getElementById('contact_type');
    const contactValueInput = document.getElementById('contact_value');
    
    contactTypeSelect.addEventListener('change', function() {
        const type = this.value;
        
        // Set placeholder and input type based on contact type
        switch(type) {
            case 'email':
                contactValueInput.type = 'email';
                contactValueInput.placeholder = 'example@domain.com';
                break;
            case 'phone':
            case 'mobile':
            case 'fax':
                contactValueInput.type = 'tel';
                contactValueInput.placeholder = '+1234567890';
                break;
            case 'website':
                contactValueInput.type = 'url';
                contactValueInput.placeholder = 'https://www.example.com';
                break;
            default:
                contactValueInput.type = 'text';
                contactValueInput.placeholder = '';
                break;
        }
    });
});
</script>
@endsection
