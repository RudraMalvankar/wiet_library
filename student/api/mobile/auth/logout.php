<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

mobile_require_method('POST');

$token = mobile_get_bearer_token();
if (!$token) {
    mobile_error('Missing authorization token', 401);
}

try {
    mobile_ensure_sessions_table($pdo);

    $stmt = $pdo->prepare('UPDATE MobileSessions SET IsActive = 0 WHERE Token = :token');
    $stmt->execute(['token' => $token]);

    mobile_ok(['message' => 'Logged out successfully']);
} catch (Throwable $e) {
    error_log('Mobile logout error: ' . $e->getMessage());
    mobile_error('Unable to logout right now.', 500);
}
