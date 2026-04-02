<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

mobile_require_method('GET');

$circulationId = (int)($_GET['circulation_id'] ?? 0);
$accNo = trim((string)($_GET['acc_no'] ?? ''));

if ($circulationId <= 0 && $accNo === '') {
    mobile_error('circulation_id or acc_no is required.', 422);
}

try {
    $student = mobile_require_auth($pdo);
    $memberNo = (int)$student['MemberNo'];

    $where = ['c.MemberNo = :member_no', 'c.Status = \'Active\''];
    $params = ['member_no' => $memberNo];

    if ($circulationId > 0) {
        $where[] = 'c.CirculationID = :circulation_id';
        $params['circulation_id'] = $circulationId;
    }

    if ($accNo !== '') {
        $where[] = 'c.AccNo = :acc_no';
        $params['acc_no'] = $accNo;
    }

    $sql = "SELECT
                c.CirculationID,
                c.AccNo,
                c.IssueDate,
                c.DueDate,
                c.RenewalCount,
                DATEDIFF(c.DueDate, CURDATE()) AS days_left,
                b.CatNo,
                b.Title,
                b.Author1,
                b.ISBN,
                b.Publisher,
                b.Edition,
                b.Subject,
                h.Location,
                m.FinePerDay
            FROM Circulation c
            INNER JOIN Holding h ON h.AccNo = c.AccNo
            INNER JOIN Books b ON b.CatNo = h.CatNo
            INNER JOIN Member m ON m.MemberNo = c.MemberNo
            WHERE " . implode(' AND ', $where) . "
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        mobile_error('Book details not found.', 404);
    }

    $daysLeft = (int)$book['days_left'];
    $fine = $daysLeft < 0 ? abs($daysLeft) * (float)($book['FinePerDay'] ?? 2.0) : 0;

    $historyStmt = $pdo->prepare(
        "SELECT c.IssueDate, r.ReturnDate, COALESCE(r.FineAmount, 0) AS FineAmount
         FROM `Return` r
         INNER JOIN Circulation c ON c.CirculationID = r.CirculationID
         WHERE c.MemberNo = :member_no
         AND c.AccNo = :acc_no
         ORDER BY r.ReturnDate DESC
         LIMIT 5"
    );
    $historyStmt->execute([
        'member_no' => $memberNo,
        'acc_no' => $book['AccNo'],
    ]);

    mobile_ok([
        'book' => [
            'circulation_id' => (int)$book['CirculationID'],
            'acc_no' => $book['AccNo'],
            'cat_no' => (int)$book['CatNo'],
            'title' => $book['Title'],
            'author' => $book['Author1'] ?? 'Unknown',
            'isbn' => $book['ISBN'] ?: 'N/A',
            'publisher' => $book['Publisher'] ?? 'N/A',
            'edition' => $book['Edition'] ?? 'N/A',
            'subject' => $book['Subject'] ?? 'General',
            'location' => $book['Location'] ?? 'Library',
            'issue_date' => $book['IssueDate'],
            'due_date' => $book['DueDate'],
            'days_left' => $daysLeft,
            'renewal_count' => (int)($book['RenewalCount'] ?? 0),
            'renewable' => ((int)($book['RenewalCount'] ?? 0)) < 2,
            'fine' => round($fine, 2),
            'return_history' => $historyStmt->fetchAll(PDO::FETCH_ASSOC),
        ],
    ]);
} catch (Throwable $e) {
    error_log('Mobile book details error: ' . $e->getMessage());
    mobile_error('Unable to load book details.', 500);
}
