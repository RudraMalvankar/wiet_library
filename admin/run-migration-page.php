<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Run Database Migration</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            padding: 40px;
            min-height: 100vh;
            color: #e2e8f0;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
        }
        .header {
            background: linear-gradient(135deg, #263c79 0%, #1e2d5f 100%);
            color: white;
            padding: 30px;
            border-bottom: 4px solid #cfac69;
        }
        .header h1 { margin-bottom: 10px; }
        .header p { opacity: 0.9; }
        .content { padding: 40px; }
        .alert {
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid;
        }
        .alert.info { background: #dbeafe; color: #1e40af; border-color: #3b82f6; }
        .alert.success { background: #d1fae5; color: #065f46; border-color: #10b981; }
        .alert.error { background: #fee2e2; color: #991b1b; border-color: #ef4444; }
        .alert.warning { background: #fef3c7; color: #92400e; border-color: #f59e0b; }
        button {
            background: linear-gradient(135deg, #263c79 0%, #1e2d5f 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        button:hover { transform: translateY(-2px); box-shadow: 0 8px 16px rgba(38, 60, 121, 0.4); }
        button:disabled { background: #94a3b8; cursor: not-allowed; transform: none; }
        pre {
            background: #1e293b;
            color: #e2e8f0;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            font-size: 13px;
            line-height: 1.6;
        }
        .step {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #263c79;
            color: #333;
        }
        .step h3 { color: #263c79; margin-bottom: 10px; }
        .step ol { margin-left: 20px; color: #555; }
        .step li { margin: 8px 0; }
        code {
            background: #e2e8f0;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            color: #1e293b;
        }
        #result { display: none; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🗄️ Database Migration Runner</h1>
            <p>Run Migration 006: Enhance Footfall Tracking</p>
        </div>
        
        <div class="content">
            <div class="alert info">
                <strong>ℹ️ What This Does:</strong>
                <ul style="margin-top: 10px; margin-left: 20px;">
                    <li>Adds 6 new columns to Footfall table: EntryTime, ExitTime, Purpose, Status, EntryMethod, WorkstationUsed</li>
                    <li>Updates existing records with calculated timestamps</li>
                    <li>Creates 3 performance indexes</li>
                    <li>Creates 3 SQL Views for analytics</li>
                </ul>
            </div>

            <div class="step">
                <h3>Option 1: Automatic Migration (Recommended)</h3>
                <p style="margin-bottom: 15px;">Click the button below to run the migration automatically:</p>
                <button onclick="runMigration()" id="runBtn">
                    <span>▶️</span> Run Migration Now
                </button>
            </div>

            <div class="step">
                <h3>Option 2: Manual Migration (Via phpMyAdmin)</h3>
                <ol>
                    <li>Open <strong>phpMyAdmin</strong> (http://localhost/phpmyadmin)</li>
                    <li>Select <strong>wiet_library</strong> database</li>
                    <li>Click <strong>SQL</strong> tab</li>
                    <li>Copy the SQL below and paste it into the SQL box</li>
                    <li>Click <strong>Go</strong> button</li>
                </ol>
                <pre><?php
                $migrationFile = '../database/migrations/006_enhance_footfall_tracking.sql';
                if (file_exists($migrationFile)) {
                    echo htmlspecialchars(file_get_contents($migrationFile));
                } else {
                    echo "-- Migration file not found at: $migrationFile";
                }
                ?></pre>
            </div>

            <div id="result"></div>

            <div class="alert warning" style="margin-top: 30px;">
                <strong>⚠️ After Migration:</strong>
                <ul style="margin-top: 10px; margin-left: 20px;">
                    <li>Go back to <a href="footfall-diagnostic.html" style="color: #263c79; font-weight: 600;">Diagnostic Tool</a> and run tests again</li>
                    <li>Or check: <a href="check-database.php" style="color: #263c79; font-weight: 600;">Database Status Page</a></li>
                    <li>All tests should pass ✓</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        async function runMigration() {
            const btn = document.getElementById('runBtn');
            const result = document.getElementById('result');
            
            btn.disabled = true;
            btn.innerHTML = '<span>⏳</span> Running migration...';
            
            result.style.display = 'block';
            result.className = 'alert info';
            result.innerHTML = '<strong>⏳ Running migration...</strong><p>This may take a few seconds...</p>';
            
            try {
                const response = await fetch('run-migration.php');
                const data = await response.json();
                
                if (data.success) {
                    result.className = 'alert success';
                    result.innerHTML = `
                        <strong>✅ Migration Successful!</strong>
                        <p>${data.message}</p>
                        <ul style="margin-top: 10px; margin-left: 20px;">
                            ${data.details ? data.details.map(d => `<li>${d}</li>`).join('') : ''}
                        </ul>
                        <p style="margin-top: 15px;">
                            <a href="footfall-diagnostic.html" style="color: #065f46; font-weight: 600; text-decoration: underline;">
                                → Go to Diagnostic Tool to verify
                            </a>
                        </p>
                    `;
                    btn.innerHTML = '<span>✓</span> Migration Complete';
                } else {
                    result.className = 'alert error';
                    result.innerHTML = `
                        <strong>❌ Migration Failed</strong>
                        <p>${data.message}</p>
                        <p style="margin-top: 10px;"><strong>Error:</strong> ${data.error || 'Unknown error'}</p>
                        <p style="margin-top: 10px;">Please try <strong>Option 2</strong> (Manual Migration) above.</p>
                    `;
                    btn.disabled = false;
                    btn.innerHTML = '<span>🔄</span> Try Again';
                }
            } catch (error) {
                result.className = 'alert error';
                result.innerHTML = `
                    <strong>❌ Connection Error</strong>
                    <p>Could not connect to migration endpoint.</p>
                    <p><strong>Error:</strong> ${error.message}</p>
                    <p style="margin-top: 10px;">Please use <strong>Option 2</strong> (Manual Migration via phpMyAdmin) above.</p>
                `;
                btn.disabled = false;
                btn.innerHTML = '<span>🔄</span> Try Again';
            }
        }
    </script>
</body>
</html>
