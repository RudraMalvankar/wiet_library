<?php
require_once '../../includes/db_connect.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$response = ['success' => false, 'message' => '', 'data' => null];

try {
    if ($action === 'list') {
        $stmt = $pdo->query('SELECT r.*, e.EventTitle FROM event_registrations r JOIN library_events e ON r.EventID = e.EventID ORDER BY r.RegistrationDate DESC');
        $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response['success'] = true;
        $response['data'] = $registrations;
    } elseif ($action === 'register') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare('INSERT INTO event_registrations (EventID, MemberNo, MemberName, Email, Phone, RegistrationDate, Status, AttendanceStatus) VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)');
        $stmt->execute([
            $data['EventID'], $data['MemberNo'], $data['MemberName'], $data['Email'], $data['Phone'], $data['Status'], $data['AttendanceStatus']
        ]);
        $response['success'] = true;
        $response['message'] = 'Registration successful.';
    } elseif ($action === 'mark_attendance') {
        $registrationId = $_POST['RegistrationID'] ?? $_GET['RegistrationID'] ?? null;
        $status = $_POST['AttendanceStatus'] ?? $_GET['AttendanceStatus'] ?? 'Present';
        if ($registrationId) {
            $stmt = $pdo->prepare('UPDATE event_registrations SET AttendanceStatus=? WHERE RegistrationID=?');
            $stmt->execute([$status, $registrationId]);
            $response['success'] = true;
            $response['message'] = 'Attendance marked.';
        } else {
            $response['message'] = 'RegistrationID required.';
        }
    } elseif ($action === 'delete') {
        $registrationId = $_POST['RegistrationID'] ?? $_GET['RegistrationID'] ?? null;
        if ($registrationId) {
            $stmt = $pdo->prepare('DELETE FROM event_registrations WHERE RegistrationID=?');
            $stmt->execute([$registrationId]);
            $response['success'] = true;
            $response['message'] = 'Registration deleted.';
        } else {
            $response['message'] = 'RegistrationID required.';
        }
    } else {
        $response['message'] = 'Invalid action.';
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
