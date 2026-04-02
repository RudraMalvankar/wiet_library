<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

mobile_require_method('GET');

try {
    mobile_require_auth($pdo);

    $query = trim((string)($_GET['q'] ?? ''));
    $category = trim((string)($_GET['category'] ?? ''));
    $limit = max(1, min((int)($_GET['limit'] ?? 20), 100));

    $categoryStmt = $pdo->query("SELECT DISTINCT Subject FROM Books WHERE Subject IS NOT NULL AND Subject != '' ORDER BY Subject ASC");
    $categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);

    $featuredStmt = $pdo->query(
        "SELECT
            b.CatNo,
            b.Title,
            b.Author1,
            b.ISBN,
            b.Subject,
            COUNT(h.AccNo) AS total_copies,
            SUM(CASE WHEN h.Status = 'Available' THEN 1 ELSE 0 END) AS copies_available
         FROM Books b
         LEFT JOIN Holding h ON h.CatNo = b.CatNo
         GROUP BY b.CatNo
         HAVING copies_available > 0
         ORDER BY b.CatNo DESC
         LIMIT 8"
    );

    $featured = $featuredStmt->fetchAll(PDO::FETCH_ASSOC);

    $where = [];
    $params = [];
    if ($query !== '') {
        $where[] = '(b.Title LIKE :term_title OR b.Author1 LIKE :term_author OR b.ISBN LIKE :term_isbn OR b.Subject LIKE :term_subject)';
        $term = '%' . $query . '%';
        $params['term_title'] = $term;
        $params['term_author'] = $term;
        $params['term_isbn'] = $term;
        $params['term_subject'] = $term;
    }

    if ($category !== '') {
        $where[] = 'b.Subject = :category';
        $params['category'] = $category;
    }

    $searchSql =
        "SELECT
            b.CatNo,
            b.Title,
            b.Author1,
            b.ISBN,
            b.Subject,
            b.Publisher,
            COUNT(h.AccNo) AS total_copies,
            SUM(CASE WHEN h.Status = 'Available' THEN 1 ELSE 0 END) AS copies_available
         FROM Books b
         LEFT JOIN Holding h ON h.CatNo = b.CatNo";

    if ($where) {
        $searchSql .= ' WHERE ' . implode(' AND ', $where);
    }

    $searchSql .= ' GROUP BY b.CatNo ORDER BY b.Title ASC LIMIT :limit';

    $searchStmt = $pdo->prepare($searchSql);
    foreach ($params as $key => $value) {
        $searchStmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
    }
    $searchStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $searchStmt->execute();

    mobile_ok([
        'query' => $query,
        'category' => $category,
        'categories' => $categories,
        'featured_books' => $featured,
        'results' => $searchStmt->fetchAll(PDO::FETCH_ASSOC),
    ]);
} catch (Throwable $e) {
    error_log('Mobile search error: ' . $e->getMessage());
    mobile_error('Unable to search books.', 500);
}
