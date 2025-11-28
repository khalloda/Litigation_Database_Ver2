# ADR-20250111-001: Universal ID Preservation Strategy

## Status
**ACCEPTED** - January 11, 2025

## Context

The Central Litigation Management system requires the ability to import data from legacy systems (MS Access) while preserving original record IDs. This is critical for:

1. **Referential Integrity**: Maintaining relationships between imported records
2. **Data Traceability**: Linking imported data back to source systems
3. **Incremental Updates**: Updating existing records during re-imports
4. **External System Integration**: Maintaining consistent IDs across systems

Previously, only `lawyers` and `clients` tables had ID preservation enabled. The user has requested that **ALL core model tables** support ID preservation during import operations.

## Decision

We will implement **Universal ID Preservation** across all core model tables by:

### 1. Database Schema Changes
- Disable `AUTO_INCREMENT` on `id` columns for all core tables
- Allow explicit `id` values to be inserted during import operations
- Maintain rollback capability to re-enable `AUTO_INCREMENT` when needed

### 2. Import System Updates
- Update `MappingEngine` to include `id` column for all core tables
- Ensure import validation handles explicit ID values
- Maintain referential integrity during import operations

### 3. Tables Affected
All core model tables now support ID preservation:

| Table | Purpose | Auto-Increment Status |
|-------|---------|----------------------|
| `lawyers` | Legal professionals | **DISABLED** |
| `clients` | Client entities | **DISABLED** |
| `cases` | Legal cases | **DISABLED** |
| `hearings` | Court hearings | **DISABLED** |
| `engagement_letters` | Legal engagement documents | **DISABLED** |
| `contacts` | Contact information | **DISABLED** |
| `power_of_attorneys` | Power of attorney documents | **DISABLED** |
| `admin_tasks` | Administrative tasks | **DISABLED** |
| `admin_subtasks` | Task subtasks | **DISABLED** |
| `client_documents` | Client-related documents | **DISABLED** |
| `option_sets` | Managed dropdown sets | **DISABLED** |
| `option_values` | Dropdown option values | **DISABLED** |

## Implementation Details

### Database Migrations
Each table has a corresponding migration that:
```sql
-- Disable auto-increment
ALTER TABLE {table_name} MODIFY COLUMN id INT(11) NOT NULL;

-- Rollback (re-enable auto-increment)
ALTER TABLE {table_name} MODIFY COLUMN id INT(11) NOT NULL AUTO_INCREMENT;
```

### Import Mapping
The `MappingEngine` service now includes `id` columns for all core tables:
```php
$idPreservationTables = [
    'lawyers', 'clients', 'cases', 'hearings', 
    'engagement_letters', 'contacts', 'power_of_attorneys', 
    'admin_tasks', 'admin_subtasks', 'client_documents',
    'option_sets', 'option_values'
];
```

### Rollback Strategy
- All migrations include `down()` methods to re-enable auto-increment
- Can be rolled back individually or as a batch
- System can return to normal auto-increment mode when needed

## Consequences

### Positive
- **Complete ID Preservation**: All core tables support original ID import
- **Referential Integrity**: Relationships maintained during import
- **Data Consistency**: IDs remain consistent across imports
- **Flexibility**: Can import data from multiple sources with original IDs
- **Traceability**: Easy to trace records back to source systems

### Negative
- **Manual ID Management**: Must ensure unique IDs during import
- **Import Complexity**: Requires careful handling of ID conflicts
- **Development Impact**: New records must use explicit IDs
- **Rollback Dependency**: Must rollback before normal auto-increment operation

### Risks
- **ID Conflicts**: Risk of duplicate IDs if not properly managed
- **Import Failures**: Invalid IDs can cause import failures
- **Data Integrity**: Must validate ID uniqueness before import

## Mitigation Strategies

### 1. Import Validation
- Validate ID uniqueness before import
- Check for ID conflicts with existing records
- Provide clear error messages for ID issues

### 2. Documentation
- Comprehensive documentation of ID preservation strategy
- Clear rollback procedures
- Import best practices guide

### 3. Monitoring
- Log all ID preservation operations
- Track import success/failure rates
- Monitor for ID conflicts

## Alternatives Considered

### 1. Hybrid Approach
- Some tables with ID preservation, others without
- **Rejected**: Inconsistent approach, complexity in relationships

### 2. UUID Strategy
- Use UUIDs instead of integer IDs
- **Rejected**: Major schema change, compatibility issues

### 3. Mapping Table
- Separate table to map original IDs to new IDs
- **Rejected**: Added complexity, performance impact

## Monitoring & Maintenance

### Regular Tasks
- Monitor import success rates
- Check for ID conflicts in logs
- Validate referential integrity after imports

### Rollback Triggers
- Performance issues with manual ID management
- Need for normal auto-increment operation
- User request to revert to standard mode

## Related Documentation
- [ID Preservation Import Runbook](../runbooks/ID-Preservation-Import-Runbook.md)
- [ETL Import Runbook](../runbooks/ETL_Import_Runbook.md)
- [Import/Export Phase 1 Summary](../SESSION-SUMMARY-ETL-COMPLETE.md)

---

**Decision Date**: January 11, 2025  
**Review Date**: TBD  
**Status**: ACTIVE  
**Owner**: Development Team
