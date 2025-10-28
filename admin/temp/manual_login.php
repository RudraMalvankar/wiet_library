<?php
/**
 * Manual Login - Bypass Form Test
 * This will attempt login without using the form
 */
require_once 'auth_system.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Manual Login Test</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f0f0f0; }
        .box { background: white; padding: 20px; margin: 10px 0; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 5px; }
        .error { color: #721c24; background: #f8d7da; padding: 10px; border-radius: 5px; }
        h1 { color: #263c79; }
        a { color: #263c79; text-decoration: none; font-weight: bold; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>🔐 Manual Login Test</h1>
    
    <?php
    // Attempt login
    $email = 'superadmin@wiet.edu.in';
    $password = 'admin@123';
    
    echo "<div class='box'>";
    echo "<h2>Attempting Login...</h2>";
    echo "<p><b>Email:</b> $email</p>";
    echo "<p><b>Password:</b> $password</p>";
    echo "</div>";
    
    // Validate credentials
    echo "<div class='box'>";
    echo "<h2>Step 1: Validate Credentials</h2>";
    $admin = validateAdminCredentials($email, $password);
    
    if ($admin) {
        echo "<div class='success'>";
        echo "✅ <b>Credentials Valid!</b><br>";
        echo "Admin ID: {$admin['AdminID']}<br>";
        echo "Name: {$admin['Name']}<br>";
        echo "Role: {$admin['Role']}<br>";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "❌ <b>Credentials Invalid!</b><br>";
        echo "The validateAdminCredentials() function returned FALSE.<br>";
        echo "This means either:<br>";
        echo "1. Password hash in database is wrong → <a href='reset_password.php'>Reset Passwords</a><br>";
        echo "2. Admin doesn't exist<br>";
        echo "3. Admin status is not 'Active'<br>";
        echo "</div>";
    }
    echo "</div>";
    
    // Initialize session
    if ($admin) {
        echo "<div class='box'>";
        echo "<h2>Step 2: Initialize Session</h2>";
        
        try {
            initializeAdminSession($admin);
            
            echo "<div class='success'>";
            echo "✅ <b>Session Initialized!</b><br>";
            echo "Session ID: " . session_id() . "<br>";
            echo "Admin ID in Session: " . ($_SESSION['admin_id'] ?? 'NOT SET') . "<br>";
            echo "Admin Name in Session: " . ($_SESSION['admin_name'] ?? 'NOT SET') . "<br>";
            echo "Admin Email in Session: " . ($_SESSION['admin_email'] ?? 'NOT SET') . "<br>";
            echo "Admin Role in Session: " . ($_SESSION['admin_role'] ?? 'NOT SET') . "<br>";
            echo "</div>";
            
            echo "<hr>";
            echo "<h2>✅ SUCCESS! Login Worked!</h2>";
            echo "<p style='font-size:18px;'>";
            echo "The authentication system is working correctly.<br>";
            echo "If the login form isn't working, the problem is:<br>";
            echo "1. Form not submitting properly<br>";
            echo "2. JavaScript error<br>";
            echo "3. Redirect being blocked<br>";
            echo "</p>";
            
            echo "<p><a href='dashboard.php' style='font-size:20px; background:#263c79; color:white; padding:15px 30px; border-radius:10px; display:inline-block;'>→ Go to Dashboard</a></p>";
            
            echo "<p><a href='layout.php'>Or try Layout.php</a></p>";
            
        } catch (Exception $e) {
            echo "<div class='error'>";
            echo "❌ <b>Session Failed!</b><br>";
            echo "Error: " . $e->getMessage() . "<br>";
            echo "</div>";
        }
        
        echo "</div>";
    }
    
    // Show next steps
    echo "<div class='box'>";
    echo "<h2>🔧 Next Steps</h2>";
    
    if (!$admin) {
        echo "<p style='color:red; font-size:18px;'><b>Priority: Fix Password Issue</b></p>";
        echo "<ol>";
        echo "<li><a href='reset_password.php'>Click here to reset all passwords</a></li>";
        echo "<li>Come back to this page and refresh</li>";
        echo "<li>Should show success</li>";
        echo "</ol>";
    } else {
        echo "<p style='color:green; font-size:18px;'><b>Authentication Works! Test the Login Form:</b></p>";
        echo "<ol>";
        echo "<li><a href='logout.php'>Logout first</a></li>";
        echo "<li><a href='login.php'>Go to login page</a></li>";
        echo "<li>Enter: $email / $password</li>";
        echo "<li>If it reloads, check browser console (F12)</li>";
        echo "</ol>";
    }
    
    echo "</div>";
    
    // Debug info
    echo "<div class='box' style='background:#f9f9f9; font-size:12px;'>";
    echo "<h3>Debug Info</h3>";
    echo "<b>PHP Version:</b> " . phpversion() . "<br>";
    echo "<b>Session ID:</b> " . session_id() . "<br>";
    echo "<b>Session Status:</b> " . (session_status() == PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . "<br>";
    echo "<b>Current File:</b> " . __FILE__ . "<br>";
    echo "</div>";
    ?>
    
</body>
</html>
