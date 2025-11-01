@extends('layouts.app')

@section('title', __('app.case_details'))

@section('content')
<div class="container-fluid" data-case-id="{{ $case->id }}">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4">{{ __('app.case_details') }}</h1>
        <div>
            <a href="{{ route('cases.index') }}" class="btn btn-outline-secondary me-2">{{ __('app.back_to_cases') }}</a>
            @can('cases.edit')
            <a href="{{ route('cases.edit', $case) }}" class="btn btn-primary me-2">{{ __('app.edit_case') }}</a>
            @endcan
            @can('cases.delete')
            <form action="{{ route('cases.destroy', $case) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete_case') }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">{{ __('app.delete') }}</button>
            </form>
            @endcan
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Accordion for all screen sizes --}}
    <div class="accordion mb-3" id="caseAccordion">
        {{-- Overview Section --}}
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading-overview">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-overview" aria-expanded="true" aria-controls="collapse-overview">
                    {{ __('app.overview') }}
                </button>
            </h2>
            <div id="collapse-overview" class="accordion-collapse collapse show" aria-labelledby="heading-overview" data-bs-parent="#caseAccordion">
                <div class="accordion-body">
                    @include('cases.partials._section_content', ['section' => 'overview'])
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
            <div id="collapse-parties" class="accordion-collapse collapse" aria-labelledby="heading-parties" data-bs-parent="#caseAccordion">
                <div class="accordion-body">
                    @include('cases.partials._section_content', ['section' => 'parties'])
                    {{-- Opponents Section --}}
                    <div class="mt-4">
                        @include('cases.partials._opponents')
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
            <div id="collapse-court" class="accordion-collapse collapse" aria-labelledby="heading-court" data-bs-parent="#caseAccordion">
                <div class="accordion-body">
                    @include('cases.partials._section_content', ['section' => 'court'])
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
            <div id="collapse-status" class="accordion-collapse collapse" aria-labelledby="heading-status" data-bs-parent="#caseAccordion">
                <div class="accordion-body">
                    @include('cases.partials._section_content', ['section' => 'status'])
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
            <div id="collapse-financials" class="accordion-collapse collapse" aria-labelledby="heading-financials" data-bs-parent="#caseAccordion">
                <div class="accordion-body">
                    @include('cases.partials._section_content', ['section' => 'financials'])
                </div>
            </div>
        </div>

        {{-- Documents & Hearings Section --}}
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading-documents">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-documents" aria-expanded="false" aria-controls="collapse-documents">
                    {{ __('app.documents_hearings') }}
                </button>
            </h2>
            <div id="collapse-documents" class="accordion-collapse collapse" aria-labelledby="heading-documents" data-bs-parent="#caseAccordion">
                <div class="accordion-body">
                    @include('cases.partials._documents_hearings_content')
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
            <div id="collapse-meta" class="accordion-collapse collapse" aria-labelledby="heading-meta" data-bs-parent="#caseAccordion">
                <div class="accordion-body">
                    @include('cases.partials._section_content', ['section' => 'meta'])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/case-opponents.js') }}"></script>
@endpush
