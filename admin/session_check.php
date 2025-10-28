<?php
/**
 * Session Check - Include at the top of every admin page
 * Ensures user is logged in and session is valid
 */

require_once __DIR__ . '/auth_system.php';

// Check if user is logged in, redirect to login if not
requireLogin('/wiet_lib/admin/login.php');

// Refresh session activity
$_SESSION['last_activity'] = time();

// Get current admin details for use in page
$current_admin = getAdminDetails();

// Helper function to check page permission and show access denied if needed
function checkPagePermission($permissionKey) {
    if (!hasPermission($permissionKey)) {
        // Log unauthorized access attempt
        logAdminActivity(
            $_SESSION['admin_id'] ?? null, 
            'Unauthorized Access Attempt', 
            "Attempted to access page requiring permission: $permissionKey"
        );
        
        // Show access denied page
        http_response_code(403);
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Access Denied - WIET Library</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body {
                    font-family: 'Arial', sans-serif;
                    background: linear-gradient(135deg, #263c79 0%, #1a2a5a 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                }
                .access-denied-container {
                    background: white;
                    padding: 3rem;
                    border-radius: 20px;
                    text-align: center;
                    max-width: 500px;
                    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
                }
                .access-denied-container i {
                    font-size: 5rem;
                    color: #dc3545;
                    margin-bottom: 1.5rem;
                }
                .access-denied-container h1 {
                    color: #263c79;
                    margin-bottom: 1rem;
                    font-size: 2rem;
                }
                .access-denied-container p {
                    color: #666;
                    margin-bottom: 2rem;
                    line-height: 1.6;
                }
                .btn {
                    display: inline-block;
                    padding: 0.8rem 2rem;
                    background: #263c79;
                    color: white;
                    text-decoration: none;
                    border-radius: 10px;
                    transition: all 0.3s ease;
                }
                .btn:hover {
                    background: #1a2a5a;
                    transform: translateY(-2px);
                    box-shadow: 0 5px 15px rgba(38, 60, 121, 0.3);
                }
                .user-info {
                    background: #f8f9fa;
                    padding: 1rem;
                    border-radius: 10px;
                    margin-bottom: 2rem;
                    font-size: 0.9rem;
                    color: #555;
                }
            </style>
        </head>
        <body>
            <div class="access-denied-container">
                <i class="fas fa-ban"></i>
                <h1>Access Denied</h1>
                <div class="user-info">
                    <strong>User:</strong> <?php echo htmlspecialchars($GLOBALS['current_admin']['name']); ?><br>
                    <strong>Role:</strong> <?php echo htmlspecialchars($GLOBALS['current_admin']['role']); ?>
                </div>
                <p>
                    You do not have permission to access this page. 
                    This resource requires specific privileges that your account does not have.
                </p>
                <p style="font-size: 0.9rem; color: #999;">
                    <i class="fas fa-info-circle"></i> 
                    Contact your Super Admin if you believe you should have access.
                </p>
                <a href="dashboard.php" class="btn">
                    <i class="fas fa-home"></i> Return to Dashboard
                </a>
            </div>
        </body>
        </html>
        <?php
        exit();
    }
}

// Helper function to display admin name in pages
function displayAdminName() {
    return htmlspecialchars($GLOBALS['current_admin']['name']);
}

// Helper function to display admin role
function displayAdminRole() {
    return htmlspecialchars($GLOBALS['current_admin']['role']);
}

// Helper function to check if current page is accessible
function isPageAccessible($permissionKey) {
    return hasPermission($permissionKey);
}
