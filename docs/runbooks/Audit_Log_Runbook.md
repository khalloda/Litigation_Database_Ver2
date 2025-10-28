# Audit Log Runbook

## Overview

The Central Litigation Management system uses **Spatie ActivityLog** to track all create, update, and delete operations on core entities. This provides comprehensive audit trails for compliance and debugging.

## Features

### ✅ **Automatic Logging**
- **All CRUD operations** on core models are automatically logged
- **User attribution** - tracks who performed each action
- **Timestamp tracking** - precise date/time of each operation
- **Change tracking** - only logs fields that actually changed
- **IP and User-Agent** tracking (when available)

### ✅ **Core Models Tracked**
- **Client** - client information changes
- **CaseModel** - case/matter updates
- **AdminTask** - task workflow changes
- **Hearing** - hearing scheduling and updates
- **Lawyer** - lawyer profile changes
- **Contact** - contact information updates
- **Document** - document uploads and metadata
- **PowerOfAttorney** - POA status changes
- **EngagementLetter** - contract updates
- **AdminSubtask** - subtask progress

### ✅ **Web Interface**
- **Audit Log Viewer** at `/audit-logs`
- **Advanced filtering** by entity type, action, user, date range
- **Search functionality** in descriptions and change data
- **Export to CSV** for external analysis
- **Detailed view** for individual activities
- **Print-friendly** layouts

## Access Control

### Permissions Required
- **`admin.audit.view`** - Required to access audit logs
- **Super Admin** - Has full access by default
- **Other roles** - Must be explicitly granted permission

### Security Features
- **Authentication required** - Must be logged in
- **Permission-based access** - Only authorized users
- **No sensitive data logging** - Passwords and secrets excluded
- **Configurable retention** - 7 years default (2555 days)

## Usage Guide

### 1. **Accessing Audit Logs**

```
URL: http://litigation.local/audit-logs
Login: khelmy@sarieldin.com
Password: P@ssw0rd
```

### 2. **Filtering Audit Logs**

#### **By Entity Type**
- Select specific models (Client, Case, AdminTask, etc.)
- View activities for specific entity types

#### **By Action**
- **Created** - New records added
- **Updated** - Existing records modified
- **Deleted** - Records removed

#### **By User**
- Filter by specific user who performed the action
- Track individual user activities

#### **By Date Range**
- Set start and end dates
- Focus on specific time periods

#### **By Search Term**
- Search in activity descriptions
- Search in change data (field values)

### 3. **Understanding Audit Entries**

#### **Activity Information**
- **Date/Time** - When the action occurred
- **User** - Who performed the action
- **Action** - Created/Updated/Deleted
- **Entity** - What type of record
- **Description** - Human-readable description

#### **Change Details**
- **Fields Changed** - Only modified fields are logged
- **Old vs New Values** - Before and after values
- **Data Types** - String, boolean, date, etc.

### 4. **Exporting Data**

#### **CSV Export**
- Click **"📄 Export CSV"** button
- Includes all filtered results (up to 1000 records)
- Contains: Date, User, Action, Entity, Description, Changes

#### **Print View**
- Click **"🖨️ Print"** button
- Removes navigation elements
- Optimized for paper printing

## Technical Details

### **Database Schema**
```sql
-- Main activity log table
activity_log:
- id (bigint, primary key)
- log_name (varchar) - 'client', 'case', 'admin_task', etc.
- description (text) - Human-readable description
- subject_type (varchar) - Model class name
- subject_id (bigint) - Record ID
- causer_type (varchar) - User model class
- causer_id (bigint) - User ID
- properties (json) - Change data
- event (varchar) - 'created', 'updated', 'deleted'
- batch_uuid (char) - For batch operations
- created_at (timestamp)
- updated_at (timestamp)
```

### **Configuration**
```php
// config/activitylog.php
'delete_records_older_than_days' => 2555, // 7 years
'enabled' => env('ACTIVITY_LOGGER_ENABLED', true),
'default_log_name' => 'default',
```

### **Model Configuration Example**
```php
// app/Models/Client.php
public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logOnly(['client_name_ar', 'client_name_en', 'status', ...])
        ->logOnlyDirty() // Only log changed fields
        ->dontSubmitEmptyLogs()
        ->useLogName('client')
        ->setDescriptionForEvent(fn($eventName) => "Client was {$eventName}");
}
```

## Troubleshooting

### **Common Issues**

#### **1. No Activities Logged**
```bash
# Check if ActivityLog is enabled
php artisan tinker
>>> config('activitylog.enabled')
# Should return: true
```

#### **2. Missing User Attribution**
- Ensure user is authenticated when performing actions
- Check that `causer_id` is being set correctly

#### **3. Too Many Logs**
- Adjust `logOnly()` configuration to limit tracked fields
- Use `logOnlyDirty()` to only log changes
- Consider archiving old logs

#### **4. Performance Issues**
- Add database indexes on frequently queried fields
- Consider pagination for large result sets
- Archive old logs periodically

### **Database Maintenance**

#### **Clean Old Logs**
```bash
# Spatie provides a cleanup command
php artisan activitylog:clean
```

#### **Manual Cleanup**
```sql
-- Delete logs older than 7 years
DELETE FROM activity_log 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 YEAR);
```

## Best Practices

### **1. Field Selection**
- **DO** log important business fields
- **DON'T** log sensitive data (passwords, tokens)
- **DO** use `logOnly()` to limit tracked fields
- **DON'T** log auto-updated fields (timestamps, counters)

### **2. Performance**
- **DO** use `logOnlyDirty()` to reduce log volume
- **DO** add database indexes for common queries
- **DON'T** log on every field update for high-frequency models

### **3. Security**
- **DO** restrict access with proper permissions
- **DO** review audit logs regularly
- **DON'T** log sensitive authentication data
- **DO** encrypt sensitive change data if needed

### **4. Compliance**
- **DO** maintain logs for required retention periods
- **DO** export logs for external compliance tools
- **DO** document audit log procedures
- **DON'T** modify historical audit logs

## Integration Points

### **With Trash System**
- Audit logs track when records are deleted
- Trash system captures full record snapshots
- Combined provide complete audit trail

### **With Data Quality Dashboard**
- Audit logs help identify data entry patterns
- Track data quality improvements over time
- Monitor user behavior and training needs

### **With RBAC System**
- Audit logs respect permission boundaries
- Track permission changes and assignments
- Monitor role-based access patterns

## Monitoring & Alerts

### **Key Metrics to Monitor**
- **Activity Volume** - Logs per hour/day
- **Error Rates** - Failed audit log writes
- **Storage Usage** - Database growth rate
- **Access Patterns** - Who's viewing logs

### **Alert Conditions**
- **High Volume** - Unusual activity spikes
- **Missing Logs** - Expected activities not logged
- **Permission Failures** - Unauthorized access attempts
- **Storage Thresholds** - Database size limits

---

## Quick Reference

### **URLs**
- **Audit Logs**: `/audit-logs`
- **Export**: `/audit-logs/export/csv`
- **Detail View**: `/audit-logs/{id}`

### **Commands**
```bash
# Clean old logs
php artisan activitylog:clean

# Check configuration
php artisan tinker
>>> config('activitylog')
```

### **Permissions**
- **View**: `admin.audit.view`
- **Super Admin**: Full access by default

### **Configuration Files**
- **Config**: `config/activitylog.php`
- **Models**: `app/Models/*.php` (getActivitylogOptions methods)
- **Views**: `resources/views/audit-logs/`
- **Controller**: `app/Http/Controllers/AuditLogController.php`

---

*Last Updated: 2025-10-09*
*Version: 1.0*
