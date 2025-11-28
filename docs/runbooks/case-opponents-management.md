# Case Opponents Management Runbook

> **Operational Procedures**: This runbook provides step-by-step procedures for managing multiple opponents per case in the Central Litigation Management system.

---

## Overview

The Multi-Opponents Per Case feature allows legal cases to have multiple opposing parties, each with specific capacities (roles) in the case. This runbook covers:

- **Daily Operations**: Adding, removing, and managing opponents
- **Import Procedures**: Bulk import of opponent data
- **Troubleshooting**: Common issues and solutions
- **Maintenance**: Data integrity and cleanup procedures

---

## Prerequisites

### Required Permissions
- `cases.opponents.view` - View opponents for a case
- `cases.opponents.edit` - Add/remove opponents
- `cases.opponents.attach` - Attach opponents to cases
- `cases.opponents.detach` - Detach opponents from cases

### System Requirements
- Laravel 10.x application
- MySQL 9.1.0+ database
- Multi-opponents feature enabled
- Proper user roles assigned

---

## Daily Operations

### 1. Adding Opponents to a Case

#### Via Web Interface
1. **Navigate to Case**: Go to Cases → Select case → View details
2. **Access Opponents Section**: Scroll to "Opponents" section
3. **Click "Add Opponent"**: Opens opponent selection modal
4. **Search Opponent**: Type opponent name (supports Arabic/English)
5. **Select Capacity**: Choose from dropdown (Defendant, Co-defendant, etc.)
6. **Add Alias** (Optional): Custom display name
7. **Set as Primary** (Optional): Check if this should be primary opponent
8. **Click "Add"**: Opponent is attached to case

#### Via API (Programmatic)
```php
use App\Services\CaseOpponentService;
use App\Models\CaseModel;

$caseOpponentService = app(CaseOpponentService::class);
$case = CaseModel::find($caseId);

// Add opponent with capacity
$pivot = $caseOpponentService->attachOpponent(
    case: $case,
    opponentId: $opponentId,
    capacityId: $capacityId,
    isPrimary: false,
    order: null,
    alias: 'Custom Alias'
);
```

### 2. Managing Primary Opponent

#### Change Primary Opponent
1. **Access Case**: Navigate to case details
2. **Find Opponent**: Locate opponent in opponents table
3. **Click "Set as Primary"**: Button next to opponent name
4. **Confirm Action**: System automatically unsets old primary
5. **Verify Change**: Primary indicator updates immediately

#### Via API
```php
// Set new primary opponent
$caseOpponentService->setPrimary($case, $newOpponentId);
```

### 3. Reordering Opponents

#### Via Web Interface
1. **Access Case**: Navigate to case details
2. **Use Arrow Buttons**: Up/Down arrows next to each opponent
3. **Visual Feedback**: Order updates immediately
4. **Save Changes**: Changes persist automatically

#### Via API
```php
// Reorder opponents
$opponentIds = [3, 1, 2]; // New order
$caseOpponentService->reorder($case, $opponentIds);
```

### 4. Removing Opponents

#### Via Web Interface
1. **Access Case**: Navigate to case details
2. **Find Opponent**: Locate opponent in table
3. **Click "Remove"**: Red remove button
4. **Confirm Deletion**: Confirm dialog appears
5. **Verify Removal**: Opponent disappears from list

#### Via API
```php
// Remove opponent (soft delete)
$caseOpponentService->detachOpponent($case, $opponentId);
```

---

## Import Procedures

### 1. Standard Import (Up to 5 Opponents)

#### Using Extended Template
1. **Download Template**: Cases Import → Extended Template
2. **Fill Data**: Use `opponent1_name`, `opponent1_capacity`, etc.
3. **Upload File**: Standard import process
4. **Verify Results**: Check opponents in case details

#### Template Columns
```
opponent1_name, opponent1_capacity
opponent2_name, opponent2_capacity
opponent3_name, opponent3_capacity
opponent4_name, opponent4_capacity
opponent5_name, opponent5_capacity
```

### 2. Companion Import (Unlimited Opponents)

#### For Cases with 5+ Opponents
1. **Import Cases First**: Use Standard/Extended template
2. **Download Companion Template**: Case Opponents template
3. **Fill Opponent Data**: One row per opponent-case relationship
4. **Upload Companion File**: Use "Case Opponents" import option
5. **Verify Linking**: Check opponents appear in case details

#### Companion Template Columns
```
case_id, case_number
opponent_id, opponent_name
capacity_id, capacity_name
is_primary, display_order, alias_text
```

### 3. Bulk Import Best Practices

#### Data Preparation
- **Opponent Names**: Use exact names from existing opponents table
- **Capacity Names**: Use standard capacity names (Arabic/English)
- **Case References**: Use valid case IDs or case numbers
- **Primary Flags**: Only one `is_primary=1` per case

#### Validation Rules
- **Maximum Opponents**: Configurable limit (default: 10 per case)
- **Unique Constraint**: Same opponent + capacity combination not allowed
- **Primary Constraint**: Exactly one primary opponent per case
- **Soft Delete Support**: Can re-attach previously removed opponents

---

## Troubleshooting

### Common Issues

#### 1. "Opponent Not Found" Error
**Symptoms**: Import fails with opponent resolution errors
**Causes**: 
- Opponent name doesn't exist in database
- Name spelling mismatch
- Fuzzy matching threshold too high

**Solutions**:
1. **Check Opponent Exists**: Verify in Opponents management
2. **Use Exact Name**: Copy name from existing opponent record
3. **Create Opponent**: Add missing opponent to database first
4. **Adjust Fuzzy Matching**: Lower threshold in preflight settings

#### 2. "Maximum Opponents Exceeded" Error
**Symptoms**: Cannot add more opponents to case
**Causes**: Case already has maximum allowed opponents

**Solutions**:
1. **Check Current Count**: Count existing opponents
2. **Remove Unnecessary Opponents**: Delete unused opponents
3. **Increase Limit**: Update `config/importer.php` max_per_case setting
4. **Use Companion Import**: For bulk operations

#### 3. "Primary Opponent Conflict" Error
**Symptoms**: Multiple primary opponents or no primary opponent
**Causes**: Data inconsistency in primary opponent flags

**Solutions**:
1. **Check Primary Flags**: Verify only one `is_primary=1` per case
2. **Set Primary**: Use "Set as Primary" button for one opponent
3. **Data Cleanup**: Run integrity check and fix conflicts
4. **Manual Fix**: Update database directly if needed

#### 4. "Capacity Not Found" Error
**Symptoms**: Import fails with capacity resolution errors
**Causes**: Capacity name doesn't exist in option_values table

**Solutions**:
1. **Check Capacity Exists**: Verify in Option Values management
2. **Use Standard Names**: Use predefined capacity names
3. **Create Capacity**: Add missing capacity to option_values
4. **Use ID Instead**: Use capacity_id instead of capacity_name

### Data Integrity Issues

#### 1. Orphaned Opponent Records
**Symptoms**: Opponents exist but case is deleted
**Detection**: Query for opponents with non-existent cases
**Solution**: Clean up orphaned records

```sql
-- Find orphaned opponents
SELECT co.* FROM case_opponents co
LEFT JOIN cases c ON co.case_id = c.id
WHERE c.id IS NULL AND co.deleted_at IS NULL;
```

#### 2. Duplicate Primary Opponents
**Symptoms**: Multiple opponents marked as primary for same case
**Detection**: Count primary opponents per case
**Solution**: Fix primary opponent assignments

```sql
-- Find cases with multiple primary opponents
SELECT case_id, COUNT(*) as primary_count
FROM case_opponents
WHERE is_primary = 1 AND deleted_at IS NULL
GROUP BY case_id
HAVING primary_count > 1;
```

#### 3. Missing Primary Opponents
**Symptoms**: Cases with no primary opponent
**Detection**: Find cases without primary opponents
**Solution**: Set primary opponent for affected cases

```sql
-- Find cases without primary opponents
SELECT c.id, c.matter_name_ar, c.matter_name_en
FROM cases c
LEFT JOIN case_opponents co ON c.id = co.case_id AND co.is_primary = 1 AND co.deleted_at IS NULL
WHERE co.id IS NULL AND c.deleted_at IS NULL;
```

---

## Maintenance Procedures

### 1. Data Integrity Checks

#### Weekly Integrity Check
```bash
# Run integrity check command
php artisan opponents:check-integrity

# Check for orphaned records
php artisan opponents:check-orphans

# Verify primary opponent assignments
php artisan opponents:check-primary
```

#### Manual Database Queries
```sql
-- Check for data inconsistencies
SELECT 
    'Multiple Primary Opponents' as issue,
    case_id,
    COUNT(*) as count
FROM case_opponents
WHERE is_primary = 1 AND deleted_at IS NULL
GROUP BY case_id
HAVING count > 1

UNION ALL

SELECT 
    'Missing Primary Opponents' as issue,
    c.id as case_id,
    0 as count
FROM cases c
LEFT JOIN case_opponents co ON c.id = co.case_id AND co.is_primary = 1 AND co.deleted_at IS NULL
WHERE co.id IS NULL AND c.deleted_at IS NULL;
```

### 2. Performance Optimization

#### Index Maintenance
```sql
-- Check index usage
SHOW INDEX FROM case_opponents;

-- Analyze table performance
ANALYZE TABLE case_opponents;
```

#### Query Optimization
- Use proper indexes for frequent queries
- Monitor slow query log for case_opponents queries
- Consider partitioning for large datasets

### 3. Backup and Recovery

#### Backup Procedures
```bash
# Backup case_opponents table
mysqldump -u username -p database_name case_opponents > case_opponents_backup.sql

# Backup with data only
mysqldump -u username -p --no-create-info database_name case_opponents > case_opponents_data.sql
```

#### Recovery Procedures
```bash
# Restore case_opponents table
mysql -u username -p database_name < case_opponents_backup.sql

# Restore data only
mysql -u username -p database_name < case_opponents_data.sql
```

---

## Monitoring and Alerts

### Key Metrics to Monitor

#### 1. Opponent Count per Case
- **Threshold**: Cases with >10 opponents
- **Action**: Review for data quality issues
- **Query**: `SELECT case_id, COUNT(*) FROM case_opponents GROUP BY case_id HAVING COUNT(*) > 10`

#### 2. Primary Opponent Violations
- **Threshold**: Cases with 0 or >1 primary opponents
- **Action**: Fix primary opponent assignments
- **Query**: See integrity check queries above

#### 3. Orphaned Records
- **Threshold**: Any orphaned case_opponents records
- **Action**: Clean up orphaned records
- **Query**: See orphaned records query above

### Performance Monitoring

#### Slow Query Detection
```sql
-- Monitor slow queries involving case_opponents
SELECT * FROM mysql.slow_log 
WHERE sql_text LIKE '%case_opponents%'
ORDER BY start_time DESC;
```

#### Index Usage Analysis
```sql
-- Check index usage statistics
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    CARDINALITY,
    SUB_PART,
    PACKED
FROM information_schema.STATISTICS 
WHERE TABLE_NAME = 'case_opponents'
ORDER BY CARDINALITY DESC;
```

---

## Emergency Procedures

### 1. Data Corruption Recovery

#### If Primary Opponent Data is Corrupted
```sql
-- Reset all primary opponents
UPDATE case_opponents SET is_primary = 0;

-- Set first opponent as primary for each case
UPDATE case_opponents co1
SET is_primary = 1
WHERE co1.id = (
    SELECT MIN(co2.id) 
    FROM case_opponents co2 
    WHERE co2.case_id = co1.case_id 
    AND co2.deleted_at IS NULL
);
```

#### If Opponent Relationships are Broken
```sql
-- Rebuild relationships from legacy opponent_id
INSERT INTO case_opponents (case_id, opponent_id, is_primary, display_order, created_at, updated_at)
SELECT 
    id as case_id,
    opponent_id,
    1 as is_primary,
    1 as display_order,
    NOW() as created_at,
    NOW() as updated_at
FROM cases 
WHERE opponent_id IS NOT NULL 
AND id NOT IN (SELECT DISTINCT case_id FROM case_opponents);
```

### 2. Rollback Procedures

#### Rollback to Single Opponent Mode
```sql
-- Disable multi-opponent feature
UPDATE cases SET opponent_id = (
    SELECT opponent_id 
    FROM case_opponents 
    WHERE case_opponents.case_id = cases.id 
    AND is_primary = 1 
    AND deleted_at IS NULL 
    LIMIT 1
);
```

#### Complete Feature Disable
```sql
-- Soft delete all case_opponents records
UPDATE case_opponents SET deleted_at = NOW();
```

---

## Contact Information

### Support Contacts
- **Technical Issues**: Development Team
- **Data Issues**: Database Administrator
- **User Training**: System Administrator

### Documentation References
- **ADR**: `/docs/adr/ADR-20251025-001-Multi-Opponents-Per-Case.md`
- **Data Dictionary**: `/docs/data-dictionary.md`
- **Import Documentation**: `/docs/imports/cases_template.md`
- **API Documentation**: `/docs/api/`

---

## Change Log

| Date | Version | Changes | Author |
|---|---|---|---|
| 2025-10-25 | 1.0 | Initial runbook creation | AI Agent |

**Last Updated**: 2025-10-25 16:45 UTC
