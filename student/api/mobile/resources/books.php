<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

mobile_require_method('GET');

try {
    $student = mobile_require_auth($pdo);
    $memberNo = (int)$student['MemberNo'];

    $memberStmt = $pdo->prepare('SELECT FinePerDay FROM Member WHERE MemberNo = :member_no LIMIT 1');
    $memberStmt->execute(['member_no' => $memberNo]);
    $memberRow = $memberStmt->fetch(PDO::FETCH_ASSOC) ?: [];
    $finePerDay = (float)($memberRow['FinePerDay'] ?? 2.0);

    $stmt = $pdo->prepare(
        "SELECT
            c.CirculationID,
            c.AccNo,
            c.IssueDate,
            c.DueDate,
            c.RenewalCount,
            DATEDIFF(c.DueDate, CURDATE()) AS days_left,
            b.Title,
            b.Author1,
            b.ISBN
         FROM Circulation c
         INNER JOIN Holding h ON h.AccNo = c.AccNo
         INNER JOIN Books b ON b.CatNo = h.CatNo
         WHERE c.MemberNo = :member_no
         AND c.Status = 'Active'
         ORDER BY c.DueDate ASC"
    );
    $stmt->execute(['member_no' => $memberNo]);

    $books = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $daysLeft = (int)$row['days_left'];
        $fine = $daysLeft < 0 ? abs($daysLeft) * $finePerDay : 0;

        $books[] = [
            'circulation_id' => (int)$row['CirculationID'],
            'acc_no' => $row['AccNo'],
            'title' => $row['Title'],
            'author' => $row['Author1'] ?? 'Unknown',
            'isbn' => $row['ISBN'] ?: 'N/A',
            'issue_date' => $row['IssueDate'],
            'due_date' => $row['DueDate'],
            'days_left' => $daysLeft,
            'renewal_count' => (int)($row['RenewalCount'] ?? 0),
            'renewable' => ((int)($row['RenewalCount'] ?? 0)) < 2,
            'fine' => round($fine, 2),
            'status' => $daysLeft < 0 ? 'Overdue' : ($daysLeft <= 3 ? 'Due Soon' : 'On Time'),
        ];
    }

    mobile_ok([
        'books' => $books,
        'library_rules' => [
            'max_books' => 5,
            'loan_period_days' => 21,
            'renewal_limit' => 2,
            'fine_per_day' => $finePerDay,
        ],
    ]);
} catch (Throwable $e) {
    error_log('Mobile books error: ' . $e->getMessage());
    mobile_error('Unable to load issued books.', 500);
}
