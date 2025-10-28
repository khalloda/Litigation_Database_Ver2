<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enabled Models for Deletion Bundles
    |--------------------------------------------------------------------------
    |
    | Define which models should create deletion bundles when deleted.
    | Set to false to disable bundle creation for a specific model type.
    |
    */

    'enabled_for' => [
        'Client' => true,
        'CaseModel' => true,
        'ClientDocument' => true,
        'Hearing' => true,
        'AdminTask' => true,
        'AdminSubtask' => true,
        'EngagementLetter' => true,
        'PowerOfAttorney' => true,
        'Contact' => true,
        'Lawyer' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Collectors
    |--------------------------------------------------------------------------
    |
    | Map model classes to their collector classes.
    | Collectors are responsible for gathering the right data for each model type.
    |
    */

    'collectors' => [
        App\Models\Client::class => App\Support\DeletionBundles\Collectors\ClientCollector::class,
        App\Models\CaseModel::class => App\Support\DeletionBundles\Collectors\CaseCollector::class,
        App\Models\ClientDocument::class => App\Support\DeletionBundles\Collectors\DocumentCollector::class,
        App\Models\Hearing::class => App\Support\DeletionBundles\Collectors\HearingCollector::class,
        App\Models\AdminTask::class => App\Support\DeletionBundles\Collectors\AdminTaskCollector::class,
        App\Models\AdminSubtask::class => App\Support\DeletionBundles\Collectors\AdminSubtaskCollector::class,
        App\Models\EngagementLetter::class => App\Support\DeletionBundles\Collectors\EngagementLetterCollector::class,
        App\Models\PowerOfAttorney::class => App\Support\DeletionBundles\Collectors\POACollector::class,
        App\Models\Contact::class => App\Support\DeletionBundles\Collectors\ContactCollector::class,
        App\Models\Lawyer::class => App\Support\DeletionBundles\Collectors\LawyerCollector::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Restore Options
    |--------------------------------------------------------------------------
    |
    | Default options for restore operations.
    |
    */

    'restore' => [
        'default_conflict_strategy' => 'skip', // skip|overwrite|new_copy
        'resolve_orphans' => 'skip', // skip|new_copy|fail
    ],

    /*
    |--------------------------------------------------------------------------
    | TTL (Time To Live)
    |--------------------------------------------------------------------------
    |
    | Default number of days before a trashed bundle is eligible for auto-purge.
    | Set to null to disable auto-purge.
    |
    */

    'ttl_days' => 90,

];

