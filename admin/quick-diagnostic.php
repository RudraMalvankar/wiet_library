<?php
session_start();
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['AdminID'])) {
    header('Location: login.php'); exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quick Diagnostic - API Test</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .test { margin: 20px 0; padding: 15px; background: #f8f9fa; border-left: 4px solid #263c79; }
        .success { border-color: #28a745; background: #d4edda; }
        .error { border-color: #dc3545; background: #f8d7da; }
        button { padding: 10px 20px; background: #263c79; color: white; border: none; cursor: pointer; margin: 5px; }
        pre { background: white; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔍  Quick API Diagnostic</h1>
    
    <div class="test">
        <h3>Current Page Info:</h3>
        <pre id="pageInfo"></pre>
    </div>
    
    <div class="test">
        <h3>Test API Calls:</h3>
        <button onclick="testAllAPIs()">Test All APIs</button>
        <div id="results"></div>
    </div>
    
    <script>
        // Display page info
        document.getElementById('pageInfo').textContent = `
URL: ${window.location.href}
Path: ${window.location.pathname}
Hash: ${window.location.hash}
        `;
        
        function getApiPath(apiFile) {
            const currentPath = window.location.pathname;
            if (currentPath.includes('/admin/layout.php') || currentPath.includes('/admin/layout2.php')) {
                return '/wiet_lib/admin/' + apiFile;
            } else if (currentPath.includes('/admin/')) {
                return apiFile;
            } else {
                return '/wiet_lib/admin/' + apiFile;
            }
        }
        
        async function testAPI(name, path) {
            const resultsDiv = document.getElementById('results');
            const testDiv = document.createElement('div');
            testDiv.className = 'test';
            testDiv.innerHTML = `<strong>${name}</strong><br>Testing: ${path}<br>Loading...`;
            resultsDiv.appendChild(testDiv);
            
            try {
                const url = getApiPath(path);
                console.log(`Testing ${name} at: ${url}`);
                
                const response = await fetch(url);
                const text = await response.text();
                
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    testDiv.className = 'test error';
                    testDiv.innerHTML = `<strong>❌ ${name}</strong><br>
                        URL: ${url}<br>
                        Status: ${response.status}<br>
                        Error: Not valid JSON<br>
                        <pre>${text.substring(0, 500)}</pre>`;
                    return;
                }
                
                if (data.success || data.data) {
                    testDiv.className = 'test success';
                    testDiv.innerHTML = `<strong>✅ ${name}</strong><br>
                        URL: ${url}<br>
                        Status: ${response.status}<br>
                        <pre>${JSON.stringify(data, null, 2).substring(0, 400)}...</pre>`;
                } else {
                    testDiv.className = 'test error';                    testDiv.innerHTML = `<strong>⚠️ ${name}</strong><br>
                        URL: ${url}<br>
                        Status: ${response.status}<br>
                        API returned success=false<br>
                        <pre>${JSON.stringify(data, null, 2)}</pre>`;
                }
            } catch (error) {
                testDiv.className = 'test error';
                testDiv.innerHTML = `<strong>❌ ${name}</strong><br>Error: ${error.message}`;
            }
        }
        
        async function testAllAPIs() {
            document.getElementById('results').innerHTML = '';
            
            await testAPI('Circulation Stats', 'api/circulation.php?action=stats');
            await testAPI('Books Stats', 'api/books.php?action=stats');
            await testAPI('Dashboard Stats', 'api/dashboard.php');
            await testAPI('Members List', 'api/members.php?action=list');
        }
    </script>
</body>
</html>
