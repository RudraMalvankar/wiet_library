<?php
// Footfall Statistics API
header('Content-Type: application/json');
require_once '../../includes/db_connect.php';

try {
    // Today's visits
    $todayStmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM footfall 
        WHERE DATE(EntryTime) = CURDATE()
    ");
    $todayVisits = $todayStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Active visitors (checked in but not checked out)
    $activeStmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM footfall 
        WHERE Status = 'Active' 
        AND DATE(EntryTime) = CURDATE()
    ");
    $activeVisitors = $activeStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // This week's visits
    $weekStmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM footfall 
        WHERE YEARWEEK(EntryTime, 1) = YEARWEEK(CURDATE(), 1)
    ");
    $weekVisits = $weekStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // This month's visits
    $monthStmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM footfall 
        WHERE MONTH(EntryTime) = MONTH(CURDATE()) 
        AND YEAR(EntryTime) = YEAR(CURDATE())
    ");
    $monthVisits = $monthStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Average duration today
    $avgStmt = $pdo->query("
        SELECT AVG(Duration) as avg_duration 
        FROM footfall 
        WHERE DATE(EntryTime) = CURDATE() 
        AND Duration IS NOT NULL
    ");
    $avgDuration = round($avgStmt->fetch(PDO::FETCH_ASSOC)['avg_duration'] ?? 0);
    
    // Peak hour today
    $peakStmt = $pdo->query("
        SELECT HOUR(EntryTime) as hour, COUNT(*) as count
        FROM footfall 
        WHERE DATE(EntryTime) = CURDATE()
        GROUP BY HOUR(EntryTime)
        ORDER BY count DESC
        LIMIT 1
    ");
    $peakData = $peakStmt->fetch(PDO::FETCH_ASSOC);
    $peakHour = $peakData ? date('g A', strtotime($peakData['hour'] . ':00')) : 'N/A';
    
    echo json_encode([
        'success' => true,
        'data' => [
            'today_visits' => (int)$todayVisits,
            'active_visitors' => (int)$activeVisitors,
            'week_visits' => (int)$weekVisits,
            'month_visits' => (int)$monthVisits,
            'avg_duration_minutes' => (int)$avgDuration,
            'peak_hour' => $peakHour
        ]
    ]);
    
} catch (PDOException $e) {
    error_log('Stats fetch error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>

