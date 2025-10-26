-- Migration: Update FinePayments table structure
-- Description: Modify FinePayments to support circulation-based fine tracking
-- Date: 2025-10-26

-- Drop the existing table if it has the old structure
DROP TABLE IF EXISTS FinePayments;

-- Create new FinePayments table with updated structure
CREATE TABLE FinePayments (
    PaymentID INT PRIMARY KEY AUTO_INCREMENT,
    CirculationID INT NOT NULL,
    MemberNo INT NOT NULL,
    FineAmount DECIMAL(10,2) NOT NULL,
    PaidAmount DECIMAL(10,2) NOT NULL,
    PaymentDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    PaymentMethod VARCHAR(50),
    ReceiptNo VARCHAR(50) UNIQUE,
    CollectedBy INT,
    Remarks TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CirculationID) REFERENCES Circulation(CirculationID) ON DELETE CASCADE,
    FOREIGN KEY (MemberNo) REFERENCES Member(MemberNo) ON DELETE RESTRICT,
    FOREIGN KEY (CollectedBy) REFERENCES Admin(AdminID) ON DELETE SET NULL,
    INDEX idx_circulation (CirculationID),
    INDEX idx_member (MemberNo),
    INDEX idx_date (PaymentDate),
    INDEX idx_receipt (ReceiptNo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note: This will delete existing fine payment records
-- If you need to preserve data, export before running this migration
