<?php
// Test footfall-records API
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../includes/db_connect.php';

echo "<h1>Testing Footfall Records API</h1>";

$dateFrom = '2025-01-01';
$dateTo = '2025-12-31';

try {
    // Test query
    $sql = "
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
        WHERE DATE(f.EntryTime) BETWEEN :date_from AND :date_to
        ORDER BY f.EntryTime DESC
        LIMIT 10
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'date_from' => $dateFrom,
        'date_to' => $dateTo
    ]);
    
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p style='color: green;'>✓ Query successful! Found " . count($records) . " records</p>";
    
    if (count($records) > 0) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>Member</th><th>Name</th><th>Branch</th><th>Entry</th><th>Purpose</th><th>Method</th><th>Status</th></tr>";
        foreach ($records as $r) {
            echo "<tr>";
            echo "<td>" . $r['MemberNo'] . "</td>";
            echo "<td>" . $r['name'] . "</td>";
            echo "<td>" . ($r['Branch'] ?? 'N/A') . "</td>";
            echo "<td>" . $r['EntryTime'] . "</td>";
            echo "<td>" . $r['Purpose'] . "</td>";
            echo "<td>" . $r['EntryMethod'] . "</td>";
            echo "<td>" . $r['Status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
