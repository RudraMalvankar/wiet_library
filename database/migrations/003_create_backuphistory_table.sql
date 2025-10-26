-- Migration: Add BackupHistory table for tracking backups
-- Date: 2024
-- Description: Creates BackupHistory table to track database backup and restore operations

CREATE TABLE IF NOT EXISTS BackupHistory (
    BackupID INT AUTO_INCREMENT PRIMARY KEY,
    FileName VARCHAR(255) NOT NULL,
    FileSize BIGINT DEFAULT 0,
    BackupType ENUM('full', 'structure', 'data', 'custom', 'restore') NOT NULL,
    Description TEXT,
    CreatedBy INT,
    CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CreatedBy) REFERENCES Admin(AdminID) ON DELETE SET NULL,
    INDEX idx_created_at (CreatedAt),
    INDEX idx_backup_type (BackupType)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Settings table if it doesn't exist
CREATE TABLE IF NOT EXISTS Settings (
    SettingID INT AUTO_INCREMENT PRIMARY KEY,
    SettingKey VARCHAR(100) UNIQUE NOT NULL,
    SettingValue TEXT,
    Description VARCHAR(255),
    UpdatedBy INT,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (UpdatedBy) REFERENCES Admin(AdminID) ON DELETE SET NULL,
    INDEX idx_key (SettingKey)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add auto_backup_settings to Settings table if it doesn't exist
INSERT IGNORE INTO Settings (SettingKey, SettingValue, Description) 
VALUES (
    'auto_backup_settings', 
    '{"enabled":"0","frequency":"daily","time":"02:00","retention":30,"email":""}',
    'Auto backup configuration settings'
);
