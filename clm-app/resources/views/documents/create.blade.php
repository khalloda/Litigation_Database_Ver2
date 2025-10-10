@extends('layouts.app')

@section('title', __('app.upload_document'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">📤 {{ __('app.upload_document') }}</h1>
                <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
                    ← {{ __('app.back_to_documents') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">📄 {{ __('app.document_upload_form') }}</h5>
                </div>
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

                    <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Document Storage Type --}}
                        <div class="mb-4">
                            <label for="document_storage_type" class="form-label">{{ __('app.document_storage_type') }} <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="document_storage_type" id="physical" value="physical" checked>
                                <label class="form-check-label" for="physical">
                                    <strong>{{ __('app.storage_type_physical') }}</strong> - {{ __('app.physical_document') }}
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="document_storage_type" id="digital" value="digital">
                                <label class="form-check-label" for="digital">
                                    <strong>{{ __('app.storage_type_digital') }}</strong> - {{ __('app.digital_document') }}
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="document_storage_type" id="both" value="both">
                                <label class="form-check-label" for="both">
                                    <strong>{{ __('app.storage_type_both') }}</strong> - {{ __('app.both_types') }}
                                </label>
                            </div>
                            @error('document_storage_type')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- File Upload (Conditional) --}}
                        <div class="mb-4" id="file-upload-section">
                            <label for="document" class="form-label">{{ __('app.document') }} {{ __('app.file') }} <span class="text-danger" id="file-required">*</span></label>
                            <input type="file" class="form-control @error('document') is-invalid @enderror"
                                id="document" name="document" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif">
                            <div class="form-text">
                                <strong>{{ __('app.supported_formats') }}:</strong> PDF, Word, Excel, PowerPoint, Text, Images<br>
                                <strong>{{ __('app.max_file_size') }}:</strong> 10MB<br>
                                <span id="file-optional-text" class="text-muted d-none">{{ __('app.file_upload_optional') }}</span>
                            </div>
                            @error('document')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Basic Information --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="client_id" class="form-label">{{ __('app.client') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" required>
                                    <option value="">{{ __('app.select_client') }}</option>
                                    @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->client_name_ar }} ({{ $client->client_name_en }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('client_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="matter_id" class="form-label">{{ __('app.case') }}</label>
                                <select class="form-select @error('matter_id') is-invalid @enderror" id="matter_id" name="matter_id">
                                    <option value="">{{ __('app.select_case') }}</option>
                                    @foreach($cases as $case)
                                    <option value="{{ $case->id }}" {{ old('matter_id') == $case->id ? 'selected' : '' }}>
                                        {{ $case->matter_name_ar }} ({{ $case->matter_name_en }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('matter_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="document_type" class="form-label">{{ __('app.document_type') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('document_type') is-invalid @enderror"
                                    id="document_type" name="document_type" value="{{ old('document_type') }}" required>
                                @error('document_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="case_number" class="form-label">{{ __('app.case_number') }}</label>
                                <input type="text" class="form-control @error('case_number') is-invalid @enderror"
                                    id="case_number" name="case_number" value="{{ old('case_number') }}">
                                @error('case_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Document Description --}}
                        <div class="mb-4">
                            <label for="description" class="form-label">{{ __('app.description') }}</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Physical Document Fields --}}
                        <div class="mb-4" id="physical-fields">
                            <h6 class="border-bottom pb-2 mb-3">📄 {{ __('app.physical_document') }} {{ __('app.details') }}</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="pages_count" class="form-label">{{ __('app.pages_count') }}</label>
                                    <input type="number" class="form-control @error('pages_count') is-invalid @enderror"
                                        id="pages_count" name="pages_count" value="{{ old('pages_count') }}" min="0">
                                    @error('pages_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="document_date" class="form-label">{{ __('app.document_date') }}</label>
                                    <input type="date" class="form-control @error('document_date') is-invalid @enderror"
                                        id="document_date" name="document_date" value="{{ old('document_date') }}">
                                    @error('document_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="deposit_date" class="form-label">{{ __('app.deposit_date') }}</label>
                                    <input type="date" class="form-control @error('deposit_date') is-invalid @enderror"
                                        id="deposit_date" name="deposit_date" value="{{ old('deposit_date') }}">
                                    @error('deposit_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="responsible_lawyer" class="form-label">{{ __('app.responsible_lawyer') }}</label>
                                    <input type="text" class="form-control @error('responsible_lawyer') is-invalid @enderror"
                                        id="responsible_lawyer" name="responsible_lawyer" value="{{ old('responsible_lawyer') }}">
                                    @error('responsible_lawyer')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="movement_card" name="movement_card" value="1" {{ old('movement_card') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="movement_card">
                                            {{ __('app.movement_card') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- M-Files Integration --}}
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2 mb-3">🔗 {{ __('app.mfiles_integration') }}</h6>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="mfiles_uploaded" name="mfiles_uploaded" value="1" {{ old('mfiles_uploaded') ? 'checked' : '' }}>
                                <label class="form-check-label" for="mfiles_uploaded">
                                    {{ __('app.mfiles_uploaded') }}
                                </label>
                            </div>
                            <div class="mb-3" id="mfiles-id-section" style="display: none;">
                                <label for="mfiles_id" class="form-label">{{ __('app.mfiles_id') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('mfiles_id') is-invalid @enderror"
                                    id="mfiles_id" name="mfiles_id" value="{{ old('mfiles_id') }}">
                                <div class="form-text">{{ __('app.mfiles_id_required') }}</div>
                                @error('mfiles_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="mb-4">
                            <label for="notes" class="form-label">{{ __('app.notes') }}</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Submit Button --}}
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
                                {{ __('app.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> {{ __('app.save') }} {{ __('app.document') }}
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
        const storageTypeRadios = document.querySelectorAll('input[name="document_storage_type"]');
        const fileUploadSection = document.getElementById('file-upload-section');
        const fileRequired = document.getElementById('file-required');
        const fileOptionalText = document.getElementById('file-optional-text');
        const mfilesCheckbox = document.getElementById('mfiles_uploaded');
        const mfilesIdSection = document.getElementById('mfiles-id-section');
        const mfilesIdInput = document.getElementById('mfiles_id');

        function updateFileUploadRequirement() {
            const selectedType = document.querySelector('input[name="document_storage_type"]:checked').value;

            if (selectedType === 'physical') {
                fileRequired.style.display = 'none';
                fileOptionalText.classList.remove('d-none');
                fileUploadSection.querySelector('input[type="file"]').removeAttribute('required');
            } else {
                fileRequired.style.display = 'inline';
                fileOptionalText.classList.add('d-none');
                fileUploadSection.querySelector('input[type="file"]').setAttribute('required', 'required');
            }
        }

        function updateMfilesRequirement() {
            if (mfilesCheckbox.checked) {
                mfilesIdSection.style.display = 'block';
                mfilesIdInput.setAttribute('required', 'required');
            } else {
                mfilesIdSection.style.display = 'none';
                mfilesIdInput.removeAttribute('required');
                mfilesIdInput.value = '';
            }
        }

        // Add event listeners
        storageTypeRadios.forEach(radio => {
            radio.addEventListener('change', updateFileUploadRequirement);
        });

        mfilesCheckbox.addEventListener('change', updateMfilesRequirement);

        // Initialize on page load
        updateFileUploadRequirement();
        updateMfilesRequirement();
    });
</script>
@endsection
