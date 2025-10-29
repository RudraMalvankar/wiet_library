-- =====================================================
-- Book Reservations System - Migration
-- =====================================================
-- Created: 2025-10-29
-- Purpose: Add book reservation/hold functionality
-- =====================================================

USE wiet_library;

-- =====================================================
-- 1. CREATE BOOK RESERVATIONS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS BookReservations (
    ReservationID INT PRIMARY KEY AUTO_INCREMENT,
    MemberNo INT NOT NULL,
    CatNo INT NOT NULL,
    RequestDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    ExpiryDate DATETIME NULL,
    Status VARCHAR(20) DEFAULT 'Pending',
    -- Status: Pending, Ready, Completed, Cancelled, Expired
    Priority INT DEFAULT 0,
    NotifiedAt DATETIME NULL,
    FulfilledAt DATETIME NULL,
    CancelledAt DATETIME NULL,
    CancellationReason TEXT NULL,
    Notes TEXT NULL,
    
    INDEX idx_member (MemberNo),
    INDEX idx_book (CatNo),
    INDEX idx_status (Status),
    INDEX idx_request_date (RequestDate),
    INDEX idx_expiry (ExpiryDate),
    
    FOREIGN KEY (MemberNo) REFERENCES Member(MemberNo) ON DELETE CASCADE,
    FOREIGN KEY (CatNo) REFERENCES Books(CatNo) ON DELETE CASCADE,
    
    UNIQUE KEY unique_active_reservation (MemberNo, CatNo, Status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. CREATE VIEW FOR RESERVATION QUEUE
-- =====================================================
CREATE OR REPLACE VIEW ReservationQueue AS
SELECT 
    br.ReservationID,
    br.MemberNo,
    m.MemberName,
    m.Email,
    m.Phone,
    br.CatNo,
    b.Title,
    b.Author1,
    b.ISBN,
    br.RequestDate,
    br.ExpiryDate,
    br.Status,
    br.Priority,
    br.NotifiedAt,
    DATEDIFF(NOW(), br.RequestDate) AS DaysWaiting,
    (SELECT COUNT(*) 
     FROM BookReservations br2 
     WHERE br2.CatNo = br.CatNo 
     AND br2.Status = 'Pending' 
     AND br2.RequestDate < br.RequestDate) + 1 AS QueuePosition,
    (SELECT COUNT(*) 
     FROM Holding h 
     WHERE h.CatNo = br.CatNo 
     AND h.Status = 'Available') AS AvailableCopies
FROM BookReservations br
INNER JOIN Member m ON br.MemberNo = m.MemberNo
INNER JOIN Books b ON br.CatNo = b.CatNo
WHERE br.Status IN ('Pending', 'Ready')
ORDER BY br.CatNo, br.Priority DESC, br.RequestDate ASC;

-- =====================================================
-- 3. CREATE VIEW FOR MEMBER RESERVATION SUMMARY
-- =====================================================
CREATE OR REPLACE VIEW MemberReservationSummary AS
SELECT 
    m.MemberNo,
    m.MemberName,
    COUNT(CASE WHEN br.Status = 'Pending' THEN 1 END) AS PendingReservations,
    COUNT(CASE WHEN br.Status = 'Ready' THEN 1 END) AS ReadyReservations,
    COUNT(CASE WHEN br.Status = 'Completed' THEN 1 END) AS CompletedReservations,
    COUNT(CASE WHEN br.Status = 'Cancelled' THEN 1 END) AS CancelledReservations,
    MAX(br.RequestDate) AS LastReservationDate,
    AVG(DATEDIFF(br.FulfilledAt, br.RequestDate)) AS AvgWaitTimeDays
FROM Member m
LEFT JOIN BookReservations br ON m.MemberNo = br.MemberNo
GROUP BY m.MemberNo, m.MemberName;

-- =====================================================
-- 4. CREATE STORED PROCEDURE TO AUTO-EXPIRE RESERVATIONS
-- =====================================================
DELIMITER $$

DROP PROCEDURE IF EXISTS ExpireOldReservations$$

CREATE PROCEDURE ExpireOldReservations()
BEGIN
    -- Expire reservations that have been in 'Ready' status for more than 3 days
    UPDATE BookReservations
    SET Status = 'Expired',
        Notes = CONCAT(COALESCE(Notes, ''), ' | Auto-expired after 3 days in Ready status')
    WHERE Status = 'Ready'
    AND ExpiryDate < NOW();
    
    SELECT ROW_COUNT() AS ExpiredCount;
END$$

DELIMITER ;

-- =====================================================
-- 5. CREATE STORED PROCEDURE TO AUTO-NOTIFY NEXT IN QUEUE
-- =====================================================
DELIMITER $$

DROP PROCEDURE IF EXISTS NotifyNextReservation$$

CREATE PROCEDURE NotifyNextReservation(IN bookCatNo INT)
BEGIN
    DECLARE nextReservationID INT;
    
    -- Find next pending reservation for this book
    SELECT ReservationID INTO nextReservationID
    FROM BookReservations
    WHERE CatNo = bookCatNo
    AND Status = 'Pending'
    AND ExpiryDate IS NULL
    ORDER BY Priority DESC, RequestDate ASC
    LIMIT 1;
    
    -- If found, mark as Ready and set expiry
    IF nextReservationID IS NOT NULL THEN
        UPDATE BookReservations
        SET Status = 'Ready',
            NotifiedAt = NOW(),
            ExpiryDate = DATE_ADD(NOW(), INTERVAL 3 DAY),
            Notes = CONCAT(COALESCE(Notes, ''), ' | Notified on ', NOW())
        WHERE ReservationID = nextReservationID;
        
        SELECT 
            br.ReservationID,
            br.MemberNo,
            m.MemberName,
            m.Email,
            m.Phone,
            br.CatNo,
            b.Title,
            br.ExpiryDate
        FROM BookReservations br
        INNER JOIN Member m ON br.MemberNo = m.MemberNo
        INNER JOIN Books b ON br.CatNo = b.CatNo
        WHERE br.ReservationID = nextReservationID;
    END IF;
END$$

DELIMITER ;

-- =====================================================
-- 6. ADD TRIGGER TO AUTO-NOTIFY ON BOOK RETURN
-- =====================================================
DELIMITER $$

DROP TRIGGER IF EXISTS after_book_return$$

CREATE TRIGGER after_book_return
AFTER UPDATE ON Holding
FOR EACH ROW
BEGIN
    -- When book status changes from Issued to Available
    IF OLD.Status = 'Issued' AND NEW.Status = 'Available' THEN
        -- Call stored procedure to notify next reservation
        CALL NotifyNextReservation(NEW.CatNo);
    END IF;
END$$

DELIMITER ;

-- =====================================================
-- VERIFICATION QUERIES
-- =====================================================

-- Check if BookReservations table exists
SELECT 
    TABLE_NAME,
    ENGINE,
    TABLE_ROWS,
    CREATE_TIME
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = 'wiet_library'
AND TABLE_NAME = 'BookReservations';

-- Check views created
SELECT COUNT(*) as ViewCount
FROM INFORMATION_SCHEMA.VIEWS
WHERE TABLE_SCHEMA = 'wiet_library'
AND TABLE_NAME IN ('ReservationQueue', 'MemberReservationSummary');

-- Check stored procedures
SELECT ROUTINE_NAME, ROUTINE_TYPE
FROM INFORMATION_SCHEMA.ROUTINES
WHERE ROUTINE_SCHEMA = 'wiet_library'
AND ROUTINE_NAME IN ('ExpireOldReservations', 'NotifyNextReservation');

-- Check trigger
SELECT TRIGGER_NAME, EVENT_MANIPULATION, EVENT_OBJECT_TABLE
FROM INFORMATION_SCHEMA.TRIGGERS
WHERE TRIGGER_SCHEMA = 'wiet_library'
AND TRIGGER_NAME = 'after_book_return';

SELECT '✅ Book Reservation Migration Complete!' AS Status;
