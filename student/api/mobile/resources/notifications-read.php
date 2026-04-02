<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

mobile_require_method('POST');

$input = mobile_read_json_body();
$notificationId = (int)($input['notification_id'] ?? 0);

if ($notificationId <= 0) {
    mobile_error('notification_id is required.', 422);
}

try {
    $student = mobile_require_auth($pdo);
    $memberNo = (int)$student['MemberNo'];

    $stmt = $pdo->prepare(
        "UPDATE Notifications
         SET IsRead = 1
         WHERE NotificationID = :notification_id
         AND (MemberNo = :member_no OR MemberNo IS NULL)"
    );
    $stmt->execute([
        'notification_id' => $notificationId,
        'member_no' => $memberNo,
    ]);

    mobile_ok([
        'updated' => $stmt->rowCount() > 0,
        'notification_id' => $notificationId,
    ]);
} catch (Throwable $e) {
    error_log('Mobile notifications read error: ' . $e->getMessage());
    mobile_error('Unable to update notification.', 500);
}
