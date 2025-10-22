<?php
/**
 * Books API Endpoints
 * Handles CRUD operations for books and holdings
 */

require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

// Quick debug entry log
file_put_contents(__DIR__ . '/api_debug.log', "\n[ENTRY] " . date('c') . " REQUEST: " . ($_SERVER['REQUEST_URI'] ?? '') . "\n", FILE_APPEND);

session_start();

// Debugging helper: capture any unexpected output or fatal errors to a log
ob_start();
register_shutdown_function(function() {
    $logFile = __DIR__ . '/api_debug.log';
    $output = ob_get_contents();
    $err = error_get_last();
    $data = "\n=== API DEBUG " . date('c') . " ===\n";
    $data .= "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? '') . "\n";
    $data .= "REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? '') . "\n";
    $data .= "OUTPUT:\n" . $output . "\n";
    if ($err) {
        $data .= "ERROR:\n" . print_r($err, true) . "\n";
    }
    file_put_contents($logFile, $data, FILE_APPEND);
});

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            // Get all books with holding information, with pagination
            $search = $_GET['search'] ?? '';
            $subject = $_GET['subject'] ?? '';
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $pageSize = isset($_GET['pageSize']) ? max(1, intval($_GET['pageSize'])) : 20;
            $offset = ($page - 1) * $pageSize;

            // Count total books for pagination
            $countSql = "SELECT COUNT(DISTINCT b.CatNo) as total FROM Books b WHERE 1=1";
            $countParams = [];
            if ($search) {
                $countSql .= " AND (b.Title LIKE ? OR b.Author1 LIKE ? OR b.ISBN LIKE ?)";
                $searchTerm = "%{$search}%";
                $countParams[] = $searchTerm;
                $countParams[] = $searchTerm;
                $countParams[] = $searchTerm;
            }
            if ($subject) {
                $countSql .= " AND b.Subject = ?";
                $countParams[] = $subject;
            }
            $countStmt = $pdo->prepare($countSql);
            $countStmt->execute($countParams);
            $total = $countStmt->fetchColumn();

            // Main query with pagination
            $sql = "
                SELECT b.*, 
                       COUNT(h.HoldID) as TotalCopies,
                       SUM(CASE WHEN h.Status = 'Available' THEN 1 ELSE 0 END) as AvailableCopies,
                       SUM(CASE WHEN h.Status = 'Issued' THEN 1 ELSE 0 END) as IssuedCopies
                FROM Books b
                LEFT JOIN Holding h ON b.CatNo = h.CatNo
                WHERE 1=1
            ";
            $params = [];
            if ($search) {
                $sql .= " AND (b.Title LIKE ? OR b.Author1 LIKE ? OR b.ISBN LIKE ?)";
                $searchTerm = "%{$search}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            if ($subject) {
                $sql .= " AND b.Subject = ?";
                $params[] = $subject;
            }
            $sql .= " GROUP BY b.CatNo ORDER BY b.Title LIMIT ? OFFSET ?";
            $params[] = $pageSize;
            $params[] = $offset;
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $books = $stmt->fetchAll();
            // Remove or sanitize any binary image data before returning JSON
            foreach ($books as &$b) {
                if (isset($b['QrCodeImg']) && $b['QrCodeImg'] !== null) {
                    // Remove large binary blob from list response to keep JSON safe and small
                    unset($b['QrCodeImg']);
                }
                if (isset($b['Barcode']) && is_resource($b['Barcode'])) {
                    unset($b['Barcode']);
                }
            }
            sendJson(['success' => true, 'data' => $books, 'total' => (int)$total, 'page' => $page, 'pageSize' => $pageSize]);
            break;
            
        case 'get':
            // Get single book details with all holdings
            $catNo = $_GET['catNo'] ?? 0;
            
            // Get book details
            $stmt = $pdo->prepare("SELECT * FROM Books WHERE CatNo = ?");
            $stmt->execute([$catNo]);
            $book = $stmt->fetch();
            
            if (!$book) {
                sendJson(['success' => false, 'message' => 'Book not found'], 404);
            }
            
            // Get all holdings for this book
            $stmt = $pdo->prepare("SELECT * FROM Holding WHERE CatNo = ? ORDER BY AccNo");
            $stmt->execute([$catNo]);
            $book['holdings'] = $stmt->fetchAll();
            // If there is binary image data for QR/Barcode, return it as base64 to keep JSON valid
            if (isset($book['QrCodeImg']) && $book['QrCodeImg'] !== null) {
                $book['QrCodeBase64'] = base64_encode($book['QrCodeImg']);
                unset($book['QrCodeImg']);
            }
            if (isset($book['Barcode']) && $book['Barcode'] !== null && !is_string($book['Barcode'])) {
                $book['BarcodeBase64'] = base64_encode($book['Barcode']);
                unset($book['Barcode']);
            }

            sendJson(['success' => true, 'data' => $book]);
            break;
            
        case 'add':
            // Add new book and holdings
            if ($method !== 'POST') {
                sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['Title'])) {
                sendJson(['success' => false, 'message' => 'Title is required'], 400);
            }
            
            $adminId = $_SESSION['AdminID'] ?? null;
            
            $pdo->beginTransaction();
            
            // Insert book
            $stmt = $pdo->prepare("
                INSERT INTO Books (Title, SubTitle, Author1, Author2, Author3, Publisher, 
                                  Place, Year, Edition, Vol, Pages, ISBN, Subject, Language, 
                                  DocumentType, CreatedBy)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['Title'],
                $data['SubTitle'] ?? null,
                $data['Author1'] ?? null,
                $data['Author2'] ?? null,
                $data['Author3'] ?? null,
                $data['Publisher'] ?? null,
                $data['Place'] ?? null,
                $data['Year'] ?? null,
                $data['Edition'] ?? null,
                $data['Vol'] ?? null,
                $data['Pages'] ?? null,
                $data['ISBN'] ?? null,
                $data['Subject'] ?? null,
                $data['Language'] ?? 'English',
                $data['DocumentType'] ?? 'BK',
                $adminId
            ]);
            
            $catNo = $pdo->lastInsertId();
            
            // Insert holdings if provided
            if (!empty($data['holdings']) && is_array($data['holdings'])) {
                $stmt = $pdo->prepare("
                    INSERT INTO Holding (AccNo, CatNo, AccDate, ClassNo, BookNo, 
                                        Status, Location, Section, Collection)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                foreach ($data['holdings'] as $holding) {
                    $stmt->execute([
                        $holding['AccNo'],
                        $catNo,
                        $holding['AccDate'] ?? date('Y-m-d'),
                        $holding['ClassNo'] ?? null,
                        $holding['BookNo'] ?? null,
                        'Available',
                        $holding['Location'] ?? null,
                        $holding['Section'] ?? null,
                        $holding['Collection'] ?? null
                    ]);
                }
            }
            
            $pdo->commit();
            
            logActivity($pdo, $adminId, 'BOOK_ADD', "Added book: {$data['Title']} (CatNo: {$catNo})");
            
            sendJson([
                'success' => true, 
                'message' => 'Book added successfully',
                'catNo' => $catNo
            ]);
            break;
            
        case 'update':
            // Update book details
            if ($method !== 'POST') {
                sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $catNo = $data['CatNo'] ?? 0;
            
            if (!$catNo) {
                sendJson(['success' => false, 'message' => 'Catalog number is required'], 400);
            }
            
            $stmt = $pdo->prepare("
                UPDATE Books 
                SET Title = ?, SubTitle = ?, Author1 = ?, Author2 = ?, Author3 = ?, 
                    Publisher = ?, Place = ?, Year = ?, Edition = ?, Vol = ?, Pages = ?, 
                    ISBN = ?, Subject = ?, Language = ?
                WHERE CatNo = ?
            ");
            
            $stmt->execute([
                $data['Title'],
                $data['SubTitle'] ?? null,
                $data['Author1'] ?? null,
                $data['Author2'] ?? null,
                $data['Author3'] ?? null,
                $data['Publisher'] ?? null,
                $data['Place'] ?? null,
                $data['Year'] ?? null,
                $data['Edition'] ?? null,
                $data['Vol'] ?? null,
                $data['Pages'] ?? null,
                $data['ISBN'] ?? null,
                $data['Subject'] ?? null,
                $data['Language'] ?? 'English',
                $catNo
            ]);
            
            sendJson(['success' => true, 'message' => 'Book updated successfully']);
            break;
            
        case 'add-holding':
            // Add a new holding/copy for existing book
            if ($method !== 'POST') {
                sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $pdo->prepare("
                INSERT INTO Holding (AccNo, CatNo, AccDate, ClassNo, BookNo, 
                                    Status, Location, Section, Collection)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['AccNo'],
                $data['CatNo'],
                $data['AccDate'] ?? date('Y-m-d'),
                $data['ClassNo'] ?? null,
                $data['BookNo'] ?? null,
                'Available',
                $data['Location'] ?? null,
                $data['Section'] ?? null,
                $data['Collection'] ?? null
            ]);
            
            sendJson(['success' => true, 'message' => 'Holding added successfully']);
            break;
            
        case 'holdings':
            // Return all holdings with book title for Holdings tab
            $sql = "SELECT h.*, b.Title FROM Holding h LEFT JOIN Books b ON h.CatNo = b.CatNo ORDER BY h.AccNo DESC LIMIT 100";
            $stmt = $pdo->query($sql);
            $holdings = $stmt->fetchAll();
            sendJson(['success' => true, 'data' => $holdings]);
            break;

        case 'search':
            // Quick search books
            $query = $_GET['q'] ?? '';
            
            if (strlen($query) < 2) {
                sendJson(['success' => true, 'data' => []]);
            }
            
            $books = searchBooks($pdo, $query, 20);
            sendJson(['success' => true, 'data' => $books]);
            break;
            
        case 'subjects':
            // Get list of all subjects
            $stmt = $pdo->query("
                SELECT DISTINCT Subject 
                FROM Books 
                WHERE Subject IS NOT NULL 
                ORDER BY Subject
            ");
            $subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            sendJson(['success' => true, 'data' => $subjects]);
            break;
            
        case 'holding-status':
            // Update holding status
            if ($method !== 'POST') {
                sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $accNo = $data['accNo'] ?? '';
            $status = $data['status'] ?? '';
            
            $validStatuses = ['Available', 'Issued', 'Damaged', 'Lost', 'Repair'];
            
            if (!in_array($status, $validStatuses)) {
                sendJson(['success' => false, 'message' => 'Invalid status'], 400);
            }
            
            $stmt = $pdo->prepare("UPDATE Holding SET Status = ? WHERE AccNo = ?");
            $stmt->execute([$status, $accNo]);
            
            sendJson(['success' => true, 'message' => 'Status updated successfully']);
            break;
            
        case 'delete':
            // Delete book (only if no holdings exist)
            if ($method !== 'POST') {
                sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $catNo = $data['CatNo'] ?? 0;
            
            // Check if any holdings exist
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM Holding WHERE CatNo = ?");
            $stmt->execute([$catNo]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                sendJson(['success' => false, 'message' => 'Cannot delete book with existing holdings'], 400);
            }
            
            $stmt = $pdo->prepare("DELETE FROM Books WHERE CatNo = ?");
            $stmt->execute([$catNo]);
            
            sendJson(['success' => true, 'message' => 'Book deleted successfully']);
            break;
            
        default:
            sendJson(['success' => false, 'message' => 'Invalid action'], 400);
    }
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendJson(['success' => false, 'message' => $e->getMessage()], 500);
}
?>
