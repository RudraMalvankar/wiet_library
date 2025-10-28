-- ========================================
-- FIX: Resolve LibraryEvents vs library_events Table Conflict
-- ========================================
-- 
-- PROBLEM: Two different event tables exist:
--   1. LibraryEvents (old, simple schema) - used by dashboard
--   2. library_events (new, comprehensive schema) - used by events management
--
-- SOLUTION: Consolidate to library_events (comprehensive schema)
--
-- ========================================

-- STEP 1: Check if LibraryEvents has any data
SELECT 'Checking LibraryEvents data...' AS Status;
SELECT COUNT(*) AS 'Records in LibraryEvents' FROM LibraryEvents;
SELECT * FROM LibraryEvents LIMIT 5;

-- STEP 2: Check library_events structure
SELECT 'Checking library_events data...' AS Status;
SELECT COUNT(*) AS 'Records in library_events' FROM library_events;
SELECT * FROM library_events LIMIT 5;

-- ========================================
-- OPTION A: RECOMMENDED - Migrate & Drop Old Table
-- ========================================

-- STEP 3A: Backup LibraryEvents data (if any exists)
-- Uncomment if you want to create a backup table first
-- CREATE TABLE LibraryEvents_backup AS SELECT * FROM LibraryEvents;

-- STEP 4A: Migrate data from LibraryEvents to library_events
-- (Only run if LibraryEvents has data)
INSERT INTO library_events 
(
    EventTitle,
    EventType,
    Description,
    StartDate,
    EndDate,
    StartTime,
    EndTime,
    Venue,
    Status,
    OrganizedBy,
    CreatedBy,
    CreatedDate
)
SELECT 
    Title AS EventTitle,
    'General' AS EventType,  -- Default type since old table doesn't have it
    Description,
    EventDate AS StartDate,
    EventDate AS EndDate,  -- Same date for start and end
    EventTime AS StartTime,
    ADDTIME(EventTime, '02:00:00') AS EndTime,  -- Assume 2-hour duration
    Location AS Venue,
    Status,
    Organizer AS OrganizedBy,
    CreatedBy,
    CreatedDate
FROM LibraryEvents
WHERE EventID NOT IN (
    -- Avoid duplicates if this script runs twice
    SELECT EventID FROM library_events WHERE EventID IN (SELECT EventID FROM LibraryEvents)
);

-- STEP 5A: Verify migration
SELECT 'Verifying migration...' AS Status;
SELECT COUNT(*) AS 'Total records after migration' FROM library_events;

-- STEP 6A: Drop old table (CAREFUL - only after verifying data)
-- Uncomment when ready to proceed
-- DROP TABLE LibraryEvents;

-- ========================================
-- STEP 7: Update dashboard.php to use library_events
-- ========================================
-- 
-- Manual file edits needed:
-- 
-- FILE: admin/dashboard.php (line ~27)
-- CHANGE FROM:
--   $stmt = $pdo->query("SELECT COUNT(*) as count FROM LibraryEvents WHERE MONTH(EventDate) = MONTH(CURDATE())");
-- 
-- CHANGE TO:
--   $stmt = $pdo->query("SELECT COUNT(*) as count FROM library_events WHERE MONTH(StartDate) = MONTH(CURDATE())");
--
-- FILE: admin/api/dashboard.php (line ~137)
-- CHANGE FROM:
--   $stmt = $pdo->query("SELECT COUNT(*) as count FROM LibraryEvents WHERE MONTH(EventDate) = MONTH(CURDATE())");
-- 
-- CHANGE TO:
--   $stmt = $pdo->query("SELECT COUNT(*) as count FROM library_events WHERE MONTH(StartDate) = MONTH(CURDATE())");
--
-- ========================================

-- ========================================
-- OPTION B: ALTERNATIVE - Keep LibraryEvents (Not Recommended)
-- ========================================
-- 
-- If you prefer to keep the old table, you need to:
-- 1. Migrate data from library_events to LibraryEvents (complex mapping)
-- 2. Update 3 files to use LibraryEvents:
--    - admin/library-events.php
--    - admin/api/events.php
--    - admin/api/event_registrations.php
-- 3. Drop library_events table
--
-- This is NOT recommended because:
-- - Loses detailed event information (contact details, capacity, registration, etc.)
-- - More file changes required (3 files vs 2 files)
-- - Less future-proof
--
-- ========================================

-- VERIFICATION QUERIES (Run after fix is complete)
-- ========================================

-- Check no LibraryEvents table exists
SELECT 'Checking tables...' AS Status;
SHOW TABLES LIKE '%event%';

-- Check library_events has all data
SELECT 
    COUNT(*) AS 'Total Events',
    SUM(CASE WHEN Status = 'Upcoming' THEN 1 ELSE 0 END) AS 'Upcoming',
    SUM(CASE WHEN Status = 'Active' THEN 1 ELSE 0 END) AS 'Active',
    SUM(CASE WHEN Status = 'Completed' THEN 1 ELSE 0 END) AS 'Completed'
FROM library_events;

-- Check event_registrations still work
SELECT 
    COUNT(*) AS 'Total Registrations'
FROM event_registrations er
JOIN library_events e ON er.EventID = e.EventID;

SELECT 'Fix complete! Now update the 2 PHP files manually (see comments above).' AS Status;
