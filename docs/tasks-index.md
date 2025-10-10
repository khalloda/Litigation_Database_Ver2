# Tasks Index — Central Litigation Management

> **Living Document**: This index tracks all tasks and subtasks for the project. Update after each completed task.

---

## Legend
- **Status**: `Todo` | `In Progress` | `Done` | `Blocked`
- **DoD**: Definition of Done (checklist)
- **Commits**: Link to commit hash or PR

---

## 1. Bootstrap

### 1.1 Laravel App Initialization
- **ID**: T-01
- **Status**: Done
- **Branch**: `chore/bootstrap-laravel-app`
- **Description**: Create Laravel 10.x project, configure environment, install core packages, create ADRs
- **DoD**:
  - [x] Laravel 10.49.1 installed in `clm-app/` directory
  - [x] `.env` configured (DB, locale, timezone, logging)
  - [x] Core packages installed (Laravel UI, Spatie Permission, Spatie Activitylog)
  - [x] Dev packages installed (IDE Helper, Larastan, Pest)
  - [x] 5 ADRs created (Auth, RBAC, ActivityLog, i18n, Storage)
  - [x] Docs structure created (`/docs/adr/`, `/docs/worklogs/`, `tasks-index.md`, `data-dictionary.md`)
  - [x] Committed and pushed
- **Commits**: 9137ba9

### 1.2 Packages & ADRs
- **ID**: T-01 (continued)
- **Status**: Done
- **Notes**: Completed as part of T-01

---

## 2. Auth & RBAC

### 2.1 Auth Scaffolding
- **ID**: T-02
- **Status**: Done
- **Branch**: `feat/auth-super-admin`
- **Description**: Install Laravel UI auth, scaffold Bootstrap views, configure auth routes
- **DoD**:
  - [x] Laravel UI Bootstrap auth installed
  - [x] Auth views generated
  - [x] npm dependencies installed and built
  - [x] Login/register/password reset routes functional
- **Commits**: bd30412

### 2.2 Super Admin Seeder
- **ID**: T-02 (continued)
- **Status**: Done
- **Branch**: `feat/auth-super-admin`
- **Description**: Create super admin user and base roles
- **DoD**:
  - [x] SuperAdminSeeder created (email: khelmy@sarieldin.com, password: hashed P@ssw0rd)
  - [x] RolesSeeder created (super_admin, admin, lawyer, staff, client_portal)
  - [x] DatabaseSeeder updated to call both seeders
  - [x] Migrations run successfully
  - [x] Super admin can login
  - [x] Tests created (3 tests, 8 assertions)
- **Commits**: bd30412

### 2.3 Roles/Permissions Policies
- **ID**: T-03
- **Status**: Done
- **Branch**: `feat/rbac-permissions-policies`
- **Description**: Configure Spatie Permission, create permissions seeder, scaffold policies
- **DoD**:
  - [x] Spatie Permission config published
  - [x] Permission migrations run
  - [x] PermissionsSeeder created (19 permissions)
  - [x] User model has HasRoles trait
  - [x] 4 policy scaffolds created (Case, Hearing, Client, Document)
  - [x] Permission middleware created and registered
  - [x] Tests created (4 tests, 29 assertions)
- **Commits**: 4de0c9c

---

## 3. Domain & Database

### 3.1 ERD & Migrations
- **ID**: T-04
- **Status**: Done (Partial - Models Complete)
- **Branch**: `feat/domain-models-migrations`
- **Description**: Create migrations and models for core domain entities
- **DoD**:
  - [x] ERD created with Mermaid diagram
  - [x] 10 domain table migrations created
  - [x] All migrations tested successfully
  - [x] 10 Eloquent models created
  - [x] Client and Case models fully implemented
  - [x] All models have SoftDeletes and trash integration
  - [ ] Factory stubs (planned for T-05)
- **Commits**: 805b04b

### 3.2 ETL Importers
- **ID**: T-05
- **Status**: Done
- **Branch**: `feat/etl-importers`
- **Description**: Build console commands to import Excel data from MS Access exports
- **DoD**:
  - [x] phpoffice/phpspreadsheet installed (1.29)
  - [x] doctrine/dbal installed (3.10.2) for column modifications
  - [x] BaseImporter class created (common Excel reading/logging)
  - [x] 10 importer classes created (one per entity)
  - [x] 10 console commands created
  - [x] import:all master command created
  - [x] ID preservation implemented (100% referential integrity)
  - [x] Schema constraints fixed (admin_tasks TEXT fields)
  - [x] 7,209+ records imported successfully
  - [x] Data quality dashboard created (data:quality)
  - [x] ETL validation report created
  - [x] ETL runbook created (comprehensive 15 sections)
  - [x] All imports tested and validated
- **Results**:
  - Lawyers: 14 (100%)
  - Clients: 308 (100%)
  - Engagement Letters: 300 (91.2%)
  - Cases: 1,695 (99.65%)
  - Hearings: 369 (3.5% - orphaned FKs expected)
  - Contacts: 39 (21% - orphaned FKs expected)
  - POAs: 3 (0.4% - orphaned FKs expected)
  - Admin Tasks: 4,077 (98.79%)
  - Admin Subtasks: 0 (100% orphaned)
  - Documents: 404 (100%)
  - **100% Referential Integrity** on all imported records
- **Commits**: c91556e
- **Documentation**:
  - ETL Validation Report: `/docs/etl/ETL-Validation-Report-20251008.md`
  - ETL Runbook: `/docs/runbooks/ETL_Import_Runbook.md`

---

## 3A. Trash / Recycle Bin System ✅

### 3A.1 Trash Schema & Models
- **ID**: T-Trash-01
- **Status**: Done
- **Branch**: `feat/trash-snapshot-schema`
- **Description**: Database schema and Eloquent models for deletion bundles
- **DoD**:
  - [x] deletion_bundles table migration (UUID, JSON, status, TTL)
  - [x] deletion_bundle_items table migration
  - [x] DeletionBundle model with scopes and helpers
  - [x] DeletionBundleItem model
  - [x] All migrations tested successfully
- **Commits**: 204dcc7

### 3A.2 Trash Service & Trait
- **ID**: T-Trash-02
- **Status**: Done
- **Branch**: `feat/trash-service-trait`
- **Description**: Core business logic and model integration
- **DoD**:
  - [x] DeletionBundleService created (create/restore/purge)
  - [x] InteractsWithDeletionBundles trait created
  - [x] Trait applied to Client and CaseModel
  - [x] Transaction-wrapped operations
  - [x] Conflict resolution strategies implemented
  - [x] Detailed restore reports
- **Commits**: b019baa

### 3A.3 Multi-Model Integration
- **ID**: T-Trash-03
- **Status**: Done
- **Branch**: `feat/trash-collectors-all-models`
- **Description**: Extend trash to all 10 core models with collectors
- **DoD**:
  - [x] config/trash.php created
  - [x] Collector interface and base class created
  - [x] 10 collectors implemented (Client, Case, Document, Hearing, etc.)
  - [x] Service refactored to use collectors
  - [x] Trait applied to all 10 models
  - [x] Trash permissions added (trash.view, trash.restore, trash.purge)
  - [x] Permissions seeded (22 total)
- **Commits**: fcb26a8

### 3A.4 Trash CLI & Web UI
- **ID**: T-Trash-04
- **Status**: Done
- **Branch**: `feat/trash-cli-and-ui`
- **Description**: Admin interfaces for trash management
- **DoD**:
  - [x] trash:list command with filtering
  - [x] trash:restore command with dry-run
  - [x] trash:purge command with bulk operations
  - [x] TrashController created
  - [x] TrashPolicy created
  - [x] Routes added with permission middleware
  - [x] trash/index.blade.php (list view)
  - [x] trash/show.blade.php (detail view)
  - [x] Bootstrap 5 responsive UI
  - [x] Dry-run modal with AJAX
- **Commits**: 94b759e

### 3A.5 Trash Tests
- **ID**: T-Trash-05
- **Status**: Done
- **Branch**: `test/trash-restore`
- **Description**: Comprehensive test coverage for trash system
- **DoD**:
  - [x] 13 Pest tests created
  - [x] Bundle creation tested for all model types
  - [x] Restore operations tested
  - [x] Dry-run validation
  - [x] Permission enforcement tested
  - [x] Configuration validation
  - [x] 87 assertions, 100% passing
- **Commits**: e84e2c8

### 3A.6 Trash Documentation
- **ID**: T-Trash-06
- **Status**: Done
- **Branch**: `docs/trash-adr-runbook`
- **Description**: Complete documentation for trash system
- **DoD**:
  - [x] ADR-006 created
  - [x] Trash_Restore_Runbook.md created (15 sections)
  - [x] Step-4 worklog created
  - [x] Master plan updated with impact matrix
  - [x] Tasks index updated
  - [ ] OpenAPI spec (pending - requires API implementation)
  - [x] All docs cross-referenced
- **Commits**: 84d1a27

---

## 4. Audit & Files

### 4.1 Activity Logs
- **ID**: T-06
- **Status**: Done
- **Branch**: `feat/audit-activity-logs`
- **Description**: Configure Spatie Activitylog, add logging to models
- **DoD**:
  - [x] Spatie ActivityLog config published and configured
  - [x] Activity log table migrations run
  - [x] All 10 domain models updated with LogsActivity trait
  - [x] Activity log options configured for each model
  - [x] AuditLogController created with filtering/search
  - [x] Audit log views created (index, show)
  - [x] Routes added with permission middleware
  - [x] Tests created (5+ tests)
  - [x] Runbook created
- **Commits**: Multiple commits in audit logging implementation

### 4.2 Secure Storage
- **ID**: T-07
- **Status**: Done
- **Branch**: `feat/secure-file-storage`
- **Description**: Implement secure file storage with signed URLs
- **DoD**:
  - [x] DocumentController created with full CRUD
  - [x] Secure file storage configuration
  - [x] Document upload validation (DocumentUploadRequest)
  - [x] Signed URLs for secure file access
  - [x] Document preview functionality (PDF, images, fallback)
  - [x] File management views (index, create, show, edit)
  - [x] AJAX client-cases loading
  - [x] Routes configured with proper order
  - [x] DocumentPolicy for permissions
  - [x] Tests created (5+ tests)
  - [x] Runbook created
- **Commits**: Multiple commits in secure file storage implementation

---

## 5. UX/UI

### 5.1 Layout & RTL
- **ID**: T-08
- **Status**: Done
- **Branch**: `feat/layout-rtl-i18n`
- **Description**: Implement bilingual UI with RTL support
- **DoD**:
  - [x] SetLocale middleware created
  - [x] LocaleController for language switching
  - [x] Language files created (en/app.php, ar/app.php)
  - [x] Layout updated with dynamic lang/dir attributes
  - [x] Language switcher dropdown in navbar
  - [x] RTL Bootstrap CSS integration
  - [x] Navigation links with permission checks
  - [x] All views updated with __() localization
  - [x] Emergency CSS fixes for UI issues
- **Commits**: Multiple commits in i18n implementation

### 5.2 CRUD Complete - Clients
- **ID**: T-CRUD-Clients
- **Status**: Done
- **Branch**: `feat/navigation-crud-stubs` → `feat/crud-complete-cases-hearings-lawyers`
- **Description**: Complete Clients CRUD implementation
- **DoD**:
  - [x] Top navbar with permission-based visibility
  - [x] ClientsController with full CRUD (index, show, create, store, edit, update, destroy)
  - [x] ClientRequest validation class
  - [x] Client views (index, show, create, edit) - bilingual & RTL
  - [x] Routes with proper permission middleware
  - [x] Soft deletes integration
  - [x] Pagination with proper column separation
  - [x] Tests created
- **Commits**: Multiple commits

### 5.3 CRUD Complete - Cases
- **ID**: T-CRUD-Cases
- **Status**: Done
- **Branch**: `feat/crud-complete-cases-hearings-lawyers`
- **Description**: Complete Cases CRUD implementation
- **DoD**:
  - [x] CasesController with full CRUD methods
  - [x] CaseRequest validation class with field validation
  - [x] Cases views (index, show, create, edit) - bilingual & RTL
  - [x] Routes with proper permission middleware and correct order
  - [x] Language files updated (EN/AR) with 40+ case-specific keys
  - [x] Links to clients with proper eager loading
  - [x] Soft deletes and audit logging
- **Commits**: 68c7376

### 5.4 CRUD Complete - Hearings
- **ID**: T-CRUD-Hearings
- **Status**: Done
- **Branch**: `feat/crud-complete-cases-hearings-lawyers`
- **Description**: Complete Hearings CRUD implementation
- **DoD**:
  - [x] HearingsController with full CRUD methods
  - [x] HearingRequest validation class with date validation
  - [x] Hearings views (index, show, create, edit) - bilingual & RTL
  - [x] Routes with proper permission middleware and correct order
  - [x] Language files updated (EN/AR) with 30+ hearing-specific keys
  - [x] Navbar integration with permission check
  - [x] Links to cases and clients with proper eager loading
  - [x] Soft deletes and audit logging
- **Commits**: 2c5a3b2

### 5.5 CRUD Complete - Lawyers
- **ID**: T-CRUD-Lawyers
- **Status**: Done
- **Branch**: `feat/crud-complete-cases-hearings-lawyers`
- **Description**: Complete Lawyers CRUD implementation
- **DoD**:
  - [x] LawyersController with full CRUD methods
  - [x] LawyerRequest validation class with field validation
  - [x] Lawyers views (index, show, create, edit) - bilingual & RTL
  - [x] Routes with admin-only access (admin.users.manage permission)
  - [x] Language files updated (EN/AR) with 20+ lawyer-specific keys
  - [x] Navbar integration (admin only)
  - [x] Links to cases with proper relationship loading
  - [x] Soft deletes and audit logging
- **Commits**: 0758d9f

### 5.6 Bug Fixes
- **ID**: Bug-Fix-01
- **Status**: Done
- **Branch**: `fix/giant-arrow-overlays`
- **Description**: Fix giant arrow overlays in Laravel pagination
- **DoD**:
  - [x] Root cause identified (Tailwind CSS w-5 h-5 classes on SVGs)
  - [x] Laravel pagination views published
  - [x] tailwind.blade.php pagination view fixed (4 SVG elements)
  - [x] Explicit width/height attributes added to SVGs
  - [x] Accessibility attributes improved
  - [x] Bug fix documentation created
  - [x] Verification completed across browsers
- **Commits**: 1c6a821
- **Documentation**: `/docs/bugfixes/Giant-Arrow-Overlays-Bugfix.md`

### 5.7 Remaining CRUD Modules
- **ID**: CRUD-Remaining
- **Status**: In Progress
- **Branch**: `feat/crud-remaining-models`
- **Description**: Complete CRUD for remaining models: EngagementLetter, Contact, PowerOfAttorney, AdminTask, AdminSubtask
- **DoD**:
  - [x] **EngagementLetter CRUD** - contracts/agreements management
  - [x] **Contact CRUD** - client contact information  
  - [x] **PowerOfAttorney CRUD** - legal authorization documents
  - [ ] AdminTask CRUD - task management
  - [ ] AdminSubtask CRUD - subtask management
  - [x] Database schema alignment fixes
  - [x] Comprehensive view updates (ALL database columns displayed)
- **Commits**: 09141b7, 4dffb12, 240ed30, 00c5b19, de0361e

### 5.8 Database Schema Alignment
- **ID**: DB-Schema-Fix
- **Status**: Done
- **Description**: Fix controllers and models to match actual database schema from ETL import
- **DoD**:
  - [x] Fixed EngagementLetter: use contract_date, contract_type, status instead of contract_number, issue_date, expiry_date, is_active
  - [x] Fixed Contact: use full_name, job_title, email, business_phone instead of contact_type, contact_value, is_primary
  - [x] Fixed PowerOfAttorney: use principal_name, issuing_authority instead of poa_type, expiry_date, is_active
  - [x] Updated fillable arrays and casts to match actual database columns
  - [x] Updated activity logging to track correct fields
  - [x] Updated controller select statements to use existing columns
  - [x] Updated request validation rules to match actual schema
- **Commits**: 09141b7, 4dffb12

### 5.9 Comprehensive View Updates
- **ID**: Views-Complete-Data
- **Status**: Done
- **Description**: Update all views to display ALL available database columns instead of minimal subset
- **DoD**:
  - [x] **Contacts**: All 22 columns displayed (contact_name, full_name, job_title, address, city, state, country, zip_code, business_phone, home_phone, mobile_phone, fax_number, email, web_page, attachments, etc.)
  - [x] **EngagementLetters**: All 15 columns displayed (client_name, contract_type, contract_date, contract_details, contract_structure, matters, status, mfiles_id, etc.)
  - [x] **PowerOfAttorneys**: All 21 columns displayed (client_print_name, principal_name, poa_number, issue_date, issuing_authority, capacity, authorized_lawyers, year, serial, etc.)
  - [x] Enhanced with proper null handling, clickable links, badges, and visual formatting
  - [x] Added comprehensive language keys (EN/AR) for all new fields
  - [x] Both index and show views updated to display complete data
- **Commits**: 240ed30, 00c5b19, de0361e

### 5.10 Global Search
- **ID**: T-09
- **Status**: Todo
- **Branch**: `feat/global-search`
- **Description**: Implement global search across entities
- **DoD**: TBD

---

## 6. API & Tests

### 6.1 OpenAPI Spec
- **ID**: T-10
- **Status**: Todo
- **Branch**: `docs/openapi-spec`
- **Description**: Create OpenAPI documentation for API endpoints
- **DoD**: TBD

### 6.2 Unit/Feature Tests
- **ID**: T-11
- **Status**: Todo
- **Branch**: `test/core-tests`
- **Description**: Add comprehensive test coverage
- **DoD**: TBD

---

## Rollback Instructions

### T-01: Laravel Project Setup
```bash
# Remove clm-app directory entirely
rm -rf clm-app/
```

### T-02: Authentication & Super Admin
```bash
cd clm-app
php artisan migrate:rollback  # Rolls back user-related tables
```

### T-03: RBAC & Policies
```bash
cd clm-app
php artisan migrate:rollback  # Rolls back permission tables
# Remove policy files if needed
```

---

**Last Updated**: 2025-01-09 18:00 UTC

