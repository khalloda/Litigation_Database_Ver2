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
- **Status**: In Progress
- **Branch**: `chore/bootstrap-laravel-app`
- **Description**: Create Laravel 10.x project, configure environment, install core packages, create ADRs
- **DoD**:
  - [x] Laravel 10.49.1 installed in `clm-app/` directory
  - [x] `.env` configured (DB, locale, timezone, logging)
  - [x] Core packages installed (Laravel UI, Spatie Permission, Spatie Activitylog)
  - [x] Dev packages installed (IDE Helper, Larastan, Pest)
  - [x] 5 ADRs created (Auth, RBAC, ActivityLog, i18n, Storage)
  - [x] Docs structure created (`/docs/adr/`, `/docs/worklogs/`, `tasks-index.md`, `data-dictionary.md`)
  - [ ] Committed and pushed
- **Commits**: TBD

### 1.2 Packages & ADRs
- **ID**: T-01 (continued)
- **Status**: Done
- **Notes**: Completed as part of T-01

---

## 2. Auth & RBAC

### 2.1 Auth Scaffolding
- **ID**: T-02
- **Status**: Todo
- **Branch**: `feat/auth-super-admin`
- **Description**: Install Laravel UI auth, scaffold Bootstrap views, configure auth routes
- **DoD**:
  - [ ] Laravel UI Bootstrap auth installed
  - [ ] Auth views generated
  - [ ] npm dependencies installed and built
  - [ ] Login/register/password reset routes functional
- **Commits**: TBD

### 2.2 Super Admin Seeder
- **ID**: T-02 (continued)
- **Status**: Todo
- **Branch**: `feat/auth-super-admin`
- **Description**: Create super admin user and base roles
- **DoD**:
  - [ ] SuperAdminSeeder created (email: khelmy@sarieldin.com, password: hashed P@ssw0rd)
  - [ ] RolesSeeder created (super_admin, admin, lawyer, staff, client_portal)
  - [ ] DatabaseSeeder updated to call both seeders
  - [ ] Migrations run successfully
  - [ ] Super admin can login
- **Commits**: TBD

### 2.3 Roles/Permissions Policies
- **ID**: T-03
- **Status**: Todo
- **Branch**: `feat/rbac-permissions-policies`
- **Description**: Configure Spatie Permission, create permissions seeder, scaffold policies
- **DoD**:
  - [ ] Spatie Permission config published
  - [ ] Permission migrations run
  - [ ] PermissionsSeeder created (20+ permissions)
  - [ ] User model has HasRoles trait
  - [ ] 4 policy scaffolds created (Case, Hearing, Client, Document)
  - [ ] Permission middleware created and registered
  - [ ] Tests created
- **Commits**: TBD

---

## 3. Domain & Database

### 3.1 ERD & Migrations
- **ID**: T-04
- **Status**: Todo
- **Branch**: `feat/domain-models-migrations`
- **Description**: Create migrations and models for core domain entities
- **DoD**: TBD

### 3.2 ETL Importers
- **ID**: T-05
- **Status**: Todo
- **Branch**: `feat/etl-importers`
- **Description**: Build console commands to import Excel data
- **DoD**: TBD

---

## 4. Audit & Files

### 4.1 Activity Logs
- **ID**: T-06
- **Status**: Todo
- **Branch**: `feat/audit-activity-logs`
- **Description**: Configure Spatie Activitylog, add logging to models
- **DoD**: TBD

### 4.2 Secure Storage
- **ID**: T-07
- **Status**: Todo
- **Branch**: `feat/secure-file-storage`
- **Description**: Implement secure file storage with signed URLs
- **DoD**: TBD

---

## 5. UX/UI

### 5.1 Layout & RTL
- **ID**: T-08
- **Status**: Todo
- **Branch**: `feat/layout-rtl-i18n`
- **Description**: Implement bilingual UI with RTL support
- **DoD**: TBD

### 5.2 Global Search
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

**Last Updated**: 2025-10-08 14:03 UTC

