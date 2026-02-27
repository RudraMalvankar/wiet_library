<?php
/**
 * Mark Notification as Read API
 * Updates the IsRead status for admin-created notifications
 */

session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true) ?? [];

// CSRF protection
if (!validateCSRFToken($input['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    http_response_code(403);
    exit();
}

$notification_id = $input['notification_id'] ?? null;
$member_no = $_SESSION['member_no'] ?? null;

if (!$notification_id || !$member_no) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

try {
    // Update notification as read (only if it belongs to this member or is a global notification)
    $stmt = $pdo->prepare("
        UPDATE Notifications 
        SET IsRead = 1 
        WHERE NotificationID = ? 
        AND (MemberNo = ? OR MemberNo IS NULL)
    ");
    
    $stmt->execute([$notification_id, $member_no]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Notification not found or already read']);
    }
    
} catch (PDOException $e) {
    error_log("Mark notification read error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
