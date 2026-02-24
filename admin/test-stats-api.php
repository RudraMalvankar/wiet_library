<?php
// Quick test to check if the stats API is working
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

echo "<h2>Testing Circulation Stats API</h2>";

try {
    $today = date('Y-m-d');
    
    echo "<p><strong>Today's date:</strong> $today</p>";
    
    // Books Currently Issued (Active Circulations)
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM Circulation WHERE Status = 'Active'");
    $stmt->execute();
    $totalIssued = (int)$stmt->fetchColumn();
    echo "<p>Total Issued: $totalIssued</p>";
    
    // Due Today
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM Circulation WHERE Status = 'Active' AND DueDate = ?");
    $stmt->execute([$today]);
    $dueToday = (int)$stmt->fetchColumn();
    echo "<p>Due Today: $dueToday</p>";
    
    // Overdue Books
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM Circulation WHERE Status = 'Active' AND DueDate < ?");
    $stmt->execute([$today]);
    $overdue = (int)$stmt->fetchColumn();
    echo "<p>Overdue: $overdue</p>";
    
    // Today's Returns
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM `Return` WHERE ReturnDate = ?");
    $stmt->execute([$today]);
    $todayReturns = (int)$stmt->fetchColumn();
    echo "<p>Today's Returns: $todayReturns</p>";
    
    echo "<hr>";
    echo "<h3>JSON Output:</h3>";
    $result = [
        'success' => true,
        'data' => [
            'totalIssued' => $totalIssued,
            'dueToday' => $dueToday,
            'overdue' => $overdue,
            'todayReturns' => $todayReturns
        ]
    ];
    echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";
    
    echo "<hr>";
    echo "<h3>Test API Call:</h3>";
    echo "<button onclick=\"testAPI()\">Test API</button>";
    echo "<pre id='result'></pre>";
    
    echo "<script>
    function testAPI() {
        fetch('api/circulation.php?action=stats')
            .then(res => res.json())
            .then(data => {
                document.getElementById('result').textContent = JSON.stringify(data, null, 2);
            })
            .catch(err => {
                document.getElementById('result').textContent = 'Error: ' + err.message;
            });
    }
    </script>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>
