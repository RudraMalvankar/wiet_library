<?php
// Include AJAX handler FIRST
require_once 'ajax-handler.php';

// Session started by ajax-handler.php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'] ?? 'Admin User';

// Fetch statistics
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Books");
    $total_books = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Holding WHERE QRCode IS NOT NULL AND QRCode != ''");
    $books_with_qr = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Member");
    $total_members = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Student WHERE QRCode IS NOT NULL AND QRCode != ''");
    $members_with_qr = $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("QR stats error: " . $e->getMessage());
    $total_books = $books_with_qr = $total_members = $members_with_qr = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Generator - Library System</title>
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 25px;
            border-radius: 10px;
            color: white;
        }

        .stat-card.green {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .stat-card.blue {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .stat-card.orange {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .stat-number {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
            overflow-x: auto;
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
            white-space: nowrap;
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

        .btn-info {
            background: #17a2b8;
            color: white;
        }

        .btn-info:hover {
            background: #138496;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .action-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .action-card:hover {
            border-color: #cfac69;
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }

        .action-card i {
            font-size: 48px;
            color: #263c79;
            margin-bottom: 15px;
        }

        .action-card h3 {
            color: #263c79;
            margin-bottom: 10px;
        }

        .action-card p {
            color: #666;
            font-size: 14px;
        }

        .qr-preview {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .qr-item {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }

        .qr-item img {
            width: 150px;
            height: 150px;
            margin-bottom: 10px;
        }

        .qr-item .code {
            font-weight: bold;
            color: #263c79;
            margin-bottom: 5px;
        }

        .qr-item .name {
            font-size: 12px;
            color: #666;
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

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
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

        .badge-warning {
            background: #fff3cd;
            color: #856404;
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

        @media print {
            body {
                background: white;
            }
            .header, .tabs, .back-btn, .btn {
                display: none;
            }
            .container {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-qrcode"></i> QR Code Generator</h1>
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_books; ?></div>
                <div class="stat-label">Total Books</div>
            </div>
            <div class="stat-card green">
                <div class="stat-number"><?php echo $books_with_qr; ?></div>
                <div class="stat-label">Books with QR Codes</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-number"><?php echo $total_members; ?></div>
                <div class="stat-label">Total Members</div>
            </div>
            <div class="stat-card orange">
                <div class="stat-number"><?php echo $members_with_qr; ?></div>
                <div class="stat-label">Members with QR Codes</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" onclick="showTab('books')">
                <i class="fas fa-book"></i> Book QR Codes
            </button>
            <button class="tab" onclick="showTab('members')">
                <i class="fas fa-users"></i> Member QR Codes
            </button>
            <button class="tab" onclick="showTab('bulk')">
                <i class="fas fa-layer-group"></i> Bulk Generation
            </button>
            <button class="tab" onclick="showTab('labels')">
                <i class="fas fa-tags"></i> Print Labels
            </button>
        </div>

        <!-- Book QR Codes Tab -->
        <div id="books" class="tab-content active">
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-book"></i>
                    Generate Book QR Codes
                </div>

                <div class="action-grid">
                    <div class="action-card" onclick="generateBookQR('single')">
                        <i class="fas fa-qrcode"></i>
                        <h3>Single Book</h3>
                        <p>Generate QR code for a specific book by AccNo</p>
                    </div>
                    <div class="action-card" onclick="generateBookQR('range')">
                        <i class="fas fa-list-ol"></i>
                        <h3>Range of Books</h3>
                        <p>Generate QR codes for a range of AccNo</p>
                    </div>
                    <div class="action-card" onclick="generateBookQR('all')">
                        <i class="fas fa-database"></i>
                        <h3>All Books (Missing)</h3>
                        <p>Generate QR codes for books without codes</p>
                    </div>
                    <div class="action-card" onclick="generateBookQR('regenerate')">
                        <i class="fas fa-sync-alt"></i>
                        <h3>Regenerate All</h3>
                        <p>Overwrite all existing book QR codes</p>
                    </div>
                </div>

                <div id="bookQRResult" style="margin-top: 20px;"></div>
            </div>
        </div>

        <!-- Member QR Codes Tab -->
        <div id="members" class="tab-content">
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-users"></i>
                    Generate Member QR Codes
                </div>

                <div class="action-grid">
                    <div class="action-card" onclick="generateMemberQR('single')">
                        <i class="fas fa-id-card"></i>
                        <h3>Single Member</h3>
                        <p>Generate QR code for a specific member</p>
                    </div>
                    <div class="action-card" onclick="generateMemberQR('batch')">
                        <i class="fas fa-users-cog"></i>
                        <h3>Batch by Branch</h3>
                        <p>Generate QR codes by department/branch</p>
                    </div>
                    <div class="action-card" onclick="generateMemberQR('all')">
                        <i class="fas fa-user-graduate"></i>
                        <h3>All Members (Missing)</h3>
                        <p>Generate QR codes for members without codes</p>
                    </div>
                    <div class="action-card" onclick="generateMemberQR('regenerate')">
                        <i class="fas fa-sync-alt"></i>
                        <h3>Regenerate All</h3>
                        <p>Overwrite all existing member QR codes</p>
                    </div>
                </div>

                <div id="memberQRResult" style="margin-top: 20px;"></div>
            </div>
        </div>

        <!-- Bulk Generation Tab -->
        <div id="bulk" class="tab-content">
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-layer-group"></i>
                    Bulk QR Code Generation
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <span>Bulk generation processes large datasets. This may take several minutes.</span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Generation Type</label>
                        <select id="bulkType">
                            <option value="books">All Books Missing QR Codes</option>
                            <option value="members">All Members Missing QR Codes</option>
                            <option value="regenerate-books">Regenerate All Book QR Codes</option>
                            <option value="regenerate-members">Regenerate All Member QR Codes</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Size (pixels)</label>
                        <select id="qrSize">
                            <option value="150">Small (150x150)</option>
                            <option value="200" selected>Medium (200x200)</option>
                            <option value="300">Large (300x300)</option>
                        </select>
                    </div>
                </div>

                <button class="btn btn-primary" onclick="startBulkGeneration()">
                    <i class="fas fa-play"></i>
                    Start Bulk Generation
                </button>

                <div id="bulkProgress" style="display: none;">
                    <div class="progress-bar">
                        <div id="progressFill" class="progress-fill" style="width: 0%">0%</div>
                    </div>
                    <div id="progressText" style="text-align: center; color: #666;"></div>
                </div>

                <div id="bulkResult"></div>
            </div>
        </div>

        <!-- Print Labels Tab -->
        <div id="labels" class="tab-content">
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-tags"></i>
                    Print QR Code Labels
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Label Type</label>
                        <select id="labelType" onchange="updateLabelOptions()">
                            <option value="book">Book Labels</option>
                            <option value="member">Member Cards</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Label Size</label>
                        <select id="labelSize">
                            <option value="small">Small (2" x 1")</option>
                            <option value="medium" selected>Medium (3" x 2")</option>
                            <option value="large">Large (4" x 3")</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Layout</label>
                        <select id="labelLayout">
                            <option value="1x1">1 per page</option>
                            <option value="2x3" selected>6 per page (2x3)</option>
                            <option value="3x4">12 per page (3x4)</option>
                        </select>
                    </div>
                </div>

                <div id="labelOptions">
                    <!-- Dynamic options based on type -->
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button class="btn btn-primary" onclick="previewLabels()">
                        <i class="fas fa-eye"></i>
                        Preview Labels
                    </button>
                    <button class="btn btn-success" onclick="printLabels()">
                        <i class="fas fa-print"></i>
                        Print Labels
                    </button>
                    <button class="btn btn-info" onclick="exportLabelsPDF()">
                        <i class="fas fa-file-pdf"></i>
                        Export as PDF
                    </button>
                </div>

                <div id="labelPreview" style="margin-top: 20px;"></div>
            </div>
        </div>
    </div>

    <script>
        // ── QR Sheet state ───────────────────────────────────────────────────
        let _sheetData = [];

        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }

        function generateBookQR(type) {
            const resultDiv = document.getElementById('bookQRResult');
            let params = '';

            if (type === 'single') {
                const accNo = prompt('Enter Book AccNo:');
                if (!accNo) return;
                params = '?type=book&accNo=' + encodeURIComponent(accNo);
            } else if (type === 'range') {
                const start = prompt('Enter Start AccNo:');
                const end   = prompt('Enter End AccNo:');
                if (!start || !end) return;
                params = '?type=book-range&start=' + encodeURIComponent(start) + '&end=' + encodeURIComponent(end);
            } else if (type === 'all') {
                if (!confirm('Generate QR codes for all books without codes?\nThey will be saved to the database.')) return;
                params = '?type=book-all';
            } else if (type === 'regenerate') {
                if (!confirm('Regenerate ALL book QR codes?\nThis will overwrite existing ones.')) return;
                params = '?type=regenerate-books';
            }

            resultDiv.innerHTML = '<div class="loading"><i class="fas fa-spinner"></i><p>Generating &amp; saving QR codes...</p></div>';
            fetch('api/qr-generator.php' + params)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showSuccess(resultDiv, data.message);
                        if (data.qrCodes && data.qrCodes.length > 0) {
                            renderQRPreview(resultDiv, data.qrCodes, 'Book QR Codes');
                        }
                    } else {
                        showError(resultDiv, data.message);
                    }
                })
                .catch(err => showError(resultDiv, 'Error: ' + err.message));
        }

        function generateMemberQR(type) {
            const resultDiv = document.getElementById('memberQRResult');
            let params = '';

            if (type === 'single') {
                const memberNo = prompt('Enter Member Number:');
                if (!memberNo) return;
                params = '?type=member&memberNo=' + encodeURIComponent(memberNo);
            } else if (type === 'batch') {
                const branch = prompt('Enter Branch/Department:');
                if (!branch) return;
                params = '?type=member-batch&branch=' + encodeURIComponent(branch);
            } else if (type === 'all') {
                if (!confirm('Generate QR codes for all members without codes?')) return;
                params = '?type=member-all';
            } else if (type === 'regenerate') {
                if (!confirm('Regenerate ALL member QR codes?\nThis will overwrite existing ones.')) return;
                params = '?type=regenerate-members';
            }

            resultDiv.innerHTML = '<div class="loading"><i class="fas fa-spinner"></i><p>Generating &amp; saving QR codes...</p></div>';
            fetch('api/qr-generator.php' + params)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showSuccess(resultDiv, data.message);
                        if (data.qrCodes && data.qrCodes.length > 0) {
                            renderQRPreview(resultDiv, data.qrCodes, 'Member QR Codes');
                        }
                    } else {
                        showError(resultDiv, data.message);
                    }
                })
                .catch(err => showError(resultDiv, 'Error: ' + err.message));
        }

        function startBulkGeneration() {
            const type      = document.getElementById('bulkType').value;
            const size      = document.getElementById('qrSize').value;
            const progressDiv = document.getElementById('bulkProgress');
            const resultDiv   = document.getElementById('bulkResult');

            if (!confirm('Start bulk generation for "' + type + '"?\nQR codes will be saved to the database.')) return;

            progressDiv.style.display = 'block';
            updateProgress(10, 'Generating...');
            resultDiv.innerHTML = '';

            fetch('api/qr-generator.php?type=' + type + '&size=' + size)
                .then(res => res.json())
                .then(data => {
                    updateProgress(100, 'Completed: ' + (data.count || 0) + ' QR codes generated');
                    if (data.success) {
                        showSuccess(resultDiv, data.message);
                        if (data.qrCodes && data.qrCodes.length > 0) {
                            const label = type.includes('member') ? 'Member QR Codes' : 'Book QR Codes';
                            renderQRPreview(resultDiv, data.qrCodes, label);
                        }
                    } else {
                        showError(resultDiv, data.message);
                    }
                })
                .catch(err => showError(resultDiv, 'Error: ' + err.message));
        }

        function updateProgress(percent, text) {
            document.getElementById('progressFill').style.width = percent + '%';
            document.getElementById('progressFill').textContent = percent + '%';
            document.getElementById('progressText').textContent = text;
        }

        function updateLabelOptions() {
            var type = document.getElementById('labelType').value;
            var optionsDiv = document.getElementById('labelOptions');

            if (type === 'book') {
                optionsDiv.innerHTML = '<div class="form-row"><div class="form-group">'
                    + '<label>Select Books</label>'
                    + '<select id="bookSelection">'
                    + '<option value="all">All Books with QR Codes</option>'
                    + '<option value="range">Range of AccNo</option>'
                    + '<option value="section">By Section/Location</option>'
                    + '</select></div></div>';
            } else {
                optionsDiv.innerHTML = '<div class="form-row"><div class="form-group">'
                    + '<label>Select Members</label>'
                    + '<select id="memberSelection">'
                    + '<option value="all">All Members with QR Codes</option>'
                    + '<option value="branch">By Branch</option>'
                    + '<option value="new">New Members Only</option>'
                    + '</select></div></div>';
            }
        }

        var _labelData = [];

        function previewLabels() {
            var previewDiv = document.getElementById('labelPreview');
            var type   = document.getElementById('labelType').value;
            var size   = document.getElementById('labelSize').value;
            var layout = document.getElementById('labelLayout').value;

            previewDiv.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i><p>Loading preview...</p></div>';

            fetch('api/qr-generator.php?action=preview&type=' + encodeURIComponent(type))
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (!data.success || !data.items || !data.items.length) {
                        previewDiv.innerHTML = '<div class="alert alert-warning">No QR codes found. Generate them first using the Book QR Codes or Member QR Codes tab.</div>';
                        return;
                    }
                    _labelData = data.items;
                    buildLabelPreview(previewDiv, data.items, layout, size, type);
                })
                .catch(function(err) {
                    previewDiv.innerHTML = '<div class="alert alert-danger">Error: ' + err.message + '</div>';
                });
        }

        function buildLabelPreview(container, items, layout, size, type) {
            // Determine columns from layout value (e.g. "3x4" -> 3 cols)
            var cols = layout === '1x1' ? 1 : layout === '2x3' ? 2 : 3;

            // Card dimensions based on size
            var cardW = size === 'small' ? 120 : size === 'large' ? 220 : 160;
            var imgW  = size === 'small' ? 70  : size === 'large' ? 130 : 100;

            // Title bar
            var bar = document.createElement('div');
            bar.style.cssText = 'display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:14px;';

            var info = document.createElement('span');
            info.style.cssText = 'color:#263c79;font-weight:600;';
            info.textContent = items.length + ' label(s) ready — ' + (type === 'member' ? 'Member' : 'Book') + ' labels';
            bar.appendChild(info);

            var btnRow = document.createElement('div');
            btnRow.style.cssText = 'display:flex;gap:8px;flex-wrap:wrap;';

            var btnPrint = document.createElement('button');
            btnPrint.className = 'btn btn-success';
            btnPrint.innerHTML = '<i class="fas fa-print"></i> Print Labels';
            btnPrint.addEventListener('click', function() { printLabelWindow(layout, size, type); });

            var btnExport = document.createElement('button');
            btnExport.className = 'btn btn-info';
            btnExport.innerHTML = '<i class="fas fa-file-pdf"></i> Export as PDF';
            btnExport.addEventListener('click', function() { printLabelWindow(layout, size, type); });

            btnRow.appendChild(btnPrint);
            btnRow.appendChild(btnExport);
            bar.appendChild(btnRow);

            // Grid
            var grid = document.createElement('div');
            grid.id = 'labelGrid';
            grid.style.cssText = 'display:grid;grid-template-columns:repeat(' + cols + ',minmax(0,1fr));gap:10px;margin-top:10px;border:1px dashed #ccc;padding:15px;border-radius:6px;background:#f9f9f9;';

            items.forEach(function(q) {
                var card = document.createElement('div');
                card.style.cssText = 'border:1px solid #999;border-radius:4px;padding:8px;text-align:center;background:#fff;overflow:hidden;';

                var img = document.createElement('img');
                img.src = q.base64 ? 'data:image/png;base64,' + q.base64 : '';
                img.style.cssText = 'width:' + imgW + 'px;height:' + imgW + 'px;object-fit:contain;display:block;margin:0 auto 4px;';

                var code = document.createElement('div');
                code.style.cssText = 'font-size:9px;color:#263c79;font-weight:700;font-family:monospace;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;';
                code.title = q.code;
                code.textContent = q.code;

                var name = document.createElement('div');
                name.style.cssText = 'font-size:8px;color:#444;margin-top:2px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;line-height:1.3;max-height:2.6em;';
                name.title = q.name;
                name.textContent = q.name;

                card.appendChild(img);
                card.appendChild(code);
                card.appendChild(name);
                grid.appendChild(card);
            });

            container.innerHTML = '';
            container.appendChild(bar);
            container.appendChild(grid);
        }

        function printLabelWindow(layout, size, type) {
            if (!_labelData.length) { alert('Click "Preview Labels" first.'); return; }
            var cols = layout === '1x1' ? 1 : layout === '2x3' ? 2 : 3;
            var imgW = size === 'small' ? '55px' : size === 'large' ? '110px' : '80px';

            var rows = '';
            _labelData.forEach(function(q) {
                var src = q.base64 ? 'data:image/png;base64,' + q.base64 : '';
                var nameShort = q.name.length > 50 ? q.name.substring(0, 47) + '...' : q.name;
                rows += '<div class="lc"><img src="' + src + '"><div class="code">' + escHtml(q.code) + '</div><div class="lname">' + escHtml(nameShort) + '</div></div>';
            });

            var labelType = type === 'member' ? 'Member Cards' : 'Book Labels';
            var html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' + labelType + '</title>'
                + '<style>body{font-family:sans-serif;margin:15px;}'
                + '.grid{display:grid;grid-template-columns:repeat(' + cols + ',1fr);gap:8px;}'
                + '.lc{border:1px solid #888;border-radius:3px;padding:6px;text-align:center;break-inside:avoid;overflow:hidden;}'
                + '.lc img{width:' + imgW + ';height:' + imgW + ';display:block;margin:0 auto 3px;}'
                + '.code{font-size:9px;color:#263c79;font-weight:700;font-family:monospace;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}'
                + '.lname{font-size:8px;color:#444;margin-top:1px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;}'
                + 'h3{text-align:center;margin-bottom:10px;}'
                + '@media print{button{display:none}}'
                + '<' + '/style><' + '/head><body>'
                + '<h3>WIET Library — ' + labelType + '</h3>'
                + '<div class="grid">' + rows + '</div>'
                + '<' + '/body><' + '/html>';
            var w = window.open('', '_blank');
            w.document.open();
            w.document.write(html);
            w.document.close();
            w.focus();
            setTimeout(function() { w.print(); }, 600);
        }

        function printLabels() {
            var layout = document.getElementById('labelLayout').value;
            var size   = document.getElementById('labelSize').value;
            var type   = document.getElementById('labelType').value;
            if (!_labelData.length) { previewLabels(); return; }
            printLabelWindow(layout, size, type);
        }

        function exportLabelsPDF() {
            var layout = document.getElementById('labelLayout').value;
            var size   = document.getElementById('labelSize').value;
            var type   = document.getElementById('labelType').value;
            if (!_labelData.length) { previewLabels(); return; }
            printLabelWindow(layout, size, type);
        }

        // helper - must be defined before renderQRPreview uses it
        function escHtml(s) {
            return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
        }

        // ── Render preview strip + sheet/download buttons ───────────────────
        function renderQRPreview(container, qrCodes, title) {
            _sheetData = qrCodes;

            var preview = document.createElement('div');
            preview.style.marginTop = '15px';

            // top bar
            var bar = document.createElement('div');
            bar.style.cssText = 'display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:8px;';
            bar.innerHTML = '<strong style="color:#263c79;">' + qrCodes.length + ' QR code(s) generated &amp; saved to database</strong>';

            var btnGroup = document.createElement('div');
            btnGroup.style.cssText = 'display:flex;gap:8px;flex-wrap:wrap;';

            var btnSheet = document.createElement('button');
            btnSheet.className = 'btn btn-primary';
            btnSheet.innerHTML = '<i class="fas fa-th"></i> View &amp; Print Sheet';
            btnSheet.addEventListener('click', function() { openQRSheet(title); });

            var btnDl = document.createElement('button');
            btnDl.className = 'btn btn-success';
            btnDl.innerHTML = '<i class="fas fa-download"></i> Download All';
            btnDl.addEventListener('click', downloadAllIndividual);

            btnGroup.appendChild(btnSheet);
            btnGroup.appendChild(btnDl);
            bar.appendChild(btnGroup);
            preview.appendChild(bar);

            // thumbnail strip
            var strip = document.createElement('div');
            strip.style.cssText = 'display:flex;flex-wrap:wrap;gap:10px;margin-top:8px;';
            var show = qrCodes.slice(0, 8);
            show.forEach(function(q) {
                var card = document.createElement('div');
                card.style.cssText = 'border:1px solid #ddd;border-radius:6px;padding:8px;text-align:center;width:110px;';
                var img = document.createElement('img');
                img.src = q.base64 ? 'data:image/png;base64,' + q.base64 : q.image;
                img.style.cssText = 'width:80px;height:80px;object-fit:contain;';
                var code = document.createElement('div');
                code.style.cssText = 'font-size:10px;color:#263c79;font-weight:700;margin-top:4px;word-break:break-all;';
                code.textContent = q.code;
                var name = document.createElement('div');
                name.style.cssText = 'font-size:10px;color:#555;margin-top:2px;word-break:break-word;';
                name.textContent = q.name;
                card.appendChild(img);
                card.appendChild(code);
                card.appendChild(name);
                strip.appendChild(card);
            });
            if (qrCodes.length > 8) {
                var more = document.createElement('div');
                more.style.cssText = 'display:flex;align-items:center;justify-content:center;width:110px;color:#999;font-size:13px;';
                more.textContent = '+' + (qrCodes.length - 8) + ' more';
                strip.appendChild(more);
            }
            preview.appendChild(strip);
            container.appendChild(preview);
        }

        // ── QR Sheet modal ───────────────────────────────────────────────────
        function openQRSheet(title) {
            var overlay = document.getElementById('qrSheetOverlay');
            document.getElementById('qrSheetTitle').textContent =
                (title || 'QR Codes') + ' - ' + _sheetData.length + ' codes - ' + new Date().toLocaleString();

            var grid = document.getElementById('qrSheetGrid');
            grid.innerHTML = '';
            _sheetData.forEach(function(q) {
                var card = document.createElement('div');
                card.style.cssText = 'border:1px solid #ddd;border-radius:8px;padding:10px 8px;text-align:center;background:#fff;break-inside:avoid;overflow:hidden;min-width:0;';

                var img = document.createElement('img');
                img.src = q.base64 ? 'data:image/png;base64,' + q.base64 : q.image;
                img.style.cssText = 'width:90px;height:90px;object-fit:contain;display:block;margin:0 auto 6px;flex-shrink:0;';
                img.onerror = function() { this.style.background = '#eee'; };

                var code = document.createElement('div');
                code.style.cssText = 'font-size:10px;color:#263c79;font-weight:700;font-family:monospace;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-top:4px;';
                code.title = q.code;
                code.textContent = q.code;

                var name = document.createElement('div');
                name.style.cssText = 'font-size:9px;color:#555;margin-top:3px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;line-height:1.3;max-height:2.6em;';
                name.title = q.name;
                name.textContent = q.name;

                card.appendChild(img);
                card.appendChild(code);
                card.appendChild(name);
                grid.appendChild(card);
            });
            overlay.style.display = 'flex';
        }

        function closeQRSheet() {
            document.getElementById('qrSheetOverlay').style.display = 'none';
        }

        function printQRSheet() {
            var subtitle = escHtml(document.getElementById('qrSheetTitle').textContent);
            var rows = '';
            _sheetData.forEach(function(q) {
                var src = q.base64 ? 'data:image/png;base64,' + q.base64 : q.image;
                rows += '<div class="card"><img src="' + src + '"><div class="code">' + escHtml(q.code) + '</div><div class="name">' + escHtml(q.name) + '</div></div>';
            });
            var html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>QR Sheet</title>'
                + '<style>body{font-family:sans-serif;margin:20px;}'
                + '.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px;}'
                + '.card{border:1px solid #ddd;border-radius:6px;padding:8px;text-align:center;break-inside:avoid;overflow:hidden;}'
                + '.card img{width:90px;height:90px;display:block;margin:0 auto 5px;}'
                + '.code{font-size:10px;color:#263c79;font-weight:700;font-family:monospace;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-top:5px;}'
                + '.name{font-size:9px;color:#555;margin-top:2px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;line-height:1.3;max-height:2.6em;}'
                + 'h2{text-align:center;color:#263c79;margin-bottom:5px;}'
                + 'p{text-align:center;color:#666;font-size:12px;margin-bottom:15px;}'
                + '@media print{button{display:none}}'
                + '<' + '/style><' + '/head><body>'
                + '<h2>WIET Library - QR Code Sheet</h2>'
                + '<p>' + subtitle + '</p>'
                + '<div class="grid">' + rows + '</div>'
                + '<' + '/body><' + '/html>';
            var w = window.open('', '_blank');
            w.document.open();
            w.document.write(html);
            w.document.close();
            w.focus();
            setTimeout(function() { w.print(); }, 500);
        }

        async function downloadSheetImage() {
            const grid = document.getElementById('qrSheetGrid');
            const btns = document.getElementById('qrSheetBtns');
            btns.style.display = 'none';
            try {
                const canvas = await html2canvas(document.getElementById('qrSheetInner'), {scale: 2, backgroundColor: '#fff', useCORS: true});
                const a = document.createElement('a');
                a.download = 'QR_Sheet_' + Date.now() + '.png';
                a.href = canvas.toDataURL('image/png');
                a.click();
            } finally {
                btns.style.display = '';
            }
        }

        async function downloadAllIndividual() {
            if (!_sheetData.length) { alert('No QR codes loaded.'); return; }
            for (var i = 0; i < _sheetData.length; i++) {
                await downloadSingleQR(_sheetData[i]);
            }
        }

        function downloadSingleQR(q) {
            return new Promise(function(resolve) {
                var img = new Image();
                img.crossOrigin = 'anonymous';
                img.onload = function() {
                    var PAD = 14, LABEL = 34;
                    var W = img.width + PAD * 2, H = img.height + PAD * 2 + LABEL;
                    var c = document.createElement('canvas');
                    c.width = W; c.height = H;
                    var ctx = c.getContext('2d');
                    ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, W, H);
                    ctx.strokeStyle = '#ddd'; ctx.lineWidth = 1;
                    ctx.strokeRect(0.5, 0.5, W - 1, H - 1);
                    ctx.drawImage(img, PAD, PAD);
                    ctx.fillStyle = '#263c79'; ctx.font = 'bold 11px monospace'; ctx.textAlign = 'center';
                    ctx.fillText(String(q.code), W / 2, img.height + PAD + 14);
                    ctx.fillStyle = '#333'; ctx.font = '10px sans-serif';
                    var nm = String(q.name || '');
                    if (nm.length > 42) { nm = nm.substring(0, 39) + '...'; }
                    ctx.fillText(nm, W / 2, img.height + PAD + 28);
                    var a = document.createElement('a');
                    a.download = 'QR_' + String(q.code).replace(/[^a-zA-Z0-9_-]/g, '_') + '.png';
                    a.href = c.toDataURL('image/png');
                    a.click();
                    setTimeout(resolve, 150);
                };
                img.onerror = resolve;
                img.src = q.base64 ? 'data:image/png;base64,' + q.base64 : q.image;
            });
        }

        function showSuccess(container, message) {
            const d = document.createElement('div');
            d.className = 'alert alert-success';
            d.innerHTML = '<i class="fas fa-check-circle"></i><span>' + message + '</span>';
            container.prepend(d);
        }

        function showError(container, message) {
            container.innerHTML = '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i><span>' + message + '</span></div>';
        }

        // Initialize
        updateLabelOptions();
    </script>

    <!-- QR Sheet Overlay -->
    <div id="qrSheetOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;overflow-y:auto;padding:20px;justify-content:center;">
        <div id="qrSheetInner" style="background:#fff;border-radius:10px;max-width:1100px;width:100%;padding:25px;margin:auto;">
            <div style="text-align:center;border-bottom:2px solid #263c79;padding-bottom:12px;margin-bottom:15px;">
                <h2 style="color:#263c79;margin:0;">WIET Library – QR Code Sheet</h2>
                <p id="qrSheetTitle" style="color:#666;font-size:13px;margin-top:4px;"></p>
            </div>
            <div id="qrSheetBtns" style="display:flex;gap:10px;margin-bottom:15px;flex-wrap:wrap;">
                <button class="btn btn-primary" onclick="printQRSheet()"><i class="fas fa-print"></i> Print</button>
                <button class="btn btn-success" onclick="downloadSheetImage()"><i class="fas fa-image"></i> Download Sheet as Image</button>
                <button class="btn btn-info" onclick="downloadAllIndividual()"><i class="fas fa-images"></i> Download All Individual</button>
                <button class="btn btn-warning" onclick="closeQRSheet()"><i class="fas fa-times"></i> Close</button>
            </div>
            <div id="qrSheetGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px;"></div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
</body>
</html>
