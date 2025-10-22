# 🏗️ WIET Library Management System - Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        WIET LIBRARY MANAGEMENT SYSTEM                    │
│                         Database-Driven Architecture                     │
└─────────────────────────────────────────────────────────────────────────┘

┌───────────────────────────────────────────────────────────────────────────────┐
│                              USER INTERFACES                                   │
├──────────────────────┬──────────────────────┬────────────────────────────────┤
│   ADMIN PANEL        │   STUDENT PORTAL     │   PUBLIC OPAC                  │
│   admin/*.php        │   student/*.php      │   opac.php                     │
│                      │                      │                                │
│ • Dashboard          │ • My Dashboard       │ • Search Books                 │
│ • Members Mgmt       │ • My Books           │ • View Availability            │
│ • Books Mgmt         │ • Borrowing History  │ • Library Events               │
│ • Circulation        │ • Digital ID         │ • Contact Info                 │
│ • Reports            │ • E-Resources        │                                │
│ • Settings           │ • Recommendations    │                                │
└──────────────────────┴──────────────────────┴────────────────────────────────┘
                                   │
                                   ▼
┌───────────────────────────────────────────────────────────────────────────────┐
│                              API LAYER                                         │
│                         admin/api/*.php                                        │
├──────────────────────┬──────────────────────┬────────────────────────────────┤
│  members.php         │  circulation.php     │  books.php                     │
│                      │                      │                                │
│ • list              │ • issue              │ • list                         │
│ • get               │ • return             │ • get                          │
│ • add               │ • renew              │ • add                          │
│ • update            │ • active             │ • update                       │
│ • delete            │ • overdue            │ • search                       │
│ • search            │ • history            │ • add-holding                  │
└──────────────────────┴──────────────────────┴────────────────────────────────┘
                                   │
                                   ▼
┌───────────────────────────────────────────────────────────────────────────────┐
│                         BUSINESS LOGIC LAYER                                   │
│                        includes/functions.php                                  │
├───────────────────────────────────────────────────────────────────────────────┤
│                                                                                │
│  Security Functions          Member Functions         Book Functions          │
│  ├─ sanitize()              ├─ getMemberByNo()       ├─ getBookByCatNo()    │
│  ├─ hashPassword()          ├─ canBorrowBook()       ├─ isBookAvailable()   │
│  └─ verifyPassword()        └─ getActiveMembers()    └─ searchBooks()       │
│                                                                                │
│  Circulation Functions       Statistics              Utilities                │
│  ├─ issueBook()             ├─ getDashboardStats()   ├─ formatDate()        │
│  ├─ returnBook()            └─ (various counts)      ├─ sendJson()          │
│  └─ getOverdueBooks()                                └─ logActivity()        │
│                                                                                │
└───────────────────────────────────────────────────────────────────────────────┘
                                   │
                                   ▼
┌───────────────────────────────────────────────────────────────────────────────┐
│                         DATABASE CONNECTION                                    │
│                        includes/db_connect.php                                 │
├───────────────────────────────────────────────────────────────────────────────┤
│                                                                                │
│  PDO Connection with Error Handling                                           │
│  ├─ Host: localhost                                                           │
│  ├─ Database: wiet_library                                                    │
│  ├─ User: root                                                                │
│  └─ Charset: utf8mb4                                                          │
│                                                                                │
└───────────────────────────────────────────────────────────────────────────────┘
                                   │
                                   ▼
┌───────────────────────────────────────────────────────────────────────────────┐
│                           DATABASE LAYER                                       │
│                      MySQL Database: wiet_library                              │
└───────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                       CORE TABLES                               │
├──────────────┬──────────────┬──────────────┬──────────────────┤
│   ADMIN      │    BOOKS     │   HOLDING    │     MEMBER       │
│              │              │              │                  │
│ AdminID (PK) │ CatNo (PK)   │ HoldID (PK)  │ MemberNo (PK)    │
│ Name         │ Title        │ AccNo (UK)   │ MemberName       │
│ Email (UK)   │ Author1-3    │ CatNo (FK)   │ Group            │
│ Password     │ Publisher    │ Status       │ Phone            │
│ Role         │ Year         │ Location     │ Email            │
│ Status       │ ISBN         │ AccDate      │ BooksIssued      │
│              │ Subject      │              │ Status           │
└──────────────┴──────────────┴──────────────┴──────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                    EXTENDED TABLES                              │
├──────────────┬──────────────┬──────────────┬──────────────────┤
│   STUDENT    │   FACULTY    │ CIRCULATION  │     RETURN       │
│              │              │              │                  │
│ StudentID    │ FacultyID    │ CircID (PK)  │ ReturnID (PK)    │
│ MemberNo(FK) │ MemberNo(FK) │ MemberNo(FK) │ CircID (FK)      │
│ PRN          │ EmployeeID   │ AccNo (FK)   │ ReturnDate       │
│ Branch       │ Department   │ IssueDate    │ FineAmount       │
│ DOB          │ Designation  │ DueDate      │ Condition        │
│ Address      │ JoinDate     │ Status       │ Remarks          │
│ QRCode       │              │              │                  │
└──────────────┴──────────────┴──────────────┴──────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                   SUPPORTING TABLES                             │
├──────────────┬──────────────┬──────────────┬──────────────────┤
│  FOOTFALL    │  EVENTS      │NOTIFICATIONS │  ACTIVITY_LOG    │
│              │              │              │                  │
│ FootfallID   │ EventID      │ NotifID      │ LogID            │
│ MemberNo(FK) │ Title        │ MemberNo(FK) │ UserID           │
│ Date         │ Description  │ Title        │ Action           │
│ TimeIn       │ EventDate    │ Message      │ Details          │
│ TimeOut      │ Location     │ Type         │ IPAddress        │
│ Duration     │ Status       │ IsRead       │ Timestamp        │
└──────────────┴──────────────┴──────────────┴──────────────────┘

┌───────────────────────────────────────────────────────────────────┐
│                      DATABASE VIEWS                               │
├───────────────────────────────────────────────────────────────────┤
│                                                                   │
│ v_available_books          - Books with copy counts              │
│ v_active_circulations      - Active issues with fine calculation │
│ v_member_summary           - Member borrowing statistics         │
│                                                                   │
└───────────────────────────────────────────────────────────────────┘

═══════════════════════════════════════════════════════════════════

                        DATA FLOW EXAMPLE
                      (Issue Book Operation)

User Action: Admin issues book BE8950 to member 2511
      │
      ├─► 1. HTTP POST to: api/circulation.php?action=issue
      │      Body: {memberNo: 2511, accNo: "BE8950"}
      │
      ├─► 2. API validates input and calls: issueBook($pdo, 2511, "BE8950")
      │
      ├─► 3. Function checks:
      │      ├─ isBookAvailable($pdo, "BE8950") → TRUE?
      │      └─ canBorrowBook($pdo, 2511) → TRUE?
      │
      ├─► 4. If valid, BEGIN TRANSACTION:
      │      ├─ INSERT INTO Circulation (MemberNo, AccNo, IssueDate, DueDate, Status)
      │      ├─ UPDATE Holding SET Status='Issued' WHERE AccNo='BE8950'
      │      └─ UPDATE Member SET BooksIssued=BooksIssued+1 WHERE MemberNo=2511
      │
      ├─► 5. COMMIT TRANSACTION
      │
      └─► 6. Return JSON: {success: true, dueDate: "2025-11-03"}

═══════════════════════════════════════════════════════════════════

                    SECURITY ARCHITECTURE

┌─────────────────────────────────────────────────────────────────┐
│                      INPUT VALIDATION                            │
│  • sanitize() - XSS prevention                                  │
│  • validateEmail() - Email format check                         │
│  • Type checking in API endpoints                               │
└─────────────────────────────────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                   SQL INJECTION PREVENTION                       │
│  • PDO Prepared Statements                                      │
│  • Parameter binding                                            │
│  • No direct SQL concatenation                                  │
└─────────────────────────────────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                    PASSWORD SECURITY                             │
│  • password_hash() - Bcrypt hashing                             │
│  • password_verify() - Secure verification                      │
│  • Never store plain text passwords                             │
└─────────────────────────────────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                      SESSION MANAGEMENT                          │
│  • session_start() on protected pages                           │
│  • Admin role checking                                          │
│  • Session timeout handling                                     │
└─────────────────────────────────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                      ACTIVITY LOGGING                            │
│  • logActivity() tracks all actions                             │
│  • ActivityLog table stores audit trail                         │
│  • Includes UserID, Action, Timestamp                           │
└─────────────────────────────────────────────────────────────────┘

═══════════════════════════════════════════════════════════════════

                      FILE ORGANIZATION

wiet_lib/
│
├─ admin/                  ← Admin Interface
│  ├─ api/                 ← RESTful API Endpoints
│  │  ├─ members.php       ← Member CRUD
│  │  ├─ circulation.php   ← Issue/Return/Renew
│  │  └─ books.php         ← Book CRUD
│  │
│  ├─ dashboard.php        ← Admin Home (✅ Live)
│  ├─ members.php          ← Member Management (✅ Live)
│  ├─ circulation.php      ← Book Issue/Return
│  ├─ books-management.php ← Book Catalog
│  └─ settings.php         ← System Settings
│
├─ student/                ← Student Interface
│  ├─ dashboard.php        ← Student Home
│  ├─ my-books.php         ← Current Books
│  └─ borrowing-history.php← Past Borrows
│
├─ includes/               ← Core PHP Files
│  ├─ db_connect.php       ← Database Connection (✅)
│  └─ functions.php        ← Business Logic (✅)
│
├─ database/               ← Database Scripts
│  ├─ schema.sql           ← Create Tables (✅)
│  └─ import_data.php      ← Sample Data (✅)
│
└─ Documentation
   ├─ README.md            ← Full Guide (✅)
   ├─ QUICK_START.md       ← Quick Setup (✅)
   ├─ CONVERSION_SUMMARY.md← This Project (✅)
   └─ data.md              ← Data Reference

═══════════════════════════════════════════════════════════════════

                    DEPLOYMENT CHECKLIST

Development (XAMPP):
├─ ☑ Install XAMPP
├─ ☑ Start Apache + MySQL
├─ ☑ Create wiet_library database
├─ ☑ Import schema.sql (15 tables)
├─ ☑ Run import_data.php (sample data)
├─ ☑ Login: admin@wiet.edu.in / admin123
└─ ☑ Test: Members page loads from DB

Production:
├─ ☐ Update db_connect.php credentials
├─ ☐ Change admin passwords (use hashPassword())
├─ ☐ Enable HTTPS
├─ ☐ Set file permissions (644/755)
├─ ☐ Disable error display
├─ ☐ Configure database backups
├─ ☐ Test all API endpoints
└─ ☐ Load balance if needed

═══════════════════════════════════════════════════════════════════

Legend:
  (PK) = Primary Key
  (FK) = Foreign Key
  (UK) = Unique Key
  ✅ = Completed
  ⚠️ = Needs Update
  ☐ = Todo

═══════════════════════════════════════════════════════════════════
