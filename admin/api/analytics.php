<?php
/**
 * Admin Analytics API
 * Provides analytics payload used by admin dashboards/reports.
 */

require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';

session_start();
header('Content-Type: application/json');

try {
    // Rate limiting for endpoint protection
    $identifier = $_SESSION['admin_id'] ?? $_SESSION['AdminID'] ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    if (!checkRateLimit($identifier, 120, 60)) {
        sendJson(['success' => false, 'message' => 'Rate limit exceeded. Please try again later.'], 429);
    }

    // Core dashboard metrics
    $stats = getDashboardStats($pdo);

    // Footfall trend for last 7 days
    $stmt = $pdo->prepare("\n        SELECT Date, COUNT(DISTINCT MemberNo) AS count\n        FROM Footfall\n        WHERE Date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)\n        GROUP BY Date\n        ORDER BY Date ASC\n    ");
    $stmt->execute();
    $footfallTrend = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Circulation trend for last 7 days
    $stmt = $pdo->prepare("\n        SELECT DATE(IssueDate) AS date_value, COUNT(*) AS count\n        FROM Circulation\n        WHERE IssueDate >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)\n        GROUP BY DATE(IssueDate)\n        ORDER BY date_value ASC\n    ");
    $stmt->execute();
    $circulationTrend = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Top issued books
    $stmt = $pdo->prepare("\n        SELECT b.Title, COUNT(c.CirculationID) AS issue_count\n        FROM Circulation c\n        JOIN Holding h ON c.AccNo = h.AccNo\n        JOIN Books b ON h.CatNo = b.CatNo\n        GROUP BY b.CatNo, b.Title\n        ORDER BY issue_count DESC\n        LIMIT 5\n    ");
    $stmt->execute();
    $topBooks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Unified analytics response
    sendJson([
        'success' => true,
        'message' => 'Analytics fetched successfully',
        'data' => [
            'summary' => [
                'total_books' => (int)($stats['totalBooks'] ?? 0),
                'total_copies' => (int)($stats['totalCopies'] ?? 0),
                'available_books' => (int)($stats['availableBooks'] ?? 0),
                'active_members' => (int)($stats['activeMembers'] ?? 0),
                'books_issued' => (int)($stats['booksIssued'] ?? 0),
                'books_overdue' => (int)($stats['overdueBooks'] ?? 0),
                'footfall_today' => (int)($stats['todayFootfall'] ?? 0)
            ],
            'footfall_trend' => $footfallTrend,
            'circulation_trend' => $circulationTrend,
            'top_books' => $topBooks
        ]
    ]);
} catch (Exception $e) {
    sendJson([
        'success' => false,
        'message' => 'Failed to fetch analytics',
        'error' => $e->getMessage()
    ], 500);
}
