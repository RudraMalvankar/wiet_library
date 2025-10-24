-- Migration: add QrCodeImg blob column to Holding
-- Run this against the wiet_library database (use mysql CLI or phpMyAdmin)

ALTER TABLE Holding
    ADD COLUMN IF NOT EXISTS QrCodeImg LONGBLOB NULL AFTER QRCode;
