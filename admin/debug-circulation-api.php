<?php
session_start();
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['AdminID'])) {
    header('Location: login.php'); exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Full API Debug</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        pre { background: #f5f5f5; padding: 10px; overflow: auto; max-height: 300px; }
        button { padding: 10px 20px; background: #263c79; color: white; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        button:hover { background: #1a2a5a; }
    </style>
</head>
<body>
    <h1>Circulation Stats API Debug Tool</h1>
    
    <div class="section">
        <h2>Step 1: Check Session</h2>
        <div id="sessionCheck">
            <?php
            session_start();
            echo "<pre>";
            echo "Session ID: " . session_id() . "\n";
            echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? "Active" : "Inactive") . "\n";
            echo "\nSession Variables:\n";
            print_r($_SESSION);
            echo "</pre>";
            ?>
        </div>
    </div>
    
    <div class="section">
        <h2>Step 2: Check Database Connection</h2>
        <div id="dbCheck">
            <?php
            try {
                require_once '../includes/db_connect.php';
                echo "<div class='success'><strong>✓ Database Connected Successfully!</strong></div>";
                echo "<pre>";
                echo "Database: " . $pdo->query('SELECT DATABASE()')->fetchColumn() . "\n";
                // Check if tables exist
                $tables = ['Circulation', 'Return', 'Member', 'Books', 'Holding'];
                foreach ($tables as $table) {
                    $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
                    echo "$table: $count records\n";
                }
                echo "</pre>";
            } catch (Exception $e) {
                echo "<div class='error'><strong>✗ Database Error:</strong> " . $e->getMessage() . "</div>";
            }
            ?>
        </div>
    </div>
    
    <div class="section">
        <h2>Step 3: Test Direct Query (PHP)</h2>
        <div id="directQuery">
            <?php
            if (isset($pdo)) {
                try {
                    $today = date('Y-m-d');
                    
                    echo "<strong>Today's date: $today</strong><br><br>";
                    
                    // Books Currently Issued
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Circulation WHERE Status = 'Active'");
                    $stmt->execute();
                    $totalIssued = (int)$stmt->fetchColumn();
                    
                    // Due Today
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Circulation WHERE Status = 'Active' AND DueDate = ?");
                    $stmt->execute([$today]);
                    $dueToday = (int)$stmt->fetchColumn();
                    
                    // Overdue Books
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Circulation WHERE Status = 'Active' AND DueDate < ?");
                    $stmt->execute([$today]);
                    $overdue = (int)$stmt->fetchColumn();
                    
                    // Today's Returns
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `Return` WHERE ReturnDate = ?");
                    $stmt->execute([$today]);
                    $todayReturns = (int)$stmt->fetchColumn();
                    
                    echo "<div class='success'>";
                    echo "<strong>Query Results:</strong><br>";
                    echo "Total Issued: $totalIssued<br>";
                    echo "Due Today: $dueToday<br>";
                    echo "Overdue: $overdue<br>";
                    echo "Today's Returns: $todayReturns<br>";
                    echo "</div>";
                    
                } catch (Exception $e) {
                    echo "<div class='error'><strong>✗ Query Error:</strong> " . $e->getMessage() . "</div>";
                }
            }
            ?>
        </div>
    </div>
    
    <div class="section">
        <h2>Step 4: Test API Call (JavaScript)</h2>
        <button onclick="testAPI()">Test API Endpoint</button>
        <button onclick="testAPIWithCredentials()">Test with Credentials</button>
        <div id="apiResult"></div>
    </div>
    
    <div class="section">
        <h2>Step 5: Direct API Response</h2>
        <p><a href="api/circulation.php?action=stats" target="_blank">Click here to view raw API response</a></p>
    </div>
    
    <script>
        function testAPI() {
            const resultDiv = document.getElementById('apiResult');
            resultDiv.innerHTML = '<p>Testing API...</p>';
            
            console.log('Making API call to: api/circulation.php?action=stats');
            
            fetch('api/circulation.php?action=stats')
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
                    console.log('Response ok:', response.ok);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    
                    return response.text();
                })
                .then(text => {
                    console.log('Raw response:', text);
                    
                    try {
                        const data = JSON.parse(text);
                        resultDiv.innerHTML = `
                            <div class="success">
                                <strong>✓ API Call Successful!</strong>
                                <pre>${JSON.stringify(data, null, 2)}</pre>
                            </div>
                        `;
                    } catch (e) {
                        resultDiv.innerHTML = `
                            <div class="error">
                                <strong>✗ Invalid JSON Response</strong>
                                <pre>${text}</pre>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('API Error:', error);
                    resultDiv.innerHTML = `
                        <div class="error">
                            <strong>✗ API Call Failed</strong>
                            <p>${error.message}</p>
                        </div>
                    `;
                });
        }
        
        function testAPIWithCredentials() {
            const resultDiv = document.getElementById('apiResult');
            resultDiv.innerHTML = '<p>Testing API with credentials...</p>';
            
            fetch('api/circulation.php?action=stats', {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    resultDiv.innerHTML = `
                        <div class="success">
                            <strong>✓ API Call with Credentials Successful!</strong>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </div>
                    `;
                })
                .catch(error => {
                    resultDiv.innerHTML = `
                        <div class="error">
                            <strong>✗ API Call Failed</strong>
                            <p>${error.message}</p>
                        </div>
                    `;
                });
        }
    </script>
</body>
</html>
