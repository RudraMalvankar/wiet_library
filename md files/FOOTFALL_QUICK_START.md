# 🚀 FOOTFALL SYSTEM - QUICK START GUIDE

## ⚡ 5-MINUTE SETUP

### Step 1: Run Database Migration (1 min)

```bash
cd c:\xampp\htdocs\wiet_lib
C:\xampp\mysql\bin\mysql.exe -u root -p wiet_library < database\migrations\006_enhance_footfall_tracking.sql
```

**OR** via phpMyAdmin:

1. Open http://localhost/phpmyadmin
2. Select `wiet_library` database
3. Click "SQL" tab
4. Copy-paste contents of `database/migrations/006_enhance_footfall_tracking.sql`
5. Click "Go"

### Step 2: Test Scanner (1 min)

```
URL: http://localhost/wiet_lib/footfall/scanner.php
```

- Allow camera access
- Try manual entry first: Enter your member number
- Should see success message

### Step 3: Test Student Check-in (1 min)

```
1. Login to: http://localhost/wiet_lib/student/student_login.php
2. Navigate to: http://localhost/wiet_lib/student/library-checkin.php
3. Select purpose, click "Check In Now"
4. Should see success message
```

### Step 4: Test Admin Dashboard (1 min)

```
1. Login to: http://localhost/wiet_lib/admin/login.php
2. Navigate to: http://localhost/wiet_lib/admin/footfall-analytics.php
3. Should see charts and statistics
```

### Step 5: Verify Data (1 min)

```sql
-- Check if records were created
SELECT * FROM Footfall ORDER BY FootfallID DESC LIMIT 5;

-- Check views
SELECT * FROM FootfallDailyStats WHERE VisitDate = CURDATE();
```

---

## 🎯 WHAT YOU GET

### ✅ For Students:

- **QR Scanner Entry** - Scan digital ID to check in (3 seconds)
- **Self Check-in Portal** - Check in from any device
- **Visit History** - Track all library visits
- **Auto Check-out** - System tracks exit time

### ✅ For Admins:

- **Real-time Dashboard** - See live visitor count
- **Interactive Charts** - Daily, hourly, purpose, branch analytics
- **Export Reports** - Download Excel/CSV reports
- **Filter & Search** - Advanced filtering by date, branch, purpose
- **Print Reports** - Printer-friendly analytics

---

## 📱 ACCESS URLS

### Student Portal

```
Check-in:        http://localhost/wiet_lib/student/library-checkin.php
Visit History:   http://localhost/wiet_lib/student/my-footfall.php
Digital ID:      http://localhost/wiet_lib/student/digital-id.php
```

### Admin Portal

```
Analytics:       http://localhost/wiet_lib/admin/footfall-analytics.php
QR Scanner:      http://localhost/wiet_lib/footfall/scanner.php
```

### API Endpoints

```
Check-in:        http://localhost/wiet_lib/footfall/api/checkin.php
Check-out:       http://localhost/wiet_lib/footfall/api/checkout.php
Statistics:      http://localhost/wiet_lib/footfall/api/footfall-stats.php
Recent Visitors: http://localhost/wiet_lib/footfall/api/recent-visitors.php
Analytics Data:  http://localhost/wiet_lib/footfall/api/analytics-data.php
Export:          http://localhost/wiet_lib/footfall/api/export-footfall.php
```

---

## 🧪 TEST SCENARIOS

### Test 1: QR Code Check-in

1. Open scanner: `footfall/scanner.php`
2. Click "Manual Entry" tab
3. Enter a member number (e.g., 1234567)
4. Select purpose
5. Click "Check In"
6. ✅ Should see "Welcome, [Name]! Check-in successful"

### Test 2: Duplicate Prevention

1. Try checking in the same member again
2. ✅ Should see error: "Already checked in today at [time]"

### Test 3: Check-out

1. Use same member number
2. Open: `student/library-checkin.php` (after logging in as that student)
3. Click "Check Out" button
4. ✅ Should see "Goodbye, [Name]! Check-out successful. Duration: Xh Ym"

### Test 4: Admin Dashboard

1. Login to admin portal
2. Open `admin/footfall-analytics.php`
3. ✅ Should see today's visit count > 0
4. ✅ Charts should display data
5. ✅ Table should show recent entries

### Test 5: Export

1. In admin dashboard, click "Export Excel"
2. ✅ File should download: `Footfall_Report_YYYY-MM-DD_to_YYYY-MM-DD.xlsx`
3. Open in Excel
4. ✅ Should contain all footfall records

---

## 🐛 QUICK TROUBLESHOOTING

### Problem: Migration SQL fails with "View already exists"

**Fix:**

```sql
DROP VIEW IF EXISTS FootfallDailyStats;
DROP VIEW IF EXISTS FootfallHourlyStats;
DROP VIEW IF EXISTS MemberFootfallSummary;
-- Then run migration again
```

### Problem: Scanner says "Camera access denied"

**Fix:**

- Click lock icon in browser address bar
- Change Camera permission to "Allow"
- Reload page
- OR use "Manual Entry" tab instead

### Problem: Admin dashboard shows no data

**Fix:**

- Check if you've created any footfall entries (run Test 1)
- Check date range filter (should include today)
- Open browser console (F12) for errors

### Problem: API returns "Database error"

**Fix:**

1. Check MySQL is running: `net start mysql`
2. Test connection:

```bash
C:\xampp\mysql\bin\mysql.exe -u root -p wiet_library
```

3. Check `includes/db_connect.php` credentials

### Problem: "Member not found" error

**Fix:**

- Verify member exists:

```sql
SELECT MemberNo, MemberName FROM Member WHERE MemberNo = 1234567;
```

- If not found, member needs to be registered in system

---

## 📊 SAMPLE DATA (For Testing)

If you need sample footfall data:

```sql
-- Insert 10 sample entries for today
INSERT INTO Footfall (MemberNo, Date, TimeIn, TimeOut, Duration, EntryTime, ExitTime, Purpose, Status, EntryMethod)
SELECT
    m.MemberNo,
    CURDATE(),
    TIME(NOW() - INTERVAL FLOOR(RAND() * 5) HOUR),
    TIME(NOW() - INTERVAL FLOOR(RAND() * 2) HOUR),
    FLOOR(RAND() * 180) + 30,
    NOW() - INTERVAL FLOOR(RAND() * 5) HOUR,
    NOW() - INTERVAL FLOOR(RAND() * 2) HOUR,
    ELT(FLOOR(RAND() * 5) + 1, 'Study', 'Research', 'Borrow Books', 'Reading Room', 'Digital Resources'),
    'Completed',
    ELT(FLOOR(RAND() * 3) + 1, 'QR Scan', 'Manual', 'Student Portal')
FROM Member m
LIMIT 10;
```

---

## 🎨 CUSTOMIZATION

### Change Scanner Logo

Edit `footfall/scanner.php` line 14:

```html
<h1><i class="fas fa-door-open"></i> Library Entry System</h1>
```

### Change Color Scheme

Edit CSS variables in scanner/admin files:

```css
/* Primary color (blue) */
--primary: #263c79;

/* Accent color (gold) */
--accent: #cfac69;

/* Success color (green) */
--success: #10b981;
```

### Add More Purpose Options

Edit dropdown in `footfall/scanner.php` and `student/library-checkin.php`:

```html
<option value="Exam Preparation">Exam Preparation</option>
<option value="Project Work">Project Work</option>
<option value="Printing">Printing</option>
```

### Change Auto-refresh Interval

Edit JavaScript in `admin/footfall-analytics.php`:

```javascript
// Current: 60 seconds
setInterval(() => {
  loadTable(1);
}, 60000);

// Change to 30 seconds
setInterval(() => {
  loadTable(1);
}, 30000);
```

---

## 📈 NEXT STEPS

1. ✅ **Complete setup** (5 minutes)
2. ✅ **Test all features** (10 minutes)
3. ✅ **Add to navigation menus** (5 minutes)
4. ✅ **Print student QR codes** (varies)
5. ✅ **Set up scanner kiosk** (30 minutes)
6. ✅ **Train library staff** (15 minutes)
7. ✅ **Announce to students** (via email/notice)
8. ✅ **Monitor for first week** (ongoing)

---

## 📞 NEED HELP?

Full documentation: `FOOTFALL_SYSTEM_DOCUMENTATION.md`

Common issues:

- Scanner not working → Use manual entry
- Database errors → Check MySQL service
- Charts not loading → Clear browser cache
- Export not working → Try CSV format

---

## ✅ SUCCESS CHECKLIST

After setup, verify:

- [ ] Can check in via scanner (manual entry)
- [ ] Can check in via student portal
- [ ] Can check out
- [ ] Duplicate prevention works
- [ ] Admin dashboard shows data
- [ ] Charts render correctly
- [ ] Export to Excel works
- [ ] Print functionality works
- [ ] Recent visitors refresh
- [ ] Statistics update in real-time

**If all checked:** 🎉 **System is ready for production!**

---

**Setup Time:** 5 minutes  
**Testing Time:** 10 minutes  
**Total:** 15 minutes to fully operational system

🚀 **Happy tracking!**
