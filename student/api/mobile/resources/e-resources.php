<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

mobile_require_method('GET');

try {
    mobile_require_auth($pdo);

    $databases = [
        [
            'name' => 'IEEE Xplore Digital Library',
            'description' => 'Full-text access to IEEE journals, conference papers, and standards',
            'category' => 'Engineering & Technology',
            'access_type' => 'Full Access',
            'status' => 'active',
            'url' => '',
        ],
        [
            'name' => 'ACM Digital Library',
            'description' => 'Computing and information technology research publications',
            'category' => 'Computer Science',
            'access_type' => 'Full Access',
            'status' => 'active',
            'url' => '',
        ],
        [
            'name' => 'SpringerLink',
            'description' => 'Scientific, technical and medical research content',
            'category' => 'Science & Technology',
            'access_type' => 'Limited Access',
            'status' => 'active',
            'url' => '',
        ],
        [
            'name' => 'ScienceDirect',
            'description' => 'Full-text scientific database for journal articles and book chapters',
            'category' => 'Science & Engineering',
            'access_type' => 'Full Access',
            'status' => 'active',
            'url' => '',
        ],
    ];

    $ebooks = [
        [
            'title' => 'Computer Networks: A Systems Approach',
            'author' => 'Peterson & Davie',
            'category' => 'Computer Science',
            'format' => 'PDF',
            'size' => '15.2 MB',
            'downloads' => 234,
            'url' => '',
        ],
        [
            'title' => 'Digital Signal Processing',
            'author' => 'Proakis & Manolakis',
            'category' => 'Electronics',
            'format' => 'EPUB',
            'size' => '8.7 MB',
            'downloads' => 189,
            'url' => '',
        ],
        [
            'title' => 'Software Engineering: A Practitioner\'s Approach',
            'author' => 'Roger Pressman',
            'category' => 'Software Engineering',
            'format' => 'PDF',
            'size' => '22.1 MB',
            'downloads' => 312,
            'url' => '',
        ],
    ];

    mobile_ok([
        'stats' => [
            'total_resources' => 15000,
            'databases_available' => count($databases),
            'ebooks_accessed' => 45,
            'this_month_downloads' => 18,
        ],
        'databases' => $databases,
        'ebooks' => $ebooks,
    ]);
} catch (Throwable $e) {
    error_log('Mobile e-resources error: ' . $e->getMessage());
    mobile_error('Unable to load e-resources.', 500);
}
