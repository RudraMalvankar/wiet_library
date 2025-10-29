<?php
/**
 * Student Logout
 * Destroys session and redirects to login page
 */

// Start session
session_start();

// Get student info for logging (before destroying session)
$student_id = $_SESSION['student_id'] ?? null;

// Log logout activity if possible
if ($student_id) {
    try {
        require_once '../includes/db_connect.php';
        
        $stmt = $pdo->prepare("
            INSERT INTO ActivityLog (UserID, UserType, Action, Details, IPAddress)
            VALUES (?, 'Student', 'Logout', 'Student logged out from portal', ?)
        ");
        $stmt->execute([
            $student_id,
            $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
        ]);
    } catch (PDOException $e) {
        // Silently fail - logout should still proceed
        error_log("Logout activity log error: " . $e->getMessage());
    }
}

// Destroy all session data
session_unset();
session_destroy();

// Redirect to login page with logout message
header('Location: student_login.php?logout=1');
exit();
?>

