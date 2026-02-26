<?php
// Change Password Page - For logged-in admins
// This file will be included in the main content area

// Session started by layout.php
require_once 'session_check.php';
require_once 'admin_auth_system.php';
require_once '../includes/db_connect.php';

$admin_email = $current_admin['email'];
$admin_name = $current_admin['name'];
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
    } elseif ($current_password === $new_password) {
        $error_message = 'New password must be different from current password.';
    } else {
        // Attempt to change password
        $result = changePassword($admin_email, $current_password, $new_password);
        
        if ($result['success']) {
            $success_message = 'Password changed successfully! Your new password is now active.';
            
            // Log the activity
            try {
                $log_stmt = $pdo->prepare("
                    INSERT INTO ActivityLog (UserID, UserType, Action, Details, Timestamp)
                    VALUES (?, 'Admin', 'Password Changed', 'Admin changed their password', NOW())
                ");
                $log_stmt->execute([$_SESSION['admin_id']]);
            } catch (PDOException $e) {
                error_log("Activity log error: " . $e->getMessage());
            }
        } else {
            $error_message = $result['message'];
        }
    }
}
?>

<style>
    .change-password-container {
        max-width: 800px;
        margin: 0 auto;
    }

    .page-header {
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 2px solid #cfac69;
    }

    .page-title {
        color: #263c79;
        font-size: 28px;
        font-weight: 700;
        margin: 0 0 8px 0;
    }

    .page-subtitle {
        color: #666;
        font-size: 16px;
        margin: 0;
    }

    .security-info-box {
        background: #fff3cd;
        border: 2px solid #ffc107;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 25px;
    }

    .security-info-box h3 {
        color: #856404;
        margin-bottom: 10px;
        font-size: 18px;
    }

    .security-info-box ul {
        margin-left: 20px;
        color: #856404;
    }

    .security-info-box li {
        font-size: 14px;
        margin-bottom: 5px;
    }

    .password-form-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 30px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        color: #263c79;
        font-weight: 600;
        font-size: 14px;
    }

    .required {
        color: #dc3545;
    }

    .input-wrapper {
        position: relative;
    }

    .form-input {
        width: 100%;
        padding: 12px 45px 12px 15px;
        border: 2px solid #cfac69;
        border-radius: 6px;
        font-size: 15px;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }

    .form-input:focus {
        outline: none;
        border-color: #263c79;
        background: white;
        box-shadow: 0 0 0 3px rgba(38, 60, 121, 0.1);
    }

    .toggle-password {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #6c757d;
        transition: color 0.3s;
    }

    .toggle-password:hover {
        color: #263c79;
    }

    .password-strength {
        margin-top: 5px;
        font-size: 13px;
        font-weight: 500;
    }

    .strength-weak { color: #dc3545; }
    .strength-medium { color: #ffc107; }
    .strength-strong { color: #28a745; }

    .btn-group {
        display: flex;
        gap: 15px;
        margin-top: 25px;
    }

    .btn {
        padding: 12px 25px;
        border: none;
        border-radius: 6px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background: #263c79;
        color: white;
        flex: 1;
    }

    .btn-primary:hover {
        background: #1e2d5f;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(38, 60, 121, 0.3);
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
        padding: 15px 20px;
        border-radius: 6px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert i {
        font-size: 18px;
    }

    .alert-danger {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }

    .alert-success {
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }

    .admin-info {
        background: #e8f4f8;
        border: 2px solid #17a2b8;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .admin-info i {
        font-size: 24px;
        color: #17a2b8;
    }

    .admin-info-text h4 {
        color: #263c79;
        margin: 0 0 5px 0;
        font-size: 16px;
    }

    .admin-info-text p {
        margin: 0;
        color: #666;
        font-size: 14px;
    }

    @media (max-width: 768px) {
        .btn-group {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="change-password-container">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-key"></i>
            Change Password
        </h1>
        <p class="page-subtitle">Update your account security credentials</p>
    </div>

    <div class="admin-info">
        <i class="fas fa-user-shield"></i>
        <div class="admin-info-text">
            <h4><?php echo htmlspecialchars($admin_name); ?></h4>
            <p><?php echo htmlspecialchars($admin_email); ?></p>
        </div>
    </div>

    <?php if ($error_message): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            <span><?php echo htmlspecialchars($error_message); ?></span>
        </div>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo htmlspecialchars($success_message); ?></span>
        </div>
    <?php endif; ?>

    <div class="security-info-box">
        <h3><i class="fas fa-shield-alt"></i> Password Requirements</h3>
        <ul>
            <li>Minimum 8 characters long</li>
            <li>Include uppercase and lowercase letters</li>
            <li>Include at least one number</li>
            <li>Include at least one special character (!@#$%^&*)</li>
            <li>Must be different from your current password</li>
        </ul>
    </div>

    <div class="password-form-card">
        <form method="POST" action="" id="changePasswordForm">
            <div class="form-group">
                <label for="current_password" class="form-label">
                    Current Password <span class="required">*</span>
                </label>
                <div class="input-wrapper">
                    <input type="password" id="current_password" name="current_password" 
                           class="form-input" placeholder="Enter your current password" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePassword('current_password')"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="new_password" class="form-label">
                    New Password <span class="required">*</span>
                </label>
                <div class="input-wrapper">
                    <input type="password" id="new_password" name="new_password" 
                           class="form-input" placeholder="Enter new secure password" 
                           required minlength="8" onkeyup="checkPasswordStrength()">
                    <i class="fas fa-eye toggle-password" onclick="togglePassword('new_password')"></i>
                </div>
                <div id="password-strength" class="password-strength"></div>
            </div>

            <div class="form-group">
                <label for="confirm_password" class="form-label">
                    Confirm New Password <span class="required">*</span>
                </label>
                <div class="input-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" 
                           class="form-input" placeholder="Confirm your new password" 
                           required minlength="8" onkeyup="checkPasswordMatch()">
                    <i class="fas fa-eye toggle-password" onclick="togglePassword('confirm_password')"></i>
                </div>
                <div id="password-match" class="password-strength"></div>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Change Password
                </button>
                <button type="button" class="btn btn-secondary" onclick="window.location.hash=''; document.querySelector('[data-page=dashboard]').click();">
                    <i class="fas fa-times"></i>
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = field.parentElement.querySelector('.toggle-password');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

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
