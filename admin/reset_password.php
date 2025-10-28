<?php
/**
 * Reset Admin Password Utility
 * Run this file once to reset passwords
 */

require_once '../includes/db_connect.php';

// Password to set for all admins
$new_password = 'admin@123';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Password Reset Utility</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h1 { color: #263c79; }
        .success { background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
<div class='container'>
<h1>🔐 Password Reset Utility</h1>
<div class='info'>
    <strong>New Password:</strong> <code>admin@123</code><br>
    <strong>Hash:</strong> <code>" . substr($hashed_password, 0, 30) . "...</code>
</div>";

try {
    // Update all admin passwords
    $stmt = $pdo->prepare("UPDATE Admin SET Password = ?");
    $stmt->execute([$hashed_password]);
    
    $count = $stmt->rowCount();
    
    echo "<div class='success'>
        ✅ <strong>Success!</strong> Updated password for $count admin account(s).
    </div>";
    
    // Display all admins
    $stmt = $pdo->query("SELECT AdminID, Name, Email, Role FROM Admin ORDER BY AdminID");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Admin Accounts:</h2>";
    echo "<table border='1' cellpadding='10' style='width:100%; border-collapse: collapse;'>
        <tr style='background: #263c79; color: white;'>
            <th>ID</th><th>Name</th><th>Email</th><th>Role</th>
        </tr>";
    
    foreach ($admins as $admin) {
        echo "<tr>
            <td>{$admin['AdminID']}</td>
            <td>{$admin['Name']}</td>
            <td>{$admin['Email']}</td>
            <td>{$admin['Role']}</td>
        </tr>";
    }
    echo "</table>";
    
    echo "<div class='info' style='margin-top: 20px;'>
        <h3>How to Login:</h3>
        <ol>
            <li>Go to: <a href='login.php'>login.php</a></li>
            <li>Use any email from the table above</li>
            <li>Password: <strong>admin@123</strong></li>
        </ol>
    </div>";
    
    echo "<div class='info'>
        <strong>⚠️ Security Note:</strong> Delete this file after use!
        <pre>Delete: admin/reset_password.php</pre>
    </div>";
    
} catch (PDOException $e) {
    echo "<div class='error'>
        ❌ <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "
    </div>";
}

echo "</div></body></html>";
