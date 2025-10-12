# Universal ID Preservation Runbook

## Overview
This runbook provides step-by-step procedures for managing the Universal ID Preservation system implemented across all core model tables in the Central Litigation Management system.

## Current Status
**ACTIVE** - All core tables have auto-increment disabled for ID preservation.

### Tables with ID Preservation Enabled
- ✅ `lawyers`
- ✅ `clients` 
- ✅ `cases`
- ✅ `hearings`
- ✅ `engagement_letters`
- ✅ `contacts`
- ✅ `power_of_attorneys`
- ✅ `admin_tasks`
- ✅ `admin_subtasks`
- ✅ `client_documents`
- ✅ `option_sets`
- ✅ `option_values`

## Import Operations

### Before Import
1. **Verify Auto-Increment Status**
   ```bash
   cd clm-app
   php artisan tinker --execute="
   \$tables = ['lawyers', 'clients', 'cases', 'hearings', 'engagement_letters', 'contacts', 'power_of_attorneys', 'admin_tasks', 'admin_subtasks', 'client_documents', 'option_sets', 'option_values'];
   foreach(\$tables as \$table) {
       \$result = DB::select('SHOW TABLE STATUS LIKE \"' . \$table . '\"');
       echo \$table . ': ' . (\$result[0]->Auto_increment ?? 'NULL') . PHP_EOL;
   }
   "
   ```

2. **Prepare Excel Files**
   - Ensure Excel files contain `id` columns
   - Verify ID values are unique within each file
   - Check for ID conflicts with existing database records

3. **Backup Database** (Recommended)
   ```bash
   mysqldump -u username -p database_name > backup_before_import_$(date +%Y%m%d_%H%M%S).sql
   ```

### During Import
1. **Use Import Module**
   - Navigate to Import module in web interface
   - Select appropriate Excel file
   - Verify mapping includes `id` column
   - Run validation before import

2. **Monitor Import Process**
   - Check for ID conflict errors
   - Monitor import progress
   - Review validation results

3. **Handle Errors**
   - **ID Conflicts**: Resolve duplicate IDs in Excel file
   - **Invalid IDs**: Ensure IDs are positive integers
   - **Referential Integrity**: Check foreign key relationships

### After Import
1. **Verify Data Integrity**
   ```bash
   # Check for duplicate IDs
   php artisan tinker --execute="
   \$tables = ['lawyers', 'clients', 'cases'];
   foreach(\$tables as \$table) {
       \$duplicates = DB::select('SELECT id, COUNT(*) as count FROM ' . \$table . ' GROUP BY id HAVING count > 1');
       if(!empty(\$duplicates)) {
           echo 'DUPLICATE IDs in ' . \$table . ': ' . count(\$duplicates) . PHP_EOL;
       }
   }
   "
   ```

2. **Test Relationships**
   - Verify foreign key relationships work correctly
   - Check that related records can be accessed
   - Test CRUD operations on imported data

## Rollback Procedures

### Temporary Rollback (Re-enable Auto-Increment)
If you need to temporarily enable auto-increment for normal operations:

```bash
cd clm-app

# Rollback specific table (example: lawyers)
php artisan migrate:rollback --path=database/migrations/2025_10_12_080123_disable_auto_increment_lawyers_id_for_import.php

# Rollback all ID preservation migrations
php artisan migrate:rollback --step=12
```

### Re-enable ID Preservation
To restore ID preservation after rollback:

```bash
cd clm-app
php artisan migrate
```

### Permanent Disable (Not Recommended)
If you need to permanently disable ID preservation:

1. **Rollback All Migrations**
   ```bash
   php artisan migrate:rollback --step=12
   ```

2. **Update MappingEngine**
   ```php
   // In app/Services/MappingEngine.php
   $idPreservationTables = []; // Empty array
   ```

3. **Remove Migration Files**
   ```bash
   rm database/migrations/*disable_auto_increment*_id_for_import.php
   ```

## Troubleshooting

### Common Issues

#### 1. Import Fails with "Duplicate ID" Error
**Cause**: Excel file contains duplicate IDs or conflicts with existing data

**Solution**:
```bash
# Check for existing IDs
php artisan tinker --execute="
\$table = 'clients'; // Replace with actual table
\$existingIds = DB::table(\$table)->pluck('id')->toArray();
echo 'Existing IDs: ' . implode(', ', array_slice(\$existingIds, 0, 10)) . '...';
"
```

#### 2. "Auto-increment is enabled" Error
**Cause**: Auto-increment was accidentally re-enabled

**Solution**:
```bash
# Re-run ID preservation migrations
php artisan migrate
```

#### 3. Foreign Key Constraint Errors
**Cause**: Referenced records don't exist

**Solution**:
- Import parent tables first (e.g., lawyers before clients)
- Verify foreign key values exist in referenced tables
- Check import order and dependencies

#### 4. Import Validation Fails
**Cause**: Excel file structure doesn't match expected format

**Solution**:
- Verify column names match database columns
- Check data types (especially dates)
- Ensure required fields are not empty

### Emergency Procedures

#### Quick Status Check
```bash
# Check migration status
php artisan migrate:status | grep disable_auto_increment

# Check specific table auto-increment
php artisan tinker --execute="
\$table = 'clients'; // Replace with table name
\$result = DB::select('SHOW TABLE STATUS LIKE \"' . \$table . '\"');
echo \$table . ' auto_increment: ' . (\$result[0]->Auto_increment ?? 'NULL');
"
```

#### Reset to Normal Mode
```bash
# Emergency reset to normal auto-increment mode
php artisan migrate:rollback --step=12
```

## Best Practices

### 1. Import Order
Import tables in dependency order:
1. `option_sets` → `option_values`
2. `lawyers`
3. `clients` (depends on lawyers, options)
4. `cases` (depends on clients, lawyers)
5. `hearings` (depends on cases)
6. `engagement_letters` (depends on clients)
7. `contacts` (depends on clients)
8. `power_of_attorneys` (depends on clients)
9. `admin_tasks` → `admin_subtasks`
10. `client_documents` (depends on clients)

### 2. Data Validation
- Always validate Excel files before import
- Check for ID conflicts with existing data
- Verify foreign key relationships
- Test with small sample first

### 3. Monitoring
- Monitor import logs for errors
- Check database integrity after imports
- Verify referential integrity
- Test application functionality

### 4. Documentation
- Document any custom import procedures
- Keep records of import sessions
- Note any data transformations applied
- Track import success/failure rates

## Related Documentation
- [ADR-20250111-001: Universal ID Preservation Strategy](../adr/ADR-20250111-001-Universal-ID-Preservation-Strategy.md)
- [ETL Import Runbook](ETL_Import_Runbook.md)
- [ID Preservation Import Runbook](ID-Preservation-Import-Runbook.md)

---

**Last Updated**: January 11, 2025  
**Version**: 1.0  
**Maintainer**: Development Team
