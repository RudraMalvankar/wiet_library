<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'success' => true,
    'service' => 'WIET Library Student Mobile API',
    'version' => '1.0.0',
    'auth' => [
        'POST /student/api/mobile/auth/login.php',
        'GET /student/api/mobile/auth/me.php',
        'POST /student/api/mobile/auth/logout.php',
        'POST /student/api/mobile/auth/forgot-password.php',
        'POST /student/api/mobile/auth/verify-otp.php',
    ],
    'resources' => [
        'GET /student/api/mobile/resources/dashboard.php',
        'GET /student/api/mobile/resources/books.php',
        'GET /student/api/mobile/resources/book-details.php?circulation_id=..',
        'GET /student/api/mobile/resources/history.php',
        'GET /student/api/mobile/resources/search.php?q=..',
        'GET /student/api/mobile/resources/recommendations.php',
        'GET /student/api/mobile/resources/profile.php',
        'GET /student/api/mobile/resources/digital-id.php',
        'GET /student/api/mobile/resources/notifications.php',
        'POST /student/api/mobile/resources/notifications-read.php',
        'GET /student/api/mobile/resources/events.php',
        'GET|POST /student/api/mobile/resources/footfall.php',
        'GET /student/api/mobile/resources/e-resources.php',
    ],
], JSON_UNESCAPED_UNICODE);
