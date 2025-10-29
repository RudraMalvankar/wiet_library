# 🎓 WIET Library Management System - Complete Project Documentation

**Project Name:** WIET Library Management System  
**Institution:** Watumull Institute of Engineering & Technology  
**Version:** 2.0 (Production Ready)  
**Last Updated:** 29/10/2025  
**Status:** 🟢 85% Complete - Moving to 100% Production Ready  
**Developer:**Esha Gond, Aditi Godse, Rudra Malvankar, Aditya Jadhav  
**Project Type:** Full-Stack Library Management System  
**Repository:** github.com/RudraMalvankar/wiet_library

---

## 📑 Table of Contents

1. [Project Overview](#1-project-overview)
2. [System Architecture](#2-system-architecture)
3. [Database Design](#3-database-design)
4. [File Structure Explained](#4-file-structure-explained)
5. [User Roles & Permissions](#5-user-roles--permissions)
6. [Core Features](#6-core-features)
7. [API Endpoints](#7-api-endpoints)
8. [Current Implementation Status](#8-current-implementation-status)
9. [Student Portal Workflow](#9-student-portal-workflow)
10. [Admin Portal Workflow](#10-admin-portal-workflow)
11. [Authentication System](#11-authentication-system)
12. [Circulation System](#12-circulation-system)
13. [Reports & Analytics](#13-reports--analytics)
14. [QR Code & Scanning](#14-qr-code--scanning)
15. [How Data Flows](#15-how-data-flows)
16. [Installation & Setup](#16-installation--setup)
17. [Testing Guide](#17-testing-guide)
18. [Deployment Checklist](#18-deployment-checklist)
19. [Future Enhancements](#19-future-enhancements)
20. [Troubleshooting](#20-troubleshooting)

---

## 1. Project Overview

### 1.1 What is WIET Library Management System?

The **WIET Library Management System** is a comprehensive, database-driven web application designed for **Watumull Institute of Engineering & Technology (WIET)** to digitize and automate their entire library operations. This system replaces the traditional manual/Excel-based approach with a modern, real-time, web-based solution.

### 1.2 Problem Statement

**Before Implementation:**

- 📋 Manual book registers (handwritten entries)
- 📊 Excel sheets for tracking (prone to errors)
- 🐌 Slow issue/return process (5-10 minutes per transaction)
- ❌ No real-time availability tracking
- 📞 Manual fine calculations (calculation errors)
- 📝 No digital borrowing history for students
- 🔍 Difficult to search books across 10,000+ titles
- 📈 No analytics or reports for decision making

**After Implementation:**

- ✅ Fully digital database (MySQL)
- ⚡ Quick transactions (30 seconds with QR scanning)
- 🔄 Real-time book availability
- 💰 Automatic fine calculation
- 📱 Student self-service portal
- 🔍 Advanced search with filters
- 📊 Live dashboards and analytics
- 📧 Automated notifications

### 1.3 Key Objectives

1. **Digitize Library Operations** - Move from manual registers to centralized database
2. **Student Self-Service** - Allow students to manage their library account online
3. **Efficient Circulation** - Quick book issue/return with barcode/QR scanning
4. **Real-Time Analytics** - Live dashboards showing statistics, trends, overdue tracking
5. **Multi-Role Access** - Separate interfaces for Admin, Students, Faculty, and Public
6. **Data Integrity** - Prevent duplicate entries, maintain consistency
7. **Scalability** - Support 2000+ students, 10,000+ books, 50,000+ transactions/year

### 1.4 Technology Stack

```
┌─────────────────────────────────────────────────────────────┐
│                    TECHNOLOGY STACK                          │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  Frontend:                                                   │
│  • HTML5, CSS3, JavaScript (ES6+)                           │
│  • Bootstrap 5.3 (Responsive Design)                        │
│  • Font Awesome 6.4 (Icons)                                 │
│  • AJAX (Asynchronous API calls)                            │
│  • SPA Pattern (Single Page App with hash routing)         │
│                                                              │
│  Backend:                                                    │
│  • PHP 8.x (Server-side logic)                              │
│  • PDO (Database abstraction)                               │
│  • Session Management (Authentication)                      │
│  • RESTful API Design                                       │
│                                                              │
│  Database:                                                   │
│  • MySQL 8.0 / MariaDB 10.6                                 │
│  • 22 Tables, 3 Views, 1 Stored Procedure                   │
│  • InnoDB Engine (ACID compliance)                          │
│  • Foreign Key Constraints                                  │
│                                                              │
│  Server Environment:                                         │
│  • Apache 2.4 (Web server)                                  │
│  • XAMPP / WAMP (Development)                               │
│  • Linux/Windows Server (Production)                        │
│                                                              │
│  Libraries:                                                  │
│  • TCPDF (PDF report generation)                            │
│  • PHPQRCode (QR code generation)                           │
│  • PHPMailer (Email notifications)                          │
│  • Chart.js (Dashboard charts)                              │
│                                                              │
│  Version Control:                                            │
│  • Git / GitHub                                             │
│                                                              │
│  Development Tools:                                          │
│  • VS Code (IDE)                                            │
│  • phpMyAdmin (Database management)                         │
│  • Postman (API testing)                                    │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

### 1.5 Target Users

| User Type       | Count     | Primary Functions                      | Access Level       |
| --------------- | --------- | -------------------------------------- | ------------------ |
| **Super Admin** | 1-2       | System configuration, admin management | Full system access |
| **Librarians**  | 3-5       | Book management, circulation, reports  | Admin portal       |
| **Faculty**     | ~100      | Book requests, borrowing               | Member portal      |
| **Students**    | ~2000+    | Search, borrow, renew, history         | Student portal     |
| **Public**      | Unlimited | Book catalog search only               | OPAC (read-only)   |

### 1.6 Project Scope

#### In Scope ✅

- Book catalog management (CRUD operations)
- Book circulation (Issue/Return/Renew)
- Member management (Students, Faculty, Staff)
- Fine calculation and payment tracking
- Student portal with self-service features
- Admin dashboard with analytics
- QR code generation for students
- Barcode scanning for books
- Report generation (PDF/Excel)
- Event management
- Notification system
- Backup and restore
- Public OPAC (Online Public Access Catalog)

#### Out of Scope ❌

- E-book/Digital library integration
- Mobile app (iOS/Android)
- Online payment gateway
- Inter-library loan system
- Book recommendation AI/ML
- Social features (reviews, ratings, comments)
- Email/SMS notifications (partially implemented)

### 1.7 Project Timeline

```
Phase 1: Database Design & Setup               [COMPLETED] ✅
├─ ER Diagram creation
├─ Table structure finalization
├─ Sample data import (10,000+ books)
└─ Database migrations

Phase 2: Admin Portal Development             [COMPLETED] ✅
├─ Authentication system
├─ Dashboard with analytics
├─ Books management (Add/Edit/Delete/Search)
├─ Circulation module (Issue/Return/Renew)
├─ Member management
├─ Reports generation
└─ Settings & configuration

Phase 3: Student Portal Development           [IN PROGRESS] 🔄
├─ Student authentication                      [COMPLETED] ✅
├─ Dashboard                                   [COMPLETED] ✅
├─ My Books (Active issues)                    [COMPLETED] ✅
├─ Borrowing History                           [COMPLETED] ✅
├─ Search Books                                [COMPLETED] ✅
├─ Notifications                               [COMPLETED] ✅
├─ Recommendations                             [COMPLETED] ✅
├─ Library Events                              [COMPLETED] ✅
├─ Digital ID                                  [IN PROGRESS] 🔄
├─ My Profile                                  [IN PROGRESS] 🔄
└─ My Footfall                                 [IN PROGRESS] 🔄

Phase 4: Public OPAC                           [COMPLETED] ✅
├─ Book search interface
└─ Advanced filters

Phase 5: Testing & Deployment                  [PLANNED] 📋
├─ Unit testing
├─ Integration testing
├─ User acceptance testing
├─ Performance testing
└─ Production deployment

Phase 6: Training & Documentation              [ONGOING] 📝
├─ User manuals
├─ Admin training
├─ Student orientation
└─ Technical documentation
```

---

## 2. System Architecture

### 2.1 High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                         CLIENT LAYER (Browser)                       │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐            │
│  │   Admin UI   │  │  Student UI  │  │   Public     │            │
│  │              │  │              │  │   OPAC UI    │            │
│  │  HTML + CSS  │  │  HTML + CSS  │  │  HTML + CSS  │            │
│  │  JavaScript  │  │  JavaScript  │  │  JavaScript  │            │
│  └──────────────┘  └──────────────┘  └──────────────┘            │
│         │                  │                  │                     │
│         └──────────────────┼──────────────────┘                     │
│                            │ HTTP/HTTPS                             │
│                            ↓                                         │
└─────────────────────────────────────────────────────────────────────┘
                             │
┌─────────────────────────────────────────────────────────────────────┐
│                    WEB SERVER LAYER (Apache)                         │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌────────────────────────────────────────────────────────────┐   │
│  │                   PHP Application Layer                     │   │
│  │                                                              │   │
│  │  Admin Portal (admin/)          Student Portal (student/)   │   │
│  │  ├─ admin_login.php             ├─ student_login.php        │   │
│  │  ├─ dashboard.php               ├─ layout.php               │   │
│  │  ├─ books-management.php        ├─ dashboard.php            │   │
│  │  ├─ circulation.php             ├─ my-books.php             │   │
│  │  ├─ members.php                 ├─ borrowing-history.php    │   │
│  │  ├─ reports.php                 ├─ search-books.php         │   │
│  │  ├─ settings.php                ├─ notifications.php        │   │
│  │  └─ ...                         └─ ...                      │   │
│  │                                                              │   │
│  │  API Layer (admin/api/)         Public (/)                  │   │
│  │  ├─ books.php                   ├─ index.php                │   │
│  │  ├─ circulation.php             └─ opac.php                 │   │
│  │  ├─ members.php                                             │   │
│  │  ├─ dashboard.php                                           │   │
│  │  └─ ...                                                     │   │
│  │                                                              │   │
│  │  Shared Layer (includes/)                                   │   │
│  │  ├─ db_connect.php (Database connection)                    │   │
│  │  ├─ functions.php (Reusable functions)                      │   │
│  │  └─ session_check.php (Auth validation)                     │   │
│  └────────────────────────────────────────────────────────────┘   │
│                            │ PDO/MySQLi                            │
│                            ↓                                        │
└─────────────────────────────────────────────────────────────────────┘
                             │
┌─────────────────────────────────────────────────────────────────────┐
│                    DATABASE LAYER (MySQL)                            │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌────────────────────────────────────────────────────────────┐   │
│  │           Database: wiet_library                            │   │
│  │                                                              │   │
│  │  Core Tables (22):                                          │   │
│  │  • Books             • Holding           • Member           │   │
│  │  • Student           • Circulation       • Return           │   │
│  │  • Footfall          • LibraryEvents     • Notifications    │   │
│  │  • Admin             • FinePayments      • EventRegistrations│  │
│  │  • Recommendations   • BookRequests      • ActivityLog      │   │
│  │  • BackupHistory     • Categories        • Publishers       │   │
│  │  • Authors           • Subjects          • BookReviews      │   │
│  │                                                              │   │
│  │  Views (3):                                                 │   │
│  │  • ActiveCirculations                                       │   │
│  │  • OverdueBooks                                             │   │
│  │  • MemberBooksSummary                                       │   │
│  │                                                              │   │
│  │  Stored Procedures (1):                                     │   │
│  │  • sp_check_overdue_books()                                 │   │
│  └────────────────────────────────────────────────────────────┘   │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

### 2.2 Three-Tier Architecture Breakdown

#### Tier 1: Presentation Layer (Frontend)

**Responsibility:** User interface and user experience

**Components:**

- HTML5 pages with semantic structure
- CSS3 for styling (custom + Bootstrap)
- JavaScript for interactivity (vanilla JS, no frameworks)
- AJAX for asynchronous API calls
- Responsive design (mobile-first approach)

**Key Features:**

- Single Page Application (SPA) pattern using hash routing
- Real-time form validation
- Dynamic content loading (no page refresh)
- Modal popups for confirmations
- Loading states and error handling
- Accessibility features (ARIA labels)

#### Tier 2: Business Logic Layer (Backend)

**Responsibility:** Process requests, apply business rules, manage security

**Components:**

- PHP 8.x for server-side processing
- Session management for authentication
- PDO for database queries (prepared statements)
- RESTful API design
- Input validation and sanitization
- Error handling and logging

**Key Features:**

- API endpoints for CRUD operations
- Authentication middleware
- Role-based access control
- Fine calculation algorithms
- Report generation logic
- Notification triggers
- Backup/restore operations

#### Tier 3: Data Layer (Database)

**Responsibility:** Data storage, retrieval, and integrity

**Components:**

- MySQL 8.0 database server
- 22 normalized tables
- Foreign key constraints
- Indexes for performance
- Views for complex queries
- Stored procedures for batch operations

**Key Features:**

- ACID transactions
- Referential integrity
- Cascading deletes/updates
- Triggers for logging
- Scheduled events (auto-overdue checks)

### 2.3 Request Flow Example: Issue Book

```
┌─────────────────────────────────────────────────────────────────┐
│ STEP 1: Admin Scans Member QR Code                              │
├─────────────────────────────────────────────────────────────────┤
│ Browser → JavaScript QR Scanner captures MemberNo: 2024001      │
│                                                                  │
│ AJAX GET Request:                                               │
│ admin/api/members.php?action=get&memberNo=2024001              │
│                                                                  │
│ Response (JSON):                                                │
│ {                                                               │
│   "success": true,                                              │
│   "data": {                                                     │
│     "MemberNo": 2024001,                                        │
│     "MemberName": "Rahul Sharma",                               │
│     "Group": "Student",                                         │
│     "BooksIssued": 2,                                           │
│     "Status": "Active",                                         │
│     "HasOverdue": false                                         │
│   }                                                             │
│ }                                                               │
│                                                                  │
│ UI Updates: Display member details in form                      │
└─────────────────────────────────────────────────────────────────┘
                             ↓
┌─────────────────────────────────────────────────────────────────┐
│ STEP 2: Admin Scans Book Barcode                                │
├─────────────────────────────────────────────────────────────────┤
│ Browser → Barcode scanner captures AccNo: BE8950                │
│                                                                  │
│ AJAX GET Request:                                               │
│ admin/api/books.php?action=lookup&accNo=BE8950                 │
│                                                                  │
│ Response (JSON):                                                │
│ {                                                               │
│   "success": true,                                              │
│   "data": {                                                     │
│     "AccNo": "BE8950",                                          │
│     "CatNo": 10084,                                             │
│     "Title": "Information Technology for Management",           │
│     "Author1": "Lucas, H.C.",                                   │
│     "Status": "Available",                                      │
│     "Location": "CMTC"                                          │
│   }                                                             │
│ }                                                               │
│                                                                  │
│ UI Updates: Display book details, enable "Issue" button         │
└─────────────────────────────────────────────────────────────────┘
                             ↓
┌─────────────────────────────────────────────────────────────────┐
│ STEP 3: Admin Clicks "Issue Book" Button                        │
├─────────────────────────────────────────────────────────────────┤
│ Browser → JavaScript validation checks:                         │
│ ✓ Member is active                                              │
│ ✓ Member has no overdue books                                   │
│ ✓ Member hasn't exceeded book limit (3 books max)               │
│ ✓ Book is available                                             │
│                                                                  │
│ AJAX POST Request:                                              │
│ admin/api/circulation.php?action=issue                          │
│                                                                  │
│ POST Data:                                                      │
│ {                                                               │
│   "memberNo": 2024001,                                          │
│   "accNo": "BE8950",                                            │
│   "dueDate": "2025-02-15" (today + 15 days)                    │
│ }                                                               │
└─────────────────────────────────────────────────────────────────┘
                             ↓
┌─────────────────────────────────────────────────────────────────┐
│ STEP 4: Backend Processing (circulation.php)                    │
├─────────────────────────────────────────────────────────────────┤
│ PHP Code Execution:                                             │
│                                                                  │
│ 1. Validate admin session                                       │
│    if (!isset($_SESSION['admin_logged_in'])) { exit; }         │
│                                                                  │
│ 2. Sanitize inputs                                              │
│    $memberNo = filter_var($_POST['memberNo'], FILTER_SANITIZE); │
│    $accNo = filter_var($_POST['accNo'], FILTER_SANITIZE);      │
│                                                                  │
│ 3. Re-validate business rules (server-side)                     │
│    • Check member status in database                            │
│    • Check book availability                                    │
│    • Check overdue books                                        │
│    • Check book limit                                           │
│                                                                  │
│ 4. Calculate due date                                           │
│    $dueDate = date('Y-m-d', strtotime('+15 days'));            │
│                                                                  │
│ 5. Begin database transaction                                   │
│    $pdo->beginTransaction();                                    │
└─────────────────────────────────────────────────────────────────┘
                             ↓
┌─────────────────────────────────────────────────────────────────┐
│ STEP 5: Database Transactions                                   │
├─────────────────────────────────────────────────────────────────┤
│ Transaction 1: Insert into Circulation                          │
│ INSERT INTO Circulation (                                       │
│   MemberNo, AccNo, IssueDate, DueDate, Status, CreatedBy       │
│ ) VALUES (                                                      │
│   2024001, 'BE8950', '2025-01-31', '2025-02-15', 'Active', 1   │
│ );                                                              │
│ → CirculationID = 145 (auto-increment)                          │
│                                                                  │
│ Transaction 2: Update Holding status                            │
│ UPDATE Holding                                                  │
│ SET Status = 'Issued'                                           │
│ WHERE AccNo = 'BE8950';                                         │
│ → 1 row affected                                                │
│                                                                  │
│ Transaction 3: Increment member's books issued                  │
│ UPDATE Member                                                   │
│ SET BooksIssued = BooksIssued + 1                               │
│ WHERE MemberNo = 2024001;                                       │
│ → 1 row affected (BooksIssued: 2 → 3)                          │
│                                                                  │
│ Transaction 4: Log activity                                     │
│ INSERT INTO ActivityLog (                                       │
│   UserType, UserID, Action, Details, IPAddress                 │
│ ) VALUES (                                                      │
│   'Admin', 1, 'Book Issued',                                    │
│   'Issued BE8950 to Member 2024001', '192.168.1.10'            │
│ );                                                              │
│ → ActivityID = 8945                                             │
│                                                                  │
│ Transaction 5: Create notification                              │
│ INSERT INTO Notifications (                                     │
│   MemberNo, Type, Title, Message                                │
│ ) VALUES (                                                      │
│   2024001, 'issue', 'Book Issued',                              │
│   'Book "Information Technology..." issued. Due: 2025-02-15'   │
│ );                                                              │
│ → NotificationID = 542                                          │
│                                                                  │
│ Commit Transaction:                                             │
│ $pdo->commit(); → All changes saved atomically                  │
└─────────────────────────────────────────────────────────────────┘
                             ↓
┌─────────────────────────────────────────────────────────────────┐
│ STEP 6: Response to Client                                      │
├─────────────────────────────────────────────────────────────────┤
│ JSON Response:                                                  │
│ {                                                               │
│   "success": true,                                              │
│   "message": "Book issued successfully",                        │
│   "data": {                                                     │
│     "CirculationID": 145,                                       │
│     "IssueDate": "2025-01-31",                                  │
│     "DueDate": "2025-02-15",                                    │
│     "MemberName": "Rahul Sharma",                               │
│     "BookTitle": "Information Technology for Management"        │
│   }                                                             │
│ }                                                               │
│                                                                  │
│ UI Updates:                                                     │
│ • Show success message (green alert)                            │
│ • Clear form fields                                             │
│ • Refresh dashboard statistics                                  │
│ • Print receipt (optional)                                      │
│ • Focus on member scan field for next transaction               │
└─────────────────────────────────────────────────────────────────┘
```

### 2.4 Security Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    SECURITY LAYERS                               │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│ Layer 1: Transport Security                                     │
│ ├─ HTTPS (SSL/TLS) encryption in production                     │
│ ├─ Secure cookie transmission (httpOnly, secure flags)          │
│ └─ HSTS (HTTP Strict Transport Security) headers                │
│                                                                  │
│ Layer 2: Authentication                                         │
│ ├─ Password hashing (bcrypt, cost=10)                           │
│ ├─ Session-based authentication                                 │
│ ├─ Session timeout (30 minutes inactivity)                      │
│ ├─ Session regeneration on login                                │
│ └─ Multi-factor authentication (planned)                        │
│                                                                  │
│ Layer 3: Authorization                                          │
│ ├─ Role-based access control (RBAC)                             │
│ ├─ Session validation on every request                          │
│ ├─ Route-level permission checks                                │
│ └─ API endpoint authentication                                  │
│                                                                  │
│ Layer 4: Input Validation                                       │
│ ├─ Client-side validation (JavaScript)                          │
│ ├─ Server-side validation (PHP)                                 │
│ ├─ Input sanitization (htmlspecialchars, filter_var)            │
│ ├─ Prepared statements (SQL injection prevention)               │
│ └─ CSRF tokens (cross-site request forgery protection)          │
│                                                                  │
│ Layer 5: Output Encoding                                        │
│ ├─ HTML entity encoding                                         │
│ ├─ JSON encoding                                                │
│ └─ XSS prevention (cross-site scripting)                        │
│                                                                  │
│ Layer 6: Database Security                                      │
│ ├─ Principle of least privilege (database users)                │
│ ├─ Separate credentials for read/write operations               │
│ ├─ Database connection encryption                               │
│ └─ Regular backups (daily automated)                            │
│                                                                  │
│ Layer 7: Application Security                                   │
│ ├─ Error handling (no sensitive info disclosure)                │
│ ├─ Logging and monitoring                                       │
│ ├─ Rate limiting (brute force protection)                       │
│ └─ File upload validation (if applicable)                       │
│                                                                  │
│ Layer 8: Infrastructure Security                                │
│ ├─ Firewall configuration                                       │
│ ├─ Server hardening                                             │
│ ├─ Regular security updates                                     │
│ └─ Intrusion detection system                                   │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 3. Database Design

### 3.1 Entity-Relationship Diagram

```
┌──────────────────────────────────────────────────────────────────────────┐
│                         DATABASE: wiet_library                            │
│                           Complete ER Diagram                             │
└──────────────────────────────────────────────────────────────────────────┘

┌──────────────┐         ┌──────────────┐         ┌──────────────┐
│    Admin     │         │    Books     │         │   Holding    │
├──────────────┤         ├──────────────┤         ├──────────────┤
│PK AdminID    │         │PK CatNo      │    ┌────│PK HoldID     │
│  Name        │         │  Title       │    │    │FK CatNo      │
│  Email       │    ┌────│  SubTitle    │◄───┘    │UK AccNo      │
│  Password    │    │    │  Author1     │         │  CopyNo      │
│  Role        │    │    │  Author2     │         │  Status      │
│  Status      │    │    │  Author3     │         │  Location    │
│  CreatedBy   │───┐│    │  Publisher   │         │  Section     │
└──────────────┘   ││    │  Year        │         │  BarCode     │
                   ││    │  ISBN        │         └──────────────┘
                   ││    │  Subject     │               │
                   ││    │  Language    │               │
                   ││    │  Pages       │               │
                   ││    │  Edition     │               │
                   ││    │  DateAdded   │               │
                   ││    │  CreatedBy   │───────────────┘
                   ││    └──────────────┘
                   ││
                   ││
┌──────────────┐  ││    ┌──────────────┐         ┌──────────────┐
│   Member     │  ││    │   Student    │         │ Circulation  │
├──────────────┤  ││    ├──────────────┤         ├──────────────┤
│PK MemberNo   │  ││    │PK StudentID  │         │PK CirculationID│
│  MemberName  │◄─┼┘    │FK MemberNo   │◄────┐   │FK MemberNo   │───┐
│  Group       │  │     │  PRN         │     │   │FK AccNo      │   │
│  Designation │  │     │  Branch      │     │   │  IssueDate   │   │
│  Email       │  │     │  DOB         │     │   │  IssueTime   │   │
│  Phone       │  │     │  BloodGroup  │     │   │  DueDate     │   │
│  BooksIssued │  │     │  Mobile      │     │   │  RenewalCount│   │
│  Status      │  │     │  Photo       │     │   │  Status      │   │
│  FinePerDay  │  │     │  QRCode      │     │   │  CreatedBy   │───┘
│  AdmissionDt │  │     │  ValidTill   │     │   └──────────────┘
│  ClosingDate │  │     │  CourseName  │     │         │
└──────────────┘  │     └──────────────┘     │         │
       │          │                           │         │
       │          └───────────────────────────┘         │
       │                                                 │
       │                                                 ↓
       │                                        ┌──────────────┐
       │                                        │   Return     │
       │                                        ├──────────────┤
       │                                        │PK ReturnID   │
       │                                        │FK CirculationID│
       │                                        │FK MemberNo   │
       │                                        │FK AccNo      │
       │                                        │  ReturnDate  │
       │                                        │  ReturnTime  │
       │                                        │  FineAmount  │
       │                                        │  Condition   │
       │                                        │  Remarks     │
       │                                        └──────────────┘
       │
       ↓
┌──────────────┐         ┌──────────────┐         ┌──────────────┐
│  Footfall    │         │LibraryEvents │         │Notifications │
├──────────────┤         ├──────────────┤         ├──────────────┤
│PK FootfallID │         │PK EventID    │         │PK NotificationID│
│FK MemberNo   │         │  EventTitle  │         │FK MemberNo   │
│  Date        │         │  EventType   │         │  Type        │
│  TimeIn      │         │  Description │         │  Title       │
│  TimeOut     │         │  StartDate   │         │  Message     │
└──────────────┘         │  EndDate     │         │  DateSent    │
                         │  StartTime   │         │  IsRead      │
                         │  EndTime     │         └──────────────┘
                         │  Venue       │
                         │  Capacity    │
                         │  Status      │
                         │  CreatedBy   │
                         └──────────────┘
                               │
                               ↓
                   ┌──────────────────────┐
                   │EventRegistrations    │
                   ├──────────────────────┤
                   │PK RegistrationID     │
                   │FK EventID            │
                   │FK MemberNo           │
                   │  RegistrationDate    │
                   │  Status              │
                   └──────────────────────┘

┌──────────────┐         ┌──────────────┐         ┌──────────────┐
│FinePayments  │         │BookRequests  │         │ActivityLog   │
├──────────────┤         ├──────────────┤         ├──────────────┤
│PK PaymentID  │         │PK RequestID  │         │PK LogID      │
│FK MemberNo   │         │FK MemberNo   │         │  UserType    │
│  Amount      │         │  BookTitle   │         │  UserID      │
│  PaymentDate │         │  Author      │         │  Action      │
│  PaymentMode │         │  ISBN        │         │  Details     │
│  ReceiptNo   │         │  RequestDate │         │  IPAddress   │
│  Remarks     │         │  Status      │         │  Timestamp   │
└──────────────┘         │  Remarks     │         └──────────────┘
                         └──────────────┘

┌──────────────┐         ┌──────────────┐         ┌──────────────┐
│Recommendations│        │BackupHistory │         │Categories    │
├──────────────┤         ├──────────────┤         ├──────────────┤
│PK RecommendID │         │PK BackupID   │         │PK CategoryID │
│FK MemberNo   │         │  BackupFile  │         │  CategoryName│
│FK CatNo      │         │  BackupDate  │         │  Description │
│  Score       │         │  BackupSize  │         └──────────────┘
│  Reason      │         │  CreatedBy   │
│  DateAdded   │         └──────────────┘
└──────────────┘
```

### 3.2 Core Tables Detailed

#### 3.2.1 Books Table - Book Catalog

**Purpose:** Stores bibliographic information for all books. One record per unique title.

```sql
CREATE TABLE Books (
    CatNo INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Unique catalog number',
    Title VARCHAR(255) NOT NULL COMMENT 'Book title',
    SubTitle VARCHAR(255) COMMENT 'Book subtitle',
    Author1 VARCHAR(100) COMMENT 'Primary author',
    Author2 VARCHAR(100) COMMENT 'Second author',
    Author3 VARCHAR(100) COMMENT 'Third author',
    Publisher VARCHAR(100) COMMENT 'Publisher name',
    Year INT COMMENT 'Publication year',
    ISBN VARCHAR(20) COMMENT 'ISBN number (10 or 13 digit)',
    Subject VARCHAR(100) COMMENT 'Subject/Category (Computer Science, Electronics, etc.)',
    Language VARCHAR(50) DEFAULT 'English' COMMENT 'Language of the book',
    Pages VARCHAR(20) COMMENT 'Number of pages (e.g., "450p")',
    Edition VARCHAR(50) COMMENT 'Edition information (1st, 2nd, Revised, etc.)',
    Description TEXT COMMENT 'Book description/summary',
    Keywords TEXT COMMENT 'Search keywords (comma-separated)',
    DateAdded DATE DEFAULT CURRENT_DATE COMMENT 'Date added to catalog',
    LastUpdated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CreatedBy INT COMMENT 'Admin ID who added this book',
    FOREIGN KEY (CreatedBy) REFERENCES Admin(AdminID),
    INDEX idx_title (Title),
    INDEX idx_author (Author1),
    INDEX idx_isbn (ISBN),
    INDEX idx_subject (Subject)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Sample Data:**

```sql
CatNo: 10084
Title: "INFORMATION TECHNOLOGY FOR MANAGEMENT"
Author1: "LUCAS, H.C."
Author2: NULL
Author3: NULL
Publisher: "TATA McGRAW HILL"
Year: 2001
ISBN: "0070403120"
Subject: "Information Technology"
Language: "English"
Pages: "550p"
Edition: "1st"
DateAdded: "2024-01-15"
CreatedBy: 1
```

**Business Rules:**

- Title is mandatory
- CatNo is auto-generated (sequential)
- Author1 is the primary author (minimum one required)
- ISBN should be unique (if provided)
- Subject is used for categorization
- DateAdded is automatically set

---

#### 3.2.2 Holding Table - Physical Book Copies

**Purpose:** Tracks physical copies of books. One book (CatNo) can have multiple holdings.

```sql
CREATE TABLE Holding (
    HoldID INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Unique holding ID',
    AccNo VARCHAR(20) UNIQUE NOT NULL COMMENT 'Accession number (physical copy identifier)',
    CatNo INT NOT NULL COMMENT 'Links to Books table',
    CopyNo INT DEFAULT 1 COMMENT 'Copy number (1st copy, 2nd copy, etc.)',
    Status VARCHAR(20) DEFAULT 'Available' COMMENT 'Available/Issued/Damaged/Lost/Withdrawn',
    Location VARCHAR(100) COMMENT 'Physical location in library (e.g., "CMTC", "Rack-5")',
    Section VARCHAR(50) COMMENT 'Section (Reference, Circulation, Reserve)',
    Collection VARCHAR(50) COMMENT 'Collection type (General, Special, Rare)',
    ClassNo VARCHAR(50) COMMENT 'Classification number (DDC/LCC)',
    AccDate DATE COMMENT 'Date of accession',
    Price DECIMAL(10,2) COMMENT 'Book price',
    Vendor VARCHAR(100) COMMENT 'Vendor/supplier name',
    BillNo VARCHAR(50) COMMENT 'Bill/Invoice number',
    BarCode VARCHAR(50) COMMENT 'Barcode for scanning',
    RFIDTag VARCHAR(50) COMMENT 'RFID tag (if applicable)',
    DateAdded TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CatNo) REFERENCES Books(CatNo) ON DELETE CASCADE,
    INDEX idx_accno (AccNo),
    INDEX idx_catno (CatNo),
    INDEX idx_status (Status),
    INDEX idx_barcode (BarCode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Sample Data:**

```sql
HoldID: 1
AccNo: "BE8950"
CatNo: 10084  (Links to "Information Technology for Management")
CopyNo: 1
Status: "Available"
Location: "CMTC"
Section: "Circulation"
ClassNo: "1.642"
AccDate: "2024-01-20"
Price: 450.00
BarCode: "BE8950"
```

**Relationship with Books:**

```
Books (CatNo 10084) ──┬─→ Holding (AccNo BE8950) - Copy 1
                      ├─→ Holding (AccNo BE8951) - Copy 2
                      └─→ Holding (AccNo BE8952) - Copy 3
```

**Status Values:**

- `Available` - Book is on shelf, ready to issue
- `Issued` - Book is currently issued to a member
- `Damaged` - Book is damaged, needs repair
- `Lost` - Book is lost
- `Withdrawn` - Book removed from circulation
- `Reference` - Reference book (cannot be issued)
- `Binding` - Book sent for binding/repair

---

#### 3.2.3 Member Table - All Library Members

**Purpose:** Stores information for all library members (students, faculty, staff).

```sql
CREATE TABLE Member (
    MemberNo INT PRIMARY KEY COMMENT 'Unique member number (manually assigned)',
    MemberName VARCHAR(100) NOT NULL COMMENT 'Full name of member',
    `Group` VARCHAR(50) DEFAULT 'Student' COMMENT 'Student/Faculty/Staff/Alumni',
    Designation VARCHAR(100) COMMENT 'Job title (for faculty/staff)',
    Department VARCHAR(100) COMMENT 'Department name',
    Email VARCHAR(100) UNIQUE COMMENT 'Email address (used for login)',
    Phone VARCHAR(15) COMMENT 'Phone number',
    Address TEXT COMMENT 'Full address',
    BooksIssued INT DEFAULT 0 COMMENT 'Number of books currently issued',
    MaxBooksAllowed INT DEFAULT 3 COMMENT 'Maximum books allowed',
    Status VARCHAR(20) DEFAULT 'Active' COMMENT 'Active/Inactive/Suspended/Blocked',
    FinePerDay DECIMAL(5,2) DEFAULT 2.00 COMMENT 'Fine rate per day for overdue',
    AdmissionDate DATE COMMENT 'Date of admission/joining',
    ClosingDate DATE COMMENT 'Account closure date',
    Photo BLOB COMMENT 'Member photo (binary data)',
    Remarks TEXT COMMENT 'Additional remarks',
    DateAdded TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    LastUpdated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (Email),
    INDEX idx_name (MemberName),
    INDEX idx_group (`Group`),
    INDEX idx_status (Status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Sample Data:**

```sql
MemberNo: 2024001
MemberName: "Rahul Kumar Sharma"
Group: "Student"
Designation: NULL
Department: "Computer Engineering"
Email: "rahul.sharma@wiet.ac.in"
Phone: "9876543210"
BooksIssued: 2
MaxBooksAllowed: 3
Status: "Active"
FinePerDay: 2.00
AdmissionDate: "2024-06-01"
```

**Business Rules:**

- MemberNo is manually assigned (not auto-increment)
- Email must be unique (used for login)
- BooksIssued is automatically updated during issue/return
- MaxBooksAllowed varies by Group (Students: 3, Faculty: 5)
- Status determines if member can borrow books

---

#### 3.2.4 Student Table - Extended Student Information

**Purpose:** Stores student-specific details. Extends the Member table.

```sql
CREATE TABLE Student (
    StudentID INT PRIMARY KEY AUTO_INCREMENT,
    MemberNo INT NOT NULL UNIQUE COMMENT 'Links to Member table',
    PRN VARCHAR(20) UNIQUE COMMENT 'Permanent Registration Number',
    RollNo VARCHAR(20) COMMENT 'Roll number',
    Branch VARCHAR(100) COMMENT 'Branch (Computer/Mechanical/Civil/Electronics)',
    Semester INT COMMENT 'Current semester',
    AcademicYear VARCHAR(20) COMMENT 'Academic year (e.g., "2024-25")',
    DOB DATE COMMENT 'Date of birth',
    BloodGroup VARCHAR(5) COMMENT 'Blood group (A+, B+, O+, etc.)',
    Gender VARCHAR(10) COMMENT 'Male/Female/Other',
    FatherName VARCHAR(100) COMMENT 'Father name',
    MotherName VARCHAR(100) COMMENT 'Mother name',
    GuardianPhone VARCHAR(15) COMMENT 'Guardian phone number',
    Mobile VARCHAR(15) COMMENT 'Student mobile number',
    AlternateEmail VARCHAR(100) COMMENT 'Personal email',
    AadhaarNo VARCHAR(12) COMMENT 'Aadhaar number',
    Photo BLOB COMMENT 'Student photo',
    QRCode VARCHAR(255) COMMENT 'Path to QR code image',
    ValidTill DATE COMMENT 'Library membership validity',
    CourseName VARCHAR(100) COMMENT 'B.Tech/M.Tech/Diploma',
    DateAdded TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (MemberNo) REFERENCES Member(MemberNo) ON DELETE CASCADE,
    INDEX idx_prn (PRN),
    INDEX idx_branch (Branch)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Sample Data:**

```sql
StudentID: 1
MemberNo: 2024001
PRN: "WIT2024001"
RollNo: "24CO01"
Branch: "Computer Engineering"
Semester: 3
AcademicYear: "2024-25"
DOB: "2005-03-15"
BloodGroup: "B+"
Gender: "Male"
Mobile: "9876543210"
QRCode: "/storage/qrcodes/2024001.png"
ValidTill: "2028-05-31"
CourseName: "B.Tech"
```

**Relationship:**

```
Member (MemberNo: 2024001) ──1:1──→ Student (MemberNo: 2024001)
```

---

#### 3.2.5 Circulation Table - Active Book Issues

**Purpose:** Tracks currently issued books. Records stay here until book is returned.

```sql
CREATE TABLE Circulation (
    CirculationID INT PRIMARY KEY AUTO_INCREMENT,
    MemberNo INT NOT NULL COMMENT 'Who borrowed the book',
    AccNo VARCHAR(20) NOT NULL COMMENT 'Which book (physical copy)',
    IssueDate DATE NOT NULL DEFAULT CURRENT_DATE COMMENT 'Date of issue',
    IssueTime TIME COMMENT 'Time of issue',
    DueDate DATE NOT NULL COMMENT 'Due date (IssueDate + loan period)',
    RenewalCount INT DEFAULT 0 COMMENT 'Number of times renewed',
    Status VARCHAR(20) DEFAULT 'Active' COMMENT 'Active/Returned/Lost/Damaged',
    Remarks TEXT COMMENT 'Any special remarks',
    DateAdded TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CreatedBy INT COMMENT 'Admin ID who issued',
    FOREIGN KEY (MemberNo) REFERENCES Member(MemberNo),
    FOREIGN KEY (AccNo) REFERENCES Holding(AccNo),
    FOREIGN KEY (CreatedBy) REFERENCES Admin(AdminID),
    INDEX idx_member (MemberNo),
    INDEX idx_accno (AccNo),
    INDEX idx_status (Status),
    INDEX idx_duedate (DueDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Sample Data:**

```sql
CirculationID: 45
MemberNo: 2024001
AccNo: "BE8950"
IssueDate: "2025-01-01"
IssueTime: "10:30:00"
DueDate: "2025-01-16"  (IssueDate + 15 days)
RenewalCount: 0
Status: "Active"
CreatedBy: 1
```

**Business Logic:**

```php
// Calculate due date
$issueDate = date('Y-m-d');
$loanPeriod = 15; // days
$dueDate = date('Y-m-d', strtotime($issueDate . " +{$loanPeriod} days"));

// Example: Issue on 2025-01-01, due on 2025-01-16
```

**Status Transitions:**

```
Active (Book issued)
   → Returned (Book returned, moved to Return table)
   → Lost (Book declared lost)
   → Damaged (Book returned damaged)
```

---

#### 3.2.6 Return Table - Return History

**Purpose:** Permanent record of all book returns.

```sql
CREATE TABLE `Return` (
    ReturnID INT PRIMARY KEY AUTO_INCREMENT,
    CirculationID INT NOT NULL COMMENT 'Links to Circulation record',
    MemberNo INT NOT NULL COMMENT 'Who returned',
    AccNo VARCHAR(20) NOT NULL COMMENT 'Which book',
    ReturnDate DATE NOT NULL DEFAULT CURRENT_DATE COMMENT 'Date of return',
    ReturnTime TIME COMMENT 'Time of return',
    FineAmount DECIMAL(10,2) DEFAULT 0 COMMENT 'Fine collected (if overdue)',
    Condition VARCHAR(50) DEFAULT 'Good' COMMENT 'Good/Fair/Damaged/Lost',
    Remarks TEXT COMMENT 'Any notes about condition',
    DateAdded TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CirculationID) REFERENCES Circulation(CirculationID),
    FOREIGN KEY (MemberNo) REFERENCES Member(MemberNo),
    FOREIGN KEY (AccNo) REFERENCES Holding(AccNo),
    INDEX idx_circulation (CirculationID),
    INDEX idx_returndate (ReturnDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Sample Data:**

```sql
ReturnID: 12
CirculationID: 45  (Links to above Circulation record)
MemberNo: 2024001
AccNo: "BE8950"
ReturnDate: "2025-01-21"
ReturnTime: "14:30:00"
FineAmount: 10.00  (5 days overdue × ₹2/day)
Condition: "Good"
Remarks: "Returned in good condition"
```

**Fine Calculation:**

```php
function calculateFine($dueDate, $returnDate, $finePerDay) {
    $due = new DateTime($dueDate);
    $return = new DateTime($returnDate);

    if ($return <= $due) {
        return 0; // No fine if returned on/before due date
    }

    $interval = $return->diff($due);
    $overdueDays = $interval->days;

    return $overdueDays * $finePerDay;
}

// Example:
// DueDate: 2025-01-16
// ReturnDate: 2025-01-21
// Overdue: 5 days
// Fine: 5 × ₹2.00 = ₹10.00
```

---

#### 3.2.7 Admin Table - System Administrators

**Purpose:** Stores admin/librarian accounts.

```sql
CREATE TABLE Admin (
    AdminID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(100) NOT NULL COMMENT 'Admin full name',
    Email VARCHAR(100) UNIQUE NOT NULL COMMENT 'Login email',
    Password VARCHAR(255) NOT NULL COMMENT 'Hashed password (bcrypt)',
    Role VARCHAR(50) DEFAULT 'Librarian' COMMENT 'SuperAdmin/Librarian/Assistant',
    Status VARCHAR(20) DEFAULT 'Active' COMMENT 'Active/Inactive',
    LastLogin TIMESTAMP COMMENT 'Last login time',
    DateAdded TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CreatedBy INT COMMENT 'Who created this admin',
    FOREIGN KEY (CreatedBy) REFERENCES Admin(AdminID),
    INDEX idx_email (Email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Sample Data:**

```sql
AdminID: 1
Name: "John Doe"
Email: "john.doe@wiet.ac.in"
Password: "$2y$10$abcd..." (bcrypt hash)
Role: "SuperAdmin"
Status: "Active"
LastLogin: "2025-01-31 09:15:00"
```

**Roles & Permissions:**

```
SuperAdmin:
  - Full system access
  - Manage admins
  - System settings
  - Database backup/restore

Librarian:
  - Book management
  - Circulation operations
  - Member management
  - Reports
```

---

## 4. File Structure Explained

This section maps the workspace folders and key files to features and responsibilities.

```
wiet_lib/
├─ index.php                      # Public landing/router
├─ opac.php                       # Public OPAC (no login)
├─ footfall.php                   # Public footfall capture (if used at gate)
├─ dropbox.php                    # Utility upload/drop integration (optional)
│
├─ admin/                         # Admin/Librarian portal (secured)
│  ├─ index.php                   # Admin router/landing
│  ├─ login.php | logout.php      # Admin auth
│  ├─ dashboard.php               # KPIs, charts, quick actions
│  ├─ books-management.php        # Catalog CRUD + holdings management
│  ├─ bulk-import.php             # CSV/SQL import tools
│  ├─ circulation.php             # Issue/Return/Renew workstation UI
│  ├─ fine-management.php         # Fines & payments
│  ├─ reports.php                 # Reports (PDF/CSV)
│  ├─ members.php | student-management.php | manage-admins.php
│  ├─ stock-verification.php      # Periodic audit / inventory
│  ├─ library-events.php          # Events CRUD (admin side)
│  ├─ settings.php                # Global configuration
│  ├─ layout.php | layout2.php    # Admin layouts/partials
│  ├─ analytics.php               # Deep-dive analytics
│  ├─ export_books_pdf.php        # TCPDF based export
│  ├─ qr-generator.php            # Generate QR for members/books
│  ├─ ajax-handler.php            # Legacy utilities
│  └─ api/                        # REST-like endpoints consumed by UI
│     ├─ books.php                # List/search/lookup holdings & catalog
│     ├─ circulation.php          # Issue/return/renew actions
│     ├─ members.php              # Member lookup/create/update
│     ├─ events.php               # LibraryEvents CRUD
│     ├─ reports.php              # Data for charts/reports
│     └─ … (small utilities)
│
├─ student/                       # Student portal (secured)
│  ├─ layout.php                  # Shell with side-nav + content
│  ├─ dashboard.php               # Live stats, notices
│  ├─ my-books.php                # Active issues + actions + modal
│  ├─ borrowing-history.php       # Full transaction history + modal
│  ├─ search-books.php            # Advanced search via admin/api/books.php
│  ├─ notifications.php           # Overdue/due soon/events/activity (LIVE)
│  ├─ recommendations.php         # Subject/branch based (LIVE)
│  ├─ library-events.php          # Public events list (LIVE)
│  ├─ digital-id.php              # Student ID + QR (WIP)
│  ├─ my-profile.php              # Profile & preferences (WIP)
│  ├─ my-footfall.php             # Visits timeline (WIP)
│  ├─ student_login.php | logout.php
│  └─ get_book_details.php        # API for book details modal (shared)
│
├─ includes/
│  ├─ db_connect.php              # Central PDO connection (MySQL)
│  ├─ functions.php               # Helpers: fines, guards, formatters, logging
│  ├─ session_check.php           # Admin guard
│  └─ requirements.md             # Dev requirements notes
│
├─ database/
│  ├─ schema.sql                  # Canonical schema (CatNo, Author1, …)
│  ├─ bulk_books_import.sql       # Seed data
│  ├─ import_data.php             # Import utility
│  ├─ setup.ps1 | setup.bat       # Windows quick-setup scripts
│  └─ migrations/                 # Future migration scripts
│
├─ libs/phpqrcode/                # Third-party QR generator
├─ images/                        # Logos and UI images
├─ storage/                       # Generated artifacts (QRs, exports, backups)
└─ md files/                      # Design docs, analysis, plans
```

Notes and cross-references:

- Catalog identifiers use CatNo in Books and AccNo in Holding. Older code using CallNo was removed; use CatNo consistently.
- All student pages now fetch LIVE data via PDO and admin/api endpoints. No dummy fallbacks remain in updated pages.
- The public OPAC (`opac.php`) shares the same Books/Holding logic as student search.

---

## 5. User Roles & Permissions

| Role       | Typical Users     | Key Permissions                                                                                 |
| ---------- | ----------------- | ----------------------------------------------------------------------------------------------- |
| SuperAdmin | Head librarian/IT | Manage admins, all settings, backups, full CRUD across modules                                  |
| Librarian  | Library staff     | Circulation (issue/return/renew), catalog and holdings CRUD, member ops, fines, reports, events |
| Assistant  | Student helpers   | Search, check-in assistance, non-destructive ops, limited reports                               |
| Student    | WIET students     | Search, view availability, view/renew my books, history, events, notifications, digital ID      |
| Public     | Visitors          | OPAC search only                                                                                |

Permission highlights:

- Circulation operations require Librarian or above.
- Settings, admin management, and backups require SuperAdmin.
- Student authentication uses email and a default password (first login) with forced change planned.

---

## 6. Core Features

Admin portal:

- Catalog and holdings management, bulk CSV/SQL import, QR/Barcode utilities
- Circulation workstation with guardrails (limits, overdue checks, availability)
- Fines tracking, fine-rate per member, payments and receipts
- Reports: inventory, overdue, circulation KPIs, top borrowers, popular titles
- Events management and registrations overview
- Backup/restore and audit logs

Student portal:

- Dashboard with live stats (active issues, due soon, overdue, fines)
- My Books with renewals and “View Details” modal (shared API)
- Borrowing history (all-time) with late/on-time classification
- Search with availability badges and counts
- Notifications (overdue, due soon, events, activity)
- Recommendations (subject/branch-popularity based)
- Events listing with statuses (Active/Upcoming/Completed)
- Digital ID with QR (in progress)

Public OPAC:

- Search by title/author/ISBN/subject with availability status

---

## 7. API Endpoints

Base path: `admin/api/`

- `books.php`

  - `GET ?action=list&title=&author=&isbn=&subject=&keywords=` → List books with aggregated copies and available counts
  - `GET ?action=lookup&accNo=BE8950` → Lookup a holding+book by AccNo

- `circulation.php`

  - `POST ?action=issue` → Issue a book. Body: memberNo, accNo, dueDate
  - `POST ?action=return` → Return a book. Body: circulationId or accNo; computes fine, records Return
  - `POST ?action=renew` → Renew active issue. Body: circulationId; increments `RenewalCount`

- `members.php`

  - `GET ?action=get&memberNo=2024001` → Member core details and blocks
  - `GET ?action=history&memberNo=…` → Past transactions summary

- `events.php`

  - CRUD endpoints for `LibraryEvents` and registrations

- `reports.php`
  - Data providers for dashboard charts and PDF exports

Response contract (typical):

```json
{ "success": true, "data": [], "message": "optional" }
```

Security:

- All endpoints validate admin sessions; student-facing fetches use read-only endpoints or internal pages that check student session.

---

## 8. Current Implementation Status

Student pages:

- Dashboard ✅ LIVE
- My Books ✅ LIVE + modal
- Borrowing History ✅ LIVE + modal
- Search Books ✅ LIVE (uses admin/api/books.php)
- Notifications ✅ LIVE (DB-driven)
- Recommendations ✅ LIVE (CatNo/Subject-based)
- Library Events ✅ LIVE
- Digital ID 🔄 WIP
- My Profile 🔄 WIP
- My Footfall 🔄 WIP

Admin modules (high level): Dashboard, Circulation, Books, Members, Reports, Events, Settings — all operational; ongoing polish on analytics and inventory.

---

## 9. Student Portal Workflow

1. Sign in → session created (30‑min idle timeout)
2. Dashboard loads live stats and notifications
3. Search or open My Books
4. From My Books, renew if eligible, or open Details modal
5. Review history, events, and recommendations
6. Receive due/overdue notices in Notifications

Modal “View Details” contract:

- Input: `acc_no`, `circulation_id` (when applicable)
- Backend: `student/get_book_details.php` joins Circulation + Holding + Books (+ Member as needed)
- Output: metadata, circulation details, return history snippet

---

## 10. Admin Portal Workflow

- Circulation station: scan member QR → scan book barcode → Issue; Return and Renew similar with server-side validations
- Books management: add/edit titles, manage holdings, import CSV/SQL, generate QR/Barcodes
- Reports: choose template → filter → preview → PDF/CSV
- Events: create/manage events; registration counts visible in list
- Settings: fine rates, loan periods, limits per group

---

## 11. Authentication System

- Admin: email + password (bcrypt), session stored with regeneration on login
- Student: email + default password (first login policy), session with 30‑minute inactivity timeout, logout clears session
- Guards:
  - `includes/session_check.php` for admin pages
  - Inline student checks at top of each student page
- Planned: force password change on first login, optional MFA for admins

---

## 12. Circulation System

Data model:

- Active issues in `Circulation` (Status='Active') with `RenewalCount`
- Returns logged in `Return` with `FineAmount`
- Holdings reflect availability via `Holding.Status`

Rules:

- Loan period defaults to 15 days (configurable)
- Max books per member group: Students 3, Faculty 5 (configurable)
- Renew blocked if overdue, holds/reservations pending, or max renewals reached
- Fine = overdue days × member `FinePerDay`

Edge cases handled:

- Returning a non-active circulation → error
- Issuing when `Holding.Status!='Available'` → error
- Duplicate scan safeguards and audit logging to `ActivityLog`

---

## 13. Reports & Analytics

Dashboards:

- KPIs: total issues, active loans, overdue count, fines collected, top subjects
- Charts via Chart.js (line, bar, pie)

PDF/CSV reports (TCPDF/CSV):

- Inventory list, member-wise circulation, overdue list, fines ledger, monthly footfall

Example SQL (overdue):

```sql
SELECT c.CirculationID, m.MemberName, b.Title, c.DueDate,
             DATEDIFF(CURDATE(), c.DueDate) AS DaysOverdue
FROM Circulation c
JOIN Holding h ON c.AccNo=h.AccNo
JOIN Books b ON b.CatNo=h.CatNo
JOIN Member m ON m.MemberNo=c.MemberNo
WHERE c.Status='Active' AND c.DueDate<CURDATE();
```

---

## 14. QR Code & Scanning

- Library uses QR for member identification and barcodes (or QR) for holdings
- QR generation via `libs/phpqrcode` and `admin/qr-generator.php`
- Student digital ID will embed QR that resolves MemberNo in the circulation UI
- Barcode value for holdings equals `AccNo`

---

## 15. How Data Flows

Search (student): UI → `admin/api/books.php?action=list` → JSON list with `TotalCopies`/`AvailableCopies` → display cards

Issue: Admin UI → `admin/api/circulation.php?action=issue` → DB transactions (Circulation insert, Holding update, Member increment, ActivityLog, Notifications) → success JSON → UI updates

Notifications: Student `notifications.php` composes from Circulation (overdue/due‑soon), LibraryEvents (upcoming), ActivityLog (recent)

---

## 16. Installation & Setup

Prerequisites:

- Windows 10/11 with XAMPP 8.x (Apache + PHP 8 + MySQL)

Steps:

1. Clone repo to `C:\\xampp\\htdocs\\wiet_lib`
2. Create database `wiet_library`
3. Import `database/schema.sql` and then `database/bulk_books_import.sql`
4. Configure DB credentials in `includes/db_connect.php`
5. Start Apache and MySQL in XAMPP
6. Visit `http://localhost/wiet_lib/`
7. Admin login: seed an admin in `Admin` table (email + bcrypt hash)

Optional scripts:

- `database/setup.ps1` or `setup.bat` can automate steps 2–4 on Windows

---

## 17. Testing Guide

Accounts:

- Create a test student (Member + Student) with 1–2 active circulations
- Create an admin account

Manual test cases:

- Student login → dashboard stats load
- My Books shows active items; open “Details” modal; renew when allowed
- Borrowing History lists and classifies returns (on‑time/late)
- Search returns available counts and badges
- Notifications show due soon and overdue correctly
- Admin circulation: issue → renew → return → verify holdings and counts

---

## 18. Deployment Checklist

- Use Apache vhost with HTTPS (Let’s Encrypt)
- Set `display_errors=Off`, `log_errors=On`
- Strong random app secrets and cookie settings (HttpOnly, Secure, SameSite)
- Create DB user with least privilege
- Daily automated backups to `storage/` with offsite copy
- Disable dev endpoints and verbose logs
- Rate limit login endpoints

---

## 19. Future Enhancements

- Force password change on first student login; password reset via email OTP
- Reservations/holds queue and email/SMS notifications
- Online fine payment gateway (UPI/NetBanking)
- Mobile PWA for students
- Advanced recommendations using borrow graph
- Full-text search (MySQL InnoDB FTS) and facets
- Role-based settings UI and per-branch configurations

---

## 20. Troubleshooting

Common schema mismatches:

- Replace legacy `CallNo` with `CatNo` in all queries (Books) and join via `Holding.CatNo = Books.CatNo`.
- Use `Author1` instead of `Author`.
- Fines table uses `FineAmount` in `Return`.

Undefined variable notices:

- Ensure arrays are initialized (`$notifications = []; $library_events = [];`) before filtering/counting.
- Guard `usort()` with `if (count($arr) > 0)`.

CORS/Fetch issues:

- Student pages should call relative paths like `../admin/api/books.php`.

Session problems:

- Verify `session_start()` at top and correct redirects on unauthenticated access.

Performance:

- Add indexes on `Holding(Status, CatNo)`, `Circulation(MemberNo, Status)`, `Books(Subject, Title)`.

If something breaks:

1. Check `php_error.log` and app `error_log()` outputs
2. Validate table/column names against `database/schema.sql`
3. Run minimal query in phpMyAdmin to isolate
4. Add temporary debug dumps, then remove after fix
