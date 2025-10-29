<?php
// Analytics Data API - Provides chart data
header('Content-Type: application/json');
require_once '../../includes/db_connect.php';

$dateFrom = $_GET['date_from'] ?? date('Y-m-01');
$dateTo = $_GET['date_to'] ?? date('Y-m-d');

try {
    // Daily visits trend
    $dailyStmt = $pdo->prepare("
        SELECT 
            DATE(EntryTime) as date,
            COUNT(*) as count
        FROM footfall
        WHERE DATE(EntryTime) BETWEEN :date_from AND :date_to
        GROUP BY DATE(EntryTime)
        ORDER BY date ASC
    ");
    $dailyStmt->execute(['date_from' => $dateFrom, 'date_to' => $dateTo]);
    $dailyData = $dailyStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $daily = [
        'labels' => array_map(fn($d) => date('M j', strtotime($d['date'])), $dailyData),
        'values' => array_map(fn($d) => (int)$d['count'], $dailyData)
    ];
    
    // Hourly distribution
    $hourlyStmt = $pdo->prepare("
        SELECT 
            HOUR(EntryTime) as hour,
            COUNT(*) as count
        FROM footfall
        WHERE DATE(EntryTime) BETWEEN :date_from AND :date_to
        GROUP BY HOUR(EntryTime)
        ORDER BY hour ASC
    ");
    $hourlyStmt->execute(['date_from' => $dateFrom, 'date_to' => $dateTo]);
    $hourlyData = $hourlyStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hourly = [
        'labels' => array_map(fn($h) => date('g A', strtotime($h['hour'] . ':00')), $hourlyData),
        'values' => array_map(fn($h) => (int)$h['count'], $hourlyData)
    ];
    
    // Purpose distribution
    $purposeStmt = $pdo->prepare("
        SELECT 
            Purpose,
            COUNT(*) as count
        FROM footfall
        WHERE DATE(EntryTime) BETWEEN :date_from AND :date_to
        GROUP BY Purpose
        ORDER BY count DESC
        LIMIT 10
    ");
    $purposeStmt->execute(['date_from' => $dateFrom, 'date_to' => $dateTo]);
    $purposeData = $purposeStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $purpose = [
        'labels' => array_map(fn($p) => $p['Purpose'], $purposeData),
        'values' => array_map(fn($p) => (int)$p['count'], $purposeData)
    ];
    
    // Branch distribution
    $branchStmt = $pdo->prepare("
        SELECT 
            s.Branch,
            COUNT(*) as count
        FROM footfall f
        INNER JOIN Student s ON f.MemberNo = s.MemberNo
        WHERE DATE(f.EntryTime) BETWEEN :date_from AND :date_to
        AND s.Branch IS NOT NULL
        GROUP BY s.Branch
        ORDER BY count DESC
    ");
    $branchStmt->execute(['date_from' => $dateFrom, 'date_to' => $dateTo]);
    $branchData = $branchStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $branch = [
        'labels' => array_map(fn($b) => $b['Branch'], $branchData),
        'values' => array_map(fn($b) => (int)$b['count'], $branchData)
    ];
    
    echo json_encode([
        'success' => true,
        'data' => [
            'daily' => $daily,
            'hourly' => $hourly,
            'purpose' => $purpose,
            'branch' => $branch
        ]
    ]);
    
} catch (PDOException $e) {
    error_log('Analytics data error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>

