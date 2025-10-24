-- =====================================================
-- WIET Library Management System - Database Schema
-- =====================================================
-- Version: 1.0
-- Date: 2025-10-19
-- Description: Complete database schema for library management
-- with all tables based on er-wiet-lib.md and data.md
-- =====================================================

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS wiet_library CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE wiet_library;

-- =====================================================
-- 1. ADMIN TABLE
-- Stores admin/librarian user accounts
-- =====================================================
CREATE TABLE IF NOT EXISTS Admin (
    AdminID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(100) NOT NULL,
    Email VARCHAR(100) UNIQUE NOT NULL,
    Phone VARCHAR(15),
    Role VARCHAR(50) DEFAULT 'Admin',
    Password VARCHAR(255) NOT NULL,
    Status VARCHAR(20) DEFAULT 'Active',
    CreatedDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    LastLogin TIMESTAMP NULL,
    INDEX idx_email (Email),
    INDEX idx_status (Status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin account (password: admin123)
INSERT INTO Admin (Name, Email, Phone, Role, Password) VALUES 
('System Administrator', 'admin@wiet.edu.in', '9876543210', 'Super Admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Librarian', 'librarian@wiet.edu.in', '9876543211', 'Admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- =====================================================
-- 2. BOOKS TABLE
-- Master catalog of books (one record per title/edition)
-- Based on data.md structure
-- =====================================================
CREATE TABLE IF NOT EXISTS Books (
    CatNo INT PRIMARY KEY AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    SubTitle VARCHAR(255),
    VarTitle VARCHAR(255),
    Author1 VARCHAR(100),
    Author2 VARCHAR(100),
    Author3 VARCHAR(100),
    CorpAuthor VARCHAR(100),
    Editors VARCHAR(100),
    Publisher VARCHAR(100),
    Place VARCHAR(100),
    Year INT,
    Edition VARCHAR(50),
    Vol VARCHAR(20),
    Pages VARCHAR(20),
    ISBN VARCHAR(20),
    Subject VARCHAR(100),
    Keywords TEXT,
    Barcode VARCHAR(255), -- stores barcode value or image path
    QRCode VARCHAR(255),  -- stores QR code value or image path
    Language VARCHAR(50) DEFAULT 'English',
    Format VARCHAR(50),
    DocumentType VARCHAR(10) DEFAULT 'BK',
    Country VARCHAR(50),
    BillNo VARCHAR(50),
    BillDate DATE,
    Currency VARCHAR(10) DEFAULT 'INR',
    ItemPrice DECIMAL(10,2),
    ItemCost DECIMAL(10,2),
    Source VARCHAR(100),
    ModeOfAcquisition VARCHAR(50),
    DateAdded TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CreatedBy INT,
    FOREIGN KEY (CreatedBy) REFERENCES Admin(AdminID) ON DELETE SET NULL,
    INDEX idx_title (Title),
    INDEX idx_author (Author1),
    INDEX idx_subject (Subject),
    INDEX idx_isbn (ISBN),
    FULLTEXT idx_search (Title, Author1, Author2, Subject, Keywords)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. HOLDING TABLE
-- Physical copies of books (multiple copies per book)
-- Based on data.md Accession structure
-- =====================================================
CREATE TABLE IF NOT EXISTS Holding (
    HoldID INT PRIMARY KEY AUTO_INCREMENT,
    AccNo VARCHAR(20) UNIQUE NOT NULL,
    CatNo INT NOT NULL,
    CopyNo INT DEFAULT 1,
    BookNo VARCHAR(20),
    AccDate DATE,
    ClassNo VARCHAR(50),
    Status VARCHAR(20) DEFAULT 'Available',
    Location VARCHAR(100),
    Section VARCHAR(50),
    Collection VARCHAR(50),
    BarCode VARCHAR(50),
    QRCode VARCHAR(255),
    QrCodeImg LONGBLOB,
    Binding VARCHAR(50),
    `Condition` VARCHAR(50) DEFAULT 'Good',
    Remarks TEXT,
    DateAdded TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CatNo) REFERENCES Books(CatNo) ON DELETE CASCADE,
    INDEX idx_accno (AccNo),
    INDEX idx_status (Status),
    INDEX idx_catno (CatNo),
    INDEX idx_location (Location)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. MEMBER TABLE
-- Library members (students, faculty, staff)
-- Based on data.md member structure
-- =====================================================
CREATE TABLE IF NOT EXISTS Member (
    MemberNo INT PRIMARY KEY,
    MemberName VARCHAR(100) NOT NULL,
    `Group` VARCHAR(50),
    Designation VARCHAR(100),
    Entitlement VARCHAR(50),
    Phone VARCHAR(15),
    Email VARCHAR(100),
    FinePerDay DECIMAL(5,2) DEFAULT 2.00,
    AdmissionDate DATE,
    BooksIssued INT DEFAULT 0,
    ClosingDate DATE,
    Status VARCHAR(20) DEFAULT 'Active',
    DateAdded TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (MemberName),
    INDEX idx_status (Status),
    INDEX idx_group (`Group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. STUDENT TABLE
-- Extended student information linked to Member
-- Based on data.md student section
-- =====================================================
CREATE TABLE IF NOT EXISTS Student (
    StudentID INT PRIMARY KEY AUTO_INCREMENT,
    MemberNo INT NOT NULL,
    Photo BLOB,
    Surname VARCHAR(50),
    MiddleName VARCHAR(50),
    FirstName VARCHAR(50),
    DOB DATE,
    Gender VARCHAR(10),
    BloodGroup VARCHAR(5),
    Branch VARCHAR(100),
    CourseName VARCHAR(100),
    ValidTill DATE,
    PRN VARCHAR(20) UNIQUE,
    Mobile VARCHAR(15),
    Email VARCHAR(100),
    Address TEXT,
    CardColour VARCHAR(20),
    QRCode VARCHAR(255),
    FOREIGN KEY (MemberNo) REFERENCES Member(MemberNo) ON DELETE CASCADE,
    INDEX idx_prn (PRN),
    INDEX idx_branch (Branch)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. FACULTY TABLE
-- Faculty/staff information linked to Member
-- =====================================================
CREATE TABLE IF NOT EXISTS Faculty (
    FacultyID INT PRIMARY KEY AUTO_INCREMENT,
    MemberNo INT NOT NULL,
    EmployeeID VARCHAR(20) UNIQUE,
    Department VARCHAR(100),
    Designation VARCHAR(100),
    JoinDate DATE,
    Mobile VARCHAR(15),
    Email VARCHAR(100),
    Address TEXT,
    FOREIGN KEY (MemberNo) REFERENCES Member(MemberNo) ON DELETE CASCADE,
    INDEX idx_empid (EmployeeID),
    INDEX idx_dept (Department)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. CIRCULATION TABLE
-- Book issue/checkout records
-- =====================================================
CREATE TABLE IF NOT EXISTS Circulation (
    CirculationID INT PRIMARY KEY AUTO_INCREMENT,
    MemberNo INT NOT NULL,
    AccNo VARCHAR(20) NOT NULL,
    IssueDate DATE NOT NULL,
    IssueTime TIME,
    DueDate DATE NOT NULL,
    RenewalCount INT DEFAULT 0,
    Status VARCHAR(20) DEFAULT 'Active',
    DateAdded TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CreatedBy INT,
    FOREIGN KEY (MemberNo) REFERENCES Member(MemberNo) ON DELETE RESTRICT,
    FOREIGN KEY (AccNo) REFERENCES Holding(AccNo) ON DELETE RESTRICT,
    FOREIGN KEY (CreatedBy) REFERENCES Admin(AdminID) ON DELETE SET NULL,
    INDEX idx_member (MemberNo),
    INDEX idx_accno (AccNo),
    INDEX idx_status (Status),
    INDEX idx_duedate (DueDate),
    INDEX idx_issuedate (IssueDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. RETURN TABLE
-- Book return records
-- =====================================================
CREATE TABLE IF NOT EXISTS `Return` (
    ReturnID INT PRIMARY KEY AUTO_INCREMENT,
    CirculationID INT NOT NULL,
    MemberNo INT NOT NULL,
    AccNo VARCHAR(20) NOT NULL,
    ReturnDate DATE NOT NULL,
    ReturnTime TIME,
    FineAmount DECIMAL(10,2) DEFAULT 0,
    FinePaid DECIMAL(10,2) DEFAULT 0,
    `Condition` VARCHAR(50) DEFAULT 'Good',
    Remarks TEXT,
    DateAdded TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CirculationID) REFERENCES Circulation(CirculationID) ON DELETE CASCADE,
    FOREIGN KEY (MemberNo) REFERENCES Member(MemberNo) ON DELETE RESTRICT,
    INDEX idx_return_date (ReturnDate),
    INDEX idx_member (MemberNo),
    INDEX idx_circulation (CirculationID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 9. FOOTFALL TABLE
-- Library entry/exit tracking
-- =====================================================
CREATE TABLE IF NOT EXISTS Footfall (
    FootfallID INT PRIMARY KEY AUTO_INCREMENT,
    MemberNo INT NOT NULL,
    Date DATE NOT NULL,
    TimeIn TIME NOT NULL,
    TimeOut TIME,
    Duration INT,
    FOREIGN KEY (MemberNo) REFERENCES Member(MemberNo) ON DELETE CASCADE,
    INDEX idx_date (Date),
    INDEX idx_member (MemberNo),
    INDEX idx_member_date (MemberNo, Date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 10. RECOMMENDATIONS TABLE
-- Book recommendations for members
-- =====================================================
CREATE TABLE IF NOT EXISTS Recommendations (
    RecID INT PRIMARY KEY AUTO_INCREMENT,
    MemberNo INT NOT NULL,
    RecommendedBookID INT NOT NULL,
    Reason TEXT,
    Score DECIMAL(3,2),
    DateRecommended DATE,
    FOREIGN KEY (MemberNo) REFERENCES Member(MemberNo) ON DELETE CASCADE,
    FOREIGN KEY (RecommendedBookID) REFERENCES Books(CatNo) ON DELETE CASCADE,
    INDEX idx_member (MemberNo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 11. LIBRARY_EVENTS TABLE
-- Events and announcements
-- =====================================================
CREATE TABLE IF NOT EXISTS LibraryEvents (
    EventID INT PRIMARY KEY AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    Description TEXT,
    EventDate DATE NOT NULL,
    EventTime TIME,
    Location VARCHAR(100),
    Organizer VARCHAR(100),
    TargetAudience VARCHAR(50),
    Status VARCHAR(20) DEFAULT 'Upcoming',
    CreatedBy INT,
    CreatedDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CreatedBy) REFERENCES Admin(AdminID) ON DELETE SET NULL,
    INDEX idx_date (EventDate),
    INDEX idx_status (Status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 12. NOTIFICATIONS TABLE
-- System notifications for members
-- =====================================================
CREATE TABLE IF NOT EXISTS Notifications (
    NotificationID INT PRIMARY KEY AUTO_INCREMENT,
    MemberNo INT,
    Title VARCHAR(255) NOT NULL,
    Message TEXT NOT NULL,
    Type VARCHAR(50),
    IsRead TINYINT(1) DEFAULT 0,
    CreatedDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (MemberNo) REFERENCES Member(MemberNo) ON DELETE CASCADE,
    INDEX idx_member (MemberNo),
    INDEX idx_read (IsRead),
    INDEX idx_date (CreatedDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 13. ACTIVITY_LOG TABLE
-- Audit trail for all activities
-- =====================================================
CREATE TABLE IF NOT EXISTS ActivityLog (
    LogID INT PRIMARY KEY AUTO_INCREMENT,
    UserID INT,
    UserType VARCHAR(20),
    Action VARCHAR(100) NOT NULL,
    Details TEXT,
    IPAddress VARCHAR(45),
    Timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (UserID, UserType),
    INDEX idx_timestamp (Timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 14. FINE_PAYMENTS TABLE
-- Track fine payments
-- =====================================================
CREATE TABLE IF NOT EXISTS FinePayments (
    PaymentID INT PRIMARY KEY AUTO_INCREMENT,
    MemberNo INT NOT NULL,
    Amount DECIMAL(10,2) NOT NULL,
    PaymentDate DATE NOT NULL,
    PaymentMode VARCHAR(50),
    ReceiptNo VARCHAR(50),
    Remarks TEXT,
    CollectedBy INT,
    DateAdded TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (MemberNo) REFERENCES Member(MemberNo) ON DELETE RESTRICT,
    FOREIGN KEY (CollectedBy) REFERENCES Admin(AdminID) ON DELETE SET NULL,
    INDEX idx_member (MemberNo),
    INDEX idx_date (PaymentDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 15. BOOK_REQUESTS TABLE
-- Member book purchase requests
-- =====================================================
CREATE TABLE IF NOT EXISTS BookRequests (
    RequestID INT PRIMARY KEY AUTO_INCREMENT,
    MemberNo INT NOT NULL,
    Title VARCHAR(255) NOT NULL,
    Author VARCHAR(100),
    Publisher VARCHAR(100),
    ISBN VARCHAR(20),
    Reason TEXT,
    Status VARCHAR(20) DEFAULT 'Pending',
    RequestDate DATE,
    ResponseDate DATE,
    ResponseBy INT,
    Response TEXT,
    FOREIGN KEY (MemberNo) REFERENCES Member(MemberNo) ON DELETE CASCADE,
    FOREIGN KEY (ResponseBy) REFERENCES Admin(AdminID) ON DELETE SET NULL,
    INDEX idx_status (Status),
    INDEX idx_member (MemberNo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SAMPLE DATA INSERT
-- Insert sample data for testing
-- =====================================================

-- Sample Books (from data.md)
INSERT INTO Books (CatNo, Title, Author1, Edition, Year, Place, Publisher, Vol, Pages, ISBN, Subject, Language, DocumentType) VALUES
(10084, 'INFORMATION TECHNOLOGY FOR MANAGEMENT', 'LUCAS, H.C.', '7th Ed.', 2001, 'NEW DELHI', 'TATA McGRAW HILL', NULL, '730p.', '978-0070393325', 'Information Technology', 'English', 'BK'),
(10085, 'DATABASE SYSTEM CONCEPTS', 'SILBERSCHATZ, A.', '6th Ed.', 2010, 'NEW YORK', 'McGRAW HILL', NULL, '1376p.', '978-0073523323', 'Database Systems', 'English', 'BK'),
(10086, 'COMPUTER NETWORKS', 'TANENBAUM, A.S.', '5th Ed.', 2010, 'DELHI', 'PEARSON', NULL, '960p.', '978-0132126953', 'Computer Networks', 'English', 'BK');

-- Sample Holdings
INSERT INTO Holding (AccNo, CatNo, AccDate, ClassNo, BookNo, Status, Location, Section, Collection) VALUES
('BE8950', 10084, '2025-07-14', '1.642', 'LUC', 'Available', 'CMTC', 'T', 'C'),
('BE8951', 10084, '2025-07-14', '1.642', 'LUC', 'Available', 'CMTC', 'T', 'C'),
('BE8952', 10085, '2025-07-15', '1.643', 'SIL', 'Available', 'CMTC', 'T', 'C'),
('BE8953', 10086, '2025-07-15', '1.644', 'TAN', 'Available', 'CMTC', 'T', 'C');

-- Sample Members (from data.md)
INSERT INTO Member (MemberNo, MemberName, `Group`, Designation, Phone, Email, AdmissionDate, ClosingDate, Status) VALUES
(2511, 'Jayesh Mahesh Adurkar', 'Student', 'FE', '9146622724', 'manishaadurkar44@gmail.com', '2025-09-15', '2029-05-31', 'Active'),
(2512, 'Rahul Sharma', 'Student', 'SE', '9876543210', 'rahul.sharma@student.wiet.edu.in', '2024-08-01', '2028-05-31', 'Active'),
(3001, 'Dr. Priya Mehta', 'Faculty', 'Associate Professor', '9988776655', 'priya.mehta@wiet.edu.in', '2020-07-01', NULL, 'Active');

-- Sample Student details
INSERT INTO Student (MemberNo, Surname, MiddleName, FirstName, DOB, Gender, Branch, CourseName, PRN, Mobile, Email, Address, CardColour) VALUES
(2511, 'Adurkar', 'Mahesh', 'Jayesh', '2007-05-15', 'Male', 'Computer', 'Computer Engineering', 'C2511', '9146622724', 'manishaadurkar44@gmail.com', 'Room no 2743 lahu jamdare chawl behind bethal church gautum nagar ambernath west', 'Green'),
(2512, 'Sharma', 'Kumar', 'Rahul', '2006-03-20', 'Male', 'IT', 'Information Technology', 'C2512', '9876543210', 'rahul.sharma@student.wiet.edu.in', 'Flat 101, Sai Residency, Ambernath East', 'Blue');

-- Sample Faculty
INSERT INTO Faculty (MemberNo, EmployeeID, Department, Designation, JoinDate, Mobile, Email) VALUES
(3001, 'EMP3001', 'Computer Engineering', 'Associate Professor', '2020-07-01', '9988776655', 'priya.mehta@wiet.edu.in');

-- =====================================================
-- VIEWS FOR COMMON QUERIES
-- =====================================================

-- View: Available Books with copy count
CREATE OR REPLACE VIEW v_available_books AS
SELECT 
    b.CatNo,
    b.Title,
    b.Author1,
    b.Author2,
    b.Publisher,
    b.Year,
    b.Edition,
    b.ISBN,
    b.Subject,
    COUNT(h.HoldID) as TotalCopies,
    SUM(CASE WHEN h.Status = 'Available' THEN 1 ELSE 0 END) as AvailableCopies,
    SUM(CASE WHEN h.Status = 'Issued' THEN 1 ELSE 0 END) as IssuedCopies
FROM Books b
LEFT JOIN Holding h ON b.CatNo = h.CatNo
GROUP BY b.CatNo;

-- View: Active Circulations with details
CREATE OR REPLACE VIEW v_active_circulations AS
SELECT 
    c.CirculationID,
    c.MemberNo,
    m.MemberName,
    m.Phone,
    m.Email,
    c.AccNo,
    b.Title,
    b.Author1,
    c.IssueDate,
    c.DueDate,
    DATEDIFF(CURDATE(), c.DueDate) as DaysOverdue,
    CASE 
        WHEN CURDATE() > c.DueDate THEN DATEDIFF(CURDATE(), c.DueDate) * m.FinePerDay
        ELSE 0
    END as FineAmount
FROM Circulation c
JOIN Member m ON c.MemberNo = m.MemberNo
JOIN Holding h ON c.AccNo = h.AccNo
JOIN Books b ON h.CatNo = b.CatNo
WHERE c.Status = 'Active';

-- View: Member borrowing summary
CREATE OR REPLACE VIEW v_member_summary AS
SELECT 
    m.MemberNo,
    m.MemberName,
    m.`Group`,
    m.Email,
    m.Phone,
    m.Status,
    m.BooksIssued,
    COUNT(DISTINCT c.CirculationID) as TotalBorrowings,
    COUNT(DISTINCT r.ReturnID) as TotalReturns,
    COALESCE(SUM(r.FineAmount), 0) as TotalFines,
    COALESCE(SUM(r.FinePaid), 0) as FinesPaid
FROM Member m
LEFT JOIN Circulation c ON m.MemberNo = c.MemberNo
LEFT JOIN `Return` r ON m.MemberNo = r.MemberNo
GROUP BY m.MemberNo;

-- =====================================================
-- STORED PROCEDURES
-- =====================================================

DELIMITER //

-- Procedure to check overdue books and send notifications
CREATE PROCEDURE sp_check_overdue_books()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_member_no INT;
    DECLARE v_title VARCHAR(255);
    DECLARE v_due_date DATE;
    DECLARE v_days_overdue INT;
    
    DECLARE cur CURSOR FOR 
        SELECT c.MemberNo, b.Title, c.DueDate, DATEDIFF(CURDATE(), c.DueDate) as DaysOverdue
        FROM Circulation c
        JOIN Holding h ON c.AccNo = h.AccNo
        JOIN Books b ON h.CatNo = b.CatNo
        WHERE c.Status = 'Active' AND c.DueDate < CURDATE();
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN cur;
    
    read_loop: LOOP
        FETCH cur INTO v_member_no, v_title, v_due_date, v_days_overdue;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Insert notification if not already sent today
        INSERT INTO Notifications (MemberNo, Title, Message, Type)
        SELECT v_member_no, 'Overdue Book Reminder', 
               CONCAT('Book "', v_title, '" is overdue by ', v_days_overdue, ' days. Due date was ', v_due_date, '. Please return it to avoid further fines.'),
               'Overdue'
        WHERE NOT EXISTS (
            SELECT 1 FROM Notifications 
            WHERE MemberNo = v_member_no 
            AND Title = 'Overdue Book Reminder' 
            AND DATE(CreatedDate) = CURDATE()
        );
    END LOOP;
    
    CLOSE cur;
END //

DELIMITER ;

-- =====================================================
-- END OF SCHEMA
-- =====================================================

-- Display success message
SELECT 'Database schema created successfully!' as Status;
SELECT 'Total tables created: 15' as Info;
