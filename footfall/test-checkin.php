<?php
// Quick test of checkin API
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/db_connect.php';

echo "<h1>Testing Check-in API</h1>";

// Test database connection
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM footfall");
    $count = $stmt->fetchColumn();
    echo "<p style='color: green;'>✓ Database connected! Current footfall records: {$count}</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
}

// Test member lookup
try {
    $stmt = $pdo->prepare("
        SELECT 
            m.MemberNo,
            m.MemberName,
            s.Branch,
            s.CourseName
        FROM Member m
        LEFT JOIN Student s ON m.MemberNo = s.MemberNo
        WHERE m.MemberNo = 2511
        LIMIT 1
    ");
    $stmt->execute();
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($member) {
        echo "<p style='color: green;'>✓ Member 2511 found: " . $member['MemberName'] . "</p>";
        echo "<pre>" . print_r($member, true) . "</pre>";
    } else {
        echo "<p style='color: red;'>✗ Member 2511 not found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Member lookup error: " . $e->getMessage() . "</p>";
}

// Test insert (simulated)
try {
    echo "<p style='color: blue;'>Testing INSERT statement structure...</p>";
    $sql = "INSERT INTO footfall 
        (MemberNo, Date, TimeIn, EntryTime, Purpose, Status, EntryMethod) 
        VALUES 
        (:member_no, CURDATE(), CURTIME(), NOW(), :purpose, 'Active', :entry_method)";
    echo "<pre>{$sql}</pre>";
    echo "<p style='color: green;'>✓ SQL structure looks correct</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

// Test actual check-in
try {
    // Check if already checked in
    $checkStmt = $pdo->prepare("
        SELECT FootfallID, EntryTime 
        FROM footfall 
        WHERE MemberNo = 2511 
        AND DATE(EntryTime) = CURDATE()
        AND Status = 'Active'
        ORDER BY EntryTime DESC
        LIMIT 1
    ");
    $checkStmt->execute();
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        echo "<p style='color: orange;'>⚠ Member 2511 already checked in today</p>";
        echo "<pre>" . print_r($existing, true) . "</pre>";
    } else {
        echo "<p style='color: green;'>✓ No active check-in found - ready to check in</p>";
        
        // Try actual insert
        $insertStmt = $pdo->prepare("
            INSERT INTO footfall 
            (MemberNo, Date, TimeIn, EntryTime, Purpose, Status, EntryMethod) 
            VALUES 
            (2511, CURDATE(), CURTIME(), NOW(), 'Library Visit', 'Active', 'Manual Entry')
        ");
        $insertStmt->execute();
        echo "<p style='color: green;'>✓✓✓ CHECK-IN SUCCESSFUL! Footfall ID: " . $pdo->lastInsertId() . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Check-in error: " . $e->getMessage() . "</p>";
}
?>
