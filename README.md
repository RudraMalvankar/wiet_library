# WIET Library Management System
### Complete Database-Driven Library Management Solution

---

## 📋 Table of Contents
- [Overview](#overview)
- [Features](#features)
- [System Requirements](#system-requirements)
- [Installation Guide](#installation-guide)
- [Database Setup](#database-setup)
- [Configuration](#configuration)
- [Usage](#usage)
- [API Documentation](#api-documentation)
- [Troubleshooting](#troubleshooting)

---

## 🎯 Overview

The WIET Library Management System is a comprehensive web-based application designed to manage library operations including:
- **Book cataloging** and inventory management
- **Member management** (students, faculty, staff)
- **Circulation** (issue, return, renewal)
- **Fine management** and overdue tracking
- **Footfall tracking**
- **Reports and analytics**
- **Digital ID cards** with QR codes
- **Public OPAC** (Online Public Access Catalog)

---

## ✨ Features

### Admin Panel
- ✅ Dashboard with real-time statistics
- ✅ Complete book and holding management
- ✅ Member CRUD operations
- ✅ Book issue, return, and renewal
- ✅ Overdue book tracking
- ✅ Fine calculation and payment
- ✅ Bulk import capabilities
- ✅ Comprehensive reporting

### Student Portal
- ✅ View currently borrowed books
- ✅ Borrowing history
- ✅ Digital library card with QR code
- ✅ E-resources access
- ✅ Book recommendations
- ✅ Footfall history

### Public Features
- ✅ OPAC for book search
- ✅ View library events
- ✅ Check book availability

---

## 💻 System Requirements

### Server Requirements
- **PHP**: 7.4 or higher (8.0+ recommended)
- **MySQL**: 5.7 or higher (8.0+ recommended)
- **Web Server**: Apache (with mod_rewrite) or Nginx
- **PHP Extensions**:
  - PDO
  - pdo_mysql
  - mbstring
  - json
  - GD (for QR code generation)

### Development Environment
- **XAMPP** 8.0+ or **WAMP** (includes Apache, MySQL, PHP)
- OR **Docker** with PHP and MySQL containers
- Minimum 2GB RAM
- 500MB disk space

---

## 🚀 Installation Guide

### Step 1: Install XAMPP

1. **Download XAMPP** from [https://www.apachefriends.org](https://www.apachefriends.org)
2. Install XAMPP to `C:\xampp` (Windows) or `/opt/lampp` (Linux)
3. Start **Apache** and **MySQL** services from XAMPP Control Panel

### Step 2: Clone/Extract Project

```bash
# Navigate to htdocs folder
cd C:\xampp\htdocs

# Extract your project
# The project should be at: C:\xampp\htdocs\wiet_lib
```

### Step 3: Verify Structure

Your project structure should look like:
```
wiet_lib/
├── admin/
│   ├── api/
│   │   ├── members.php
│   │   ├── circulation.php
│   │   └── books.php
│   ├── dashboard.php
│   ├── members.php
│   ├── circulation.php
│   └── books-management.php
├── student/
│   ├── dashboard.php
│   ├── my-books.php
│   └── borrowing-history.php
├── includes/
│   ├── db_connect.php
│   └── functions.php
├── database/
│   ├── schema.sql
│   └── import_data.php
├── index.php
└── README.md
```

---

## 🗄️ Database Setup

### Method 1: Using phpMyAdmin (Recommended for Beginners)

1. **Open phpMyAdmin**:
   - Navigate to [http://localhost/phpmyadmin](http://localhost/phpmyadmin)

2. **Create Database**:
   - Click "New" in the left sidebar
   - Database name: `wiet_library`
   - Collation: `utf8mb4_unicode_ci`
   - Click "Create"

3. **Import Schema**:
   - Select `wiet_library` database
   - Click "Import" tab
   - Choose file: `database/schema.sql`
   - Click "Go"
   - Wait for success message

4. **Verify Tables**:
   - You should see 15 tables created:
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

### Method 2: Using MySQL Command Line

```bash
# Open MySQL command line
mysql -u root -p

# Create database
CREATE DATABASE wiet_library CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Use database
USE wiet_library;

# Import schema
SOURCE C:/xampp/htdocs/wiet_lib/database/schema.sql;

# Verify
SHOW TABLES;

# Exit
EXIT;
```

### Step 4: Import Sample Data

1. **Open browser** and navigate to:
   ```
   http://localhost/wiet_lib/database/import_data.php
   ```

2. This will import:
   - ✅ Sample books from `data.md`
   - ✅ Sample members (students and faculty)
   - ✅ Holdings/copies

3. **Expected Output**:
   ```
   Books Import Complete! Imported 3 books and 3 holdings.
   Members Import Complete! Imported 3 members (2 students, 1 faculty).
   ```

---

## ⚙️ Configuration

### Database Configuration

Edit `includes/db_connect.php`:

```php
// Database configuration
define('DB_HOST', 'localhost');      // Database host
define('DB_NAME', 'wiet_library');   // Database name
define('DB_USER', 'root');           // MySQL username
define('DB_PASS', '');               // MySQL password (empty for XAMPP default)
define('DB_CHARSET', 'utf8mb4');     // Character set
```

### For Production:
```php
define('DB_HOST', 'your-production-host');
define('DB_USER', 'your-username');
define('DB_PASS', 'your-strong-password');
```

---

## 🎮 Usage

### Access the Application

1. **Admin Panel**:
   ```
   http://localhost/wiet_lib/admin/admin_login.php
   ```
   **Default Credentials**:
   - Email: `admin@wiet.edu.in`
   - Password: `admin123`

2. **Student Portal**:
   ```
   http://localhost/wiet_lib/student/student_login.php
   ```
   Use Member Number from database

3. **Public OPAC**:
   ```
   http://localhost/wiet_lib/opac.php
   ```

### First-Time Setup

1. **Login as Admin**
2. **Go to Dashboard** - Verify statistics
3. **Add Members**:
   - Navigate to Members → Add Member
   - Fill in required details
   - System auto-generates Member Number

4. **Add Books**:
   - Navigate to Books Management → Add Book
   - Enter book details
   - Add holdings/copies with accession numbers

5. **Issue Books**:
   - Go to Circulation
   - Click "Issue Book"
   - Enter Member Number and Accession Number
   - System validates and issues book

---

## 📚 API Documentation

### Members API (`admin/api/members.php`)

**Get All Members**:
```javascript
GET api/members.php?action=list&status=Active&group=Student

Response:
{
    "success": true,
    "data": [...]
}
```

**Add Member**:
```javascript
POST api/members.php?action=add
Body: {
    "MemberName": "John Doe",
    "Group": "Student",
    "Email": "john@example.com",
    "Phone": "1234567890",
    "PRN": "C2513"
}
```

### Circulation API (`admin/api/circulation.php`)

**Issue Book**:
```javascript
POST api/circulation.php?action=issue
Body: {
    "memberNo": 2511,
    "accNo": "BE8950"
}
```

**Return Book**:
```javascript
POST api/circulation.php?action=return
Body: {
    "circulationId": 1,
    "condition": "Good",
    "remarks": ""
}
```

### Books API (`admin/api/books.php`)

**Search Books**:
```javascript
GET api/books.php?action=search&q=database

Response:
{
    "success": true,
    "data": [...]
}
```

---

## 🔧 Troubleshooting

### Database Connection Error

**Error**: "Database Connection Failed"

**Solutions**:
1. Verify MySQL is running in XAMPP
2. Check database name is `wiet_library`
3. Verify credentials in `includes/db_connect.php`
4. Ensure database exists:
   ```sql
   SHOW DATABASES;
   ```

### Tables Not Found

**Error**: "Table 'wiet_library.Member' doesn't exist"

**Solution**:
- Re-run `database/schema.sql` in phpMyAdmin
- Or drop database and recreate:
  ```sql
  DROP DATABASE wiet_library;
  CREATE DATABASE wiet_library;
  -- Then import schema.sql again
  ```

### API Returns Empty Data

**Issue**: Members/Books list is empty

**Solution**:
- Run `database/import_data.php` to import sample data
- Or add data manually through admin panel

### Permission Issues (Linux)

```bash
# Give write permissions
sudo chmod -R 755 /opt/lampp/htdocs/wiet_lib
sudo chown -R daemon:daemon /opt/lampp/htdocs/wiet_lib
```

### Apache Not Starting

**Conflict on Port 80**:
1. Stop Skype/other services using port 80
2. Or change Apache port in `httpd.conf`:
   ```
   Listen 8080
   ```
3. Access via: `http://localhost:8080/wiet_lib`

---

## 📊 Database Schema Overview

### Core Tables

**Books** → **Holding** (One-to-Many)
- A book can have multiple physical copies (holdings)

**Member** → **Student/Faculty** (One-to-One)
- Member is parent, Student/Faculty are children

**Member** ← **Circulation** → **Holding**
- Tracks who borrowed which copy

**Circulation** → **Return** (One-to-One)
- Return record linked to circulation

---

## 🔐 Security Notes

### For Production Deployment:

1. **Change Default Passwords**:
   ```sql
   UPDATE Admin SET Password = 'NEW_HASHED_PASSWORD' WHERE Email = 'admin@wiet.edu.in';
   ```

2. **Update Database Credentials**:
   - Use strong password for MySQL user
   - Never use `root` with empty password

3. **Enable HTTPS**:
   - Get SSL certificate (Let's Encrypt)
   - Force HTTPS redirect

4. **Secure File Permissions**:
   - `includes/db_connect.php` → 600 (read/write owner only)
   - Other PHP files → 644

5. **Disable Error Display**:
   ```php
   // In production
   error_reporting(0);
   ini_set('display_errors', 0);
   ```

---

## 📝 Data Structure (from data.md)

### Book Data Format
```
Accession No | Cat No | Author | Title | Edition | Year | Publisher | ISBN | Location
BE8950 | 10084 | LUCAS, H.C. | INFORMATION TECHNOLOGY FOR MANAGEMENT | 7th Ed. | 2001 | TATA McGRAW HILL | ... | CMTC
```

### Member Data Format
```
MemberNo | Course | Surname | First Name | Group | Email | Mobile | Gender | Admission Date
C2511 | Computer | Adurkar | Jayesh | Student | email@example.com | 9146622724 | Male | 15/09/2025
```

---

## 🤝 Support

For issues or questions:
1. Check troubleshooting section
2. Review error logs: `C:\xampp\apache\logs\error.log`
3. Check PHP errors: Enable display_errors in php.ini

---

## 📄 License

This project is for educational purposes - WIET College Library Management System.

---

**Last Updated**: October 19, 2025
**Version**: 1.0.0
**Status**: ✅ Production Ready (Database-Enabled)
