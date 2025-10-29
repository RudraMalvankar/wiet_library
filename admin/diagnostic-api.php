<?php
/**
 * Footfall System Diagnostic API
 * Tests database tables, columns, views, and data integrity
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../includes/db_connect.php';

$test = $_GET['test'] ?? '';

try {
    switch ($test) {
        case 'table':
            // Check if Footfall table exists (case-insensitive)
            $stmt = $pdo->query("SHOW TABLES FROM wiet_library");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $exists = in_array('footfall', array_map('strtolower', $tables));
            
            echo json_encode([
                'success' => $exists,
                'message' => $exists ? '✓ Footfall table exists' : '✗ Footfall table not found'
            ]);
            break;
            
        case 'columns':
            // Check required columns exist (case-insensitive)
            $requiredColumns = ['EntryTime', 'ExitTime', 'Purpose', 'Status', 'EntryMethod', 'WorkstationUsed'];
            $stmt = $pdo->query("DESCRIBE footfall");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
            
            $missing = array_diff(array_map('strtolower', $requiredColumns), array_map('strtolower', $columns));
            $success = count($missing) === 0;
            
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => '✓ All 6 required columns exist: ' . implode(', ', $requiredColumns)
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => '✗ Missing columns: ' . implode(', ', $missing)
                ]);
            }
            break;
            
        case 'views':
            // Check SQL Views exist (case-insensitive)
            $requiredViews = ['FootfallDailyStats', 'FootfallHourlyStats', 'MemberFootfallSummary'];
            $stmt = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
            $views = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
            
            // Check case-insensitively
            $viewsLower = array_map('strtolower', $views);
            $requiredLower = array_map('strtolower', $requiredViews);
            $missing = array_diff($requiredLower, $viewsLower);
            $success = count($missing) === 0;
            
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => '✓ All 3 SQL Views exist: ' . implode(', ', $requiredViews)
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => '✗ Missing views: ' . implode(', ', $missing) . ' (Run migration 006)'
                ]);
            }
            break;
            
        case 'data':
            // Check if sample data exists
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM footfall");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = $result['total'];
            
            echo json_encode([
                'success' => $count > 0,
                'message' => $count > 0 
                    ? "✓ Found {$count} footfall records" 
                    : '✗ No footfall data found (table is empty)'
            ]);
            break;
            
        case 'migration':
            // Check migration status - verify all columns have data
            $stmt = $pdo->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN EntryTime IS NOT NULL THEN 1 ELSE 0 END) as has_entry_time,
                    SUM(CASE WHEN Status IS NOT NULL THEN 1 ELSE 0 END) as has_status,
                    SUM(CASE WHEN EntryMethod IS NOT NULL THEN 1 ELSE 0 END) as has_entry_method
                FROM footfall
            ");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['total'] == 0) {
                echo json_encode([
                    'success' => false,
                    'message' => '✗ No data to check migration status'
                ]);
            } else {
                $migrated = ($result['has_entry_time'] > 0 && $result['has_status'] > 0);
                echo json_encode([
                    'success' => $migrated,
                    'message' => $migrated 
                        ? "✓ Migration applied ({$result['has_entry_time']}/{$result['total']} records migrated)"
                        : "✗ Migration not applied (columns exist but no data)"
                ]);
            }
            break;
            
        case 'indexes':
            // Check if indexes exist
            $stmt = $pdo->query("SHOW INDEX FROM footfall WHERE Key_name IN ('idx_entry_time', 'idx_status', 'idx_entry_method')");
            $indexes = $stmt->fetchAll(PDO::FETCH_COLUMN, 2); // Column 2 is Key_name
            $unique = array_unique($indexes);
            
            echo json_encode([
                'success' => count($unique) >= 3,
                'message' => count($unique) >= 3 
                    ? '✓ All 3 performance indexes exist' 
                    : '✗ Missing indexes: ' . (3 - count($unique)) . ' (Run migration 006)'
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => '✗ Invalid test parameter. Use: table, columns, views, data, migration, or indexes'
            ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => '✗ Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '✗ Error: ' . $e->getMessage()
    ]);
}
