<?php
/**
 * Unified Admin Login Page
 * Single login for all admin roles (Super Admin, Librarian, Assistant, etc.)
 */

// Start output buffering to allow headers
ob_start();

require_once 'auth_system.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Location: dashboard.php', true, 302);
    exit();
}

$error_message = "";
$success_message = "";

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error_message = "Please enter both email and password.";
    } else {
        // Validate credentials
        $admin = validateAdminCredentials($email, $password);
        
        if ($admin) {
            // Initialize session
            initializeAdminSession($admin);
            
            // Clear ALL output buffers
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Redirect to layout.php
            header("Location: layout.php");
            exit();
        } else {
            $error_message = "Invalid email or password. Please check your credentials.";
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
    <title>Admin Login - WIET Library Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Lato", sans-serif;
            font-weight: 400;
            background: linear-gradient(135deg, #263c79 0%, #1a2a5a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: "Poppins", sans-serif;
            font-weight: 700;
        }

        .login-container {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            border: 3px solid #cfac69;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #cfac69 0%, #263c79 100%);
        }

        .logo-section {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-section i {
            font-size: 4rem;
            color: #263c79;
            margin-bottom: 1rem;
            display: inline-block;
            animation: fadeInDown 0.6s ease-out;
        }

        .logo-section h2 {
            color: #263c79;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            animation: fadeInUp 0.6s ease-out;
        }

        .logo-section p {
            color: #666;
            font-size: 0.95rem;
            animation: fadeInUp 0.6s ease-out 0.1s both;
        }

        .form-group {
            margin-bottom: 1.5rem;
            animation: fadeInUp 0.6s ease-out 0.2s both;
        }

        .form-group label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-group label i {
            margin-right: 0.5rem;
            color: #cfac69;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 1.1rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.9rem 1rem 0.9rem 3rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: "Lato", sans-serif;
        }

        .form-group input:focus {
            outline: none;
            border-color: #263c79;
            box-shadow: 0 0 0 3px rgba(38, 60, 121, 0.1);
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            font-size: 1.1rem;
            z-index: 10;
        }

        .password-toggle:hover {
            color: #263c79;
        }

        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            animation: fadeInDown 0.4s ease-out;
        }

        .alert-error {
            background-color: #fee;
            border: 1px solid #fcc;
            color: #c00;
        }

        .alert-success {
            background-color: #efe;
            border: 1px solid #cfc;
            color: #0c0;
        }

        .alert i {
            margin-right: 0.5rem;
        }

        .login-button {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #263c79 0%, #1a2a5a 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: "Poppins", sans-serif;
            animation: fadeInUp 0.6s ease-out 0.3s both;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(38, 60, 121, 0.3);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .footer-text {
            text-align: center;
            margin-top: 1.5rem;
            color: #666;
            font-size: 0.85rem;
            animation: fadeInUp 0.6s ease-out 0.4s both;
        }

        .role-info {
            background: linear-gradient(135deg, #f5f5f5 0%, #e9e9e9 100%);
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            text-align: center;
            animation: fadeInUp 0.6s ease-out 0.15s both;
        }

        .role-info p {
            color: #555;
            font-size: 0.9rem;
            margin: 0;
        }

        .role-info strong {
            color: #263c79;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 1.5rem;
            }

            .logo-section h2 {
                font-size: 1.5rem;
            }

            .logo-section i {
                font-size: 3rem;
            }
        }

        .loading {
            display: none;
            text-align: center;
            margin-top: 1rem;
        }

        .loading.active {
            display: block;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #263c79;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-section">
            <i class="fas fa-book-reader"></i>
            <h2>WIET Library</h2>
            <p>Management System</p>
        </div>

        <div class="role-info">
            <p><i class="fas fa-info-circle"></i> <strong>Unified Login</strong> - All admin roles use this page</p>
        </div>

        <?php if ($error_message): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($success_message); ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> Email Address
                </label>
                <div class="input-wrapper">
                    <i class="fas fa-user"></i>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="Enter your email"
                        required 
                        autocomplete="email"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    />
                </div>
            </div>

            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> Password
                </label>
                <div class="input-wrapper">
                    <i class="fas fa-key"></i>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter your password"
                        required 
                        autocomplete="current-password"
                    />
                    <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                </div>
            </div>

            <button type="submit" class="login-button" id="loginBtn">
                <i class="fas fa-sign-in-alt"></i> Login to Dashboard
            </button>

            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p style="margin-top: 0.5rem; color: #666;">Authenticating...</p>
            </div>
        </form>

        <div class="footer-text">
            <i class="fas fa-shield-alt"></i> Secure Login Portal<br>
            <small>&copy; <?php echo date('Y'); ?> WIET Library. All rights reserved.</small>
        </div>
    </div>

    <script>
        // Password toggle
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function() {
            document.getElementById('loginBtn').disabled = true;
            document.getElementById('loading').classList.add('active');
        });

        // Auto-focus email field
        document.getElementById('email').focus();
    </script>
</body>
</html>
