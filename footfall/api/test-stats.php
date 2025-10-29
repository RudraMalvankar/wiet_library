<?php
// Test footfall-stats API
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../includes/db_connect.php';

echo "<h1>Testing Footfall Stats API</h1>";

try {
    // Today's visits
    $todayStmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM footfall 
        WHERE DATE(EntryTime) = CURDATE()
    ");
    $todayVisits = $todayStmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<p style='color: green;'>✓ Today's visits: {$todayVisits}</p>";
    
    // Active visitors
    $activeStmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM footfall 
        WHERE Status = 'Active' 
        AND DATE(EntryTime) = CURDATE()
    ");
    $activeVisitors = $activeStmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<p style='color: green;'>✓ Active visitors: {$activeVisitors}</p>";
    
    // This week's visits
    $weekStmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM footfall 
        WHERE YEARWEEK(EntryTime, 1) = YEARWEEK(CURDATE(), 1)
    ");
    $weekVisits = $weekStmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<p style='color: green;'>✓ This week's visits: {$weekVisits}</p>";
    
    // Test the actual API response
    echo "<hr><h2>Testing API Output</h2>";
    $stats = [
        'today_visits' => (int)$todayVisits,
        'active_visitors' => (int)$activeVisitors,
        'week_visits' => (int)$weekVisits
    ];
    
    $response = json_encode(['success' => true, 'stats' => $stats]);
    echo "<p style='color: blue;'>API Response:</p>";
    echo "<pre>" . $response . "</pre>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>
