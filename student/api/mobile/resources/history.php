<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

mobile_require_method('GET');

try {
    $student = mobile_require_auth($pdo);
    $memberNo = (int)$student['MemberNo'];

    $statusFilter = strtolower(trim((string)($_GET['status'] ?? 'all')));

    $stmt = $pdo->prepare(
        "SELECT
            c.CirculationID,
            c.AccNo,
            c.IssueDate,
            c.DueDate,
            c.RenewalCount,
            r.ReturnDate,
            COALESCE(r.FineAmount, 0) AS FineAmount,
            b.Title,
            b.Author1,
            CASE
                WHEN r.ReturnID IS NULL THEN 'Issued'
                WHEN r.ReturnDate > c.DueDate THEN 'Returned Late'
                ELSE 'Returned'
            END AS Status
         FROM Circulation c
         INNER JOIN Holding h ON h.AccNo = c.AccNo
         INNER JOIN Books b ON b.CatNo = h.CatNo
         LEFT JOIN `Return` r ON r.CirculationID = c.CirculationID
         WHERE c.MemberNo = :member_no
         ORDER BY c.IssueDate DESC"
    );
    $stmt->execute(['member_no' => $memberNo]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $history = [];
    foreach ($rows as $row) {
        $item = [
            'circulation_id' => (int)$row['CirculationID'],
            'transaction_id' => 'BOR' . str_pad((string)$row['CirculationID'], 5, '0', STR_PAD_LEFT),
            'acc_no' => $row['AccNo'],
            'title' => $row['Title'],
            'author' => $row['Author1'] ?? 'Unknown',
            'issue_date' => $row['IssueDate'],
            'due_date' => $row['DueDate'],
            'return_date' => $row['ReturnDate'],
            'status' => $row['Status'],
            'fine' => (float)($row['FineAmount'] ?? 0),
            'renewal_count' => (int)($row['RenewalCount'] ?? 0),
        ];

        if ($statusFilter !== 'all') {
            $normalized = strtolower(str_replace(' ', '_', $item['status']));
            if ($normalized !== $statusFilter) {
                continue;
            }
        }

        $history[] = $item;
    }

    $stats = [
        'total_borrowed' => count($history),
        'currently_issued' => count(array_filter($history, static fn($h) => $h['status'] === 'Issued')),
        'total_fines_paid' => array_sum(array_map(static fn($h) => (float)$h['fine'], $history)),
        'books_renewed' => array_sum(array_map(static fn($h) => (int)$h['renewal_count'], $history)),
    ];

    mobile_ok([
        'history' => $history,
        'stats' => $stats,
    ]);
} catch (Throwable $e) {
    error_log('Mobile history error: ' . $e->getMessage());
    mobile_error('Unable to load borrowing history.', 500);
}
