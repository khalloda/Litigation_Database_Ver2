# Web UI Testing Checklist — Data Quality Dashboard

**Date**: 2025-10-08  
**Feature**: Data Quality Dashboard  
**URL**: `/data-quality`  
**Permission Required**: `admin.audit.view`  

---

## Pre-Test Setup

### 1. Ensure Database Has Production Data

```bash
cd clm-app

# Verify import ran successfully
php artisan data:quality

# Expected output: 7,209+ records
```

### 2. Create Test User (if needed)

```bash
php artisan tinker

# In tinker:
$user = User::create([
    'name' => 'Test Admin',
    'email' => 'admin@test.com',
    'password' => Hash::make('password'),
    'email_verified_at' => now(),
]);
$user->assignRole('super_admin');
exit
```

### 3. Start Local Server

```bash
php artisan serve
# Access: http://127.0.0.1:8000
```

---

## Manual Test Cases

### Test Case 1: Authentication & Authorization ✅

**Steps**:
1. Navigate to `http://127.0.0.1:8000/data-quality` without logging in
2. Expected: Redirect to `/login`
3. Login as super admin (`khelmy@sarieldin.com` / `P@ssw0rd`)
4. Navigate to `/data-quality` again
5. Expected: Dashboard loads successfully

**Pass Criteria**:
- ✅ Unauthenticated users redirected to login
- ✅ Authenticated super admin can access dashboard

---

### Test Case 2: Record Counts Section ✅

**Visual Elements to Verify**:
- [ ] Section title: "📊 Record Counts"
- [ ] 10 count cards displayed in responsive grid
- [ ] Each card shows entity name and count
- [ ] "TOTAL RECORDS" card highlighted in green
- [ ] All counts formatted with commas (e.g., "1,695" not "1695")

**Expected Counts** (approximate):
- Lawyers: 14
- Clients: 308
- Engagement Letters: 300
- Cases: 1,695
- Hearings: 369
- Contacts: 39
- Power of Attorneys: 3
- Admin Tasks: 4,077
- Admin Subtasks: 0
- Documents: 404
- **TOTAL**: 7,209

**Pass Criteria**:
- ✅ All counts display correctly
- ✅ Numbers formatted with thousand separators
- ✅ Total matches sum of individual counts
- ✅ Grid is responsive (test mobile, tablet, desktop)

---

### Test Case 3: Referential Integrity Section ✅

**Visual Elements to Verify**:
- [ ] Section title: "🔗 Referential Integrity"
- [ ] Table with 6 rows (one per relationship)
- [ ] Columns: Relationship, Status, Valid, Total, Orphans, Percentage
- [ ] Progress bars displayed for each relationship
- [ ] Color coding:
  - Green badges/bars for ≥95% (✓ Excellent)
  - Yellow badges/bars for 50-94% (! Needs Review)
  - Red badges/bars for <50% (✗ Critical)

**Expected Integrity** (100% on all):
| Relationship | Status | Valid | Total | Orphans | Percentage |
|---|---|---|---|---|---|
| Cases → Client | ✓ Excellent (green) | 1,695 | 1,695 | 0 | 100% |
| Hearings → Case | ✓ Excellent (green) | 369 | 369 | 0 | 100% |
| Tasks → Case | ✓ Excellent (green) | 4,077 | 4,077 | 0 | 100% |
| Subtasks → Task | N/A or ✗ Critical | 0 | 0 | 0 | 0% |
| Documents → Client | ✓ Excellent (green) | 404 | 404 | 0 | 100% |
| Contacts → Client | ✓ Excellent (green) | 39 | 39 | 0 | 100% |

**Pass Criteria**:
- ✅ All relationships show 100% (except Subtasks which is 0/0)
- ✅ Progress bars fill to 100% (green)
- ✅ All badges show "✓ Excellent" in green
- ✅ Orphan counts show "0" in green
- ✅ Table is horizontally scrollable on mobile

---

### Test Case 4: Data Completeness Section ✅

**Visual Elements to Verify**:
- [ ] Section title: "✅ Data Completeness (Key Fields)"
- [ ] Table with 5 rows (key fields)
- [ ] Columns: Field, Status, Filled, Total, Percentage
- [ ] Progress bars with color coding
- [ ] Status badges:
  - Green for ≥90%
  - Yellow for 50-89%
  - Red for <50%

**Expected Completeness**:
| Field | Status | Filled | Total | Percentage |
|---|---|---:|---:|---:|
| Cases Start Date | ✓ Excellent (green) | 1,612 | 1,695 | 95.1% |
| Cases Status | ✓ Excellent (green) | 1,681 | 1,695 | 99.17% |
| Hearings Date | ✗ Critical (red) | 0 | 369 | 0% |
| Tasks Status | ✗ Critical (red) | 1,673 | 4,077 | 41.04% |
| Documents Date | ✓ Excellent (green) | 404 | 404 | 100% |

**Pass Criteria**:
- ✅ Percentages calculated correctly
- ✅ Progress bars match percentages
- ✅ Color coding matches thresholds
- ✅ Red badges for low completeness fields (expected in legacy data)

---

### Test Case 5: Relationship Statistics Card ✅

**Visual Elements to Verify**:
- [ ] Card title: "📈 Relationship Statistics"
- [ ] 3 statistics displayed as list items
- [ ] Each stat has label and badge value

**Expected Statistics** (approximate):
- Avg. cases per client: **5.5** (badge)
- Avg. hearings per case: **0.22** (badge)
- Avg. tasks per case: **2.41** (badge)

**Pass Criteria**:
- ✅ Averages calculated correctly
- ✅ Values displayed as blue pill badges
- ✅ Labels are clear and readable

---

### Test Case 6: Top 10 Clients Table ✅

**Visual Elements to Verify**:
- [ ] Card title: "🏆 Top 10 Clients by Case Count"
- [ ] Table with 10 rows (or less if <10 clients)
- [ ] Columns: #, Client Name, Cases
- [ ] Arabic client names displayed correctly (RTL)
- [ ] Case counts in green badges

**Expected Top Client**:
- **#1**: أدخنة النخلة - **376 cases** (green badge)

**Pass Criteria**:
- ✅ Clients ordered by case count (descending)
- ✅ Arabic text displays correctly (no garbled characters)
- ✅ Top client has highest count (376)
- ✅ All 10 clients visible (or all clients if <10)

---

### Test Case 7: Responsive Design ✅

**Devices to Test**:

#### Desktop (1920x1080)
- [ ] All cards display in grid layout
- [ ] No horizontal scrolling required
- [ ] Tables fit within card bodies
- [ ] Progress bars visible and correctly sized

#### Tablet (768x1024)
- [ ] Cards stack vertically or in 2-column grid
- [ ] Tables remain readable
- [ ] No content overflow
- [ ] Touch-friendly tap targets

#### Mobile (375x667)
- [ ] All cards stack vertically
- [ ] Tables horizontally scrollable
- [ ] Text remains readable
- [ ] No zooming required for readability

**Pass Criteria**:
- ✅ Responsive breakpoints work correctly
- ✅ Content adapts to screen size
- ✅ No layout breaks or overlaps

---

### Test Case 8: Print Functionality ✅

**Steps**:
1. Click "🖨️ Print" button in top-right
2. Print preview dialog opens
3. Verify print layout

**Print Layout Verification**:
- [ ] Navigation bar hidden (CSS: `@media print`)
- [ ] Alert boxes hidden
- [ ] Print button hidden
- [ ] All data sections visible
- [ ] Tables formatted for printing
- [ ] Page breaks appropriate

**Pass Criteria**:
- ✅ Print preview shows data-only layout
- ✅ All metrics visible in print view
- ✅ No unnecessary UI elements in print

---

### Test Case 9: Data Accuracy Validation ✅

**Cross-Check with CLI Dashboard**:

```bash
# In terminal
php artisan data:quality
```

**Verification**:
- [ ] Web UI counts match CLI output
- [ ] Integrity percentages match
- [ ] Averages match
- [ ] Top clients match

**Pass Criteria**:
- ✅ Web UI and CLI show identical data
- ✅ Calculations are consistent

---

### Test Case 10: Performance ✅

**Metrics to Measure**:

1. **Page Load Time**:
   - Expected: <3 seconds
   - Measure: Browser DevTools Network tab
   
2. **Query Performance**:
   - Check Laravel debug bar or query log
   - Expected: <20 queries, <1 second total

3. **Memory Usage**:
   - Check with `php artisan horizon:snapshot` (if Horizon installed)
   - Expected: <100MB per request

**Pass Criteria**:
- ✅ Page loads in under 3 seconds
- ✅ No N+1 query issues
- ✅ No excessive memory usage
- ✅ Dashboard remains responsive under load

---

### Test Case 11: Error Handling ✅

**Scenarios to Test**:

1. **Empty Database**:
   ```bash
   php artisan migrate:fresh --seed
   # (Without running import:all)
   ```
   - Expected: Dashboard shows 0 counts, N/A percentages
   - No errors or crashes

2. **Permission Denied**:
   - Login as user without `admin.audit.view` permission
   - Expected: 403 Forbidden page

3. **Network Latency**:
   - Simulate slow connection (DevTools throttling)
   - Expected: Loading states (if implemented) or graceful wait

**Pass Criteria**:
- ✅ Graceful handling of empty data
- ✅ Clear permission denial message
- ✅ No console errors

---

### Test Case 12: Accessibility ✅

**WCAG 2.1 AA Compliance Checks**:

1. **Keyboard Navigation**:
   - [ ] Tab through all interactive elements
   - [ ] Print button accessible via keyboard
   - [ ] Focus indicators visible

2. **Screen Reader**:
   - [ ] Tables have proper headers
   - [ ] Progress bars have aria-labels
   - [ ] Badges have meaningful text

3. **Color Contrast**:
   - [ ] Text readable on all backgrounds
   - [ ] Status badges meet 4.5:1 contrast ratio
   - [ ] Progress bars distinguishable

4. **Text Scaling**:
   - [ ] Test at 200% zoom
   - [ ] No content overflow
   - [ ] Text remains readable

**Pass Criteria**:
- ✅ All elements keyboard accessible
- ✅ Screen reader friendly
- ✅ Color contrast meets WCAG AA
- ✅ Scales well with text zoom

---

## Automated Test Results

### Pest Tests

```bash
php artisan test --filter=DataQualityControllerTest
```

**Results**:
- ✅ Authentication test: PASSED
- ✅ Authorization test: PASSED
- ✅ Dashboard displays: PASSED (with production data)
- ⚠️ Factory-based tests: SKIPPED (factories not yet created)

**Note**: Factory-based tests will be completed in future sprints.

---

## Browser Compatibility

### Browsers to Test

- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile Safari (iOS)
- [ ] Chrome Mobile (Android)

**Pass Criteria**:
- ✅ Dashboard renders correctly in all browsers
- ✅ No console errors
- ✅ All features functional

---

## Known Issues / Limitations

1. **Arabic RTL Not Fully Implemented**: 
   - Arabic client names display correctly, but overall layout is LTR
   - To be addressed in T-08: Bilingual UI with RTL support

2. **No Real-Time Updates**:
   - Dashboard requires page refresh to see updated data
   - Future enhancement: Add "Refresh" button or auto-refresh

3. **No Filtering/Sorting**:
   - Top clients list is fixed (top 10)
   - Future enhancement: Allow filtering by date range, entity type, etc.

4. **Factories Not Implemented**:
   - Some automated tests skipped
   - To be addressed in future testing phase

---

## Test Sign-Off

### Test Environment

- **Laravel Version**: 10.49.1
- **PHP Version**: 8.4
- **MySQL Version**: 9.1.0
- **Browser**: [To be filled]
- **OS**: [To be filled]
- **Date Tested**: [To be filled]

### Test Results

- [ ] All manual tests passed
- [ ] Automated tests passed (where applicable)
- [ ] Browser compatibility verified
- [ ] Performance acceptable
- [ ] Accessibility requirements met
- [ ] Print functionality working
- [ ] Responsive design verified

### Tester Sign-Off

- **Name**: ____________________
- **Role**: ____________________
- **Date**: ____________________
- **Signature**: ____________________

### Stakeholder Approval

- **Name**: ____________________
- **Role**: ____________________
- **Date**: ____________________
- **Signature**: ____________________

---

## Next Steps

After successful testing:

1. [ ] Merge `feat/etl-importers` branch to `main`
2. [ ] Deploy to staging environment
3. [ ] UAT (User Acceptance Testing) with stakeholders
4. [ ] Address any UAT feedback
5. [ ] Deploy to production

---

**Prepared By**: AI Agent  
**Date**: 2025-10-08  
**Version**: 1.0

