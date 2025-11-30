# Performance Considerations for Operational Reports

**Date**: 2025-01-15  
**Status**: Documentation  
**Applies To**: All Operational Reports (T-Report-02, T-Report-03, T-Report-04, T-Report-05)

---

## Overview

This document outlines performance considerations, optimization strategies, and best practices for the operational reports system. These guidelines ensure reports remain responsive and efficient, even with large datasets.

---

## Performance Targets

### Response Time Goals

- **PDF Generation**: < 3 seconds for up to 1000 records
- **Excel Export**: < 5 seconds for up to 5000 records
- **Query Execution**: < 2 seconds for filtered queries
- **Total Request Time**: < 10 seconds for complex reports

### Scalability Targets

- Support up to 10,000 hearings/tasks/documents without timeout
- Handle date ranges up to 1 year without performance degradation
- Support concurrent report generation for multiple users

---

## Current Implementation Performance

### Query Optimization

#### 1. Eager Loading

All reports use eager loading to prevent N+1 query problems:

```php
// ✅ Good: Eager loading relationships
$query = AdminTask::with(['case.client', 'lawyer', 'subtasks']);

// ❌ Bad: Lazy loading (N+1 queries)
$tasks = AdminTask::all();
foreach ($tasks as $task) {
    $task->case->client; // Multiple queries
}
```

**Reports Using Eager Loading**:
- Hearing Schedule Report: `Hearing::with(['case.client', 'lawyer', 'case.court'])`
- Administrative Tasks Report: `AdminTask::with(['case.client', 'lawyer', 'subtasks'])`
- Case Status Dashboard: `CaseModel::with(['client', 'court', 'latestHearing', 'adminTasks'])`
- Document Inventory Report: `ClientDocument::with(['client', 'case'])`

#### 2. Index Recommendations

**Critical Indexes** (verify these exist):

```sql
-- Hearings table
CREATE INDEX idx_hearings_date ON hearings(date);
CREATE INDEX idx_hearings_matter_id ON hearings(matter_id);
CREATE INDEX idx_hearings_lawyer_id ON hearings(lawyer_id);
CREATE INDEX idx_hearings_date_status ON hearings(date, decision);

-- Admin Tasks table
CREATE INDEX idx_admin_tasks_execution_date ON admin_tasks(execution_date);
CREATE INDEX idx_admin_tasks_matter_id ON admin_tasks(matter_id);
CREATE INDEX idx_admin_tasks_lawyer_id ON admin_tasks(lawyer_id);
CREATE INDEX idx_admin_tasks_status_result ON admin_tasks(status, result);
CREATE INDEX idx_admin_tasks_alert ON admin_tasks(alert) WHERE alert = 1;

-- Cases table
CREATE INDEX idx_cases_status ON cases(matter_status);
CREATE INDEX idx_cases_client_id ON cases(client_id);
CREATE INDEX idx_cases_court_id ON cases(court_id);
CREATE INDEX idx_cases_category ON cases(matter_category_id);

-- Client Documents table
CREATE INDEX idx_documents_client_id ON client_documents(client_id);
CREATE INDEX idx_documents_matter_id ON client_documents(matter_id);
CREATE INDEX idx_documents_deposit_date ON client_documents(deposit_date);
CREATE INDEX idx_documents_storage_type ON client_documents(document_storage_type);
CREATE INDEX idx_documents_location ON client_documents(document_location);
```

#### 3. Query Filtering Best Practices

**Date Range Filtering**:
- Always use indexed columns for date filtering
- Limit date ranges to reasonable periods (recommended: 1-3 months)
- Use `whereBetween()` for date ranges instead of multiple `where()` clauses

**Filter Order**:
- Apply most selective filters first (e.g., specific IDs before status filters)
- Use indexed columns in WHERE clauses before non-indexed ones

**Example**:
```php
// ✅ Good: Selective filter first, then date range
$query->where('lawyer_id', $lawyerId)
      ->whereBetween('execution_date', [$start, $end]);

// ⚠️ Less Optimal: Date range first (unless lawyer_id is more selective)
$query->whereBetween('execution_date', [$start, $end])
      ->where('lawyer_id', $lawyerId);
```

---

## Excel Export Performance

### Memory Management

**Current Implementation**:
- Uses `PhpOffice/PhpSpreadsheet` directly
- Processes data in collections before generating Excel
- No streaming/chunking implemented (future optimization)

**Memory Considerations**:
- Each spreadsheet cell consumes memory
- Multi-sheet exports multiply memory usage
- Large datasets (10,000+ rows) may require chunking

### Optimization Strategies

#### 1. Limit Result Sets

**Current**: All reports fetch all matching records

**Recommendation**: For very large datasets, consider:
```php
// Limit Excel exports to reasonable sizes
if ($request->input('format') === 'excel') {
    $query->limit(10000); // Hard limit for Excel exports
}
```

#### 2. Chunk Processing (Future Enhancement)

For exports > 10,000 rows:
```php
// Process in chunks to reduce memory usage
$tasks->chunk(1000, function ($chunk) use ($sheet, $row) {
    foreach ($chunk as $task) {
        // Add to sheet
    }
});
```

#### 3. Optimize Sheet Generation

**Current**: All sheets generated regardless of data size

**Recommendation**: Skip empty sheets or consolidate small sheets

---

## PDF Generation Performance

### Snappy Configuration

**Current Settings**:
```php
->setPaper('a4', $orientation)
->setOption('margin-top', '20mm')
->setOption('margin-bottom', '20mm')
```

**Performance Optimizations**:
- Reduce margin sizes for multi-page reports
- Use `disable-smart-shrinking` for faster rendering
- Consider `lowquality` option for draft reports

### Template Optimization

**Current**: All data passed to Blade templates

**Recommendation**: 
- Limit data passed to views
- Use pagination for large datasets in PDFs
- Consider summary views for very large datasets

---

## Caching Strategies

### Report Result Caching

**Not Currently Implemented** - Consider for future:

```php
// Cache filtered results for 5 minutes
$cacheKey = 'report:admin-tasks:' . md5(json_encode($validated));
$tasks = Cache::remember($cacheKey, 300, function () use ($query) {
    return $query->get();
});
```

**When to Cache**:
- Frequently accessed reports with same filters
- Reports with expensive calculations (completion rates, statistics)
- Dashboard reports with relatively static data

**When NOT to Cache**:
- Reports requiring real-time data accuracy
- Reports with user-specific filters
- Overdue/missing document detection (requires current data)

### Statistics Caching

**Case Status Dashboard** calculations could benefit from caching:

```php
// Cache statistics for 15 minutes
$statsCacheKey = 'report:case-stats:' . md5(json_encode($filters));
$stats = Cache::remember($statsCacheKey, 900, function () {
    // Calculate statistics
});
```

---

## Database Optimization

### Query Optimization Checklist

- [ ] Verify all foreign key columns have indexes
- [ ] Verify date columns used in filters have indexes
- [ ] Check for composite indexes on frequently filtered columns
- [ ] Monitor slow query log for report-related queries
- [ ] Analyze EXPLAIN plans for complex queries

### Monitoring Queries

```sql
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2; -- Log queries > 2 seconds

-- Monitor index usage
SHOW INDEX FROM hearings;
SHOW INDEX FROM admin_tasks;
SHOW INDEX FROM cases;
SHOW INDEX FROM client_documents;
```

---

## Performance Testing Guidelines

### Test Scenarios

1. **Small Dataset** (< 100 records)
   - Expected: < 1 second
   - Verify: All features work correctly

2. **Medium Dataset** (100-1000 records)
   - Expected: < 3 seconds
   - Verify: No timeout issues

3. **Large Dataset** (1000-5000 records)
   - Expected: < 5 seconds
   - Verify: Memory usage acceptable

4. **Very Large Dataset** (5000-10000 records)
   - Expected: < 10 seconds
   - Verify: No memory errors

5. **Extreme Dataset** (> 10000 records)
   - Expected: May require pagination/chunking
   - Verify: Error handling works

### Performance Testing Commands

```bash
# Laravel Debugbar (if installed)
# Enable in development to monitor queries

# Manual timing
$start = microtime(true);
// ... generate report ...
$duration = microtime(true) - $start;
Log::info("Report generated in {$duration} seconds");
```

---

## Optimization Recommendations by Report

### Hearing Schedule Report

**Current Performance**: Good for up to 1000 hearings

**Optimization Opportunities**:
1. Add composite index on `(date, matter_id, lawyer_id)`
2. Cache court/case/lawyer lookups
3. Consider pagination for very large date ranges

### Administrative Tasks Report

**Current Performance**: Good for up to 5000 tasks

**Optimization Opportunities**:
1. Add composite index on `(execution_date, status, result)`
2. Optimize completion rate calculations (could cache)
3. Consider limiting subtask loading for very large task sets

### Case Status Dashboard Report

**Current Performance**: Good (single-page summary)

**Optimization Opportunities**:
1. Cache statistics calculations
2. Optimize attention-required queries (could be expensive)
3. Limit recent activity to top 20 (already implemented)

### Document Inventory Report

**Current Performance**: Good for up to 5000 documents

**Optimization Opportunities**:
1. Add composite index on `(client_id, matter_id, deposit_date)`
2. Optimize missing documents query (uses `whereDoesntHave`)
3. Consider pagination for location/grouped views

---

## Monitoring & Alerting

### Metrics to Monitor

1. **Report Generation Time**: Track average and P95/P99 percentiles
2. **Database Query Count**: Should be < 20 queries per report
3. **Memory Usage**: Should stay < 128MB per request
4. **Error Rate**: Should be < 1% for report generation

### Recommended Monitoring

```php
// Add to report generation methods
Log::channel('reports')->info('Report generated', [
    'report' => 'admin-tasks',
    'format' => 'excel',
    'duration' => $duration,
    'record_count' => $tasks->count(),
    'memory_usage' => memory_get_peak_usage(true),
]);
```

---

## Future Optimizations

### Priority 1 (High Impact)

1. **Database Indexes**: Verify and add missing indexes (see recommendations above)
2. **Query Optimization**: Review EXPLAIN plans for slow queries
3. **Result Limiting**: Add hard limits for Excel exports (10,000 rows)

### Priority 2 (Medium Impact)

1. **Caching**: Implement caching for statistics and frequently accessed reports
2. **Chunking**: Implement chunk processing for large Excel exports
3. **Background Jobs**: Consider queue-based report generation for very large reports

### Priority 3 (Low Impact)

1. **CDN Caching**: Cache generated PDF files for frequently accessed reports
2. **Database Read Replicas**: Use read replicas for report queries
3. **Redis Caching**: Use Redis for distributed caching

---

## Troubleshooting Performance Issues

### Common Issues

1. **Slow Query Execution**
   - Check EXPLAIN plans
   - Verify indexes exist and are used
   - Consider query optimization or restructuring

2. **High Memory Usage**
   - Reduce dataset size
   - Implement chunking
   - Optimize eager loading relationships

3. **Timeout Errors**
   - Increase PHP execution time limit for reports
   - Consider background job processing
   - Add pagination or result limits

4. **Slow PDF Generation**
   - Check wkhtmltopdf installation
   - Reduce template complexity
   - Consider alternative PDF generation methods

---

## Related Documents

- [Category 1 Operational Reports Plan](./Category-1-Operational-Reports-Plan.md)
- [Main Reports Documentation](../reports.md)
- [Data Dictionary](../data-dictionary.md)

---

**Last Updated**: 2025-01-15  
**Next Review**: After Phase 3 testing and performance analysis

