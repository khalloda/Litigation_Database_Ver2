<!doctype html>
@php($isRtl = app()->getLocale() === 'ar')
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('app.app_name') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <!-- jQuery for Select2 and other plugins -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Custom Select2 Bootstrap 5 Styling -->
    <style>
        .select2-container--bootstrap-5 .select2-selection {
            border: 1px solid #ced4da !important;
            border-radius: 0.375rem !important;
            min-height: 38px !important;
            padding: 0.375rem 0.75rem !important;
        }

        .select2-container--bootstrap-5 .select2-selection:focus {
            border-color: #86b7fe !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple {
            padding: 0.25rem 0.5rem !important;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
            background-color: #e9ecef !important;
            border: 1px solid #ced4da !important;
            border-radius: 0.25rem !important;
            color: #495057 !important;
            margin: 0.125rem 0.25rem 0.125rem 0 !important;
            padding: 0.25rem 0.5rem !important;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove {
            color: #6c757d !important;
            margin-right: 0.25rem !important;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #dc3545 !important;
        }

        .select2-container--bootstrap-5 .select2-dropdown {
            border: 1px solid #ced4da !important;
            border-radius: 0.375rem !important;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .select2-container--bootstrap-5 .select2-results__option {
            padding: 0.5rem 0.75rem !important;
        }

        .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: #0d6efd !important;
            color: white !important;
        }

        .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field {
            border: 1px solid #ced4da !important;
            border-radius: 0.375rem !important;
            padding: 0.375rem 0.75rem !important;
        }
    </style>

    <!-- Emergency fix for giant arrow overlays -->
    <style>
        /* Hide any giant arrow overlays */
        [class*="arrow"]:not(.btn-group):not(.dropdown-toggle),
        [class*="chevron"]:not(.btn-group):not(.dropdown-toggle) {
            max-width: 2rem !important;
            max-height: 2rem !important;
            position: relative !important;
            z-index: 1 !important;
        }

        /* Hide fixed positioned arrows */
        [style*="position: fixed"][class*="arrow"],
        [style*="position: fixed"][class*="chevron"] {
            display: none !important;
        }

        /* Hide any elements with large arrow backgrounds */
        [style*="background-image"][style*="arrow"],
        [style*="background-image"][style*="chevron"] {
            display: none !important;
        }

        /* Target pagination arrows specifically */
        .pagination .page-link {
            position: relative !important;
            z-index: 1 !important;
        }
    </style>
</head>

<body>
    <div id="app" class="{{ $isRtl ? 'text-end' : '' }}">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ __('app.app_name') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        @auth
                        @can('clients.view')
                        <li class="nav-item"><a class="nav-link" href="{{ route('clients.index') }}">Clients</a></li>
                        @endcan
                        @can('cases.view')
                        <li class="nav-item"><a class="nav-link" href="{{ route('cases.index') }}">{{ __('app.cases') }}</a></li>
                        @endcan
                        <li class="nav-item"><a class="nav-link" href="{{ route('courts.index') }}">{{ __('app.courts') }}</a></li>
                        @can('hearings.view')
                        <li class="nav-item"><a class="nav-link" href="{{ route('hearings.index') }}">{{ __('app.hearings') }}</a></li>
                        @endcan
                        @can('viewAny', App\Models\EngagementLetter::class)
                        <li class="nav-item"><a class="nav-link" href="{{ route('engagement-letters.index') }}">{{ __('app.engagement_letters') }}</a></li>
                        @endcan
                        @can('viewAny', App\Models\Contact::class)
                        <li class="nav-item"><a class="nav-link" href="{{ route('contacts.index') }}">{{ __('app.contacts') }}</a></li>
                        @endcan
                        @can('viewAny', App\Models\PowerOfAttorney::class)
                        <li class="nav-item"><a class="nav-link" href="{{ route('power-of-attorneys.index') }}">{{ __('app.power_of_attorneys') }}</a></li>
                        @endcan
                        @can('documents.view')
                        <li class="nav-item"><a class="nav-link" href="{{ route('documents.index') }}">{{ __('app.documents') }}</a></li>
                        @endcan

                        <!-- Admin Dropdown -->
                        @if(auth()->user()->hasAnyPermission(['admin.users.manage', 'viewAny', 'admin.audit.view']) ||
                        auth()->user()->can('viewAny', App\Models\AdminTask::class) ||
                        auth()->user()->can('viewAny', App\Models\AdminSubtask::class) ||
                        auth()->user()->can('viewAny', App\Models\ImportSession::class) ||
                        auth()->user()->can('viewAny', App\Models\OptionSet::class))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ __('app.admin') }}
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                                @can('admin.users.manage')
                                <li><a class="dropdown-item" href="{{ route('lawyers.index') }}">{{ __('app.lawyers') }}</a></li>
                                @endcan
                                @can('viewAny', App\Models\AdminTask::class)
                                <li><a class="dropdown-item" href="{{ route('admin-tasks.index') }}">{{ __('app.admin_tasks') }}</a></li>
                                @endcan
                                @can('viewAny', App\Models\AdminSubtask::class)
                                <li><a class="dropdown-item" href="{{ route('admin-subtasks.index') }}">{{ __('app.admin_subtasks') }}</a></li>
                                @endcan
                                @can('viewAny', App\Models\ImportSession::class)
                                <li><a class="dropdown-item" href="{{ route('import.index') }}">{{ __('app.import_export') }}</a></li>
                                @endcan
                                @can('viewAny', App\Models\OptionSet::class)
                                <li><a class="dropdown-item" href="{{ route('admin.options.index') }}">{{ __('app.option_sets') }}</a></li>
                                @endcan
                                <li><a class="dropdown-item" href="{{ route('opponents.index') }}">{{ __('app.opponents') }}</a></li>
                                @can('admin.audit.view')
                                <li><a class="dropdown-item" href="{{ route('audit-logs.index') }}">{{ __('app.audit_logs') }}</a></li>
                                @endcan
                                <li><a class="dropdown-item" href="{{ route('data-quality.index') }}">{{ __('app.data_quality') }}</a></li>
                            </ul>
                        </li>
                        @endif
                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto align-items-center">
                        <li class="nav-item dropdown me-2">
                            <a id="localeDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ __('app.language') }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="localeDropdown">
                                <li><a class="dropdown-item" href="{{ route('locale.switch', 'en') }}">{{ __('app.english') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('locale.switch', 'ar') }}">{{ __('app.arabic') }}</a></li>
                            </ul>
                        </li>
                        <!-- Authentication Links -->
                        @guest
                        @if (Route::has('login'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                        </li>
                        @endif

                        @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                        </li>
                        @endif
                        @else
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ Auth::user()->name }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>

    <!-- Additional Scripts Stack -->
    @stack('scripts')
</body>

</html>
