<?php
// Recent Visitors API
header('Content-Type: application/json');
require_once '../../includes/db_connect.php';

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$limit = min($limit, 50); // Max 50

try {
    $stmt = $pdo->prepare("
        SELECT 
            m.MemberName as name,
            f.EntryTime,
            f.Purpose,
            s.Branch,
            f.EntryMethod
        FROM footfall f
        INNER JOIN Member m ON f.MemberNo = m.MemberNo
        LEFT JOIN Student s ON m.MemberNo = s.MemberNo
        WHERE DATE(f.EntryTime) = CURDATE()
        ORDER BY f.EntryTime DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $visitors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $formatted = array_map(function($v) {
        return [
            'name' => $v['name'],
            'time' => date('h:i A', strtotime($v['EntryTime'])),
            'purpose' => $v['Purpose'],
            'branch' => $v['Branch'] ?? 'N/A',
            'method' => $v['EntryMethod']
        ];
    }, $visitors);
    
    echo json_encode([
        'success' => true,
        'visitors' => $formatted,
        'count' => count($formatted)
    ]);
    
} catch (PDOException $e) {
    error_log('Recent visitors error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>

