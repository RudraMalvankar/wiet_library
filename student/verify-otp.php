<?php
/**
 * Student Verify OTP & Reset Password - Step 2
 * Verify OTP and allow password reset
 */

session_start();
require_once '../includes/db_connect.php';

$message = "";
$message_type = "";
$step = 1; // 1 = verify OTP, 2 = set new password

// Check if reset token exists in session
if (!isset($_SESSION['reset_token']) || !isset($_SESSION['reset_email'])) {
    header('Location: forgot-password.php');
    exit();
}

// Check if session expired (30 minutes)
if (isset($_SESSION['reset_time']) && (time() - $_SESSION['reset_time'] > 1800)) {
    session_unset();
    header('Location: forgot-password.php?expired=1');
    exit();
}

$reset_token = $_SESSION['reset_token'];
$email = $_SESSION['reset_email'];

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'verify_otp') {
        $otp = trim($_POST['otp'] ?? '');
        
        if (empty($otp)) {
            $message = "Please enter the OTP sent to your email.";
            $message_type = "error";
        } elseif (strlen($otp) != 6 || !ctype_digit($otp)) {
            $message = "OTP must be a 6-digit number.";
            $message_type = "error";
        } else {
            try {
                // Verify OTP and token
                $stmt = $pdo->prepare("
                    SELECT ResetID, MemberNo, Email, OTP, ExpiresAt, IsUsed
                    FROM PasswordResets
                    WHERE ResetToken = ? AND Email = ? AND IsUsed = 0
                    ORDER BY CreatedAt DESC
                    LIMIT 1
                ");
                $stmt->execute([$reset_token, $email]);
                $reset_record = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$reset_record) {
                    $message = "Invalid or expired reset request. Please try again.";
                    $message_type = "error";
                } elseif (strtotime($reset_record['ExpiresAt']) < time()) {
                    $message = "OTP has expired. Please request a new one.";
                    $message_type = "error";
                } elseif ($reset_record['OTP'] !== $otp) {
                    $message = "Invalid OTP. Please check and try again.";
                    $message_type = "error";
                } else {
                    // OTP verified successfully
                    $_SESSION['otp_verified'] = true;
                    $_SESSION['reset_id'] = $reset_record['ResetID'];
                    $_SESSION['reset_member_no'] = $reset_record['MemberNo'];
                    $step = 2; // Move to password reset step
                    $message = "OTP verified successfully! Now set your new password.";
                    $message_type = "success";
                }
            } catch (PDOException $e) {
                error_log("OTP verification error: " . $e->getMessage());
                $message = "System error. Please try again.";
                $message_type = "error";
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'reset_password') {
        // Check if OTP was verified
        if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified']) {
            $message = "Please verify OTP first.";
            $message_type = "error";
            $step = 1;
        } else {
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (empty($new_password) || empty($confirm_password)) {
                $message = "Please fill in all fields.";
                $message_type = "error";
                $step = 2;
            } elseif (strlen($new_password) < 6) {
                $message = "Password must be at least 6 characters long.";
                $message_type = "error";
                $step = 2;
            } elseif ($new_password !== $confirm_password) {
                $message = "Passwords do not match. Please try again.";
                $message_type = "error";
                $step = 2;
            } else {
                try {
                    // Hash the new password
                    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                    
                    // Update student password
                    $update_stmt = $pdo->prepare("
                        UPDATE Student
                        SET Password = ?
                        WHERE MemberNo = ?
                    ");
                    $update_stmt->execute([$hashed_password, $_SESSION['reset_member_no']]);
                    
                    // Mark reset token as used
                    $mark_used_stmt = $pdo->prepare("
                        UPDATE PasswordResets
                        SET IsUsed = 1, UsedAt = NOW()
                        WHERE ResetID = ?
                    ");
                    $mark_used_stmt->execute([$_SESSION['reset_id']]);
                    
                    // Log activity
                    try {
                        $log_stmt = $pdo->prepare("
                            INSERT INTO ActivityLog (UserID, UserType, Action, Details, IPAddress)
                            VALUES (?, 'Student', 'Password Reset Completed', 'Password successfully reset', ?)
                        ");
                        $log_stmt->execute([
                            $_SESSION['reset_member_no'],
                            $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
                        ]);
                    } catch (PDOException $e) {
                        error_log("Activity log error: " . $e->getMessage());
                    }
                    
                    // Clear session variables
                    unset($_SESSION['reset_token']);
                    unset($_SESSION['reset_email']);
                    unset($_SESSION['reset_time']);
                    unset($_SESSION['otp_verified']);
                    unset($_SESSION['reset_id']);
                    unset($_SESSION['reset_member_no']);
                    
                    // Redirect to login with success message
                    $_SESSION['password_reset_success'] = true;
                    header('Location: student_login.php?reset=success');
                    exit();
                    
                } catch (PDOException $e) {
                    error_log("Password reset error: " . $e->getMessage());
                    $message = "Failed to reset password. Please try again.";
                    $message_type = "error";
                    $step = 2;
                }
            }
        }
    }
}

// Check if OTP was already verified (coming back to page)
if (isset($_SESSION['otp_verified']) && $_SESSION['otp_verified']) {
    $step = 2;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - WIET Library</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #263c79 0%, #1a2850 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .verify-container {
            background: white;
            padding: 2.5rem 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo {
            width: 70px;
            height: 70px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #cfac69 0%, #f3ebdc 100%);
            border: 3px solid #263c79;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #263c79;
            font-size: 1.75rem;
        }

        .title {
            font-size: 1.6rem;
            color: #263c79;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .email-display {
            background: #f3ebdc;
            border: 2px solid #cfac69;
            border-radius: 8px;
            padding: 0.75rem;
            text-align: center;
            margin-bottom: 1.5rem;
            color: #263c79;
            font-weight: 600;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .alert i {
            font-size: 1.2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .input-wrapper {
            position: relative;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            border: 2px solid #cfac69;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f3ebdc;
        }

        .form-input:focus {
            outline: none;
            border-color: #263c79;
            background: white;
            box-shadow: 0 0 0 3px rgba(38, 60, 121, 0.1);
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #263c79;
            font-size: 1rem;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            cursor: pointer;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #263c79;
        }

        .submit-btn {
            width: 100%;
            padding: 0.85rem;
            background: #263c79;
            color: white;
            border: 2px solid #263c79;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .submit-btn:hover {
            background: #cfac69;
            border-color: #cfac69;
            color: #263c79;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(207, 172, 105, 0.4);
        }

        .back-link {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e0e0e0;
        }

        .back-link a {
            color: #263c79;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
            color: #cfac69;
        }

        .otp-input {
            font-size: 1.5rem;
            letter-spacing: 0.5rem;
            text-align: center;
            font-weight: 600;
        }

        .password-requirements {
            font-size: 0.8rem;
            color: #666;
            margin-top: 0.5rem;
        }

        .password-requirements ul {
            margin-left: 1.25rem;
            margin-top: 0.25rem;
        }

        .password-requirements li {
            margin-bottom: 0.25rem;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }

        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
            border: 2px solid #cfac69;
            color: #666;
            background: white;
        }

        .step.active {
            background: #263c79;
            border-color: #263c79;
            color: white;
        }

        .step.completed {
            background: #28a745;
            border-color: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <div class="header">
            <div class="logo">
                <i class="fas fa-<?php echo $step == 1 ? 'shield-alt' : 'lock'; ?>"></i>
            </div>
            <h1 class="title"><?php echo $step == 1 ? 'Verify OTP' : 'Create New Password'; ?></h1>
            <p class="subtitle">
                <?php echo $step == 1 
                    ? 'Enter the 6-digit code sent to your email.' 
                    : 'Choose a strong password for your account.'; 
                ?>
            </p>
        </div>

        <div class="step-indicator">
            <div class="step <?php echo $step >= 1 ? 'completed' : ''; ?>">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="step <?php echo $step >= 2 ? 'active' : ''; ?>">
                <i class="fas fa-key"></i>
            </div>
            <div class="step">
                <i class="fas fa-check"></i>
            </div>
        </div>

        <div class="email-display">
            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($email); ?>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <div><?php echo $message; ?></div>
        </div>
        <?php endif; ?>

        <?php if ($step == 1): ?>
        <!-- Step 1: Verify OTP -->
        <form method="POST" action="">
            <input type="hidden" name="action" value="verify_otp">
            
            <div class="form-group">
                <label class="form-label" for="otp">6-Digit Code</label>
                <div class="input-wrapper">
                    <i class="input-icon fas fa-shield-alt"></i>
                    <input 
                        type="text" 
                        id="otp" 
                        name="otp" 
                        class="form-input otp-input" 
                        placeholder="000000"
                        maxlength="6"
                        pattern="[0-9]{6}"
                        required
                        autofocus
                    >
                </div>
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-check"></i>
                Verify Code
            </button>
        </form>

        <?php else: ?>
        <!-- Step 2: Reset Password -->
        <form method="POST" action="">
            <input type="hidden" name="action" value="reset_password">
            
            <div class="form-group">
                <label class="form-label" for="new_password">New Password</label>
                <div class="input-wrapper">
                    <i class="input-icon fas fa-lock"></i>
                    <input 
                        type="password" 
                        id="new_password" 
                        name="new_password" 
                        class="form-input" 
                        placeholder="Enter new password"
                        minlength="6"
                        required
                        autofocus
                    >
                    <i class="password-toggle fas fa-eye" onclick="togglePassword('new_password')"></i>
                </div>
                <div class="password-requirements">
                    <ul>
                        <li>At least 6 characters long</li>
                        <li>Use a mix of letters and numbers</li>
                    </ul>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="confirm_password">Confirm Password</label>
                <div class="input-wrapper">
                    <i class="input-icon fas fa-lock"></i>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        class="form-input" 
                        placeholder="Re-enter new password"
                        minlength="6"
                        required
                    >
                    <i class="password-toggle fas fa-eye" onclick="togglePassword('confirm_password')"></i>
                </div>
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-save"></i>
                Reset Password
            </button>
        </form>
        <?php endif; ?>

        <div class="back-link">
            <a href="forgot-password.php">
                <i class="fas fa-arrow-left"></i>
                Request New Code
            </a>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.parentElement.querySelector('.password-toggle');
            
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

        // Auto-format OTP input (digits only)
        const otpInput = document.getElementById('otp');
        if (otpInput) {
            otpInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
    </script>
</body>
</html>
