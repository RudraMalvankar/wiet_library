<?php
/**
 * Quick Database Check Script
 * Run this to see current Footfall table structure
 */

require_once '../includes/db_connect.php';

echo "<html><head><title>DB Check</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1e293b;color:#e2e8f0;} table{border-collapse:collapse;width:100%;margin:20px 0;} th,td{border:1px solid #475569;padding:8px;text-align:left;} th{background:#334155;color:#fbbf24;} .success{color:#10b981;} .error{color:#ef4444;} h2{color:#fbbf24;border-bottom:2px solid #475569;padding-bottom:10px;}</style>";
echo "</head><body>";

try {
    // Check if table exists
    echo "<h2>1. Footfall Table Check</h2>";
    $stmt = $conn->query("SHOW TABLES LIKE 'Footfall'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='success'>✓ Footfall table EXISTS</p>";
        
        // Show table structure
        echo "<h2>2. Table Structure</h2>";
        echo "<table>";
        echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        $stmt = $conn->query("DESCRIBE Footfall");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $requiredColumns = ['EntryTime', 'ExitTime', 'Purpose', 'Status', 'EntryMethod', 'WorkstationUsed'];
        $foundColumns = array_column($columns, 'Field');
        
        foreach ($columns as $col) {
            $highlight = in_array($col['Field'], $requiredColumns) ? " style='background:#064e3b;'" : "";
            echo "<tr{$highlight}>";
            echo "<td><strong>{$col['Field']}</strong></td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td>{$col['Null']}</td>";
            echo "<td>{$col['Key']}</td>";
            echo "<td>{$col['Default']}</td>";
            echo "<td>{$col['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check required columns
        echo "<h2>3. Required Columns Check</h2>";
        $missing = array_diff($requiredColumns, $foundColumns);
        if (count($missing) === 0) {
            echo "<p class='success'>✓ All 6 required columns exist:</p>";
            echo "<ul>";
            foreach ($requiredColumns as $col) {
                echo "<li class='success'>{$col}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p class='error'>✗ Missing columns:</p>";
            echo "<ul>";
            foreach ($missing as $col) {
                echo "<li class='error'>{$col} - MISSING</li>";
            }
            echo "</ul>";
            echo "<p style='color:#fbbf24;'><strong>ACTION REQUIRED:</strong> Run migration file: database/migrations/006_enhance_footfall_tracking.sql</p>";
        }
        
        // Check views
        echo "<h2>4. SQL Views Check</h2>";
        $stmt = $conn->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
        $views = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        
        $requiredViews = ['FootfallDailyStats', 'FootfallHourlyStats', 'MemberFootfallSummary'];
        echo "<table><tr><th>View Name</th><th>Status</th></tr>";
        foreach ($requiredViews as $view) {
            $exists = in_array($view, $views);
            $status = $exists ? "<span class='success'>✓ EXISTS</span>" : "<span class='error'>✗ MISSING</span>";
            echo "<tr><td>{$view}</td><td>{$status}</td></tr>";
        }
        echo "</table>";
        
        // Check indexes
        echo "<h2>5. Indexes Check</h2>";
        $stmt = $conn->query("SHOW INDEX FROM Footfall");
        $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table><tr><th>Index Name</th><th>Column</th><th>Type</th></tr>";
        foreach ($indexes as $idx) {
            $highlight = in_array($idx['Key_name'], ['idx_entry_time', 'idx_status', 'idx_entry_method']) ? " style='background:#064e3b;'" : "";
            echo "<tr{$highlight}>";
            echo "<td>{$idx['Key_name']}</td>";
            echo "<td>{$idx['Column_name']}</td>";
            echo "<td>{$idx['Index_type']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check data
        echo "<h2>6. Data Check</h2>";
        $stmt = $conn->query("SELECT COUNT(*) as total FROM Footfall");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Total records: <strong>{$result['total']}</strong></p>";
        
        if ($result['total'] > 0) {
            $stmt = $conn->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN EntryTime IS NOT NULL THEN 1 ELSE 0 END) as has_entry_time,
                    SUM(CASE WHEN ExitTime IS NOT NULL THEN 1 ELSE 0 END) as has_exit_time,
                    SUM(CASE WHEN Purpose IS NOT NULL THEN 1 ELSE 0 END) as has_purpose,
                    SUM(CASE WHEN Status IS NOT NULL THEN 1 ELSE 0 END) as has_status,
                    SUM(CASE WHEN EntryMethod IS NOT NULL THEN 1 ELSE 0 END) as has_entry_method,
                    SUM(CASE WHEN WorkstationUsed IS NOT NULL THEN 1 ELSE 0 END) as has_workstation
                FROM Footfall
            ");
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<table>";
            echo "<tr><th>Column</th><th>Records with Data</th><th>Percentage</th></tr>";
            echo "<tr><td>EntryTime</td><td>{$data['has_entry_time']}</td><td>" . round($data['has_entry_time']/$data['total']*100) . "%</td></tr>";
            echo "<tr><td>ExitTime</td><td>{$data['has_exit_time']}</td><td>" . round($data['has_exit_time']/$data['total']*100) . "%</td></tr>";
            echo "<tr><td>Purpose</td><td>{$data['has_purpose']}</td><td>" . round($data['has_purpose']/$data['total']*100) . "%</td></tr>";
            echo "<tr><td>Status</td><td>{$data['has_status']}</td><td>" . round($data['has_status']/$data['total']*100) . "%</td></tr>";
            echo "<tr><td>EntryMethod</td><td>{$data['has_entry_method']}</td><td>" . round($data['has_entry_method']/$data['total']*100) . "%</td></tr>";
            echo "<tr><td>WorkstationUsed</td><td>{$data['has_workstation']}</td><td>" . round($data['has_workstation']/$data['total']*100) . "%</td></tr>";
            echo "</table>";
            
            // Sample data
            echo "<h2>7. Sample Data (Last 5 Records)</h2>";
            $stmt = $conn->query("SELECT FootfallID, MemberID, EntryTime, ExitTime, Purpose, Status, EntryMethod FROM Footfall ORDER BY FootfallID DESC LIMIT 5");
            $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table>";
            echo "<tr><th>ID</th><th>Member</th><th>EntryTime</th><th>ExitTime</th><th>Purpose</th><th>Status</th><th>Method</th></tr>";
            foreach ($samples as $row) {
                echo "<tr>";
                echo "<td>{$row['FootfallID']}</td>";
                echo "<td>{$row['MemberID']}</td>";
                echo "<td>" . ($row['EntryTime'] ?? '<span class="error">NULL</span>') . "</td>";
                echo "<td>" . ($row['ExitTime'] ?? '<span style="color:#94a3b8;">NULL</span>') . "</td>";
                echo "<td>" . ($row['Purpose'] ?? '<span class="error">NULL</span>') . "</td>";
                echo "<td>" . ($row['Status'] ?? '<span class="error">NULL</span>') . "</td>";
                echo "<td>" . ($row['EntryMethod'] ?? '<span class="error">NULL</span>') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } else {
        echo "<p class='error'>✗ Footfall table DOES NOT EXIST</p>";
        echo "<p style='color:#fbbf24;'><strong>ACTION REQUIRED:</strong> Run database setup: database/schema.sql</p>";
    }
    
    echo "<h2>8. Summary & Next Steps</h2>";
    
    // Final check
    $allGood = true;
    $issues = [];
    
    if ($stmt->rowCount() == 0) {
        $allGood = false;
        $issues[] = "Footfall table missing";
    }
    
    if (isset($missing) && count($missing) > 0) {
        $allGood = false;
        $issues[] = count($missing) . " required columns missing";
    }
    
    if (isset($views) && count(array_diff($requiredViews, $views)) > 0) {
        $allGood = false;
        $issues[] = count(array_diff($requiredViews, $views)) . " SQL views missing";
    }
    
    if ($allGood) {
        echo "<p class='success' style='font-size:18px;'>✓ ALL CHECKS PASSED - Database is ready!</p>";
    } else {
        echo "<p class='error' style='font-size:18px;'>✗ ISSUES FOUND:</p>";
        echo "<ul>";
        foreach ($issues as $issue) {
            echo "<li class='error'>{$issue}</li>";
        }
        echo "</ul>";
        echo "<p style='color:#fbbf24;'><strong>TO FIX:</strong></p>";
        echo "<ol style='color:#cbd5e1;'>";
        echo "<li>Open phpMyAdmin</li>";
        echo "<li>Select 'wiet_library' database</li>";
        echo "<li>Go to SQL tab</li>";
        echo "<li>Copy contents of: <code>database/migrations/006_enhance_footfall_tracking.sql</code></li>";
        echo "<li>Paste and click 'Go'</li>";
        echo "<li>Refresh this page to verify</li>";
        echo "</ol>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>ERROR: " . $e->getMessage() . "</p>";
}

echo "</body></html>";
