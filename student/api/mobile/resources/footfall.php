<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

try {
    $student = mobile_require_auth($pdo);
    $memberNo = (int)$student['MemberNo'];

    if ($method === 'GET') {
        $statusStmt = $pdo->prepare(
            "SELECT FootfallID, EntryTime, Purpose
             FROM Footfall
             WHERE MemberNo = :member_no
             AND DATE(EntryTime) = CURDATE()
             AND Status = 'Active'
             ORDER BY EntryTime DESC
             LIMIT 1"
        );
        $statusStmt->execute(['member_no' => $memberNo]);
        $active = $statusStmt->fetch(PDO::FETCH_ASSOC);

        $recentStmt = $pdo->prepare(
            "SELECT DATE(EntryTime) AS visit_date, TIME(EntryTime) AS entry_time, Purpose
             FROM Footfall
             WHERE MemberNo = :member_no
             AND EntryTime IS NOT NULL
             ORDER BY EntryTime DESC
             LIMIT 20"
        );
        $recentStmt->execute(['member_no' => $memberNo]);

        $weekly = [
            'Mon' => 0,
            'Tue' => 0,
            'Wed' => 0,
            'Thu' => 0,
            'Fri' => 0,
            'Sat' => 0,
            'Sun' => 0,
        ];

        $weeklyStmt = $pdo->prepare(
            "SELECT DAYNAME(EntryTime) AS day_name, COUNT(*) AS visits
             FROM Footfall
             WHERE MemberNo = :member_no
             AND DATE(EntryTime) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
             GROUP BY DAYNAME(EntryTime), DAYOFWEEK(EntryTime)
             ORDER BY DAYOFWEEK(EntryTime)"
        );
        $weeklyStmt->execute(['member_no' => $memberNo]);
        foreach ($weeklyStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $abbr = substr((string)$row['day_name'], 0, 3);
            if (isset($weekly[$abbr])) {
                $weekly[$abbr] = (int)$row['visits'];
            }
        }

        mobile_ok([
            'is_checked_in' => (bool)$active,
            'active_entry' => $active ?: null,
            'weekly_visits' => $weekly,
            'recent_visits' => $recentStmt->fetchAll(PDO::FETCH_ASSOC),
        ]);
    }

    if ($method !== 'POST') {
        mobile_error('Method not allowed', 405);
    }

    $input = mobile_read_json_body();
    $action = strtolower(trim((string)($input['action'] ?? '')));

    if (!in_array($action, ['checkin', 'checkout'], true)) {
        mobile_error('action must be checkin or checkout', 422);
    }

    if ($action === 'checkin') {
        $purpose = trim((string)($input['purpose'] ?? 'Library Visit'));

        $checkStmt = $pdo->prepare(
            "SELECT FootfallID, EntryTime
             FROM Footfall
             WHERE MemberNo = :member_no
             AND DATE(EntryTime) = CURDATE()
             AND Status = 'Active'
             LIMIT 1"
        );
        $checkStmt->execute(['member_no' => $memberNo]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            mobile_error('You are already checked in today.', 409, ['entry_time' => $existing['EntryTime']]);
        }

        $insertStmt = $pdo->prepare(
            "INSERT INTO Footfall
                (MemberNo, Date, TimeIn, EntryTime, Purpose, Status, EntryMethod)
             VALUES
                (:member_no, CURDATE(), CURTIME(), NOW(), :purpose, 'Active', 'Mobile App')"
        );
        $insertStmt->execute([
            'member_no' => $memberNo,
            'purpose' => $purpose,
        ]);

        mobile_ok([
            'message' => 'Check-in successful',
            'footfall_id' => (int)$pdo->lastInsertId(),
            'entry_time' => date('Y-m-d H:i:s'),
        ]);
    }

    $activeStmt = $pdo->prepare(
        "SELECT FootfallID, EntryTime
         FROM Footfall
         WHERE MemberNo = :member_no
         AND DATE(EntryTime) = CURDATE()
         AND Status = 'Active'
         ORDER BY EntryTime DESC
         LIMIT 1"
    );
    $activeStmt->execute(['member_no' => $memberNo]);
    $entry = $activeStmt->fetch(PDO::FETCH_ASSOC);

    if (!$entry) {
        mobile_error('No active check-in found for today.', 404);
    }

    $updateStmt = $pdo->prepare(
        "UPDATE Footfall
         SET TimeOut = CURTIME(),
             ExitTime = NOW(),
             Duration = TIMESTAMPDIFF(MINUTE, EntryTime, NOW()),
             Status = 'Completed'
         WHERE FootfallID = :footfall_id"
    );
    $updateStmt->execute(['footfall_id' => $entry['FootfallID']]);

    $entryTime = new DateTime((string)$entry['EntryTime']);
    $exitTime = new DateTime('now');
    $diff = $entryTime->diff($exitTime);
    $duration = sprintf('%dh %dm', $diff->h + ($diff->days * 24), $diff->i);

    mobile_ok([
        'message' => 'Check-out successful',
        'duration' => $duration,
        'entry_time' => $entry['EntryTime'],
        'exit_time' => $exitTime->format('Y-m-d H:i:s'),
    ]);
} catch (Throwable $e) {
    error_log('Mobile footfall error: ' . $e->getMessage());
    mobile_error('Unable to process footfall request.', 500);
}
