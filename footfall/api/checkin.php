<?php
// Check-in API - Process library entry
header('Content-Type: application/json');
require_once '../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$memberIdentifier = $input['member_identifier'] ?? '';
$entryMethod = $input['entry_method'] ?? 'Manual';
$purpose = $input['purpose'] ?? 'Library Visit';

if (empty($memberIdentifier)) {
    echo json_encode(['success' => false, 'message' => 'Member identifier is required']);
    exit;
}

try {
    // Find member by MemberNo or PRN (Student ID)
    $stmt = $pdo->prepare("
        SELECT 
            m.MemberNo,
            m.MemberName,
            s.Branch,
            s.CourseName,
            s.PRN,
            s.Photo
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
        echo json_encode(['success' => false, 'message' => 'Member not found. Please check your Member Number.']);
        exit;
    }
    
    $memberNo = $member['MemberNo'];
    
    // Check if already checked in today
    $checkStmt = $pdo->prepare("
        SELECT FootfallID, EntryTime 
        FROM footfall 
        WHERE MemberNo = :member_no 
        AND DATE(EntryTime) = CURDATE()
        AND Status = 'Active'
        ORDER BY EntryTime DESC
        LIMIT 1
    ");
    $checkStmt->execute(['member_no' => $memberNo]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        $entryTime = date('h:i A', strtotime($existing['EntryTime']));
        echo json_encode([
            'success' => false, 
            'message' => "Already checked in today at {$entryTime}. Please check out first."
        ]);
        exit;
    }
    
    // Insert new footfall entry
    $insertStmt = $pdo->prepare("
        INSERT INTO footfall 
        (MemberNo, Date, TimeIn, EntryTime, Purpose, Status, EntryMethod) 
        VALUES 
        (:member_no, CURDATE(), CURTIME(), NOW(), :purpose, 'Active', :entry_method)
    ");
    
    $insertStmt->execute([
        'member_no' => $memberNo,
        'purpose' => $purpose,
        'entry_method' => $entryMethod
    ]);
    
    // Prepare response with member info
    $response = [
        'success' => true,
        'message' => "Welcome, {$member['MemberName']}! Check-in successful.",
        'member' => [
            'member_no' => 'M' . str_pad($memberNo, 7, '0', STR_PAD_LEFT),
            'name' => $member['MemberName'],
            'branch' => $member['Branch'] ?? 'N/A',
            'course' => $member['CourseName'] ?? 'N/A',
            'photo' => $member['Photo'] ?? null
        ],
        'entry_time' => date('Y-m-d H:i:s'),
        'footfall_id' => $pdo->lastInsertId()
    ];
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    error_log('Check-in error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
}
?>

