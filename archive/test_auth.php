<?php
/**
 * Test Page - Verify Auth System
 * Access: http://localhost/wiet_lib/admin/test_auth.php
 */

require_once 'session_check.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auth System Test</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 2rem;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #263c79;
            margin-bottom: 1rem;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        .info-box {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
        }
        .info-box h3 {
            color: #263c79;
            margin-bottom: 0.5rem;
        }
        .permission-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 0.5rem;
            margin-top: 1rem;
        }
        .permission-item {
            background: #e9ecef;
            padding: 0.5rem;
            border-radius: 3px;
            font-size: 0.9rem;
        }
        .btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background: #263c79;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 1rem;
        }
        .btn:hover {
            background: #1a2a5a;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✅ Authentication System Test</h1>
        
        <div class="success">
            <strong>Success!</strong> You are logged in and the authentication system is working correctly.
        </div>

        <div class="info-box">
            <h3>Current User Information</h3>
            <p><strong>Admin ID:</strong> <?php echo $current_admin['id']; ?></p>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($current_admin['name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($current_admin['email']); ?></p>
            <p><strong>Role:</strong> <?php echo htmlspecialchars($current_admin['role']); ?></p>
            <p><strong>Super Admin:</strong> <?php echo $current_admin['is_superadmin'] ? 'Yes' : 'No'; ?></p>
        </div>

        <div class="info-box">
            <h3>Your Permissions (<?php echo count($current_admin['permissions']); ?> total)</h3>
            <div class="permission-list">
                <?php foreach ($current_admin['permissions'] as $permission): ?>
                    <div class="permission-item">
                        ✓ <?php echo htmlspecialchars($permission); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="info-box">
            <h3>Session Information</h3>
            <p><strong>Session ID:</strong> <?php echo session_id(); ?></p>
            <p><strong>Login Time:</strong> <?php echo isset($_SESSION['login_time']) ? date('Y-m-d H:i:s', $_SESSION['login_time']) : 'N/A'; ?></p>
            <p><strong>Last Activity:</strong> <?php echo isset($_SESSION['last_activity']) ? date('Y-m-d H:i:s', $_SESSION['last_activity']) : 'N/A'; ?></p>
        </div>

        <div class="info-box">
            <h3>Permission Tests</h3>
            <p>✓ Can view dashboard: <?php echo hasPermission('view_dashboard') ? '<strong>Yes</strong>' : 'No'; ?></p>
            <p>✓ Can add books: <?php echo hasPermission('add_books') ? '<strong>Yes</strong>' : 'No'; ?></p>
            <p>✓ Can delete books: <?php echo hasPermission('delete_books') ? '<strong>Yes</strong>' : 'No'; ?></p>
            <p>✓ Can manage admins: <?php echo hasPermission('add_admins') ? '<strong>Yes</strong>' : 'No'; ?></p>
            <p>✓ Can create backups: <?php echo hasPermission('create_backup') ? '<strong>Yes</strong>' : 'No'; ?></p>
            <p>✓ Is Super Admin: <?php echo isSuperAdmin() ? '<strong>Yes</strong>' : 'No'; ?></p>
        </div>

        <a href="dashboard.php" class="btn">Go to Dashboard</a>
        <a href="logout.php" class="btn" style="background: #dc3545;">Logout</a>
    </div>
</body>
</html>
