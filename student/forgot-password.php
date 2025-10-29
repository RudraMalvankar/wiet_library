<?php
/**
 * Student Forgot Password - Step 1: Request Reset
 * Sends OTP to student's email
 */

session_start();
require_once '../includes/db_connect.php';

$message = "";
$message_type = "";
$show_otp_form = false;
$email = "";

// Handle forgot password form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'send_otp') {
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email)) {
            $message = "Please enter your email address.";
            $message_type = "error";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Please enter a valid email address.";
            $message_type = "error";
        } else {
            try {
                // Check if student exists with this email
                $stmt = $pdo->prepare("
                    SELECT s.StudentID, s.MemberNo, s.FirstName, s.Surname, s.Email, m.MemberName, m.Status
                    FROM Student s
                    INNER JOIN Member m ON s.MemberNo = m.MemberNo
                    WHERE s.Email = ?
                    LIMIT 1
                ");
                $stmt->execute([$email]);
                $student = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$student) {
                    $message = "No account found with this email address. Please contact the library office.";
                    $message_type = "error";
                } elseif ($student['Status'] != 'Active') {
                    $message = "Your account is not active. Please contact the library office.";
                    $message_type = "error";
                } else {
                    // Generate 6-digit OTP
                    $otp = sprintf("%06d", mt_rand(0, 999999));
                    
                    // Generate unique reset token
                    $reset_token = bin2hex(random_bytes(32));
                    
                    // Set expiry to 30 minutes from now
                    $expires_at = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                    
                    // Store in database
                    $insert_stmt = $pdo->prepare("
                        INSERT INTO PasswordResets (MemberNo, Email, ResetToken, OTP, ExpiresAt, IPAddress)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $insert_stmt->execute([
                        $student['MemberNo'],
                        $email,
                        $reset_token,
                        $otp,
                        $expires_at,
                        $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
                    ]);
                    
                    // Store token and email in session for verification
                    $_SESSION['reset_token'] = $reset_token;
                    $_SESSION['reset_email'] = $email;
                    $_SESSION['reset_time'] = time();
                    
                    // TODO: Send email with OTP
                    // For now, display OTP on screen (development mode)
                    $message = "Reset code sent! For demonstration: Your OTP is <strong>$otp</strong> (valid for 30 minutes).<br><small>In production, this will be sent to your email.</small>";
                    $message_type = "success";
                    $show_otp_form = true;
                    
                    // Log activity
                    try {
                        $log_stmt = $pdo->prepare("
                            INSERT INTO ActivityLog (UserID, UserType, Action, Details, IPAddress)
                            VALUES (?, 'Student', 'Password Reset Requested', ?, ?)
                        ");
                        $log_stmt->execute([
                            $student['StudentID'],
                            "OTP sent to $email",
                            $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
                        ]);
                    } catch (PDOException $e) {
                        error_log("Activity log error: " . $e->getMessage());
                    }
                }
            } catch (PDOException $e) {
                error_log("Forgot password error: " . $e->getMessage());
                $message = "System error. Please try again later.";
                $message_type = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - WIET Library</title>
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

        .forgot-container {
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

        .info-box {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
            color: #004085;
        }

        .info-box i {
            color: #0066cc;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="header">
            <div class="logo">
                <i class="fas fa-key"></i>
            </div>
            <h1 class="title">Forgot Password?</h1>
            <p class="subtitle">Enter your email address and we'll send you a code to reset your password.</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <div><?php echo $message; ?></div>
        </div>
        <?php endif; ?>

        <?php if (!$show_otp_form): ?>
        <!-- Step 1: Request OTP -->
        <form method="POST" action="">
            <input type="hidden" name="action" value="send_otp">
            
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <div class="input-wrapper">
                    <i class="input-icon fas fa-envelope"></i>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="Enter your registered email"
                        value="<?php echo htmlspecialchars($email); ?>"
                        required
                        autofocus
                    >
                </div>
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-paper-plane"></i>
                Send Reset Code
            </button>
        </form>
        <?php else: ?>
        <!-- Step 2: OTP sent, redirect to verify -->
        <div class="info-box">
            <i class="fas fa-info-circle"></i>
            <strong>Next Step:</strong> Click the button below to enter your OTP and create a new password.
        </div>
        <a href="verify-otp.php" class="submit-btn" style="text-decoration: none;">
            <i class="fas fa-arrow-right"></i>
            Verify OTP & Reset Password
        </a>
        <?php endif; ?>

        <div class="back-link">
            <a href="student_login.php">
                <i class="fas fa-arrow-left"></i>
                Back to Login
            </a>
        </div>
    </div>
</body>
</html>
