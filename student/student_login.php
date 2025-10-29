<?php
// Student Login Page
session_start();
require_once '../includes/db_connect.php';

$error_message = "";

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate input
    if (empty($email) || empty($password)) {
        $error_message = "Please enter both email and password.";
    } else {
        try {
            // Query student with member details
            $stmt = $pdo->prepare("
                SELECT 
                    s.StudentID,
                    s.MemberNo,
                    s.FirstName,
                    s.MiddleName,
                    s.Surname,
                    s.Email,
                    s.Branch,
                    s.CourseName,
                    s.PRN,
                    s.Mobile,
                    s.ValidTill,
                    m.MemberName,
                    m.Status,
                    m.BooksIssued,
                    m.Group
                FROM Student s
                INNER JOIN Member m ON s.MemberNo = m.MemberNo
                WHERE s.Email = ? 
                AND m.Status = 'Active'
                LIMIT 1
            ");
            
            $stmt->execute([$email]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Validate credentials (password is 123456 for all students)
            if ($student && $password === '123456') {
                // Check if membership is still valid
                if ($student['ValidTill'] && strtotime($student['ValidTill']) < time()) {
                    $error_message = "Your library membership has expired. Please contact the library office.";
                } else {
                    // Set session variables
                    $_SESSION['student_id'] = $student['StudentID'];
                    $_SESSION['member_no'] = $student['MemberNo'];
                    $_SESSION['student_name'] = $student['MemberName'];
                    $_SESSION['student_email'] = $student['Email'];
                    $_SESSION['student_branch'] = $student['Branch'];
                    $_SESSION['student_course'] = $student['CourseName'];
                    $_SESSION['student_prn'] = $student['PRN'];
                    $_SESSION['student_mobile'] = $student['Mobile'];
                    $_SESSION['books_issued'] = $student['BooksIssued'];
                    $_SESSION['logged_in'] = true;
                    $_SESSION['login_time'] = time();
                    $_SESSION['last_activity'] = time();
                    
                    // Log student login activity (optional)
                    try {
                        $log_stmt = $pdo->prepare("
                            INSERT INTO ActivityLog (UserID, UserType, Action, Details, IPAddress)
                            VALUES (?, 'Student', 'Login', 'Student logged into portal', ?)
                        ");
                        $log_stmt->execute([
                            $student['StudentID'],
                            $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
                        ]);
                    } catch (PDOException $e) {
                        // Log error but continue with login
                        error_log("Activity log error: " . $e->getMessage());
                    }
                    
                    // Redirect to student dashboard
                    header('Location: ./layout.php');
                    exit();
                }
            } else {
                $error_message = "Invalid email or password. Please contact the library office for assistance.";
            }
            
        } catch (PDOException $e) {
            error_log("Student login error: " . $e->getMessage());
            $error_message = "System error. Please contact library administration.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student Login - WIET Library</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Lato:wght@300;400;700&display=swap"
        rel="stylesheet" />
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
</head>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* Typography Setup */
    body {
        font-family: "Lato", sans-serif;
        font-weight: 400;
        font-style: normal;
        background: #263c79;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Headings use Poppins font */
    h1, h2, h3, h4, h5, h6 {
        font-family: "Poppins", sans-serif;
        font-weight: 700;
    }

    .login-container {
    background: white;
    padding: 1.5rem;
    border-radius: 15px;
    border: 3px solid #cfac69;
    box-shadow: 0 15px 35px rgba(38, 60, 121, 0.15);
    width: 100%;
    max-width: 400px;
    min-height: 490px;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: center;
    }

    .login-header {
    text-align: center;
    margin-bottom: 1rem;
    width: 100%;
    }

    .logo {
        width: 70px;
        height: 70px;
        margin: 0 auto 0.75rem;
        border-radius: 50%;
        background: linear-gradient(135deg, #cfac69 0%, #f3ebdc 100%);
        border: 3px solid #263c79;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #263c79;
        font-size: 1.75rem;
    }

    .login-title {
        font-family: "Poppins", sans-serif;
        color: #263c79;
        font-size: 1.6rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .login-subtitle {
        color: #666;
        font-size: 0.85rem;
    }

    .form-group {
        margin-bottom: 1.25rem;
        position: relative;
        padding-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 0.4rem;
        color: #333;
        font-weight: 500;
        font-size: 0.85rem;
    }

    .form-input {
        width: 100%;
        padding: 0.65rem 1rem 0.65rem 2.25rem;
        border: 2px solid #cfac69;
        border-radius: 6px;
        font-size: 0.95rem;
        transition: border-color 0.3s ease;
        background: #f3ebdc;
    }

    .form-input:focus {
        outline: none;
        border-color: #263c79;
        background: white;
    }

    .input-icon {
        position: absolute;
        left: 0.65rem;
        top: 50%;
        transform: translateY(-50%);
        color: #263c79;
        font-size: 0.9rem;
    }

    .login-btn {
    width: auto;
    padding: 0.5rem 1.8rem;
    background: #263c79;
    color: #fff;
    border: 2px solid #263c79;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s;
    margin: 0.75rem auto 0;
    display: block;
    }

    .login-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(207, 172, 105, 0.4);
    background: #cfac69;
    color: #263c79;
    }

    .login-btn:active {
        transform: translateY(0);
    }

    .error-message {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
        padding: 0.75rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        border: 2px solid #dc3545;
        font-size: 0.9rem;
    }

    .forgot-password {
        text-align: center;
        margin: 1rem 0;
    }

    .forgot-password a {
        color: #333;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .forgot-password a:hover {
        text-decoration: underline;
    }



    /* Watermark */
    .watermark {
        position: fixed;
        top: 50%;
        left: 50%;
        z-index: 3;
        pointer-events: none;
        user-select: none;
    }

    .watermark img {
         max-width: 300px;
         max-height: 300px;
         width: auto;
         height: auto;
    }

    .login-container > * {
        position: relative;
        z-index: 1;
    }

    /* Watermark styles for login form */
    .login-watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 10;
        pointer-events: none;
        width: 120px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .login-watermark img {
        width: 120px !important;
        height: 80px !important;
        opacity: 0.08;
    }

    .login-header-styled {
        margin-top: 0;
        z-index: 1;
        position: relative;
    }

    .login-form-styled {
        z-index: 1;
        width: 100%;
        position: relative;
    }

    .error-message-styled {
        z-index: 1;
    }

    /* Mobile Responsive */
    @media (max-width: 480px) {
        .login-container {
            margin: 1rem;
            padding: 1.25rem;
            max-width: 300px;
            min-height: 400px;
        }

        .login-title {
            font-size: 1.4rem;
        }

        .logo {
            width: 55px;
            height: 55px;
            font-size: 1.4rem;
        }

        .login-header {
            margin-bottom: 1.25rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }
    }
</style>

<body>
    <div class="login-container">
        <div class="login-watermark">
            <img src="/wiet_lib/images/watumull_logo.png" alt="Watumull Logo">
        </div>
        <div class="login-header login-header-styled">
            <div class="logo">
                <i class="fas fa-book"></i>
            </div>
            <h1 class="login-title">Student Login</h1>
            <p class="login-subtitle">WIET Central Library</p>
        </div>
        <?php 
        // Show success/info messages
        if (isset($_GET['logout']) && $_GET['logout'] == '1'): ?>
            <div class="success-message" style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.85rem; text-align: center;">
                <i class="fas fa-check-circle"></i> You have been successfully logged out.
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['timeout']) && $_GET['timeout'] == '1'): ?>
            <div class="warning-message" style="background: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.85rem; text-align: center;">
                <i class="fas fa-clock"></i> Your session has expired. Please login again.
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['inactive']) && $_GET['inactive'] == '1'): ?>
            <div class="error-message" style="background: #f8d7da; border: 1px solid #dc3545; color: #721c24; padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.85rem; text-align: center;">
                <i class="fas fa-ban"></i> Your account is inactive. Please contact the library office.
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="error-message error-message-styled">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="" class="login-form-styled">
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <div style="position: relative;">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" id="email" name="email" class="form-input" 
                           placeholder="Enter your email address" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div style="position: relative;">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="password" name="password" class="form-input" 
                           placeholder="Enter your password" required>
                </div>
                <small style="color: #666; font-size: 0.75rem; margin-top: 0.25rem; display: block;">
                    <i class="fas fa-info-circle"></i> Default password is <strong>123456</strong>
                </small>
            </div>
            <div class="forgot-password">
                <a href="#">Forgot Password?</a>
            </div>
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
    </div>
</body>

</html>

