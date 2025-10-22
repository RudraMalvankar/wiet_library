# 🚀 QUICK START GUIDE - WIET Library Management System

## ⚡ 5-Minute Setup

### Step 1: Start XAMPP (1 minute)
1. Open **XAMPP Control Panel**
2. Click **Start** for:
   - ✅ Apache
   - ✅ MySQL

### Step 2: Create Database (2 minutes)
1. Open browser: **http://localhost/phpmyadmin**
2. Click **"New"** → Create database: `wiet_library`
3. Click on `wiet_library` database
4. Go to **"Import"** tab
5. Choose file: `database/schema.sql`
6. Click **"Go"** and wait

✅ You should see 15 tables created!

### Step 3: Import Sample Data (1 minute)
1. Open browser: **http://localhost/wiet_lib/database/import_data.php**
2. Wait for import to complete
3. You should see:
   - ✅ Books imported: 3
   - ✅ Members imported: 3

### Step 4: Login (1 minute)
1. Go to: **http://localhost/wiet_lib/admin/admin_login.php**
2. Login with:
   - **Email**: `admin@wiet.edu.in`
   - **Password**: `admin123`

🎉 **Done!** You're now in the dashboard with live data!

---

## 📊 What You Get

### Database Tables Created:
- ✅ **Admin** - Admin users (2 default admins)
- ✅ **Books** - Book catalog (3 sample books)
- ✅ **Holding** - Physical copies (3 copies)
- ✅ **Member** - Library members (3 members)
- ✅ **Student** - Student details (2 students)
- ✅ **Faculty** - Faculty details (1 faculty)
- ✅ **Circulation** - Book issue/return tracking
- ✅ **Return** - Return records
- ✅ **Footfall** - Library entry tracking
- ✅ **Notifications** - System notifications
- ✅ **LibraryEvents** - Events and announcements
- ✅ **ActivityLog** - Audit trail
- ✅ **Recommendations** - Book recommendations
- ✅ **FinePayments** - Fine tracking
- ✅ **BookRequests** - Member requests

---

## 🔑 Default Login Credentials

### Admin Panel:
```
URL: http://localhost/wiet_lib/admin/admin_login.php
Email: admin@wiet.edu.in
Password: admin123
```

### Super Admin:
```
Email: admin@wiet.edu.in
Password: admin123
Role: Super Admin
```

### Student Login:
```
URL: http://localhost/wiet_lib/student/student_login.php
Member No: 2511 (Jayesh Adurkar)
Member No: 2512 (Rahul Sharma)
```

---

## 🧪 Test the System

### 1. View Members
- Go to **Members** from sidebar
- You should see 3 members loaded from database

### 2. Issue a Book
- Go to **Circulation**
- Click **"Issue Book"**
- Member Number: `2511`
- Accession Number: `BE8950`
- Click **Submit**

### 3. View Dashboard Stats
- Go to **Dashboard**
- All statistics are now LIVE from database!

---

## ❌ Troubleshooting

### Problem: "Database Connection Failed"
**Fix:**
```
1. Check if MySQL is running in XAMPP
2. Verify database name is: wiet_library
3. Check includes/db_connect.php has correct credentials
```

### Problem: "Table doesn't exist"
**Fix:**
```
Re-import database/schema.sql in phpMyAdmin
```

### Problem: "No members showing"
**Fix:**
```
Run: http://localhost/wiet_lib/database/import_data.php
```

### Problem: "Port 80 already in use"
**Fix:**
```
1. Stop Skype or other services using port 80
2. Or change Apache port to 8080 in XAMPP
```

---

## 📝 What Changed from Static to Live?

### Before (Static):
```php
// Hardcoded array
$sampleMembers = [
    ['MemberNo' => 2024001, 'Name' => 'Rahul']
];
```

### After (Live):
```php
// Database query
$stmt = $pdo->query("SELECT * FROM Member");
$members = $stmt->fetchAll();
```

### All Pages Updated:
- ✅ **admin/members.php** - Now fetches from Member table
- ✅ **admin/dashboard.php** - Real-time statistics
- ✅ **API endpoints** - Complete CRUD operations
- ✅ **Circulation** - Database-backed issue/return
- ✅ **Books Management** - Real book catalog

---

## 🎯 Next Steps

### 1. Add More Data
- Add more books via **Books Management**
- Add more members via **Members**
- Issue some books to test circulation

### 2. Explore Features
- ✅ Search members/books
- ✅ Issue and return books
- ✅ View overdue books
- ✅ Generate reports

### 3. Customize
- Update college logo
- Change color scheme
- Add custom fields

### 4. Deploy to Production
- See full **README.md** for production deployment
- Secure database credentials
- Enable HTTPS
- Change default passwords

---

## 📞 Need Help?

Check these files:
- **README.md** - Complete documentation
- **database/schema.sql** - Database structure
- **includes/functions.php** - Available functions
- **C:\xampp\apache\logs\error.log** - Error logs

---

**Last Updated**: October 19, 2025  
**Status**: ✅ **FULLY FUNCTIONAL - DATABASE ENABLED**

🎉 **Your library management system is now LIVE!**
