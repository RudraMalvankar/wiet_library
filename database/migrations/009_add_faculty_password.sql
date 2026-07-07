-- Migration 009: Add Password column to Faculty table for faculty/staff portal login
-- Date: 2026-07-07

ALTER TABLE Faculty ADD COLUMN IF NOT EXISTS Password VARCHAR(255) DEFAULT NULL AFTER Email;

-- Set default password (123456 hashed) for existing faculty/staff
UPDATE Faculty SET Password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE Password IS NULL;
