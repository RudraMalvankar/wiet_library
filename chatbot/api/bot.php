<?php
/**
 * Chatbot Backend API (non-AI)
 * Provides student-specific information from the library database.
 * URL: /chatbot/api/bot.php?action=<action>
 * Actions: my_loans, due_books, visit_count, search_books, book_info, history_summary
 */

header('Content-Type: application/json');

// Start session & ensure student is logged in
require_once __DIR__ . '/../../student/student_session_check.php'; // defines $member_no and $student_id
require_once __DIR__ . '/../../includes/db_connect.php';

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

try {
    if (!$member_no && !$student_id) {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }

    switch ($action) {
        case 'my_loans':
            // Current active loans for logged-in student
            $stmt = $pdo->prepare("SELECT c.AccNo, b.Title, b.Author1, c.IssueDate, c.DueDate, 
                GREATEST(0, DATEDIFF(CURDATE(), c.DueDate)) AS DaysOverdue
                FROM circulation c
                JOIN holding h ON c.AccNo = h.AccNo
                JOIN books b ON h.CatNo = b.CatNo
                WHERE c.MemberNo = :member_no AND c.Status = 'Active'
                ORDER BY c.DueDate ASC");
            $stmt->execute(['member_no' => $member_no]);
            $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $loans]);
            break;

        case 'due_books':
            // Books due soon or overdue (next 7 days and overdue)
            $stmt = $pdo->prepare("SELECT c.AccNo, b.Title, c.IssueDate, c.DueDate, 
                IF(CURDATE()>c.DueDate, 'overdue', IF(DATEDIFF(c.DueDate, CURDATE())<=7, 'due_soon', 'ok')) as status, 
                GREATEST(0, DATEDIFF(CURDATE(), c.DueDate)) AS DaysOverdue
                FROM circulation c
                JOIN holding h ON c.AccNo = h.AccNo
                JOIN books b ON h.CatNo = b.CatNo
                WHERE c.MemberNo = :member_no AND c.Status = 'Active'
                ORDER BY c.DueDate ASC");
            $stmt->execute(['member_no' => $member_no]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $items]);
            break;

        case 'visit_count':
            // Total visits and visits in last 30 days
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM footfall WHERE MemberNo = :member_no");
            $stmt->execute(['member_no' => $member_no]);
            $total = (int)$stmt->fetchColumn();

            $stmt = $pdo->prepare("SELECT COUNT(*) as last_30_days FROM footfall WHERE MemberNo = :member_no AND EntryTime >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $stmt->execute(['member_no' => $member_no]);
            $last30 = (int)$stmt->fetchColumn();

            echo json_encode(['success' => true, 'data' => ['total' => $total, 'last_30_days' => $last30]]);
            break;

        case 'search_books':
            $q = trim($_GET['q'] ?? ($_POST['q'] ?? ''));
            if ($q === '') {
                echo json_encode(['success' => false, 'message' => 'Query is empty']);
                exit;
            }
            $like = '%' . $q . '%';
            // Use v_available_books view if exists, fallback to join
            $hasView = false;
            try {
                $check = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'VIEW' AND Tables_in_wiet_library = 'v_available_books'");
                $hasView = $check->rowCount() > 0;
            } catch (Exception $e) {
                $hasView = false;
            }

            if ($hasView) {
                $stmt = $pdo->prepare("SELECT CatNo, Title, Author1, Author2, Publisher, Year, TotalCopies, AvailableCopies, IssuedCopies FROM v_available_books WHERE Title LIKE :q OR Author1 LIKE :q OR Author2 LIKE :q OR ISBN LIKE :q LIMIT 40");
                $stmt->execute(['q' => $like]);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $stmt = $pdo->prepare("SELECT b.CatNo, b.Title, b.Author1, b.Author2, b.Publisher, b.Year, COUNT(h.HoldID) AS TotalCopies, SUM(h.Status='Available') AS AvailableCopies FROM books b LEFT JOIN holding h ON b.CatNo = h.CatNo WHERE b.Title LIKE :q OR b.Author1 LIKE :q OR b.Author2 LIKE :q OR b.ISBN LIKE :q GROUP BY b.CatNo LIMIT 40");
                $stmt->execute(['q' => $like]);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            echo json_encode(['success' => true, 'data' => $results]);
            break;

        case 'book_info':
            $catNo = $_GET['catno'] ?? ($_POST['catno'] ?? '');
            if ($catNo === '') { echo json_encode(['success' => false, 'message' => 'catno required']); exit; }
            $stmt = $pdo->prepare("SELECT b.*, COUNT(h.HoldID) AS TotalCopies, SUM(h.Status='Available') AS AvailableCopies FROM books b LEFT JOIN holding h ON b.CatNo = h.CatNo WHERE b.CatNo = :catno GROUP BY b.CatNo LIMIT 1");
            $stmt->execute(['catno' => $catNo]);
            $info = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $info]);
            break;

        case 'history_summary':
            // Total visits, total borrows, last borrowed date
            $stmt = $pdo->prepare("SELECT COUNT(*) as total_visits FROM footfall WHERE MemberNo = :member_no");
            $stmt->execute(['member_no' => $member_no]);
            $visits = (int)$stmt->fetchColumn();

            $stmt = $pdo->prepare("SELECT COUNT(*) as total_borrows FROM circulation WHERE MemberNo = :member_no");
            $stmt->execute(['member_no' => $member_no]);
            $borrows = (int)$stmt->fetchColumn();

            $stmt = $pdo->prepare("SELECT MAX(IssueDate) as last_borrow FROM circulation WHERE MemberNo = :member_no");
            $stmt->execute(['member_no' => $member_no]);
            $lastBorrow = $stmt->fetchColumn();

            echo json_encode(['success' => true, 'data' => ['visits' => $visits, 'borrows' => $borrows, 'last_borrow' => $lastBorrow]]);
            break;

        case 'ask':
            // Simple natural-language-ish parser that maps free-text to actions
            $q = trim($_GET['q'] ?? ($_POST['q'] ?? ''));
            if ($q === '') { echo json_encode(['success' => false, 'message' => 'Query is empty']); exit; }
            $low = strtolower($q);

            // Initialize session context storage
            if (!isset($_SESSION['chatbot_context'])) $_SESSION['chatbot_context'] = [];
            if (!isset($_SESSION['chatbot_last_result'])) $_SESSION['chatbot_last_result'] = null;
            if (!isset($_SESSION['chatbot_last_index'])) $_SESSION['chatbot_last_index'] = 0;

            $mapped = '';
            $response = ['success' => true, 'query' => $q];

            // follow-up detection (e.g., "one after that", "next one")
            $is_follow_up = preg_match('/\b(next|after that|one after that|what about the next|what about the one after that)\b/', $low);
            if ($is_follow_up && $_SESSION['chatbot_last_result'] && is_array($_SESSION['chatbot_last_result']['data'])) {
                $last = $_SESSION['chatbot_last_result'];
                $idx = $_SESSION['chatbot_last_index'] ?? 0;
                $idx++;
                $data = $last['data'];
                if (isset($data[$idx])) {
                    $_SESSION['chatbot_last_index'] = $idx;
                    $item = $data[$idx];
                    $response['action'] = $last['action'];
                    $response['data'] = [$item];
                    $response['reply'] = 'Next item: ' . ($item['Title'] ?? ($item['name'] ?? 'Item')) . ' — ' . (($item['DueDate'] ?? $item['date']) ?? 'N/A');
                    // Save context
                    $_SESSION['chatbot_context'][] = ['time' => time(), 'q' => $q, 'mapped' => 'follow_up'];
                    echo json_encode($response);
                    break;
                } else {
                    $response['reply'] = 'No more items in the previous list.';
                    echo json_encode($response);
                    break;
                }
            }

            // intent mapping rules
            if (preg_match('/\\b(loan|borrow|issued|my loans)\\b/', $low)) {
                $mapped = 'my_loans';
            } elseif (preg_match('/\\b(due|overdue|return|when is my)\\b/', $low)) {
                $mapped = 'due_books';
            } elseif (preg_match('/\\b(visit|visited|how many times|times visited)\\b/', $low)) {
                $mapped = 'visit_count';
            } elseif (preg_match('/\\b(summary|history)\\b/', $low)) {
                $mapped = 'history_summary';
            } elseif (preg_match('/\\b(search|find|title|author|isbn)\\b/', $low) || strlen($low) > 2) {
                $mapped = 'search_books';
            }

            // Save context (query)
            $_SESSION['chatbot_context'][] = ['time' => time(), 'q' => $q, 'mapped' => $mapped];

            // Execute mapped action and provide a friendly reply + data where appropriate
            if ($mapped === 'my_loans') {
                $stmt = $pdo->prepare("SELECT c.AccNo, b.Title, b.Author1, c.IssueDate, c.DueDate, TO_DAYS(CURDATE())-TO_DAYS(c.DueDate) AS DaysOverdue, c.FineAmount
                    FROM circulation c
                    JOIN holding h ON c.AccNo = h.AccNo
                    JOIN books b ON h.CatNo = b.CatNo
                    WHERE c.MemberNo = :member_no AND c.Status = 'Active'");
                $stmt->execute(['member_no' => $member_no]);
                $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response['action'] = 'my_loans';
                $response['data'] = $loans;
                if (count($loans) === 0) $response['reply'] = 'You have no active loans.';
                else $response['reply'] = 'You have ' . count($loans) . ' active loan(s).';
            } elseif ($mapped === 'due_books') {
                $stmt = $pdo->prepare("SELECT c.AccNo, b.Title, c.IssueDate, c.DueDate, IF(CURDATE()>c.DueDate, 'overdue', IF(DATEDIFF(c.DueDate, CURDATE())<=7, 'due_soon', 'ok')) as status, TO_DAYS(CURDATE())-TO_DAYS(c.DueDate) AS DaysOverdue
                    FROM circulation c
                    JOIN holding h ON c.AccNo = h.AccNo
                    JOIN books b ON h.CatNo = b.CatNo
                    WHERE c.MemberNo = :member_no AND c.Status = 'Active'
                    ORDER BY c.DueDate ASC");
                $stmt->execute(['member_no' => $member_no]);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response['action'] = 'due_books';
                $response['data'] = $items;
                if (count($items) === 0) $response['reply'] = 'No books are due or overdue.';
                else {
                    $first = $items[0];
                    $response['reply'] = 'Your next due book is "' . ($first['Title'] ?? 'Untitled') . '" on ' . $first['DueDate'];
                }
            } elseif ($mapped === 'visit_count') {
                $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM footfall WHERE MemberNo = :member_no");
                $stmt->execute(['member_no' => $member_no]);
                $total = (int)$stmt->fetchColumn();
                $stmt = $pdo->prepare("SELECT COUNT(*) as last_30_days FROM footfall WHERE MemberNo = :member_no AND EntryTime >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
                $stmt->execute(['member_no' => $member_no]);
                $last30 = (int)$stmt->fetchColumn();
                $response['action'] = 'visit_count';
                $response['data'] = ['total' => $total, 'last_30_days' => $last30];
                $response['reply'] = "You have visited the library {$total} time(s). {$last30} visits in the last 30 days.";
            } elseif ($mapped === 'history_summary') {
                $stmt = $pdo->prepare("SELECT COUNT(*) as total_visits FROM footfall WHERE MemberNo = :member_no");
                $stmt->execute(['member_no' => $member_no]);
                $visits = (int)$stmt->fetchColumn();
                $stmt = $pdo->prepare("SELECT COUNT(*) as total_borrows FROM circulation WHERE MemberNo = :member_no");
                $stmt->execute(['member_no' => $member_no]);
                $borrows = (int)$stmt->fetchColumn();
                $stmt = $pdo->prepare("SELECT MAX(IssueDate) as last_borrow FROM circulation WHERE MemberNo = :member_no");
                $stmt->execute(['member_no' => $member_no]);
                $lastBorrow = $stmt->fetchColumn();
                $response['action'] = 'history_summary';
                $response['data'] = ['visits' => $visits, 'borrows' => $borrows, 'last_borrow' => $lastBorrow];
                $response['reply'] = "Visits: {$visits}. Borrows: {$borrows}. Last borrow: " . ($lastBorrow ?: 'N/A');
            } else {
                // default to search
                $like = '%' . $q . '%';
                $stmt = $pdo->prepare("SELECT b.CatNo, b.Title, b.Author1, b.Author2, b.Publisher, b.Year, COUNT(h.HoldID) AS TotalCopies, SUM(h.Status='Available') AS AvailableCopies FROM books b LEFT JOIN holding h ON b.CatNo = h.CatNo WHERE b.Title LIKE :q OR b.Author1 LIKE :q OR b.Author2 LIKE :q OR b.ISBN LIKE :q GROUP BY b.CatNo LIMIT 40");
                $stmt->execute(['q' => $like]);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response['action'] = 'search_books';
                $response['data'] = $results;
                $response['reply'] = count($results) . ' result(s) found for "' . $q . '"';
            }

            // Save last result for follow-up handling (reset index)
            $_SESSION['chatbot_last_result'] = ['action' => $response['action'] ?? null, 'data' => $response['data'] ?? null];
            $_SESSION['chatbot_last_index'] = 0;

            echo json_encode($response);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

?>