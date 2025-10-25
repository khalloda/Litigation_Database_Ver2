# Trash / Recycle Bin — Restore Runbook

**Version**: 1.0  
**Date**: 2025-10-08  
**System**: Central Litigation Management  

---

## Overview

This runbook provides step-by-step instructions for managing the deletion bundle / recycle bin system.

---

## Quick Reference

| Task | CLI Command | Web UI |
|---|---|---|
| List bundles | `php artisan trash:list` | `/trash` |
| View bundle details | `php artisan trash:list --type=Client` | `/trash/{uuid}` |
| Dry-run restore | `php artisan trash:restore {uuid} --dry-run` | "Dry Run" button |
| Restore bundle | `php artisan trash:restore {uuid}` | "Restore" button |
| Purge bundle | `php artisan trash:purge {uuid}` | "Purge" button |
| Bulk purge old | `php artisan trash:purge --older-than=90` | N/A |

---

## Section 1: Understanding Deletion Bundles

### What is a Deletion Bundle?

A **deletion bundle** is a snapshot container that captures:
- The deleted entity (root)
- All related entities (cascade)
- File references (for documents)
- Metadata (who, when, why)

### Supported Root Types

| Root Type | Cascade Behavior | Example |
|---|---|---|
| **Client** | Full cascade: cases, hearings, tasks, contacts, POAs, engagement letters | Delete "ABC Corp" → bundles all 50 cases + 200 hearings |
| **Case** | Hearings, tasks, subtasks, case documents | Delete case #123 → bundles 10 hearings + 5 tasks |
| **Document** | Single entity + file reference | Delete "contract.pdf" → bundles doc metadata + file |
| **Hearing** | Single entity | Delete hearing 2024-10-15 → bundles hearing record |
| **AdminTask** | Task + all subtasks | Delete task → bundles task + 3 subtasks |
| **AdminSubtask** | Single entity | Delete subtask → bundles subtask record |
| **EngagementLetter** | Single entity | Delete engagement letter → bundles letter record |
| **PowerOfAttorney** | Single entity | Delete POA → bundles POA record |
| **Contact** | Single entity | Delete contact → bundles contact record |
| **Lawyer** | Single entity + assignment metadata | Delete lawyer → bundles lawyer + assignment refs |

### Bundle Status Lifecycle

```
[trashed] ──restore──> [restored]
    │
    └──purge──> [purged]
```

---

## Section 2: Listing Bundles

### CLI

```bash
# List all bundles
php artisan trash:list

# Filter by type
php artisan trash:list --type=Client
php artisan trash:list --type=Case
php artisan trash:list --type=Document

# Filter by status
php artisan trash:list --status=trashed
php artisan trash:list --status=restored

# Limit results
php artisan trash:list --limit=50
```

### Web UI

1. Navigate to `/trash`
2. Use filters:
   - Type dropdown (Client, Case, Document, etc.)
   - Status dropdown (Trashed, Restored, Purged)
3. Click "Filter" button
4. View results in paginated table

---

## Section 3: Restoring Bundles

### Prerequisites

- User must have `trash.restore` permission (super_admin or admin)
- Bundle must be in `trashed` status
- For child entities: parent must exist (or restoration will skip/fail)

### Dry Run (Recommended First Step)

Always test with dry-run before actual restoration:

```bash
php artisan trash:restore {bundle-uuid} --dry-run
```

**Output shows**:
- Items that would be restored
- Items that would be skipped
- Conflicts detected
- Errors (e.g., missing parents)

**Web UI**: Click "🔍 Dry Run Restore" button → view report modal

### Conflict Resolution Strategies

When a record with the same ID already exists:

| Strategy | Behavior | Use Case |
|---|---|---|
| **skip** (default) | Don't restore, log as skipped | Safe default, preserves existing data |
| **overwrite** | Update existing record with snapshot data | Intentional restore of old version |
| **new_copy** | Create new record with new ID | Keep both versions |

**Example**:
```bash
# Safe restore (skip conflicts)
php artisan trash:restore abc123 --resolve-conflicts=skip

# Overwrite existing records
php artisan trash:restore abc123 --resolve-conflicts=overwrite
```

### Full Restore Procedure

**Step 1**: Locate the bundle
```bash
php artisan trash:list --type=Client
```

**Step 2**: View details
```bash
php artisan trash:list | grep "ABC Corp"
# Note the bundle UUID (e.g., abc123-def456...)
```

**Step 3**: Dry run
```bash
php artisan trash:restore abc123-def456 --dry-run
```

**Step 4**: Review output
- Check "Restored" count
- Review "Skipped" items (conflicts)
- Check "Errors" (missing parents, validation failures)

**Step 5**: Execute restoration
```bash
php artisan trash:restore abc123-def456 --resolve-conflicts=skip
```

**Step 6**: Verify
- Check database for restored records
- Verify relationships re-linked correctly
- Test file downloads for documents

### Orphan Handling

If a child entity's parent no longer exists:
- **Default**: Skip restoration, log warning
- **Why**: Prevents orphaned records
- **Solution**: Restore parent bundle first

**Example**: 
- Hearing belongs to Case #123
- Case #123 doesn't exist
- Hearing restoration skipped
- **Fix**: Restore Case bundle first, then Hearing bundle

---

## Section 4: Purging Bundles

### Single Bundle Purge

```bash
# CLI
php artisan trash:purge {bundle-uuid}

# With force (no confirmation)
php artisan trash:purge {bundle-uuid} --force
```

**Web UI**: Click "🔥 Purge" button → confirm → bundle marked as purged

**Effect**:
- Bundle status → `purged`
- Bundle remains in database (for audit)
- Files remain in storage (no physical deletion)

### Bulk Purge (Scheduled Task)

```bash
# Purge bundles older than 90 days
php artisan trash:purge --older-than=90 --force

# Custom threshold
php artisan trash:purge --older-than=30
```

**Recommended Cron**:
```bash
# Run monthly to clean up old bundles
0 0 1 * * cd /path/to/clm-app && php artisan trash:purge --older-than=90 --force
```

---

## Section 5: Special Cases

### Restoring a Client

**Scenario**: Deleted client "ABC Corporation" with 10 cases

**Bundle includes**:
- 1 client record
- 10 case records
- 50 hearings
- 20 admin tasks
- 30 admin subtasks
- 5 contacts
- 100 documents

**Restore procedure**:
1. Run dry-run
2. Check for conflicts (if client recreated)
3. Restore with appropriate conflict strategy
4. Verify all cases visible under client

### Restoring a Document

**Scenario**: Deleted document "contract.pdf"

**Bundle includes**:
- Document metadata (client_id, matter_id, description, dates)
- File descriptor (disk, path, size, MIME)

**Restore procedure**:
1. Verify parent client/case still exists
2. Restore bundle
3. File remains in `storage/app/secure/`
4. Download link works immediately

**If parent deleted**:
- Document restored with NULL parent references
- File still accessible by direct document ID

### Restoring an Admin Task

**Scenario**: Deleted task "File motion by 2024-10-20" with 3 subtasks

**Bundle includes**:
- Admin task record
- 3 admin subtask records

**Restore procedure**:
1. Verify parent case still exists
2. Restore bundle
3. Task and subtasks recreated in correct order
4. FK relationships re-established

### Restoring a Lawyer

**Scenario**: Deleted lawyer "Ahmed Said"

**Bundle includes**:
- Lawyer record (name, email, etc.)
- Assignment metadata (which hearings/tasks assigned to)

**Note**: Assignments are **information only**, not restored
- Prevents orphaned assignments
- Logged for reference

**Restore procedure**:
1. Restore lawyer record
2. Manual re-assignment to hearings/tasks if needed

---

## Section 6: Troubleshooting

### Bundle Not Created on Deletion

**Symptoms**: Deleted model but no bundle in `/trash`

**Causes**:
1. Model not configured in `config/trash.php`
2. Force delete used (`forceDelete()`)
3. `skipBundleCreation` flag set
4. Collector class missing

**Solutions**:
```bash
# Check config
php artisan config:clear
php artisan config:cache

# Verify collector exists
php artisan tinker
config('trash.collectors')[App\Models\Client::class]
```

### Restore Fails with "Parent Not Found"

**Symptoms**: `Skipped: parent missing`

**Cause**: Child entity's parent was deleted separately

**Solutions**:
1. Restore parent bundle first
2. Use `--resolve-conflicts=new_copy` to create orphan with new ID (not recommended)

### Large Bundle Performance

**Symptoms**: Client with 500 cases takes 30+ seconds to bundle

**Mitigation**:
- Bundles created asynchronously via queue (future enhancement)
- For now, warn users before deleting large clients

### Files Not Accessible After Restore

**Symptoms**: Document restored but download fails

**Causes**:
1. File physically deleted from storage
2. File path changed
3. Permission issue

**Solutions**:
- Check `storage/app/secure/{path}` exists
- Verify bundle's `files_json` has correct path
- Re-upload file if lost

---

## Section 7: Best Practices

### For Admins

1. **Always dry-run first** — Prevents surprises
2. **Review conflicts** — Understand what will be skipped
3. **Document reasons** — Use deletion_reason field
4. **Regular purges** — Set up cron for `--older-than=90`
5. **Monitor bundle sizes** — Alert on large snapshots

### For Developers

1. **Update collectors** — When schema changes
2. **Test restoration** — Add tests for new model types
3. **Document cascades** — Update ERD with deletion flows
4. **Log verbosely** — Capture bundle IDs in audit logs

### For Users (via UI)

1. **Confirm before delete** — Understand what will be bundled
2. **Provide reasons** — Help future recovery
3. **Notify admins** — For large deletions (client with many cases)

---

## Section 8: Security & Permissions

### Permission Requirements

| Action | Permission | Default Roles |
|---|---|---|
| View bundles | `trash.view` | super_admin, admin |
| Restore bundles | `trash.restore` | super_admin, admin |
| Purge bundles | `trash.purge` | super_admin, admin |

### Audit Trail

All trash operations are logged:
- Bundle creation: Who deleted, when, why
- Restoration: Who restored, conflict resolution used
- Purge: Who purged, bundle age

**Activity Log Query**:
```php
Activity::where('log_name', 'deletion_bundle')
    ->where('causer_id', $userId)
    ->get();
```

---

## Section 9: Monitoring & Alerts

### Recommended Metrics

1. **Bundle Growth Rate**: Track `deletion_bundles` table size
2. **Large Bundles**: Alert when `cascade_count` > 100
3. **Old Bundles**: Alert when `trashed` bundles > 1000
4. **Failed Restorations**: Log and alert on errors

### Dashboard Queries

```sql
-- Count by type and status
SELECT root_type, status, COUNT(*) as count
FROM deletion_bundles
GROUP BY root_type, status;

-- Largest bundles
SELECT id, root_label, cascade_count, created_at
FROM deletion_bundles
ORDER BY cascade_count DESC
LIMIT 10;

-- Expired bundles (past TTL)
SELECT COUNT(*)
FROM deletion_bundles
WHERE status = 'trashed'
  AND ttl_at < NOW();
```

---

## Section 10: Emergency Procedures

### Critical Client Accidentally Deleted

**Immediate Actions**:
1. Find bundle: `php artisan trash:list --type=Client | grep "Client Name"`
2. Dry-run: `php artisan trash:restore {uuid} --dry-run`
3. Restore: `php artisan trash:restore {uuid}`
4. Verify: Check client dashboard, cases visible

**Timeline**: ~5 minutes

### Mass Deletion (50+ bundles)

**Scenario**: Bug caused mass deletion

**Recovery Steps**:
1. List recent bundles: `php artisan trash:list --limit=100`
2. Identify affected bundles by timestamp
3. Restore each: `php artisan trash:restore {uuid}`
4. Or write custom script for bulk restore

### Database Full (Storage Issue)

**Scenario**: `deletion_bundles` table consuming 10GB+

**Immediate Actions**:
1. Purge old bundles: `php artisan trash:purge --older-than=30 --force`
2. Check remaining size
3. If still critical, purge restored bundles: manual query
4. Long-term: Reduce TTL from 90 to 60 days

---

## Section 11: Configuration

### Config File: `config/trash.php`

```php
return [
    // Enable/disable per model
    'enabled_for' => [
        'Client' => true,
        'Lawyer' => false, // Example: disable for lawyers
    ],
    
    // TTL (days)
    'ttl_days' => 90,
    
    // Restore defaults
    'restore' => [
        'default_conflict_strategy' => 'skip',
        'resolve_orphans' => 'skip',
    ],
];
```

### Disable Bundle Creation

**Temporarily** (single operation):
```php
$model->skipBundleCreation = true;
$model->delete();
```

**Permanently** (model type):
```php
// config/trash.php
'enabled_for' => [
    'Lawyer' => false,
],
```

---

## Section 12: Examples

### Example 1: Restore Deleted Client with Cases

```bash
# Step 1: Find the client bundle
$ php artisan trash:list --type=Client
┌─────────────┬────────┬──────────────┬───────┬─────────────┬─────────────┬─────────┐
│ Bundle ID   │ Type   │ Label        │ Items │ Deleted By  │ Deleted At  │ Status  │
├─────────────┼────────┼──────────────┼───────┼─────────────┼─────────────┼─────────┤
│ abc123...   │ Client │ ABC Corp     │ 125   │ John Doe    │ 2 hours ago │ trashed │
└─────────────┴────────┴──────────────┴───────┴─────────────┴─────────────┴─────────┘

# Step 2: Dry run
$ php artisan trash:restore abc123 --dry-run
=== Restore Report ===
Root Type: Client
Root Label: ABC Corp
Conflict Strategy: skip

Restored: 125
Skipped: 0
Errors: 0

# Step 3: Execute restore
$ php artisan trash:restore abc123
Restore this bundle? (yes/no) [no]: yes
✓ Bundle restored successfully!

# Step 4: Verify
$ mysql -u root -p litigation_db_ver2 -e "SELECT * FROM clients WHERE id = 24"
# Client record exists, not soft-deleted
```

### Example 2: Restore Document with File

```bash
# Find document bundle
$ php artisan trash:list --type=ClientDocument
│ xyz789...   │ ClientDocument │ Contract ABC-XYZ.pdf │ 1 │ Jane │ 1 day ago │ trashed │

# Restore
$ php artisan trash:restore xyz789
✓ Bundle restored successfully!

# Verify file download works
$ curl http://litigation.local/documents/456/download
# File downloads successfully
```

### Example 3: Bulk Purge Old Bundles

```bash
# Preview what will be purged
$ php artisan trash:purge --older-than=90
Found 45 bundles older than 90 days:
  Client: 10
  Case: 25
  Document: 10

Purge all 45 bundles? (yes/no) [no]: yes
████████████████████ 100%
✓ Purged 45 bundles
```

---

## Section 13: Validation Steps

After restoration, verify:

### Client Restoration
- [ ] Client record exists (`SELECT * FROM clients WHERE id = X`)
- [ ] Client not soft-deleted (`deleted_at IS NULL`)
- [ ] Cases visible under client (`SELECT * FROM cases WHERE client_id = X`)
- [ ] Contacts linked (`SELECT * FROM contacts WHERE client_id = X`)
- [ ] Dashboard displays client correctly

### Case Restoration
- [ ] Case record exists and not soft-deleted
- [ ] Hearings linked to case
- [ ] Admin tasks linked to case
- [ ] Client relationship intact

### Document Restoration
- [ ] Document metadata restored
- [ ] File exists in storage
- [ ] Download link works
- [ ] Permissions respected (only authorized users can download)

---

## Section 14: Known Limitations

1. **No Partial Restore**: Must restore entire bundle (future enhancement)
2. **No File Quarantine**: Files not moved to separate location (future enhancement)
3. **Large Bundles**: Clients with 500+ cases may be slow
4. **Pivot Relations**: Not fully restored (manual re-linking needed)
5. **Auto-Increment**: Restored IDs may create gaps in sequences

---

## Section 15: Support & Contact

**Documentation**:
- ADR: `/docs/adr/ADR-20251008-006.md`
- ERD: `/docs/erd.md`
- Code: `/app/Services/DeletionBundleService.php`

**Testing**:
- Test Suite: `/tests/Feature/TrashSystemTest.php`
- Run: `php artisan test --filter=TrashSystemTest`

**Logs**:
- Application: `storage/logs/laravel.log`
- Search: `grep "Deletion bundle" storage/logs/laravel-*.log`

---

**Last Updated**: 2025-10-08  
**Version**: 1.0  
**Maintainer**: Development Team

