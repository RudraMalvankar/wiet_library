<!DOCTYPE html>
<html>
<head>
    <title>Quick Login Test</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <h1>🔍 Login System Test</h1>
    
<?php
require_once '../includes/db_connect.php';

$email = 'superadmin@wiet.edu.in';
$password = 'admin@123';

echo "<div class='box info'>";
echo "<h2>Testing Credentials:</h2>";
echo "Email: <b>$email</b><br>";
echo "Password: <b>$password</b>";
echo "</div>";

// Step 1: Check database connection
echo "<div class='box'>";
echo "<h2>1️⃣ Database Connection</h2>";
if ($pdo) {
    echo "<span class='success'>✅ Connected</span>";
} else {
    echo "<span class='error'>❌ Failed</span>";
    die();
}
echo "</div>";

// Step 2: Fetch admin from database
echo "<div class='box'>";
echo "<h2>2️⃣ Admin Record</h2>";
$stmt = $pdo->prepare("SELECT AdminID, Name, Email, Password, Role, Status FROM Admin WHERE Email = ?");
$stmt->execute([$email]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin) {
    echo "<span class='success'>✅ Admin Found</span><br><br>";
    echo "<b>ID:</b> {$admin['AdminID']}<br>";
    echo "<b>Name:</b> {$admin['Name']}<br>";
    echo "<b>Email:</b> {$admin['Email']}<br>";
    echo "<b>Role:</b> {$admin['Role']}<br>";
    echo "<b>Status:</b> {$admin['Status']}<br>";
    echo "<b>Password Hash:</b> " . substr($admin['Password'], 0, 30) . "...<br>";
    echo "<b>Hash Length:</b> " . strlen($admin['Password']) . " chars<br>";
} else {
    echo "<span class='error'>❌ Admin Not Found</span>";
    die();
}
echo "</div>";

// Step 3: Test password verification
echo "<div class='box'>";
echo "<h2>3️⃣ Password Verification</h2>";
$verified = password_verify($password, $admin['Password']);
if ($verified) {
    echo "<span class='success'>✅ Password Matches!</span><br>";
    echo "The password '$password' is correct for this account.";
} else {
    echo "<span class='error'>❌ Password Does NOT Match!</span><br>";
    echo "The password '$password' failed verification.<br><br>";
    echo "<b>Solution:</b> <a href='reset_password.php'>Click here to reset all passwords</a>";
}
echo "</div>";

// Step 4: Test auth_system function
echo "<div class='box'>";
echo "<h2>4️⃣ Auth System Function</h2>";
require_once 'auth_system.php';
$result = validateAdminCredentials($email, $password);
if ($result) {
    echo "<span class='success'>✅ validateAdminCredentials() Returned Admin Data</span><br><br>";
    echo "<b>AdminID:</b> {$result['AdminID']}<br>";
    echo "<b>Name:</b> {$result['Name']}<br>";
    echo "<b>Email:</b> {$result['Email']}<br>";
    echo "<b>Role:</b> {$result['Role']}<br>";
} else {
    echo "<span class='error'>❌ validateAdminCredentials() Returned FALSE</span><br>";
    echo "The login function is failing. Check auth_system.php logic.";
}
echo "</div>";

// Step 5: Test session initialization
if ($result) {
    echo "<div class='box'>";
    echo "<h2>5️⃣ Session Initialization</h2>";
    try {
        initializeAdminSession($result);
        echo "<span class='success'>✅ Session Initialized</span><br><br>";
        echo "<b>Session admin_id:</b> " . ($_SESSION['admin_id'] ?? 'NOT SET') . "<br>";
        echo "<b>Session admin_name:</b> " . ($_SESSION['admin_name'] ?? 'NOT SET') . "<br>";
        echo "<b>Session admin_email:</b> " . ($_SESSION['admin_email'] ?? 'NOT SET') . "<br>";
        echo "<b>Session admin_role:</b> " . ($_SESSION['admin_role'] ?? 'NOT SET') . "<br>";
    } catch (Exception $e) {
        echo "<span class='error'>❌ Session Failed: " . $e->getMessage() . "</span>";
    }
    echo "</div>";
}

echo "<div class='box info'>";
echo "<h2>🎯 Conclusion</h2>";
if ($verified && $result) {
    echo "<span class='success'><b>✅ EVERYTHING WORKS!</b></span><br><br>";
    echo "Login should work. Try logging in at: <a href='login.php'>login.php</a><br><br>";
    echo "If login page still reloads, the issue is with:<br>";
    echo "- Form submission not reaching PHP<br>";
    echo "- JavaScript preventing form submit<br>";
    echo "- Headers already sent before redirect<br>";
} else {
    echo "<span class='error'><b>❌ PROBLEM FOUND</b></span><br><br>";
    if (!$verified) {
        echo "Password verification failed. <a href='reset_password.php'>Run password reset</a>";
    }
    if (!$result) {
        echo "Auth system function failed. Check auth_system.php";
    }
}
echo "</div>";
?>

<div class="box">
    <h2>🔧 Quick Actions</h2>
    <a href="reset_password.php">Reset All Passwords</a> | 
    <a href="login.php">Go to Login Page</a> | 
    <a href="test_auth.php">Full Auth Test</a>
</div>

</body>
</html>
