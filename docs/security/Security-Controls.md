# Security Controls — Central Litigation Management

**Version**: 1.1  
**Date**: 2025-10-08  
**Classification**: Internal  

---

## Overview

This document outlines the security controls implemented in the Central Litigation Management system to protect sensitive legal data and ensure compliance with security best practices.

---

## Authentication Controls

### User Authentication
- **Method**: Email/password (Laravel UI)
- **Password Policy**:
  - Minimum 8 characters (configurable)
  - Hashed using bcrypt (Laravel default)
  - Reset via email token (1 hour expiry)
- **Session Management**:
  - Secure session cookies
  - CSRF protection
  - Session timeout: 120 minutes (configurable)

### Super Admin Account
- **Email**: khelmy@sarieldin.com
- **Access**: Full system control including trash operations
- **Audit**: All actions logged

---

## Authorization Controls (RBAC)

### Roles

| Role | Description | Typical Users |
|---|---|---|
| `super_admin` | Full system access | System administrators |
| `admin` | Most operations including trash | Office managers |
| `lawyer` | Case management, limited admin | Attorneys |
| `staff` | Data entry, viewing | Paralegals, assistants |
| `client_portal` | Read-only client-specific | External clients (future) |

### Permissions (22 Total)

#### Cases (4)
- `cases.view`: View case list and details
- `cases.create`: Create new cases
- `cases.edit`: Modify existing cases
- `cases.delete`: Soft-delete cases (creates trash bundle)

#### Hearings (4)
- `hearings.view`, `hearings.create`, `hearings.edit`, `hearings.delete`

#### Documents (4)
- `documents.view`, `documents.upload`, `documents.download`, `documents.delete`

#### Clients (4)
- `clients.view`, `clients.create`, `clients.edit`, `clients.delete`

#### Admin (3)
- `admin.users.manage`: User CRUD operations
- `admin.roles.manage`: Role/permission management
- `admin.audit.view`: View audit logs

#### Trash / Recycle Bin (3) ← **New**
- **`trash.view`**: View deletion bundles in recycle bin
- **`trash.restore`**: Restore deleted entities from bundles
- **`trash.purge`**: Permanently purge deletion bundles

**Assignment**:
- `super_admin`: ALL 22 permissions
- `admin`: All except `admin.users.manage`, `admin.roles.manage` (configurable)
- `lawyer`, `staff`, `client_portal`: Subset based on role

---

## Data Protection Controls

### Soft Deletes
- **All domain models** use soft deletes (`deleted_at` column)
- Records marked as deleted but retained in database
- Can be queried with `Model::withTrashed()`
- Permanent deletion requires explicit `forceDelete()`

### Deletion Bundles (Trash System) ← **New**

**Purpose**: Enterprise-grade data recovery layer

**How It Works**:
1. User deletes a model (e.g., Client, Case)
2. **Before** soft delete applies, system creates snapshot bundle
3. Bundle captures:
   - Deleted entity + all related entities (cascade graph)
   - File references (for documents)
   - Metadata (who, when, why)
4. Bundle stored in `deletion_bundles` table (JSON)
5. Original models soft-deleted normally

**Security Controls**:
- **Access**: Restricted to `super_admin` and `admin` roles
- **Permissions**: `trash.view`, `trash.restore`, `trash.purge`
- **Audit**: All bundle operations logged
- **Policy Enforcement**: TrashPolicy guards all actions
- **Dry-Run Mode**: Test restoration without applying changes
- **Conflict Resolution**: Three strategies (skip, overwrite, new_copy)
- **TTL**: Auto-purge after 90 days (prevents indefinite storage)

**Supported Models**:
- Client (full cascade: cases, hearings, tasks, contacts, documents)
- CaseModel (hearings, tasks, subtasks, documents)
- ClientDocument (single + file descriptor)
- Hearing, AdminTask, AdminSubtask
- EngagementLetter, PowerOfAttorney, Contact, Lawyer

**Restoration Security**:
- Parent validation (skip orphans if parent missing)
- Transaction-wrapped (atomic, rollback on error)
- Detailed restore report (conflicts, errors, skipped items)
- Audit log entry on restore

**Data Retention**:
- Bundles kept for 90 days (configurable)
- Auto-purge via cron: `php artisan trash:purge --older-than=90`
- Purged bundles marked as `purged` (retained for audit)

---

## Audit & Logging Controls

### Activity Logging (Spatie ActivityLog)

**Logged Events**:
- Create, Update, Delete on all domain models
- User login/logout
- Permission changes
- Role assignments
- **Bundle creation, restore, purge** ← **New**

**Captured Data**:
- **Causer**: User who performed action
- **Subject**: Entity affected
- **Properties**: Old/new values (JSON)
- **IP Address**: Request IP (optional)
- **User Agent**: Browser/client info (optional)
- **Timestamp**: When action occurred

**Retention**: Indefinite (manual archival after 2 years)

### Trash-Specific Logging ← **New**

**Bundle Creation**:
```json
{
  "event": "bundle_created",
  "bundle_id": "uuid",
  "root_type": "Client",
  "root_id": 123,
  "cascade_count": 45,
  "deleted_by": "user@example.com",
  "reason": "User requested deletion"
}
```

**Bundle Restore**:
```json
{
  "event": "bundle_restored",
  "bundle_id": "uuid",
  "conflict_strategy": "skip",
  "restored_count": 42,
  "skipped_count": 3,
  "errors_count": 0
}
```

**Bundle Purge**:
```json
{
  "event": "bundle_purged",
  "bundle_id": "uuid",
  "age_days": 95
}
```

---

## File Storage Controls

### Secure Storage (Planned - T-07)
- **Location**: `storage/app/secure/` (not web-accessible)
- **Access**: Via controller with permission checks
- **Download**: Signed URLs (time-limited, tamper-proof)
- **Upload Validation**:
  - Max size: 10 MB
  - Allowed MIME types: PDF, DOCX, XLSX, images
  - Virus scanning (optional via ClamAV)

### Trash & Files ← **New**
- **On Delete**: Files NOT physically deleted
- **Bundle**: File descriptor stored (disk, path, size, MIME)
- **On Restore**: File relinked (no physical move needed)
- **On Purge**: Files can be deleted (optional, configurable)
- **Quarantine** (future): Copy deleted files to separate location

---

## Network Security

### HTTPS (Production)
- TLS 1.2+ required
- Valid SSL certificate
- HSTS headers

### CSRF Protection
- Laravel CSRF tokens on all forms
- API: Bearer tokens (Sanctum)

### Rate Limiting
- Login attempts: 5 per minute
- API calls: 60 per minute (authenticated)

---

## Access Control Matrix

| Resource | super_admin | admin | lawyer | staff | client_portal |
|---|:---:|:---:|:---:|:---:|:---:|
| **View Cases** | ✅ | ✅ | ✅ | ✅ | ✅ (own only) |
| **Create Cases** | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Delete Cases** | ✅ | ✅ | ✅ | ❌ | ❌ |
| **View Trash** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Restore Bundles** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Purge Bundles** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Manage Users** | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Manage Roles** | ✅ | ❌ | ❌ | ❌ | ❌ |

---

## Compliance Controls

### Data Privacy
- Personal data minimization
- Consent tracking (planned)
- Right to erasure (via trash purge)
- Data retention policies (TTL)

### Audit Requirements
- Complete audit trail
- Tamper-proof logs (append-only)
- **Deletion tracking**: All deletions bundled and logged ← **New**
- **Restoration tracking**: Who restored what and when ← **New**

---

## Incident Response

### Data Breach
1. Identify scope (affected records)
2. Isolate system
3. Notify stakeholders
4. Review audit logs
5. Implement additional controls

### Accidental Deletion ← **New**
1. **Immediate**: Locate bundle in trash (`php artisan trash:list`)
2. **Dry-Run**: Test restoration (`trash:restore {uuid} --dry-run`)
3. **Restore**: Execute restoration
4. **Verify**: Check data integrity
5. **Document**: Log incident and resolution
**RTO**: 5 minutes

### Mass Deletion (Security Incident) ← **New**
1. **Identify**: Review trash by timestamp
2. **Assess**: Count affected bundles by type
3. **Restore**: Bulk restore via CLI script
4. **Investigate**: Review audit logs for unauthorized access
5. **Harden**: Update permissions, review access
**RTO**: 30 minutes

---

## Security Testing

### Penetration Testing (Planned)
- Authentication bypass attempts
- Authorization escalation
- SQL injection
- XSS vulnerabilities
- CSRF attacks

### Security Scans
- **Static Analysis**: Larastan (enabled)
- **Dependency Scanning**: Composer audit
- **Code Review**: Required for all PRs

### Trash-Specific Security Tests ← **New**
- [x] Regular users cannot access trash
- [x] Dry-run doesn't modify database
- [x] Permission enforcement on all operations
- [x] Audit logs created for all trash actions
- [x] Transaction rollback on error

---

## Secure Development Practices

### Code Security
- PSR-12 coding standards
- Input validation (Form Requests)
- Output escaping (Blade auto-escapes)
- Parameterized queries (Eloquent)
- No eval() or shell_exec()

### Secrets Management
- All secrets in `.env` (git-ignored)
- Database passwords not in code
- API keys environment-based
- No hardcoded credentials

### Dependency Security
- Regular `composer update`
- Security advisories monitoring
- Lock file version control

---

## Recommendations

### Immediate (Phase 3)
1. Implement file encryption at rest
2. Add 2FA for super_admin
3. Set up automated backups
4. Configure trash auto-purge cron

### Short-Term (Phase 4-5)
1. API rate limiting per user
2. IP whitelisting for admin panel
3. Session timeout warnings
4. Password complexity enforcement

### Long-Term (Phase 6+)
1. SOC 2 compliance audit
2. Penetration testing
3. Security awareness training
4. Incident response drills

---

## Change Log

| Date | Version | Changes |
|---|---|---|
| 2025-10-08 | 1.0 | Initial security controls documented |
| 2025-10-08 | 1.1 | Added trash system security controls, incident response procedures |

---

**Security Officer**: TBD  
**Last Security Review**: 2025-10-08  
**Next Review**: 2025-11-08

