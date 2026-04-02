<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

mobile_require_method('GET');

try {
    $student = mobile_require_auth($pdo);
    $memberNo = (int)$student['MemberNo'];
    $studentId = (int)$student['StudentID'];

    $notifications = [];

    $adminStmt = $pdo->prepare(
        "SELECT NotificationID, Title, Message, Type, IsRead, CreatedDate
         FROM Notifications
         WHERE (MemberNo = :member_no OR MemberNo IS NULL)
         ORDER BY CreatedDate DESC
         LIMIT 50"
    );
    $adminStmt->execute(['member_no' => $memberNo]);
    foreach ($adminStmt->fetchAll(PDO::FETCH_ASSOC) as $n) {
        $notifications[] = [
            'id' => 'admin_' . $n['NotificationID'],
            'notification_id' => (int)$n['NotificationID'],
            'title' => $n['Title'],
            'message' => $n['Message'],
            'type' => $n['Type'] ?: 'info',
            'category' => 'admin',
            'date' => $n['CreatedDate'],
            'read' => (bool)$n['IsRead'],
            'action_required' => false,
        ];
    }

    $overdueStmt = $pdo->prepare(
        "SELECT c.CirculationID, b.Title, DATEDIFF(CURDATE(), c.DueDate) AS days_overdue
         FROM Circulation c
         INNER JOIN Holding h ON h.AccNo = c.AccNo
         INNER JOIN Books b ON b.CatNo = h.CatNo
         WHERE c.MemberNo = :member_no
         AND c.Status = 'Active'
         AND c.DueDate < CURDATE()"
    );
    $overdueStmt->execute(['member_no' => $memberNo]);
    foreach ($overdueStmt->fetchAll(PDO::FETCH_ASSOC) as $book) {
        $notifications[] = [
            'id' => 'overdue_' . $book['CirculationID'],
            'notification_id' => null,
            'title' => 'Book Overdue',
            'message' => '"' . $book['Title'] . '" is overdue by ' . $book['days_overdue'] . ' day(s).',
            'type' => 'warning',
            'category' => 'overdue',
            'date' => date('Y-m-d H:i:s'),
            'read' => false,
            'action_required' => true,
        ];
    }

    $dueSoonStmt = $pdo->prepare(
        "SELECT c.CirculationID, b.Title, c.DueDate, DATEDIFF(c.DueDate, CURDATE()) AS days_left
         FROM Circulation c
         INNER JOIN Holding h ON h.AccNo = c.AccNo
         INNER JOIN Books b ON b.CatNo = h.CatNo
         WHERE c.MemberNo = :member_no
         AND c.Status = 'Active'
         AND c.DueDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)"
    );
    $dueSoonStmt->execute(['member_no' => $memberNo]);
    foreach ($dueSoonStmt->fetchAll(PDO::FETCH_ASSOC) as $book) {
        $notifications[] = [
            'id' => 'due_' . $book['CirculationID'],
            'notification_id' => null,
            'title' => 'Book Due Soon',
            'message' => '"' . $book['Title'] . '" is due in ' . $book['days_left'] . ' day(s).',
            'type' => 'info',
            'category' => 'due',
            'date' => date('Y-m-d H:i:s'),
            'read' => false,
            'action_required' => true,
        ];
    }

    $activityStmt = $pdo->prepare(
        "SELECT Action, Details, Timestamp
         FROM ActivityLog
         WHERE UserID = :student_id
         AND UserType = 'Student'
         ORDER BY Timestamp DESC
         LIMIT 5"
    );
    $activityStmt->execute(['student_id' => $studentId]);
    foreach ($activityStmt->fetchAll(PDO::FETCH_ASSOC) as $a) {
        $notifications[] = [
            'id' => 'activity_' . md5((string)$a['Timestamp'] . (string)$a['Action']),
            'notification_id' => null,
            'title' => $a['Action'],
            'message' => $a['Details'] ?: $a['Action'],
            'type' => 'info',
            'category' => 'activity',
            'date' => $a['Timestamp'],
            'read' => true,
            'action_required' => false,
        ];
    }

    usort($notifications, static function (array $a, array $b): int {
        return strtotime((string)$b['date']) <=> strtotime((string)$a['date']);
    });

    $unread = count(array_filter($notifications, static fn($n) => empty($n['read'])));
    $actionRequired = count(array_filter($notifications, static fn($n) => !empty($n['action_required'])));

    mobile_ok([
        'notifications' => $notifications,
        'stats' => [
            'total' => count($notifications),
            'unread' => $unread,
            'action_required' => $actionRequired,
        ],
    ]);
} catch (Throwable $e) {
    error_log('Mobile notifications error: ' . $e->getMessage());
    mobile_error('Unable to load notifications.', 500);
}
