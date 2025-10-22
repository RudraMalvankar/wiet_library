<?php
// Admin credentials storage (in real application, this would be in database)
// This simulates a simple admin database
$admin_credentials_file = 'admin_credentials.json';

function loadAdminCredentials() {
    global $admin_credentials_file;
    if (file_exists($admin_credentials_file)) {
        $content = file_get_contents($admin_credentials_file);
        return json_decode($content, true) ?: [];
    }
    
    // Default credentials if file doesn't exist
    return [
        'superAdmin@lib.com' => [
            'email' => 'superAdmin@lib.com',
            'password' => 'pass123',
            'name' => 'Super Admin',
            'role' => 'Super Admin',
            'is_superadmin' => true,
            'status' => 'Active',
            'created_date' => '2024-01-01',
            'last_login' => null
        ],
        'admin@lib.com' => [
            'email' => 'admin@lib.com',
            'password' => 'pass123',
            'name' => 'Admin User',
            'role' => 'Librarian',
            'is_superadmin' => false,
            'status' => 'Active',
            'created_date' => '2024-01-15',
            'last_login' => null
        ]
    ];
}

function saveAdminCredentials($credentials) {
    global $admin_credentials_file;
    return file_put_contents($admin_credentials_file, json_encode($credentials, JSON_PRETTY_PRINT));
}

function validateAdmin($email, $password) {
    $credentials = loadAdminCredentials();
    
    if (isset($credentials[$email])) {
        $admin = $credentials[$email];
        if ($admin['password'] === $password && $admin['status'] === 'Active') {
            return $admin;
        }
    }
    
    return false;
}

function addNewAdmin($adminData) {
    $credentials = loadAdminCredentials();
    
    // Check if email already exists
    if (isset($credentials[$adminData['email']])) {
        return ['success' => false, 'message' => 'Email already exists'];
    }
    
    // Generate secure temporary password
    $tempPassword = generateTempPassword();
    
    // Add new admin
    $credentials[$adminData['email']] = [
        'email' => $adminData['email'],
        'password' => $tempPassword,
        'name' => $adminData['name'],
        'role' => $adminData['role'],
        'is_superadmin' => ($adminData['role'] === 'Super Admin'),
        'status' => 'Active',
        'created_date' => date('Y-m-d'),
        'last_login' => null,
        'first_login' => true, // Flag to force password change on first login
        'password_changed' => false
    ];
    
    if (saveAdminCredentials($credentials)) {
        // In real application, send email here
        sendWelcomeEmail($adminData['email'], $adminData['name'], $tempPassword);
        
        return [
            'success' => true, 
            'message' => 'Admin created successfully',
            'login_email' => $adminData['email'],
            'temp_password' => $tempPassword,
            'email_sent' => true
        ];
    } else {
        return ['success' => false, 'message' => 'Failed to save admin credentials'];
    }
}

function generateTempPassword($length = 10) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}

function sendWelcomeEmail($email, $name, $tempPassword) {
    // In a real application, this would send an actual email
    // For demo purposes, we'll simulate this
    
    $emailContent = "
    Dear {$name},
    
    Welcome to WIET Library Admin System!
    
    Your account has been created with the following details:
    Email: {$email}
    Temporary Password: {$tempPassword}
    
    Please log in and change your password immediately for security purposes.
    
    Login URL: " . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://{$_SERVER['HTTP_HOST']}" . dirname($_SERVER['REQUEST_URI']) . "/admin_login.php
    
    Best regards,
    WIET Library System
    ";
    
    // In real application: mail($email, 'Welcome to WIET Library Admin System', $emailContent);
    // For now, we'll log this to a file for demo purposes
    file_put_contents('email_log.txt', date('Y-m-d H:i:s') . " - Email sent to {$email}\n" . $emailContent . "\n\n", FILE_APPEND);
    
    return true;
}

function changePassword($email, $oldPassword, $newPassword) {
    $credentials = loadAdminCredentials();
    
    if (isset($credentials[$email])) {
        // Verify old password
        if ($credentials[$email]['password'] === $oldPassword) {
            // Update password
            $credentials[$email]['password'] = $newPassword;
            $credentials[$email]['first_login'] = false;
            $credentials[$email]['password_changed'] = true;
            
            if (saveAdminCredentials($credentials)) {
                return ['success' => true, 'message' => 'Password changed successfully'];
            }
        } else {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }
    }
    
    return ['success' => false, 'message' => 'Admin not found'];
}

function mustChangePassword($email) {
    $credentials = loadAdminCredentials();
    
    if (isset($credentials[$email])) {
        return isset($credentials[$email]['first_login']) && $credentials[$email]['first_login'] === true;
    }
    
    return false;
}

function updateAdminStatus($email, $status) {
    $credentials = loadAdminCredentials();
    
    if (isset($credentials[$email])) {
        $credentials[$email]['status'] = $status;
        if (saveAdminCredentials($credentials)) {
            return ['success' => true, 'message' => 'Admin status updated'];
        }
    }
    
    return ['success' => false, 'message' => 'Admin not found or update failed'];
}

function removeAdmin($email) {
    $credentials = loadAdminCredentials();
    
    if (isset($credentials[$email])) {
        unset($credentials[$email]);
        if (saveAdminCredentials($credentials)) {
            return ['success' => true, 'message' => 'Admin removed successfully'];
        }
    }
    
    return ['success' => false, 'message' => 'Admin not found or removal failed'];
}

function updateLastLogin($email) {
    $credentials = loadAdminCredentials();
    
    if (isset($credentials[$email])) {
        $credentials[$email]['last_login'] = date('Y-m-d H:i:s');
        saveAdminCredentials($credentials);
    }
}
?>