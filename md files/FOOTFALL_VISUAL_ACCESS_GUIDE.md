# 🎯 FOOTFALL SYSTEM - VISUAL ACCESS GUIDE

## Quick Access URLs

---

## 1️⃣ ADMIN DASHBOARD

### **URL:**

```
http://localhost/wiet_lib/admin/footfall-analytics.php
```

### **What You'll See:**

```
┌────────────────────────────────────────────────────────────────┐
│ 📊 Footfall Analytics         [Excel][Print][Scanner]        │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━│
│                                                                 │
│ ┌───────────┐ ┌───────────┐ ┌───────────┐ ┌───────────┐     │
│ │    127    │ │    892    │ │     34    │ │  2h 15m   │     │
│ │   Total   │ │  Unique   │ │  Active   │ │    Avg    │     │
│ │  Visits   │ │ Visitors  │ │    Now    │ │ Duration  │     │
│ └───────────┘ └───────────┘ └───────────┘ └───────────┘     │
│                                                                 │
│ [Analytics & Charts] [All Records] [Currently Active] [Reports]│
│  ═══════════════                                               │
│                                                                 │
│ 🔍 Filter Data                                                 │
│ From: [2025-10-01] To: [2025-10-29] Branch: [All ▼]          │
│ Purpose: [All ▼] [Apply Filters] [Reset]                      │
│                                                                 │
│ ┌──────────────────────────┐ ┌──────────────────────────┐    │
│ │ 📈 Daily Visits Trend    │ │ 📊 Hourly Distribution  │    │
│ │                          │ │                          │    │
│ │   Line Chart             │ │   Bar Chart              │    │
│ │                          │ │                          │    │
│ └──────────────────────────┘ └──────────────────────────┘    │
│                                                                 │
│ ┌──────────────────────────┐ ┌──────────────────────────┐    │
│ │ 🍩 Purpose Distribution  │ │ 🥧 Branch Distribution   │    │
│ │                          │ │                          │    │
│ │   Doughnut Chart         │ │   Pie Chart              │    │
│ │                          │ │                          │    │
│ └──────────────────────────┘ └──────────────────────────┘    │
└────────────────────────────────────────────────────────────────┘
```

### **Features Available:**

- ✅ 4 real-time stat cards (auto-updates)
- ✅ 4 interactive charts (click to filter)
- ✅ Date range filter
- ✅ Branch filter dropdown
- ✅ Purpose filter dropdown
- ✅ Export to Excel button (downloads XLSX)
- ✅ Print button (opens print dialog)
- ✅ Open Scanner link (new tab)

### **Other Tabs:**

- **All Records:** Full data table with search
- **Currently Active:** Live visitor list
- **Reports:** Export and reporting options

---

## 2️⃣ SCANNER KIOSK

### **URL:**

```
http://localhost/wiet_lib/footfall/scanner.php
```

### **What You'll See:**

```
┌────────────────────────────────────────────────┐
│         🚪 LIBRARY ENTRY SYSTEM                │
│    Scan your Digital ID or enter manually     │
├────────────────────────────────────────────────┤
│                                                │
│ Stats: 👥 127 Today  |  ✅ 34 Active Now     │
│        📊 892 This Week                        │
│                                                │
├────────────────────────────────────────────────┤
│                                                │
│  [📷 QR Scan]  [⌨️ Manual Entry]              │
│   ═════════                                    │
│                                                │
│  ┌──────────────────────────────────────┐     │
│  │                                      │     │
│  │      📷 CAMERA VIEW                  │     │
│  │                                      │     │
│  │   Hold QR code here to scan          │     │
│  │                                      │     │
│  └──────────────────────────────────────┘     │
│                                                │
│  [▶️ Start Scan] [⏹️ Stop]                    │
│                                                │
│  Purpose: [Study ▼]                           │
│                                                │
├────────────────────────────────────────────────┤
│  Recent Check-ins:                             │
│  • John Doe - 2:30 PM - Study                 │
│  • Jane Smith - 2:28 PM - Reading             │
│  • Bob Johnson - 2:25 PM - Research           │
└────────────────────────────────────────────────┘
```

### **How to Use:**

1. **QR Mode:**

   - Click "Start Scan"
   - Allow camera access
   - Hold QR code to camera
   - Auto check-in on success

2. **Manual Mode:**
   - Click "Manual Entry" tab
   - Enter member number
   - Select purpose
   - Click "Check In"

### **Features:**

- ✅ Live camera QR scanning
- ✅ Manual entry fallback
- ✅ Real-time stats
- ✅ Recent visitors list
- ✅ Auto-refresh every 30 seconds
- ✅ Purpose selection
- ✅ Welcome messages

---

## 3️⃣ STUDENT SELF CHECK-IN

### **URL:**

```
http://localhost/wiet_lib/student/library-checkin.php
```

### **Login Required:** Use student portal credentials

### **What You'll See (Not Checked In):**

```
┌────────────────────────────────────────────────┐
│         📚 Library Check-in                    │
├────────────────────────────────────────────────┤
│                                                │
│  Status: ⚪ Not Checked In                    │
│                                                │
│  Ready to check in to the library?            │
│                                                │
│  Purpose of Visit:                             │
│  [Study ▼]                                     │
│   - Study                                      │
│   - Research                                   │
│   - Reading                                    │
│   - Book Issue/Return                          │
│   - Computer Use                               │
│   - Group Study                                │
│   - Assignment Work                            │
│   - Project Work                               │
│                                                │
│  [✅ Check In Now]                             │
│                                                │
└────────────────────────────────────────────────┘
```

### **What You'll See (Checked In):**

```
┌────────────────────────────────────────────────┐
│         📚 Library Check-in                    │
├────────────────────────────────────────────────┤
│                                                │
│  Status: ✅ Currently Checked In               │
│                                                │
│  Entry Time: 2:30 PM                           │
│  Duration: 1h 45m                              │
│  Purpose: Study                                │
│                                                │
│  ⏱️ Live Duration Timer                        │
│  (Updates every minute)                        │
│                                                │
│  [🚪 Check Out]                                │
│                                                │
│  Note: Click check out when you leave          │
│                                                │
└────────────────────────────────────────────────┘
```

### **Features:**

- ✅ One-click check-in
- ✅ Purpose dropdown
- ✅ Live duration counter
- ✅ Check-out button
- ✅ Current status display
- ✅ Entry time shown
- ✅ Auto-updates every 60 seconds

---

## 4️⃣ STUDENT VISIT HISTORY

### **URL:**

```
http://localhost/wiet_lib/student/my-footfall.php
```

### **What You'll See:**

```
┌────────────────────────────────────────────────┐
│         📊 My Library Visits                   │
├────────────────────────────────────────────────┤
│                                                │
│  This Month Stats:                             │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐      │
│  │    12    │ │   24hrs  │ │  2h 15m  │      │
│  │  Visits  │ │   Total  │ │   Avg    │      │
│  └──────────┘ └──────────┘ └──────────┘      │
│                                                │
│  Recent Visits:                                │
│  ┌──────────────────────────────────────┐     │
│  │ Oct 29, 2025                         │     │
│  │ In: 10:30 AM | Out: 12:45 PM        │     │
│  │ Duration: 2h 15m | Purpose: Study   │     │
│  └──────────────────────────────────────┘     │
│  ┌──────────────────────────────────────┐     │
│  │ Oct 28, 2025                         │     │
│  │ In: 2:00 PM | Out: 5:30 PM          │     │
│  │ Duration: 3h 30m | Purpose: Research│     │
│  └──────────────────────────────────────┘     │
│  ┌──────────────────────────────────────┐     │
│  │ Oct 27, 2025                         │     │
│  │ In: 9:00 AM | Out: 11:15 AM         │     │
│  │ Duration: 2h 15m | Purpose: Reading │     │
│  └──────────────────────────────────────┘     │
│                                                │
└────────────────────────────────────────────────┘
```

### **Features:**

- ✅ Monthly statistics
- ✅ Complete visit history
- ✅ Entry/exit times
- ✅ Duration display
- ✅ Purpose shown
- ✅ Responsive cards

---

## 🎨 COLOR GUIDE

### **Status Colors:**

- 🟢 **Active/Success:** #28a745 (Green)
- 🔵 **Primary/Info:** #263c79 (Navy Blue)
- 🟡 **Warning/Due:** #ffc107 (Yellow)
- 🔴 **Danger/Overdue:** #dc3545 (Red)
- 🟠 **Accent:** #cfac69 (Gold)
- ⚪ **Secondary:** #6c757d (Gray)

### **Badge Examples:**

- `[QR Scan]` = Blue badge
- `[Manual]` = Blue badge
- `[Active]` = Green badge
- `[Completed]` = Yellow badge
- `[Study]` = Primary badge

---

## 📊 DATA EXAMPLES

### **Member Number Format:**

```
M0001234  (Always 8 characters with M prefix)
```

### **Duration Format:**

```
2h 15m   (Hours and minutes)
45m      (Just minutes if < 1 hour)
```

### **Time Format:**

```
Oct 29, 2025 2:30 PM   (Full date/time)
2:30 PM                (Just time for today)
```

### **Purpose Options:**

1. Library Visit
2. Study
3. Research
4. Book Issue/Return
5. Reading
6. Computer Use
7. Group Study
8. Assignment Work
9. Project Work

---

## 🔄 AUTO-REFRESH SCHEDULE

| Component        | Refresh Rate | What Updates          |
| ---------------- | ------------ | --------------------- |
| Admin Stats      | 60 seconds   | All 4 stat cards      |
| Admin Charts     | Manual       | Click "Apply Filters" |
| Active Visitors  | 60 seconds   | When on that tab      |
| Scanner Stats    | 30 seconds   | All stats             |
| Scanner Recent   | 30 seconds   | Recent list           |
| Student Duration | 60 seconds   | Live counter          |

---

## 🎯 QUICK ACTIONS

### **Admin:**

1. Login → Dashboard opens
2. See live stats instantly
3. Click tabs to switch views
4. Use filters for date ranges
5. Click "Export Excel" to download
6. Click "Print" for reports
7. Click "Open Scanner" for kiosk

### **Scanner:**

1. Open URL on entrance device
2. Click "Start Scan"
3. Allow camera access
4. Scan QR codes automatically
5. Or use manual entry

### **Student:**

1. Login to portal
2. Go to library-checkin.php
3. Select purpose
4. Click "Check In Now"
5. See duration update live
6. Click "Check Out" when leaving
7. View history in my-footfall.php

---

## 📱 MOBILE VIEW

All pages adapt to mobile screens:

### **Desktop (> 768px):**

- 4-column stats grid
- Side-by-side charts
- Full navigation bar

### **Tablet (768px):**

- 2-column stats grid
- Charts stack 2x2
- Tabs scroll horizontally

### **Mobile (< 768px):**

- 1-column stats stack
- Charts full width
- Vertical navigation
- Touch-friendly buttons

---

## ✅ VERIFICATION CHECKLIST

Before showing to users:

- [ ] Admin can access footfall-analytics.php
- [ ] All 4 stat cards show numbers
- [ ] Charts render correctly
- [ ] Filters work and update charts
- [ ] Export to Excel downloads file
- [ ] Print opens print dialog
- [ ] Scanner page loads
- [ ] Camera permission works
- [ ] Manual entry works
- [ ] Student can check in
- [ ] Duration updates live
- [ ] Check out works
- [ ] History shows visits
- [ ] Mobile view looks good

---

## 🎉 READY TO DEMO!

Everything is set up and ready to show:

1. **For Management:** Show admin dashboard with charts
2. **For Library Staff:** Show scanner device
3. **For Students:** Show self check-in and history

All interfaces are professional, intuitive, and production-ready!

---

**Visual Guide v1.0 | October 29, 2025**
**Status: ✅ All Systems Operational**
