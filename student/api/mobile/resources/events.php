<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

mobile_require_method('GET');

try {
    mobile_require_auth($pdo);

    $stmt = $pdo->query(
        "SELECT
            e.EventID,
            e.EventTitle,
            e.EventType,
            e.Description,
            e.StartDate,
            e.EndDate,
            e.StartTime,
            e.EndTime,
            e.Venue,
            e.Capacity,
            e.Status,
            e.OrganizedBy,
            (
                SELECT COUNT(*)
                FROM event_registrations er
                WHERE er.EventID = e.EventID
            ) AS Registered
         FROM library_events e
         WHERE e.Status IN ('Active', 'Upcoming', 'Completed')
         ORDER BY
            CASE
                WHEN e.Status = 'Active' THEN 1
                WHEN e.Status = 'Upcoming' THEN 2
                ELSE 3
            END,
            e.StartDate DESC"
    );

    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $transform = static function (array $row): array {
        $capacity = max(1, (int)($row['Capacity'] ?? 0));
        $registered = (int)($row['Registered'] ?? 0);

        return [
            'event_id' => (int)$row['EventID'],
            'title' => $row['EventTitle'],
            'type' => $row['EventType'] ?: 'Event',
            'description' => $row['Description'] ?: '',
            'start_date' => $row['StartDate'],
            'end_date' => $row['EndDate'],
            'start_time' => $row['StartTime'],
            'end_time' => $row['EndTime'],
            'venue' => $row['Venue'] ?: '',
            'status' => $row['Status'],
            'organizer' => $row['OrganizedBy'] ?: '',
            'capacity' => (int)($row['Capacity'] ?? 0),
            'registered' => $registered,
            'occupancy_percent' => (int)min(100, round(($registered * 100) / $capacity)),
        ];
    };

    $active = [];
    $upcoming = [];
    $completed = [];

    foreach ($events as $event) {
        $item = $transform($event);
        if ($item['status'] === 'Active') {
            $active[] = $item;
        } elseif ($item['status'] === 'Upcoming') {
            $upcoming[] = $item;
        } else {
            $completed[] = $item;
        }
    }

    mobile_ok([
        'active' => $active,
        'upcoming' => $upcoming,
        'completed' => $completed,
    ]);
} catch (Throwable $e) {
    error_log('Mobile events error: ' . $e->getMessage());
    mobile_error('Unable to load library events.', 500);
}
