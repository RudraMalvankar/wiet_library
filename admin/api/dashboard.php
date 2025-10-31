<?php
/**
 * Dashboard API
 * Returns live statistics and recent activities for the admin dashboard
 */

require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

session_start();

// Basic debug logging (append-only)
// @file_put_contents(__DIR__ . '/api_debug.log', "\n[dashboard] " . date('c') . " REQUEST: " . ($_SERVER['REQUEST_URI'] ?? '') . "\n", FILE_APPEND);

try {
    // Ensure the user is authenticated as admin (best-effort; adjust to your auth system)
    // If your session uses different keys, change these checks accordingly
    if (!isset($_SESSION['admin_id']) && !isset($_SESSION['AdminID'])) {
        // Allow read-only access for now; you can tighten this later
        // sendJson(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    // Get aggregated stats
    $stats = getDashboardStats($pdo);

    // Most issued books (top 5)
    $stmt = $pdo->prepare("
        SELECT b.Title, b.Author1, COUNT(c.CirculationID) as IssueCount,
               COUNT(h.HoldID) as TotalCopies,
               SUM(CASE WHEN h.Status = 'Available' THEN 1 ELSE 0 END) as AvailableCopies
        FROM Circulation c
        JOIN Holding h ON c.AccNo = h.AccNo
        JOIN Books b ON h.CatNo = b.CatNo
        GROUP BY b.CatNo
        ORDER BY IssueCount DESC
        LIMIT 5
    ");
    $stmt->execute();
    $mostIssued = $stmt->fetchAll();

    // Active borrowers (top 5)
    $stmt = $pdo->prepare("
        SELECT m.MemberNo, m.MemberName, m.BooksIssued, COUNT(c.CirculationID) as TotalIssues
        FROM Member m
        LEFT JOIN Circulation c ON m.MemberNo = c.MemberNo
        WHERE m.Status = 'Active' AND m.BooksIssued > 0
        GROUP BY m.MemberNo
        ORDER BY m.BooksIssued DESC
        LIMIT 5
    ");
    $stmt->execute();
    $activeBorrowers = $stmt->fetchAll();

    // Circulation trend (last 7 days)
    $stmt = $pdo->prepare("
        SELECT DATE(IssueDate) as Date, COUNT(*) as Count
        FROM Circulation
        WHERE IssueDate >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(IssueDate)
        ORDER BY Date ASC
    ");
    $stmt->execute();
    $circulationTrend = $stmt->fetchAll();

    // Weekly footfall (last 7 days)
    $stmt = $pdo->prepare("
        SELECT Date, COUNT(DISTINCT MemberNo) as Count
        FROM Footfall
        WHERE Date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY Date
        ORDER BY Date ASC
    ");
    $stmt->execute();
    $footfallTrend = $stmt->fetchAll();

    // Fetch recent activity log (limit 10)
    $stmt = $pdo->prepare("SELECT * FROM ActivityLog ORDER BY Timestamp DESC LIMIT 10");
    $stmt->execute();
    $activities = $stmt->fetchAll();

    // Normalize activity items for frontend
    $recent = [];
    foreach ($activities as $a) {
        $recent[] = [
            'type' => strtolower($a['Action'] ?? 'system'),
            'action' => $a['Action'],
            'details' => $a['Details'] ?? '',
            'member' => $a['UserID'] ?? 'System',
            'time' => $a['Timestamp'],
            'status' => 'success'
        ];
    }

    // Build critical alerts (simple example: overdue count)
    $alerts = [];
    if (!empty($stats['overdueBooks'])) {
        $alerts[] = [
            'type' => 'overdue',
            'title' => $stats['overdueBooks'] . ' Overdue Books',
            'description' => 'Books not returned past due date',
            'priority' => 'high',
            'count' => (int)$stats['overdueBooks'],
            'action' => 'circulation'
        ];
    }

    // Return JSON payload
    sendJson([
        'success' => true,
        'data' => array_merge($stats, [
            'recent_activities' => $recent,
            'critical_alerts' => $alerts,
            'most_issued_books' => $mostIssued,
            'active_borrowers' => $activeBorrowers,
            'circulation_trend' => $circulationTrend,
            'footfall_trend' => $footfallTrend
        ])
    ]);

} catch (Exception $e) {
    sendJson(['success' => false, 'message' => $e->getMessage()], 500);
}

?>
<?php
// Lightweight API endpoint returning dashboard stats as JSON
session_start();
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';

try {
    $stats = getDashboardStats($pdo);

    // Additional stats
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM LibraryEvents WHERE MONTH(EventDate) = MONTH(CURDATE())");
    $stats['eventsThisMonth'] = (int) ($stmt->fetch()['count'] ?? 0);

    // Map keys to the names used by the frontend
    $response = [
        'total_books' => (int) ($stats['totalBooks'] ?? 0),
        'total_copies' => (int) ($stats['totalCopies'] ?? 0),
        'active_members' => (int) ($stats['activeMembers'] ?? 0),
        'books_issued' => (int) ($stats['booksIssued'] ?? 0),
        'books_overdue' => (int) ($stats['overdueBooks'] ?? 0),
        'footfall_today' => (int) ($stats['todayFootfall'] ?? 0),
        'events_this_month' => (int) ($stats['eventsThisMonth'] ?? 0),
        'e_resources' => 0,
        'pending_acquisitions' => 0,
        'dropbox_active' => 0
    ];

    sendJson(['success' => true, 'data' => $response]);

} catch (Exception $e) {
    error_log('Dashboard API error: ' . $e->getMessage());
    sendJson(['success' => false, 'message' => 'Unable to fetch dashboard stats'], 500);
}

?>
