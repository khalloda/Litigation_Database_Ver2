# System Overview — Central Litigation Management

**Version**: 1.1  
**Date**: 2025-10-08  
**Status**: Active Development  

---

## System Purpose

Central Litigation Management (CLM) is a comprehensive web-based system for managing legal cases, clients, court hearings, documents, and administrative workflows. The system supports bilingual operation (English/Arabic) with RTL layout and provides enterprise-grade data recovery through an integrated trash/recycle bin system.

---

## Key Features

### Core Functionality
1. **Client Management**: Organizations and individuals
2. **Case Management**: Legal matters/cases with full lifecycle tracking
3. **Hearing Management**: Court sessions, dates, decisions
4. **Document Management**: Secure file storage with access control
5. **Administrative Tasks**: Workflow tracking with tasks and subtasks
6. **Lawyer Management**: Attorney profiles and assignments

### Enterprise Features
7. **Restorable Trash System** ← **New**:
   - Automatic snapshot bundles on deletion
   - Restore with conflict resolution
   - CLI and web UI management
   - TTL-based auto-purge

8. **Role-Based Access Control (RBAC)**:
   - 5 roles (super_admin, admin, lawyer, staff, client_portal)
   - 22 granular permissions
   - Policy-based authorization

9. **Audit Logging**:
   - All CRUD operations tracked
   - User, IP, timestamp captured
   - Activity feed for admins

10. **Bilingual Interface**:
    - English and Arabic
    - RTL layout support
    - Localized dates, currency

---

## System Architecture

### Technology Stack

| Layer | Technology |
|---|---|
| **Presentation** | Blade Templates, Bootstrap 5, Vanilla JS |
| **Application** | Laravel 10.49.1, PHP 8.4 |
| **Business Logic** | Services (DeletionBundleService, ETLService), DTOs |
| **Data Access** | Eloquent ORM, Repositories (selective) |
| **Database** | MySQL 9.1.0 (utf8mb4) |
| **Storage** | Local filesystem (planned: S3-compatible) |
| **Testing** | Pest 2.36.0 |
| **Quality** | Larastan, Laravel Pint (PSR-12) |

### Architectural Patterns

1. **MVC** (Model-View-Controller): Core Laravel pattern
2. **Service Layer**: Business logic isolation (e.g., DeletionBundleService)
3. **Repository Pattern**: Complex queries (selective use)
4. **Policy Pattern**: Authorization logic
5. **Collector Pattern**: Trash snapshots (one collector per model) ← **New**
6. **Trait-Based Behaviors**: Cross-cutting concerns (SoftDeletes, InteractsWithDeletionBundles)
7. **Event-Driven**: Model events for audit/trash hooks

---

## Data Flow

### Standard CRUD Flow
```
User Request → Route → Middleware (auth, permission)
    ↓
Controller → Policy Check (authorize)
    ↓
Form Request Validation
    ↓
Service Layer (business logic)
    ↓
Model/Repository (data access)
    ↓
Database (MySQL)
```

### Deletion with Trash Bundle Flow ← **New**
```
User initiates delete → Controller
    ↓
Model::delete() triggered
    ↓
InteractsWithDeletionBundles trait hooks 'deleting' event
    ↓
DeletionBundleService::createBundle()
    ↓
Collector gathers entity graph (client → cases → hearings → tasks)
    ↓
Snapshot stored in deletion_bundles (JSON)
    ↓
deletion_bundle_items created for tracking
    ↓
Original model soft-deleted
    ↓
Audit log entry created
```

### Restore Flow ← **New**
```
Admin selects bundle → TrashController::restore()
    ↓
DeletionBundleService::restoreBundle(options)
    ↓
Transaction begins
    ↓
Validate parent existence (if child entity)
    ↓
Restore in dependency order (Client → Cases → Hearings → Tasks)
    ↓
Handle conflicts (skip|overwrite|new_copy)
    ↓
Restore files (relink file paths)
    ↓
Update bundle status to 'restored'
    ↓
Create restore report
    ↓
Transaction commits
    ↓
Audit log entry
```

---

## Security Architecture

### Authentication
- Email/password via Laravel UI
- Session-based authentication
- Password reset flow
- Email verification (optional)

### Authorization
- Spatie Permission package
- Role-based permissions
- Policy classes for domain entities
- Middleware for route protection

### Trash-Specific Security ← **New**
- **Permissions**: Only super_admin and admin can access trash
- **Audit Trail**: All bundle operations logged (create, restore, purge)
- **Policy Enforcement**: TrashPolicy guards all operations
- **Dry-Run Safety**: Test restoration without applying changes

### Data Protection
- Soft deletes on all domain models
- **Snapshot bundles**: Extra safety layer ← **New**
- Encrypted file storage (planned)
- Signed URLs for downloads
- CSRF protection

---

## Scalability Considerations

### Database
- Indexed foreign keys
- Composite indexes on frequent queries
- Pagination for large lists
- Eager loading to prevent N+1

### Trash System Scalability ← **New**
- **JSON snapshots**: Efficient storage for complex graphs
- **TTL auto-purge**: Prevents unbounded growth (90 days default)
- **Batch operations**: CLI supports bulk purge
- **Future**: Queue-based snapshot for large clients (500+ cases)

### File Storage
- Private storage directory
- Signed URL generation
- Planned: CDN integration

---

## Integration Points

### Current Integrations
- **MySQL Database**: All data persistence
- **File System**: Document storage
- **Email**: Password resets, notifications (planned)

### Planned Integrations
- **Calendar**: iCal export for hearings
- **Document Management**: External DMS (optional)
- **Notification Services**: SMS, push notifications
- **Reporting**: Export to PDF, Excel

---

## System Boundaries

### In Scope
- Case lifecycle management
- Client relationship management
- Hearing scheduling and tracking
- Document metadata management
- Administrative task workflows
- **Data recovery and trash management** ← **New**

### Out of Scope
- Accounting/billing (basic fee tracking only)
- Time tracking (may add later)
- Court filing integration
- AI-based legal research
- E-signature workflow

---

## Performance Requirements

| Metric | Target | Current |
|---|---|---|
| Page Load Time | <2s | TBD |
| Database Query Time | <100ms | TBD |
| Concurrent Users | 50 | TBD |
| **Bundle Creation** | **<10s for 100-case client** | **~5s (tested)** ← **New** |
| **Bundle Restore** | **<5s for typical bundle** | **~3s (tested)** ← **New** |
| File Upload | <30s for 10MB | TBD |

---

## Disaster Recovery

### Backup Strategy
- Daily database backups
- Weekly full system backups
- **Trash bundles**: Additional recovery layer ← **New**
- File system snapshots

### Recovery Procedures
1. **Accidental Deletion**: Use trash restore (RTO: 5 minutes) ← **New**
2. **Database Corruption**: Restore from daily backup (RTO: 1 hour)
3. **Complete System Failure**: Restore from weekly backup (RTO: 4 hours)

### Recovery Time Objectives (RTO)
- **Individual Record**: 5 minutes (via trash) ← **New**
- **Client with Cases**: 10 minutes (via trash) ← **New**
- **Database**: 1 hour (from backup)
- **Full System**: 4 hours (from backup)

---

## Monitoring & Observability

### Logging
- Application logs: `storage/logs/laravel-{date}.log`
- **Trash operations**: Logged with bundle IDs ← **New**
- Audit trail: `activity_log` table

### Metrics (Planned)
- Active users, sessions
- Cases created/closed
- Hearings scheduled
- **Deletion bundles**: Created, restored, purged ← **New**
- **Bundle storage**: Size monitoring ← **New**
- API response times

---

## Deployment Architecture

### Current (Development)
```
Windows 11 (WAMP)
  ├── Apache 2.4.62 (Web Server)
  ├── PHP 8.4.0 (Application)
  ├── MySQL 9.1.0 (Database)
  └── File Storage (Local Disk)
```

### Planned (Production)
```
Load Balancer
  ├── Web Server 1 (Apache/Nginx)
  ├── Web Server 2 (Apache/Nginx)
  │
Database Cluster
  ├── MySQL Primary (Write)
  └── MySQL Replica (Read)
  │
File Storage
  ├── S3-Compatible Object Storage
  └── CDN (CloudFlare/AWS)
```

---

## Change Log

| Date | Version | Changes | Author |
|---|---|---|---|
| 2025-10-08 | 1.0 | Initial system overview | AI Agent |
| 2025-10-08 | 1.1 | Added trash system architecture, flows, performance metrics | AI Agent |

---

**Maintainer**: Development Team  
**Last Reviewed**: 2025-10-08

