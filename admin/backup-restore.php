<?php
// Include AJAX handler FIRST
require_once 'ajax-handler.php';

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'] ?? 'Admin User';

// Backup directory
$backupDir = __DIR__ . '/../storage/backups';
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Get backup files
$backups = [];
if (is_dir($backupDir)) {
    $files = glob($backupDir . '/*.sql');
    rsort($files); // Latest first
    foreach ($files as $file) {
        $backups[] = [
            'name' => basename($file),
            'path' => $file,
            'size' => filesize($file),
            'date' => filemtime($file)
        ];
    }
}

// Database info
$db_host = 'localhost';
$db_name = 'wiet_library';
$db_user = 'root';
$db_pass = '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup & Restore - Library System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #cfac69;
        }

        .header h1 {
            color: #263c79;
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header h1 i {
            color: #cfac69;
        }

        .back-btn {
            padding: 10px 20px;
            background: #263c79;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s;
        }

        .back-btn:hover {
            background: #1a2a5a;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }

        .tab {
            padding: 12px 24px;
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 15px;
            font-weight: 500;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .tab:hover {
            color: #263c79;
            background: #f8f9fa;
        }

        .tab.active {
            color: #263c79;
            border-bottom-color: #cfac69;
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.3s;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }

        .section-title {
            font-size: 18px;
            color: #263c79;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #cfac69;
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .action-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 25px;
            border-radius: 10px;
            color: white;
            cursor: pointer;
            transition: transform 0.3s;
            text-align: center;
        }

        .action-card:hover {
            transform: translateY(-5px);
        }

        .action-card.green {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .action-card.blue {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .action-card.orange {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .action-card i {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .action-card h3 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .action-card p {
            font-size: 14px;
            opacity: 0.9;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 500;
            color: #263c79;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #263c79;
            color: white;
        }

        .btn-primary:hover {
            background: #1a2a5a;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(38, 60, 121, 0.3);
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-warning {
            background: #ffc107;
            color: #000;
        }

        .btn-warning:hover {
            background: #e0a800;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-info {
            background: #17a2b8;
            color: white;
        }

        .btn-info:hover {
            background: #138496;
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 12px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .table th {
            background: #f8f9fa;
            color: #263c79;
            font-weight: 600;
        }

        .table tr:hover {
            background: #f8f9fa;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-state i {
            font-size: 64px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .progress-bar {
            width: 100%;
            height: 30px;
            background: #e9ecef;
            border-radius: 15px;
            overflow: hidden;
            margin: 20px 0;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .schedule-box {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }

        .schedule-box h4 {
            color: #263c79;
            margin-bottom: 15px;
        }

        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .loading i {
            font-size: 48px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-database"></i> Database Backup & Restore</h1>
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>

        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <span><strong>Important:</strong> Always test your backups! Restore to a test database first before using in production.</span>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" onclick="showTab('backup')">
                <i class="fas fa-cloud-upload-alt"></i> Create Backup
            </button>
            <button class="tab" onclick="showTab('restore')">
                <i class="fas fa-cloud-download-alt"></i> Restore Backup
            </button>
            <button class="tab" onclick="showTab('schedule')">
                <i class="fas fa-clock"></i> Auto Backup
            </button>
            <button class="tab" onclick="showTab('history')">
                <i class="fas fa-history"></i> Backup History
            </button>
        </div>

        <!-- Create Backup Tab -->
        <div id="backup" class="tab-content active">
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-cloud-upload-alt"></i>
                    Create Database Backup
                </div>

                <div class="action-grid">
                    <div class="action-card" onclick="createBackup('full')">
                        <i class="fas fa-database"></i>
                        <h3>Full Backup</h3>
                        <p>Complete database backup including all tables and data</p>
                    </div>
                    <div class="action-card green" onclick="createBackup('structure')">
                        <i class="fas fa-sitemap"></i>
                        <h3>Structure Only</h3>
                        <p>Backup database structure without data</p>
                    </div>
                    <div class="action-card blue" onclick="createBackup('data')">
                        <i class="fas fa-table"></i>
                        <h3>Data Only</h3>
                        <p>Backup data without structure</p>
                    </div>
                    <div class="action-card orange" onclick="createBackup('custom')">
                        <i class="fas fa-cog"></i>
                        <h3>Custom Backup</h3>
                        <p>Select specific tables to backup</p>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Backup Name (Optional)</label>
                        <input type="text" id="backupName" placeholder="Leave empty for auto-generated name">
                    </div>
                    <div class="form-group">
                        <label>Compression</label>
                        <select id="compression">
                            <option value="none">None</option>
                            <option value="zip" selected>ZIP Compression</option>
                            <option value="gzip">GZIP Compression</option>
                        </select>
                    </div>
                </div>

                <div id="backupProgress" style="display: none;">
                    <div class="progress-bar">
                        <div id="backupProgressFill" class="progress-fill" style="width: 0%">0%</div>
                    </div>
                    <div id="backupProgressText" style="text-align: center; color: #666;"></div>
                </div>

                <div id="backupResult"></div>
            </div>
        </div>

        <!-- Restore Backup Tab -->
        <div id="restore" class="tab-content">
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-cloud-download-alt"></i>
                    Restore Database Backup
                </div>

                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><strong>Warning:</strong> Restoring will overwrite current database. This action cannot be undone!</span>
                </div>

                <?php if (empty($backups)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>No Backups Found</h3>
                        <p>Create your first backup to get started.</p>
                    </div>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Backup File</th>
                                <th>Date & Time</th>
                                <th>Size</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($backups as $backup): ?>
                                <tr>
                                    <td>
                                        <i class="fas fa-file-archive"></i>
                                        <?php echo htmlspecialchars($backup['name']); ?>
                                    </td>
                                    <td><?php echo date('Y-m-d H:i:s', $backup['date']); ?></td>
                                    <td><?php echo formatFileSize($backup['size']); ?></td>
                                    <td>
                                        <button class="btn btn-success btn-sm" onclick="restoreBackup('<?php echo htmlspecialchars($backup['name']); ?>')">
                                            <i class="fas fa-undo"></i> Restore
                                        </button>
                                        <button class="btn btn-info btn-sm" onclick="downloadBackup('<?php echo htmlspecialchars($backup['name']); ?>')">
                                            <i class="fas fa-download"></i> Download
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="deleteBackup('<?php echo htmlspecialchars($backup['name']); ?>')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <div style="margin-top: 30px; padding-top: 30px; border-top: 2px solid #e9ecef;">
                    <h4 style="color: #263c79; margin-bottom: 15px;">
                        <i class="fas fa-upload"></i> Upload Backup File
                    </h4>
                    <form id="uploadBackupForm" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Select Backup File (.sql or .zip)</label>
                                <input type="file" name="backupFile" accept=".sql,.zip" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload & Restore
                        </button>
                    </form>
                </div>

                <div id="restoreResult"></div>
            </div>
        </div>

        <!-- Auto Backup Schedule Tab -->
        <div id="schedule" class="tab-content">
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-clock"></i>
                    Automatic Backup Schedule
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <span>Set up automatic backups to run at scheduled intervals. Requires cron job or Windows Task Scheduler.</span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Enable Auto Backup</label>
                        <select id="autoBackupEnabled">
                            <option value="0">Disabled</option>
                            <option value="1">Enabled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Backup Frequency</label>
                        <select id="backupFrequency">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Backup Time</label>
                        <input type="time" id="backupTime" value="02:00">
                    </div>
                </div>

                <div class="schedule-box">
                    <h4>Backup Days (for Weekly)</h4>
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <input type="checkbox" id="day-monday" value="1">
                            <label for="day-monday">Monday</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="day-tuesday" value="2">
                            <label for="day-tuesday">Tuesday</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="day-wednesday" value="3">
                            <label for="day-wednesday">Wednesday</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="day-thursday" value="4">
                            <label for="day-thursday">Thursday</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="day-friday" value="5">
                            <label for="day-friday">Friday</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="day-saturday" value="6">
                            <label for="day-saturday">Saturday</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="day-sunday" value="0" checked>
                            <label for="day-sunday">Sunday</label>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Keep Backups For</label>
                        <select id="retentionDays">
                            <option value="7">7 Days</option>
                            <option value="14">14 Days</option>
                            <option value="30" selected>30 Days</option>
                            <option value="60">60 Days</option>
                            <option value="90">90 Days</option>
                            <option value="0">Keep All</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Email Notification</label>
                        <input type="email" id="notificationEmail" placeholder="admin@example.com">
                    </div>
                </div>

                <button class="btn btn-primary" onclick="saveSchedule()">
                    <i class="fas fa-save"></i> Save Schedule
                </button>

                <div class="schedule-box" style="margin-top: 30px;">
                    <h4>Cron Job Command (Linux/Mac)</h4>
                    <code style="display: block; background: #2d3748; color: #68d391; padding: 15px; border-radius: 5px; font-family: monospace;">
                        0 2 * * * php /path/to/wiet_lib/admin/api/backup-cron.php
                    </code>
                    <h4 style="margin-top: 20px;">Task Scheduler Command (Windows)</h4>
                    <code style="display: block; background: #2d3748; color: #68d391; padding: 15px; border-radius: 5px; font-family: monospace;">
                        schtasks /create /tn "Library Backup" /tr "php C:\xampp\htdocs\wiet_lib\admin\api\backup-cron.php" /sc daily /st 02:00
                    </code>
                </div>

                <div id="scheduleResult"></div>
            </div>
        </div>

        <!-- Backup History Tab -->
        <div id="history" class="tab-content">
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-history"></i>
                    Backup History & Logs
                </div>

                <?php if (empty($backups)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>No Backup History</h3>
                        <p>Backup history will appear here once you create backups.</p>
                    </div>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Backup Name</th>
                                <th>Type</th>
                                <th>Date Created</th>
                                <th>Size</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($backups as $backup): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($backup['name']); ?></td>
                                    <td><span class="badge badge-info">Full</span></td>
                                    <td><?php echo date('M d, Y H:i', $backup['date']); ?></td>
                                    <td><?php echo formatFileSize($backup['size']); ?></td>
                                    <td><span class="badge badge-success">Complete</span></td>
                                    <td>
                                        <button class="btn btn-info btn-sm" onclick="viewBackupInfo('<?php echo htmlspecialchars($backup['name']); ?>')">
                                            <i class="fas fa-info-circle"></i> Info
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="deleteBackup('<?php echo htmlspecialchars($backup['name']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <div style="margin-top: 20px;">
                    <button class="btn btn-warning" onclick="cleanOldBackups()">
                        <i class="fas fa-broom"></i> Clean Old Backups
                    </button>
                    <button class="btn btn-danger" onclick="deleteAllBackups()">
                        <i class="fas fa-trash-alt"></i> Delete All Backups
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });

            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }

        function createBackup(type) {
            const name = document.getElementById('backupName').value;
            const compression = document.getElementById('compression').value;
            const progressDiv = document.getElementById('backupProgress');
            const resultDiv = document.getElementById('backupResult');

            if (!confirm(`Create ${type} backup?`)) return;

            progressDiv.style.display = 'block';
            resultDiv.innerHTML = '';

            const formData = new FormData();
            formData.append('backupType', type);
            formData.append('compression', compression);
            formData.append('description', name);

            fetch(`api/backup-restore.php?action=create-backup`, {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showSuccess(resultDiv, data.message);
                        updateProgress('backupProgressFill', 100);
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showError(resultDiv, data.message);
                    }
                })
                .catch(err => {
                    showError(resultDiv, 'Error: ' + err.message);
                });
        }

        function restoreBackup(filename) {
            if (!confirm('⚠️ WARNING: This will overwrite the current database!\n\nAre you absolutely sure you want to restore from this backup?\n\nFilename: ' + filename)) {
                return;
            }

            if (!confirm('FINAL CONFIRMATION: All current data will be lost. Continue?')) {
                return;
            }

            const resultDiv = document.getElementById('restoreResult');
            resultDiv.innerHTML = '<div class="loading"><i class="fas fa-spinner"></i><p>Restoring database...</p></div>';

            const formData = new FormData();
            formData.append('filename', filename);

            fetch(`api/backup-restore.php?action=restore-backup`, {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showSuccess(resultDiv, data.message);
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showError(resultDiv, data.message);
                    }
                })
                .catch(err => {
                    showError(resultDiv, 'Error: ' + err.message);
                });
        }

        function downloadBackup(filename) {
            window.open(`api/backup-restore.php?action=download-backup&filename=${filename}`, '_blank');
        }

        function deleteBackup(filename) {
            if (!confirm(`Delete backup: ${filename}?`)) return;

            const formData = new FormData();
            formData.append('filename', filename);

            fetch(`api/backup-restore.php?action=delete-backup`, {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(err => {
                    alert('Error: ' + err.message);
                });
        }

        function saveSchedule() {
            const enabled = document.getElementById('autoBackupEnabled').value;
            const frequency = document.getElementById('backupFrequency').value;
            const time = document.getElementById('backupTime').value;
            const retention = document.getElementById('retentionDays').value;
            const email = document.getElementById('notificationEmail').value;

            const formData = new FormData();
            formData.append('enabled', enabled);
            formData.append('frequency', frequency);
            formData.append('time', time);
            formData.append('retention', retention);
            formData.append('email', email);

            fetch('api/backup-restore.php?action=save-auto-backup', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                const resultDiv = document.getElementById('scheduleResult');
                if (data.success) {
                    showSuccess(resultDiv, data.message);
                } else {
                    showError(resultDiv, data.message);
                }
            })
            .catch(err => {
                showError(document.getElementById('scheduleResult'), 'Error: ' + err.message);
            });
        }

        function cleanOldBackups() {
            if (!confirm('Delete backups older than retention period?')) return;

            fetch('api/backup-restore.php?action=cleanup-backups')
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) location.reload();
                });
        }

        function deleteAllBackups() {
            if (!confirm('⚠️ Delete ALL backups? This cannot be undone!')) return;

            fetch('api/backup-restore.php?action=cleanup-backups')
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) location.reload();
                });
        }

        function viewBackupInfo(filename) {
            alert('Backup Information:\n\nFilename: ' + filename + '\n\nFull details coming soon...');
        }

        function updateProgress(elementId, percent) {
            const element = document.getElementById(elementId);
            element.style.width = percent + '%';
            element.textContent = percent + '%';
        }

        function showSuccess(container, message) {
            container.innerHTML = `
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>${message}</span>
                </div>
            `;
        }

        function showError(container, message) {
            container.innerHTML = `
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>${message}</span>
                </div>
            `;
        }

        // Handle upload form
        document.getElementById('uploadBackupForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const resultDiv = document.getElementById('restoreResult');
            
            resultDiv.innerHTML = '<div class="loading"><i class="fas fa-spinner"></i><p>Uploading and restoring...</p></div>';

            fetch('api/backup-restore.php?action=restore-backup', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showSuccess(resultDiv, data.message);
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showError(resultDiv, data.message);
                }
            })
            .catch(err => {
                showError(resultDiv, 'Error: ' + err.message);
            });
        });
    </script>
</body>
</html>

<?php
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < 3) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}
?>
