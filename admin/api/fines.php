<?php
/**
 * Fine Management API
 * Handles fine collection, waivers, and reporting
 */

require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

// Ensure FinePayments table exists
try {
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
} catch (PDOException $e) {
    error_log("FinePayments table creation: " . $e->getMessage());
}

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

session_start();
$adminId = $_SESSION['admin_id'] ?? $_SESSION['AdminID'] ?? 1;

switch ($action) {
    case 'pending':
        // Get pending fines
        $search = $_GET['search'] ?? '';
        
        try {
            $sql = "
                SELECT 
                    c.CirculationID,
                    c.MemberNo,
                    c.AccNo,
                    c.IssueDate,
                    c.DueDate,
                    r.ReturnDate,
                    r.FineAmount as Fine,
                    DATEDIFF(r.ReturnDate, c.DueDate) as DaysOverdue,
                    m.MemberName,
                    b.Title,
                    COALESCE(SUM(fp.PaidAmount), 0) as PaidAmount
                FROM Circulation c
                INNER JOIN `Return` r ON c.CirculationID = r.CirculationID
                INNER JOIN Member m ON c.MemberNo = m.MemberNo
                LEFT JOIN Holding h ON c.AccNo = h.AccNo
                LEFT JOIN Books b ON h.CatNo = b.CatNo
                LEFT JOIN FinePayments fp ON c.CirculationID = fp.CirculationID
                WHERE r.FineAmount > 0
            ";
            
            if ($search) {
                $sql .= " AND (c.MemberNo LIKE ? OR m.MemberName LIKE ? OR c.AccNo LIKE ? OR b.Title LIKE ?)";
            }
            
            $sql .= " GROUP BY c.CirculationID
                     HAVING r.FineAmount > COALESCE(SUM(fp.PaidAmount), 0)
                     ORDER BY r.ReturnDate DESC";
            
            $stmt = $pdo->prepare($sql);
            
            if ($search) {
                $searchTerm = "%$search%";
                $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            } else {
                $stmt->execute();
            }
            
            $fines = $stmt->fetchAll(PDO::FETCH_ASSOC);
            sendJson(['success' => true, 'data' => $fines]);
            
        } catch (PDOException $e) {
            sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
        break;

    case 'collect':
        // Collect fine payment
        if ($method !== 'POST') {
            sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $circulationId = $data['circulationId'] ?? 0;
        $fineAmount = $data['fineAmount'] ?? 0;
        $paidAmount = $data['paidAmount'] ?? 0;
        $paymentMethod = $data['paymentMethod'] ?? 'Cash';
        $remarks = $data['remarks'] ?? '';
        
        if (!$circulationId || $paidAmount <= 0) {
            sendJson(['success' => false, 'message' => 'Invalid data'], 400);
        }
        
        try {
            $pdo->beginTransaction();
            
            // Get circulation details
            $stmt = $pdo->prepare("SELECT MemberNo FROM Circulation WHERE CirculationID = ?");
            $stmt->execute([$circulationId]);
            $circulation = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$circulation) {
                throw new Exception('Circulation record not found');
            }
            
            // Generate receipt number
            $receiptNo = 'RCP' . date('Ymd') . str_pad($circulationId, 6, '0', STR_PAD_LEFT);
            
            // Insert payment record
            $stmt = $pdo->prepare("
                INSERT INTO FinePayments 
                (CirculationID, MemberNo, FineAmount, PaidAmount, PaymentMethod, ReceiptNo, CollectedBy, Remarks)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $circulationId,
                $circulation['MemberNo'],
                $fineAmount,
                $paidAmount,
                $paymentMethod,
                $receiptNo,
                $adminId,
                $remarks
            ]);
            
            // Log activity
            logActivity($pdo, $adminId, 'FINE_COLLECTED', "Collected fine: ₹$paidAmount for CirculationID: $circulationId");
            
            $pdo->commit();
            sendJson(['success' => true, 'message' => 'Payment collected successfully', 'receiptNo' => $receiptNo]);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
        break;

    case 'waive':
        // Waive fine
        if ($method !== 'POST') {
            sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $circulationId = $data['circulationId'] ?? 0;
        $fineAmount = $data['fineAmount'] ?? 0;
        $remarks = $data['remarks'] ?? 'Fine waived';
        
        if (!$circulationId) {
            sendJson(['success' => false, 'message' => 'Invalid data'], 400);
        }
        
        try {
            $pdo->beginTransaction();
            
            // Get circulation details
            $stmt = $pdo->prepare("SELECT MemberNo FROM Circulation WHERE CirculationID = ?");
            $stmt->execute([$circulationId]);
            $circulation = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$circulation) {
                throw new Exception('Circulation record not found');
            }
            
            // Generate receipt number
            $receiptNo = 'WAV' . date('Ymd') . str_pad($circulationId, 6, '0', STR_PAD_LEFT);
            
            // Insert waiver record (paid amount = 0, fine amount = original fine)
            $stmt = $pdo->prepare("
                INSERT INTO FinePayments 
                (CirculationID, MemberNo, FineAmount, PaidAmount, PaymentMethod, ReceiptNo, CollectedBy, Remarks)
                VALUES (?, ?, ?, 0, 'Waived', ?, ?, ?)
            ");
            $stmt->execute([
                $circulationId,
                $circulation['MemberNo'],
                $fineAmount,
                $receiptNo,
                $adminId,
                $remarks
            ]);
            
            // Log activity
            logActivity($pdo, $adminId, 'FINE_WAIVED', "Waived fine: ₹$fineAmount for CirculationID: $circulationId");
            
            $pdo->commit();
            sendJson(['success' => true, 'message' => 'Fine waived successfully']);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
        break;

    case 'history':
        // Get payment history
        $search = $_GET['search'] ?? '';
        $from = $_GET['from'] ?? '';
        $to = $_GET['to'] ?? '';
        
        try {
            $sql = "
                SELECT 
                    fp.*,
                    m.MemberName,
                    c.MemberNo,
                    a.Name as CollectorName
                FROM FinePayments fp
                INNER JOIN Circulation c ON fp.CirculationID = c.CirculationID
                INNER JOIN Member m ON fp.MemberNo = m.MemberNo
                LEFT JOIN Admin a ON fp.CollectedBy = a.AdminID
                WHERE 1=1
            ";
            
            $params = [];
            
            if ($search) {
                $sql .= " AND (fp.ReceiptNo LIKE ? OR m.MemberName LIKE ? OR c.MemberNo LIKE ?)";
                $searchTerm = "%$search%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            if ($from) {
                $sql .= " AND DATE(fp.PaymentDate) >= ?";
                $params[] = $from;
            }
            
            if ($to) {
                $sql .= " AND DATE(fp.PaymentDate) <= ?";
                $params[] = $to;
            }
            
            $sql .= " ORDER BY fp.PaymentDate DESC LIMIT 100";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            sendJson(['success' => true, 'data' => $payments]);
            
        } catch (PDOException $e) {
            sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
        break;

    case 'receipt':
        // Get receipt details
        $circulationId = $_GET['circulationId'] ?? 0;
        $receiptNo = $_GET['receiptNo'] ?? '';
        
        try {
            if ($receiptNo) {
                $stmt = $pdo->prepare("
                    SELECT 
                        fp.*,
                        m.MemberName,
                        c.MemberNo,
                        c.AccNo,
                        b.Title,
                        a.Name as CollectorName
                    FROM FinePayments fp
                    INNER JOIN Circulation c ON fp.CirculationID = c.CirculationID
                    INNER JOIN Member m ON fp.MemberNo = m.MemberNo
                    LEFT JOIN Holding h ON c.AccNo = h.AccNo
                    LEFT JOIN Books b ON h.CatNo = b.CatNo
                    LEFT JOIN Admin a ON fp.CollectedBy = a.AdminID
                    WHERE fp.ReceiptNo = ?
                ");
                $stmt->execute([$receiptNo]);
            } else {
                $stmt = $pdo->prepare("
                    SELECT 
                        fp.*,
                        m.MemberName,
                        c.MemberNo,
                        c.AccNo,
                        b.Title,
                        a.Name as CollectorName
                    FROM FinePayments fp
                    INNER JOIN Circulation c ON fp.CirculationID = c.CirculationID
                    INNER JOIN Member m ON fp.MemberNo = m.MemberNo
                    LEFT JOIN Holding h ON c.AccNo = h.AccNo
                    LEFT JOIN Books b ON h.CatNo = b.CatNo
                    LEFT JOIN Admin a ON fp.CollectedBy = a.AdminID
                    WHERE fp.CirculationID = ?
                    ORDER BY fp.PaymentDate DESC
                    LIMIT 1
                ");
                $stmt->execute([$circulationId]);
            }
            
            $receipt = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($receipt) {
                sendJson(['success' => true, 'data' => $receipt]);
            } else {
                sendJson(['success' => false, 'message' => 'Receipt not found'], 404);
            }
            
        } catch (PDOException $e) {
            sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
        break;

    case 'report':
        // Generate reports
        $type = $_GET['type'] ?? 'daily';
        
        try {
            $html = '<html><head><title>Fine Report</title><style>
                body { font-family: Arial, sans-serif; padding: 40px; }
                h1 { color: #263c79; border-bottom: 3px solid #cfac69; padding-bottom: 10px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th { background: #263c79; color: white; padding: 12px; text-align: left; }
                td { border: 1px solid #ddd; padding: 10px; }
                tr:nth-child(even) { background: #f8f9fa; }
                .total { font-weight: bold; background: #cfac69 !important; }
            </style></head><body>';
            
            switch ($type) {
                case 'daily':
                    $stmt = $pdo->query("
                        SELECT 
                            fp.ReceiptNo,
                            fp.PaymentDate,
                            m.MemberName,
                            c.MemberNo,
                            fp.FineAmount,
                            fp.PaidAmount,
                            fp.PaymentMethod
                        FROM FinePayments fp
                        INNER JOIN Circulation c ON fp.CirculationID = c.CirculationID
                        INNER JOIN Member m ON fp.MemberNo = m.MemberNo
                        WHERE DATE(fp.PaymentDate) = CURDATE()
                        ORDER BY fp.PaymentDate DESC
                    ");
                    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $total = array_sum(array_column($payments, 'PaidAmount'));
                    
                    $html .= '<h1>Daily Collection Report - ' . date('d-m-Y') . '</h1>';
                    $html .= '<table><tr><th>Receipt No</th><th>Time</th><th>Member</th><th>Fine</th><th>Paid</th><th>Method</th></tr>';
                    
                    foreach ($payments as $p) {
                        $html .= "<tr>
                            <td>{$p['ReceiptNo']}</td>
                            <td>" . date('H:i', strtotime($p['PaymentDate'])) . "</td>
                            <td>{$p['MemberName']} ({$p['MemberNo']})</td>
                            <td>₹" . number_format($p['FineAmount'], 2) . "</td>
                            <td>₹" . number_format($p['PaidAmount'], 2) . "</td>
                            <td>{$p['PaymentMethod']}</td>
                        </tr>";
                    }
                    
                    $html .= "<tr class='total'><td colspan='4'>Total Collected</td><td colspan='2'>₹" . number_format($total, 2) . "</td></tr>";
                    $html .= '</table>';
                    break;
                    
                case 'monthly':
                    $stmt = $pdo->query("
                        SELECT 
                            DATE(fp.PaymentDate) as Date,
                            COUNT(*) as Transactions,
                            SUM(fp.PaidAmount) as Total
                        FROM FinePayments fp
                        WHERE MONTH(fp.PaymentDate) = MONTH(CURDATE())
                        AND YEAR(fp.PaymentDate) = YEAR(CURDATE())
                        GROUP BY DATE(fp.PaymentDate)
                        ORDER BY Date DESC
                    ");
                    $daily = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $total = array_sum(array_column($daily, 'Total'));
                    
                    $html .= '<h1>Monthly Collection Report - ' . date('F Y') . '</h1>';
                    $html .= '<table><tr><th>Date</th><th>Transactions</th><th>Amount Collected</th></tr>';
                    
                    foreach ($daily as $d) {
                        $html .= "<tr>
                            <td>" . date('d-m-Y', strtotime($d['Date'])) . "</td>
                            <td>{$d['Transactions']}</td>
                            <td>₹" . number_format($d['Total'], 2) . "</td>
                        </tr>";
                    }
                    
                    $html .= "<tr class='total'><td>Total</td><td>" . array_sum(array_column($daily, 'Transactions')) . "</td><td>₹" . number_format($total, 2) . "</td></tr>";
                    $html .= '</table>';
                    break;
                    
                case 'defaulters':
                    $stmt = $pdo->query("
                        SELECT 
                            c.MemberNo,
                            m.MemberName,
                            COUNT(*) as FineCount,
                            SUM(r.FineAmount) as TotalFine,
                            SUM(COALESCE(fp.PaidAmount, 0)) as TotalPaid
                        FROM Circulation c
                        INNER JOIN `Return` r ON c.CirculationID = r.CirculationID
                        INNER JOIN Member m ON c.MemberNo = m.MemberNo
                        LEFT JOIN (
                            SELECT CirculationID, SUM(PaidAmount) as PaidAmount
                            FROM FinePayments
                            GROUP BY CirculationID
                        ) fp ON c.CirculationID = fp.CirculationID
                        WHERE r.FineAmount > 0
                        GROUP BY c.MemberNo, m.MemberName
                        HAVING SUM(r.FineAmount) > SUM(COALESCE(fp.PaidAmount, 0))
                        ORDER BY TotalFine DESC
                    ");
                    $defaulters = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    $html .= '<h1>Defaulters Report</h1>';
                    $html .= '<table><tr><th>Member No</th><th>Name</th><th>Fine Count</th><th>Total Fine</th><th>Paid</th><th>Pending</th></tr>';
                    
                    foreach ($defaulters as $d) {
                        $pending = $d['TotalFine'] - $d['TotalPaid'];
                        $html .= "<tr>
                            <td>{$d['MemberNo']}</td>
                            <td>{$d['MemberName']}</td>
                            <td>{$d['FineCount']}</td>
                            <td>₹" . number_format($d['TotalFine'], 2) . "</td>
                            <td>₹" . number_format($d['TotalPaid'], 2) . "</td>
                            <td>₹" . number_format($pending, 2) . "</td>
                        </tr>";
                    }
                    
                    $html .= '</table>';
                    break;
                    
                case 'waivers':
                    $stmt = $pdo->query("
                        SELECT 
                            fp.PaymentDate,
                            fp.ReceiptNo,
                            m.MemberName,
                            c.MemberNo,
                            fp.FineAmount,
                            fp.Remarks,
                            a.Name as AdminName
                        FROM FinePayments fp
                        INNER JOIN Circulation c ON fp.CirculationID = c.CirculationID
                        INNER JOIN Member m ON fp.MemberNo = m.MemberNo
                        LEFT JOIN Admin a ON fp.CollectedBy = a.AdminID
                        WHERE fp.PaymentMethod = 'Waived'
                        ORDER BY fp.PaymentDate DESC
                    ");
                    $waivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $total = array_sum(array_column($waivers, 'FineAmount'));
                    
                    $html .= '<h1>Fine Waiver Report</h1>';
                    $html .= '<table><tr><th>Date</th><th>Receipt No</th><th>Member</th><th>Amount Waived</th><th>Remarks</th><th>Approved By</th></tr>';
                    
                    foreach ($waivers as $w) {
                        $html .= "<tr>
                            <td>" . date('d-m-Y', strtotime($w['PaymentDate'])) . "</td>
                            <td>{$w['ReceiptNo']}</td>
                            <td>{$w['MemberName']} ({$w['MemberNo']})</td>
                            <td>₹" . number_format($w['FineAmount'], 2) . "</td>
                            <td>{$w['Remarks']}</td>
                            <td>{$w['AdminName']}</td>
                        </tr>";
                    }
                    
                    $html .= "<tr class='total'><td colspan='3'>Total Waived</td><td colspan='3'>₹" . number_format($total, 2) . "</td></tr>";
                    $html .= '</table>';
                    break;
            }
            
            $html .= '<p style="margin-top: 30px; text-align: center; color: #666;">Generated on ' . date('d-m-Y H:i:s') . '</p>';
            $html .= '</body></html>';
            
            header('Content-Type: text/html');
            echo $html;
            exit;
            
        } catch (PDOException $e) {
            echo '<h1>Error generating report</h1><p>' . $e->getMessage() . '</p>';
            exit;
        }

    case 'export':
        // Export to CSV
        $type = $_GET['type'] ?? 'pending';
        
        try {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="fines_' . $type . '_' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            
            if ($type === 'pending') {
                fputcsv($output, ['Member No', 'Member Name', 'Book', 'AccNo', 'Return Date', 'Days Overdue', 'Fine Amount', 'Paid', 'Pending']);
                
                $stmt = $pdo->query("
                    SELECT 
                        c.MemberNo,
                        m.MemberName,
                        b.Title,
                        c.AccNo,
                        r.ReturnDate,
                        DATEDIFF(r.ReturnDate, c.DueDate) as DaysOverdue,
                        r.FineAmount as Fine,
                        COALESCE(SUM(fp.PaidAmount), 0) as PaidAmount
                    FROM Circulation c
                    INNER JOIN `Return` r ON c.CirculationID = r.CirculationID
                    INNER JOIN Member m ON c.MemberNo = m.MemberNo
                    LEFT JOIN Holding h ON c.AccNo = h.AccNo
                    LEFT JOIN Books b ON h.CatNo = b.CatNo
                    LEFT JOIN FinePayments fp ON c.CirculationID = fp.CirculationID
                    WHERE r.FineAmount > 0
                    GROUP BY c.CirculationID
                    HAVING r.FineAmount > COALESCE(SUM(fp.PaidAmount), 0)
                ");
                
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $pending = $row['Fine'] - $row['PaidAmount'];
                    fputcsv($output, [
                        $row['MemberNo'],
                        $row['MemberName'],
                        $row['Title'],
                        $row['AccNo'],
                        $row['ReturnDate'],
                        $row['DaysOverdue'],
                        $row['Fine'],
                        $row['PaidAmount'],
                        $pending
                    ]);
                }
            }
            
            fclose($output);
            exit;
            
        } catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage();
            exit;
        }

    default:
        sendJson(['success' => false, 'message' => 'Invalid action'], 400);
}
?>
