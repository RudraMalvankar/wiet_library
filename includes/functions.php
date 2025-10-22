<?php
/**
 * Common Functions Library
 * WIET Library Management System
 * 
 * Reusable functions for database operations, validation, and utilities
 */

// Security Functions
// ==================

/**
 * Sanitize user input to prevent XSS attacks
 */
function sanitize($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Hash password securely
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Member Functions
// ================

/**
 * Get member details by member number
 */
function getMemberByNo($pdo, $memberNo) {
    $stmt = $pdo->prepare("SELECT * FROM Member WHERE MemberNo = ?");
    $stmt->execute([$memberNo]);
    return $stmt->fetch();
}

/**
 * Get all active members
 */
function getActiveMembers($pdo) {
    $stmt = $pdo->query("SELECT * FROM Member WHERE Status = 'Active' ORDER BY MemberName");
    return $stmt->fetchAll();
}

/**
 * Check if member can borrow more books
 */
function canBorrowBook($pdo, $memberNo) {
    $member = getMemberByNo($pdo, $memberNo);
    if (!$member || $member['Status'] !== 'Active') {
        return false;
    }
    
    // Check book limit based on group
    $limits = [
        'Student' => 3,
        'Faculty' => 5,
        'Staff' => 3
    ];
    
    $limit = $limits[$member['Group']] ?? 3;
    return $member['BooksIssued'] < $limit;
}

// Book Functions
// ==============

/**
 * Get book details by catalog number
 */
function getBookByCatNo($pdo, $catNo) {
    $stmt = $pdo->prepare("SELECT * FROM Books WHERE CatNo = ?");
    $stmt->execute([$catNo]);
    return $stmt->fetch();
}

/**
 * Get holding details by accession number
 */
function getHoldingByAccNo($pdo, $accNo) {
    $stmt = $pdo->prepare("
        SELECT h.*, b.Title, b.Author1, b.Publisher, b.Year 
        FROM Holding h 
        JOIN Books b ON h.CatNo = b.CatNo 
        WHERE h.AccNo = ?
    ");
    $stmt->execute([$accNo]);
    return $stmt->fetch();
}

/**
 * Check if book is available for issue
 */
function isBookAvailable($pdo, $accNo) {
    $stmt = $pdo->prepare("SELECT Status FROM Holding WHERE AccNo = ?");
    $stmt->execute([$accNo]);
    $holding = $stmt->fetch();
    return $holding && $holding['Status'] === 'Available';
}

/**
 * Search books by title, author, or ISBN
 */
function searchBooks($pdo, $query, $limit = 50) {
    $searchTerm = "%{$query}%";
    $stmt = $pdo->prepare("
        SELECT b.*, COUNT(h.HoldID) as TotalCopies,
               SUM(CASE WHEN h.Status = 'Available' THEN 1 ELSE 0 END) as AvailableCopies
        FROM Books b
        LEFT JOIN Holding h ON b.CatNo = h.CatNo
        WHERE b.Title LIKE ? 
           OR b.Author1 LIKE ? 
           OR b.Author2 LIKE ? 
           OR b.ISBN LIKE ?
           OR b.Subject LIKE ?
        GROUP BY b.CatNo
        ORDER BY b.Title
        LIMIT ?
    ");
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit]);
    return $stmt->fetchAll();
}

// Circulation Functions
// =====================

/**
 * Issue a book to a member
 */
function issueBook($pdo, $memberNo, $accNo, $adminId = null) {
    try {
        $pdo->beginTransaction();
        
        // Check if book is available
        if (!isBookAvailable($pdo, $accNo)) {
            throw new Exception("Book is not available");
        }
        
        // Check if member can borrow
        if (!canBorrowBook($pdo, $memberNo)) {
            throw new Exception("Member has reached borrowing limit or is inactive");
        }
        
        // Calculate due date (15 days from now)
        $issueDate = date('Y-m-d');
        $dueDate = date('Y-m-d', strtotime('+15 days'));
        
        // Insert circulation record
        $stmt = $pdo->prepare("
            INSERT INTO Circulation (MemberNo, AccNo, IssueDate, IssueTime, DueDate, Status, CreatedBy)
            VALUES (?, ?, ?, ?, ?, 'Active', ?)
        ");
        $stmt->execute([$memberNo, $accNo, $issueDate, date('H:i:s'), $dueDate, $adminId]);
        
        // Update holding status
        $stmt = $pdo->prepare("UPDATE Holding SET Status = 'Issued' WHERE AccNo = ?");
        $stmt->execute([$accNo]);
        
        // Increment member's books issued count
        $stmt = $pdo->prepare("UPDATE Member SET BooksIssued = BooksIssued + 1 WHERE MemberNo = ?");
        $stmt->execute([$memberNo]);
        
        $pdo->commit();
        return ['success' => true, 'message' => 'Book issued successfully', 'dueDate' => $dueDate];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Return a book
 */
function returnBook($pdo, $circulationId, $condition = 'Good', $remarks = '') {
    try {
        $pdo->beginTransaction();
        
        // Get circulation details
        $stmt = $pdo->prepare("SELECT * FROM Circulation WHERE CirculationID = ? AND Status = 'Active'");
        $stmt->execute([$circulationId]);
        $circulation = $stmt->fetch();
        
        if (!$circulation) {
            throw new Exception("Invalid circulation record");
        }
        
        $returnDate = date('Y-m-d');
        $returnTime = date('H:i:s');
        
        // Calculate fine if overdue
        $fine = 0;
        if ($returnDate > $circulation['DueDate']) {
            $member = getMemberByNo($pdo, $circulation['MemberNo']);
            $finePerDay = $member['FinePerDay'] ?? 2.00;
            
            $daysOverdue = (strtotime($returnDate) - strtotime($circulation['DueDate'])) / (60 * 60 * 24);
            $fine = $daysOverdue * $finePerDay;
        }
        
        // Insert return record
        $stmt = $pdo->prepare("
            INSERT INTO `Return` (CirculationID, MemberNo, AccNo, ReturnDate, ReturnTime, FineAmount, Condition, Remarks)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $circulationId, 
            $circulation['MemberNo'], 
            $circulation['AccNo'], 
            $returnDate, 
            $returnTime, 
            $fine, 
            $condition, 
            $remarks
        ]);
        
        // Update circulation status
        $stmt = $pdo->prepare("UPDATE Circulation SET Status = 'Returned' WHERE CirculationID = ?");
        $stmt->execute([$circulationId]);
        
        // Update holding status
        $stmt = $pdo->prepare("UPDATE Holding SET Status = 'Available' WHERE AccNo = ?");
        $stmt->execute([$circulation['AccNo']]);
        
        // Decrement member's books issued count
        $stmt = $pdo->prepare("UPDATE Member SET BooksIssued = BooksIssued - 1 WHERE MemberNo = ?");
        $stmt->execute([$circulation['MemberNo']]);
        
        $pdo->commit();
        return ['success' => true, 'message' => 'Book returned successfully', 'fine' => $fine];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get active circulations for a member
 */
function getMemberActiveCirculations($pdo, $memberNo) {
    $stmt = $pdo->prepare("
        SELECT c.*, h.AccNo, b.Title, b.Author1, b.Publisher
        FROM Circulation c
        JOIN Holding h ON c.AccNo = h.AccNo
        JOIN Books b ON h.CatNo = b.CatNo
        WHERE c.MemberNo = ? AND c.Status = 'Active'
        ORDER BY c.IssueDate DESC
    ");
    $stmt->execute([$memberNo]);
    return $stmt->fetchAll();
}

/**
 * Get overdue books
 */
function getOverdueBooks($pdo) {
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("
        SELECT c.*, m.MemberName, m.Phone, m.Email, b.Title, b.Author1
        FROM Circulation c
        JOIN Member m ON c.MemberNo = m.MemberNo
        JOIN Holding h ON c.AccNo = h.AccNo
        JOIN Books b ON h.CatNo = b.CatNo
        WHERE c.Status = 'Active' AND c.DueDate < ?
        ORDER BY c.DueDate ASC
    ");
    $stmt->execute([$today]);
    return $stmt->fetchAll();
}

// Dashboard Statistics
// ====================

/**
 * Get dashboard statistics
 */
function getDashboardStats($pdo) {
    $stats = [];
    
    // Total books
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Books");
    $stats['totalBooks'] = $stmt->fetch()['total'];
    
    // Total holdings/copies
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Holding");
    $stats['totalCopies'] = $stmt->fetch()['total'];
    
    // Available books
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Holding WHERE Status = 'Available'");
    $stats['availableBooks'] = $stmt->fetch()['total'];
    
    // Books issued
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Circulation WHERE Status = 'Active'");
    $stats['booksIssued'] = $stmt->fetch()['total'];
    
    // Total members
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Member");
    $stats['totalMembers'] = $stmt->fetch()['total'];
    
    // Active members
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Member WHERE Status = 'Active'");
    $stats['activeMembers'] = $stmt->fetch()['total'];
    
    // Overdue books
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM Circulation WHERE Status = 'Active' AND DueDate < ?");
    $stmt->execute([$today]);
    $stats['overdueBooks'] = $stmt->fetch()['total'];
    
    // Today's footfall
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT MemberNo) as total FROM Footfall WHERE Date = ?");
    $stmt->execute([$today]);
    $stats['todayFootfall'] = $stmt->fetch()['total'];
    
    return $stats;
}

// Utility Functions
// =================

/**
 * Format date for display
 */
function formatDate($date, $format = 'd-m-Y') {
    return date($format, strtotime($date));
}

/**
 * Calculate days between dates
 */
function daysBetween($date1, $date2) {
    return abs((strtotime($date2) - strtotime($date1)) / (60 * 60 * 24));
}

/**
 * Generate unique member number
 */
function generateMemberNo($pdo, $prefix = 'C') {
    $year = date('y');
    $stmt = $pdo->query("SELECT MAX(MemberNo) as maxNo FROM Member");
    $result = $stmt->fetch();
    $maxNo = $result['maxNo'] ?? 0;
    
    // Extract number part and increment
    $number = intval(substr($maxNo, -4)) + 1;
    return $prefix . $year . str_pad($number, 4, '0', STR_PAD_LEFT);
}

/**
 * Generate AccNo in format: CatNo-CopyNo (e.g., 1001-1, 1001-2)
 */
function generateAccNo($catNo, $copyNo) {
    return sprintf("%d-%d", $catNo, $copyNo);
}

/**
 * Log activity
 */
function logActivity($pdo, $userId, $action, $details = '') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO ActivityLog (UserID, Action, Details, Timestamp)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $action, $details]);
    } catch (Exception $e) {
        error_log("Activity logging failed: " . $e->getMessage());
    }
}

/**
 * Send JSON response
 */
function sendJson($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    $json = json_encode($data);
    if ($json === false) {
        $err = json_last_error_msg();
        error_log("JSON encode error: $err");
        // Try a safe encode that substitutes invalid UTF-8 and partial output
        $json = json_encode($data, JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            // As last resort, convert to a simple string representation
            $json = json_encode(['success' => false, 'message' => 'Failed to encode response to JSON', 'error' => $err]);
        }
    }
    echo $json;
    exit;
}
?>
