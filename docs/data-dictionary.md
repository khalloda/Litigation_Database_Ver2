# Data Dictionary — Central Litigation Management

> **Living Document**: This dictionary describes the database schema, including tables, columns, types, constraints, and relationships. Update when migrations change the schema.

---

## Metadata
- **Database**: `litigation_db_ver2`
- **DBMS**: MySQL 9.1.0
- **Charset**: `utf8mb4`
- **Collation**: `utf8mb4_unicode_ci`
- **Timezone**: `Africa/Cairo`

---

## Standard Conventions

### Audit Columns
All core tables include the following audit columns:
- `created_by` (unsignedBigInteger, nullable, foreign key to `users.id`)
- `updated_by` (unsignedBigInteger, nullable, foreign key to `users.id`)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)
- `ip_address` (string, nullable, optional)
- `user_agent` (text, nullable, optional)

### Soft Deletes
Core domain tables support soft deletes:
- `deleted_at` (timestamp, nullable)

### Primary Keys
- Default: `id` (unsignedBigInteger, auto-increment)

### Foreign Key Naming
- Convention: `{related_table}_id` (e.g., `client_id`, `matter_id`, `lawyer_id`)

---

## Tables

### users
**Description**: System users (lawyers, staff, admins, clients)

| Column | Type | NULL | Default | Constraints | Description |
|---|---|:---:|---|---|---|
| id | unsignedBigInteger | NO | AUTO | PK | User ID |
| name | string(255) | NO | - | - | Full name |
| email | string(255) | NO | - | UNIQUE | Email address |
| email_verified_at | timestamp | YES | NULL | - | Email verification timestamp |
| password | string(255) | NO | - | - | Hashed password |
| locale | string(2) | YES | 'en' | - | Preferred locale (en, ar) |
| remember_token | string(100) | YES | NULL | - | Remember me token |
| created_at | timestamp | YES | NULL | - | Record creation |
| updated_at | timestamp | YES | NULL | - | Last update |

**Indexes**:
- PRIMARY: `id`
- UNIQUE: `email`

**Relationships**:
- Has many: `roles` (via Spatie Permission)
- Has many: `permissions` (via Spatie Permission)
- Has many: `activities` (via Spatie Activitylog)

---

### roles
**Description**: User roles (managed by Spatie Permission)

| Column | Type | NULL | Default | Constraints | Description |
|---|---|:---:|---|---|---|
| id | unsignedBigInteger | NO | AUTO | PK | Role ID |
| name | string(255) | NO | - | UNIQUE | Role name |
| guard_name | string(255) | NO | - | - | Guard name |
| created_at | timestamp | YES | NULL | - | Record creation |
| updated_at | timestamp | YES | NULL | - | Last update |

**Indexes**:
- PRIMARY: `id`
- UNIQUE: `name`, `guard_name`

---

### permissions
**Description**: System permissions (managed by Spatie Permission)

| Column | Type | NULL | Default | Constraints | Description |
|---|---|:---:|---|---|---|
| id | unsignedBigInteger | NO | AUTO | PK | Permission ID |
| name | string(255) | NO | - | UNIQUE | Permission name |
| guard_name | string(255) | NO | - | - | Guard name |
| created_at | timestamp | YES | NULL | - | Record creation |
| updated_at | timestamp | YES | NULL | - | Last update |

**Indexes**:
- PRIMARY: `id`
- UNIQUE: `name`, `guard_name`

---

### activity_log
**Description**: Audit trail of all system activities (managed by Spatie Activitylog)

| Column | Type | NULL | Default | Constraints | Description |
|---|---|:---:|---|---|---|
| id | unsignedBigInteger | NO | AUTO | PK | Activity ID |
| log_name | string(255) | YES | NULL | - | Log category |
| description | text | NO | - | - | Activity description |
| subject_type | string(255) | YES | NULL | - | Subject model class |
| subject_id | unsignedBigInteger | YES | NULL | - | Subject model ID |
| causer_type | string(255) | YES | NULL | - | Causer model class |
| causer_id | unsignedBigInteger | YES | NULL | - | Causer model ID |
| properties | json | YES | NULL | - | Additional properties |
| created_at | timestamp | YES | NULL | - | Activity timestamp |
| updated_at | timestamp | YES | NULL | - | Last update |

**Indexes**:
- PRIMARY: `id`
- INDEX: `subject_type`, `subject_id`
- INDEX: `causer_type`, `causer_id`
- INDEX: `log_name`

---

## Domain Tables (Created and Populated)

### clients
**Description**: Client organizations/individuals  
**Source**: `clients.xlsx`  
**Records**: 308 (100% imported)  
**Key Columns**: `client_name_ar`, `client_name_en`, `client_type`, `contact_person`, `contact_email`, `contact_phone`, `address`, `notes`

### cases (matters)
**Description**: Legal cases/matters  
**Source**: `cases.xlsx`  
**Records**: 1,695 (99.65% imported)  
**Key Columns**: `client_id`, `matter_name_ar`, `matter_name_en`, `matter_status`, `matter_description`, `start_date`, `end_date`

### hearings
**Description**: Court hearings and sessions  
**Source**: `hearings.xlsx`  
**Records**: 369 (3.5% imported - orphaned FKs expected)  
**Key Columns**: `case_id`, `hearing_date`, `hearing_time`, `court_name`, `judge_name`, `hearing_type`, `status`

### lawyers
**Description**: Lawyer profiles  
**Source**: `lawyers.xlsx`  
**Records**: 14 (100% imported)  
**Key Columns**: `name`, `email`, `phone`, `specialization`, `bar_number`, `status`

### contacts
**Description**: Client contacts  
**Source**: `contacts.xlsx`  
**Records**: 39 (21% imported - orphaned FKs expected)  
**Key Columns**: `client_id`, `name`, `email`, `phone`, `position`, `is_primary`

### engagement_letters
**Description**: Fee engagement letters  
**Source**: `engagement_letters.xlsx`  
**Records**: 300 (91.2% imported)  
**Key Columns**: `client_id`, `case_id`, `letter_number`, `letter_date`, `fee_amount`, `currency`, `terms`

### power_of_attorneys
**Description**: Power of attorney documents  
**Source**: `power_of_attorneys.xlsx`  
**Records**: 3 (0.4% imported - orphaned FKs expected)  
**Key Columns**: `client_id`, `poa_number`, `poa_date`, `poa_type`, `grantor_name`, `grantee_name`

### admin_tasks
**Description**: Administrative work tasks  
**Source**: `admin_work_tasks.xlsx`  
**Records**: 4,077 (98.79% imported)  
**Key Columns**: `case_id`, `task_name`, `task_description`, `assigned_to`, `priority`, `status`, `due_date`, `completed_date`, `last_follow_up`

### admin_subtasks
**Description**: Subtasks under admin tasks  
**Source**: `admin_work_subtasks.xlsx`  
**Records**: 0 (100% orphaned)  
**Key Columns**: `admin_task_id`, `subtask_name`, `subtask_description`, `assigned_to`, `status`, `due_date`

### client_documents
**Description**: Uploaded legal documents  
**Source**: `clients_matters_documents.xlsx`  
**Records**: 404 (100% imported)  
**Key Columns**: `client_id`, `case_id`, `document_name`, `document_type`, `document_description`, `file_path`, `file_size`, `mime_type`, `uploaded_by`

---

## System Tables (Additional)

### deletion_bundles
**Description**: Trash/recycle bin system for soft-deleted entities  
**Records**: Dynamic (created on deletions)  
**Key Columns**: `id` (UUID), `name`, `description`, `deleted_by`, `deleted_at`, `ttl_days`, `status`, `restore_conflicts`, `created_at`

### deletion_bundle_items
**Description**: Individual items within deletion bundles  
**Records**: Dynamic (created on deletions)  
**Key Columns**: `id` (UUID), `deletion_bundle_id`, `model_type`, `model_id`, `snapshot` (JSON), `file_descriptors` (JSON), `created_at`

---

## ERD

> **Note**: ERD diagram available in `/docs/erd.md` with complete relationship mapping.

---

## Change Log

| Date | Version | Changes | By |
|---|---|---|---|
| 2025-10-08 | 1.0 | Initial schema with users, roles, permissions, activity_log | System |
| 2025-10-08 | 1.1 | Added domain tables after ETL import completion | System |
| 2025-01-09 | 1.2 | Updated with import statistics and system tables | System |
| 2025-01-09 | 1.3 | Database schema alignment - fixed column mismatches, comprehensive view updates | System |

---

**Last Updated**: 2025-01-09 15:45 UTC

