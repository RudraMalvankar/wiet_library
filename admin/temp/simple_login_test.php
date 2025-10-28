<!DOCTYPE html>
<html>
<head>
    <title>Simple Login Test - No Styling</title>
</head>
<body>
    <h1>BASIC LOGIN TEST - No JavaScript</h1>
    
    <?php
    require_once 'auth_system.php';
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        echo "<p><b>Form Submitted!</b></p>";
        echo "<p>Email: $email</p>";
        echo "<p>Password: " . str_repeat('*', strlen($password)) . "</p>";
        
        $admin = validateAdminCredentials($email, $password);
        
        if ($admin) {
            echo "<p style='color:green;'><b>✅ Credentials Valid!</b></p>";
            initializeAdminSession($admin);
            echo "<p style='color:green;'><b>✅ Session Initialized!</b></p>";
            echo "<p>Session ID: " . session_id() . "</p>";
            echo "<p>Admin ID: " . $_SESSION['admin_id'] . "</p>";
            
            // Try redirect
            echo "<p><b>Attempting redirect...</b></p>";
            while (ob_get_level()) ob_end_clean();
            header("Location: layout.php");
            exit('<p>If you see this, redirect failed. <a href="layout.php">Click here</a></p>');
        } else {
            echo "<p style='color:red;'><b>❌ Invalid Credentials!</b></p>";
        }
    }
    ?>
    
    <hr>
    
    <form method="POST" action="">
        <p>
            <label>Email:</label><br>
            <input type="email" name="email" value="superadmin@wiet.edu.in" required>
        </p>
        <p>
            <label>Password:</label><br>
            <input type="password" name="password" value="admin@123" required>
        </p>
        <p>
            <button type="submit">LOGIN</button>
        </p>
    </form>
    
    <hr>
    <p><small>This is a BARE BONES login form with NO JavaScript, NO styling, NO fancy stuff.</small></p>
    
</body>
</html>
