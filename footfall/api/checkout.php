<?php
// Check-out API - Process library exit
header('Content-Type: application/json');
require_once '../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$memberIdentifier = $input['member_identifier'] ?? '';

if (empty($memberIdentifier)) {
    echo json_encode(['success' => false, 'message' => 'Member identifier is required']);
    exit;
}

try {
    // Find member
    $stmt = $pdo->prepare("
        SELECT m.MemberNo, m.MemberName
        FROM Member m
        LEFT JOIN Student s ON m.MemberNo = s.MemberNo
        WHERE m.MemberNo = :identifier 
        OR s.PRN = :identifier
        OR s.StudentID = :identifier
        LIMIT 1
    ");
    $stmt->execute(['identifier' => $memberIdentifier]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$member) {
        echo json_encode(['success' => false, 'message' => 'Member not found']);
        exit;
    }
    
    $memberNo = $member['MemberNo'];
    
    // Find active entry for today
    $entryStmt = $pdo->prepare("
        SELECT FootfallID, EntryTime 
        FROM footfall 
        WHERE MemberNo = :member_no 
        AND DATE(EntryTime) = CURDATE()
        AND Status = 'Active'
        ORDER BY EntryTime DESC
        LIMIT 1
    ");
    $entryStmt->execute(['member_no' => $memberNo]);
    $entry = $entryStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$entry) {
        echo json_encode([
            'success' => false, 
            'message' => 'No active check-in found for today'
        ]);
        exit;
    }
    
    // Update with check-out time
    $updateStmt = $pdo->prepare("
        UPDATE footfall 
        SET 
            TimeOut = CURTIME(),
            ExitTime = NOW(),
            Duration = TIMESTAMPDIFF(MINUTE, EntryTime, NOW()),
            Status = 'Completed'
        WHERE FootfallID = :footfall_id
    ");
    
    $updateStmt->execute(['footfall_id' => $entry['FootfallID']]);
    
    // Calculate duration
    $entryTime = new DateTime($entry['EntryTime']);
    $exitTime = new DateTime();
    $interval = $entryTime->diff($exitTime);
    $duration = sprintf('%dh %dm', $interval->h + ($interval->days * 24), $interval->i);
    
    echo json_encode([
        'success' => true,
        'message' => "Goodbye, {$member['MemberName']}! Check-out successful.",
        'duration' => $duration,
        'entry_time' => $entry['EntryTime'],
        'exit_time' => date('Y-m-d H:i:s')
    ]);
    
} catch (PDOException $e) {
    error_log('Check-out error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
}
?>

