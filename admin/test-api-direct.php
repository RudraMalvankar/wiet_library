<!DOCTYPE html>
<html>
<head>
    <title>API Test - Direct Call</title>
</head>
<body>
    <h2>Testing Circulation Stats API</h2>
    <button onclick="testAPI()">Test API Call</button>
    <h3>Result:</h3>
    <pre id="result">Click the button to test...</pre>
    
    <h3>Direct API Test:</h3>
    <iframe src="api/circulation.php?action=stats" style="width:100%; height:200px; border:1px solid #ccc;"></iframe>
    
    <script>
    function testAPI() {
        const resultEl = document.getElementById('result');
        resultEl.textContent = 'Loading...';
        
        fetch('api/circulation.php?action=stats')
            .then(res => {
                console.log('Response status:', res.status);
                console.log('Response headers:', res.headers);
                return res.text();
            })
            .then(text => {
                console.log('Raw response:', text);
                try {
                    const data = JSON.parse(text);
                    resultEl.textContent = JSON.stringify(data, null, 2);
                } catch(e) {
                    resultEl.textContent = 'Not JSON:\n' + text;
                }
            })
            .catch(err => {
                resultEl.textContent = 'Error: ' + err.message;
                console.error('Fetch error:', err);
            });
    }
    </script>
</body>
</html>
