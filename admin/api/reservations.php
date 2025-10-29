<?php
/**
 * Book Reservations API
 * Handles book reservation/hold requests
 */

session_start();
header('Content-Type: application/json');

require_once '../../includes/db_connect.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Check authentication
$is_admin = isset($_SESSION['admin_id']);
$is_student = isset($_SESSION['student_id']);
$member_no = $_SESSION['member_no'] ?? null;

if (!$is_admin && !$is_student) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    switch ($action) {
        case 'reserve':
            reserveBook($pdo, $member_no);
            break;
            
        case 'cancel':
            cancelReservation($pdo, $member_no, $is_admin);
            break;
            
        case 'my_reservations':
            getMyReservations($pdo, $member_no);
            break;
            
        case 'list':
            listAllReservations($pdo, $is_admin);
            break;
            
        case 'stats':
            getReservationStats($pdo, $is_admin);
            break;
            
        case 'fulfill':
            fulfillReservation($pdo, $is_admin);
            break;
            
        case 'check_eligibility':
            checkEligibility($pdo, $member_no);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (PDOException $e) {
    error_log("Reservation API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

/**
 * Reserve a book
 */
function reserveBook($pdo, $member_no) {
    $cat_no = $_POST['cat_no'] ?? null;
    
    if (!$cat_no || !$member_no) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }
    
    // Check if book exists
    $book_stmt = $pdo->prepare("SELECT CatNo, Title FROM Books WHERE CatNo = ?");
    $book_stmt->execute([$cat_no]);
    $book = $book_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$book) {
        echo json_encode(['success' => false, 'message' => 'Book not found']);
        return;
    }
    
    // Check if all copies are issued
    $available_stmt = $pdo->prepare("
        SELECT COUNT(*) as available_count
        FROM Holding
        WHERE CatNo = ? AND Status = 'Available'
    ");
    $available_stmt->execute([$cat_no]);
    $available = $available_stmt->fetch(PDO::FETCH_ASSOC)['available_count'];
    
    if ($available > 0) {
        echo json_encode(['success' => false, 'message' => 'Book is currently available. Please borrow it directly.']);
        return;
    }
    
    // Check if member already has active reservation for this book
    $existing_stmt = $pdo->prepare("
        SELECT ReservationID, Status
        FROM BookReservations
        WHERE MemberNo = ? AND CatNo = ? AND Status IN ('Pending', 'Ready')
    ");
    $existing_stmt->execute([$member_no, $cat_no]);
    $existing = $existing_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        echo json_encode([
            'success' => false, 
            'message' => 'You already have a ' . strtolower($existing['Status']) . ' reservation for this book'
        ]);
        return;
    }
    
    // Check member's reservation limit (max 3 active reservations)
    $count_stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM BookReservations
        WHERE MemberNo = ? AND Status IN ('Pending', 'Ready')
    ");
    $count_stmt->execute([$member_no]);
    $active_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($active_count >= 3) {
        echo json_encode(['success' => false, 'message' => 'You have reached the maximum limit of 3 active reservations']);
        return;
    }
    
    // Create reservation
    $insert_stmt = $pdo->prepare("
        INSERT INTO BookReservations (MemberNo, CatNo, Status, Notes)
        VALUES (?, ?, 'Pending', 'Reservation created by student')
    ");
    $insert_stmt->execute([$member_no, $cat_no]);
    $reservation_id = $pdo->lastInsertId();
    
    // Get queue position
    $queue_stmt = $pdo->prepare("
        SELECT COUNT(*) + 1 as position
        FROM BookReservations
        WHERE CatNo = ? AND Status = 'Pending' AND RequestDate < NOW()
    ");
    $queue_stmt->execute([$cat_no]);
    $queue_position = $queue_stmt->fetch(PDO::FETCH_ASSOC)['position'];
    
    // Log activity
    try {
        $log_stmt = $pdo->prepare("
            INSERT INTO ActivityLog (UserID, UserType, Action, Details, IPAddress)
            VALUES (?, 'Student', 'Reserve Book', ?, ?)
        ");
        $log_stmt->execute([
            $member_no,
            "Reserved book: $book[Title] (CatNo: $cat_no)",
            $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
        ]);
    } catch (PDOException $e) {
        error_log("Activity log error: " . $e->getMessage());
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Book reserved successfully! You are #' . $queue_position . ' in the queue.',
        'data' => [
            'reservation_id' => $reservation_id,
            'queue_position' => $queue_position,
            'book_title' => $book['Title']
        ]
    ]);
}

/**
 * Cancel reservation
 */
function cancelReservation($pdo, $member_no, $is_admin) {
    $reservation_id = $_POST['reservation_id'] ?? null;
    
    if (!$reservation_id) {
        echo json_encode(['success' => false, 'message' => 'Missing reservation ID']);
        return;
    }
    
    // Build WHERE clause based on user type
    $where = "ReservationID = ?";
    $params = [$reservation_id];
    
    if (!$is_admin) {
        $where .= " AND MemberNo = ?";
        $params[] = $member_no;
    }
    
    // Check if reservation exists and can be cancelled
    $check_stmt = $pdo->prepare("
        SELECT ReservationID, Status, CatNo, MemberNo
        FROM BookReservations
        WHERE $where AND Status IN ('Pending', 'Ready')
    ");
    $check_stmt->execute($params);
    $reservation = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reservation) {
        echo json_encode(['success' => false, 'message' => 'Reservation not found or cannot be cancelled']);
        return;
    }
    
    // Cancel reservation
    $cancel_stmt = $pdo->prepare("
        UPDATE BookReservations
        SET Status = 'Cancelled',
            CancelledAt = NOW(),
            CancellationReason = ?
        WHERE ReservationID = ?
    ");
    $reason = $_POST['reason'] ?? 'Cancelled by ' . ($is_admin ? 'admin' : 'student');
    $cancel_stmt->execute([$reason, $reservation_id]);
    
    // If this was a Ready reservation, notify next in queue
    if ($reservation['Status'] == 'Ready') {
        try {
            $notify_stmt = $pdo->prepare("CALL NotifyNextReservation(?)");
            $notify_stmt->execute([$reservation['CatNo']]);
        } catch (PDOException $e) {
            error_log("Notify next error: " . $e->getMessage());
        }
    }
    
    // Log activity
    try {
        $log_stmt = $pdo->prepare("
            INSERT INTO ActivityLog (UserID, UserType, Action, Details, IPAddress)
            VALUES (?, ?, 'Cancel Reservation', ?, ?)
        ");
        $log_stmt->execute([
            $is_admin ? $_SESSION['admin_id'] : $member_no,
            $is_admin ? 'Admin' : 'Student',
            "Cancelled reservation ID: $reservation_id",
            $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
        ]);
    } catch (PDOException $e) {
        error_log("Activity log error: " . $e->getMessage());
    }
    
    echo json_encode(['success' => true, 'message' => 'Reservation cancelled successfully']);
}

/**
 * Get my reservations (student)
 */
function getMyReservations($pdo, $member_no) {
    $stmt = $pdo->prepare("
        SELECT 
            br.ReservationID,
            br.CatNo,
            b.Title,
            b.Author1,
            b.ISBN,
            br.RequestDate,
            br.ExpiryDate,
            br.Status,
            br.NotifiedAt,
            DATEDIFF(NOW(), br.RequestDate) AS DaysWaiting,
            (SELECT COUNT(*) 
             FROM BookReservations br2 
             WHERE br2.CatNo = br.CatNo 
             AND br2.Status = 'Pending' 
             AND br2.RequestDate < br.RequestDate) + 1 AS QueuePosition
        FROM BookReservations br
        INNER JOIN Books b ON br.CatNo = b.CatNo
        WHERE br.MemberNo = ?
        ORDER BY 
            CASE br.Status
                WHEN 'Ready' THEN 1
                WHEN 'Pending' THEN 2
                WHEN 'Completed' THEN 3
                ELSE 4
            END,
            br.RequestDate DESC
    ");
    $stmt->execute([$member_no]);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $reservations]);
}

/**
 * List all reservations (admin)
 */
function listAllReservations($pdo, $is_admin) {
    if (!$is_admin) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }
    
    $status = $_GET['status'] ?? '';
    
    $where = $status ? "WHERE Status = ?" : "";
    $params = $status ? [$status] : [];
    
    $stmt = $pdo->prepare("
        SELECT * FROM ReservationQueue
        $where
        ORDER BY 
            CASE Status
                WHEN 'Ready' THEN 1
                WHEN 'Pending' THEN 2
                ELSE 3
            END,
            RequestDate ASC
        LIMIT 100
    ");
    $stmt->execute($params);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $reservations]);
}

/**
 * Get reservation statistics
 */
function getReservationStats($pdo, $is_admin) {
    if (!$is_admin) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }
    
    $stats = [];
    
    // Total reservations by status
    $stmt = $pdo->query("
        SELECT 
            Status,
            COUNT(*) as count
        FROM BookReservations
        GROUP BY Status
    ");
    $by_status = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($by_status as $row) {
        $stats[strtolower($row['Status']) . '_count'] = (int)$row['count'];
    }
    
    // Average wait time
    $wait_stmt = $pdo->query("
        SELECT AVG(DATEDIFF(FulfilledAt, RequestDate)) as avg_wait
        FROM BookReservations
        WHERE Status = 'Completed' AND FulfilledAt IS NOT NULL
    ");
    $wait_result = $wait_stmt->fetch(PDO::FETCH_ASSOC);
    $stats['avg_wait_days'] = round($wait_result['avg_wait'] ?? 0, 1);
    
    // Most reserved books
    $popular_stmt = $pdo->query("
        SELECT 
            b.Title,
            b.Author1,
            COUNT(*) as reservation_count
        FROM BookReservations br
        INNER JOIN Books b ON br.CatNo = b.CatNo
        WHERE br.Status IN ('Pending', 'Ready')
        GROUP BY br.CatNo, b.Title, b.Author1
        ORDER BY reservation_count DESC
        LIMIT 5
    ");
    $stats['most_reserved'] = $popular_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $stats]);
}

/**
 * Fulfill reservation (mark as completed when book is issued)
 */
function fulfillReservation($pdo, $is_admin) {
    if (!$is_admin) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }
    
    $reservation_id = $_POST['reservation_id'] ?? null;
    
    if (!$reservation_id) {
        echo json_encode(['success' => false, 'message' => 'Missing reservation ID']);
        return;
    }
    
    $stmt = $pdo->prepare("
        UPDATE BookReservations
        SET Status = 'Completed',
            FulfilledAt = NOW()
        WHERE ReservationID = ? AND Status = 'Ready'
    ");
    $stmt->execute([$reservation_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Reservation fulfilled']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Reservation not found or not ready']);
    }
}

/**
 * Check if member can reserve more books
 */
function checkEligibility($pdo, $member_no) {
    $cat_no = $_GET['cat_no'] ?? null;
    
    if (!$cat_no || !$member_no) {
        echo json_encode(['success' => false, 'can_reserve' => false, 'reason' => 'Missing parameters']);
        return;
    }
    
    // Check available copies
    $available_stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM Holding
        WHERE CatNo = ? AND Status = 'Available'
    ");
    $available_stmt->execute([$cat_no]);
    $available = $available_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($available > 0) {
        echo json_encode([
            'success' => true,
            'can_reserve' => false,
            'reason' => 'Book is currently available for borrowing'
        ]);
        return;
    }
    
    // Check existing reservation
    $existing_stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM BookReservations
        WHERE MemberNo = ? AND CatNo = ? AND Status IN ('Pending', 'Ready')
    ");
    $existing_stmt->execute([$member_no, $cat_no]);
    $existing = $existing_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($existing > 0) {
        echo json_encode([
            'success' => true,
            'can_reserve' => false,
            'reason' => 'You already have an active reservation for this book'
        ]);
        return;
    }
    
    // Check reservation limit
    $count_stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM BookReservations
        WHERE MemberNo = ? AND Status IN ('Pending', 'Ready')
    ");
    $count_stmt->execute([$member_no]);
    $active_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($active_count >= 3) {
        echo json_encode([
            'success' => true,
            'can_reserve' => false,
            'reason' => 'Maximum 3 active reservations allowed'
        ]);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'can_reserve' => true,
        'message' => 'You can reserve this book'
    ]);
}
?>
