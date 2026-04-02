<?php
// Entry point for /student directory
// Redirect unauthenticated users to login and authenticated users to dashboard.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: student_login.php');
    exit();
}

header('Location: dashboard.php');
exit();
