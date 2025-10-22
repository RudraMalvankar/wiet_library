# 🗄️ Database Setup Guide - Step by Step

## ✅ Prerequisites Check

Before starting, ensure:
- [ ] XAMPP is installed
- [ ] Apache is running (green in XAMPP Control Panel)
- [ ] MySQL is running (green in XAMPP Control Panel)

---

## 📊 Method 1: Using phpMyAdmin (EASIEST - 2 Minutes)

### Step 1: Start XAMPP Services

1. Open **XAMPP Control Panel**
2. Click **Start** next to **Apache** (should turn green)
3. Click **Start** next to **MySQL** (should turn green)

![XAMPP](https://i.imgur.com/xampp.png)

### Step 2: Open phpMyAdmin

1. Open your browser
2. Go to: **http://localhost/phpmyadmin**
3. You should see the phpMyAdmin interface

### Step 3: Create Database

1. Click **"New"** in the left sidebar (or **"Databases"** tab at top)
2. In the **"Create database"** field, type: `wiet_library`
3. Select Collation: `utf8mb4_unicode_ci`
4. Click **"Create"**

✅ You should see "Database wiet_library has been created"

### Step 4: Import Schema

1. Click on **`wiet_library`** database in the left sidebar (it should be highlighted)
2. Click the **"Import"** tab at the top
3. Click **"Choose File"** button
4. Navigate to: `C:\xampp\htdocs\wiet_lib\database\schema.sql`
5. Select the file and click **Open**
6. Scroll down and click **"Go"** button at the bottom

### Step 5: Wait for Success

You should see:
```
✅ Import has been successfully finished
✅ 15 queries executed
✅ Database schema created successfully!
```

### Step 6: Verify Tables

1. Click on **`wiet_library`** in the left sidebar
2. You should see **15 tables**:
   - Admin
   - Books
   - Holding
   - Member
   - Student
   - Faculty
   - Circulation
   - Return
   - Footfall
   - Recommendations
   - LibraryEvents
   - Notifications
   - ActivityLog
   - FinePayments
   - BookRequests

### Step 7: Check Sample Data

1. Click on **`Admin`** table
2. Click **"Browse"** tab
3. You should see **2 admin accounts**

Click on **`Books`** table → You should see **3 sample books**
Click on **`Member`** table → You should see **3 members**

---

## 📊 Method 2: Using MySQL Command Line

### Step 1: Open MySQL Command Line

**Option A: From XAMPP**
1. Open XAMPP Control Panel
2. Click **"Shell"** button
3. Type: `mysql -u root -p`
4. Press Enter (password is blank, just press Enter again)

**Option B: From Windows Command Prompt**
1. Press `Win + R`
2. Type: `cmd` and press Enter
3. Type: `cd C:\xampp\mysql\bin`
4. Type: `mysql -u root -p`
5. Press Enter (password is blank, just press Enter again)

### Step 2: Create Database

```sql
CREATE DATABASE wiet_library CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Press Enter. You should see:
```
Query OK, 1 row affected
```

### Step 3: Use Database

```sql
USE wiet_library;
```

Press Enter. You should see:
```
Database changed
```

### Step 4: Import Schema File

```sql
SOURCE C:/xampp/htdocs/wiet_lib/database/schema.sql;
```

**Note:** Use forward slashes `/` not backslashes `\`

Press Enter. You'll see multiple "Query OK" messages.

### Step 5: Verify

```sql
SHOW TABLES;
```

You should see 15 tables listed.

```sql
SELECT COUNT(*) FROM Admin;
```

Should return: 2

```sql
SELECT COUNT(*) FROM Books;
```

Should return: 3

### Step 6: Exit

```sql
EXIT;
```

---

## 📊 Method 3: Import Sample Data

After creating the database and importing schema, import sample data:

1. Open browser
2. Go to: **http://localhost/wiet_lib/database/import_data.php**
3. You should see:

```
✅ Books Import Complete! Imported 3 books and 4 holdings.
✅ Members Import Complete! Imported 3 members (2 students, 1 faculty).
```

---

## ✅ Verify Everything is Working

### Test 1: Check Database Connection

1. Go to: **http://localhost/wiet_lib/admin/dashboard.php**
2. You should see the dashboard (not a database error)

### Test 2: Check Members Page

1. Go to: **http://localhost/wiet_lib/admin/members.php**
2. You should see a list of members loading from database
3. Should show: Jayesh Mahesh Adurkar, Rahul Sharma, Dr. Priya Mehta

### Test 3: Login to Admin Panel

1. Go to: **http://localhost/wiet_lib/admin/admin_login.php**
2. Email: `admin@wiet.edu.in`
3. Password: `admin123`
4. Click Login

If successful, you're all set! 🎉

---

## ❌ Troubleshooting

### Problem: "Can't connect to MySQL server"

**Fix:**
1. Open XAMPP Control Panel
2. Stop MySQL (if running)
3. Click **"Config"** next to MySQL → select **"my.ini"**
4. Find line: `port=3306`
5. Make sure it's not commented (no # at start)
6. Save and restart MySQL

### Problem: "Access denied for user 'root'@'localhost'"

**Fix:**
1. Open phpMyAdmin
2. Click **"User accounts"** tab
3. Find user **"root"** with host **"localhost"**
4. Click **"Edit privileges"**
5. Click **"Change password"**
6. Select **"No password"**
7. Click **"Go"**
8. Update `includes/db_connect.php` if needed

### Problem: "Table already exists"

**Fix:**
1. Open phpMyAdmin
2. Select `wiet_library` database
3. Click **"Drop"** tab
4. Confirm deletion
5. Re-import schema.sql

### Problem: "Import file too large"

**Fix:**
1. Open phpMyAdmin
2. The schema.sql file is small (~20KB), should work fine
3. If still issues, use MySQL Command Line method instead

### Problem: "Database wiet_library not found"

**Fix:**
1. Make sure you created the database first
2. In phpMyAdmin: Databases → Create database → `wiet_library`
3. Then import schema.sql

---

## 🎯 What Gets Created

### Tables (15):
✅ Admin - 2 records (admin accounts)
✅ Books - 3 records (sample books)
✅ Holding - 4 records (book copies)
✅ Member - 3 records (members)
✅ Student - 2 records (student details)
✅ Faculty - 1 record (faculty details)
✅ Circulation - 0 records (ready for use)
✅ Return - 0 records (ready for use)
✅ Footfall - 0 records (ready for use)
✅ Recommendations - 0 records (ready for use)
✅ LibraryEvents - 0 records (ready for use)
✅ Notifications - 0 records (ready for use)
✅ ActivityLog - 0 records (ready for use)
✅ FinePayments - 0 records (ready for use)
✅ BookRequests - 0 records (ready for use)

### Views (3):
✅ v_available_books
✅ v_active_circulations
✅ v_member_summary

### Stored Procedures (1):
✅ sp_check_overdue_books

---

## 📞 Still Having Issues?

Check these files for errors:
- MySQL Error Log: `C:\xampp\mysql\data\*.err`
- Apache Error Log: `C:\xampp\apache\logs\error.log`

Or use the MySQL Command Line method which shows detailed error messages.

---

## 🎉 Success!

Once you see "Database schema created successfully!" you're done!

Next steps:
1. Visit: http://localhost/wiet_lib/admin/admin_login.php
2. Login with: admin@wiet.edu.in / admin123
3. Explore the system!

Your library management system is now LIVE! 🚀
