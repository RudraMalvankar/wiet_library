# Reports.php Database Column Fixes - Complete Summary

## Date: October 26, 2025

## Overview
Fixed all SQL column mismatches in `admin/api/reports.php` that were causing "Column not found" errors. The root cause was queries referencing columns that didn't exist in the actual database schema.

---

## Issues Fixed

### 1. **RegistrationDate → AdmissionDate** (9 instances)
**Issue:** Queries referenced `RegistrationDate` but Member table has `AdmissionDate`

**Locations Fixed:**
- Line 728: New registrations count query
- Lines 753, 756: Registration trend query (DATE_FORMAT and WHERE clause)
- Lines 778, 785: Member list detail query (SELECT and ORDER BY)
- Lines 829, 831, 832: New registrations query (SELECT, WHERE, ORDER BY)

**Test Result:** ✅ Members report returns valid JSON

---

### 2. **Department → Group** (5 instances)
**Issue:** Queries referenced `Department` but Member table has `Group` column

**Locations Fixed:**
- Line 737-738: Group-wise distribution query (COALESCE and GROUP BY)
- Line 776: Member list detail query
- Line 794-798: Department report query (renamed to group report)
- Line 809: Top borrowers query
- Line 826: Registrations query

**Changes:**
- Column name changed from `Department` to `Group` (with backticks because it's a reserved keyword)
- Variable `$departments` renamed to `$groups` for clarity
- Comment changed from "Department-wise" to "Group-wise"

**Test Result:** ✅ Members report stats now working

---

### 3. **ReturnDate in Wrong Table** (5 instances)
**Issue:** Queries tried to access `ReturnDate` from Circulation table, but it only exists in Return table

**Locations Fixed:**
- Line 167: Total returned count - changed to query Return table
- Lines 197-203: Return trend query - changed FROM Circulation to Return
- Line 172: Overdue books - rewritten to use NOT EXISTS with Return table
- Lines 250-266: Detailed circulation records - added LEFT JOIN Return table
- Lines 530, 534, 589, 642: Currently issued books logic - rewritten using NOT EXISTS

**New Logic:**
```sql
-- Before (WRONG):
WHERE ReturnDate IS NULL

-- After (CORRECT):
WHERE NOT EXISTS (SELECT 1 FROM `Return` r WHERE r.CirculationID = c.CirculationID)
```

**Test Result:** ✅ Circulation report working correctly

---

### 4. **Fine Column in Circulation Table**
**Issue:** Query referenced `c.Fine` but Circulation table has no Fine column

**Location Fixed:**
- Lines 369-377: Pending fines calculation

**Solution:** Changed to use Return table's FineAmount and FinePaid columns
```sql
-- Before (WRONG):
SELECT SUM(c.Fine - COALESCE(fp.PaidAmount, 0))
FROM Circulation c ...

-- After (CORRECT):
SELECT COALESCE(SUM(r.FineAmount - r.FinePaid), 0)
FROM `Return` r
WHERE r.FineAmount > r.FinePaid
```

**Test Result:** ✅ Financial report working

---

### 5. **FinePayments Table Schema Mismatch**
**Issue:** Database had old FinePayments table with `Amount` column, but API expected new schema with `CirculationID`, `FineAmount`, and `PaidAmount`

**Solution:** 
- Created migration `004_update_finepayments_table.sql`
- Dropped old table and recreated with correct structure
- Removed `CREATE TABLE IF NOT EXISTS` from reports.php (line 342-357) - now relies on schema

**New Schema:**
```sql
CREATE TABLE FinePayments (
    PaymentID INT PRIMARY KEY AUTO_INCREMENT,
    CirculationID INT NOT NULL,
    MemberNo INT NOT NULL,
    FineAmount DECIMAL(10,2) NOT NULL,
    PaidAmount DECIMAL(10,2) NOT NULL,
    PaymentDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    PaymentMethod VARCHAR(50),
    ReceiptNo VARCHAR(50) UNIQUE,
    CollectedBy INT,
    Remarks TEXT,
    ...
)
```

**Test Result:** ✅ Financial report now working with correct columns

---

### 6. **Condition Reserved Keyword** (4 instances)
**Issue:** `Condition` is a SQL reserved word and must be escaped with backticks

**Locations Fixed:**
- Line 533: Damaged/lost count
- Lines 542, 545: Condition distribution query
- Line 585: Book inventory details
- Lines 620, 621, 624: Damaged books detail query

**Solution:** Wrapped all `Condition` references in backticks: `` `Condition` ``

**Test Result:** ✅ Inventory report working correctly

---

### 7. **Category → Subject** (5 instances)
**Issue:** Queries referenced `Category` but Books table has `Subject` column

**Locations Fixed:**
- Lines 557, 561: Subject-wise stock distribution (renamed from category-wise)
- Line 582: Books list detail query
- Line 601: Acquisitions query
- Line 635: Low stock alert query

**Test Result:** ✅ Inventory report fully functional

---

## Schema Validation

### Correct Column Names (from schema.sql):

**Member Table:**
- ✅ `MemberNo` (NOT MemberID)
- ✅ `MemberName` (NOT FullName)
- ✅ `Group` (NOT Department) - must use backticks
- ✅ `AdmissionDate` (NOT RegistrationDate)

**Books Table:**
- ✅ `CatNo` (NOT BookID)
- ✅ `Author1` (NOT Author)
- ✅ `Subject` (NOT Category)

**Circulation Table:**
- ✅ `CirculationID`, `MemberNo`, `AccNo`, `IssueDate`, `DueDate`, `Status`
- ❌ NO `ReturnDate` column
- ❌ NO `Fine` column

**Return Table:**
- ✅ `ReturnID`, `CirculationID`, `ReturnDate`, `FineAmount`, `FinePaid`
- ❌ NOT `PaidAmount` (that's in FinePayments table)

**Holding Table:**
- ✅ `AccNo`, `CatNo`, `Condition`, `Price`, `PurchaseDate`
- ⚠️  `Condition` must be in backticks (reserved word)

**FinePayments Table:**
- ✅ `CirculationID`, `MemberNo`, `FineAmount`, `PaidAmount`
- ❌ NOT just `Amount`

---

## Test Results Summary

### All 4 Report Endpoints Now Working ✅

1. **Circulation Report**
   ```
   GET /admin/api/reports.php?action=circulation&from=2025-01-01&to=2025-12-31
   Status: ✅ Success - Returns stats, charts, and details
   ```

2. **Financial Report**
   ```
   GET /admin/api/reports.php?action=financial&from=2025-01-01&to=2025-12-31
   Status: ✅ Success - Returns financial stats and payment records
   ```

3. **Inventory Report**
   ```
   GET /admin/api/reports.php?action=inventory&type=stats
   Status: ✅ Success - Returns book stats, condition and subject distribution
   ```

4. **Members Report**
   ```
   GET /admin/api/reports.php?action=members&type=stats
   Status: ✅ Success - Returns member stats, group distribution, trends
   ```

---

## Files Modified

1. **c:\xampp\htdocs\wiet_lib\admin\api\reports.php**
   - Total changes: ~50 column name fixes
   - Lines affected: 167-642
   - Status: ✅ All SQL errors resolved

2. **c:\xampp\htdocs\wiet_lib\database\migrations\004_update_finepayments_table.sql**
   - Status: ✅ Created and executed
   - Purpose: Update FinePayments table structure

---

## Lessons Learned

1. **Always check actual database schema** before writing queries
2. **Reserved keywords** (like Condition, Group) must be escaped with backticks
3. **Table relationships matter** - ReturnDate is in Return table, not Circulation
4. **Column naming conventions** - stick to one pattern (Subject vs Category)
5. **Migration strategy** - `CREATE TABLE IF NOT EXISTS` can hide schema mismatches

---

## Next Steps Recommended

1. ✅ **Remove redundant table creation** from reports.php (FinePayments CREATE TABLE)
2. ⚠️ **Update schema.sql** with FinePayments new structure
3. ⚠️ **Run BackupHistory migration** (003_create_backuphistory_table.sql)
4. ⚠️ **Test all report export functions** (PDF, CSV)
5. ⚠️ **Add database schema validation** to prevent future mismatches

---

## Commands Used for Testing

```powershell
# Test circulation report
Invoke-WebRequest -Uri "http://localhost/wiet_lib/admin/api/reports.php?action=circulation&from=2025-01-01&to=2025-12-31" -Method GET -UseBasicParsing

# Test financial report
Invoke-WebRequest -Uri "http://localhost/wiet_lib/admin/api/reports.php?action=financial&from=2025-01-01&to=2025-12-31" -Method GET -UseBasicParsing

# Test inventory report  
Invoke-WebRequest -Uri "http://localhost/wiet_lib/admin/api/reports.php?action=inventory&type=stats" -Method GET -UseBasicParsing

# Test members report
Invoke-WebRequest -Uri "http://localhost/wiet_lib/admin/api/reports.php?action=members&type=stats" -Method GET -UseBasicParsing
```

---

## Status: ✅ COMPLETE

All SQL column errors in reports.php have been resolved. All 4 report endpoints are returning valid JSON responses.
