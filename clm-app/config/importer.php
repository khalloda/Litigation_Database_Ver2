<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Database Backup Settings
    |--------------------------------------------------------------------------
    |
    | Configure automatic backup behavior before imports.
    |
    */

    'backup' => [
        'enabled' => env('IMPORT_BACKUP_ENABLED', true),
        'driver' => env('IMPORT_BACKUP_DRIVER', 'auto'), // auto|mysqldump|php
        'path' => storage_path('app/backups'),
        'max_age_days' => env('IMPORT_BACKUP_MAX_AGE_DAYS', 30),
        'compress' => env('IMPORT_BACKUP_COMPRESS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Import Processing Limits
    |--------------------------------------------------------------------------
    */

    'limits' => [
        'max_upload_mb' => env('IMPORT_MAX_UPLOAD_MB', 10),
        'chunk_rows' => env('IMPORT_CHUNK_ROWS', 2000),
        'timeout_seconds' => env('IMPORT_TIMEOUT_SECONDS', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Enabled Import Tables
    |--------------------------------------------------------------------------
    |
    | Define which database tables can be imported into.
    |
    */

    'enabled_tables' => [
        'clients',
        'cases',
        'contacts',
        'lawyers',
        'hearings',
        'engagement_letters',
        'power_of_attorneys',
        'admin_tasks',
        'admin_subtasks',
        'clients_matters_documents',
    ],

    /*
    |--------------------------------------------------------------------------
    | Column Mapping Strategies
    |--------------------------------------------------------------------------
    |
    | Define how source columns are mapped to database columns.
    |
    */

    'mapping' => [
        // Similarity threshold for auto-mapping (0.0 to 1.0)
        'similarity_threshold' => 0.65,

        // Weight for different similarity algorithms
        'weights' => [
            'exact_match' => 1.0,
            'levenshtein' => 0.4,
            'jaro_winkler' => 0.6,
        ],

        // Common transformations
        'transforms' => [
            'trim' => 'Trim whitespace',
            'uppercase' => 'Convert to uppercase',
            'lowercase' => 'Convert to lowercase',
            'title_case' => 'Convert to title case',
            'remove_special_chars' => 'Remove special characters',
            'date_dmy' => 'Parse date (DD/MM/YYYY)',
            'date_mdy' => 'Parse date (MM/DD/YYYY)',
            'date_ymd' => 'Parse date (YYYY-MM-DD)',
            'boolean_yn' => 'Convert Y/N to boolean',
            'boolean_10' => 'Convert 1/0 to boolean',
            'decimal_comma' => 'Convert comma decimal to dot',
            'phone_normalize' => 'Normalize phone number',
        ],

        // Default column mappings (source => target)
        'defaults' => [
            'clients' => [
                'id' => 'id', // For ID preservation during import
                'client_code' => 'client_code',
                'client_name_ar' => 'client_name_ar',
                'client_name_en' => 'client_name_en',
                'client_print_name' => 'client_print_name',
                'national_id' => 'national_id',
                'email' => 'email',
                'phone' => 'phone',
                'status' => 'status',
                'cash_or_probono' => 'cash_or_probono',
                'client_start' => 'client_start',
                'client_end' => 'client_end',
                'contact_lawyer' => 'contact_lawyer',
                'logo' => 'logo',
                'power_of_attorney_location' => 'power_of_attorney_location',
                'documents_location' => 'documents_location',
            ],
            'cases' => [
                'case_number' => 'case_number',
                'case_title' => 'case_title',
                'case_type' => 'case_type',
                'court' => 'court',
                'status' => 'status',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    */

    'validation' => [
        // Stop import if error rate exceeds this threshold
        'max_error_rate' => 0.15, // 15%

        // Preflight validation batch size
        'preflight_batch_size' => 500,
    ],

    /*
    |--------------------------------------------------------------------------
    | Foreign Key Resolution
    |--------------------------------------------------------------------------
    |
    | Define how to resolve foreign key references during import.
    |
    */

    'foreign_keys' => [
        'clients' => [
            'lookup_columns' => ['client_code', 'client_name_ar', 'national_id'],
            'create_missing' => false,
        ],
        'lawyers' => [
            'lookup_columns' => ['id'],
            'create_missing' => false,
        ],
        'option_values' => [
            'lookup_columns' => ['label_en', 'label_ar'],
            'create_missing' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    */

    'queue' => [
        'enabled' => env('IMPORT_QUEUE_ENABLED', false),
        'connection' => env('IMPORT_QUEUE_CONNECTION', 'database'),
        'queue' => env('IMPORT_QUEUE_NAME', 'imports'),
    ],

];
