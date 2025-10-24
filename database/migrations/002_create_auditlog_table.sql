-- Migration: Create AuditLog table for fine-grained auditing
-- Run with your MySQL client in the wiet_library database

CREATE TABLE IF NOT EXISTS AuditLog (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NULL,
    entity_id VARCHAR(100) NULL,
    metadata LONGTEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_action_created (action, created_at),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_admin_created (admin_id, created_at),
    CONSTRAINT fk_auditlog_admin FOREIGN KEY (admin_id) REFERENCES Admin(AdminID) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
