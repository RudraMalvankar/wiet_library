<?php
/**
 * Student Session Check
 * Include at the top of every student page to ensure authentication
 * 
 * This file validates that:
 * 1. Student is logged in
 * 2. Session hasn't expired (30 minutes timeout)
 * 3. Session variables are properly set
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if student is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['student_id'])) {
    // Not logged in, redirect to login page
    header('Location: student_login.php');
    exit();
}

// Check session timeout (30 minutes = 1800 seconds)
$timeout = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    // Session expired
    session_unset();
    session_destroy();
    header('Location: student_login.php?timeout=1');
    exit();
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Get student information for use in pages
$student_id = $_SESSION['student_id'] ?? null;
$member_no = $_SESSION['member_no'] ?? null;
$student_name = $_SESSION['student_name'] ?? 'Student';
$student_email = $_SESSION['student_email'] ?? '';
$student_branch = $_SESSION['student_branch'] ?? '';
$student_course = $_SESSION['student_course'] ?? '';
$student_prn = $_SESSION['student_prn'] ?? '';
$books_issued = $_SESSION['books_issued'] ?? 0;

// Optional: Refresh session data from database periodically (every 5 minutes)
if (!isset($_SESSION['last_refresh']) || (time() - $_SESSION['last_refresh']) > 300) {
    try {
        require_once '../includes/db_connect.php';
        
        $stmt = $pdo->prepare("
            SELECT 
                s.StudentID,
                s.MemberNo,
                s.Email,
                s.Branch,
                s.CourseName,
                s.PRN,
                m.MemberName,
                m.Status,
                m.BooksIssued
            FROM Student s
            INNER JOIN Member m ON s.MemberNo = m.MemberNo
            WHERE s.StudentID = ? AND m.Status = 'Active'
            LIMIT 1
        ");
        
        $stmt->execute([$student_id]);
        $student_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($student_data) {
            // Update session with fresh data
            $_SESSION['books_issued'] = $student_data['BooksIssued'];
            $_SESSION['last_refresh'] = time();
        } else {
            // Student no longer active, logout
            session_unset();
            session_destroy();
            header('Location: student_login.php?inactive=1');
            exit();
        }
    } catch (PDOException $e) {
        // Log error but don't interrupt user experience
        error_log("Session refresh error: " . $e->getMessage());
    }
}
?>

