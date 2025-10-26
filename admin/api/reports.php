<?php
/**
 * Reports API
 * Comprehensive reporting system for library management
 */

require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

session_start();
$adminId = $_SESSION['admin_id'] ?? $_SESSION['AdminID'] ?? 1;

$action = $_GET['action'] ?? '';
$export = $_GET['export'] ?? '';

function sendHTML($html) {
    header('Content-Type: text/html; charset=UTF-8');
    echo $html;
    exit;
}

function sendCSV($filename, $data) {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    if (!empty($data)) {
        // Write headers
        fputcsv($output, array_keys($data[0]));
        
        // Write data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
    exit;
}

function generatePDFHTML($title, $content) {
    return "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>$title</title>
    <style>
        @page { margin: 20mm; }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #263c79;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #263c79;
            margin: 0;
            font-size: 24px;
        }
        .header .subtitle {
            color: #666;
            margin: 5px 0 0 0;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        .stat-box {
            border: 2px solid #cfac69;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
        }
        .stat-box .value {
            font-size: 28px;
            font-weight: bold;
            color: #263c79;
        }
        .stat-box .label {
            color: #666;
            margin-top: 5px;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background: #263c79;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        td {
            border: 1px solid #ddd;
            padding: 10px;
        }
        tr:nth-child(even) {
            background: #f8f9fa;
        }
        .total-row {
            background: #cfac69 !important;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #999;
            font-size: 11px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .section-title {
            color: #263c79;
            border-bottom: 2px solid #cfac69;
            padding-bottom: 8px;
            margin: 25px 0 15px 0;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class='header'>
        <h1>📚 WIET Library Management System</h1>
        <div class='subtitle'>$title</div>
        <div class='subtitle'>Generated on " . date('d-m-Y H:i:s') . "</div>
    </div>
    $content
    <div class='footer'>
        <p>This is a computer-generated report from WIET Library Management System</p>
        <p>© " . date('Y') . " WIET Library. All rights reserved.</p>
    </div>
</body>
</html>";
}

switch ($action) {
    // ===========================
    // CIRCULATION REPORTS
    // ===========================
    case 'circulation':
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');
        
        try {
            // Get statistics
            $stats = [];
            
            // Total Issued
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM Circulation WHERE DATE(IssueDate) BETWEEN ? AND ?");
            $stmt->execute([$from, $to]);
            $stats['totalIssued'] = $stmt->fetchColumn();
            
            // Total Returned
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM `Return` WHERE DATE(ReturnDate) BETWEEN ? AND ?");
            $stmt->execute([$from, $to]);
            $stats['totalReturned'] = $stmt->fetchColumn();
            
            // Overdue Books (not returned and past due date)
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM Circulation c WHERE NOT EXISTS (SELECT 1 FROM `Return` r WHERE r.CirculationID = c.CirculationID) AND c.DueDate < CURDATE()");
            $stats['overdue'] = $stmt->fetchColumn();
            
            // Active Members (who issued books in period)
            $stmt = $pdo->prepare("SELECT COUNT(DISTINCT MemberNo) as total FROM Circulation WHERE DATE(IssueDate) BETWEEN ? AND ?");
            $stmt->execute([$from, $to]);
            $stats['activeMembers'] = $stmt->fetchColumn();
            
            // Charts Data
            $charts = [];
            
            // Daily Trend
            $stmt = $pdo->prepare("
                SELECT 
                    DATE(IssueDate) as date,
                    COUNT(*) as issues
                FROM Circulation 
                WHERE DATE(IssueDate) BETWEEN ? AND ?
                GROUP BY DATE(IssueDate)
                ORDER BY date
            ");
            $stmt->execute([$from, $to]);
            $issueTrend = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt = $pdo->prepare("
                SELECT 
                    DATE(ReturnDate) as date,
                    COUNT(*) as returns
                FROM `Return`
                WHERE DATE(ReturnDate) BETWEEN ? AND ?
                GROUP BY DATE(ReturnDate)
                ORDER BY date
            ");
            $stmt->execute([$from, $to]);
            $returnTrend = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Merge trends
            $dates = [];
            foreach ($issueTrend as $row) {
                $dates[$row['date']] = ['issues' => $row['issues'], 'returns' => 0];
            }
            foreach ($returnTrend as $row) {
                if (!isset($dates[$row['date']])) {
                    $dates[$row['date']] = ['issues' => 0, 'returns' => $row['returns']];
                } else {
                    $dates[$row['date']]['returns'] = $row['returns'];
                }
            }
            ksort($dates);
            
            $charts['trend'] = [
                'labels' => array_keys($dates),
                'issues' => array_column($dates, 'issues'),
                'returns' => array_column($dates, 'returns')
            ];
            
            // Top Borrowed Books
            $stmt = $pdo->prepare("
                SELECT 
                    b.Title,
                    COUNT(*) as BorrowCount
                FROM Circulation c
                INNER JOIN Holding h ON c.AccNo = h.AccNo
                INNER JOIN Books b ON h.CatNo = b.CatNo
                WHERE DATE(c.IssueDate) BETWEEN ? AND ?
                GROUP BY b.CatNo, b.Title
                ORDER BY BorrowCount DESC
                LIMIT 10
            ");
            $stmt->execute([$from, $to]);
            $topBooks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $charts['topBooks'] = [
                'labels' => array_column($topBooks, 'Title'),
                'values' => array_column($topBooks, 'BorrowCount')
            ];
            
            // Detailed Records
            $stmt = $pdo->prepare("
                SELECT 
                    c.CirculationID,
                    c.MemberNo,
                    c.AccNo,
                    c.IssueDate,
                    c.DueDate,
                    r.ReturnDate,
                    m.MemberName,
                    b.Title
                FROM Circulation c
                INNER JOIN Member m ON c.MemberNo = m.MemberNo
                LEFT JOIN `Return` r ON c.CirculationID = r.CirculationID
                LEFT JOIN Holding h ON c.AccNo = h.AccNo
                LEFT JOIN Books b ON h.CatNo = b.CatNo
                WHERE DATE(c.IssueDate) BETWEEN ? AND ?
                ORDER BY c.IssueDate DESC
                LIMIT 100
            ");
            $stmt->execute([$from, $to]);
            $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Handle Export
            if ($export === 'pdf') {
                $content = "<div class='stats-grid'>";
                $content .= "<div class='stat-box'><div class='value'>{$stats['totalIssued']}</div><div class='label'>Total Issued</div></div>";
                $content .= "<div class='stat-box'><div class='value'>{$stats['totalReturned']}</div><div class='label'>Total Returned</div></div>";
                $content .= "<div class='stat-box'><div class='value'>{$stats['overdue']}</div><div class='label'>Overdue Books</div></div>";
                $content .= "<div class='stat-box'><div class='value'>{$stats['activeMembers']}</div><div class='label'>Active Members</div></div>";
                $content .= "</div>";
                
                $content .= "<h2 class='section-title'>Circulation Details</h2>";
                $content .= "<table><thead><tr><th>Member</th><th>Book</th><th>Issue Date</th><th>Due Date</th><th>Return Date</th><th>Status</th></tr></thead><tbody>";
                
                foreach ($details as $row) {
                    $status = $row['ReturnDate'] ? 'Returned' : (strtotime($row['DueDate']) < time() ? 'Overdue' : 'Issued');
                    $content .= "<tr>";
                    $content .= "<td>{$row['MemberName']} ({$row['MemberNo']})</td>";
                    $content .= "<td>{$row['Title']}</td>";
                    $content .= "<td>" . date('d-m-Y', strtotime($row['IssueDate'])) . "</td>";
                    $content .= "<td>" . date('d-m-Y', strtotime($row['DueDate'])) . "</td>";
                    $content .= "<td>" . ($row['ReturnDate'] ? date('d-m-Y', strtotime($row['ReturnDate'])) : '-') . "</td>";
                    $content .= "<td>$status</td>";
                    $content .= "</tr>";
                }
                
                $content .= "</tbody></table>";
                
                $html = generatePDFHTML("Circulation Report ($from to $to)", $content);
                sendHTML($html);
            }
            
            if ($export === 'excel') {
                $csvData = [];
                foreach ($details as $row) {
                    $status = $row['ReturnDate'] ? 'Returned' : (strtotime($row['DueDate']) < time() ? 'Overdue' : 'Issued');
                    $csvData[] = [
                        'Member No' => $row['MemberNo'],
                        'Member Name' => $row['MemberName'],
                        'Book Title' => $row['Title'],
                        'Acc No' => $row['AccNo'],
                        'Issue Date' => $row['IssueDate'],
                        'Due Date' => $row['DueDate'],
                        'Return Date' => $row['ReturnDate'] ?? '-',
                        'Status' => $status
                    ];
                }
                sendCSV("circulation_report_" . date('Y-m-d') . ".csv", $csvData);
            }
            
            sendJson([
                'success' => true,
                'stats' => $stats,
                'charts' => $charts,
                'details' => $details
            ]);
            
        } catch (PDOException $e) {
            sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
        break;

    // ===========================
    // FINANCIAL REPORTS
    // ===========================
    case 'financial':
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');
        
        try {
            // Ensure FinePayments table exists
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS FinePayments (
                    PaymentID INT PRIMARY KEY AUTO_INCREMENT,
                    CirculationID INT NOT NULL,
                    MemberNo INT NOT NULL,
                    FineAmount DECIMAL(10,2) NOT NULL,
                    PaidAmount DECIMAL(10,2) NOT NULL,
                    PaymentDate DATETIME DEFAULT CURRENT_TIMESTAMP,
                    PaymentMethod VARCHAR(50),
                    ReceiptNo VARCHAR(50) UNIQUE,
                    CollectedBy INT,
                    Remarks TEXT,
                    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (CirculationID) REFERENCES Circulation(CirculationID),
                    FOREIGN KEY (MemberNo) REFERENCES Member(MemberNo),
                    FOREIGN KEY (CollectedBy) REFERENCES Admin(AdminID)
                )
            ");
            
            // Get statistics
            $stats = [];
            
            // Total Collected
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(PaidAmount), 0) as total FROM FinePayments WHERE DATE(PaymentDate) BETWEEN ? AND ?");
            $stmt->execute([$from, $to]);
            $stats['totalCollected'] = number_format($stmt->fetchColumn(), 2);
            
            // Pending Fines (from Return table: FineAmount - FinePaid)
            $stmt = $pdo->query("
                SELECT COALESCE(SUM(r.FineAmount - r.FinePaid), 0) as total
                FROM `Return` r
                WHERE r.FineAmount > r.FinePaid
            ");
            $stats['pendingFines'] = number_format($stmt->fetchColumn(), 2);
            
            // Total Waived
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(FineAmount), 0) as total FROM FinePayments WHERE PaymentMethod = 'Waived' AND DATE(PaymentDate) BETWEEN ? AND ?");
            $stmt->execute([$from, $to]);
            $stats['totalWaived'] = number_format($stmt->fetchColumn(), 2);
            
            // Transactions Count
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM FinePayments WHERE DATE(PaymentDate) BETWEEN ? AND ?");
            $stmt->execute([$from, $to]);
            $stats['transactions'] = $stmt->fetchColumn();
            
            // Charts Data
            $charts = [];
            
            // Daily Collection Trend
            $stmt = $pdo->prepare("
                SELECT 
                    DATE(PaymentDate) as date,
                    SUM(PaidAmount) as amount
                FROM FinePayments
                WHERE DATE(PaymentDate) BETWEEN ? AND ?
                GROUP BY DATE(PaymentDate)
                ORDER BY date
            ");
            $stmt->execute([$from, $to]);
            $trend = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $charts['trend'] = [
                'labels' => array_column($trend, 'date'),
                'values' => array_column($trend, 'amount')
            ];
            
            // Payment Methods Distribution
            $stmt = $pdo->prepare("
                SELECT 
                    PaymentMethod,
                    COUNT(*) as count
                FROM FinePayments
                WHERE DATE(PaymentDate) BETWEEN ? AND ?
                GROUP BY PaymentMethod
            ");
            $stmt->execute([$from, $to]);
            $methods = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $charts['methods'] = [
                'labels' => array_column($methods, 'PaymentMethod'),
                'values' => array_column($methods, 'count')
            ];
            
            // Detailed Records
            $stmt = $pdo->prepare("
                SELECT 
                    fp.*,
                    m.MemberName,
                    c.MemberNo
                FROM FinePayments fp
                INNER JOIN Circulation c ON fp.CirculationID = c.CirculationID
                INNER JOIN Member m ON c.MemberNo = m.MemberNo
                WHERE DATE(fp.PaymentDate) BETWEEN ? AND ?
                ORDER BY fp.PaymentDate DESC
                LIMIT 100
            ");
            $stmt->execute([$from, $to]);
            $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Handle Export
            if ($export === 'pdf') {
                $content = "<div class='stats-grid'>";
                $content .= "<div class='stat-box'><div class='value'>₹{$stats['totalCollected']}</div><div class='label'>Total Collected</div></div>";
                $content .= "<div class='stat-box'><div class='value'>₹{$stats['pendingFines']}</div><div class='label'>Pending Fines</div></div>";
                $content .= "<div class='stat-box'><div class='value'>₹{$stats['totalWaived']}</div><div class='label'>Total Waived</div></div>";
                $content .= "<div class='stat-box'><div class='value'>{$stats['transactions']}</div><div class='label'>Transactions</div></div>";
                $content .= "</div>";
                
                $content .= "<h2 class='section-title'>Payment Details</h2>";
                $content .= "<table><thead><tr><th>Receipt No</th><th>Date</th><th>Member</th><th>Fine</th><th>Paid</th><th>Method</th></tr></thead><tbody>";
                
                $totalFine = 0;
                $totalPaid = 0;
                foreach ($details as $row) {
                    $totalFine += $row['FineAmount'];
                    $totalPaid += $row['PaidAmount'];
                    $content .= "<tr>";
                    $content .= "<td>{$row['ReceiptNo']}</td>";
                    $content .= "<td>" . date('d-m-Y', strtotime($row['PaymentDate'])) . "</td>";
                    $content .= "<td>{$row['MemberName']} ({$row['MemberNo']})</td>";
                    $content .= "<td>₹" . number_format($row['FineAmount'], 2) . "</td>";
                    $content .= "<td>₹" . number_format($row['PaidAmount'], 2) . "</td>";
                    $content .= "<td>{$row['PaymentMethod']}</td>";
                    $content .= "</tr>";
                }
                
                $content .= "<tr class='total-row'>";
                $content .= "<td colspan='3'><strong>TOTAL</strong></td>";
                $content .= "<td><strong>₹" . number_format($totalFine, 2) . "</strong></td>";
                $content .= "<td><strong>₹" . number_format($totalPaid, 2) . "</strong></td>";
                $content .= "<td></td>";
                $content .= "</tr>";
                $content .= "</tbody></table>";
                
                $html = generatePDFHTML("Financial Report ($from to $to)", $content);
                sendHTML($html);
            }
            
            if ($export === 'excel') {
                $csvData = [];
                foreach ($details as $row) {
                    $csvData[] = [
                        'Receipt No' => $row['ReceiptNo'],
                        'Date' => $row['PaymentDate'],
                        'Member No' => $row['MemberNo'],
                        'Member Name' => $row['MemberName'],
                        'Fine Amount' => $row['FineAmount'],
                        'Paid Amount' => $row['PaidAmount'],
                        'Payment Method' => $row['PaymentMethod'],
                        'Remarks' => $row['Remarks']
                    ];
                }
                sendCSV("financial_report_" . date('Y-m-d') . ".csv", $csvData);
            }
            
            sendJson([
                'success' => true,
                'stats' => $stats,
                'charts' => $charts,
                'details' => $details
            ]);
            
        } catch (PDOException $e) {
            sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
        break;

    // ===========================
    // INVENTORY REPORTS
    // ===========================
    case 'inventory':
        $type = $_GET['type'] ?? 'summary';
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');
        
        try {
            // Get statistics
            $stats = [];
            
            // Total Books (Holdings)
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM Holding");
            $stats['totalBooks'] = $stmt->fetchColumn();
            
            // Available (books not currently issued)
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM Holding WHERE AccNo NOT IN (SELECT c.AccNo FROM Circulation c WHERE NOT EXISTS (SELECT 1 FROM `Return` r WHERE r.CirculationID = c.CirculationID))");
            $stats['available'] = $stmt->fetchColumn();
            
            // Currently Issued (books not yet returned)
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM Circulation c WHERE NOT EXISTS (SELECT 1 FROM `Return` r WHERE r.CirculationID = c.CirculationID)");
            $stats['issued'] = $stmt->fetchColumn();
            
            // Damaged/Lost (if Condition column exists)
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM Holding WHERE `Condition` IN ('Damaged', 'Lost')");
            $stats['damaged'] = $stmt->fetchColumn();
            
            // Charts Data
            $charts = [];
            
            // Book Condition Distribution
            $stmt = $pdo->query("
                SELECT 
                    COALESCE(Condition, 'Good') as Condition,
                    COUNT(*) as count
                FROM Holding
                GROUP BY Condition
            ");
            $conditions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $charts['condition'] = [
                'labels' => array_column($conditions, 'Condition'),
                'values' => array_column($conditions, 'count')
            ];
            
            // Category-wise Stock
            $stmt = $pdo->query("
                SELECT 
                    b.Category,
                    COUNT(h.AccNo) as count
                FROM Books b
                LEFT JOIN Holding h ON b.CatNo = h.CatNo
                GROUP BY b.Category
                ORDER BY count DESC
                LIMIT 10
            ");
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $charts['category'] = [
                'labels' => array_column($categories, 'Category'),
                'values' => array_column($categories, 'count')
            ];
            
            // Detailed Records based on type
            $details = [];
            
            switch ($type) {
                case 'summary':
                    $stmt = $pdo->query("
                        SELECT 
                            b.CatNo,
                            b.Title,
                            b.Author1 as Author,
                            b.Category,
                            COUNT(h.AccNo) as TotalCopies,
                            COUNT(CASE WHEN h.AccNo NOT IN (SELECT c.AccNo FROM Circulation c WHERE NOT EXISTS (SELECT 1 FROM `Return` r WHERE r.CirculationID = c.CirculationID)) THEN 1 END) as Available,
                            COALESCE(h.`Condition`, 'Good') as Status
                        FROM Books b
                        LEFT JOIN Holding h ON b.CatNo = h.CatNo
                        GROUP BY b.CatNo
                        ORDER BY b.Title
                        LIMIT 100
                    ");
                    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;
                    
                case 'acquisitions':
                    $stmt = $pdo->prepare("
                        SELECT 
                            b.Title,
                            b.Author1 as Author,
                            b.Publisher,
                            b.Category,
                            h.AccNo,
                            h.Price,
                            h.PurchaseDate
                        FROM Holding h
                        INNER JOIN Books b ON h.CatNo = b.CatNo
                        WHERE DATE(h.PurchaseDate) BETWEEN ? AND ?
                        ORDER BY h.PurchaseDate DESC
                    ");
                    $stmt->execute([$from, $to]);
                    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;
                    
                case 'condition':
                    $stmt = $pdo->query("
                        SELECT 
                            b.Title,
                            b.Author1 as Author,
                            h.AccNo,
                            COALESCE(h.`Condition`, 'Good') as `Condition`,
                            h.Remarks
                        FROM Holding h
                        INNER JOIN Books b ON h.CatNo = b.CatNo
                        WHERE h.`Condition` IN ('Damaged', 'Lost', 'Fair')
                        ORDER BY h.`Condition`, b.Title
                    ");
                    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;
                    
                case 'low_stock':
                    $stmt = $pdo->query("
                        SELECT 
                            b.Title,
                            b.Author1 as Author,
                            b.Category,
                            COUNT(h.AccNo) as TotalCopies,
                            COUNT(CASE WHEN h.AccNo NOT IN (SELECT c.AccNo FROM Circulation c WHERE NOT EXISTS (SELECT 1 FROM `Return` r WHERE r.CirculationID = c.CirculationID)) THEN 1 END) as Available
                        FROM Books b
                        LEFT JOIN Holding h ON b.CatNo = h.CatNo
                        GROUP BY b.CatNo
                        HAVING TotalCopies <= 3 OR Available = 0
                        ORDER BY TotalCopies
                    ");
                    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;
            }
            
            // Handle Export
            if ($export === 'pdf') {
                $content = "<div class='stats-grid'>";
                $content .= "<div class='stat-box'><div class='value'>{$stats['totalBooks']}</div><div class='label'>Total Books</div></div>";
                $content .= "<div class='stat-box'><div class='value'>{$stats['available']}</div><div class='label'>Available</div></div>";
                $content .= "<div class='stat-box'><div class='value'>{$stats['issued']}</div><div class='label'>Currently Issued</div></div>";
                $content .= "<div class='stat-box'><div class='value'>{$stats['damaged']}</div><div class='label'>Damaged/Lost</div></div>";
                $content .= "</div>";
                
                $content .= "<h2 class='section-title'>Inventory Details - " . ucfirst($type) . "</h2>";
                $content .= "<table><thead><tr>";
                
                if (!empty($details)) {
                    foreach (array_keys($details[0]) as $header) {
                        $content .= "<th>$header</th>";
                    }
                    $content .= "</tr></thead><tbody>";
                    
                    foreach ($details as $row) {
                        $content .= "<tr>";
                        foreach ($row as $value) {
                            $content .= "<td>$value</td>";
                        }
                        $content .= "</tr>";
                    }
                } else {
                    $content .= "<th>No Data</th></tr></thead><tbody><tr><td>No records found</td></tr>";
                }
                
                $content .= "</tbody></table>";
                
                $html = generatePDFHTML("Inventory Report - " . ucfirst($type), $content);
                sendHTML($html);
            }
            
            if ($export === 'excel') {
                sendCSV("inventory_report_" . $type . "_" . date('Y-m-d') . ".csv", $details);
            }
            
            sendJson([
                'success' => true,
                'stats' => $stats,
                'charts' => $charts,
                'details' => $details
            ]);
            
        } catch (PDOException $e) {
            sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
        break;

    // ===========================
    // MEMBER REPORTS
    // ===========================
    case 'members':
        $type = $_GET['type'] ?? 'summary';
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');
        
        try {
            // Get statistics
            $stats = [];
            
            // Total Members
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM Member");
            $stats['totalMembers'] = $stmt->fetchColumn();
            
            // Active Members (issued books in last 30 days)
            $stmt = $pdo->query("SELECT COUNT(DISTINCT MemberNo) as total FROM Circulation WHERE IssueDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
            $stats['activeMembers'] = $stmt->fetchColumn();
            
            // Inactive Members
            $stats['inactiveMembers'] = $stats['totalMembers'] - $stats['activeMembers'];
            
            // New Registrations This Month
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM Member WHERE MONTH(AdmissionDate) = MONTH(CURDATE()) AND YEAR(AdmissionDate) = YEAR(CURDATE())");
            $stats['newRegistrations'] = $stmt->fetchColumn();
            
            // Charts Data
            $charts = [];
            
            // Group-wise Distribution
            $stmt = $pdo->query("
                SELECT 
                    COALESCE(`Group`, 'Not Specified') as `Group`,
                    COUNT(*) as count
                FROM Member
                GROUP BY `Group`
                ORDER BY count DESC
            ");
            $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $charts['department'] = [
                'labels' => array_column($groups, 'Group'),
                'values' => array_column($groups, 'count')
            ];
            
            // Registration Trend (Last 6 months)
            $stmt = $pdo->query("
                SELECT 
                    DATE_FORMAT(AdmissionDate, '%Y-%m') as month,
                    COUNT(*) as count
                FROM Member
                WHERE AdmissionDate >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                GROUP BY month
                ORDER BY month
            ");
            $trend = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $charts['trend'] = [
                'labels' => array_column($trend, 'month'),
                'values' => array_column($trend, 'count')
            ];
            
            // Detailed Records based on type
            $details = [];
            
            switch ($type) {
                case 'summary':
                    $stmt = $pdo->query("
                        SELECT 
                            m.MemberNo,
                            m.MemberName,
                            m.`Group`,
                            m.Phone,
                            m.AdmissionDate,
                            CASE 
                                WHEN EXISTS(SELECT 1 FROM Circulation c WHERE c.MemberNo = m.MemberNo AND c.IssueDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY))
                                THEN 'Active'
                                ELSE 'Inactive'
                            END as Status
                        FROM Member m
                        ORDER BY m.AdmissionDate DESC
                        LIMIT 100
                    ");
                    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;
                    
                case 'department':
                    $stmt = $pdo->query("
                        SELECT 
                            m.`Group`,
                            COUNT(*) as TotalMembers,
                            COUNT(CASE WHEN EXISTS(SELECT 1 FROM Circulation c WHERE c.MemberNo = m.MemberNo AND c.IssueDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) THEN 1 END) as ActiveMembers
                        FROM Member m
                        GROUP BY m.`Group`
                        ORDER BY TotalMembers DESC
                    ");
                    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;
                    
                case 'activity':
                    $stmt = $pdo->query("
                        SELECT 
                            m.MemberNo,
                            m.MemberName,
                            m.`Group`,
                            COUNT(c.CirculationID) as BooksIssued,
                            MAX(c.IssueDate) as LastIssue
                        FROM Member m
                        LEFT JOIN Circulation c ON m.MemberNo = c.MemberNo
                        GROUP BY m.MemberNo
                        ORDER BY BooksIssued DESC
                        LIMIT 100
                    ");
                    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;
                    
                case 'registrations':
                    $stmt = $pdo->prepare("
                        SELECT 
                            m.MemberNo,
                            m.MemberName,
                            m.`Group`,
                            m.Email,
                            m.Phone,
                            m.AdmissionDate
                        FROM Member m
                        WHERE DATE(m.AdmissionDate) BETWEEN ? AND ?
                        ORDER BY m.AdmissionDate DESC
                    ");
                    $stmt->execute([$from, $to]);
                    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;
            }
            
            // Handle Export
            if ($export === 'pdf') {
                $content = "<div class='stats-grid'>";
                $content .= "<div class='stat-box'><div class='value'>{$stats['totalMembers']}</div><div class='label'>Total Members</div></div>";
                $content .= "<div class='stat-box'><div class='value'>{$stats['activeMembers']}</div><div class='label'>Active Members</div></div>";
                $content .= "<div class='stat-box'><div class='value'>{$stats['inactiveMembers']}</div><div class='label'>Inactive Members</div></div>";
                $content .= "<div class='stat-box'><div class='value'>{$stats['newRegistrations']}</div><div class='label'>New This Month</div></div>";
                $content .= "</div>";
                
                $content .= "<h2 class='section-title'>Member Details - " . ucfirst($type) . "</h2>";
                $content .= "<table><thead><tr>";
                
                if (!empty($details)) {
                    foreach (array_keys($details[0]) as $header) {
                        $content .= "<th>$header</th>";
                    }
                    $content .= "</tr></thead><tbody>";
                    
                    foreach ($details as $row) {
                        $content .= "<tr>";
                        foreach ($row as $value) {
                            $content .= "<td>" . ($value ?? '-') . "</td>";
                        }
                        $content .= "</tr>";
                    }
                } else {
                    $content .= "<th>No Data</th></tr></thead><tbody><tr><td>No records found</td></tr>";
                }
                
                $content .= "</tbody></table>";
                
                $html = generatePDFHTML("Member Report - " . ucfirst($type), $content);
                sendHTML($html);
            }
            
            if ($export === 'excel') {
                sendCSV("member_report_" . $type . "_" . date('Y-m-d') . ".csv", $details);
            }
            
            sendJson([
                'success' => true,
                'stats' => $stats,
                'charts' => $charts,
                'details' => $details
            ]);
            
        } catch (PDOException $e) {
            sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
        break;

    default:
        sendJson(['success' => false, 'message' => 'Invalid action'], 400);
}
?>
