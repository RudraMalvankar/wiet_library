-- Migration 010: Add BillScan columns to Books table for scanned bill images
-- Date: 2026-07-07

ALTER TABLE Books ADD COLUMN IF NOT EXISTS BillScan LONGBLOB DEFAULT NULL AFTER BillDate;
ALTER TABLE Books ADD COLUMN IF NOT EXISTS BillScanMime VARCHAR(50) DEFAULT NULL AFTER BillScan;
