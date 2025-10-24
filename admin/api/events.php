<?php
require_once '../../includes/db_connect.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$response = ['success' => false, 'message' => '', 'data' => null];

try {
    if ($action === 'list') {
        $stmt = $pdo->query('SELECT * FROM library_events ORDER BY StartDate DESC');
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response['success'] = true;
        $response['data'] = $events;
    } elseif ($action === 'create') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare('INSERT INTO library_events (EventTitle, EventType, Description, StartDate, EndDate, StartTime, EndTime, Venue, Capacity, Status, OrganizedBy, ContactPerson, ContactEmail, ContactPhone, RegistrationRequired, RegistrationDeadline) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['EventTitle'], $data['EventType'], $data['Description'], $data['StartDate'], $data['EndDate'], $data['StartTime'], $data['EndTime'], $data['Venue'], $data['Capacity'], $data['Status'], $data['OrganizedBy'], $data['ContactPerson'], $data['ContactEmail'], $data['ContactPhone'], $data['RegistrationRequired'], $data['RegistrationDeadline']
        ]);
        $response['success'] = true;
        $response['message'] = 'Event created successfully.';
    } elseif ($action === 'edit') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare('UPDATE library_events SET EventTitle=?, EventType=?, Description=?, StartDate=?, EndDate=?, StartTime=?, EndTime=?, Venue=?, Capacity=?, Status=?, OrganizedBy=?, ContactPerson=?, ContactEmail=?, ContactPhone=?, RegistrationRequired=?, RegistrationDeadline=? WHERE EventID=?');
        $stmt->execute([
            $data['EventTitle'], $data['EventType'], $data['Description'], $data['StartDate'], $data['EndDate'], $data['StartTime'], $data['EndTime'], $data['Venue'], $data['Capacity'], $data['Status'], $data['OrganizedBy'], $data['ContactPerson'], $data['ContactEmail'], $data['ContactPhone'], $data['RegistrationRequired'], $data['RegistrationDeadline'], $data['EventID']
        ]);
        $response['success'] = true;
        $response['message'] = 'Event updated successfully.';
    } elseif ($action === 'delete') {
        $eventId = $_POST['EventID'] ?? $_GET['EventID'] ?? null;
        if ($eventId) {
            $stmt = $pdo->prepare('DELETE FROM library_events WHERE EventID=?');
            $stmt->execute([$eventId]);
            $response['success'] = true;
            $response['message'] = 'Event deleted successfully.';
        } else {
            $response['message'] = 'EventID required.';
        }
    } elseif ($action === 'get') {
        $eventId = $_GET['EventID'] ?? null;
        if ($eventId) {
            $stmt = $pdo->prepare('SELECT * FROM library_events WHERE EventID=?');
            $stmt->execute([$eventId]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);
            $response['success'] = true;
            $response['data'] = $event;
        } else {
            $response['message'] = 'EventID required.';
        }
    } else {
        $response['message'] = 'Invalid action.';
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
