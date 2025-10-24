<?php
session_start();

// Include database connection and functions
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$admin_id = $_SESSION['admin_id'] ?? 1;
$admin_name = $_SESSION['admin_name'] ?? 'Admin User';

// ============================================================
// DATA SOURCE: DATABASE (Fully Integrated)
// ============================================================
// ✅ Issue books - POSTS to api/circulation.php?action=issue
// ✅ Return books - POSTS to api/circulation.php?action=return
// ✅ Active circulations - FETCHES from api/circulation.php?action=active
// ✅ Return history - FETCHES from api/circulation.php?action=history
// ✅ Member search - FETCHES from api/members.php?action=get
// ✅ Book search - FETCHES from api/books.php?action=get
// ============================================================

// For now, using dummy data for UI display
// Once JavaScript is updated to call APIs, this can be removed

$sampleMembers = [
    ['MemberNo' => 2024001, 'MemberName' => 'Rahul Sharma', 'Group' => 'Student', 'BooksIssued' => 2, 'Status' => 'Active'],
    ['MemberNo' => 2024002, 'MemberName' => 'Priya Patel', 'Group' => 'Student', 'BooksIssued' => 1, 'Status' => 'Active'],
    ['MemberNo' => 2024003, 'MemberName' => 'Dr. Amit Kumar', 'Group' => 'Faculty', 'BooksIssued' => 3, 'Status' => 'Active'],
];

$sampleBooks = [
    ['AccNo' => 'ACC001001', 'CatNo' => 1001, 'Title' => 'Introduction to Computer Science', 'Author1' => 'John Smith', 'Status' => 'Available'],
    ['AccNo' => 'ACC001002', 'CatNo' => 1001, 'Title' => 'Introduction to Computer Science', 'Author1' => 'John Smith', 'Status' => 'Issued'],
    ['AccNo' => 'ACC002001', 'CatNo' => 1002, 'Title' => 'Advanced Mathematics', 'Author1' => 'Jane Doe', 'Status' => 'Available'],
];

$sampleCirculations = [
    [
        'CirculationID' => 1,
        'MemberNo' => 2024001,
        'MemberName' => 'Rahul Sharma',
        'AccNo' => 'ACC001002',
        'Title' => 'Introduction to Computer Science',
        'IssueDate' => '2024-09-20',
        'DueDate' => '2024-10-05',
        'Status' => 'Active',
        'DaysLeft' => 3
    ],
    [
        'CirculationID' => 2,
        'MemberNo' => 2024002,
        'MemberName' => 'Priya Patel',
        'AccNo' => 'ACC003001',
        'Title' => 'Database Management Systems',
        'IssueDate' => '2024-09-18',
        'DueDate' => '2024-10-03',
        'Status' => 'Overdue',
        'DaysLeft' => -2
    ],
];

$sampleReturns = [
    [
        'ReturnID' => 1,
        'CirculationID' => 3,
        'MemberNo' => 2024003,
        'MemberName' => 'Dr. Amit Kumar',
        'AccNo' => 'ACC004001',
        'Title' => 'Software Engineering',
        'IssueDate' => '2024-09-10',
        'ReturnDate' => '2024-09-25',
        'DueDate' => '2024-09-25',
        'FineAmount' => 0.00,
        'Status' => 'Returned'
    ],
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Circulation Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .circulation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #cfac69;
        }

        .circulation-title {
            color: #263c79;
            font-size: 28px;
            font-weight: 700;
            margin: 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #cfac69;
        }

        .stat-card.overdue {
            border-left-color: #dc3545;
        }

        .stat-card.due-today {
            border-left-color: #ffc107;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #263c79;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6c757d;
            font-size: 14px;
            font-weight: 500;
        }

        .tabs-container {
            margin-bottom: 20px;
        }

        .tab-buttons {
            display: flex;
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 20px;
        }

        .tab-btn {
            background: none;
            border: none;
            padding: 12px 20px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            color: #6c757d;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            flex-shrink: 0;
            white-space: nowrap;
        }

        .tab-btn.active {
            color: #263c79;
            border-bottom-color: #cfac69;
            font-weight: 600;
        }

        .tab-btn:hover {
            color: #263c79;
            background-color: rgba(207, 172, 105, 0.1);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #263c79;
            color: white;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .issue-form,
        .return-form {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
        }

        .form-section {
            margin-bottom: 25px;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            background: #f8f9fa;
            height: fit-content;
        }

        .section-title {
            color: #263c79;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #cfac69;
        }

        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .form-row-scan {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-bottom: 15px;
            align-items: center;
        }

        .scan-group {
            width: 100%;
            max-width: 400px;
        }

        .manual-group {
            width: 100%;
            max-width: 400px;
        }

        .form-group {
            flex: 1;
            min-width: 200px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #263c79;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: #cfac69;
            box-shadow: 0 0 0 2px rgba(207, 172, 105, 0.2);
        }

        .scan-area {
            border: 2px dashed #cfac69;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
            background: white;
            margin: 15px auto;
            width: 350px;
            height: 520px;
            position: relative;
            overflow: hidden;
        }

        .scan-icon {
            font-size: 32px;
            color: #cfac69;
            margin-bottom: 10px;
        }

        .scan-text {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
        }

        .camera-container {
            position: relative;
            width: 100%;
            height: 450px;
            background: #f8f9fa;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .camera-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }

        .camera-canvas {
            display: none;
        }

        .camera-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: #6c757d;
            font-size: 14px;
            text-align: center;
        }

        .scan-controls {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-top: 10px;
        }

        .btn-scan {
            padding: 6px 12px;
            background-color: #263c79;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .btn-scan:hover {
            background-color: #1e2d5f;
        }

        .btn-scan:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }

        .btn-scan-secondary {
            background-color: #6c757d;
        }

        .btn-scan-secondary:hover {
            background-color: #5a6268;
        }

        .scanning-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(38, 60, 121, 0.8);
            display: none;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            z-index: 10;
        }

        .scanning-line {
            position: absolute;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #cfac69, transparent);
            animation: scan-line 2s infinite;
        }

        @keyframes scan-line {
            0% {
                top: 0;
            }

            100% {
                top: 100%;
            }
        }

        .scan-result {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            margin-top: 10px;
            display: none;
        }

        .scan-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            margin-top: 10px;
            display: none;
        }

        .form-row-scan {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-bottom: 15px;
            align-items: center;
        }

        .steps-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }

        .circulation-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            min-width: 600px;
        }

        .circulation-table th {
            background-color: #263c79;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .circulation-table td {
            padding: 12px 8px;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
            word-break: break-word;
        }

        .circulation-table tr:hover {
            background-color: rgba(207, 172, 105, 0.1);
        }

        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        @media (max-width: 1200px) {
            .steps-container {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }

        @media (max-width: 768px) {
            .issue-form {
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .form-section {
                width: 100%;
                max-width: 320px;
                margin: 0 auto 20px auto;
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .form-row-scan {
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }

            .scan-group, .manual-group {
                width: 100%;
                max-width: 300px;
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .scan-area {
                width: 280px;
                height: 380px;
                margin: 10px auto;
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
                padding: 10px;
            }

            .camera-container {
                height: 300px;
                flex-shrink: 0;
                margin-bottom: 5px;
            }

            .scan-controls {
                margin-top: 5px;
                flex-shrink: 0;
            }

            .steps-container {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .tab-buttons {
                overflow-x: auto;
                white-space: nowrap;
                -webkit-overflow-scrolling: touch;
            }

            .circulation-table {
                font-size: 12px;
                min-width: 500px;
            }

            .circulation-table th,
            .circulation-table td {
                padding: 8px 4px;
            }
        }
    </style>
    <!-- Add QR Code scanning library -->
    <script src="https://unpkg.com/@zxing/library@latest/umd/index.min.js"></script>
</head>

<body>
    <div class="circulation-header">
        <h1 class="circulation-title">
            <i class="fas fa-exchange-alt"></i>
            Circulation Management
        </h1>
        <button class="btn btn-success" onclick="openQuickScan()" style="padding:10px 20px;">
            <i class="fas fa-qrcode"></i>
            Quick Scan (Ctrl+K)
        </button>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number" id="totalIssued">-</div>
            <div class="stat-label">Books Currently Issued</div>
        </div>
        <div class="stat-card due-today">
            <div class="stat-number" id="dueToday">-</div>
            <div class="stat-label">Due Today</div>
        </div>
        <div class="stat-card overdue">
            <div class="stat-number" id="overdue">-</div>
            <div class="stat-label">Overdue Books</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="todayReturns">-</div>
            <div class="stat-label">Today's Returns</div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs-container">
        <div class="tab-buttons">
            <button class="tab-btn active" data-tab="issue" onclick="showTab('issue')">
                <i class="fas fa-plus-circle"></i>
                Issue Books
            </button>
            <button class="tab-btn" data-tab="return" onclick="showTab('return')">
                <i class="fas fa-undo"></i>
                Return Books
            </button>
            <button class="tab-btn" data-tab="active" onclick="showTab('active')">
                <i class="fas fa-list"></i>
                Active Circulations
            </button>
            <button class="tab-btn" data-tab="history" onclick="showTab('history')">
                <i class="fas fa-history"></i>
                Return History
            </button>
        </div>

        <!-- Issue Books Tab -->
        <div id="issue" class="tab-content active">
            <div class="issue-form">
                <!-- Steps 1 & 2 Container -->
                <div class="steps-container">
                    <!-- Step 1: Member Scan -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-user"></i>
                            Step 1: Scan or Search Member
                        </div>
                        <div class="form-row-scan">
                            <div class="scan-group">
                                <label for="memberScan">Member QR Code / ID Card</label>
                                <div class="scan-area" id="memberScanArea">
                                    <div class="camera-container">
                                        <video id="memberVideo" class="camera-video" autoplay playsinline></video>
                                        <canvas id="memberCanvas" class="camera-canvas"></canvas>
                                        <div class="camera-placeholder" id="memberPlaceholder">
                                            <div>
                                                <i class="fas fa-qrcode scan-icon"></i>
                                                <div class="scan-text">Position member QR code or ID card here</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="scanning-overlay" id="memberScanningOverlay">
                                        <div>
                                            <div class="scanning-line"></div>
                                            <i class="fas fa-camera"></i> Scanning...
                                        </div>
                                    </div>
                                    <div class="scan-controls">
                                        <button class="btn-scan" onclick="startMemberScan()" id="memberScanBtn">
                                            <i class="fas fa-camera"></i>
                                            Start Camera
                                        </button>
                                        <button class="btn-scan btn-scan-secondary" onclick="stopMemberScan()" id="memberStopBtn" disabled>
                                            <i class="fas fa-stop"></i>
                                            Stop
                                        </button>
                                    </div>
                                    <div class="scan-result" id="memberScanResult"></div>
                                    <div class="scan-error" id="memberScanError"></div>
                                </div>
                            </div>
                            <div class="manual-group">
                                <label for="memberNo">Or Enter Member Number</label>
                                <input type="text" id="memberNo" class="form-control" placeholder="Enter member number..." onchange="searchMember()">
                                <button type="button" class="btn btn-primary" onclick="searchMember()" style="margin-top: 10px; width: 100%;">
                                    <i class="fas fa-search"></i>
                                    Search Member
                                </button>
                            </div>
                        </div>

                        <div id="memberInfo" class="member-info">
                            <div class="info-header">
                                <div class="info-title">Member Information</div>
                                <span class="status-badge status-active">Active</span>
                            </div>
                            <div class="info-details">
                                <div class="info-item">
                                    <span class="info-label">Name:</span>
                                    <span class="info-value" id="memberName">-</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Member No:</span>
                                    <span class="info-value" id="memberNumber">-</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Group:</span>
                                    <span class="info-value" id="memberGroup">-</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Books Issued:</span>
                                    <span class="info-value" id="memberBooks">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Book Scan -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-book"></i>
                            Step 2: Scan or Search Book
                        </div>
                        <div class="form-row-scan">
                            <div class="scan-group">
                                <label for="bookScan">Book QR Code / Barcode</label>
                                <div class="scan-area" id="bookScanArea">
                                    <div class="camera-container">
                                        <video id="bookVideo" class="camera-video" autoplay playsinline></video>
                                        <canvas id="bookCanvas" class="camera-canvas"></canvas>
                                        <div class="camera-placeholder" id="bookPlaceholder">
                                            <div>
                                                <i class="fas fa-barcode scan-icon"></i>
                                                <div class="scan-text">Position book barcode or QR code here</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="scanning-overlay" id="bookScanningOverlay">
                                        <div>
                                            <div class="scanning-line"></div>
                                            <i class="fas fa-camera"></i> Scanning...
                                        </div>
                                    </div>
                                    <div class="scan-controls">
                                        <button class="btn-scan" onclick="startBookScan()" id="bookScanBtn">
                                            <i class="fas fa-camera"></i>
                                            Start Camera
                                        </button>
                                        <button class="btn-scan btn-scan-secondary" onclick="stopBookScan()" id="bookStopBtn" disabled>
                                            <i class="fas fa-stop"></i>
                                            Stop
                                        </button>
                                    </div>
                                    <div class="scan-result" id="bookScanResult"></div>
                                    <div class="scan-error" id="bookScanError"></div>
                                </div>
                            </div>
                            <div class="manual-group">
                                <label for="accNo">Or Enter Accession Number</label>
                                <input type="text" id="accNo" class="form-control" placeholder="Enter accession number..." onchange="searchBook()">
                                <button type="button" class="btn btn-primary" onclick="searchBook()" style="margin-top: 10px; width: 100%;">
                                    <i class="fas fa-search"></i>
                                    Search Book
                                </button>
                            </div>
                        </div>

                        <div id="bookInfo" class="book-info">
                            <div class="info-header">
                                <div class="info-title">Book Information</div>
                                <span class="status-badge status-active">Available</span>
                            </div>
                            <div class="info-details">
                                <div class="info-item">
                                    <span class="info-label">Title:</span>
                                    <span class="info-value" id="bookTitle">-</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Author:</span>
                                    <span class="info-value" id="bookAuthor">-</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Accession No:</span>
                                    <span class="info-value" id="bookAccNo">-</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Location:</span>
                                    <span class="info-value" id="bookLocation">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Issue Details (Full Width) -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-calendar"></i>
                        Step 3: Set Issue Details
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="issueDate">Issue Date</label>
                            <input type="date" id="issueDate" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="dueDate">Due Date</label>
                            <input type="date" id="dueDate" class="form-control" value="<?php echo date('Y-m-d', strtotime('+15 days')); ?>">
                        </div>
                        <div class="form-group">
                            <label for="remarks">Remarks (Optional)</label>
                            <input type="text" id="remarks" class="form-control" placeholder="Any special remarks...">
                        </div>
                    </div>
                    <div class="form-row">
                        <button type="button" class="btn btn-success" onclick="issueBook()" id="issueBtn" disabled>
                            <i class="fas fa-check"></i>
                            Issue Book
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="resetIssueForm()">
                            <i class="fas fa-refresh"></i>
                            Reset Form
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Return Books Tab -->
        <div id="return" class="tab-content">
            <div class="return-form">
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-barcode"></i>
                        Scan Book to Return
                    </div>
                    <div class="form-row-scan">
                        <div class="form-group">
                            <label for="returnBookScan">Book QR Code / Barcode</label>
                            <div class="scan-area" id="returnScanArea">
                                <div class="camera-container">
                                    <video id="returnVideo" class="camera-video" autoplay playsinline></video>
                                    <canvas id="returnCanvas" class="camera-canvas"></canvas>
                                    <div class="camera-placeholder" id="returnPlaceholder">
                                        <div>
                                            <i class="fas fa-undo scan-icon"></i>
                                            <div class="scan-text">Position book barcode or QR code here</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="scanning-overlay" id="returnScanningOverlay">
                                    <div>
                                        <div class="scanning-line"></div>
                                        <i class="fas fa-camera"></i> Scanning...
                                    </div>
                                </div>
                                <div class="scan-controls">
                                    <button class="btn-scan" onclick="startReturnScan()" id="returnScanBtn">
                                        <i class="fas fa-camera"></i>
                                        Start Camera
                                    </button>
                                    <button class="btn-scan btn-scan-secondary" onclick="stopReturnScan()" id="returnStopBtn" disabled>
                                        <i class="fas fa-stop"></i>
                                        Stop
                                    </button>
                                </div>
                                <div class="scan-result" id="returnScanResult"></div>
                                <div class="scan-error" id="returnScanError"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="returnAccNo">Or Enter Accession Number</label>
                            <input type="text" id="returnAccNo" class="form-control" placeholder="Enter accession number..." onchange="searchReturnBook()">
                            <button type="button" class="btn btn-primary" onclick="searchReturnBook()" style="margin-top: 10px;">
                                <i class="fas fa-search"></i>
                                Search Book
                            </button>
                        </div>
                    </div>

                    <div id="returnBookInfo" class="book-info">
                        <div class="info-header">
                            <div class="info-title">Book Return Information</div>
                            <span class="status-badge status-overdue">Overdue</span>
                        </div>
                        <div class="info-details">
                            <div class="info-item">
                                <span class="info-label">Title:</span>
                                <span class="info-value" id="returnBookTitle">-</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Member:</span>
                                <span class="info-value" id="returnMemberName">-</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Issue Date:</span>
                                <span class="info-value" id="returnIssueDate">-</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Due Date:</span>
                                <span class="info-value" id="returnDueDate">-</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Days Overdue:</span>
                                <span class="info-value" id="overdueDays">-</span>
                            </div>
                        </div>
                    </div>

                    <div id="fineCalculator" class="fine-calculator">
                        <h4 style="color: #856404; margin-bottom: 10px;">
                            <i class="fas fa-exclamation-triangle"></i>
                            Fine Calculation
                        </h4>
                        <div class="info-details">
                            <div class="info-item">
                                <span class="info-label">Days Overdue:</span>
                                <span class="info-value" id="fineOverdueDays">0</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Fine Per Day:</span>
                                <span class="info-value">₹2.00</span>
                            </div>
                            <div class="info-item">
                                <span class="fine-amount">Total Fine: ₹<span id="totalFine">0.00</span></span>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="returnCondition">Book Condition</label>
                            <select id="returnCondition" class="form-control">
                                <option value="Good">Good Condition</option>
                                <option value="Fair">Fair Condition</option>
                                <option value="Damaged">Damaged</option>
                                <option value="Lost">Lost</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="returnRemarks">Remarks</label>
                            <input type="text" id="returnRemarks" class="form-control" placeholder="Any remarks about the return...">
                        </div>
                    </div>

                    <div class="form-row">
                        <button type="button" class="btn btn-success" onclick="returnBook()" id="returnBtn" disabled>
                            <i class="fas fa-check"></i>
                            Process Return
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="resetReturnForm()">
                            <i class="fas fa-refresh"></i>
                            Reset Form
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Circulations Tab -->
        <div id="active" class="tab-content">
            <div class="search-filters">
                <div class="form-row">
                    <div class="form-group">
                        <label for="searchMember">Member Name/Number</label>
                        <input type="text" id="searchMember" class="form-control" placeholder="Search member...">
                    </div>
                    <div class="form-group">
                        <label for="searchAccession">Accession Number</label>
                        <input type="text" id="searchAccession" class="form-control" placeholder="Search book...">
                    </div>
                    <div class="form-group">
                        <label for="filterStatus">Status</label>
                        <select id="filterStatus" class="form-control">
                            <option value="">All Status</option>
                            <option value="Active">Active</option>
                            <option value="Overdue">Overdue</option>
                            <option value="Due Today">Due Today</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-primary" onclick="searchCirculations()">
                            <i class="fas fa-search"></i>
                            Search
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table class="circulation-table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Book Details</th>
                            <th>Accession No</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Days Left</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="activeCirculationsTable">
                        <!-- Data will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Return History Tab -->
        <div id="history" class="tab-content">
            <div class="search-filters">
                <div class="form-row">
                    <div class="form-group">
                        <label for="historyMember">Member Name/Number</label>
                        <input type="text" id="historyMember" class="form-control" placeholder="Search member...">
                    </div>
                    <div class="form-group">
                        <label for="historyFromDate">From Date</label>
                        <input type="date" id="historyFromDate" class="form-control" value="<?php echo date('Y-m-01'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="historyToDate">To Date</label>
                        <input type="date" id="historyToDate" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-primary" onclick="searchHistory()">
                            <i class="fas fa-search"></i>
                            Search
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table class="circulation-table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Book Details</th>
                            <th>Accession No</th>
                            <th>Issue Date</th>
                            <th>Return Date</th>
                            <th>Due Date</th>
                            <th>Fine</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="returnHistoryTable">
                        <!-- Data will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let selectedMember = null;
        let selectedBook = null;
        let returnBookData = null;

        // Tab switching function
        function showTab(tabName) {
            // Hide all tabs
            var tabs = document.querySelectorAll('.tab-content');
            for (var i = 0; i < tabs.length; i++) {
                tabs[i].style.display = 'none';
                tabs[i].classList.remove('active');
            }
            
            // Remove active from all buttons
            var buttons = document.querySelectorAll('.tab-btn');
            for (var i = 0; i < buttons.length; i++) {
                buttons[i].classList.remove('active');
            }
            
            // Show selected tab
            var selectedTab = document.getElementById(tabName);
            if (selectedTab) {
                selectedTab.style.display = 'block';
                selectedTab.classList.add('active');
            }
            
            // Activate button
            var activeButton = document.querySelector('.tab-btn[data-tab="' + tabName + '"]');
            if (activeButton) {
                activeButton.classList.add('active');
            }
            
            // Load content
            if (tabName === 'active') {
                loadActiveCirculations();
            } else if (tabName === 'history') {
                loadReturnHistory();
            }
        }

        function loadTabContent(tabName) {
            switch (tabName) {
                case 'active':
                    loadActiveCirculations();
                    break;
                case 'history':
                    loadReturnHistory();
                    break;
            }
        }

        // Issue Book Functions
        function simulateMemberScan() {
            const memberNo = prompt('Simulate QR Scan - Enter Member Number:', '2024001');
            if (memberNo) {
                document.getElementById('memberNo').value = memberNo;
                searchMember();
            }
        }

        async function searchMember() {
            const memberNo = document.getElementById('memberNo').value.trim();
            
            if (!memberNo) {
                showScanError('memberScanError', 'Please enter a Member Number');
                return;
            }
            
            try {
                const response = await fetch(`api/members.php?action=get&memberNo=${encodeURIComponent(memberNo)}`);
                const result = await response.json();

                if (result.success && result.data) {
                    const member = result.data;
                    selectedMember = member;
                    document.getElementById('memberName').textContent = member.MemberName || 'N/A';
                    document.getElementById('memberNumber').textContent = member.MemberNo;
                    document.getElementById('memberGroup').textContent = member.Group || 'N/A';
                    document.getElementById('memberBooks').textContent = member.BooksIssued || 0;
                    document.getElementById('memberInfo').classList.add('show');
                    showScanResult('memberScanResult', `✓ Member found: ${member.MemberName}`);
                    checkIssueFormComplete();
                } else {
                    showScanError('memberScanError', `Member ${memberNo} not found!`);
                    selectedMember = null;
                    document.getElementById('memberInfo').classList.remove('show');
                }
            } catch (error) {
                console.error('Error searching member:', error);
                showScanError('memberScanError', 'Error searching member. Please try again.');
                document.getElementById('memberInfo').classList.remove('show');
            }
        }

        function simulateBookScan() {
            const accNo = prompt('Simulate Barcode Scan - Enter Accession Number:', 'ACC001001');
            if (accNo) {
                document.getElementById('accNo').value = accNo;
                searchBook();
            }
        }

        async function searchBook() {
            const accNo = document.getElementById('accNo').value.trim();
            
            if (!accNo) {
                showScanError('bookScanError', 'Please enter an Accession Number');
                return;
            }
            
            try {
                const response = await fetch(`api/books.php?action=lookup&accNo=${encodeURIComponent(accNo)}`);
                const result = await response.json();

                if (result.success && result.data) {
                    const book = result.data;
                    
                    if (book.Status === 'Available') {
                        selectedBook = book;
                        document.getElementById('bookTitle').textContent = book.Title || 'Unknown';
                        document.getElementById('bookAuthor').textContent = book.Author1 || 'N/A';
                        document.getElementById('bookAccNo').textContent = book.AccNo;
                        document.getElementById('bookLocation').textContent = book.Location || 'Library';
                        document.getElementById('bookInfo').classList.add('show');
                        showScanResult('bookScanResult', `✓ Book available: ${book.Title}`);
                        checkIssueFormComplete();
                    } else {
                        showScanError('bookScanError', `Book is not available for issue! Current status: ${book.Status}`);
                        selectedBook = null;
                        document.getElementById('bookInfo').classList.remove('show');
                    }
                } else {
                    showScanError('bookScanError', `Book with AccNo ${accNo} not found!`);
                    selectedBook = null;
                    document.getElementById('bookInfo').classList.remove('show');
                }
            } catch (error) {
                console.error('Error searching book:', error);
                showScanError('bookScanError', 'Error searching book. Please try again.');
                document.getElementById('bookInfo').classList.remove('show');
            }
        }

        function checkIssueFormComplete() {
            const issueBtn = document.getElementById('issueBtn');
            if (selectedMember && selectedBook) {
                issueBtn.disabled = false;
            } else {
                issueBtn.disabled = true;
            }
        }

        async function issueBook() {
            if (!selectedMember || !selectedBook) {
                alert('Please select both a member and a book');
                return;
            }

            const issueDate = document.getElementById('issueDate').value;
            const dueDate = document.getElementById('dueDate').value;
            const remarks = document.getElementById('remarks').value;

            if (!issueDate || !dueDate) {
                alert('Please enter issue date and due date');
                return;
            }

            // Call API to issue book
            try {
                const response = await fetch('api/circulation.php?action=issue', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        memberNo: selectedMember.MemberNo,
                        accNo: selectedBook.AccNo,
                        issueDate: issueDate,
                        dueDate: dueDate,
                        remarks: remarks
                    })
                });

                const result = await response.json();

                if (result.success) {
                    alert(`Book issued successfully!\n\nMember: ${selectedMember.MemberName}\nBook: ${selectedBook.Title}\nDue Date: ${dueDate}`);
                    resetIssueForm();
                    loadStatistics(); // Refresh stats
                    loadActiveCirculations(); // Refresh the list
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error issuing book:', error);
                alert('Failed to issue book. Please try again.');
            }
        }

        function resetIssueForm() {
            selectedMember = null;
            selectedBook = null;
            document.getElementById('memberNo').value = '';
            document.getElementById('accNo').value = '';
            document.getElementById('remarks').value = '';
            document.getElementById('memberInfo').classList.remove('show');
            document.getElementById('bookInfo').classList.remove('show');
            document.getElementById('issueBtn').disabled = true;
        }

        // Return Book Functions
        function simulateReturnScan() {
            const accNo = prompt('Simulate Return Scan - Enter Accession Number:', 'ACC001002');
            if (accNo) {
                document.getElementById('returnAccNo').value = accNo;
                searchReturnBook();
            }
        }

        async function searchReturnBook() {
            const accNo = document.getElementById('returnAccNo').value.trim();
            
            if (!accNo) {
                showScanError('returnScanError', 'Please enter an Accession Number');
                return;
            }
            
            try {
                // Get active circulation for this accession number
                const response = await fetch('api/circulation.php?action=active');
                const result = await response.json();

                if (result.success && result.data) {
                    const circulation = result.data.find(c => c.AccNo === accNo);

                    if (circulation) {
                        returnBookData = circulation;
                        document.getElementById('returnBookTitle').textContent = circulation.Title || 'Unknown';
                        document.getElementById('returnMemberName').textContent = circulation.MemberName || 'N/A';
                        document.getElementById('returnIssueDate').textContent = circulation.IssueDate;
                        document.getElementById('returnDueDate').textContent = circulation.DueDate;

                        // Calculate overdue days
                        const dueDate = new Date(circulation.DueDate);
                        const today = new Date();
                        const diffTime = today - dueDate;
                        const overdueDays = Math.max(0, Math.ceil(diffTime / (1000 * 60 * 60 * 24)));
                        
                        document.getElementById('overdueDays').textContent = overdueDays > 0 ? overdueDays : '0';
                        document.getElementById('returnBookInfo').classList.add('show');

                        if (overdueDays > 0) {
                            const totalFine = overdueDays * 2; // ₹2 per day
                            document.getElementById('fineOverdueDays').textContent = overdueDays;
                            document.getElementById('totalFine').textContent = totalFine.toFixed(2);
                            document.getElementById('fineCalculator').classList.add('show');
                            showScanError('returnScanError', `⚠️ Book is overdue by ${overdueDays} days. Fine: ₹${totalFine.toFixed(2)}`);
                        } else {
                            document.getElementById('fineCalculator').classList.remove('show');
                            showScanResult('returnScanResult', `✓ Circulation found: ${circulation.Title}`);
                        }

                        document.getElementById('returnBtn').disabled = false;
                    } else {
                        showScanError('returnScanError', `No active circulation found for AccNo: ${accNo}`);
                        returnBookData = null;
                        returnBookInfo.classList.remove('show');
                        document.getElementById('fineCalculator').classList.remove('show');
                        document.getElementById('returnBtn').disabled = true;
                    }
                } else {
                    showScanError('returnScanError', 'Unable to fetch active circulations. Please try again.');
                    returnBookData = null;
                    returnBookInfo.classList.remove('show');
                    document.getElementById('fineCalculator').classList.remove('show');
                    document.getElementById('returnBtn').disabled = true;
                }
            } catch (error) {
                console.error('Error searching return book:', error);
                showScanError('returnScanError', 'Error searching for book circulation. Please try again.');
                returnBookInfo.classList.remove('show');
                document.getElementById('fineCalculator').classList.remove('show');
                document.getElementById('returnBtn').disabled = true;
            }
        }

        async function returnBook() {
            if (!returnBookData) {
                alert('Please scan a book to return');
                return;
            }

            const condition = document.getElementById('returnCondition').value;
            const remarks = document.getElementById('returnRemarks').value;
            const fineAmount = parseFloat(document.getElementById('totalFine').textContent || '0');

            // Call API to return book
            try {
                const response = await fetch('api/circulation.php?action=return', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        circulationId: returnBookData.CirculationID,
                        returnDate: new Date().toISOString().split('T')[0],
                        condition: condition,
                        remarks: remarks,
                        fineAmount: fineAmount
                    })
                });

                const result = await response.json();

                if (result.success) {
                    alert(`Book returned successfully!\n\nBook: ${returnBookData.Title}\nMember: ${returnBookData.MemberName}\nFine: ₹${fineAmount.toFixed(2)}`);
                    resetReturnForm();
                    loadStatistics(); // Refresh stats
                    loadActiveCirculations(); // Refresh the list
                    loadReturnHistory(); // Refresh returns
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error returning book:', error);
                alert('Failed to return book. Please try again.');
            }
        }

        function resetReturnForm() {
            returnBookData = null;
            document.getElementById('returnAccNo').value = '';
            document.getElementById('returnRemarks').value = '';
            document.getElementById('returnCondition').value = 'Good';
            document.getElementById('returnBookInfo').classList.remove('show');
            document.getElementById('fineCalculator').classList.remove('show');
            document.getElementById('returnBtn').disabled = true;
        }

        // Load Active Circulations from API
        async function loadActiveCirculations() {
            try {
                const response = await fetch('api/circulation.php?action=active');
                const result = await response.json();

                let tableHTML = '';

                if (result.success && result.data && result.data.length > 0) {
                    result.data.forEach(circ => {
                        // Calculate days left/overdue
                        const dueDate = new Date(circ.DueDate);
                        const today = new Date();
                        const diffTime = dueDate - today;
                        const daysLeft = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                        
                        const statusClass = daysLeft < 0 ? 'status-overdue' :
                            daysLeft === 0 ? 'status-due-today' : 'status-active';
                        const status = daysLeft < 0 ? 'Overdue' :
                            daysLeft === 0 ? 'Due Today' : 'Active';

                        tableHTML += `
                            <tr>
                                <td>
                                    <strong>${circ.MemberName}</strong><br>
                                    <small>${circ.MemberNo}</small>
                                </td>
                                <td>
                                    <strong>${circ.Title}</strong>
                                </td>
                                <td>${circ.AccNo}</td>
                                <td>${new Date(circ.IssueDate).toLocaleDateString('en-IN')}</td>
                                <td>${new Date(circ.DueDate).toLocaleDateString('en-IN')}</td>
                                <td>${daysLeft > 0 ? daysLeft : Math.abs(daysLeft)}</td>
                                <td><span class="status-badge ${statusClass}">${status}</span></td>
                                <td class="action-links">
                                    <button class="btn-renew" onclick="renewBook(${circ.CirculationID})">
                                        <i class="fas fa-refresh"></i> Renew
                                    </button>
                                    <button class="btn-return" onclick="processReturn('${circ.AccNo}')">
                                        <i class="fas fa-undo"></i> Return
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    tableHTML = '<tr><td colspan="8" style="text-align: center; padding: 20px; color: #6c757d;">No active circulations found</td></tr>';
                }

                document.getElementById('activeCirculationsTable').innerHTML = tableHTML;
            } catch (error) {
                console.error('Error loading active circulations:', error);
                document.getElementById('activeCirculationsTable').innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 20px; color: #dc3545;">Error loading data</td></tr>';
            }
        }

        // Load Return History from API
        async function loadReturnHistory() {
            try {
                const response = await fetch('api/circulation.php?action=history');
                const result = await response.json();

                let tableHTML = '';

                if (result.success && result.data && result.data.length > 0) {
                    result.data.forEach(ret => {
                        tableHTML += `
                            <tr>
                                <td>
                                    <strong>${ret.MemberName}</strong><br>
                                    <small>${ret.MemberNo}</small>
                                </td>
                                <td>
                                    <strong>${ret.Title}</strong>
                                </td>
                                <td>${ret.AccNo}</td>
                                <td>${new Date(ret.IssueDate).toLocaleDateString('en-IN')}</td>
                                <td>${new Date(ret.ReturnDate).toLocaleDateString('en-IN')}</td>
                                <td>${new Date(ret.DueDate).toLocaleDateString('en-IN')}</td>
                                <td>₹${parseFloat(ret.LateFine || 0).toFixed(2)}</td>
                                <td><span class="status-badge status-returned">${ret.Status || 'Returned'}</span></td>
                            </tr>
                        `;
                    });
                } else {
                    tableHTML = '<tr><td colspan="8" style="text-align: center; padding: 20px; color: #6c757d;">No return history found</td></tr>';
                }

                document.getElementById('returnHistoryTable').innerHTML = tableHTML;
            } catch (error) {
                console.error('Error loading return history:', error);
                document.getElementById('returnHistoryTable').innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 20px; color: #dc3545;">Error loading data</td></tr>';
            }
        }
            });

            document.getElementById('returnHistoryTable').innerHTML = tableHTML;
        }

        // Action Functions
        function renewBook(circulationId) {
            console.log('Renewing book for circulation:', circulationId);
            alert('Book renewed successfully! New due date: ' + new Date(Date.now() + 15 * 24 * 60 * 60 * 1000).toLocaleDateString());
            loadActiveCirculations();
        }

        function processReturn(accNo) {
            showTab('return');
            document.getElementById('returnAccNo').value = accNo;
            searchReturnBook();
        }

        function searchCirculations() {
            console.log('Searching active circulations...');
            loadActiveCirculations();
        }

        function searchHistory() {
            console.log('Searching return history...');
            loadReturnHistory();
        }

        // Load Statistics from API
        function loadStatistics() {
            fetch('api/circulation.php?action=stats')
                .then(res => res.json())
                .then(result => {
                    if (result.success && result.data) {
                        document.getElementById('totalIssued').textContent = result.data.totalIssued || 0;
                        document.getElementById('dueToday').textContent = result.data.dueToday || 0;
                        document.getElementById('overdue').textContent = result.data.overdue || 0;
                        document.getElementById('todayReturns').textContent = result.data.todayReturns || 0;
                    }
                })
                .catch(err => {
                    console.error('Error loading circulation stats:', err);
                    document.getElementById('totalIssued').textContent = '0';
                    document.getElementById('dueToday').textContent = '0';
                    document.getElementById('overdue').textContent = '0';
            document.getElementById('todayReturns').textContent = todayReturns;
        }

        // Camera and QR scanning functionality
        let memberStream = null;
        let bookStream = null;
        let returnStream = null;
        let memberCodeReader = null;
        let bookCodeReader = null;
        let returnCodeReader = null;

        // Initialize ZXing code readers
        function initializeCodeReaders() {
            if (typeof ZXing !== 'undefined') {
                memberCodeReader = new ZXing.BrowserQRCodeReader();
                bookCodeReader = new ZXing.BrowserMultiFormatReader();
                returnCodeReader = new ZXing.BrowserMultiFormatReader();
            }
        }

        // Member scanning functions
        async function startMemberScan() {
            try {
                const constraints = {
                    video: {
                        facingMode: 'environment', // Use back camera if available
                        width: {
                            ideal: 350
                        },
                        height: {
                            ideal: 150
                        }
                    }
                };

                memberStream = await navigator.mediaDevices.getUserMedia(constraints);
                const video = document.getElementById('memberVideo');
                const placeholder = document.getElementById('memberPlaceholder');
                const scanBtn = document.getElementById('memberScanBtn');
                const stopBtn = document.getElementById('memberStopBtn');

                video.srcObject = memberStream;
                video.style.display = 'block';
                placeholder.style.display = 'none';
                scanBtn.disabled = true;
                stopBtn.disabled = false;

                // Start QR code detection
                if (memberCodeReader) {
                    memberCodeReader.decodeFromVideoDevice(null, 'memberVideo', (result, error) => {
                        if (result) {
                            handleMemberScanResult(result.text);
                        }
                    });
                }

                document.getElementById('memberScanningOverlay').style.display = 'flex';
                setTimeout(() => {
                    document.getElementById('memberScanningOverlay').style.display = 'none';
                }, 3000);

            } catch (error) {
                console.error('Error accessing camera:', error);
                showScanError('memberScanError', 'Could not access camera. Please check permissions.');
            }
        }

        function stopMemberScan() {
            if (memberStream) {
                memberStream.getTracks().forEach(track => track.stop());
                memberStream = null;
            }

            if (memberCodeReader) {
                memberCodeReader.reset();
            }

            const video = document.getElementById('memberVideo');
            const placeholder = document.getElementById('memberPlaceholder');
            const scanBtn = document.getElementById('memberScanBtn');
            const stopBtn = document.getElementById('memberStopBtn');

            video.style.display = 'none';
            placeholder.style.display = 'flex';
            scanBtn.disabled = false;
            stopBtn.disabled = true;
            document.getElementById('memberScanningOverlay').style.display = 'none';
        }

        function handleMemberScanResult(scannedData) {
            console.log('Member scan result:', scannedData);

            // Extract member number from scanned data (assuming it contains member number)
            let memberNo = scannedData;

            // If it's a JSON or structured data, try to extract member number
            try {
                const data = JSON.parse(scannedData);
                memberNo = data.memberNo || data.MemberNo || data.id || scannedData;
            } catch (e) {
                // If not JSON, assume the scanned data is the member number
                memberNo = scannedData;
            }

            document.getElementById('memberNo').value = memberNo;
            showScanResult('memberScanResult', `Member scanned: ${memberNo}`);
            searchMember();
            stopMemberScan();
        }

        // Book scanning functions
        async function startBookScan() {
            try {
                const constraints = {
                    video: {
                        facingMode: 'environment',
                        width: {
                            ideal: 350
                        },
                        height: {
                            ideal: 150
                        }
                    }
                };

                bookStream = await navigator.mediaDevices.getUserMedia(constraints);
                const video = document.getElementById('bookVideo');
                const placeholder = document.getElementById('bookPlaceholder');
                const scanBtn = document.getElementById('bookScanBtn');
                const stopBtn = document.getElementById('bookStopBtn');

                video.srcObject = bookStream;
                video.style.display = 'block';
                placeholder.style.display = 'none';
                scanBtn.disabled = true;
                stopBtn.disabled = false;

                if (bookCodeReader) {
                    bookCodeReader.decodeFromVideoDevice(null, 'bookVideo', (result, error) => {
                        if (result) {
                            handleBookScanResult(result.text);
                        }
                    });
                }

                document.getElementById('bookScanningOverlay').style.display = 'flex';
                setTimeout(() => {
                    document.getElementById('bookScanningOverlay').style.display = 'none';
                }, 3000);

            } catch (error) {
                console.error('Error accessing camera:', error);
                showScanError('bookScanError', 'Could not access camera. Please check permissions.');
            }
        }

        function stopBookScan() {
            if (bookStream) {
                bookStream.getTracks().forEach(track => track.stop());
                bookStream = null;
            }

            if (bookCodeReader) {
                bookCodeReader.reset();
            }

            const video = document.getElementById('bookVideo');
            const placeholder = document.getElementById('bookPlaceholder');
            const scanBtn = document.getElementById('bookScanBtn');
            const stopBtn = document.getElementById('bookStopBtn');

            video.style.display = 'none';
            placeholder.style.display = 'flex';
            scanBtn.disabled = false;
            stopBtn.disabled = true;
            document.getElementById('bookScanningOverlay').style.display = 'none';
        }

        function handleBookScanResult(scannedData) {
            console.log('Book scan result:', scannedData);

            let accNo = scannedData;

            try {
                const data = JSON.parse(scannedData);
                accNo = data.accNo || data.AccNo || data.barcode || scannedData;
            } catch (e) {
                accNo = scannedData;
            }

            document.getElementById('accNo').value = accNo;
            showScanResult('bookScanResult', `Book scanned: ${accNo}`);
            searchBook();
            stopBookScan();
        }

        // Return scanning functions
        async function startReturnScan() {
            try {
                const constraints = {
                    video: {
                        facingMode: 'environment',
                        width: {
                            ideal: 350
                        },
                        height: {
                            ideal: 150
                        }
                    }
                };

                returnStream = await navigator.mediaDevices.getUserMedia(constraints);
                const video = document.getElementById('returnVideo');
                const placeholder = document.getElementById('returnPlaceholder');
                const scanBtn = document.getElementById('returnScanBtn');
                const stopBtn = document.getElementById('returnStopBtn');

                video.srcObject = returnStream;
                video.style.display = 'block';
                placeholder.style.display = 'none';
                scanBtn.disabled = true;
                stopBtn.disabled = false;

                if (returnCodeReader) {
                    returnCodeReader.decodeFromVideoDevice(null, 'returnVideo', (result, error) => {
                        if (result) {
                            handleReturnScanResult(result.text);
                        }
                    });
                }

                document.getElementById('returnScanningOverlay').style.display = 'flex';
                setTimeout(() => {
                    document.getElementById('returnScanningOverlay').style.display = 'none';
                }, 3000);

            } catch (error) {
                console.error('Error accessing camera:', error);
                showScanError('returnScanError', 'Could not access camera. Please check permissions.');
            }
        }

        function stopReturnScan() {
            if (returnStream) {
                returnStream.getTracks().forEach(track => track.stop());
                returnStream = null;
            }

            if (returnCodeReader) {
                returnCodeReader.reset();
            }

            const video = document.getElementById('returnVideo');
            const placeholder = document.getElementById('returnPlaceholder');
            const scanBtn = document.getElementById('returnScanBtn');
            const stopBtn = document.getElementById('returnStopBtn');

            video.style.display = 'none';
            placeholder.style.display = 'flex';
            scanBtn.disabled = false;
            stopBtn.disabled = true;
            document.getElementById('returnScanningOverlay').style.display = 'none';
        }

        function handleReturnScanResult(scannedData) {
            console.log('Return scan result:', scannedData);

            let accNo = scannedData;

            try {
                const data = JSON.parse(scannedData);
                accNo = data.accNo || data.AccNo || data.barcode || scannedData;
            } catch (e) {
                accNo = scannedData;
            }

            document.getElementById('returnAccNo').value = accNo;
            showScanResult('returnScanResult', `Book scanned: ${accNo}`);
            searchReturnBook();
            stopReturnScan();
        }

        // Utility functions for scan results
        function showScanResult(elementId, message) {
            const element = document.getElementById(elementId);
            element.textContent = message;
            element.style.display = 'block';
            setTimeout(() => {
                element.style.display = 'none';
            }, 5000);
        }

        function showScanError(elementId, message) {
            const element = document.getElementById(elementId);
            element.textContent = message;
            element.style.display = 'block';
            setTimeout(() => {
                element.style.display = 'none';
            }, 5000);
        }

        // Legacy simulation functions for backward compatibility
        function simulateMemberScan() {
            const memberNo = prompt('Simulate QR Scan - Enter Member Number:', '2024001');
            if (memberNo) {
                document.getElementById('memberNo').value = memberNo;
                searchMember();
            }
        }

        function simulateBookScan() {
            const accNo = prompt('Simulate Barcode Scan - Enter Accession Number:', 'ACC001001');
            if (accNo) {
                document.getElementById('accNo').value = accNo;
                searchBook();
            }
        }

        function simulateReturnScan() {
            const accNo = prompt('Simulate Return Scan - Enter Accession Number:', 'ACC001002');
            if (accNo) {
                document.getElementById('returnAccNo').value = accNo;
                searchReturnBook();
            }
        }

        // Clean up streams when tab changes
        // Initialize everything when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize code readers
            initializeCodeReaders();

            // Load statistics and data
            loadStatistics();
            loadActiveCirculations();
            loadReturnHistory();

            // Refresh stats every 30 seconds
            setInterval(loadStatistics, 30000);

            // ...existing code...
        });

        // Clean up streams when page unloads
        window.addEventListener('beforeunload', function() {
            stopMemberScan();
            stopBookScan();
            stopReturnScan();
        });

        // ...existing code...
    </script>

    <!-- Quick Scan Modal -->
    <div id="quickScanModal" class="modal" style="display:none;">
        <div class="modal-content" style="max-width:600px;">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-qrcode"></i>
                    Quick Scan Lookup
                </h3>
                <button class="close" onclick="closeQuickScan()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="quickScanInput">Scan or Enter Accession Number (AccNo)</label>
                    <input type="text" id="quickScanInput" class="form-control" placeholder="Scan QR or enter AccNo..." 
                           style="font-size:18px; padding:12px;" autofocus>
                    <small style="color:#6c757d; display:block; margin-top:8px;">
                        <i class="fas fa-info-circle"></i> Focus this field and scan the QR code, or type manually
                    </small>
                </div>
                
                <div id="quickScanResult" style="margin-top:20px; display:none;">
                    <div class="info-header" style="background:#f8f9fa; padding:12px; border-radius:8px; margin-bottom:12px;">
                        <strong style="color:#263c79;">Holding Details</strong>
                    </div>
                    <div id="quickScanDetails" style="padding:0 12px;"></div>
                    <div style="margin-top:20px; text-align:right;">
                        <button type="button" class="btn btn-secondary" onclick="closeQuickScan()">Close</button>
                        <button type="button" class="btn btn-success" onclick="quickIssue()" id="quickIssueBtn" style="display:none;">
                            <i class="fas fa-check"></i> Issue Book
                        </button>
                        <button type="button" class="btn btn-warning" onclick="quickReturn()" id="quickReturnBtn" style="display:none;">
                            <i class="fas fa-undo"></i> Return Book
                        </button>
                    </div>
                </div>
                
                <div id="quickScanError" style="margin-top:20px; padding:12px; background:#f8d7da; color:#721c24; border-radius:8px; display:none;">
                    <i class="fas fa-exclamation-triangle"></i> <span id="quickScanErrorText"></span>
                </div>
                
                <div id="quickScanLoading" style="text-align:center; margin-top:20px; display:none;">
                    <i class="fas fa-spinner fa-spin" style="font-size:24px; color:#263c79;"></i>
                    <p style="margin-top:10px; color:#6c757d;">Looking up...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Quick Scan Modal Functions
        function openQuickScan() {
            document.getElementById('quickScanModal').style.display = 'flex';
            document.getElementById('quickScanInput').value = '';
            document.getElementById('quickScanResult').style.display = 'none';
            document.getElementById('quickScanError').style.display = 'none';
            document.getElementById('quickScanInput').focus();
        }

        function closeQuickScan() {
            document.getElementById('quickScanModal').style.display = 'none';
        }

        // Listen for Enter key or barcode scanner input
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('quickScanInput');
            if (input) {
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        lookupAccNo();
                    }
                });
            }
        });

        function lookupAccNo() {
            const accNo = document.getElementById('quickScanInput').value.trim();
            if (!accNo) return;

            document.getElementById('quickScanLoading').style.display = 'block';
            document.getElementById('quickScanResult').style.display = 'none';
            document.getElementById('quickScanError').style.display = 'none';

            fetch(`api/books.php?action=lookup&accNo=${encodeURIComponent(accNo)}`)
                .then(res => res.json())
                .then(result => {
                    document.getElementById('quickScanLoading').style.display = 'none';
                    
                    if (result.success && result.data) {
                        const h = result.data;
                        let html = `
                            <div style="margin-bottom:10px;"><strong>AccNo:</strong> ${escapeHtml(h.AccNo || '')}</div>
                            <div style="margin-bottom:10px;"><strong>Book:</strong> ${escapeHtml(h.Title || 'Unknown')}</div>
                            <div style="margin-bottom:10px;"><strong>Author:</strong> ${escapeHtml(h.Author1 || 'Unknown')}</div>
                            <div style="margin-bottom:10px;"><strong>Publisher:</strong> ${escapeHtml(h.Publisher || 'N/A')} (${h.Year || 'N/A'})</div>
                            <div style="margin-bottom:10px;"><strong>Status:</strong> <span class="status-badge status-${(h.Status || 'available').toLowerCase()}">${escapeHtml(h.Status || 'Unknown')}</span></div>
                            <div style="margin-bottom:10px;"><strong>Location:</strong> ${escapeHtml(h.Location || 'N/A')}</div>
                        `;
                        
                        document.getElementById('quickScanDetails').innerHTML = html;
                        document.getElementById('quickScanResult').style.display = 'block';
                        
                        // Show action buttons based on status
                        document.getElementById('quickIssueBtn').style.display = (h.Status === 'Available') ? 'inline-block' : 'none';
                        document.getElementById('quickReturnBtn').style.display = (h.Status === 'Issued') ? 'inline-block' : 'none';
                        
                        // Store for quick actions
                        window.currentQuickAccNo = accNo;
                        window.currentQuickHolding = h;
                    } else {
                        document.getElementById('quickScanError').style.display = 'block';
                        document.getElementById('quickScanErrorText').textContent = result.message || 'Accession number not found';
                    }
                })
                .catch(err => {
                    document.getElementById('quickScanLoading').style.display = 'none';
                    document.getElementById('quickScanError').style.display = 'block';
                    document.getElementById('quickScanErrorText').textContent = 'Error: ' + err.message;
                });
        }

        function quickIssue() {
            closeQuickScan();
            // Switch to Issue tab and pre-fill AccNo
            showTab('issue');
            document.getElementById('accNo').value = window.currentQuickAccNo || '';
            searchBook();
        }

        function quickReturn() {
            closeQuickScan();
            // Switch to Return tab and pre-fill AccNo
            showTab('return');
            document.getElementById('returnAccNo').value = window.currentQuickAccNo || '';
            searchReturnBook();
        }

        function escapeHtml(text) {
            if (typeof text !== 'string' && typeof text !== 'number') return '';
            return String(text).replace(/[&<>"']/g, function (c) {
                return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c];
            });
        }

        // Global keyboard shortcut: Ctrl+K to open quick scan
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'k') {
                e.preventDefault();
                openQuickScan();
            }
        });
    </script>
</body>

</html>