<?php
// Password Change Page for First-time Login
session_start();

// Check if user is supposed to change password
if (!isset($_SESSION['must_change_password']) || !isset($_SESSION['temp_email'])) {
    header('Location: ../../admin_login.php');
    exit();
}

// Include the admin authentication system
require_once '../../admin_auth_system.php';

$email = $_SESSION['temp_email'];
$error_message = '';
$success_message = '';

// Handle password change form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = 'All fields are required.';
    } elseif ($new_password !== $confirm_password) {
        $error_message = 'New passwords do not match.';
    } elseif (strlen($new_password) < 8) {
        $error_message = 'Password must be at least 8 characters long.';
    } else {
        // Attempt to change password
        $result = changePassword($email, $current_password, $new_password);
        
        if ($result['success']) {
            // Password changed successfully
            unset($_SESSION['must_change_password']);
            unset($_SESSION['temp_email']);
            
            // Get updated admin data
            $admin = validateAdmin($email, $new_password);
            
            if ($admin) {
                // Set full session variables
                $_SESSION['admin_id'] = $admin['is_superadmin'] ? 'SUPERADM2024001' : 'ADM2024001';
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['logged_in'] = true;
                $_SESSION['is_superadmin'] = $admin['is_superadmin'];
                
                // Update last login
                updateLastLogin($email);
                
                $success_message = 'Password changed successfully! Redirecting to dashboard...';
                
                // Redirect after 2 seconds
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'layout.php';
                    }, 2000);
                </script>";
            }
        } else {
            $error_message = $result['message'];
        }
    }
}

// Get admin info for display
$credentials = loadAdminCredentials();
$admin_name = isset($credentials[$email]) ? $credentials[$email]['name'] : 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - WIET Library</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: "Lato", sans-serif;
        background: linear-gradient(135deg, #263c79 0%, #1e2d5f 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .change-password-container {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        border: 3px solid #cfac69;
        box-shadow: 0 15px 35px rgba(38, 60, 121, 0.15);
        width: 100%;
        max-width: 500px;
        position: relative;
    }

    .header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .logo {
        width: 80px;
        height: 80px;
        margin: 0 auto 1rem;
        border-radius: 50%;
        background: linear-gradient(135deg, #cfac69 0%, #f3ebdc 100%);
        border: 3px solid #263c79;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #263c79;
        font-size: 2rem;
    }

    h1 {
        font-family: "Poppins", sans-serif;
        color: #263c79;
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .subtitle {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }

    .welcome-message {
        background: #e8f4f8;
        border: 2px solid #17a2b8;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 1.5rem;
        text-align: center;
    }

    .welcome-message h3 {
        color: #263c79;
        margin-bottom: 10px;
        font-size: 1.1rem;
    }

    .security-info {
        background: #fff3cd;
        border: 2px solid #ffc107;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 1.5rem;
    }

    .security-info h4 {
        color: #856404;
        margin-bottom: 8px;
        font-size: 1rem;
    }

    .security-info ul {
        margin-left: 20px;
        color: #856404;
    }

    .security-info li {
        font-size: 0.85rem;
        margin-bottom: 5px;
    }

    .form-group {
        margin-bottom: 1.5rem;
        position: relative;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        color: #263c79;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .required {
        color: #dc3545;
    }

    .form-input {
        width: 100%;
        padding: 12px 15px 12px 45px;
        border: 2px solid #cfac69;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }

    .form-input:focus {
        outline: none;
        border-color: #263c79;
        background: white;
        box-shadow: 0 0 0 3px rgba(38, 60, 121, 0.1);
    }

    .input-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #263c79;
        font-size: 1rem;
    }

    .password-strength {
        margin-top: 5px;
        font-size: 0.8rem;
    }

    .strength-weak { color: #dc3545; }
    .strength-medium { color: #ffc107; }
    .strength-strong { color: #28a745; }

    .btn {
        width: 100%;
        padding: 12px;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 10px;
    }

    .btn-primary {
        background: #263c79;
        color: white;
        border: 2px solid #263c79;
    }

    .btn-primary:hover {
        background: #cfac69;
        color: #263c79;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(207, 172, 105, 0.4);
    }

    .btn-secondary {
        background: transparent;
        color: #6c757d;
        border: 2px solid #6c757d;
    }

    .btn-secondary:hover {
        background: #6c757d;
        color: white;
    }

    .alert {
        padding: 12px 15px;
        border-radius: 6px;
        margin-bottom: 1rem;
        border: 1px solid transparent;
    }

    .alert-danger {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }

    .alert-success {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
    }

    .watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 0;
        pointer-events: none;
        opacity: 0.05;
    }

    .watermark img {
        width: 200px;
        height: auto;
    }

    .form-content {
        position: relative;
        z-index: 1;
    }

    @media (max-width: 480px) {
        .change-password-container {
            padding: 1.5rem;
            margin: 10px;
        }

        h1 {
            font-size: 1.5rem;
        }

        .logo {
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
        }
    }
</style>

<body>
    <div class="change-password-container">
        <div class="watermark">
            <img src="../images/watumull%20logo.png" alt="Watumull Logo">
        </div>
        
        <div class="form-content">
            <div class="header">
                <div class="logo">
                    <i class="fas fa-key"></i>
                </div>
                <h1>Change Password</h1>
                <p class="subtitle">WIET Central Library - First Time Login</p>
            </div>

            <div class="welcome-message">
                <h3><i class="fas fa-user-shield"></i> Welcome, <?php echo htmlspecialchars($admin_name); ?>!</h3>
                <p>For security purposes, you must change your temporary password before accessing the system.</p>
            </div>

            <div class="security-info">
                <h4><i class="fas fa-shield-alt"></i> Password Requirements</h4>
                <ul>
                    <li>Minimum 8 characters long</li>
                    <li>Include uppercase and lowercase letters</li>
                    <li>Include at least one number</li>
                    <li>Include at least one special character (!@#$%^&*)</li>
                </ul>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="changePasswordForm">
                <div class="form-group">
                    <label for="current_password" class="form-label">
                        Current Password (Temporary) <span class="required">*</span>
                    </label>
                    <div style="position: relative;">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="current_password" name="current_password" 
                               class="form-input" placeholder="Enter temporary password" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="new_password" class="form-label">
                        New Password <span class="required">*</span>
                    </label>
                    <div style="position: relative;">
                        <i class="fas fa-key input-icon"></i>
                        <input type="password" id="new_password" name="new_password" 
                               class="form-input" placeholder="Enter new secure password" 
                               required minlength="8" onkeyup="checkPasswordStrength()">
                    </div>
                    <div id="password-strength" class="password-strength"></div>
                </div>

                <div class="form-group">
                    <label for="confirm_password" class="form-label">
                        Confirm New Password <span class="required">*</span>
                    </label>
                    <div style="position: relative;">
                        <i class="fas fa-check-circle input-icon"></i>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               class="form-input" placeholder="Confirm your new password" 
                               required minlength="8" onkeyup="checkPasswordMatch()">
                    </div>
                    <div id="password-match" class="password-strength"></div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Change Password & Continue
                </button>
                
                <button type="button" class="btn btn-secondary" onclick="logout()">
                    <i class="fas fa-sign-out-alt"></i>
                    Cancel & Logout
                </button>
            </form>
        </div>
    </div>

    <script>
        function checkPasswordStrength() {
            const password = document.getElementById('new_password').value;
            const strengthDiv = document.getElementById('password-strength');
            
            let score = 0;
            let feedback = [];
            
            if (password.length >= 8) score++;
            else feedback.push('At least 8 characters');
            
            if (/[a-z]/.test(password)) score++;
            else feedback.push('lowercase letter');
            
            if (/[A-Z]/.test(password)) score++;
            else feedback.push('uppercase letter');
            
            if (/\d/.test(password)) score++;
            else feedback.push('number');
            
            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) score++;
            else feedback.push('special character');
            
            if (score <= 2) {
                strengthDiv.className = 'password-strength strength-weak';
                strengthDiv.innerHTML = '<i class="fas fa-times-circle"></i> Weak - Missing: ' + feedback.join(', ');
            } else if (score <= 4) {
                strengthDiv.className = 'password-strength strength-medium';
                strengthDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Medium - Consider adding: ' + feedback.join(', ');
            } else {
                strengthDiv.className = 'password-strength strength-strong';
                strengthDiv.innerHTML = '<i class="fas fa-check-circle"></i> Strong password!';
            }
        }
        
        function checkPasswordMatch() {
            const password = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchDiv = document.getElementById('password-match');
            
            if (confirmPassword === '') {
                matchDiv.innerHTML = '';
                return;
            }
            
            if (password === confirmPassword) {
                matchDiv.className = 'password-strength strength-strong';
                matchDiv.innerHTML = '<i class="fas fa-check-circle"></i> Passwords match!';
            } else {
                matchDiv.className = 'password-strength strength-weak';
                matchDiv.innerHTML = '<i class="fas fa-times-circle"></i> Passwords do not match!';
            }
        }
        
        function logout() {
            if (confirm('Are you sure you want to logout? You will need to contact the administrator to reset your password.')) {
                window.location.href = '../../admin_login.php?logout=1';
            }
        }

        // Form validation
        document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (newPassword.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long!');
                return false;
            }
        });
    </script>
</body>
</html>