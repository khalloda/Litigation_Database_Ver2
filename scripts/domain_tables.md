### `activity_log`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | bigint | NO | — | AUTO_INCREMENT |
| `log_name` | varchar(191) | YES | NULL | — |
| `description` | text | NO | — | — |
| `subject_type` | varchar(191) | YES | NULL | — |
| `event` | varchar(191) | YES | NULL | — |
| `subject_id` | bigint | YES | NULL | — |
| `causer_type` | varchar(191) | YES | NULL | — |
| `causer_id` | bigint | YES | NULL | — |
| `properties` | json | YES | NULL | — |
| `batch_uuid` | char(36) | YES | NULL | — |
| `created_at` | timestamp | YES | NULL | — |
| `updated_at` | timestamp | YES | NULL | — |

**Indexes:**
- `PRIMARY KEY (`id`)`
- `KEY `subject` (`subject_type`,`subject_id`)`
- `KEY `causer` (`causer_type`,`causer_id`)`
- `KEY `activity_log_log_name_index` (`log_name`)`

### `admin_subtasks`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | int | NO | — | — |
| `task_id` | bigint | NO | — | — |
| `lawyer_id` | bigint | YES | NULL | — |
| `performer` | varchar(191) | YES | NULL | — |
| `next_date` | date | YES | NULL | — |
| `result` | text | YES | — | — |
| `procedure_date` | date | YES | NULL | — |
| `report` | tinyint(1) | NO | '0' | — |
| `created_by` | bigint | YES | NULL | — |
| `updated_by` | bigint | YES | NULL | — |
| `created_at` | timestamp | YES | NULL | — |
| `updated_at` | timestamp | YES | NULL | — |
| `deleted_at` | timestamp | YES | NULL | — |

**Indexes:**
- `PRIMARY KEY (`id`)`
- `KEY `admin_subtasks_task_id_index` (`task_id`)`
- `KEY `admin_subtasks_lawyer_id_index` (`lawyer_id`)`
- `KEY `admin_subtasks_next_date_index` (`next_date`)`
- `KEY `admin_subtasks_created_by_foreign` (`created_by`)`
- `KEY `admin_subtasks_updated_by_foreign` (`updated_by`)`

### `admin_tasks`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | int | NO | — | — |
| `matter_id` | bigint | NO | — | — |
| `lawyer_id` | bigint | YES | NULL | — |
| `last_follow_up` | text | YES | — | — |
| `last_date` | date | YES | NULL | — |
| `authority` | varchar(191) | YES | NULL | — |
| `status` | varchar(191) | YES | NULL | — |
| `circuit` | varchar(191) | YES | NULL | — |
| `required_work` | text | YES | — | — |
| `performer` | varchar(191) | YES | NULL | — |
| `previous_decision` | text | YES | — | — |
| `court` | varchar(191) | YES | NULL | — |
| `result` | text | YES | — | — |
| `creation_date` | datetime | YES | NULL | — |
| `execution_date` | datetime | YES | NULL | — |
| `alert` | tinyint(1) | NO | '0' | — |
| `created_by` | bigint | YES | NULL | — |
| `updated_by` | bigint | YES | NULL | — |
| `created_at` | timestamp | YES | NULL | — |
| `updated_at` | timestamp | YES | NULL | — |
| `deleted_at` | timestamp | YES | NULL | — |

**Indexes:**
- `PRIMARY KEY (`id`)`
- `KEY `admin_tasks_matter_id_status_index` (`matter_id`,`status`)`
- `KEY `admin_tasks_lawyer_id_index` (`lawyer_id`)`
- `KEY `admin_tasks_execution_date_index` (`execution_date`)`
- `KEY `admin_tasks_created_by_foreign` (`created_by`)`
- `KEY `admin_tasks_updated_by_foreign` (`updated_by`)`

### `cases`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | int | NO | — | — |
| `client_id` | bigint | NO | — | — |
| `client_in_case_name` | varchar(191) | YES | NULL | — |
| `opponent_in_case_name` | varchar(191) | YES | NULL | — |
| `contract_id` | bigint | YES | NULL | — |
| `engagement_letter_no` | varchar(191) | YES | NULL | — |
| `matter_name_ar` | varchar(191) | NO | — | — |
| `matter_name_en` | varchar(191) | NO | — | — |
| `matter_description` | text | YES | — | — |
| `matter_status` | varchar(191) | YES | NULL | — |
| `matter_status_id` | bigint | YES | NULL | — |
| `matter_category` | varchar(191) | YES | NULL | — |
| `matter_category_id` | bigint | YES | NULL | — |
| `court_id` | bigint | YES | NULL | — |
| `circuit_name_id` | bigint | YES | NULL | — |
| `circuit_serial_id` | bigint | YES | NULL | — |
| `circuit_shift_id` | bigint | YES | '221' | — |
| `matter_circuit_legacy` | bigint | YES | NULL | — |
| `circuit_secretary` | bigint | YES | NULL | — |
| `court_floor` | bigint | YES | NULL | — |
| `court_hall` | bigint | YES | NULL | — |
| `matter_degree` | varchar(191) | YES | NULL | — |
| `matter_degree_id` | bigint | YES | NULL | — |
| `matter_court_text` | varchar(191) | YES | NULL | — |
| `matter_destination` | varchar(191) | YES | NULL | — |
| `matter_destination_id` | bigint | YES | NULL | — |
| `matter_importance` | varchar(191) | YES | NULL | — |
| `matter_importance_id` | bigint | YES | NULL | — |
| `matter_evaluation` | varchar(191) | YES | NULL | — |
| `matter_start_date` | date | YES | NULL | — |
| `matter_end_date` | date | YES | NULL | — |
| `matter_asked_amount` | decimal(15,2) | YES | NULL | — |
| `matter_judged_amount` | decimal(15,2) | YES | NULL | — |
| `matter_shelf` | varchar(10) | YES | NULL | — |
| `matter_partner` | varchar(191) | YES | NULL | — |
| `matter_partner_id` | bigint | YES | NULL | — |
| `lawyer_a` | varchar(191) | YES | NULL | — |
| `lawyer_b` | varchar(191) | YES | NULL | — |
| `fee_letter` | decimal(15,2) | YES | NULL | — |
| `allocated_budget` | text | YES | — | — |
| `team_id` | int | YES | NULL | — |
| `legal_opinion` | text | YES | — | — |
| `financial_provision` | text | YES | — | — |
| `current_status` | text | YES | — | — |
| `notes_1` | text | YES | — | — |
| `notes_2` | text | YES | — | — |
| `client_and_capacity` | text | YES | — | — |
| `client_capacity_id` | bigint | YES | NULL | — |
| `opponent_and_capacity` | text | YES | — | — |
| `opponent_id` | bigint | YES | NULL | — |
| `opponent_capacity_id` | bigint | YES | NULL | — |
| `client_branch` | varchar(191) | YES | NULL | — |
| `matter_branch_id` | bigint | YES | NULL | — |
| `client_type` | varchar(191) | YES | NULL | — |
| `client_type_id` | bigint | YES | NULL | — |
| `matter_select` | tinyint(1) | NO | '1' | — |
| `created_by` | bigint | YES | NULL | — |
| `updated_by` | bigint | YES | NULL | — |
| `created_at` | timestamp | YES | NULL | — |
| `updated_at` | timestamp | YES | NULL | — |
| `deleted_at` | timestamp | YES | NULL | — |
| `client_capacity_note` | text | YES | — | — |
| `opponent_capacity_note` | text | YES | — | — |

**Indexes:**
- `PRIMARY KEY (`id`)`
- `KEY `cases_client_id_matter_status_index` (`client_id`,`matter_status`)`
- `KEY `cases_matter_name_ar_index` (`matter_name_ar`)`
- `KEY `cases_matter_name_en_index` (`matter_name_en`)`
- `KEY `cases_matter_status_index` (`matter_status`)`
- `KEY `cases_matter_start_date_index` (`matter_start_date`)`
- `KEY `cases_contract_id_index` (`contract_id`)`
- `KEY `cases_created_by_foreign` (`created_by`)`
- `KEY `cases_updated_by_foreign` (`updated_by`)`
- `KEY `cases_court_id_foreign` (`court_id`)`
- `KEY `cases_circuit_secretary_foreign` (`circuit_secretary`)`
- `KEY `cases_court_floor_foreign` (`court_floor`)`
- `KEY `cases_court_hall_foreign` (`court_hall`)`
- `KEY `cases_circuit_name_id_index` (`circuit_name_id`)`
- `KEY `cases_circuit_serial_id_index` (`circuit_serial_id`)`
- `KEY `cases_circuit_shift_id_index` (`circuit_shift_id`)`
- `KEY `cases_matter_category_id_foreign` (`matter_category_id`)`
- `KEY `cases_matter_degree_id_foreign` (`matter_degree_id`)`
- `KEY `cases_matter_status_id_foreign` (`matter_status_id`)`
- `KEY `cases_matter_importance_id_foreign` (`matter_importance_id`)`
- `KEY `cases_matter_branch_id_foreign` (`matter_branch_id`)`
- `KEY `cases_client_capacity_id_foreign` (`client_capacity_id`)`
- `KEY `cases_client_type_id_foreign` (`client_type_id`)`
- `KEY `cases_opponent_capacity_id_foreign` (`opponent_capacity_id`)`
- `KEY `cases_matter_destination_id_foreign` (`matter_destination_id`)`
- `KEY `cases_matter_partner_id_foreign` (`matter_partner_id`)`
- `KEY `cases_opponent_id_foreign` (`opponent_id`)`

### `clients`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | int | NO | — | — |
| `mfiles_id` | int | YES | NULL | COMMENT MFiles system identifier |
| `client_code` | varchar(50) | YES | NULL | COMMENT Unique client code for identification |
| `client_name_ar` | varchar(191) | NO | — | — |
| `client_name_en` | varchar(191) | YES | NULL | — |
| `client_print_name` | varchar(191) | NO | — | — |
| `status` | varchar(191) | YES | 'Active' | — |
| `cash_or_probono` | varchar(191) | YES | NULL | — |
| `client_start` | date | YES | NULL | — |
| `client_end` | date | YES | NULL | — |
| `contact_lawyer` | varchar(191) | YES | NULL | — |
| `contact_lawyer_id` | bigint | YES | NULL | — |
| `logo` | varchar(191) | YES | NULL | — |
| `power_of_attorney_location` | varchar(191) | YES | NULL | — |
| `documents_location` | varchar(191) | YES | NULL | — |
| `created_by` | bigint | YES | NULL | — |
| `updated_by` | bigint | YES | NULL | — |
| `created_at` | timestamp | YES | NULL | — |
| `updated_at` | timestamp | YES | NULL | — |
| `deleted_at` | timestamp | YES | NULL | — |
| `cash_or_probono_id` | bigint | YES | NULL | — |
| `status_id` | bigint | YES | NULL | — |
| `power_of_attorney_location_id` | bigint | YES | NULL | — |
| `documents_location_id` | bigint | YES | NULL | — |

**Indexes:**
- `PRIMARY KEY (`id`)`
- `UNIQUE KEY `clients_client_code_unique` (`client_code`)`
- `KEY `clients_client_name_ar_index` (`client_name_ar`)`
- `KEY `clients_client_name_en_index` (`client_name_en`)`
- `KEY `clients_status_index` (`status`)`
- `KEY `clients_client_start_index` (`client_start`)`
- `KEY `clients_created_by_foreign` (`created_by`)`
- `KEY `clients_updated_by_foreign` (`updated_by`)`
- `KEY `clients_cash_or_probono_id_index` (`cash_or_probono_id`)`
- `KEY `clients_status_id_index` (`status_id`)`
- `KEY `clients_power_of_attorney_location_id_index` (`power_of_attorney_location_id`)`
- `KEY `clients_documents_location_id_index` (`documents_location_id`)`
- `KEY `clients_contact_lawyer_id_index` (`contact_lawyer_id`)`

### `client_documents`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | int | NO | — | — |
| `client_id` | bigint | NO | — | — |
| `matter_id` | bigint | YES | NULL | — |
| `client_name` | varchar(191) | YES | NULL | — |
| `document_name` | varchar(191) | YES | NULL | — |
| `document_type` | varchar(191) | YES | NULL | — |
| `file_path` | varchar(191) | YES | NULL | — |
| `file_size` | bigint | YES | NULL | — |
| `mime_type` | varchar(191) | YES | NULL | — |
| `document_storage_type` | enum('physical','digital','both') | NO | 'physical' | — |
| `mfiles_uploaded` | tinyint(1) | NO | '0' | — |
| `mfiles_id` | varchar(191) | YES | NULL | — |
| `responsible_lawyer` | varchar(191) | YES | NULL | — |
| `movement_card` | tinyint(1) | NO | '0' | — |
| `document_description` | text | YES | — | — |
| `deposit_date` | date | NO | — | — |
| `document_date` | date | YES | NULL | — |
| `case_number` | varchar(191) | YES | NULL | — |
| `pages_count` | varchar(191) | YES | NULL | — |
| `notes` | text | YES | — | — |
| `created_by` | bigint | YES | NULL | — |
| `updated_by` | bigint | YES | NULL | — |
| `created_at` | timestamp | YES | NULL | — |
| `updated_at` | timestamp | YES | NULL | — |
| `deleted_at` | timestamp | YES | NULL | — |

**Indexes:**
- `PRIMARY KEY (`id`)`
- `KEY `client_documents_matter_id_foreign` (`matter_id`)`
- `KEY `client_documents_client_id_matter_id_deposit_date_index` (`client_id`,`matter_id`,`deposit_date`)`
- `KEY `client_documents_deposit_date_index` (`deposit_date`)`
- `KEY `client_documents_created_by_foreign` (`created_by`)`
- `KEY `client_documents_updated_by_foreign` (`updated_by`)`
- `KEY `client_documents_document_type_index` (`document_type`)`
- `KEY `client_documents_mime_type_index` (`mime_type`)`

### `contacts`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | int | NO | — | — |
| `client_id` | bigint | NO | — | — |
| `contact_name` | varchar(191) | YES | NULL | — |
| `full_name` | varchar(191) | YES | NULL | — |
| `job_title` | varchar(191) | YES | NULL | — |
| `address` | varchar(191) | YES | NULL | — |
| `city` | varchar(191) | YES | NULL | — |
| `state` | varchar(191) | YES | NULL | — |
| `country` | varchar(191) | YES | NULL | — |
| `zip_code` | varchar(191) | YES | NULL | — |
| `business_phone` | varchar(191) | YES | NULL | — |
| `home_phone` | varchar(191) | YES | NULL | — |
| `mobile_phone` | varchar(191) | YES | NULL | — |
| `fax_number` | varchar(191) | YES | NULL | — |
| `email` | varchar(191) | YES | NULL | — |
| `web_page` | varchar(191) | YES | NULL | — |
| `attachments` | text | YES | — | — |
| `created_by` | bigint | YES | NULL | — |
| `updated_by` | bigint | YES | NULL | — |
| `created_at` | timestamp | YES | NULL | — |
| `updated_at` | timestamp | YES | NULL | — |
| `deleted_at` | timestamp | YES | NULL | — |

**Indexes:**
- `PRIMARY KEY (`id`)`
- `KEY `contacts_client_id_index` (`client_id`)`
- `KEY `contacts_email_index` (`email`)`
- `KEY `contacts_created_by_foreign` (`created_by`)`
- `KEY `contacts_updated_by_foreign` (`updated_by`)`

### `courts`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | int | NO | — | — |
| `court_name_ar` | varchar(191) | YES | NULL | — |
| `court_name_en` | varchar(191) | YES | NULL | — |
| `is_active` | tinyint(1) | NO | '1' | — |
| `created_by` | bigint | YES | NULL | — |
| `updated_by` | bigint | YES | NULL | — |
| `created_at` | timestamp | YES | NULL | — |
| `updated_at` | timestamp | YES | NULL | — |
| `deleted_at` | timestamp | YES | NULL | — |

**Indexes:**
- `PRIMARY KEY (`id`)`
- `KEY `courts_court_name_ar_index` (`court_name_ar`)`
- `KEY `courts_court_name_en_index` (`court_name_en`)`
- `KEY `courts_is_active_index` (`is_active`)`
- `KEY `courts_created_by_foreign` (`created_by`)`
- `KEY `courts_updated_by_foreign` (`updated_by`)`

### `court_circuit`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | bigint | NO | — | AUTO_INCREMENT |
| `court_id` | bigint | NO | — | — |
| `circuit_name_id` | bigint | NO | — | — |
| `circuit_serial_id` | bigint | YES | NULL | — |
| `circuit_shift_id` | bigint | NO | — | — |
| `created_at` | timestamp | YES | NULL | — |
| `updated_at` | timestamp | YES | NULL | — |

**Indexes:**
- `PRIMARY KEY (`id`)`
- `UNIQUE KEY `unique_court_circuit_combo` (`court_id`,`circuit_name_id`,`circuit_serial_id`,`circuit_shift_id`)`
- `KEY `court_circuit_court_id_index` (`court_id`)`
- `KEY `court_circuit_circuit_name_id_index` (`circuit_name_id`)`
- `KEY `court_circuit_circuit_serial_id_index` (`circuit_serial_id`)`
- `KEY `court_circuit_circuit_shift_id_index` (`circuit_shift_id`)`

### `court_floor`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | bigint | NO | — | AUTO_INCREMENT |
| `court_id` | bigint | NO | — | — |
| `option_value_id` | bigint | NO | — | — |
| `created_at` | timestamp | YES | NULL | — |
| `updated_at` | timestamp | YES | NULL | — |

**Indexes:**
- `PRIMARY KEY (`id`)`
- `UNIQUE KEY `court_floor_court_id_option_value_id_unique` (`court_id`,`option_value_id`)`
- `KEY `court_floor_court_id_index` (`court_id`)`
- `KEY `court_floor_option_value_id_index` (`option_value_id`)`

### `court_hall`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | bigint | NO | — | AUTO_INCREMENT |
| `court_id` | bigint | NO | — | — |
| `option_value_id` | bigint | NO | — | — |
| `created_at` | timestamp | YES | NULL | — |
| `updated_at` | timestamp | YES | NULL | — |

**Indexes:**
- `PRIMARY KEY (`id`)`
- `UNIQUE KEY `court_hall_court_id_option_value_id_unique` (`court_id`,`option_value_id`)`
- `KEY `court_hall_court_id_index` (`court_id`)`
- `KEY `court_hall_option_value_id_index` (`option_value_id`)`

### `court_secretary`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | bigint | NO | — | AUTO_INCREMENT |
| `court_id` | bigint | NO | — | — |
| `option_value_id` | bigint | NO | — | — |
| `created_at` | timestamp | YES | NULL | — |
| `updated_at` | timestamp | YES | NULL | — |

**Indexes:**
- `PRIMARY KEY (`id`)`
- `UNIQUE KEY `court_secretary_court_id_option_value_id_unique` (`court_id`,`option_value_id`)`
- `KEY `court_secretary_court_id_index` (`court_id`)`
- `KEY `court_secretary_option_value_id_index` (`option_value_id`)`

### `deletion_bundles`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | char(36) | NO | — | — |
| `root_type` | varchar(191) | NO | — | — |
| `root_id` | bigint | NO | — | — |
| `root_label` | varchar(191) | NO | — | — |
| `snapshot_json` | json | NO | — | — |
| `files_json` | json | YES | NULL | — |
| `cascade_count` | int | NO | '0' | — |
| `deleted_by` | bigint | NO | — | — |
| `reason` | text | YES | — | — |
| `status` | enum('trashed','restored','purged') | NO | 'trashed' | — |
| `ttl_at` | datetime | YES | NULL | — |
| `restored_at` | datetime | YES | NULL | — |
| `restore_notes` | text | YES | — | — |
| `created_at` | timestamp | YES | NULL | — |
| `updated_at` | timestamp | YES | NULL | — |

**Indexes:**
- `PRIMARY KEY (`id`)`
- `KEY `deletion_bundles_root_type_root_id_index` (`root_type`,`root_id`)`
- `KEY `deletion_bundles_status_index` (`status`)`
- `KEY `deletion_bundles_deleted_by_index` (`deleted_by`)`
- `KEY `deletion_bundles_ttl_at_index` (`ttl_at`)`
- `KEY `deletion_bundles_created_at_index` (`created_at`)`

### `deletion_bundle_items`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | char(36) | NO | — | — |
| `bundle_id` | char(36) | NO | — | — |
| `model` | varchar(191) | NO | — | — |
| `model_id` | bigint | YES | NULL | — |
| `payload_json` | json | NO | — | — |
| `created_at` | timestamp | YES | NULL | — |
| `updated_at` | timestamp | YES | NULL | — |

**Indexes:**
- `PRIMARY KEY (`id`)`
- `KEY `deletion_bundle_items_bundle_id_index` (`bundle_id`)`
- `KEY `deletion_bundle_items_model_model_id_index` (`model`,`model_id`)`

### `engagement_letters`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | int | NO | — | — |
| `client_id` | bigint | NO | — | — |
| `client_name` | varchar(191) | YES | NULL | — |
| `contract_date` | datetime | YES | NULL | — |
| `contract_details` | text | YES | — | — |
| `contract_structure` | text | YES | — | — |
| `contract_type` | varchar(191) | YES | NULL | — |
| `matters` | text | YES | — | — |
| `status` | varchar(191) | YES | NULL | — |
| `mfiles_id` | int | YES | NULL | — |
| `created_by` | bigint | YES | NULL | — |
| `updated_by` | bigint | YES | NULL | — |
| `created_at` | timestamp | YES | NULL | — |
| `updated_at` | timestamp | YES | NULL | — |
| `deleted_at` | timestamp | YES | NULL | — |

**Indexes:**
- `PRIMARY KEY (`id`)`
- `KEY `engagement_letters_client_id_index` (`client_id`)`
- `KEY `engagement_letters_contract_date_index` (`contract_date`)`
- `KEY `engagement_letters_status_index` (`status`)`
- `KEY `engagement_letters_created_by_foreign` (`created_by`)`
- `KEY `engagement_letters_updated_by_foreign` (`updated_by`)`

### `failed_jobs`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | bigint | NO | — | AUTO_INCREMENT |
| `uuid` | varchar(191) | NO | — | — |
| `connection` | text | NO | — | — |
| `queue` | text | NO | — | — |
| `payload` | longtext | NO | — | — |
| `exception` | longtext | NO | — | — |
| `failed_at` | timestamp | NO | CURRENT_TIMESTAMP | — |

**Indexes:**
- `PRIMARY KEY (`id`)`
- `UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)`

### `hearings`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | int | NO | — | — |
| `matter_id` | bigint | NO | — | — |
| `lawyer_id` | bigint | YES | NULL | — |
| `date` | date | YES | NULL | — |
| `procedure` | varchar(191) | YES | NULL | — |
| `court` | varchar(191) | YES | NULL | — |
| `circuit` | varchar(191) | YES | NULL | — |
| `destination` | varchar(191) | YES | NULL | — |
| `decision` | text | YES | — | — |
| `short_decision` | varchar(191) | YES | NULL | — |
| `last_decision` | varchar(191) | YES | NULL | — |
| `next_hearing` | date | YES | NULL | — |
| `report` | tinyint(1) | NO | '0' | — |
| `notify_client` | tinyint(1) | NO | '0' | — |
| `attendee` | varchar(191) | YES | NULL | — |
| `attendee_1` | varchar(191) | YES | NULL | — |
| `attendee_2` | varchar(191) | YES | NULL | — |
| `attendee_3` | varchar(191) | YES | NULL | — |
| `attendee_4` | varchar(191) | YES | NULL | — |
| `next_attendee` | varchar(191) | YES | NULL | — |
| `evaluation` | varchar(191) | YES | NULL | — |
| `notes` | text | YES | — | — |
| `created_by` | bigint | YES | NULL | — |
| `updated_by` | bigint | YES | NULL | — |
| `created_at` | timestamp | YES | NULL | — |
| `updated_at` | timestamp | YES | NULL | — |
| `deleted_at` | timestamp | YES | NULL | — |

**Indexes:**
- `PRIMARY KEY (`id`)`
- `KEY `hearings_matter_id_date_index` (`matter_id`,`date`)`
- `KEY `hearings_next_hearing_index` (`next_hearing`)`
- `KEY `hearings_lawyer_id_index` (`lawyer_id`)`
- `KEY `hearings_created_by_foreign` (`created_by`)`
- `KEY `hearings_updated_by_foreign` (`updated_by`)`

### `import_sessions`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | bigint | NO | — | AUTO_INCREMENT |
| `session_id` | varchar(36) | NO | — | — |
| `table_name` | varchar(64) | NO | — | — |
| `original_filename` | varchar(191) | NO | — | — |
| `stored_filename` | varchar(191) | NO | — | — |
| `status` | enum('uploaded','mapped','validated','importing','completed','failed','cancelled') | NO | 'uploaded' | — |
| `file_type` | varchar(10) | NO | — | — |
| `file_size` | int | NO | — | — |
| `file_hash` | varchar(64) | NO | — | — |
| `total_rows` | int | YES | NULL | — |
| `header_row` | int | NO | '1' | — |
| `column_mapping` | json | YES | NULL | — |
| `transforms` | json | YES | NULL | — |
| `preflight_errors` | json | YES | NULL | — |
| `preflight_error_count` | int | NO | '0' | — |
| `preflight_warning_count` | int | NO | '0' | — |
| `imported_count` | int | NO | '0' | — |
| `failed_count` | int | NO | '0' | — |
| `skipped_count` | int | NO | '0' | — |
| `import_errors` | json | YES | NULL | — |
| `backup_file` | varchar(191) | YES | NULL | — |
| `backup_size` | bigint | YES | NULL | — |
| `backup_created_at` | timestamp | YES | NULL | — |
| `started_at` | timestamp | YES | NULL | — |
| `completed_at` | timestamp | YES | NULL | — |
| `duration_seconds` | int | YES | NULL | — |
| `user_id` | bigint | NO | — | — |
| `created_at` | timestamp | YES | NULL | — |
| `updated_at` | timestamp | YES | NULL | — |
| `deleted_at` | timestamp | YES | NULL | — |

**Indexes:**
- `PRIMARY KEY (`id`)`
- `UNIQUE KEY `import_sessions_session_id_unique` (`session_id`)`
- `KEY `import_sessions_user_id_created_at_index` (`user_id`,`created_at`)`
- `KEY `import_sessions_created_at_index` (`created_at`)`
- `KEY `import_sessions_table_name_index` (`table_name`)`
- `KEY `import_sessions_status_index` (`status`)`

### `lawyers`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | int | NO | — | — |
| `lawyer_name_ar` | varchar(191) | NO | — | — |
| `lawyer_name_en` | varchar(191) | NO | — | — |
| `lawyer_name_title` | varchar(191) | YES | NULL | — |
| `title_id` | bigint | YES | NULL | — |
| `lawyer_email` | varchar(191) | YES | NULL | — |
| `attendance_track` | tinyint(1) | NO | '0' | — |
| `created_by` | bigint | YES | NULL | — |
| `updated_by` | bigint | YES | NULL | — |
| `created_at` | timestamp | YES | NULL | — |
| `updated_at` | timestamp | YES | NULL | — |
| `deleted_at` | timestamp | YES | NULL | — |

**Indexes:**
- `PRIMARY KEY (`id`)`
- `KEY `lawyers_lawyer_name_ar_index` (`lawyer_name_ar`)`
- `KEY `lawyers_lawyer_name_en_index` (`lawyer_name_en`)`
- `KEY `lawyers_lawyer_email_index` (`lawyer_email`)`
- `KEY `lawyers_created_by_foreign` (`created_by`)`
- `KEY `lawyers_updated_by_foreign` (`updated_by`)`
- `KEY `lawyers_title_id_foreign` (`title_id`)`

### `migrations`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | int | NO | — | AUTO_INCREMENT |
| `migration` | varchar(191) | NO | — | — |
| `batch` | int | NO | — | — |

**Indexes:**
- `PRIMARY KEY (`id`)`

### `model_has_permissions`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `permission_id` | bigint | NO | — | — |
| `model_type` | varchar(191) | NO | — | — |
| `model_id` | bigint | NO | — | — |

**Indexes:**
- `PRIMARY KEY (`permission_id`,`model_id`,`model_type`)`
- `KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`)`

### `model_has_roles`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `role_id` | bigint | NO | — | — |
| `model_type` | varchar(191) | NO | — | — |
| `model_id` | bigint | NO | — | — |

**Indexes:**
- `PRIMARY KEY (`role_id`,`model_id`,`model_type`)`
- `KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`)`

### `opponents`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | bigint | NO | — | AUTO_INCREMENT |
| `opponent_name_ar` | varchar(191) | YES | NULL | — |
| `opponent_name_en` | varchar(191) | YES | NULL | — |
| `normalized_name` | varchar(512) | YES | NULL | — |
| `is_active` | tinyint(1) | NO | '1' | — |
| `description` | text | YES | — | — |
| `notes` | text | YES | — | — |
| `created_by` | bigint | YES | NULL | — |
| `updated_by` | bigint | YES | NULL | — |
| `created_at` | timestamp | YES | NULL | — |
| `updated_at` | timestamp | YES | NULL | — |
| `deleted_at` | timestamp | YES | NULL | — |
| `first_token` | varchar(64) | YES | NULL | — |
| `last_token` | varchar(64) | YES | NULL | — |
| `token_count` | tinyint | YES | NULL | — |
| `latin_key` | varchar(64) | YES | NULL | — |

**Indexes:**
- `PRIMARY KEY (`id`)`
- `KEY `opponents_opponent_name_ar_index` (`opponent_name_ar`)`
- `KEY `opponents_opponent_name_en_index` (`opponent_name_en`)`
- `KEY `opponents_is_active_index` (`is_active`)`
- `KEY `opponents_created_by_foreign` (`created_by`)`
- `KEY `opponents_updated_by_foreign` (`updated_by`)`
- `KEY `opponents_normalized_name_prefix` (`normalized_name`(191))`
- `KEY `opponents_first_token_index` (`first_token`)`
- `KEY `opponents_last_token_index` (`last_token`)`
- `KEY `opponents_token_count_index` (`token_count`)`
- `KEY `opponents_latin_key_index` (`latin_key`)`

### `opponent_aliases`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | bigint | NO | — | AUTO_INCREMENT |
| `opponent_id` | bigint | NO | — | — |
| `alias_normalized` | varchar(512) | NO | — | — |
| `created_at` | timestamp | YES | NULL | — |
| `updated_at` | timestamp | YES | NULL | — |

**Indexes:**
- `PRIMARY KEY (`id`)`
- `UNIQUE KEY `opponent_alias_unique` (`opponent_id`,`alias_normalized`(191))`
- `KEY `opponent_aliases_opponent_id_index` (`opponent_id`)`

### `opponent_trigrams`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `opponent_id` | bigint | NO | — | — |
| `tri` | char(3) | NO | — | — |

**Indexes:**
- `PRIMARY KEY (`opponent_id`,`tri`)`
- `KEY `opponent_trigrams_tri_index` (`tri`)`

### `option_sets`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | int | NO | — | — |
| `key` | varchar(191) | NO | — | — |
| `name_en` | varchar(191) | NO | — | — |
| `name_ar` | varchar(191) | NO | — | — |
| `description_en` | text | YES | — | — |
| `description_ar` | text | YES | — | — |
| `is_active` | tinyint(1) | NO | '1' | — |
| `created_at` | timestamp | YES | NULL | — |
| `updated_at` | timestamp | YES | NULL | — |

**Indexes:**
- `PRIMARY KEY (`id`)`
- `UNIQUE KEY `option_sets_key_unique` (`key`)`

### `option_values`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | int | NO | — | AUTO_INCREMENT |
| `set_id` | bigint | NO | — | — |
| `code` | varchar(191) | NO | — | — |
| `label_en` | varchar(191) | NO | — | — |
| `label_ar` | varchar(191) | NO | — | — |
| `position` | int | NO | '0' | — |
| `is_active` | tinyint(1) | NO | '1' | — |
| `created_at` | timestamp | YES | NULL | — |
| `updated_at` | timestamp | YES | NULL | — |

**Indexes:**
- `PRIMARY KEY (`id`)`
- `UNIQUE KEY `option_values_set_id_code_unique` (`set_id`,`code`)`
- `KEY `option_values_set_id_is_active_position_index` (`set_id`,`is_active`,`position`)`

### `password_resets`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `email` | varchar(191) | NO | — | — |
| `token` | varchar(191) | NO | — | — |
| `created_at` | timestamp | YES | NULL | — |

**Indexes:**
- `KEY `password_resets_email_index` (`email`)`

### `password_reset_tokens`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `email` | varchar(191) | NO | — | — |
| `token` | varchar(191) | NO | — | — |
| `created_at` | timestamp | YES | NULL | — |

**Indexes:**
- `PRIMARY KEY (`email`)`

### `permissions`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | bigint | NO | — | AUTO_INCREMENT |
| `name` | varchar(125) | NO | — | — |
| `guard_name` | varchar(125) | NO | — | — |
| `created_at` | timestamp | YES | NULL | — |
| `updated_at` | timestamp | YES | NULL | — |

**Indexes:**
- `PRIMARY KEY (`id`)`
- `UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)`

### `personal_access_tokens`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | bigint | NO | — | AUTO_INCREMENT |
| `tokenable_type` | varchar(191) | NO | — | — |
| `tokenable_id` | bigint | NO | — | — |
| `name` | varchar(191) | NO | — | — |
| `token` | varchar(64) | NO | — | — |
| `abilities` | text | YES | — | — |
| `last_used_at` | timestamp | YES | NULL | — |
| `expires_at` | timestamp | YES | NULL | — |
| `created_at` | timestamp | YES | NULL | — |
| `updated_at` | timestamp | YES | NULL | — |

**Indexes:**
- `PRIMARY KEY (`id`)`
- `UNIQUE KEY `personal_access_tokens_token_unique` (`token`)`
- `KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)`

### `power_of_attorneys`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | int | NO | — | — |
| `client_id` | bigint | NO | — | — |
| `client_print_name` | varchar(191) | YES | NULL | — |
| `principal_name` | varchar(191) | NO | — | — |
| `year` | int | YES | NULL | — |
| `capacity` | varchar(191) | YES | NULL | — |
| `authorized_lawyers` | text | YES | — | — |
| `issue_date` | date | YES | NULL | — |
| `inventory` | tinyint(1) | NO | '1' | — |
| `issuing_authority` | varchar(191) | YES | NULL | — |
| `letter` | varchar(191) | YES | NULL | — |
| `poa_number` | int | YES | NULL | — |
| `principal_capacity` | varchar(191) | YES | NULL | — |
| `copies_count` | int | YES | NULL | — |
| `serial` | varchar(191) | YES | NULL | — |
| `notes` | text | YES | — | — |
| `created_by` | bigint | YES | NULL | — |
| `updated_by` | bigint | YES | NULL | — |
| `created_at` | timestamp | YES | NULL | — |
| `updated_at` | timestamp | YES | NULL | — |
| `deleted_at` | timestamp | YES | NULL | — |

**Indexes:**
- `PRIMARY KEY (`id`)`
- `KEY `power_of_attorneys_client_id_index` (`client_id`)`
- `KEY `power_of_attorneys_issue_date_index` (`issue_date`)`
- `KEY `power_of_attorneys_poa_number_index` (`poa_number`)`
- `KEY `power_of_attorneys_created_by_foreign` (`created_by`)`
- `KEY `power_of_attorneys_updated_by_foreign` (`updated_by`)`

### `roles`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | bigint | NO | — | AUTO_INCREMENT |
| `name` | varchar(125) | NO | — | — |
| `guard_name` | varchar(125) | NO | — | — |
| `created_at` | timestamp | YES | NULL | — |
| `updated_at` | timestamp | YES | NULL | — |

**Indexes:**
- `PRIMARY KEY (`id`)`
- `UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)`

### `role_has_permissions`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `permission_id` | bigint | NO | — | — |
| `role_id` | bigint | NO | — | — |

**Indexes:**
- `PRIMARY KEY (`permission_id`,`role_id`)`
- `KEY `role_has_permissions_role_id_foreign` (`role_id`)`

### `users`
| Column | DB Type | Null | Default | Extra |
|---|---|---|---|---|
| `id` | bigint | NO | — | AUTO_INCREMENT |
| `name` | varchar(191) | NO | — | — |
| `email` | varchar(191) | NO | — | — |
| `email_verified_at` | timestamp | YES | NULL | — |
| `password` | varchar(191) | NO | — | — |
| `locale` | varchar(2) | NO | 'en' | — |
| `remember_token` | varchar(100) | YES | NULL | — |
| `created_at` | timestamp | YES | NULL | — |
| `updated_at` | timestamp | YES | NULL | — |

**Indexes:**
- `PRIMARY KEY (`id`)`
- `UNIQUE KEY `users_email_unique` (`email`)`
