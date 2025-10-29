<?php
/**
 * Activity Log API
 * Provides endpoints for activity log data
 */

session_start();
header('Content-Type: application/json');

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../../includes/db_connect.php';

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'stats':
            getStatistics($pdo);
            break;
            
        case 'list':
            getActivityLogs($pdo);
            break;
            
        case 'export':
            exportActivityLogs($pdo);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (PDOException $e) {
    error_log("Activity Log API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

/**
 * Get activity log statistics
 */
function getStatistics($pdo) {
    // Total logs
    $total_stmt = $pdo->query("SELECT COUNT(*) as total FROM ActivityLog");
    $total_logs = $total_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Today's logs
    $today_stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM ActivityLog 
        WHERE DATE(Timestamp) = CURDATE()
    ");
    $today_logs = $today_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Active users in last 24 hours
    $active_users_stmt = $pdo->query("
        SELECT COUNT(DISTINCT UserID) as total 
        FROM ActivityLog 
        WHERE Timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ");
    $active_users = $active_users_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Average daily logs (last 30 days)
    $avg_stmt = $pdo->query("
        SELECT AVG(daily_count) as avg_daily
        FROM (
            SELECT DATE(Timestamp) as log_date, COUNT(*) as daily_count
            FROM ActivityLog
            WHERE Timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(Timestamp)
        ) as daily_stats
    ");
    $avg_result = $avg_stmt->fetch(PDO::FETCH_ASSOC);
    $avg_daily_logs = round($avg_result['avg_daily'] ?? 0);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'total_logs' => (int)$total_logs,
            'today_logs' => (int)$today_logs,
            'active_users_24h' => (int)$active_users,
            'avg_daily_logs' => (int)$avg_daily_logs
        ]
    ]);
}

/**
 * Get paginated activity logs with filters
 */
function getActivityLogs($pdo) {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $offset = ($page - 1) * $limit;
    
    // Build WHERE clause based on filters
    $where_conditions = [];
    $params = [];
    
    if (!empty($_GET['user_type'])) {
        $where_conditions[] = "UserType = ?";
        $params[] = $_GET['user_type'];
    }
    
    if (!empty($_GET['action'])) {
        $where_conditions[] = "Action LIKE ?";
        $params[] = '%' . $_GET['action'] . '%';
    }
    
    if (!empty($_GET['from_date'])) {
        $where_conditions[] = "DATE(Timestamp) >= ?";
        $params[] = $_GET['from_date'];
    }
    
    if (!empty($_GET['to_date'])) {
        $where_conditions[] = "DATE(Timestamp) <= ?";
        $params[] = $_GET['to_date'];
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Get total count
    $count_sql = "SELECT COUNT(*) as total FROM ActivityLog $where_clause";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_logs = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get logs with user names
    $sql = "
        SELECT 
            al.LogID,
            al.UserID,
            al.UserType,
            al.Action,
            al.Details,
            al.IPAddress,
            al.Timestamp,
            CASE 
                WHEN al.UserType = 'Admin' THEN a.Name
                WHEN al.UserType = 'Student' THEN m.MemberName
                ELSE 'System'
            END as UserName
        FROM ActivityLog al
        LEFT JOIN Admin a ON al.UserID = a.AdminID AND al.UserType = 'Admin'
        LEFT JOIN Member m ON al.UserID = m.MemberNo AND al.UserType = 'Student'
        $where_clause
        ORDER BY al.Timestamp DESC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_pages = ceil($total_logs / $limit);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'logs' => $logs,
            'total_logs' => (int)$total_logs,
            'current_page' => $page,
            'total_pages' => (int)$total_pages,
            'per_page' => $limit
        ]
    ]);
}

/**
 * Export activity logs to CSV
 */
function exportActivityLogs($pdo) {
    // Build WHERE clause based on filters (same as list)
    $where_conditions = [];
    $params = [];
    
    if (!empty($_GET['user_type'])) {
        $where_conditions[] = "UserType = ?";
        $params[] = $_GET['user_type'];
    }
    
    if (!empty($_GET['action'])) {
        $where_conditions[] = "Action LIKE ?";
        $params[] = '%' . $_GET['action'] . '%';
    }
    
    if (!empty($_GET['from_date'])) {
        $where_conditions[] = "DATE(Timestamp) >= ?";
        $params[] = $_GET['from_date'];
    }
    
    if (!empty($_GET['to_date'])) {
        $where_conditions[] = "DATE(Timestamp) <= ?";
        $params[] = $_GET['to_date'];
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Get all logs (up to 10000)
    $sql = "
        SELECT 
            al.LogID,
            al.Timestamp,
            al.UserType,
            CASE 
                WHEN al.UserType = 'Admin' THEN a.Name
                WHEN al.UserType = 'Student' THEN m.MemberName
                ELSE 'System'
            END as UserName,
            al.Action,
            al.Details,
            al.IPAddress
        FROM ActivityLog al
        LEFT JOIN Admin a ON al.UserID = a.AdminID AND al.UserType = 'Admin'
        LEFT JOIN Member m ON al.UserID = m.MemberNo AND al.UserType = 'Student'
        $where_clause
        ORDER BY al.Timestamp DESC
        LIMIT 10000
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Set CSV headers
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="activity_log_' . date('Y-m-d_His') . '.csv"');
    
    // Output CSV
    $output = fopen('php://output', 'w');
    
    // CSV header row
    fputcsv($output, ['Log ID', 'Timestamp', 'User Type', 'User Name', 'Action', 'Details', 'IP Address']);
    
    // CSV data rows
    foreach ($logs as $log) {
        fputcsv($output, [
            $log['LogID'],
            $log['Timestamp'],
            $log['UserType'],
            $log['UserName'] ?? 'Unknown',
            $log['Action'],
            $log['Details'] ?? '',
            $log['IPAddress'] ?? ''
        ]);
    }
    
    fclose($output);
    exit();
}
?>
