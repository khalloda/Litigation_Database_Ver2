@extends('layouts.app')

@section('title', __('app.case_opponents_import'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4 class="mb-0">{{ __('app.case_opponents_import') }}</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        {{ __('app.case_opponents_import_info') }}
                    </div>

                    <form action="{{ route('case-opponents.import.process-upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="file" class="form-label">{{ __('app.select_file') }}</label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror"
                                   id="file" name="file" accept=".csv,.xlsx,.xls" required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> {{ __('app.upload_file') }}
                            </button>
                            <a href="{{ route('import.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> {{ __('app.back_to_imports') }}
                            </a>
                        </div>
                    </form>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>{{ __('app.download_templates') }}</h5>
                            <p class="text-muted">{{ __('app.case_opponents_template_info') }}</p>

                            <div class="d-grid gap-2">
                                <a href="{{ route('case-opponents.template.csv') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-file-csv"></i> {{ __('app.download_csv_template') }}
                                </a>
                                <a href="{{ route('case-opponents.template.xlsx') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-file-excel"></i> {{ __('app.download_xlsx_template') }}
                                </a>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h5>{{ __('app.import_instructions') }}</h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> {{ __('app.case_opponents_instruction_1') }}</li>
                                <li><i class="fas fa-check text-success"></i> {{ __('app.case_opponents_instruction_2') }}</li>
                                <li><i class="fas fa-check text-success"></i> {{ __('app.case_opponents_instruction_3') }}</li>
                                <li><i class="fas fa-check text-success"></i> {{ __('app.case_opponents_instruction_4') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
