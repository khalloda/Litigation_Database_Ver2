<!doctype html>
<?php ($isRtl = app()->getLocale() === 'ar'); ?>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" dir="<?php echo e($isRtl ? 'rtl' : 'ltr'); ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(__('app.app_name')); ?></title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/sass/app.scss', 'resources/js/app.js']); ?>

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
    <div id="app" class="<?php echo e($isRtl ? 'text-end' : ''); ?>">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="<?php echo e(url('/')); ?>">
                    <?php echo e(__('app.app_name')); ?>

                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="<?php echo e(__('Toggle navigation')); ?>">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        <?php if(auth()->guard()->check()): ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('clients.view')): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo e(route('clients.index')); ?>">Clients</a></li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('cases.view')): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo e(route('cases.index')); ?>"><?php echo e(__('app.cases')); ?></a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo e(route('courts.index')); ?>"><?php echo e(__('app.courts')); ?></a></li>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('hearings.view')): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo e(route('hearings.index')); ?>"><?php echo e(__('app.hearings')); ?></a></li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\EngagementLetter::class)): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo e(route('engagement-letters.index')); ?>"><?php echo e(__('app.engagement_letters')); ?></a></li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Contact::class)): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo e(route('contacts.index')); ?>"><?php echo e(__('app.contacts')); ?></a></li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\PowerOfAttorney::class)): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo e(route('power-of-attorneys.index')); ?>"><?php echo e(__('app.power_of_attorneys')); ?></a></li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('documents.view')): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo e(route('documents.index')); ?>"><?php echo e(__('app.documents')); ?></a></li>
                        <?php endif; ?>

                        <!-- Admin Dropdown -->
                        <?php if(auth()->user()->hasAnyPermission(['admin.users.manage', 'viewAny', 'admin.audit.view']) ||
                        auth()->user()->can('viewAny', App\Models\AdminTask::class) ||
                        auth()->user()->can('viewAny', App\Models\AdminSubtask::class) ||
                        auth()->user()->can('viewAny', App\Models\ImportSession::class) ||
                        auth()->user()->can('viewAny', App\Models\OptionSet::class)): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo e(__('app.admin')); ?>

                            </a>
                            <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('admin.users.manage')): ?>
                                <li><a class="dropdown-item" href="<?php echo e(route('lawyers.index')); ?>"><?php echo e(__('app.lawyers')); ?></a></li>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\AdminTask::class)): ?>
                                <li><a class="dropdown-item" href="<?php echo e(route('admin-tasks.index')); ?>"><?php echo e(__('app.admin_tasks')); ?></a></li>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\AdminSubtask::class)): ?>
                                <li><a class="dropdown-item" href="<?php echo e(route('admin-subtasks.index')); ?>"><?php echo e(__('app.admin_subtasks')); ?></a></li>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\ImportSession::class)): ?>
                                <li><a class="dropdown-item" href="<?php echo e(route('import.index')); ?>"><?php echo e(__('app.import_export')); ?></a></li>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\OptionSet::class)): ?>
                                <li><a class="dropdown-item" href="<?php echo e(route('admin.options.index')); ?>"><?php echo e(__('app.option_sets')); ?></a></li>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('admin.audit.view')): ?>
                                <li><a class="dropdown-item" href="<?php echo e(route('audit-logs.index')); ?>"><?php echo e(__('app.audit_logs')); ?></a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="<?php echo e(route('data-quality.index')); ?>"><?php echo e(__('app.data_quality')); ?></a></li>
                            </ul>
                        </li>
                        <?php endif; ?>
                        <?php endif; ?>
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto align-items-center">
                        <li class="nav-item dropdown me-2">
                            <a id="localeDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo e(__('app.language')); ?>

                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="localeDropdown">
                                <li><a class="dropdown-item" href="<?php echo e(route('locale.switch', 'en')); ?>"><?php echo e(__('app.english')); ?></a></li>
                                <li><a class="dropdown-item" href="<?php echo e(route('locale.switch', 'ar')); ?>"><?php echo e(__('app.arabic')); ?></a></li>
                            </ul>
                        </li>
                        <!-- Authentication Links -->
                        <?php if(auth()->guard()->guest()): ?>
                        <?php if(Route::has('login')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo e(route('login')); ?>"><?php echo e(__('Login')); ?></a>
                        </li>
                        <?php endif; ?>

                        <?php if(Route::has('register')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo e(route('register')); ?>"><?php echo e(__('Register')); ?></a>
                        </li>
                        <?php endif; ?>
                        <?php else: ?>
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                <?php echo e(Auth::user()->name); ?>

                            </a>

                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="<?php echo e(route('logout')); ?>"
                                    onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                    <?php echo e(__('Logout')); ?>

                                </a>

                                <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="d-none">
                                    <?php echo csrf_field(); ?>
                                </form>
                            </div>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>

    <!-- Additional Scripts Stack -->
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html>
<?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/layouts/app.blade.php ENDPATH**/ ?>