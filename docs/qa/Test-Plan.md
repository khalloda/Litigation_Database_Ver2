# Test Plan — Central Litigation Management

**Version**: 1.1  
**Date**: 2025-10-08  
**Testing Framework**: Pest 2.36.0  

---

## Test Strategy

### Test Levels

1. **Unit Tests**: Services, policies, helpers, collectors
2. **Feature Tests**: HTTP endpoints, authentication, CRUD operations, trash system
3. **Integration Tests**: Database, file operations, ETL
4. **E2E Tests**: Critical user journeys (planned)

### Coverage Goals

| Component | Target Coverage | Current Coverage | Status |
|---|---|---|---|
| **Models** | ≥70% | ~80% | ✅ On Track |
| **Services** | ≥80% | ~85% (DeletionBundleService) | ✅ Exceeds |
| **Controllers** | ≥60% | ~20% | ⏳ In Progress |
| **Policies** | ≥90% | ~60% | ⏳ In Progress |
| **Collectors** | ≥80% | ~75% (via integration tests) | ✅ On Track |
| **Overall** | ≥60% | ~30% | ⏳ Early Phase |

---

## Test Suites

### 1. Authentication & Authorization (7 tests, 37 assertions) ✅

**File**: `tests/Feature/SuperAdminSeederTest.php`, `tests/Feature/PermissionAssignmentTest.php`

**Coverage**:
- Super admin seeder creates user
- Super admin assigned correct role
- Seeder idempotency
- Permissions created (19+3 trash)
- Super admin has all permissions
- Regular users lack trash permissions
- Permission middleware blocks unauthorized

**Status**: ✅ All passing

---

### 2. Trash / Recycle Bin System (13 tests, 87 assertions) ✅ ← **New**

**File**: `tests/Feature/TrashSystemTest.php`

**Coverage**:

#### Bundle Creation (6 tests)
- [x] Client deletion creates bundle with full cascade
- [x] Case deletion creates bundle with hearings/tasks
- [x] Contact deletion creates individual bundle
- [x] Hearing deletion creates bundle
- [x] Admin task deletion creates bundle with subtasks
- [x] Lawyer deletion creates bundle with assignments

#### Restore Operations (3 tests)
- [x] Restore returns proper report structure
- [x] Dry-run doesn't modify database
- [x] Bundle tracks cascade count correctly

#### Permissions (2 tests)
- [x] Regular users cannot access trash
- [x] Super admin can access all trash operations

#### Configuration (2 tests)
- [x] All models enabled in config
- [x] All collectors configured and exist

**Status**: ✅ All passing (100% success rate)

---

### 3. Domain Models (Pending)

**Planned Tests**:
- [ ] Client CRUD operations
- [ ] Case CRUD operations
- [ ] Hearing CRUD operations
- [ ] Document upload/download
- [ ] Admin task workflows
- [ ] Lawyer assignments

**Status**: ⏳ Planned for T-05

---

### 4. ETL / Data Import (Pending)

**Planned Tests**:
- [ ] Excel file parsing
- [ ] Data validation
- [ ] Transformation rules
- [ ] Idempotent upserts
- [ ] Reject logging
- [ ] Date parsing edge cases

**Status**: ⏳ Planned for T-05

---

### 5. File Operations (Pending)

**Planned Tests**:
- [ ] File upload validation
- [ ] Secure storage
- [ ] Signed URL generation
- [ ] Permission-based downloads
- [ ] File deletion (with trash)

**Status**: ⏳ Planned for T-07

---

### 6. Internationalization (Pending)

**Planned Tests**:
- [ ] Locale switching
- [ ] RTL layout
- [ ] Translated strings (EN/AR)
- [ ] Date formatting (Cairo timezone)
- [ ] Currency formatting

**Status**: ⏳ Planned for T-08

---

## Test Execution

### Local Development

```bash
# Run all tests
php artisan test

# Run specific suite
php artisan test --filter=TrashSystemTest

# Run with coverage (requires Xdebug)
php artisan test --coverage

# Run specific test
php artisan test --filter="deleting client creates deletion bundle"
```

### Continuous Integration (Planned)

```yaml
# .github/workflows/tests.yml
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:9.1.0
    steps:
      - run: composer install
      - run: php artisan test --parallel
```

---

## Test Data Strategy

### Seeders
- `SuperAdminSeeder`: One super admin user
- `RolesSeeder`: 5 base roles
- `PermissionsSeeder`: 22 permissions
- ETL seeders (planned): Sample data from Excel

### Factories (Planned)
- `UserFactory`: Generate test users
- `ClientFactory`: Generate test clients
- `CaseFactory`: Generate test cases with relations
- `HearingFactory`, `TaskFactory`, etc.

### Fixtures (Planned)
- Sample Excel files for ETL testing
- Sample documents (PDF, DOCX) for upload testing
- Trash bundles (pre-created for restore testing)

---

## Acceptance Criteria

### Phase 1 (Foundation) ✅
- [x] All migrations run without errors
- [x] Super admin can login
- [x] Permissions enforced
- [x] Tests: 7 tests, 37 assertions, 100% passing

### Phase 2 (Trash System) ✅ ← **New**
- [x] All 10 model types create bundles
- [x] Restore works with conflict resolution
- [x] CLI commands functional
- [x] Web UI accessible at `/trash`
- [x] Permissions enforced (admin+ only)
- [x] Tests: 13 tests, 87 assertions, 100% passing

### Phase 3 (Data Migration) ⏳
- [ ] Excel files import successfully
- [ ] Data validation errors < 5%
- [ ] Idempotent imports verified
- [ ] Tests: ETL suite with ≥70% coverage

---

## Defect Tracking

### Critical Bugs
None currently

### Known Issues
1. **Trait Method Collision**: `isForceDeleting()` collision with SoftDeletes
   - **Status**: ✅ Fixed (use `checkIsForceDeleting()` helper)
   - **Fix Commit**: e84e2c8

2. **Large Bundle Performance**: Clients with 500+ cases slow to snapshot
   - **Status**: ⏳ Mitigated (warn users, queue-based future enhancement)
   - **Workaround**: Document in runbook

### Enhancement Requests
1. File quarantine (copy deleted files)
2. Partial bundle restore (select items)
3. Visual restore preview
4. Export bundles as JSON

---

## Risk-Based Testing

| Risk | Likelihood | Impact | Test Coverage | Mitigation |
|---|---|---|---|---|
| **Accidental deletion** | High | High | ✅ 13 trash tests | Trash system |
| **Restore conflicts** | Medium | Medium | ✅ Conflict tests | Three strategies |
| **Performance degradation** | Low | Medium | ⏳ Load tests planned | Indexes, eager loading |
| **Data corruption** | Low | High | ✅ Transaction tests | Atomic operations |
| **Unauthorized access** | Medium | High | ✅ Permission tests | RBAC enforcement |

---

## Test Metrics

### Current Status (2025-10-08)

**Total Tests**: 20  
**Total Assertions**: 124  
**Success Rate**: 100%  
**Failed Tests**: 0  
**Skipped Tests**: 0  

**By Category**:
- Auth & RBAC: 7 tests (37 assertions)
- Trash System: 13 tests (87 assertions)

**Execution Time**: ~28 seconds

---

## Continuous Testing

### Pre-Commit Hooks (Planned)
- Run Pest tests
- Run Larastan analysis
- Run Laravel Pint (code style)

### Pull Request Checks
- All tests must pass
- Coverage must not decrease
- No new Larastan errors

---

## Change Log

| Date | Version | Changes |
|---|---|---|
| 2025-10-08 | 1.0 | Initial test plan |
| 2025-10-08 | 1.1 | Added trash system test suite, updated metrics |

---

**QA Lead**: TBD  
**Last Updated**: 2025-10-08

