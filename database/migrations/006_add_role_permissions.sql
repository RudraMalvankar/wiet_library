-- ========================================
-- Migration 006: Add Role-Based Permissions System
-- ========================================

-- Add IsSuperAdmin column to Admin table
ALTER TABLE Admin 
ADD COLUMN IsSuperAdmin TINYINT(1) DEFAULT 0 AFTER Role;

-- Create AdminRoles table for role definitions
CREATE TABLE IF NOT EXISTS AdminRoles (
    RoleID INT AUTO_INCREMENT PRIMARY KEY,
    RoleName VARCHAR(50) NOT NULL UNIQUE,
    Description TEXT,
    CreatedDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default roles
INSERT INTO AdminRoles (RoleName, Description) VALUES
('Super Admin', 'Full system access with all permissions'),
('Librarian', 'Library operations: books, circulation, members'),
('Assistant', 'Limited access: view only, issue/return books'),
('Cataloger', 'Book management: add, edit, categorize books'),
('Accountant', 'Financial operations: fines, reports');

-- Create AdminPermissions table for granular permissions
CREATE TABLE IF NOT EXISTS AdminPermissions (
    PermissionID INT AUTO_INCREMENT PRIMARY KEY,
    PermissionName VARCHAR(100) NOT NULL UNIQUE,
    PermissionKey VARCHAR(50) NOT NULL UNIQUE,
    Description TEXT,
    Category VARCHAR(50),
    CreatedDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert page-level permissions
INSERT INTO AdminPermissions (PermissionName, PermissionKey, Description, Category) VALUES
-- Dashboard
('View Dashboard', 'view_dashboard', 'Access main dashboard', 'Dashboard'),

-- Books Management
('View Books', 'view_books', 'View books list', 'Books'),
('Add Books', 'add_books', 'Add new books', 'Books'),
('Edit Books', 'edit_books', 'Edit book details', 'Books'),
('Delete Books', 'delete_books', 'Delete books', 'Books'),
('Export Books', 'export_books', 'Export books to PDF/CSV', 'Books'),

-- Circulation
('View Circulation', 'view_circulation', 'View circulation records', 'Circulation'),
('Issue Books', 'issue_books', 'Issue books to members', 'Circulation'),
('Return Books', 'return_books', 'Process book returns', 'Circulation'),
('Renew Books', 'renew_books', 'Renew book issues', 'Circulation'),

-- Members
('View Members', 'view_members', 'View members list', 'Members'),
('Add Members', 'add_members', 'Add new members', 'Members'),
('Edit Members', 'edit_members', 'Edit member details', 'Members'),
('Delete Members', 'delete_members', 'Delete members', 'Members'),

-- Students
('View Students', 'view_students', 'View students list', 'Students'),
('Manage Students', 'manage_students', 'Add/Edit/Delete students', 'Students'),
('Import Students', 'import_students', 'Bulk import students', 'Students'),

-- Fines
('View Fines', 'view_fines', 'View fine records', 'Fines'),
('Collect Fines', 'collect_fines', 'Collect fine payments', 'Fines'),
('Waive Fines', 'waive_fines', 'Waive fines', 'Fines'),

-- Reports
('View Reports', 'view_reports', 'Access reports module', 'Reports'),
('Generate Reports', 'generate_reports', 'Generate custom reports', 'Reports'),
('Export Reports', 'export_reports', 'Export reports to PDF/CSV', 'Reports'),

-- Events
('View Events', 'view_events', 'View library events', 'Events'),
('Manage Events', 'manage_events', 'Create/Edit/Delete events', 'Events'),

-- QR Generator
('Generate QR', 'generate_qr', 'Generate QR codes for books', 'QR'),

-- Backup & Restore
('View Backups', 'view_backups', 'View backup history', 'Backup'),
('Create Backup', 'create_backup', 'Create database backups', 'Backup'),
('Restore Backup', 'restore_backup', 'Restore database', 'Backup'),

-- Inventory
('View Inventory', 'view_inventory', 'View inventory', 'Inventory'),
('Stock Verification', 'stock_verification', 'Perform stock verification', 'Inventory'),

-- Analytics
('View Analytics', 'view_analytics', 'Access analytics dashboard', 'Analytics'),

-- Settings
('View Settings', 'view_settings', 'View system settings', 'Settings'),
('Manage Settings', 'manage_settings', 'Modify system settings', 'Settings'),

-- Admin Management
('View Admins', 'view_admins', 'View admin users', 'Admin Management'),
('Add Admins', 'add_admins', 'Add new admin users', 'Admin Management'),
('Edit Admins', 'edit_admins', 'Edit admin details', 'Admin Management'),
('Delete Admins', 'delete_admins', 'Delete admin users', 'Admin Management'),
('Assign Roles', 'assign_roles', 'Assign roles to admins', 'Admin Management'),

-- Bulk Import
('Bulk Import', 'bulk_import', 'Import data from CSV/Excel', 'Import'),

-- Notifications
('View Notifications', 'view_notifications', 'View notifications', 'Notifications'),
('Send Notifications', 'send_notifications', 'Send notifications', 'Notifications');

-- Create RolePermissions mapping table
CREATE TABLE IF NOT EXISTS RolePermissions (
    RolePermissionID INT AUTO_INCREMENT PRIMARY KEY,
    RoleID INT NOT NULL,
    PermissionID INT NOT NULL,
    CreatedDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (RoleID) REFERENCES AdminRoles(RoleID) ON DELETE CASCADE,
    FOREIGN KEY (PermissionID) REFERENCES AdminPermissions(PermissionID) ON DELETE CASCADE,
    UNIQUE KEY unique_role_permission (RoleID, PermissionID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Assign permissions to Super Admin (Role ID: 1) - ALL permissions
INSERT INTO RolePermissions (RoleID, PermissionID)
SELECT 1, PermissionID FROM AdminPermissions;

-- Assign permissions to Librarian (Role ID: 2)
INSERT INTO RolePermissions (RoleID, PermissionID)
SELECT 2, PermissionID FROM AdminPermissions 
WHERE PermissionKey IN (
    'view_dashboard',
    'view_books', 'add_books', 'edit_books', 'export_books',
    'view_circulation', 'issue_books', 'return_books', 'renew_books',
    'view_members', 'add_members', 'edit_members',
    'view_students', 'manage_students',
    'view_fines', 'collect_fines',
    'view_reports', 'generate_reports', 'export_reports',
    'view_events', 'manage_events',
    'generate_qr',
    'view_inventory', 'stock_verification',
    'view_analytics',
    'view_notifications'
);

-- Assign permissions to Assistant (Role ID: 3)
INSERT INTO RolePermissions (RoleID, PermissionID)
SELECT 3, PermissionID FROM AdminPermissions 
WHERE PermissionKey IN (
    'view_dashboard',
    'view_books',
    'view_circulation', 'issue_books', 'return_books',
    'view_members',
    'view_fines', 'collect_fines',
    'view_reports',
    'view_notifications'
);

-- Assign permissions to Cataloger (Role ID: 4)
INSERT INTO RolePermissions (RoleID, PermissionID)
SELECT 4, PermissionID FROM AdminPermissions 
WHERE PermissionKey IN (
    'view_dashboard',
    'view_books', 'add_books', 'edit_books', 'export_books',
    'view_inventory', 'stock_verification',
    'generate_qr',
    'bulk_import',
    'view_reports',
    'view_notifications'
);

-- Assign permissions to Accountant (Role ID: 5)
INSERT INTO RolePermissions (RoleID, PermissionID)
SELECT 5, PermissionID FROM AdminPermissions 
WHERE PermissionKey IN (
    'view_dashboard',
    'view_books',
    'view_circulation',
    'view_members',
    'view_fines', 'collect_fines', 'waive_fines',
    'view_reports', 'generate_reports', 'export_reports',
    'view_analytics',
    'view_notifications'
);

-- Update existing Super Admin
UPDATE Admin 
SET IsSuperAdmin = 1, 
    Role = 'Super Admin' 
WHERE AdminID = 1;

-- Create ActivityLog table if not exists (for tracking admin actions)
CREATE TABLE IF NOT EXISTS ActivityLog (
    LogID INT AUTO_INCREMENT PRIMARY KEY,
    AdminID INT,
    Action VARCHAR(255) NOT NULL,
    Description TEXT,
    IPAddress VARCHAR(45),
    UserAgent TEXT,
    CreatedDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (AdminID) REFERENCES Admin(AdminID) ON DELETE SET NULL,
    INDEX idx_admin (AdminID),
    INDEX idx_date (CreatedDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Verification queries
SELECT 'Migration 006 completed successfully!' AS Status;

SELECT 
    'Admin Roles Created' AS Status,
    COUNT(*) AS Count 
FROM AdminRoles;

SELECT 
    'Permissions Created' AS Status,
    COUNT(*) AS Count 
FROM AdminPermissions;

SELECT 
    'Role-Permission Mappings' AS Status,
    COUNT(*) AS Count 
FROM RolePermissions;
