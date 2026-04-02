<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

mobile_require_method('GET');

try {
    $student = mobile_require_auth($pdo);
    mobile_ok([
        'student' => mobile_build_student_payload($student),
        'session' => [
            'expires_at' => $student['ExpiresAt'],
        ],
    ]);
} catch (Throwable $e) {
    error_log('Mobile auth me error: ' . $e->getMessage());
    mobile_error('Unable to verify session.', 500);
}
