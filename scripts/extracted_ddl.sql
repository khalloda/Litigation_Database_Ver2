-- activity_log

  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `log_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint UNSIGNED DEFAULT NULL,
  `causer_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` bigint UNSIGNED DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `batch_uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`)
);

-- admin_subtasks

  `id` int NOT NULL,
  `task_id` bigint UNSIGNED NOT NULL,
  `lawyer_id` bigint UNSIGNED DEFAULT NULL,
  `performer` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `next_date` date DEFAULT NULL,
  `result` text COLLATE utf8mb4_unicode_ci,
  `procedure_date` date DEFAULT NULL,
  `report` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_subtasks_task_id_index` (`task_id`),
  KEY `admin_subtasks_lawyer_id_index` (`lawyer_id`),
  KEY `admin_subtasks_next_date_index` (`next_date`),
  KEY `admin_subtasks_created_by_foreign` (`created_by`),
  KEY `admin_subtasks_updated_by_foreign` (`updated_by`)
);

-- admin_tasks

  `id` int NOT NULL,
  `matter_id` bigint UNSIGNED NOT NULL,
  `lawyer_id` bigint UNSIGNED DEFAULT NULL,
  `last_follow_up` text COLLATE utf8mb4_unicode_ci,
  `last_date` date DEFAULT NULL,
  `authority` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `circuit` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `required_work` text COLLATE utf8mb4_unicode_ci,
  `performer` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `previous_decision` text COLLATE utf8mb4_unicode_ci,
  `court` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `result` text COLLATE utf8mb4_unicode_ci,
  `creation_date` datetime DEFAULT NULL,
  `execution_date` datetime DEFAULT NULL,
  `alert` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_tasks_matter_id_status_index` (`matter_id`,`status`),
  KEY `admin_tasks_lawyer_id_index` (`lawyer_id`),
  KEY `admin_tasks_execution_date_index` (`execution_date`),
  KEY `admin_tasks_created_by_foreign` (`created_by`),
  KEY `admin_tasks_updated_by_foreign` (`updated_by`)
);

-- cases

  `id` int NOT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `client_in_case_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `opponent_in_case_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contract_id` bigint UNSIGNED DEFAULT NULL,
  `engagement_letter_no` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matter_name_ar` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `matter_name_en` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `matter_description` text COLLATE utf8mb4_unicode_ci,
  `matter_status` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matter_status_id` bigint UNSIGNED DEFAULT NULL,
  `matter_category` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matter_category_id` bigint UNSIGNED DEFAULT NULL,
  `court_id` bigint UNSIGNED DEFAULT NULL,
  `circuit_name_id` bigint UNSIGNED DEFAULT NULL,
  `circuit_serial_id` bigint UNSIGNED DEFAULT NULL,
  `circuit_shift_id` bigint UNSIGNED DEFAULT '221',
  `matter_circuit_legacy` bigint UNSIGNED DEFAULT NULL,
  `circuit_secretary` bigint UNSIGNED DEFAULT NULL,
  `court_floor` bigint UNSIGNED DEFAULT NULL,
  `court_hall` bigint UNSIGNED DEFAULT NULL,
  `matter_degree` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matter_degree_id` bigint UNSIGNED DEFAULT NULL,
  `matter_court_text` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matter_destination` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matter_destination_id` bigint UNSIGNED DEFAULT NULL,
  `matter_importance` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matter_importance_id` bigint UNSIGNED DEFAULT NULL,
  `matter_evaluation` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matter_start_date` date DEFAULT NULL,
  `matter_end_date` date DEFAULT NULL,
  `matter_asked_amount` decimal(15,2) DEFAULT NULL,
  `matter_judged_amount` decimal(15,2) DEFAULT NULL,
  `matter_shelf` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matter_partner` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matter_partner_id` bigint UNSIGNED DEFAULT NULL,
  `lawyer_a` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lawyer_b` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fee_letter` decimal(15,2) DEFAULT NULL,
  `allocated_budget` text COLLATE utf8mb4_unicode_ci,
  `team_id` int DEFAULT NULL,
  `legal_opinion` text COLLATE utf8mb4_unicode_ci,
  `financial_provision` text COLLATE utf8mb4_unicode_ci,
  `current_status` text COLLATE utf8mb4_unicode_ci,
  `notes_1` text COLLATE utf8mb4_unicode_ci,
  `notes_2` text COLLATE utf8mb4_unicode_ci,
  `client_and_capacity` text COLLATE utf8mb4_unicode_ci,
  `client_capacity_id` bigint UNSIGNED DEFAULT NULL,
  `opponent_and_capacity` text COLLATE utf8mb4_unicode_ci,
  `opponent_id` bigint UNSIGNED DEFAULT NULL,
  `opponent_capacity_id` bigint UNSIGNED DEFAULT NULL,
  `client_branch` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matter_branch_id` bigint UNSIGNED DEFAULT NULL,
  `client_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_type_id` bigint UNSIGNED DEFAULT NULL,
  `matter_select` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `client_capacity_note` text COLLATE utf8mb4_unicode_ci,
  `opponent_capacity_note` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `cases_client_id_matter_status_index` (`client_id`,`matter_status`),
  KEY `cases_matter_name_ar_index` (`matter_name_ar`),
  KEY `cases_matter_name_en_index` (`matter_name_en`),
  KEY `cases_matter_status_index` (`matter_status`),
  KEY `cases_matter_start_date_index` (`matter_start_date`),
  KEY `cases_contract_id_index` (`contract_id`),
  KEY `cases_created_by_foreign` (`created_by`),
  KEY `cases_updated_by_foreign` (`updated_by`),
  KEY `cases_court_id_foreign` (`court_id`),
  KEY `cases_circuit_secretary_foreign` (`circuit_secretary`),
  KEY `cases_court_floor_foreign` (`court_floor`),
  KEY `cases_court_hall_foreign` (`court_hall`),
  KEY `cases_circuit_name_id_index` (`circuit_name_id`),
  KEY `cases_circuit_serial_id_index` (`circuit_serial_id`),
  KEY `cases_circuit_shift_id_index` (`circuit_shift_id`),
  KEY `cases_matter_category_id_foreign` (`matter_category_id`),
  KEY `cases_matter_degree_id_foreign` (`matter_degree_id`),
  KEY `cases_matter_status_id_foreign` (`matter_status_id`),
  KEY `cases_matter_importance_id_foreign` (`matter_importance_id`),
  KEY `cases_matter_branch_id_foreign` (`matter_branch_id`),
  KEY `cases_client_capacity_id_foreign` (`client_capacity_id`),
  KEY `cases_client_type_id_foreign` (`client_type_id`),
  KEY `cases_opponent_capacity_id_foreign` (`opponent_capacity_id`),
  KEY `cases_matter_destination_id_foreign` (`matter_destination_id`),
  KEY `cases_matter_partner_id_foreign` (`matter_partner_id`),
  KEY `cases_opponent_id_foreign` (`opponent_id`)
);

-- clients

  `id` int NOT NULL,
  `mfiles_id` int DEFAULT NULL COMMENT 'MFiles system identifier',
  `client_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Unique client code for identification',
  `client_name_ar` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_name_en` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_print_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT 'Active',
  `cash_or_probono` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_start` date DEFAULT NULL,
  `client_end` date DEFAULT NULL,
  `contact_lawyer` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_lawyer_id` bigint UNSIGNED DEFAULT NULL,
  `logo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `power_of_attorney_location` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `documents_location` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `cash_or_probono_id` bigint UNSIGNED DEFAULT NULL,
  `status_id` bigint UNSIGNED DEFAULT NULL,
  `power_of_attorney_location_id` bigint UNSIGNED DEFAULT NULL,
  `documents_location_id` bigint UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clients_client_code_unique` (`client_code`),
  KEY `clients_client_name_ar_index` (`client_name_ar`),
  KEY `clients_client_name_en_index` (`client_name_en`),
  KEY `clients_status_index` (`status`),
  KEY `clients_client_start_index` (`client_start`),
  KEY `clients_created_by_foreign` (`created_by`),
  KEY `clients_updated_by_foreign` (`updated_by`),
  KEY `clients_cash_or_probono_id_index` (`cash_or_probono_id`),
  KEY `clients_status_id_index` (`status_id`),
  KEY `clients_power_of_attorney_location_id_index` (`power_of_attorney_location_id`),
  KEY `clients_documents_location_id_index` (`documents_location_id`),
  KEY `clients_contact_lawyer_id_index` (`contact_lawyer_id`)
);

-- client_documents

  `id` int NOT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `matter_id` bigint UNSIGNED DEFAULT NULL,
  `client_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` bigint DEFAULT NULL,
  `mime_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_storage_type` enum('physical','digital','both') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'physical',
  `mfiles_uploaded` tinyint(1) NOT NULL DEFAULT '0',
  `mfiles_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsible_lawyer` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `movement_card` tinyint(1) NOT NULL DEFAULT '0',
  `document_description` text COLLATE utf8mb4_unicode_ci,
  `deposit_date` date NOT NULL,
  `document_date` date DEFAULT NULL,
  `case_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pages_count` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_documents_matter_id_foreign` (`matter_id`),
  KEY `client_documents_client_id_matter_id_deposit_date_index` (`client_id`,`matter_id`,`deposit_date`),
  KEY `client_documents_deposit_date_index` (`deposit_date`),
  KEY `client_documents_created_by_foreign` (`created_by`),
  KEY `client_documents_updated_by_foreign` (`updated_by`),
  KEY `client_documents_document_type_index` (`document_type`),
  KEY `client_documents_mime_type_index` (`mime_type`)
);

-- contacts

  `id` int NOT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `contact_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `full_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `job_title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `home_phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile_phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fax_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `web_page` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attachments` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contacts_client_id_index` (`client_id`),
  KEY `contacts_email_index` (`email`),
  KEY `contacts_created_by_foreign` (`created_by`),
  KEY `contacts_updated_by_foreign` (`updated_by`)
);

-- courts

  `id` int NOT NULL,
  `court_name_ar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `court_name_en` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `courts_court_name_ar_index` (`court_name_ar`),
  KEY `courts_court_name_en_index` (`court_name_en`),
  KEY `courts_is_active_index` (`is_active`),
  KEY `courts_created_by_foreign` (`created_by`),
  KEY `courts_updated_by_foreign` (`updated_by`)
);

-- court_circuit

  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `court_id` bigint UNSIGNED NOT NULL,
  `circuit_name_id` bigint UNSIGNED NOT NULL,
  `circuit_serial_id` bigint UNSIGNED DEFAULT NULL,
  `circuit_shift_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_court_circuit_combo` (`court_id`,`circuit_name_id`,`circuit_serial_id`,`circuit_shift_id`),
  KEY `court_circuit_court_id_index` (`court_id`),
  KEY `court_circuit_circuit_name_id_index` (`circuit_name_id`),
  KEY `court_circuit_circuit_serial_id_index` (`circuit_serial_id`),
  KEY `court_circuit_circuit_shift_id_index` (`circuit_shift_id`)
);

-- court_floor

  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `court_id` bigint UNSIGNED NOT NULL,
  `option_value_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `court_floor_court_id_option_value_id_unique` (`court_id`,`option_value_id`),
  KEY `court_floor_court_id_index` (`court_id`),
  KEY `court_floor_option_value_id_index` (`option_value_id`)
);

-- court_hall

  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `court_id` bigint UNSIGNED NOT NULL,
  `option_value_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `court_hall_court_id_option_value_id_unique` (`court_id`,`option_value_id`),
  KEY `court_hall_court_id_index` (`court_id`),
  KEY `court_hall_option_value_id_index` (`option_value_id`)
);

-- court_secretary

  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `court_id` bigint UNSIGNED NOT NULL,
  `option_value_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `court_secretary_court_id_option_value_id_unique` (`court_id`,`option_value_id`),
  KEY `court_secretary_court_id_index` (`court_id`),
  KEY `court_secretary_option_value_id_index` (`option_value_id`)
);

-- deletion_bundles

  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `root_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `root_id` bigint UNSIGNED NOT NULL,
  `root_label` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `snapshot_json` json NOT NULL,
  `files_json` json DEFAULT NULL,
  `cascade_count` int NOT NULL DEFAULT '0',
  `deleted_by` bigint UNSIGNED NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `status` enum('trashed','restored','purged') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'trashed',
  `ttl_at` datetime DEFAULT NULL,
  `restored_at` datetime DEFAULT NULL,
  `restore_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deletion_bundles_root_type_root_id_index` (`root_type`,`root_id`),
  KEY `deletion_bundles_status_index` (`status`),
  KEY `deletion_bundles_deleted_by_index` (`deleted_by`),
  KEY `deletion_bundles_ttl_at_index` (`ttl_at`),
  KEY `deletion_bundles_created_at_index` (`created_at`)
);

-- deletion_bundle_items

  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bundle_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED DEFAULT NULL,
  `payload_json` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deletion_bundle_items_bundle_id_index` (`bundle_id`),
  KEY `deletion_bundle_items_model_model_id_index` (`model`,`model_id`)
);

-- engagement_letters

  `id` int NOT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `client_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contract_date` datetime DEFAULT NULL,
  `contract_details` text COLLATE utf8mb4_unicode_ci,
  `contract_structure` text COLLATE utf8mb4_unicode_ci,
  `contract_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matters` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mfiles_id` int DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `engagement_letters_client_id_index` (`client_id`),
  KEY `engagement_letters_contract_date_index` (`contract_date`),
  KEY `engagement_letters_status_index` (`status`),
  KEY `engagement_letters_created_by_foreign` (`created_by`),
  KEY `engagement_letters_updated_by_foreign` (`updated_by`)
);

-- failed_jobs

  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
);

-- hearings

  `id` int NOT NULL,
  `matter_id` bigint UNSIGNED NOT NULL,
  `lawyer_id` bigint UNSIGNED DEFAULT NULL,
  `date` date DEFAULT NULL,
  `procedure` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `court` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `circuit` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `destination` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `decision` text COLLATE utf8mb4_unicode_ci,
  `short_decision` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_decision` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `next_hearing` date DEFAULT NULL,
  `report` tinyint(1) NOT NULL DEFAULT '0',
  `notify_client` tinyint(1) NOT NULL DEFAULT '0',
  `attendee` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attendee_1` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attendee_2` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attendee_3` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attendee_4` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `next_attendee` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `evaluation` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hearings_matter_id_date_index` (`matter_id`,`date`),
  KEY `hearings_next_hearing_index` (`next_hearing`),
  KEY `hearings_lawyer_id_index` (`lawyer_id`),
  KEY `hearings_created_by_foreign` (`created_by`),
  KEY `hearings_updated_by_foreign` (`updated_by`)
);

-- import_sessions

  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `session_id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `table_name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_filename` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_filename` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('uploaded','mapped','validated','importing','completed','failed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'uploaded',
  `file_type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int UNSIGNED NOT NULL,
  `file_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_rows` int UNSIGNED DEFAULT NULL,
  `header_row` int UNSIGNED NOT NULL DEFAULT '1',
  `column_mapping` json DEFAULT NULL,
  `transforms` json DEFAULT NULL,
  `preflight_errors` json DEFAULT NULL,
  `preflight_error_count` int UNSIGNED NOT NULL DEFAULT '0',
  `preflight_warning_count` int UNSIGNED NOT NULL DEFAULT '0',
  `imported_count` int UNSIGNED NOT NULL DEFAULT '0',
  `failed_count` int UNSIGNED NOT NULL DEFAULT '0',
  `skipped_count` int UNSIGNED NOT NULL DEFAULT '0',
  `import_errors` json DEFAULT NULL,
  `backup_file` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `backup_size` bigint UNSIGNED DEFAULT NULL,
  `backup_created_at` timestamp NULL DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `duration_seconds` int UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `import_sessions_session_id_unique` (`session_id`),
  KEY `import_sessions_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `import_sessions_created_at_index` (`created_at`),
  KEY `import_sessions_table_name_index` (`table_name`),
  KEY `import_sessions_status_index` (`status`)
);

-- lawyers

  `id` int NOT NULL,
  `lawyer_name_ar` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lawyer_name_en` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lawyer_name_title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title_id` bigint UNSIGNED DEFAULT NULL,
  `lawyer_email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attendance_track` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lawyers_lawyer_name_ar_index` (`lawyer_name_ar`),
  KEY `lawyers_lawyer_name_en_index` (`lawyer_name_en`),
  KEY `lawyers_lawyer_email_index` (`lawyer_email`),
  KEY `lawyers_created_by_foreign` (`created_by`),
  KEY `lawyers_updated_by_foreign` (`updated_by`),
  KEY `lawyers_title_id_foreign` (`title_id`)
);

-- migrations

  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
);

-- model_has_permissions

  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`)
);

-- model_has_roles

  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`)
);

-- opponents

  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `opponent_name_ar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `opponent_name_en` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `normalized_name` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `first_token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_count` tinyint UNSIGNED DEFAULT NULL,
  `latin_key` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `opponents_opponent_name_ar_index` (`opponent_name_ar`),
  KEY `opponents_opponent_name_en_index` (`opponent_name_en`),
  KEY `opponents_is_active_index` (`is_active`),
  KEY `opponents_created_by_foreign` (`created_by`),
  KEY `opponents_updated_by_foreign` (`updated_by`),
  KEY `opponents_normalized_name_prefix` (`normalized_name`(191)),
  KEY `opponents_first_token_index` (`first_token`),
  KEY `opponents_last_token_index` (`last_token`),
  KEY `opponents_token_count_index` (`token_count`),
  KEY `opponents_latin_key_index` (`latin_key`)
);

-- opponent_aliases

  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `opponent_id` bigint UNSIGNED NOT NULL,
  `alias_normalized` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `opponent_alias_unique` (`opponent_id`,`alias_normalized`(191)),
  KEY `opponent_aliases_opponent_id_index` (`opponent_id`)
);

-- opponent_trigrams

  `opponent_id` bigint UNSIGNED NOT NULL,
  `tri` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`opponent_id`,`tri`),
  KEY `opponent_trigrams_tri_index` (`tri`)
);

-- option_sets

  `id` int NOT NULL,
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_en` text COLLATE utf8mb4_unicode_ci,
  `description_ar` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `option_sets_key_unique` (`key`)
);

-- option_values

  `id` int NOT NULL AUTO_INCREMENT,
  `set_id` bigint UNSIGNED NOT NULL,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label_en` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label_ar` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `option_values_set_id_code_unique` (`set_id`,`code`),
  KEY `option_values_set_id_is_active_position_index` (`set_id`,`is_active`,`position`)
);

-- password_resets

  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
);

-- password_reset_tokens

  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
);

-- permissions

  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
);

-- personal_access_tokens

  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
);

-- power_of_attorneys

  `id` int NOT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `client_print_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `principal_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `year` int DEFAULT NULL,
  `capacity` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `authorized_lawyers` text COLLATE utf8mb4_unicode_ci,
  `issue_date` date DEFAULT NULL,
  `inventory` tinyint(1) NOT NULL DEFAULT '1',
  `issuing_authority` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `letter` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `poa_number` int DEFAULT NULL,
  `principal_capacity` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `copies_count` int DEFAULT NULL,
  `serial` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `power_of_attorneys_client_id_index` (`client_id`),
  KEY `power_of_attorneys_issue_date_index` (`issue_date`),
  KEY `power_of_attorneys_poa_number_index` (`poa_number`),
  KEY `power_of_attorneys_created_by_foreign` (`created_by`),
  KEY `power_of_attorneys_updated_by_foreign` (`updated_by`)
);

-- roles

  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
);

-- role_has_permissions

  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`)
);

-- users

  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `locale` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
);
