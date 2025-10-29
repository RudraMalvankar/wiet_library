<?php
// Footfall Records API - Provides paginated records
header('Content-Type: application/json');
require_once '../../includes/db_connect.php';

$dateFrom = $_GET['date_from'] ?? date('Y-m-01');
$dateTo = $_GET['date_to'] ?? date('Y-m-d');
$status = $_GET['status'] ?? null; // Filter by Active or Completed
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = min(100, (int)($_GET['limit'] ?? 20));
$offset = ($page - 1) * $limit;

try {
    // Build WHERE clause
    $whereClause = "DATE(EntryTime) BETWEEN :date_from AND :date_to";
    $params = ['date_from' => $dateFrom, 'date_to' => $dateTo];
    
    if ($status) {
        $whereClause .= " AND Status = :status";
        $params['status'] = $status;
    }
    
    // Get total count
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM footfall
        WHERE $whereClause
    ");
    $countStmt->execute($params);
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get records
    $recordsStmt = $pdo->prepare("
        SELECT 
            f.FootfallID,
            f.MemberNo,
            m.MemberName as name,
            s.Branch,
            f.EntryTime,
            f.ExitTime,
            f.Duration,
            f.Purpose,
            f.Status,
            f.EntryMethod
        FROM footfall f
        INNER JOIN Member m ON f.MemberNo = m.MemberNo
        LEFT JOIN Student s ON f.MemberNo = s.MemberNo
        WHERE $whereClause
        ORDER BY f.EntryTime DESC
        LIMIT :limit OFFSET :offset
    ");
    
    // Bind parameters
    foreach ($params as $key => $value) {
        $recordsStmt->bindValue(':' . $key, $value);
    }
    $recordsStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $recordsStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $recordsStmt->execute();
    $records = $recordsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $formatted = array_map(function($r) {
        $duration = '';
        if ($r['Duration']) {
            $hours = floor($r['Duration'] / 60);
            $minutes = $r['Duration'] % 60;
            $duration = sprintf('%dh %dm', $hours, $minutes);
        }
        
        return [
            'member_no' => 'M' . str_pad($r['MemberNo'], 7, '0', STR_PAD_LEFT),
            'name' => $r['name'],
            'branch' => $r['Branch'] ?? 'N/A',
            'entry_time' => date('M j, Y g:i A', strtotime($r['EntryTime'])),
            'exit_time' => $r['ExitTime'] ? date('g:i A', strtotime($r['ExitTime'])) : null,
            'duration' => $duration,
            'purpose' => $r['Purpose'],
            'method' => $r['EntryMethod'],
            'status' => $r['Status']
        ];
    }, $records);
    
    $totalPages = ceil($total / $limit);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'records' => $formatted,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_records' => (int)$total,
                'per_page' => $limit
            ]
        ]
    ]);
    
} catch (PDOException $e) {
    error_log('Records fetch error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>

