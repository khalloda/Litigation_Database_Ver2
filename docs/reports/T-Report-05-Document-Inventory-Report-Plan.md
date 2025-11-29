# T-Report-05: Document Inventory Report - Detailed Plan

**Task ID**: T-Report-05  
**Priority**: High  
**Status**: Planning  
**Branch**: `feat/report-document-inventory`  
**Estimated Effort**: 3-4 days

---

## Overview

Generate comprehensive document inventory reports for tracking physical and digital documents. Supports filtering by client, case, document type, and location. Shows physical storage locations, deposit dates, movement cards, and document counts. Includes missing document flags and expiry/retention tracking if applicable.

---

## Use Cases

1. **Document Audits**: Complete inventory of all documents
2. **Vault Management**: Track physical document locations
3. **Compliance**: Verify document retention and expiry dates
4. **Client Reporting**: Show all documents related to a client
5. **Case Documentation**: List all documents for a specific case
6. **Location Tracking**: Find documents by physical location
7. **Movement History**: Track document movement via movement cards

---

## Features

### Core Features
- ✅ Filter by client (client_id)
- ✅ Filter by case (matter_id)
- ✅ Filter by document type (document_type)
- ✅ Filter by location (document_location)
- ✅ Filter by storage type (physical/digital/both)
- ✅ Show physical location (documents_location from clients table)
- ✅ Show deposit dates (deposit_date)
- ✅ Show document dates (document_date)
- ✅ Show movement card status (movement_card boolean)
- ✅ Show document counts by client/case
- ✅ Missing documents flag (if document expected but not found)
- ✅ Document type breakdown

### Advanced Features
- ✅ Expiry/retention tracking (if dates tracked in future)
- ✅ M-Files integration status (mfiles_uploaded, mfiles_id)
- ✅ File size totals by client/case
- ✅ Digital vs. physical document counts
- ✅ Department/staff assignment tracking

---

## Technical Design

### Database Queries

**Main Query** (client_documents with relationships):
```php
ClientDocument::with(['client', 'case'])
    ->when($clientId, fn($q) => $q->where('client_id', $clientId))
    ->when($caseId, fn($q) => $q->where('matter_id', $caseId))
    ->when($documentType, fn($q) => $q->where('document_type', $documentType))
    ->when($location, fn($q) => $q->where('document_location', $location))
    ->when($storageType, fn($q) => $q->where('document_storage_type', $storageType))
    ->orderBy('client_id', 'asc')
    ->orderBy('matter_id', 'asc')
    ->orderBy('deposit_date', 'desc')
    ->get();
```

**Document Counts by Client**:
```php
ClientDocument::select('client_id')
    ->selectRaw('COUNT(*) as total_documents')
    ->selectRaw('SUM(CASE WHEN document_storage_type = "physical" THEN 1 ELSE 0 END) as physical_count')
    ->selectRaw('SUM(CASE WHEN document_storage_type = "digital" THEN 1 ELSE 0 END) as digital_count')
    ->selectRaw('SUM(CASE WHEN document_storage_type = "both" THEN 1 ELSE 0 END) as both_count')
    ->selectRaw('SUM(file_size) as total_size')
    ->groupBy('client_id')
    ->with('client')
    ->get();
```

**Missing Documents** (cases with no documents):
```php
CaseModel::whereDoesntHave('documents')
    ->whereNotNull('matter_description') // Active cases
    ->with('client')
    ->get();
```

**Documents by Location**:
```php
ClientDocument::select('document_location')
    ->selectRaw('COUNT(*) as count')
    ->whereNotNull('document_location')
    ->groupBy('document_location')
    ->orderBy('count', 'desc')
    ->get();
```

### API Endpoint

**Route**: `POST /api/reports/document-inventory/{format}`  
**Format**: `pdf` or `excel`  
**Permission**: `reports.view`

**Request Body**:
```json
{
  "client_id": 123,
  "case_id": 456,
  "document_type": "contract|pleading|evidence",
  "location": "Vault A|Vault B|Office",
  "storage_type": "physical|digital|both",
  "show_missing": true,
  "group_by": "client|case|location|null",
  "orientation": "portrait|landscape"
}
```

**Response**: PDF download or Excel download

---

## Task Breakdown

### Task 5.1: Backend API Implementation
**ID**: T-Report-05.1  
**Estimated Time**: 1.5 days

#### Sub-tasks:
- [ ] Create `DocumentInventoryReportRequest` validation class
  - Validate client_id, case_id, document_type, location filters
  - Validate storage_type, group_by options
- [ ] Extend `ReportController` with `documentInventoryPdf()` method
  - Build query with filters
  - Calculate document counts
  - Identify missing documents if requested
  - Group data if needed
  - Generate PDF using Snappy
- [ ] Create `documentInventoryExcel()` method
  - Generate Excel with multiple sheets
  - Sheet 1: Summary statistics
  - Sheet 2: Detailed inventory (grouped if requested)
  - Sheet 3: Documents by location
  - Sheet 4: Missing documents (if applicable)
  - Sheet 5: Counts by client/case
- [ ] Create Blade template: `resources/views/reports/document_inventory_pdf.blade.php`
  - RTL layout support
  - Table with document details
  - Location grouping if requested
  - Summary section with counts
- [ ] Add route in `routes/api.php`
  - `POST /api/reports/document-inventory/pdf`
  - `POST /api/reports/document-inventory/excel`

**DoD**:
- [ ] API endpoints return correct PDF/Excel files
- [ ] All filters work correctly
- [ ] Document counts accurate
- [ ] Missing document detection works
- [ ] Location grouping works
- [ ] Bilingual labels in output

---

### Task 5.2: Frontend Integration
**ID**: T-Report-05.2  
**Estimated Time**: 1 day

#### Sub-tasks:
- [ ] Update `ReportsPage.tsx` with Document Inventory Report widget
  - Add filter form (client, case, type, location, storage type)
  - Add show missing documents checkbox
  - Add group by selector
  - Add export format buttons (PDF/Excel)
- [ ] Create API service method: `fetchDocumentInventoryReport()`
  - Handle filter combinations
  - Handle file download
- [ ] Add loading states and error handling
- [ ] Add success notifications
- [ ] Test all filter combinations

**DoD**:
- [ ] UI matches existing report design patterns
- [ ] All filters work from frontend
- [ ] File downloads trigger correctly
- [ ] Error messages display properly
- [ ] Bilingual labels in UI

---

### Task 5.3: Testing
**ID**: T-Report-05.3  
**Estimated Time**: 0.5 days

#### Sub-tasks:
- [ ] Create feature test: `tests/Feature/Reports/DocumentInventoryReportTest.php`
  - Test PDF generation
  - Test Excel generation
  - Test filter combinations
  - Test permission checks
  - Test document count calculations
  - Test missing document detection
  - Test location grouping
  - Test empty results handling
- [ ] Manual testing with real data
- [ ] Performance testing with large datasets

**DoD**:
- [ ] All tests pass (>80% coverage)
- [ ] Tests cover all filter combinations
- [ ] Performance acceptable (< 5 seconds for 5000+ documents)

---

### Task 5.4: Documentation
**ID**: T-Report-05.4  
**Estimated Time**: 0.5 days

#### Sub-tasks:
- [ ] Update `/docs/reports.md` with Document Inventory Report section
  - API endpoint documentation
  - Request/response examples
  - Filter options
  - Use cases
- [ ] Add code comments
- [ ] Update `/docs/tasks-index.md`

**DoD**:
- [ ] Documentation complete and accurate

---

## Data Model Reference

### ClientDocument Model Fields Used
- `id`: Document ID
- `legacy_document_id`: Legacy document ID
- `client_id`: Client reference (FK)
- `matter_id`: Case reference (FK)
- `legacy_matter_name`: Legacy matter name
- `client_name`: Client name (text)
- `document_name`: File name (for digital)
- `document_type`: Document type (filter key)
- `document_description`: Description
- `file_path`: File path (for digital)
- `file_size`: File size in bytes
- `mime_type`: MIME type
- `document_storage_type`: physical/digital/both
- `mfiles_uploaded`: M-Files integration flag
- `mfiles_id`: M-Files document ID
- `department`: Department name
- `admin_staff`: Admin staff name
- `lawyer`: Lawyer name
- `responsible_lawyer`: Responsible lawyer
- `movement_card`: Movement card boolean
- `document_location`: Physical location (auto-synced from client)
- `deposit_date`: Deposit date (filter/sort key)
- `document_date`: Document date
- `case_number`: Case number
- `pages_count`: Page count
- `notes`: Notes

### Related Models
- **Client**: `client_name_ar/en`, `documents_location_id`
- **CaseModel**: `matter_name_ar/en`

---

## Output Format Specifications

### PDF Output
- **Page Size**: A4 (portrait or landscape)
- **Header**: Report title, filters applied, generation timestamp
- **Content**: 
  - Summary section: Total documents, by storage type, by location
  - Main table with columns:
    - Document ID
    - Client name
    - Case name
    - Document type
    - Description
    - Location
    - Deposit date
    - Document date
    - Pages count
    - Storage type
    - Movement card indicator
    - File size (if digital)
  - Missing documents section (if requested)
- **Footer**: Page numbers, total count

### Excel Output
- **Sheet 1 - Summary**: Statistics and counts
- **Sheet 2 - Detailed Inventory**: All documents with full details
- **Sheet 3 - By Location**: Documents grouped by location
- **Sheet 4 - By Client**: Documents grouped by client with counts
- **Sheet 5 - By Case**: Documents grouped by case with counts
- **Sheet 6 - Missing Documents**: Cases without documents (if requested)
- **Formatting**: 
  - Headers in bold
  - Date columns formatted
  - File size columns formatted (bytes/MB)
  - Location columns color-coded

---

## Missing Document Detection

### Criteria
A case is considered to have "missing documents" if:
1. Case has `matter_description` (is an active case)
2. Case has no associated documents in `client_documents` table
3. OR case has expected document types but they're missing (future enhancement)

### Optional Enhancement
- Track expected document types per case category
- Flag if expected documents are missing
- This requires additional configuration/data model

---

## Location Tracking

### Physical Location Sources
1. **Primary**: `client_documents.document_location` (auto-synced from client)
2. **Client-level**: `clients.documents_location_id` → `option_values.label`
3. **Case-level**: Can be derived from case metadata (future)

### Location Filtering
- Filter by exact location match
- Filter by location pattern (contains, starts with)
- Group by location for inventory organization

---

## Bilingual Support

### Language Keys Required
```php
'reports.document_inventory.title' => 'Document Inventory Report' / 'تقرير جرد المستندات',
'reports.document_inventory.location' => 'Location' / 'الموقع',
'reports.document_inventory.movement_card' => 'Movement Card' / 'بطاقة الحركة',
'reports.document_inventory.missing_documents' => 'Missing Documents' / 'المستندات المفقودة',
// ... more keys
```

---

## Performance Considerations

### Indexes Required
Ensure these indexes exist:
- `client_id` (FK index)
- `matter_id` (FK index)
- `document_type` (for filtering)
- `document_location` (for filtering)
- `deposit_date` (for sorting)
- Composite: `(client_id, matter_id)` for common queries

### Query Optimization
- Use eager loading for relationships
- Limit results or paginate for very large inventories
- Cache location lists for dropdowns
- Consider materialized views for counts/statistics

---

## Success Metrics

- [ ] Report generates in < 5 seconds for 5000+ documents
- [ ] All filters work correctly
- [ ] Document counts accurate
- [ ] Missing document detection works
- [ ] Location grouping works
- [ ] PDF output is print-ready
- [ ] Excel export opens correctly
- [ ] Bilingual output correct
- [ ] Permission checks enforced
- [ ] Test coverage > 80%

---

## Dependencies

- Existing: `barryvdh/laravel-snappy`, `maatwebsite/excel`
- Models: `ClientDocument`, `Client`, `CaseModel`
- Permission: `reports.view`

---

## Future Enhancements

1. **Expiry Tracking**: Add expiry_date field and track document retention
2. **Expected Documents**: Configure expected document types per case category
3. **Digital Document Links**: Include clickable links for digital documents in Excel
4. **Barcode Support**: Add barcode scanning for physical document tracking
5. **Movement History**: Track document movement history separately

---

## Related Tasks

- T-Report-02: Hearing Schedule Report (reference)
- T-Report-03: Administrative Tasks Report (reference)

---

**Last Updated**: 2025-01-15  
**Assigned To**: TBD

