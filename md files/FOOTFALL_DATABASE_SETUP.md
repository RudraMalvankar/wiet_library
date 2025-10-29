# 🔧 Footfall System - Database Setup Required

## ❌ Issue Detected

The diagnostic tests are failing because the **database migration has not been run yet**.

The Footfall table exists, but it's missing the 6 new columns needed for the enhanced footfall system:

- `EntryTime` (DATETIME)
- `ExitTime` (DATETIME)
- `Purpose` (VARCHAR)
- `Status` (VARCHAR)
- `EntryMethod` (VARCHAR)
- `WorkstationUsed` (VARCHAR)

---

## ✅ Solution: Run Database Migration

I've created **3 tools** to help you fix this:

### 🎯 Option 1: Automatic Migration (Easiest)

**Open this page in your browser:**

```
http://localhost/wiet_lib/admin/run-migration-page.php
```

**Then:**

1. Click the big **"Run Migration Now"** button
2. Wait 2-3 seconds
3. See success message ✅
4. Go back to diagnostic tool to verify

---

### 🔍 Option 2: Check Database Status First

**Open this page to see what's missing:**

```
http://localhost/wiet_lib/admin/check-database.php
```

This shows:

- ✓ Which columns exist
- ✗ Which columns are missing
- ✓ SQL Views status
- ✓ Indexes status
- 📊 Sample data from Footfall table

---

### 📝 Option 3: Manual Migration (Via phpMyAdmin)

**If automatic migration doesn't work:**

1. **Open phpMyAdmin:**

   ```
   http://localhost/phpmyadmin
   ```

2. **Select Database:**

   - Click `wiet_library` in left sidebar

3. **Open SQL Tab:**

   - Click the **SQL** tab at top

4. **Copy Migration SQL:**

   - Open file: `database/migrations/006_enhance_footfall_tracking.sql`
   - Copy entire contents

5. **Paste and Execute:**
   - Paste SQL into the text box
   - Click **Go** button
   - Wait for success message

---

## 📋 What the Migration Does

### 1. Adds 6 New Columns

```sql
ALTER TABLE Footfall
ADD COLUMN EntryTime DATETIME DEFAULT NULL,
ADD COLUMN ExitTime DATETIME DEFAULT NULL,
ADD COLUMN Purpose VARCHAR(100) DEFAULT 'Library Visit',
ADD COLUMN Status VARCHAR(20) DEFAULT 'Active',
ADD COLUMN EntryMethod VARCHAR(50) DEFAULT 'Manual',
ADD COLUMN WorkstationUsed VARCHAR(50) DEFAULT NULL;
```

### 2. Updates Existing Records

```sql
UPDATE Footfall
SET EntryTime = TIMESTAMP(Date, TimeIn),
    ExitTime = TIMESTAMP(Date, TimeOut),
    Status = CASE WHEN TimeOut IS NULL THEN 'Active' ELSE 'Completed' END;
```

This converts old `Date + TimeIn/TimeOut` format to new `EntryTime/ExitTime` format.

### 3. Creates 3 Performance Indexes

```sql
CREATE INDEX idx_entry_time ON Footfall(EntryTime);
CREATE INDEX idx_status ON Footfall(Status);
CREATE INDEX idx_entry_method ON Footfall(EntryMethod);
```

Makes queries faster for analytics dashboard.

### 4. Creates 3 SQL Views

```sql
CREATE VIEW FootfallDailyStats AS ...
CREATE VIEW FootfallHourlyStats AS ...
CREATE VIEW MemberFootfallSummary AS ...
```

Pre-calculates statistics for charts and reports.

---

## 🧪 After Running Migration

### Verify It Worked:

**Option A: Use Diagnostic Tool**

```
http://localhost/wiet_lib/admin/footfall-diagnostic.html
```

Click "Run All Tests" - All should show **PASS** ✅

**Option B: Check Database Status**

```
http://localhost/wiet_lib/admin/check-database.php
```

Should show all columns exist with green checkmarks ✅

**Option C: Test Admin Dashboard**

1. Login to admin panel
2. Click "Footfall Analytics" in sidebar
3. Should load dashboard with charts and data

---

## ⚠️ Common Issues & Fixes

### Issue 1: "Table 'Footfall' doesn't exist"

**Fix:** Run main database setup first:

```sql
-- Run this file first:
database/schema.sql
```

### Issue 2: "Duplicate column name 'EntryTime'"

**Meaning:** Migration already ran!
**Fix:** Nothing needed - columns already exist

### Issue 3: "Unknown column 'EntryTime' in field list"

**Meaning:** Migration not run yet
**Fix:** Run migration using one of the 3 options above

### Issue 4: Migration fails with error

**Fix:** Use manual method (Option 3 via phpMyAdmin)

- Errors are shown clearly in phpMyAdmin
- Can run statements one by one if needed

---

## 📊 Expected Results After Migration

### Database Structure:

```
Footfall Table:
├── FootfallID (INT) - Primary Key
├── MemberID (INT)
├── MemberNo (VARCHAR)
├── Date (DATE) - Old field
├── TimeIn (TIME) - Old field
├── TimeOut (TIME) - Old field
├── EntryTime (DATETIME) ← NEW
├── ExitTime (DATETIME) ← NEW
├── Purpose (VARCHAR) ← NEW
├── Status (VARCHAR) ← NEW
├── EntryMethod (VARCHAR) ← NEW
└── WorkstationUsed (VARCHAR) ← NEW

Indexes:
├── idx_entry_time
├── idx_status
└── idx_entry_method

Views:
├── FootfallDailyStats
├── FootfallHourlyStats
└── MemberFootfallSummary
```

### Sample Data After Migration:

```
FootfallID: 1
MemberNo: M0001234
EntryTime: 2024-10-29 09:15:00 ✓
ExitTime: 2024-10-29 11:30:00 ✓
Purpose: Study ✓
Status: Completed ✓
EntryMethod: QR Scan ✓
WorkstationUsed: WS-12 ✓
```

---

## 🎯 Quick Start (TL;DR)

**Fastest way to fix:**

1. Open: `http://localhost/wiet_lib/admin/run-migration-page.php`
2. Click: **"Run Migration Now"** button
3. Wait for success message
4. Open: `http://localhost/wiet_lib/admin/footfall-diagnostic.html`
5. Click: **"Run All Tests"**
6. Verify: All tests show **PASS** ✅

**Done!** 🎉

---

## 📱 Tool URLs (Bookmark These)

| Tool                 | URL                              | Purpose                      |
| -------------------- | -------------------------------- | ---------------------------- |
| **Migration Runner** | `admin/run-migration-page.php`   | Run migration automatically  |
| **Database Check**   | `admin/check-database.php`       | See detailed table structure |
| **Diagnostic Tool**  | `admin/footfall-diagnostic.html` | Test all components          |
| **Diagnostic API**   | `admin/diagnostic-api.php`       | API for automated tests      |
| **phpMyAdmin**       | `http://localhost/phpmyadmin`    | Manual database access       |

---

## 🔄 Migration Status Check

Run this SQL in phpMyAdmin to check if migration ran:

```sql
-- Check if new columns exist
SELECT
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'Footfall'
AND TABLE_SCHEMA = 'wiet_library'
AND COLUMN_NAME IN ('EntryTime', 'ExitTime', 'Purpose', 'Status', 'EntryMethod', 'WorkstationUsed');
```

**Expected result:** 6 rows (one for each column)
**If 0 rows:** Migration not run yet - use one of the 3 options above

---

## ✅ Success Criteria

After migration, you should have:

- [x] 6 new columns in Footfall table
- [x] Existing records updated with EntryTime/ExitTime
- [x] 3 performance indexes created
- [x] 3 SQL Views created
- [x] All diagnostic tests passing
- [x] Admin dashboard loads with charts
- [x] Scanner can check-in/out members
- [x] Student portal shows visit history

---

## 🆘 Need Help?

**If you get stuck:**

1. **Check detailed status:**

   - Open `admin/check-database.php`
   - Screenshot the output
   - Shows exactly what's missing

2. **Check browser console:**

   - Press F12 in browser
   - Look for JavaScript errors
   - Check Network tab for failed API calls

3. **Check PHP errors:**

   - Open `xampp/apache/logs/error.log`
   - Look for recent errors
   - Shows database connection issues

4. **Use manual method:**
   - phpMyAdmin is most reliable
   - Shows exact error messages
   - Can run SQL line by line

---

**Created:** 2024-10-29
**Status:** Database migration required before system is functional
**Priority:** HIGH - Must run migration first
**Estimated Time:** 2-3 minutes

---

## 🎬 Next Steps

**After migration completes successfully:**

1. ✅ Test admin navigation → Click "Footfall Analytics"
2. ✅ Test scanner → Open `footfall/scanner.php`
3. ✅ Test check-in → Scan QR code or enter member number
4. ✅ Test dashboard → View charts and statistics
5. ✅ Test student portal → Check visit history

**Everything will work once migration runs!** 🚀
