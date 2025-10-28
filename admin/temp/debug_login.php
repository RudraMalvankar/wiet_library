<?php
/**
 * Debug Login Test
 */
require_once 'auth_system.php';

echo "<h1>Login Debug Test</h1>";

// Test credentials
$email = 'superadmin@wiet.edu.in';
$password = 'admin@123';

echo "<h2>Testing Credentials:</h2>";
echo "Email: $email<br>";
echo "Password: $password<br><br>";

// Test database connection
echo "<h2>Database Connection:</h2>";
try {
    global $pdo;
    echo "✅ Database connected: " . ($pdo ? "YES" : "NO") . "<br><br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br><br>";
}

// Check if admin exists
echo "<h2>Admin Record:</h2>";
try {
    $stmt = $pdo->prepare("SELECT AdminID, Name, Email, Password, Role, Status FROM Admin WHERE Email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "✅ Admin found!<br>";
        echo "ID: " . $admin['AdminID'] . "<br>";
        echo "Name: " . $admin['Name'] . "<br>";
        echo "Email: " . $admin['Email'] . "<br>";
        echo "Role: " . $admin['Role'] . "<br>";
        echo "Status: " . $admin['Status'] . "<br>";
        echo "Password Hash (first 30 chars): " . substr($admin['Password'], 0, 30) . "...<br>";
        echo "Password Hash Length: " . strlen($admin['Password']) . "<br><br>";
        
        // Test password verification
        echo "<h2>Password Verification:</h2>";
        $verified = password_verify($password, $admin['Password']);
        echo "password_verify() result: " . ($verified ? "✅ TRUE (Password matches!)" : "❌ FALSE (Password doesn't match)") . "<br><br>";
        
        // Show what hash should be
        echo "<h2>Expected Hash:</h2>";
        $correct_hash = password_hash($password, PASSWORD_DEFAULT);
        echo "New hash for '$password': " . substr($correct_hash, 0, 30) . "...<br>";
        echo "Note: Each run generates a different hash, but all should verify correctly<br><br>";
        
    } else {
        echo "❌ Admin not found in database<br><br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br><br>";
}

// Test the validateAdminCredentials function
echo "<h2>Testing validateAdminCredentials():</h2>";
$result = validateAdminCredentials($email, $password);
if ($result) {
    echo "✅ Function returned admin data:<br>";
    echo "<pre>" . print_r($result, true) . "</pre>";
} else {
    echo "❌ Function returned FALSE (login would fail)<br>";
}

echo "<hr>";
echo "<h2>What to do:</h2>";
echo "<ol>";
echo "<li>If password_verify() is FALSE: Run <a href='reset_password.php'>reset_password.php</a></li>";
echo "<li>If validateAdminCredentials() is FALSE: Check auth_system.php</li>";
echo "<li>If both are TRUE: Check if session is being initialized</li>";
echo "</ol>";
?>
