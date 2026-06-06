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

/**
 * Generate CSRF token for form protection
 */
if (!function_exists('generateCSRFToken')) {
    function generateCSRFToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

/**
 * Validate CSRF token
 */
function validateCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

/**
 * Rate limiting function to prevent API abuse
 * @param string $identifier Unique identifier (user ID, IP, etc.)
 * @param int $maxRequests Maximum requests allowed
 * @param int $timeWindow Time window in seconds
 * @return bool True if within rate limit, false if exceeded
 */
function checkRateLimit($identifier, $maxRequests = 100, $timeWindow = 60) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $key = "rate_limit_" . md5($identifier);
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 1, 'start' => time()];
        return true;
    }
    
    $data = $_SESSION[$key];
    $elapsed = time() - $data['start'];
    
    // Reset if time window passed
    if ($elapsed > $timeWindow) {
        $_SESSION[$key] = ['count' => 1, 'start' => time()];
        return true;
    }
    
    // Check limit
    if ($data['count'] >= $maxRequests) {
        return false;
    }
    
    // Increment counter
    $_SESSION[$key]['count']++;
    return true;
}

/**
 * Validate and sanitize integer input
 */
function validateInt($value, $min = null, $max = null) {
    $filtered = filter_var($value, FILTER_VALIDATE_INT);
    if ($filtered === false) {
        return false;
    }
    if ($min !== null && $filtered < $min) {
        return false;
    }
    if ($max !== null && $filtered > $max) {
        return false;
    }
    return $filtered;
}

/**
 * Validate and sanitize string input
 */
function validateString($value, $maxLength = 255, $pattern = null) {
    if (!is_string($value) && !is_numeric($value)) {
        return false;
    }
    $value = trim((string)$value);
    if (strlen($value) > $maxLength) {
        return false;
    }
    if ($pattern !== null && !preg_match($pattern, $value)) {
        return false;
    }
    return $value;
}

/**
 * Validate date format (YYYY-MM-DD)
 */
function validateDate($date) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }
    $parts = explode('-', $date);
    return checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0]) ? $date : false;
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
function issueBook($pdo, $memberNo, $accNo, $adminId = null, $issueDate = null) {
    try {
        $pdo->beginTransaction();

        $issueDate = $issueDate ?: date('Y-m-d');
        $validatedIssueDate = validateDate($issueDate);
        if (!$validatedIssueDate) {
            throw new Exception("Invalid issue date");
        }

        $today = date('Y-m-d');
        if ($validatedIssueDate > $today) {
            throw new Exception("Issue date cannot be in the future");
        }
        
        // Check if book is available
        if (!isBookAvailable($pdo, $accNo)) {
            throw new Exception("Book is not available");
        }
        
        // Check if member can borrow
        if (!canBorrowBook($pdo, $memberNo)) {
            throw new Exception("Member has reached borrowing limit or is inactive");
        }
        
        // Due date is always 15 days from entered issue date
        $dueDate = date('Y-m-d', strtotime($validatedIssueDate . ' +15 days'));
        
        // Insert circulation record
        $stmt = $pdo->prepare("
            INSERT INTO Circulation (MemberNo, AccNo, IssueDate, IssueTime, DueDate, Status, CreatedBy)
            VALUES (?, ?, ?, ?, ?, 'Active', ?)
        ");
        $stmt->execute([$memberNo, $accNo, $validatedIssueDate, date('H:i:s'), $dueDate, $adminId]);
        
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
function returnBook($pdo, $circulationId, $condition = 'Good', $remarks = '', $options = []) {
    try {
        $pdo->beginTransaction();
        
        // Get circulation details
        $stmt = $pdo->prepare("SELECT * FROM Circulation WHERE CirculationID = ? AND Status = 'Active'");
        $stmt->execute([$circulationId]);
        $circulation = $stmt->fetch();
        
        if (!$circulation) {
            throw new Exception("Invalid circulation record");
        }

        // Lost/damaged/dead-stock books cannot be processed through normal return flow.
        $holdingStmt = $pdo->prepare("SELECT Status FROM Holding WHERE AccNo = ? LIMIT 1");
        $holdingStmt->execute([$circulation['AccNo']]);
        $holding = $holdingStmt->fetch(PDO::FETCH_ASSOC);
        $holdingStatus = $holding['Status'] ?? '';
        if (in_array($holdingStatus, ['Lost', 'Damaged', 'Dead Stock'], true)) {
            throw new Exception("Book is marked as {$holdingStatus} and cannot be returned in Return Books workflow");
        }

        if (in_array($condition, ['Lost', 'Damaged'], true)) {
            throw new Exception("Lost/Damaged books should be handled via stock verification, not normal return processing");
        }
        
        $returnDate = $options['returnDate'] ?? date('Y-m-d');
        $validatedReturnDate = validateDate($returnDate);
        if (!$validatedReturnDate) {
            throw new Exception("Invalid return date");
        }

        $issueDate = $circulation['IssueDate'];
        if ($validatedReturnDate < $issueDate) {
            throw new Exception("Return date cannot be before issue date");
        }

        $returnTime = date('H:i:s');
        
        // Calculate fine if overdue
        $fine = 0;
        if ($validatedReturnDate > $circulation['DueDate']) {
            $member = getMemberByNo($pdo, $circulation['MemberNo']);
            $finePerDay = $member['FinePerDay'] ?? 2.00;
            
            $daysOverdue = (strtotime($validatedReturnDate) - strtotime($circulation['DueDate'])) / (60 * 60 * 24);
            $fine = $daysOverdue * $finePerDay;
        }

        $finePaid = 0.0;
        if ($fine > 0) {
            $isFinePaid = !empty($options['finePaid']);
            if (!$isFinePaid) {
                throw new Exception("Fine must be paid before returning an overdue book");
            }

            $finePaid = (float)($options['finePaidAmount'] ?? 0);
            if ($finePaid < $fine) {
                throw new Exception("Fine payment is insufficient. Please collect full fine before return");
            }

            $receiptNo = trim((string)($options['fineReceiptNo'] ?? ''));
            $paymentDate = trim((string)($options['finePaymentDate'] ?? ''));
            $paymentQr = trim((string)($options['finePaymentQr'] ?? ''));
            if ($receiptNo === '' || $paymentDate === '' || $paymentQr === '') {
                throw new Exception("Receipt number, payment date, amount and QR reference are required for fine payment");
            }

            if (!validateDate($paymentDate)) {
                throw new Exception("Invalid fine payment date");
            }
            if ($paymentDate > $validatedReturnDate) {
                throw new Exception("Fine payment date cannot be after return date");
            }

            $columnsStmt = $pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'FinePayments'");
            $columnsStmt->execute();
            $paymentColumns = array_map('strtolower', array_column($columnsStmt->fetchAll(PDO::FETCH_ASSOC), 'COLUMN_NAME'));

            $hasModernFinePayments = in_array('circulationid', $paymentColumns, true)
                && in_array('memberno', $paymentColumns, true)
                && in_array('fineamount', $paymentColumns, true)
                && in_array('paidamount', $paymentColumns, true)
                && in_array('paymentdate', $paymentColumns, true)
                && in_array('receiptno', $paymentColumns, true);

            if ($hasModernFinePayments) {
                $paymentMethod = 'QR';
                $collectedBy = $options['collectedBy'] ?? null;
                $paymentRemarks = trim((string)($options['finePaymentRemarks'] ?? ''));
                if ($paymentRemarks !== '') {
                    $paymentRemarks .= ' | ';
                }
                $paymentRemarks .= 'QR Ref: ' . $paymentQr;

                $insertPayment = $pdo->prepare("INSERT INTO FinePayments (CirculationID, MemberNo, FineAmount, PaidAmount, PaymentDate, PaymentMethod, ReceiptNo, CollectedBy, Remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $insertPayment->execute([
                    $circulationId,
                    $circulation['MemberNo'],
                    $fine,
                    $finePaid,
                    $paymentDate . ' ' . $returnTime,
                    $paymentMethod,
                    $receiptNo,
                    $collectedBy,
                    $paymentRemarks
                ]);
            }

            if ($remarks !== '') {
                $remarks .= ' | ';
            }
            $remarks .= 'Fine payment: Receipt ' . $receiptNo . ', Date ' . $paymentDate . ', Amount ' . number_format($finePaid, 2, '.', '') . ', QR ' . $paymentQr;
        }
        
        // Insert return record
        $stmt = $pdo->prepare("
        INSERT INTO `Return` (CirculationID, MemberNo, AccNo, ReturnDate, ReturnTime, FineAmount, FinePaid, `Condition`, Remarks)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
        $stmt->execute([
            $circulationId, 
            $circulation['MemberNo'], 
            $circulation['AccNo'], 
            $validatedReturnDate, 
            $returnTime, 
            $fine, 
            $finePaid,
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
        $stmt = $pdo->prepare("UPDATE Member SET BooksIssued = GREATEST(BooksIssued - 1, 0) WHERE MemberNo = ?");
        $stmt->execute([$circulation['MemberNo']]);
        
        $pdo->commit();
        return [
            'success' => true,
            'message' => 'Book returned successfully',
            'fine' => $fine,
            'finePaid' => $finePaid
        ];
        
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
    
    try {
        // Total books
        $stmt = $pdo->query("SELECT COUNT(DISTINCT CatNo) as total FROM Books");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['totalBooks'] = (int)($result['total'] ?? 0);
        
        // Total holdings/copies
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM Holding");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['totalCopies'] = (int)($result['total'] ?? 0);
        
        // Available books
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM Holding WHERE Status = 'Available'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['availableBooks'] = (int)($result['total'] ?? 0);
        
        // Books issued
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM Circulation WHERE Status = 'Active'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['booksIssued'] = (int)($result['total'] ?? 0);
        
        // Total members
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM Member");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['totalMembers'] = (int)($result['total'] ?? 0);
        
        // Active members
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM Member WHERE Status = 'Active'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['activeMembers'] = (int)($result['total'] ?? 0);
        
        // Overdue books
        $today = date('Y-m-d');
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM Circulation WHERE Status = 'Active' AND DueDate < ?");
        $stmt->execute([$today]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['overdueBooks'] = (int)($result['total'] ?? 0);
        
        // Today's footfall
        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT MemberNo) as total FROM Footfall WHERE Date = ?");
        $stmt->execute([$today]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['todayFootfall'] = (int)($result['total'] ?? 0);
        
    } catch (PDOException $e) {
        error_log("Dashboard stats error: " . $e->getMessage());
        // Return zeros if there's an error
        $stats = [
            'totalBooks' => 0,
            'totalCopies' => 0,
            'availableBooks' => 0,
            'booksIssued' => 0,
            'totalMembers' => 0,
            'activeMembers' => 0,
            'overdueBooks' => 0,
            'todayFootfall' => 0
        ];
    }
    
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
 * Log audit event (admin-focused, entity-aware)
 *
 * Contract:
 * - Inputs: $adminId (nullable INT), $action (string), $entityType (string|null), $entityId (string|int|null), $metadata (array|string|null)
 * - Side-effects: Inserts a row into AuditLog capturing IP and User-Agent
 * - Resilience: Never throws; on failure writes to error_log
 */
function logAudit($pdo, $adminId, $action, $entityType = null, $entityId = null, $metadata = null) {
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
        // Encode metadata safely if it's an array/object
        if (is_array($metadata) || is_object($metadata)) {
            $metaJson = json_encode(
                $metadata,
                JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_UNICODE
            );
        } else {
            $metaJson = $metadata; // allow raw string/null
        }

        $stmt = $pdo->prepare("INSERT INTO AuditLog (admin_id, action, entity_type, entity_id, metadata, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $entityIdStr = isset($entityId) ? (string)$entityId : null;
        $stmt->execute([$adminId, $action, $entityType, $entityIdStr, $metaJson, $ip, $ua]);
    } catch (Exception $e) {
        error_log('Audit logging failed: ' . $e->getMessage());
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
