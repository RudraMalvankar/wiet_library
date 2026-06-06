<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

mobile_require_method('GET');

try {
    $student = mobile_require_auth($pdo);
    $memberNo = (int)$student['MemberNo'];
    $branch = (string)($student['Branch'] ?? '');

    $statsStmt = $pdo->prepare(
        "SELECT
            COUNT(DISTINCT c.CirculationID) AS books_issued,
            COUNT(DISTINCT CASE
                WHEN DATEDIFF(c.DueDate, CURDATE()) BETWEEN 0 AND 7 THEN c.CirculationID
                ELSE NULL
            END) AS books_due
         FROM Circulation c
         WHERE c.MemberNo = :member_no
         AND c.Status = 'Active'"
    );
    $statsStmt->execute(['member_no' => $memberNo]);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $fineStmt = $pdo->prepare(
        "SELECT
            c.CirculationID,
            CASE
                WHEN r.CirculationID IS NOT NULL THEN r.FineAmount
                ELSE GREATEST(DATEDIFF(CURDATE(), c.DueDate), 0) * COALESCE(m.FinePerDay, 2)
            END AS CalculatedFine,
            COALESCE(SUM(fp.PaidAmount), 0) AS PaidAmount
         FROM Circulation c
         INNER JOIN Member m ON c.MemberNo = m.MemberNo
         LEFT JOIN `Return` r ON c.CirculationID = r.CirculationID
         LEFT JOIN FinePayments fp ON c.CirculationID = fp.CirculationID
         WHERE c.MemberNo = :member_no
         AND (
            (r.CirculationID IS NOT NULL AND r.FineAmount > 0)
            OR (r.CirculationID IS NULL AND c.DueDate < CURDATE())
         )
         GROUP BY c.CirculationID, m.FinePerDay, r.CirculationID, r.FineAmount
         HAVING CalculatedFine > PaidAmount"
    );
    $fineStmt->execute(['member_no' => $memberNo]);
    $fineRows = $fineStmt->fetchAll(PDO::FETCH_ASSOC);
    $pendingFines = 0.0;
    foreach ($fineRows as $row) {
        $pendingFines += ((float)$row['CalculatedFine'] - (float)$row['PaidAmount']);
    }

    $recStmt = $pdo->prepare(
        "SELECT COUNT(DISTINCT b.CatNo) AS recommendations
         FROM Books b
         INNER JOIN Holding h ON h.CatNo = b.CatNo
         WHERE h.Status = 'Available'
         AND b.Subject LIKE CONCAT('%', :branch, '%')"
    );
    $recStmt->execute(['branch' => $branch]);
    $recData = $recStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $dueStmt = $pdo->prepare(
        "SELECT
            c.CirculationID,
            c.AccNo,
            c.DueDate,
            DATEDIFF(c.DueDate, CURDATE()) AS days_left,
            b.Title,
            b.Author1
         FROM Circulation c
         INNER JOIN Holding h ON h.AccNo = c.AccNo
         INNER JOIN Books b ON b.CatNo = h.CatNo
         WHERE c.MemberNo = :member_no
         AND c.Status = 'Active'
         AND DATEDIFF(c.DueDate, CURDATE()) BETWEEN 0 AND 7
         ORDER BY c.DueDate ASC
         LIMIT 5"
    );
    $dueStmt->execute(['member_no' => $memberNo]);
    $upcomingDue = $dueStmt->fetchAll(PDO::FETCH_ASSOC);

    $activityStmt = $pdo->prepare(
        "SELECT Action, Details, Timestamp
         FROM ActivityLog
         WHERE UserID = :student_id
         AND UserType = 'Student'
         ORDER BY Timestamp DESC
         LIMIT 10"
    );
    $activityStmt->execute(['student_id' => $student['StudentID']]);
    $recentActivity = $activityStmt->fetchAll(PDO::FETCH_ASSOC);

    $notifStmt = $pdo->prepare(
        "SELECT COUNT(*) AS unread_admin
         FROM Notifications
         WHERE (MemberNo = :member_no OR MemberNo IS NULL)
         AND IsRead = 0"
    );
    $notifStmt->execute(['member_no' => $memberNo]);
    $unreadAdmin = (int)(($notifStmt->fetch(PDO::FETCH_ASSOC) ?: [])['unread_admin'] ?? 0);

    $overdueStmt = $pdo->prepare(
        "SELECT COUNT(*) AS overdue_count
         FROM Circulation
         WHERE MemberNo = :member_no
         AND Status = 'Active'
         AND DueDate < CURDATE()"
    );
    $overdueStmt->execute(['member_no' => $memberNo]);
    $overdueCount = (int)(($overdueStmt->fetch(PDO::FETCH_ASSOC) ?: [])['overdue_count'] ?? 0);

    mobile_ok([
        'quick_stats' => [
            'books_issued' => (int)($stats['books_issued'] ?? 0),
            'books_due' => (int)($stats['books_due'] ?? 0),
            'pending_fines' => (float)$pendingFines,
            'recommendations' => min((int)($recData['recommendations'] ?? 0), 99),
        ],
        'upcoming_due' => $upcomingDue,
        'recent_activity' => $recentActivity,
        'notifications_summary' => [
            'unread_admin' => $unreadAdmin,
            'overdue_books' => $overdueCount,
            'total_attention' => $unreadAdmin + $overdueCount,
        ],
    ]);
} catch (Throwable $e) {
    error_log('Mobile dashboard error: ' . $e->getMessage());
    mobile_error('Unable to load dashboard data.', 500);
}
