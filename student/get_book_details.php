<?php
// Get Book Details - Fetch complete book information for modal display
session_start();

// Check authentication
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Include database connection and functions
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Get parameters
$acc_no = $_GET['acc_no'] ?? null;
$circulation_id = $_GET['circulation_id'] ?? null;
$member_no = $_SESSION['member_no'] ?? null;

if (!$acc_no || !$circulation_id || !$member_no) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

try {
    // Fetch complete book details with circulation information
    $query = "
        SELECT 
            c.CirculationID,
            c.AccNo,
            c.IssueDate,
            c.DueDate,
            c.RenewalCount,
            DATEDIFF(c.DueDate, CURDATE()) as days_left,
            h.CatNo,
            b.Title,
            b.Author1 as Author,
            b.ISBN,
            b.Publisher,
            b.Edition,
            h.Location,
            m.FinePerDay
        FROM Circulation c
        INNER JOIN Holding h ON c.AccNo = h.AccNo
        INNER JOIN Books b ON h.CatNo = b.CatNo
        INNER JOIN Member m ON c.MemberNo = m.MemberNo
        WHERE c.CirculationID = :circulation_id
        AND c.MemberNo = :member_no
        AND c.AccNo = :acc_no
        AND c.Status = 'Active'
        AND NOT EXISTS (
            SELECT 1 FROM `Return` r 
            WHERE r.CirculationID = c.CirculationID
        )
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'circulation_id' => $circulation_id,
        'member_no' => $member_no,
        'acc_no' => $acc_no
    ]);
    
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$book) {
        echo json_encode(['success' => false, 'message' => 'Book not found']);
        exit();
    }
    
    // Calculate fine for overdue books
    $daysLeft = (int)$book['days_left'];
    $fine = 0;
    if ($daysLeft < 0) {
        $fine = abs($daysLeft) * (float)($book['FinePerDay'] ?? 2.00);
    }
    
    // Format dates
    $book['issue_date_formatted'] = date('M j, Y', strtotime($book['IssueDate']));
    $book['due_date_formatted'] = date('M j, Y', strtotime($book['DueDate']));
    $book['fine'] = $fine;
    $book['days_left'] = $daysLeft;
    
    // Fetch previous borrowing history for this book by this member
    $historyQuery = "
        SELECT 
            c.IssueDate,
            r.ReturnDate,
            r.FineAmount as Fine
        FROM `Return` r
        INNER JOIN Circulation c ON r.CirculationID = c.CirculationID
        WHERE c.MemberNo = :member_no
        AND c.AccNo = :acc_no
        AND r.ReturnDate IS NOT NULL
        ORDER BY r.ReturnDate DESC
        LIMIT 5
    ";
    
    $historyStmt = $pdo->prepare($historyQuery);
    $historyStmt->execute([
        'member_no' => $member_no,
        'acc_no' => $acc_no
    ]);
    
    $returnHistory = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format history dates
    $formattedHistory = [];
    foreach ($returnHistory as $history) {
        $formattedHistory[] = [
            'issue_date' => date('M j, Y', strtotime($history['IssueDate'])),
            'return_date' => date('M j, Y', strtotime($history['ReturnDate'])),
            'fine' => (float)$history['Fine']
        ];
    }
    
    $book['return_history'] = $formattedHistory;
    
    echo json_encode([
        'success' => true,
        'book' => $book
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>

