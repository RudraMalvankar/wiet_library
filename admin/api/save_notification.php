<?php
/**
 * Save Notification API
 * Saves admin-created notifications to database and sends to students
 */

session_start();
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

require_once '../../includes/db_connect.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$type = $input['Type'] ?? '';
$title = $input['Title'] ?? '';
$message = $input['Message'] ?? '';
$recipients = $input['Recipients'] ?? '';
$status = $input['Status'] ?? 'Draft'; // Draft, Scheduled, or Sent
$admin_id = $_SESSION['admin_id'];

if (!$type || !$title || !$message || !$recipients) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

try {
    $pdo->beginTransaction();
    
    $target_members = [];
    
    // Determine target members based on recipient selection
    if ($recipients === 'All Members') {
        // Get all active members
        $stmt = $pdo->query("SELECT MemberNo FROM Member WHERE Status = 'Active'");
        $target_members = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
    } elseif ($recipients === 'Overdue Members') {
        // Get members with overdue books
        $stmt = $pdo->query("
            SELECT DISTINCT c.MemberNo 
            FROM Circulation c 
            WHERE c.Status = 'Active' 
            AND c.DueDate < CURRENT_DATE
        ");
        $target_members = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
    } elseif ($recipients === 'Specific Members') {
        // For specific members, you'd pass member numbers in the request
        $target_members = $input['MemberNumbers'] ?? [];
        
    } else {
        // Default: send to all members
        $stmt = $pdo->query("SELECT MemberNo FROM Member WHERE Status = 'Active'");
        $target_members = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    // If no target members found, still save as a system-wide notification (MemberNo = NULL)
    if (empty($target_members)) {
        $stmt = $pdo->prepare("
            INSERT INTO Notifications (MemberNo, Title, Message, Type, IsRead, CreatedDate)
            VALUES (NULL, ?, ?, ?, 0, NOW())
        ");
        $stmt->execute([$title, $message, $type]);
        $notification_count = 1;
    } else {
        // Insert notification for each target member
        $stmt = $pdo->prepare("
            INSERT INTO Notifications (MemberNo, Title, Message, Type, IsRead, CreatedDate)
            VALUES (?, ?, ?, ?, 0, NOW())
        ");
        
        $notification_count = 0;
        foreach ($target_members as $member_no) {
            $stmt->execute([$member_no, $title, $message, $type]);
            $notification_count++;
        }
    }
    
    // Log the activity
    $log_stmt = $pdo->prepare("
        INSERT INTO ActivityLog (UserID, UserType, Action, Details, Timestamp)
        VALUES (?, 'Admin', 'Notification Sent', ?, NOW())
    ");
    $log_stmt->execute([
        $admin_id,
        "Sent notification '{$title}' to {$notification_count} recipients"
    ]);
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => "Notification sent to {$notification_count} recipient(s)",
        'count' => $notification_count
    ]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Save notification error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
