<?php
/**
 * Circulation API Endpoints
 * Handles book issue, return, and renewal operations
 */

require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

// Start session for admin info
session_start();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'issue':
            // Issue a book to a member
            if ($method !== 'POST') {
                sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $memberNo = $data['memberNo'] ?? 0;
            $accNo = $data['accNo'] ?? '';
            $adminId = $_SESSION['AdminID'] ?? null;
            
            if (!$memberNo || !$accNo) {
                sendJson(['success' => false, 'message' => 'Member number and accession number are required'], 400);
            }
            
            // Use the issueBook function from functions.php
            $result = issueBook($pdo, $memberNo, $accNo, $adminId);
            
            if ($result['success']) {
                // Log activity
                logActivity($pdo, $adminId, 'BOOK_ISSUE', "Issued book {$accNo} to member {$memberNo}");
            }
            
            sendJson($result);
            break;
            
        case 'return':
            // Return a book
            if ($method !== 'POST') {
                sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $circulationId = $data['circulationId'] ?? 0;
            $condition = $data['condition'] ?? 'Good';
            $remarks = $data['remarks'] ?? '';
            $adminId = $_SESSION['AdminID'] ?? null;
            
            if (!$circulationId) {
                sendJson(['success' => false, 'message' => 'Circulation ID is required'], 400);
            }
            
            // Use the returnBook function from functions.php
            $result = returnBook($pdo, $circulationId, $condition, $remarks);
            
            if ($result['success']) {
                // Log activity
                logActivity($pdo, $adminId, 'BOOK_RETURN', "Returned book for circulation ID {$circulationId}");
            }
            
            sendJson($result);
            break;
            
        case 'renew':
            // Renew a book
            if ($method !== 'POST') {
                sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $circulationId = $data['circulationId'] ?? 0;
            $adminId = $_SESSION['AdminID'] ?? null;
            
            // Get circulation details
            $stmt = $pdo->prepare("SELECT * FROM Circulation WHERE CirculationID = ? AND Status = 'Active'");
            $stmt->execute([$circulationId]);
            $circulation = $stmt->fetch();
            
            if (!$circulation) {
                sendJson(['success' => false, 'message' => 'Circulation record not found'], 404);
            }
            
            // Check renewal limit (max 2 renewals)
            if ($circulation['RenewalCount'] >= 2) {
                sendJson(['success' => false, 'message' => 'Maximum renewal limit reached'], 400);
            }
            
            // Extend due date by 15 days
            $newDueDate = date('Y-m-d', strtotime($circulation['DueDate'] . ' +15 days'));
            
            $stmt = $pdo->prepare("
                UPDATE Circulation 
                SET DueDate = ?, RenewalCount = RenewalCount + 1 
                WHERE CirculationID = ?
            ");
            $stmt->execute([$newDueDate, $circulationId]);
            
            // Log activity
            logActivity($pdo, $adminId, 'BOOK_RENEWAL', "Renewed book for circulation ID {$circulationId}");
            
            sendJson([
                'success' => true, 
                'message' => 'Book renewed successfully',
                'newDueDate' => $newDueDate
            ]);
            break;
            
        case 'active':
            // Get all active circulations
            $stmt = $pdo->query("
                SELECT c.*, 
                       m.MemberName, m.Phone, m.Email, m.`Group`,
                       b.Title, b.Author1, b.Publisher,
                       h.AccNo,
                       DATEDIFF(CURDATE(), c.DueDate) as DaysOverdue,
                       CASE 
                           WHEN CURDATE() > c.DueDate THEN DATEDIFF(CURDATE(), c.DueDate) * m.FinePerDay
                           ELSE 0
                       END as FineAmount
                FROM Circulation c
                JOIN Member m ON c.MemberNo = m.MemberNo
                JOIN Holding h ON c.AccNo = h.AccNo
                JOIN Books b ON h.CatNo = b.CatNo
                WHERE c.Status = 'Active'
                ORDER BY c.IssueDate DESC
            ");
            
            $circulations = $stmt->fetchAll();
            sendJson(['success' => true, 'data' => $circulations]);
            break;
            
        case 'overdue':
            // Get overdue books
            $overdueBooks = getOverdueBooks($pdo);
            sendJson(['success' => true, 'data' => $overdueBooks]);
            break;
            
        case 'history':
            // Get circulation history
            $memberNo = $_GET['memberNo'] ?? 0;
            $limit = $_GET['limit'] ?? 50;
            
            $sql = "
                SELECT c.*, 
                       m.MemberName,
                       b.Title, b.Author1,
                       r.ReturnDate, r.FineAmount, r.Condition
                FROM Circulation c
                JOIN Member m ON c.MemberNo = m.MemberNo
                JOIN Holding h ON c.AccNo = h.AccNo
                JOIN Books b ON h.CatNo = b.CatNo
                LEFT JOIN `Return` r ON c.CirculationID = r.CirculationID
            ";
            
            $params = [];
            
            if ($memberNo) {
                $sql .= " WHERE c.MemberNo = ?";
                $params[] = $memberNo;
            }
            
            $sql .= " ORDER BY c.IssueDate DESC LIMIT ?";
            $params[] = (int)$limit;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $history = $stmt->fetchAll();
            
            sendJson(['success' => true, 'data' => $history]);
            break;
            
        case 'member-books':
            // Get books currently issued to a member
            $memberNo = $_GET['memberNo'] ?? 0;
            
            if (!$memberNo) {
                sendJson(['success' => false, 'message' => 'Member number is required'], 400);
            }
            
            $books = getMemberActiveCirculations($pdo, $memberNo);
            sendJson(['success' => true, 'data' => $books]);
            break;
            
        case 'check-availability':
            // Check if a book is available
            $accNo = $_GET['accNo'] ?? '';
            
            if (!$accNo) {
                sendJson(['success' => false, 'message' => 'Accession number is required'], 400);
            }
            
            $holding = getHoldingByAccNo($pdo, $accNo);
            
            if (!$holding) {
                sendJson(['success' => false, 'message' => 'Book not found'], 404);
            }
            
            $available = $holding['Status'] === 'Available';
            
            sendJson([
                'success' => true,
                'available' => $available,
                'status' => $holding['Status'],
                'book' => $holding
            ]);
            break;
            
        case 'stats':
            // Get circulation statistics for dashboard cards
            $today = date('Y-m-d');
            
            // Books Currently Issued (Active Circulations)
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM Circulation WHERE Status = 'Active'");
            $stmt->execute();
            $totalIssued = (int)$stmt->fetchColumn();
            
            // Due Today
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM Circulation WHERE Status = 'Active' AND DueDate = ?");
            $stmt->execute([$today]);
            $dueToday = (int)$stmt->fetchColumn();
            
            // Overdue Books
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM Circulation WHERE Status = 'Active' AND DueDate < ?");
            $stmt->execute([$today]);
            $overdue = (int)$stmt->fetchColumn();
            
            // Today's Returns
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM `Return` WHERE ReturnDate = ?");
            $stmt->execute([$today]);
            $todayReturns = (int)$stmt->fetchColumn();
            
            sendJson([
                'success' => true,
                'data' => [
                    'totalIssued' => $totalIssued,
                    'dueToday' => $dueToday,
                    'overdue' => $overdue,
                    'todayReturns' => $todayReturns
                ]
            ]);
            break;
            
        default:
            sendJson(['success' => false, 'message' => 'Invalid action'], 400);
    }
    
} catch (Exception $e) {
    sendJson(['success' => false, 'message' => $e->getMessage()], 500);
}
?>
