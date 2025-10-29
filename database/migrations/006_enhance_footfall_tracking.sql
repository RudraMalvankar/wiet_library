-- Enhance Footfall table with additional tracking fields
-- Run this migration to add new fields

ALTER TABLE Footfall 
ADD COLUMN IF NOT EXISTS EntryTime DATETIME DEFAULT NULL COMMENT 'Entry timestamp with date and time',
ADD COLUMN IF NOT EXISTS ExitTime DATETIME DEFAULT NULL COMMENT 'Exit timestamp with date and time',
ADD COLUMN IF NOT EXISTS Purpose VARCHAR(100) DEFAULT 'Library Visit' COMMENT 'Purpose of visit',
ADD COLUMN IF NOT EXISTS Status VARCHAR(20) DEFAULT 'Active' COMMENT 'Active or Completed',
ADD COLUMN IF NOT EXISTS EntryMethod VARCHAR(50) DEFAULT 'Manual' COMMENT 'Manual, QR Scan, Card Scan',
ADD COLUMN IF NOT EXISTS WorkstationUsed VARCHAR(50) DEFAULT NULL COMMENT 'Computer/workstation if applicable';

-- Update existing records to have EntryTime and ExitTime based on Date, TimeIn, TimeOut
UPDATE Footfall 
SET EntryTime = TIMESTAMP(Date, TimeIn),
    ExitTime = CASE WHEN TimeOut IS NOT NULL THEN TIMESTAMP(Date, TimeOut) ELSE NULL END,
    Status = CASE WHEN TimeOut IS NULL THEN 'Active' ELSE 'Completed' END
WHERE EntryTime IS NULL;

-- Create index for faster queries
CREATE INDEX IF NOT EXISTS idx_entry_time ON Footfall(EntryTime);
CREATE INDEX IF NOT EXISTS idx_status ON Footfall(Status);
CREATE INDEX IF NOT EXISTS idx_entry_method ON Footfall(EntryMethod);

-- Create view for daily statistics
CREATE OR REPLACE VIEW FootfallDailyStats AS
SELECT 
    DATE(EntryTime) as VisitDate,
    COUNT(*) as TotalVisits,
    COUNT(DISTINCT MemberNo) as UniqueVisitors,
    AVG(TIMESTAMPDIFF(MINUTE, EntryTime, ExitTime)) as AvgDurationMinutes,
    SUM(CASE WHEN Status = 'Active' THEN 1 ELSE 0 END) as ActiveVisitors,
    SUM(CASE WHEN EntryMethod = 'QR Scan' THEN 1 ELSE 0 END) as QRScans,
    SUM(CASE WHEN EntryMethod = 'Manual' THEN 1 ELSE 0 END) as ManualEntries
FROM Footfall
GROUP BY DATE(EntryTime);

-- Create view for hourly distribution
CREATE OR REPLACE VIEW FootfallHourlyStats AS
SELECT 
    HOUR(EntryTime) as HourOfDay,
    COUNT(*) as VisitCount,
    AVG(TIMESTAMPDIFF(MINUTE, EntryTime, ExitTime)) as AvgDurationMinutes
FROM Footfall
WHERE EntryTime IS NOT NULL
GROUP BY HOUR(EntryTime)
ORDER BY HourOfDay;

-- Create view for member visit summary
CREATE OR REPLACE VIEW MemberFootfallSummary AS
SELECT 
    f.MemberNo,
    m.MemberName,
    s.Branch,
    s.CourseName,
    COUNT(*) as TotalVisits,
    AVG(TIMESTAMPDIFF(MINUTE, f.EntryTime, f.ExitTime)) as AvgDurationMinutes,
    MAX(f.EntryTime) as LastVisit,
    SUM(CASE WHEN DATE(f.EntryTime) = CURDATE() THEN 1 ELSE 0 END) as VisitsToday,
    SUM(CASE WHEN YEARWEEK(f.EntryTime) = YEARWEEK(CURDATE()) THEN 1 ELSE 0 END) as VisitsThisWeek,
    SUM(CASE WHEN MONTH(f.EntryTime) = MONTH(CURDATE()) AND YEAR(f.EntryTime) = YEAR(CURDATE()) THEN 1 ELSE 0 END) as VisitsThisMonth
FROM Footfall f
INNER JOIN Member m ON f.MemberNo = m.MemberNo
LEFT JOIN Student s ON m.MemberNo = s.MemberNo
GROUP BY f.MemberNo, m.MemberName, s.Branch, s.CourseName;
