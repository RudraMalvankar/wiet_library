<?php
/**
 * Simple Footfall System Diagnostic
 * Tests database directly without additional API calls
 */

require_once '../includes/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Footfall Diagnostic</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #263c79 0%, #1a2a52 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .header {
            background: linear-gradient(135deg, #263c79 0%, #1e2d5f 100%);
            color: white;
            padding: 30px;
            border-bottom: 4px solid #cfac69;
        }
        .content { padding: 30px; }
        .test-section {
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #263c79;
        }
        .test-section h2 {
            color: #263c79;
            margin-bottom: 15px;
            font-size: 20px;
        }
        .test-item {
            padding: 12px;
            margin: 8px 0;
            background: white;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .status {
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 13px;
        }
        .status.pass { background: #d1fae5; color: #065f46; }
        .status.fail { background: #fee2e2; color: #991b1b; }
        .test-name { flex: 1; color: #374151; font-weight: 500; }
        .test-result { color: #6b7280; font-size: 14px; }
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid;
        }
        .alert.success { background: #d1fae5; color: #065f46; border-color: #10b981; }
        .alert.error { background: #fee2e2; color: #991b1b; border-color: #ef4444; }
        .alert.warning { background: #fef3c7; color: #92400e; border-color: #f59e0b; }
        .btn {
            background: linear-gradient(135deg, #263c79 0%, #1e2d5f 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(38, 60, 121, 0.4); }
        code { background: #e2e8f0; padding: 2px 6px; border-radius: 4px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Footfall System Diagnostic</h1>
            <p>Direct database check - No API required</p>
        </div>
        
        <div class="content">
            <?php
            $allPassed = true;
            $issues = [];
            
            try {
                // Test 1: Check if Footfall table exists
                echo '<div class="test-section">';
                echo '<h2>📊 Database Tests</h2>';
                
                $stmt = $conn->query("SHOW TABLES LIKE 'Footfall'");
                $tableExists = $stmt->rowCount() > 0;
                
                echo '<div class="test-item">';
                echo '<span class="status ' . ($tableExists ? 'pass' : 'fail') . '">' . ($tableExists ? 'PASS' : 'FAIL') . '</span>';
                echo '<span class="test-name">Footfall table exists</span>';
                echo '<span class="test-result">' . ($tableExists ? '✓ Table found' : '✗ Table missing') . '</span>';
                echo '</div>';
                
                if (!$tableExists) {
                    $allPassed = false;
                    $issues[] = "Footfall table does not exist";
                }
                
                // Test 2: Check required columns
                if ($tableExists) {
                    $stmt = $conn->query("DESCRIBE Footfall");
                    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
                    
                    $requiredColumns = ['EntryTime', 'ExitTime', 'Purpose', 'Status', 'EntryMethod', 'WorkstationUsed'];
                    $missing = array_diff($requiredColumns, $columns);
                    $columnsPassed = count($missing) === 0;
                    
                    echo '<div class="test-item">';
                    echo '<span class="status ' . ($columnsPassed ? 'pass' : 'fail') . '">' . ($columnsPassed ? 'PASS' : 'FAIL') . '</span>';
                    echo '<span class="test-name">Required columns exist</span>';
                    echo '<span class="test-result">' . ($columnsPassed 
                        ? '✓ All 6 columns: ' . implode(', ', $requiredColumns)
                        : '✗ Missing: ' . implode(', ', $missing)) . '</span>';
                    echo '</div>';
                    
                    if (!$columnsPassed) {
                        $allPassed = false;
                        $issues[] = "Missing columns: " . implode(', ', $missing);
                    }
                    
                    // Test 3: Check SQL Views
                    $stmt = $conn->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
                    $views = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
                    
                    $requiredViews = ['FootfallDailyStats', 'FootfallHourlyStats', 'MemberFootfallSummary'];
                    $missingViews = array_diff($requiredViews, $views);
                    $viewsPassed = count($missingViews) === 0;
                    
                    echo '<div class="test-item">';
                    echo '<span class="status ' . ($viewsPassed ? 'pass' : 'fail') . '">' . ($viewsPassed ? 'PASS' : 'FAIL') . '</span>';
                    echo '<span class="test-name">SQL Views created</span>';
                    echo '<span class="test-result">' . ($viewsPassed 
                        ? '✓ All 3 views exist'
                        : '✗ Missing: ' . implode(', ', $missingViews)) . '</span>';
                    echo '</div>';
                    
                    if (!$viewsPassed) {
                        $allPassed = false;
                        $issues[] = "Missing SQL views: " . implode(', ', $missingViews);
                    }
                    
                    // Test 4: Check for data
                    $stmt = $conn->query("SELECT COUNT(*) as total FROM footfall");
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $hasData = $result['total'] > 0;
                    
                    echo '<div class="test-item">';
                    echo '<span class="status ' . ($hasData ? 'pass' : 'fail') . '">' . ($hasData ? 'PASS' : 'FAIL') . '</span>';
                    echo '<span class="test-name">Sample data exists</span>';
                    echo '<span class="test-result">' . ($hasData 
                        ? '✓ Found ' . $result['total'] . ' records'
                        : '✗ No data in table') . '</span>';
                    echo '</div>';
                    
                    // Test 5: Check if migration was applied (data in new columns)
                    if ($hasData && $columnsPassed) {
                        $stmt = $conn->query("
                            SELECT 
                                COUNT(*) as total,
                                SUM(CASE WHEN EntryTime IS NOT NULL THEN 1 ELSE 0 END) as has_entry_time,
                                SUM(CASE WHEN Status IS NOT NULL THEN 1 ELSE 0 END) as has_status
                            FROM footfall
                        ");
                        $data = $stmt->fetch(PDO::FETCH_ASSOC);
                        $migrationApplied = $data['has_entry_time'] > 0 && $data['has_status'] > 0;
                        
                        echo '<div class="test-item">';
                        echo '<span class="status ' . ($migrationApplied ? 'pass' : 'fail') . '">' . ($migrationApplied ? 'PASS' : 'FAIL') . '</span>';
                        echo '<span class="test-name">Migration applied (data in new columns)</span>';
                        echo '<span class="test-result">' . ($migrationApplied 
                            ? "✓ {$data['has_entry_time']}/{$data['total']} records migrated"
                            : '✗ Columns exist but no data') . '</span>';
                        echo '</div>';
                        
                        if (!$migrationApplied) {
                            $allPassed = false;
                            $issues[] = "Migration not fully applied - columns exist but empty";
                        }
                    }
                }
                
                echo '</div>';
                
                // Summary
                if ($allPassed && $tableExists) {
                    echo '<div class="alert success">';
                    echo '<strong>✅ ALL TESTS PASSED!</strong><br>';
                    echo 'Database is correctly configured. Footfall system is ready to use.';
                    echo '</div>';
                    
                    echo '<div style="margin-top: 20px;">';
                    echo '<a href="footfall-analytics.php" class="btn">Open Footfall Dashboard</a>';
                    echo '<a href="../footfall/scanner.php" class="btn">Open Scanner</a>';
                    echo '</div>';
                } else {
                    echo '<div class="alert error">';
                    echo '<strong>❌ ISSUES FOUND:</strong><br>';
                    echo '<ul style="margin-top: 10px; margin-left: 20px;">';
                    foreach ($issues as $issue) {
                        echo "<li>{$issue}</li>";
                    }
                    echo '</ul>';
                    echo '</div>';
                    
                    echo '<div class="alert warning">';
                    echo '<strong>🔧 HOW TO FIX:</strong><br>';
                    echo '<ol style="margin-top: 10px; margin-left: 20px;">';
                    echo '<li>Click button below to run migration automatically, OR</li>';
                    echo '<li>Open <code>database/migrations/006_enhance_footfall_tracking.sql</code></li>';
                    echo '<li>Copy contents and run in phpMyAdmin SQL tab</li>';
                    echo '</ol>';
                    echo '</div>';
                    
                    echo '<div style="margin-top: 20px;">';
                    echo '<a href="run-migration-page.php" class="btn">🚀 Run Migration Now</a>';
                    echo '<a href="check-database.php" class="btn">📊 Detailed Database View</a>';
                    echo '</div>';
                }
                
            } catch (PDOException $e) {
                echo '<div class="alert error">';
                echo '<strong>❌ DATABASE ERROR:</strong><br>';
                echo htmlspecialchars($e->getMessage());
                echo '</div>';
                
                echo '<div class="alert warning">';
                echo '<strong>⚠️ CHECK:</strong>';
                echo '<ul style="margin-top: 10px; margin-left: 20px;">';
                echo '<li>Is XAMPP MySQL running?</li>';
                echo '<li>Check <code>includes/db_connect.php</code> settings</li>';
                echo '<li>Database name: <code>wiet_library</code></li>';
                echo '</ul>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</body>
</html>

