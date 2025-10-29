-- =====================================================
-- Password Reset System - Migration
-- =====================================================
-- Created: 2025-10-29
-- Purpose: Add password reset functionality for students
-- =====================================================

USE wiet_library;

-- =====================================================
-- 1. CREATE PASSWORD RESETS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS PasswordResets (
    ResetID INT PRIMARY KEY AUTO_INCREMENT,
    MemberNo INT NOT NULL,
    Email VARCHAR(255) NOT NULL,
    ResetToken VARCHAR(255) NOT NULL,
    OTP VARCHAR(6) NOT NULL,
    ExpiresAt DATETIME NOT NULL,
    IsUsed TINYINT(1) DEFAULT 0,
    UsedAt DATETIME NULL,
    CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    IPAddress VARCHAR(45) NULL,
    
    INDEX idx_token (ResetToken),
    INDEX idx_email (Email),
    INDEX idx_expires (ExpiresAt),
    INDEX idx_member (MemberNo),
    
    FOREIGN KEY (MemberNo) REFERENCES Member(MemberNo) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. ADD PASSWORD FIELD TO STUDENT TABLE (if not exists)
-- =====================================================
-- Check if Password column exists in Student table
SET @dbname = DATABASE();
SET @tablename = 'Student';
SET @columnname = 'Password';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(255) DEFAULT NULL AFTER QRCode')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- =====================================================
-- 3. UPDATE EXISTING STUDENTS WITH DEFAULT PASSWORD
-- =====================================================
-- Set default password for students who don't have one
-- Password: 123456 (hashed with bcrypt)
UPDATE Student 
SET Password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE Password IS NULL OR Password = '';

-- =====================================================
-- 4. CREATE VIEW FOR PASSWORD RESET STATISTICS
-- =====================================================
CREATE OR REPLACE VIEW PasswordResetStats AS
SELECT 
    DATE(CreatedAt) AS ResetDate,
    COUNT(*) AS TotalRequests,
    SUM(CASE WHEN IsUsed = 1 THEN 1 ELSE 0 END) AS CompletedResets,
    SUM(CASE WHEN IsUsed = 0 AND ExpiresAt < NOW() THEN 1 ELSE 0 END) AS ExpiredTokens,
    SUM(CASE WHEN IsUsed = 0 AND ExpiresAt >= NOW() THEN 1 ELSE 0 END) AS PendingResets
FROM PasswordResets
GROUP BY DATE(CreatedAt)
ORDER BY ResetDate DESC;

-- =====================================================
-- 5. CREATE STORED PROCEDURE TO CLEANUP EXPIRED TOKENS
-- =====================================================
DELIMITER $$

DROP PROCEDURE IF EXISTS CleanupExpiredResetTokens$$

CREATE PROCEDURE CleanupExpiredResetTokens()
BEGIN
    -- Delete expired tokens older than 7 days
    DELETE FROM PasswordResets
    WHERE ExpiresAt < DATE_SUB(NOW(), INTERVAL 7 DAY)
    AND IsUsed = 0;
    
    SELECT ROW_COUNT() AS DeletedTokens;
END$$

DELIMITER ;

-- =====================================================
-- VERIFICATION QUERIES
-- =====================================================

-- Check if PasswordResets table exists
SELECT 
    TABLE_NAME,
    ENGINE,
    TABLE_ROWS,
    CREATE_TIME
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = 'wiet_library'
AND TABLE_NAME = 'PasswordResets';

-- Check if Password column exists in Student table
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'wiet_library'
AND TABLE_NAME = 'Student'
AND COLUMN_NAME = 'Password';

-- Check view created
SELECT COUNT(*) as ViewExists
FROM INFORMATION_SCHEMA.VIEWS
WHERE TABLE_SCHEMA = 'wiet_library'
AND TABLE_NAME = 'PasswordResetStats';

-- Check stored procedure
SELECT ROUTINE_NAME, ROUTINE_TYPE
FROM INFORMATION_SCHEMA.ROUTINES
WHERE ROUTINE_SCHEMA = 'wiet_library'
AND ROUTINE_NAME = 'CleanupExpiredResetTokens';

SELECT '✅ Password Reset Migration Complete!' AS Status;
