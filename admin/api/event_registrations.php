<?php
session_start();
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
header('Content-Type: application/json');

// Admin authentication check
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['AdminID'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Admin login required.']);
    exit;
}

// Rate limiting
if (!checkRateLimit('event_registrations_api', 100, 60)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many requests. Please try again later.']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// CSRF protection for write operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action !== 'get-csrf-token') {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!validateCSRFToken($token)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }
}

// Handle CSRF token request
if ($action === 'get-csrf-token') {
    echo json_encode(['success' => true, 'token' => generateCSRFToken()]);
    exit;
}

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
