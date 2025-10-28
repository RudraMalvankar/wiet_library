-- ========================================
-- Add Sample Admin Users with Hashed Passwords
-- ========================================

-- Note: All passwords are: admin@123

-- Update existing Super Admin with hashed password
UPDATE Admin 
SET 
    Name = 'Super Administrator',
    Email = 'superadmin@wiet.edu.in',
    Password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- Password: admin@123
    Phone = '9876543210',
    Role = 'Super Admin',
    IsSuperAdmin = 1,
    Status = 'Active'
WHERE AdminID = 1;

-- Insert Librarian
INSERT INTO Admin (Name, Email, Phone, Role, Password, IsSuperAdmin, Status, CreatedDate)
VALUES (
    'John Librarian',
    'librarian@wiet.edu.in',
    '9876543211',
    'Librarian',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- Password: admin@123
    0,
    'Active',
    CURRENT_TIMESTAMP
);

-- Insert Assistant
INSERT INTO Admin (Name, Email, Phone, Role, Password, IsSuperAdmin, Status, CreatedDate)
VALUES (
    'Mary Assistant',
    'assistant@wiet.edu.in',
    '9876543212',
    'Assistant',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- Password: admin@123
    0,
    'Active',
    CURRENT_TIMESTAMP
);

-- Insert Cataloger
INSERT INTO Admin (Name, Email, Phone, Role, Password, IsSuperAdmin, Status, CreatedDate)
VALUES (
    'David Cataloger',
    'cataloger@wiet.edu.in',
    '9876543213',
    'Cataloger',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- Password: admin@123
    0,
    'Active',
    CURRENT_TIMESTAMP
);

-- Insert Accountant
INSERT INTO Admin (Name, Email, Phone, Role, Password, IsSuperAdmin, Status, CreatedDate)
VALUES (
    'Sarah Accountant',
    'accountant@wiet.edu.in',
    '9876543214',
    'Accountant',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- Password: admin@123
    0,
    'Active',
    CURRENT_TIMESTAMP
);

-- Display all admin users
SELECT 
    AdminID,
    Name,
    Email,
    Phone,
    Role,
    IsSuperAdmin,
    Status,
    CreatedDate
FROM Admin
ORDER BY AdminID;

-- Display credentials information
SELECT '========================================' AS '';
SELECT 'ADMIN LOGIN CREDENTIALS' AS '';
SELECT '========================================' AS '';
SELECT 'All users have password: admin@123' AS '';
SELECT '========================================' AS '';
SELECT CONCAT('Super Admin: ', Email) AS 'Credentials' FROM Admin WHERE Role = 'Super Admin';
SELECT CONCAT('Librarian: ', Email) AS 'Credentials' FROM Admin WHERE Role = 'Librarian';
SELECT CONCAT('Assistant: ', Email) AS 'Credentials' FROM Admin WHERE Role = 'Assistant';
SELECT CONCAT('Cataloger: ', Email) AS 'Credentials' FROM Admin WHERE Role = 'Cataloger';
SELECT CONCAT('Accountant: ', Email) AS 'Credentials' FROM Admin WHERE Role = 'Accountant';
SELECT '========================================' AS '';
