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
                        @can('hearings.view')
                        <li class="nav-item"><a class="nav-link" href="{{ route('hearings.index') }}">{{ __('app.hearings') }}</a></li>
                        @endcan
                        @can('viewAny', App\Models\EngagementLetter::class)
                        <li class="nav-item"><a class="nav-link" href="{{ route('engagement-letters.index') }}">{{ __('app.engagement_letters') }}</a></li>
                        @endcan
                        @can('viewAny', App\Models\Contact::class)
                        <li class="nav-item"><a class="nav-link" href="{{ route('contacts.index') }}">{{ __('app.contacts') }}</a></li>
                        @endcan
                        @can('admin.users.manage')
                        <li class="nav-item"><a class="nav-link" href="{{ route('lawyers.index') }}">{{ __('app.lawyers') }}</a></li>
                        @endcan
                        @can('documents.view')
                        <li class="nav-item"><a class="nav-link" href="{{ route('documents.index') }}">{{ __('app.documents') }}</a></li>
                        @endcan
                        @can('admin.audit.view')
                        <li class="nav-item"><a class="nav-link" href="{{ route('audit-logs.index') }}">Audit Logs</a></li>
                        @endcan
                        <li class="nav-item"><a class="nav-link" href="{{ route('data-quality.index') }}">Data Quality</a></li>
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
</body>

</html>
