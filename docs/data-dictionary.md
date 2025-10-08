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

## Planned Tables (To Be Created in Future Tasks)

### clients
**Description**: Client organizations/individuals  
**Source**: `clients.xlsx`

### cases (matters)
**Description**: Legal cases/matters  
**Source**: `cases.xlsx`

### hearings
**Description**: Court hearings and sessions  
**Source**: `hearings.xlsx`

### lawyers
**Description**: Lawyer profiles  
**Source**: `lawyers.xlsx`

### contacts
**Description**: Client contacts  
**Source**: `contacts.xlsx`

### engagement_letters
**Description**: Fee engagement letters  
**Source**: `engagement_letters.xlsx`

### power_of_attorneys
**Description**: Power of attorney documents  
**Source**: `power_of_attorneys.xlsx`

### admin_tasks
**Description**: Administrative work tasks  
**Source**: `admin_work_tasks.xlsx`

### admin_subtasks
**Description**: Subtasks under admin tasks  
**Source**: `admin_work_subtasks.xlsx`

### client_documents
**Description**: Uploaded legal documents  
**Source**: `clients_matters_documents.xlsx`

---

## ERD

> **Note**: ERD diagram will be added in Task T-04 after finalizing the domain model structure.

---

## Change Log

| Date | Version | Changes | By |
|---|---|---|---|
| 2025-10-08 | 1.0 | Initial schema with users, roles, permissions, activity_log | System |

---

**Last Updated**: 2025-10-08 14:04 UTC

