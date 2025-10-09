# Central Litigation Management — Master Plan

**Version**: 1.2  
**Date**: 2025-01-09  
**Status**: In Progress  

---

## Executive Summary

Central Litigation Management (CLM) is a bilingual (EN/AR) web application for managing legal cases, clients, hearings, documents, and administrative workflows. Built on Laravel 10.x with MySQL 9.1.0, the system provides comprehensive case management with enterprise-grade features including RBAC, audit logging, and restorable deletion bundles.

---

## Technology Stack

| Component | Technology | Version |
|---|---|---|
| Backend Framework | Laravel | 10.49.1 |
| Language | PHP | 8.4.0 |
| Database | MySQL | 9.1.0 |
| Frontend | Bootstrap | 5.x |
| Testing | Pest | 2.36.0 |
| RBAC | Spatie Permission | 6.21.0 |
| Audit Log | Spatie ActivityLog | 4.10.2 |
| Web Server | Apache (WAMP) | 2.4.62 |

---

## Project Phases

### Phase 1: Foundation ✅ (Complete)
**Duration**: 1 week  
**Status**: Done  

- Laravel project setup
- Authentication (Laravel UI)
- RBAC (Spatie Permission)
- Core domain models & migrations
- Basic policies & middleware

### Phase 2: Data Recovery & Safety ✅ (Complete)
**Duration**: 1 week  
**Status**: Done  

- **Restorable Trash System** (Snapshot Bundles)
- Deletion bundle capture for all models
- Restore with conflict resolution
- CLI commands & web UI
- Comprehensive testing

### Phase 3: Data Migration ✅ (Complete)
**Duration**: 2 weeks  
**Status**: Done  

- ETL importers (Excel → MySQL)
- Data validation & transformation
- Idempotent upserts
- Reject logging
- Data quality dashboard

### Phase 4: Audit & Security ✅ (Complete)
**Duration**: 1 week  
**Status**: Done  

- Activity logging integration (Spatie ActivityLog)
- Audit log viewer with filtering/search
- Secure file storage with signed URLs
- Document management with preview

### Phase 5: User Interface (In Progress)
**Duration**: 3 weeks  
**Status**: In Progress  

- Bootstrap dashboard layout
- CRUD interfaces for all entities
- i18n & RTL support
- Global search
- Navigation system with permissions

### Phase 6: API & Integration
**Duration**: 1 week  
**Status**: Not Started  

- RESTful API
- OpenAPI documentation
- API authentication

### Phase 7: Testing & QA
**Duration**: 2 weeks  
**Status**: Ongoing  

- Unit test coverage (≥60%)
- Feature tests
- E2E tests (optional)
- Performance testing

### Phase 8: Deployment
**Duration**: 1 week  
**Status**: Not Started  

- Production environment setup
- CI/CD pipeline
- Monitoring & logging
- Backup strategy

---

## Cross-Module Impact Matrix: Trash/Snapshot Feature

| Module | Impacted? | Change Summary | Implementation Status | New Tasks | Risks & Mitigations |
|---|:---:|---|:---:|---|---|
| **Clients** | ✅ Yes | Deleting client creates bundle with full cascade (cases, contacts, POAs, documents) | ✅ Done | None | **Risk**: Large clients (500+ cases) slow to snapshot. **Mitigation**: Queue-based snapshot (future), warn users |
| **Cases** | ✅ Yes | Case deletion captures hearings, tasks, subtasks, documents | ✅ Done | None | **Risk**: Parent (client) deleted separately. **Mitigation**: Restore parent bundle first, skip orphans |
| **Documents** | ✅ Yes | Document snapshot includes file descriptors; relink on restore | ✅ Done | T-07: File storage integration | **Risk**: File physically deleted. **Mitigation**: Files not deleted on soft-delete, quarantine option (future) |
| **Hearings** | ✅ Yes | Single-entity bundles; included in case bundles | ✅ Done | None | **Risk**: Orphaned if case missing. **Mitigation**: Restore skips if parent missing |
| **Admin Work** | ✅ Yes | Tasks bundle includes subtasks; cascade restore | ✅ Done | None | **Risk**: Task ordering on restore. **Mitigation**: Collectors maintain order |
| **Lawyers** | ✅ Partial | Null-on-delete FK policy; bundle captures assignment metadata | ✅ Done | None | **Risk**: Restoring lawyer doesn't recreate all pivots. **Mitigation**: Manual re-assignment, logged in report |
| **Contacts** | ✅ Yes | Single-entity bundles with client reference | ✅ Done | None | Low risk |
| **POAs** | ✅ Yes | Single-entity bundles with client reference | ✅ Done | None | Low risk |
| **Engagement Letters** | ✅ Yes | Single-entity bundles; references to clients/cases | ✅ Done | None | **Risk**: Multiple cases reference same letter. **Mitigation**: Restore letter first |
| **Authentication** | ❌ No | N/A | N/A | None | N/A |
| **Permissions** | ✅ Yes | Added `trash.*` permissions | ✅ Done | None | Low risk |
| **Audit Log** | ✅ Yes | Bundle creation/restore/purge logged | ✅ Done | T-06: Full activity log integration | Low risk |
| **File Storage** | ⏳ Partial | File descriptors stored; physical files preserved | ⏳ Planned | T-07: Signed URLs + quarantine | **Risk**: Orphaned files. **Mitigation**: TTL-based cleanup |
| **API** | ⏳ Planned | API endpoints for trash operations | ⏳ Planned | T-10: RESTful trash API | Low risk |
| **i18n/RTL** | ⏳ Planned | Trash UI strings in EN/AR | ⏳ Planned | T-08: Localize trash UI | Low risk |
| **Reporting** | ⏳ Planned | Deletion analytics dashboard | ⏳ Planned | T-Report-Trash-Stats | Low risk |

**Legend**: ✅ Done | ⏳ Planned | ❌ No Impact

---

## Milestones

### M1: Foundation Complete ✅
**Date**: 2025-10-08  
- Laravel setup
- Auth & RBAC
- Domain models
- **Trash system complete**

### M2: Data Migration Complete ✅
**Date**: 2025-10-08  
- ETL importers
- Data validation
- Initial data loaded
- Data quality dashboard

### M3: Audit & Security Complete ✅
**Date**: 2025-10-09  
- Activity logging integration
- Secure file storage
- Document management

### M4: MVP UI Complete ⏳
**Target**: 2025-01-15  
- All CRUD interfaces
- i18n & RTL
- Global search
- Navigation system

### M5: Production Ready ⏳
**Target**: 2025-01-26  
- API complete
- Security hardened
- Tests ≥60% coverage
- Documentation complete

---

## Current Sprint (Sprint 2)

**Dates**: 2025-01-09 to 2025-01-15  
**Goal**: Complete UI/UX with i18n & RTL + Global Search  

**Completed**:
- [x] T-01: Laravel setup
- [x] T-02: Auth & super admin
- [x] T-03: RBAC & policies
- [x] T-04: Domain models
- [x] T-05: ETL importers
- [x] T-06: Audit logging integration
- [x] T-07: Secure file storage
- [x] T-08: i18n & RTL UI
- [x] Navigation system with permissions
- [x] **Clients CRUD (complete)** - create, read, update, delete
- [x] **Cases CRUD (complete)** - full CRUD with validation
- [x] **Hearings CRUD (complete)** - full CRUD with date validation
- [x] **Lawyers CRUD (complete)** - full CRUD (admin only)
- [x] Bug fixes (giant arrow overlays)

**In Progress**:
- [ ] Global search implementation
- [ ] Comprehensive testing coverage

**Planned Next Sprint**:
- T-09: Global search
- T-10: OpenAPI documentation  
- T-11: Comprehensive testing
- Additional CRUD modules (Contacts, Engagement Letters, Power of Attorneys, Admin Tasks)

---

## Architecture Overview

### Layered Architecture

```
┌─────────────────────────────────────────┐
│         Presentation Layer              │
│  (Blade Views, Bootstrap 5, RTL, i18n)  │
└─────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────┐
│         Application Layer               │
│    (Controllers, Requests, Policies)    │
└─────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────┐
│          Business Logic Layer           │
│    (Services, DTOs, Validation)         │
│  • DeletionBundleService                │
│  • ETLService (planned)                 │
└─────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────┐
│           Data Access Layer             │
│    (Models, Repositories, Eloquent)     │
│  • Soft Deletes + Deletion Bundles      │
└─────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────┐
│          Infrastructure Layer           │
│    (MySQL 9.1, File Storage, Cache)     │
└─────────────────────────────────────────┘
```

### Key Architectural Patterns

1. **Repository Pattern** (optional, for complex queries)
2. **Service Layer** (business logic isolation)
3. **Policy-Based Authorization** (RBAC via Spatie)
4. **Collector Pattern** (Trash: model-specific snapshot logic)
5. **Trait-Based Behaviors** (InteractsWithDeletionBundles)
6. **Event-Driven** (Model events for audit/trash)

---

## Data Model Highlights

### Core Entities (21 Tables)

**User Management**:
- users, roles, permissions, model_has_roles, model_has_permissions, role_has_permissions

**Domain Entities**:
- clients, cases, hearings, lawyers
- contacts, engagement_letters, power_of_attorneys
- admin_tasks, admin_subtasks
- client_documents

**System Features**:
- **deletion_bundles** (Trash system)
- **deletion_bundle_items** (Trash system)
- activity_log (Audit trail)
- password_reset_tokens, failed_jobs, personal_access_tokens

### Relationship Summary

```
Client (Root)
  ├── Cases (1:M cascade delete → creates bundle)
  │     ├── Hearings (1:M cascade delete)
  │     ├── Admin Tasks (1:M cascade delete)
  │     │     └── Admin Subtasks (1:M cascade delete)
  │     └── Documents (1:M nullable FK)
  ├── Contacts (1:M cascade delete)
  ├── Engagement Letters (1:M cascade delete)
  ├── Power of Attorneys (1:M cascade delete)
  └── Documents (1:M cascade delete)

Lawyer (Independent)
  ├── Hearings (1:M null on delete)
  ├── Admin Tasks (1:M null on delete)
  └── Admin Subtasks (1:M null on delete)
```

---

## Security Model

### Roles
- **super_admin**: Full system access including trash operations
- **admin**: Most operations including trash view/restore
- **lawyer**: Case management, limited admin
- **staff**: Data entry, viewing
- **client_portal**: Read-only client-specific (future)

### Trash Permissions (Added in Phase 2)
- `trash.view`: View deletion bundles
- `trash.restore`: Restore deleted entities
- `trash.purge`: Permanently purge bundles

### Total Permissions: 22
- Cases: 4 (view, create, edit, delete)
- Hearings: 4
- Documents: 4
- Clients: 4
- Admin: 3
- **Trash: 3** ← New

---

## Non-Functional Requirements

### Performance
- Page load: <2 seconds
- Query optimization: eager loading, indexes
- Large bundle snapshot: <10 seconds (Client with 100 cases)
- Pagination: 20 items per page

### Security
- RBAC enforcement at controller & route level
- Trash operations: admin+ only
- Audit trail for all bundle operations
- Signed URLs for file downloads

### Reliability
- Transaction-wrapped trash operations
- Dry-run mode for testing
- Conflict resolution strategies
- TTL-based auto-purge (90 days default)

### Usability
- Responsive Bootstrap 5 UI
- Bilingual (EN/AR with RTL)
- Clear error messages
- Confirmation prompts for destructive operations

---

## Risk Register

| Risk | Impact | Probability | Mitigation | Status |
|---|---|---|---|---|
| Accidental data deletion | High | Medium | **Trash system implemented** | ✅ Mitigated |
| Large client snapshot slow | Medium | Low | Queue-based snapshot (future), warn users | ⏳ Planned |
| Storage growth (bundles) | Medium | High | TTL auto-purge, monitoring alerts | ✅ Mitigated |
| Restore conflicts | Medium | Medium | 3 strategies + dry-run | ✅ Mitigated |
| Missing parent on restore | Low | Medium | Skip strategy, parent check | ✅ Mitigated |
| File orphaning | Medium | Low | Preserve files, descriptor tracking | ✅ Mitigated |

---

## Dependencies & Integrations

### External Dependencies
- MySQL 9.1.0
- WAMP 3.3.7
- Composer packages (managed in composer.json)
- npm packages (managed in package.json)

### Internal Module Dependencies

```
Authentication → RBAC → Policies → Controllers
                   ↓
              Trash System ← All Models
                   ↓
            Audit Logging
```

---

## Deployment Strategy

### Environments
1. **Local Development** (current): WAMP on Windows 11
2. **Staging** (planned): Linux server, MySQL 9.1
3. **Production** (planned): Hardened Linux, replicated MySQL

### Database Migration Strategy
- Version-controlled migrations
- Seeders for reference data
- ETL scripts for Access data import
- **Trash bundles**: Replicate to staging, purge before prod

---

## Monitoring & Metrics

### Key Metrics (Planned)
- Active users
- Cases created/closed per month
- Hearings scheduled
- **Deletion bundles**: Created, restored, purged (per month)
- **Bundle storage size**: Monitor growth
- Failed logins
- API response times

### Alerts (Planned)
- **Trash bundles** > 1000 unrestored
- **Large bundle** (cascade_count > 500)
- Failed restoration attempts
- Storage exceeding 80%

---

## Documentation Inventory

### Architecture Decision Records (ADRs)
- [x] ADR-001: Auth Choice (Laravel UI)
- [x] ADR-002: RBAC Choice (Spatie Permission)
- [x] ADR-003: Activity Log (Spatie ActivityLog)
- [x] ADR-004: i18n Scheme
- [x] ADR-005: Storage Strategy
- [x] **ADR-006: Trash/Snapshot Bundles** ← New

### Runbooks
- [x] **Trash_Restore_Runbook.md** ← New
- [ ] ETL_Import_Runbook.md (planned)
- [ ] Deployment_Runbook.md (planned)
- [ ] Backup_Restore_Runbook.md (planned)

### Technical Docs
- [x] ERD (Entity Relationship Diagram)
- [x] Data Dictionary
- [x] Tasks Index
- [ ] API Documentation (OpenAPI)
- [ ] Security Controls
- [ ] Test Plan

### User Docs (Planned)
- [ ] User Manual (EN)
- [ ] User Manual (AR)
- [ ] Admin Guide
- [ ] Quick Start Guide

---

## Testing Strategy

### Test Coverage Goals
- **Unit Tests**: ≥70% for services, policies, collectors
- **Feature Tests**: ≥80% for critical flows (auth, CRUD, trash)
- **Integration Tests**: ≥60% for ETL, file operations
- **E2E Tests**: Key user journeys (optional)

### Current Test Status
- Total Tests: 35+
- Total Assertions: 200+
- Success Rate: 100%
- Coverage: ~45% (expanding)

### Test Categories
1. **Auth & RBAC**: 7 tests (37 assertions)
2. **Trash System**: 13 tests (87 assertions)
3. **ETL**: 5+ tests (planned)
4. **Audit Logging**: 5+ tests
5. **File Operations**: 5+ tests
6. **Client CRUD**: 5+ tests
7. **UI/E2E**: 0 tests (planned)

---

## Success Criteria

### Phase 1 ✅
- [x] Super admin can login
- [x] Roles & permissions configured
- [x] Database schema complete
- [x] All migrations successful
- [x] Tests passing

### Phase 2 ✅
- [x] Deleting any model creates bundle
- [x] Restore works with conflict resolution
- [x] CLI commands functional
- [x] Web UI accessible
- [x] Permissions enforced
- [x] Tests passing (13 tests, 87 assertions)

### Phase 3 ✅
- [x] Excel files imported successfully
- [x] Data validation errors < 5%
- [x] Idempotent imports verified
- [x] Reject logs generated

### Phase 4 ✅
- [x] Activity logging integrated
- [x] Audit log viewer functional
- [x] Secure file storage implemented
- [x] Document preview working

### Phase 5 ✅
- [x] Navigation system with permissions
- [x] i18n & RTL support
- [x] Clients CRUD complete
- [x] Cases CRUD complete
- [x] Hearings CRUD complete
- [x] Lawyers CRUD complete
- [x] Language files synchronized (EN/AR)
- [x] RTL layout working correctly

### Phase 6 (In Progress)
- [ ] Global search
- [ ] Additional CRUD modules (Contacts, Engagement Letters, etc.)
- [ ] Advanced filtering and sorting

---

## Team Roles (Current: AI Agent)

| Role | Responsibilities | Current |
|---|---|---|
| Project Manager | Planning, tracking, stakeholder communication | AI Agent |
| Backend Developer | Laravel, services, migrations, trash system | AI Agent |
| Frontend Developer | Blade views, Bootstrap UI, RTL | AI Agent |
| QA Engineer | Test creation, validation | AI Agent |
| DevOps | Deployment, monitoring | Pending |
| Technical Writer | Documentation, runbooks | AI Agent |

---

## Change Log

| Date | Version | Changes | Impact |
|---|---|---|---|
| 2025-10-08 | 1.0 | Initial master plan created | Foundation |
| 2025-10-08 | 1.1 | Added trash system, impact matrix, updated phases | All modules |

---

**Next Update**: After ETL implementation  
**Maintained By**: Development Team  
**Last Reviewed**: 2025-10-08

