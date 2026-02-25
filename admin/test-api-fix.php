<!DOCTYPE html>
<html>
<head>
    <title>API Path Fix Test</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .test-section { background: white; margin: 15px 0; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .loading { color: #17a2b8; }
        button { padding: 10px 20px; background: #263c79; color: white; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        button:hover { background: #1a2a5a; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .file-status { display: inline-block; padding: 4px 8px; border-radius: 4px; margin: 2px; font-size: 12px; }
        .file-fixed { background: #d4edda; color: #155724; }
        .file-pending { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <h1>🔧 API Path Fix Verification</h1>
    <p>This page tests if the API path fixes are working correctly when loaded through layout.php</p>
    
    <div class="test-section">
        <h2>✅ Files Fixed:</h2>
        <div>
            <span class="file-status file-fixed">circulation.php</span>
            <span class="file-status file-fixed">books-management.php</span>
            <span class="file-status file-fixed">dashboard.php</span>
            <span class="file-status file-fixed">footfall-analytics.php</span>
        </div>
    </div>
    
    <div class="test-section">
        <h2>⚙️ File Status Requiring Similar Fix:</h2>
        <div>
            <span class="file-status file-pending">student-management.php</span>
            <span class="file-status file-pending">members.php</span>
            <span class="file-status file-pending">fine-management.php</span>
            <span class="file-status file-pending">book-assignments.php</span>
        </div>
        <p><small>These files use similar API paths and may need the same fix if they show "-" statistics</small></p>
    </div>
    
    <div class="test-section">
        <h2>🧪 Test API Calls</h2>
        <button onclick="testCirculationAPI()">Test Circulation API</button>
        <button onclick="testBooksAPI()">Test Books API</button>
        <button onclick="testDashboardAPI()">Test Dashboard API</button>
        <button onclick="testFootfallAPI()">Test Footfall API</button>
        <div id="testResults" style="margin-top: 15px;"></div>
    </div>
    
    <div class="test-section">
        <h2>📝 How the Fix Works</h2>
        <p>Added a helper function to each page:</p>
        <pre>function getApiPath(apiFile) {
    const currentPath = window.location.pathname;
    if (currentPath.includes('/admin/layout.php') || currentPath.includes('/admin/layout2.php')) {
        return '/wiet_lib/admin/' + apiFile;
    } else if (currentPath.includes('/admin/')) {
        return apiFile;
    } else {
        return '/wiet_lib/admin/' + apiFile;
    }
}</pre>
        <p><strong>Usage:</strong> All fetch calls now use <code>fetch(getApiPath('api/circulation.php'))</code> instead of <code>fetch('api/circulation.php')</code></p>
    </div>
    
    <div class="test-section">
        <h2>🎯 Next Steps</h2>
        <ol>
            <li>Visit <a href="layout.php#circulation" target="_blank">layout.php#circulation</a> and check if statistics load</li>
            <li>Visit <a href="layout.php#books-management" target="_blank">layout.php#books-management</a> and check stats</li>
            <li>Visit <a href="layout.php#footfall-analytics" target="_blank">layout.php#footfall-analytics</a> and check stats</li>
            <li>Visit <a href="layout.php#dashboard" target="_blank">layout.php#dashboard</a> and check stats</li>
            <li>Press F12 → Console to see debug logs showing API calls</li>
        </ol>
    </div>
    
    <script>
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
        
        async function testAPI(name, apiPath) {
            const resultsDiv = document.getElementById('testResults');
            resultsDiv.innerHTML += `<div class="loading">Testing ${name}...</div>`;
            
            try {
                const url = getApiPath(apiPath);
                console.log(`Testing ${name} at: ${url}`);
                
                const response = await fetch(url);
                const data = await response.json();
                
                if (data.success || data.data) {
                    resultsDiv.innerHTML += `<div class="success">✓ ${name}: SUCCESS</div>`;
                    resultsDiv.innerHTML += `<pre>${JSON.stringify(data, null, 2).substring(0, 200)}...</pre>`;
                } else {
                    resultsDiv.innerHTML += `<div class="error">✗ ${name}: API returned success=false</div>`;
                    resultsDiv.innerHTML += `<pre>${JSON.stringify(data, null, 2)}</pre>`;
                }
            } catch (error) {
                resultsDiv.innerHTML += ` <div class="error">✗ ${name}: ${error.message}</div>`;
            }
        }
        
        function testCirculationAPI() {
            document.getElementById('testResults').innerHTML = '';
            testAPI('Circulation Stats', 'api/circulation.php?action=stats');
        }
        
        function testBooksAPI() {
            document.getElementById('testResults').innerHTML = '';
            testAPI('Books Stats', 'api/books.php?action=stats');
        }
        
        function testDashboardAPI() {
            document.getElementById('testResults').innerHTML = '';
            testAPI('Dashboard Stats', 'api/dashboard.php');
        }
        
        function testFootfallAPI() {
            document.getElementById('testResults').innerHTML = '';
            testAPI('Footfall Stats', '../footfallapi/footfall-stats.php');
        }
    </script>
</body>
</html>
