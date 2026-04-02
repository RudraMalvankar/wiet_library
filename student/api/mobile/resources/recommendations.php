<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

mobile_require_method('GET');

try {
    $student = mobile_require_auth($pdo);
    $memberNo = (int)$student['MemberNo'];
    $branch = (string)($student['Branch'] ?? '');

    $recommendations = [];

    $branchStmt = $pdo->prepare(
        "SELECT
            b.CatNo,
            b.Title,
            b.Author1,
            b.ISBN,
            b.Subject,
            b.Publisher,
            COUNT(h.AccNo) AS total_copies,
            SUM(CASE WHEN h.Status = 'Available' THEN 1 ELSE 0 END) AS copies_available,
            (
                SELECT COUNT(*)
                FROM Circulation c2
                INNER JOIN Holding h2 ON h2.AccNo = c2.AccNo
                WHERE h2.CatNo = b.CatNo
            ) AS popularity_score
         FROM Books b
         INNER JOIN Holding h ON h.CatNo = b.CatNo
         WHERE b.Subject LIKE CONCAT('%', :branch, '%')
         AND b.CatNo NOT IN (
            SELECT h3.CatNo
            FROM Circulation c3
            INNER JOIN Holding h3 ON h3.AccNo = c3.AccNo
            WHERE c3.MemberNo = :member_no
         )
         GROUP BY b.CatNo
         HAVING copies_available > 0
         ORDER BY popularity_score DESC, b.CatNo DESC
         LIMIT 20"
    );
    $branchStmt->execute([
        'branch' => $branch,
        'member_no' => $memberNo,
    ]);

    foreach ($branchStmt->fetchAll(PDO::FETCH_ASSOC) as $book) {
        $recommendations[] = [
            'cat_no' => (int)$book['CatNo'],
            'title' => $book['Title'],
            'author' => $book['Author1'] ?? 'Unknown',
            'isbn' => $book['ISBN'] ?: 'N/A',
            'category' => $book['Subject'] ?: 'General',
            'publisher' => $book['Publisher'] ?? 'N/A',
            'copies_available' => (int)$book['copies_available'],
            'total_copies' => (int)$book['total_copies'],
            'reason' => 'Recommended for ' . ($branch ?: 'your branch') . ' students',
            'recommendation_score' => (int)($book['popularity_score'] ?? 0),
        ];
    }

    if (count($recommendations) < 10) {
        $remaining = 10 - count($recommendations);
        $popularStmt = $pdo->prepare(
            "SELECT
                b.CatNo,
                b.Title,
                b.Author1,
                b.ISBN,
                b.Subject,
                b.Publisher,
                COUNT(h.AccNo) AS total_copies,
                SUM(CASE WHEN h.Status = 'Available' THEN 1 ELSE 0 END) AS copies_available,
                (
                    SELECT COUNT(*)
                    FROM Circulation c2
                    INNER JOIN Holding h2 ON h2.AccNo = c2.AccNo
                    WHERE h2.CatNo = b.CatNo
                ) AS popularity_score
             FROM Books b
             INNER JOIN Holding h ON h.CatNo = b.CatNo
             WHERE b.CatNo NOT IN (
                SELECT h3.CatNo
                FROM Circulation c3
                INNER JOIN Holding h3 ON h3.AccNo = c3.AccNo
                WHERE c3.MemberNo = :member_no
             )
             GROUP BY b.CatNo
             HAVING copies_available > 0
             ORDER BY popularity_score DESC
             LIMIT :remaining"
        );
        $popularStmt->bindValue(':member_no', $memberNo, PDO::PARAM_INT);
        $popularStmt->bindValue(':remaining', $remaining, PDO::PARAM_INT);
        $popularStmt->execute();

        foreach ($popularStmt->fetchAll(PDO::FETCH_ASSOC) as $book) {
            $recommendations[] = [
                'cat_no' => (int)$book['CatNo'],
                'title' => $book['Title'],
                'author' => $book['Author1'] ?? 'Unknown',
                'isbn' => $book['ISBN'] ?: 'N/A',
                'category' => $book['Subject'] ?: 'General',
                'publisher' => $book['Publisher'] ?? 'N/A',
                'copies_available' => (int)$book['copies_available'],
                'total_copies' => (int)$book['total_copies'],
                'reason' => 'Popular among students',
                'recommendation_score' => (int)($book['popularity_score'] ?? 0),
            ];
        }
    }

    mobile_ok([
        'recommendations' => $recommendations,
        'stats' => [
            'count' => count($recommendations),
            'categories_covered' => count(array_unique(array_map(static fn($r) => $r['category'], $recommendations))),
        ],
    ]);
} catch (Throwable $e) {
    error_log('Mobile recommendations error: ' . $e->getMessage());
    mobile_error('Unable to load recommendations.', 500);
}
