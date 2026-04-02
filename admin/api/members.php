<?php
/**
 * Member API Endpoints
 * Handles CRUD operations for library members
 */

require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

function normalizeNamePart($value): string {
    $value = trim((string)($value ?? ''));
    if ($value === '') {
        return '';
    }
    return preg_replace('/\s+/', ' ', $value);
}

function buildFullName($firstName, $middleName, $lastName): string {
    $parts = [
        normalizeNamePart($firstName),
        normalizeNamePart($middleName),
        normalizeNamePart($lastName)
    ];
    $parts = array_values(array_filter($parts, function ($part) {
        return $part !== '';
    }));
    return implode(' ', $parts);
}

// Start session for authentication and CSRF
session_start();

if (!isset($_SESSION['admin_id']) && !isset($_SESSION['AdminID'])) {
    sendJson(['success' => false, 'message' => 'Authentication required'], 401);
}

// Rate limiting check
$identifier = $_SESSION['AdminID'] ?? $_SESSION['admin_id'] ?? $_SERVER['REMOTE_ADDR'];
if (!checkRateLimit($identifier, 100, 60)) {
    sendJson(['success' => false, 'message' => 'Rate limit exceeded. Please try again later.'], 429);
}

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$rawInput = file_get_contents('php://input');
$jsonData = json_decode($rawInput, true);
if (!is_array($jsonData)) {
    $jsonData = [];
}

$action = $_GET['action'] ?? $_POST['action'] ?? ($jsonData['action'] ?? '');

// CSRF validation for POST/PUT/DELETE requests
if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
    $csrfToken = $jsonData['csrf_token'] ?? $_POST['csrf_token'] ?? '';
    
    if (!validateCSRFToken($csrfToken)) {
        sendJson(['success' => false, 'message' => 'Invalid CSRF token. Please refresh the page.'], 403);
    }
}

try {
    switch ($action) {
        case 'get-csrf-token':
            // Get CSRF token for form submissions
            $token = generateCSRFToken();
            sendJson(['success' => true, 'token' => $token]);
            break;
            
        case 'list':
            // Get all members or filtered list
            $status = $_GET['status'] ?? 'all';
            $group = $_GET['group'] ?? 'all';
            $search = $_GET['search'] ?? '';
            
          // Select explicit columns so we can prefer Student.Mobile when Member.Phone is empty
          $sql = "SELECT 
                    m.MemberNo,
                    m.MemberName,
                    m.`Group`,
                    m.Designation,
                    COALESCE(m.Phone, s.Mobile) AS Phone,
                    COALESCE(m.Email, s.Email) AS Email,
                    m.FinePerDay,
                    m.BooksIssued,
                    m.AdmissionDate,
                    m.ClosingDate,
                    m.Status,
                    s.PRN,
                    s.Branch,
                    CASE WHEN s.StudentID IS NOT NULL THEN 'Student' 
                        WHEN f.FacultyID IS NOT NULL THEN 'Faculty' 
                        ELSE 'Other' END as MemberType
                FROM Member m
                LEFT JOIN Student s ON m.MemberNo = s.MemberNo
                LEFT JOIN Faculty f ON m.MemberNo = f.MemberNo
                WHERE 1=1";
            
            $params = [];
            
            if ($status !== 'all') {
                $sql .= " AND m.Status = ?";
                $params[] = $status;
            }
            
            if ($group !== 'all') {
                $sql .= " AND m.`Group` = ?";
                $params[] = $group;
            }
            
            if ($search) {
                $sql .= " AND (m.MemberName LIKE ? OR m.MemberNo LIKE ? OR m.Email LIKE ?)";
                $searchTerm = "%{$search}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $sql .= " ORDER BY m.MemberName ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $members = $stmt->fetchAll();
            
            sendJson(['success' => true, 'data' => $members]);
            break;
            
        case 'get':
            // Get single member details
            $memberNo = $_GET['memberNo'] ?? 0;
            
            $stmt = $pdo->prepare("
                SELECT m.*, 
                       s.*, 
                       f.EmployeeID, f.Department
                FROM Member m
                LEFT JOIN Student s ON m.MemberNo = s.MemberNo
                LEFT JOIN Faculty f ON m.MemberNo = f.MemberNo
                WHERE m.MemberNo = ?
            ");
            $stmt->execute([$memberNo]);
            $member = $stmt->fetch();
            
            if (!$member) {
                sendJson(['success' => false, 'message' => 'Member not found'], 404);
            }
            
            // Normalize phone/email fields: prefer Member.Phone then Student.Mobile
            $memberPhone = isset($member['Phone']) && $member['Phone'] ? $member['Phone'] : (isset($member['Mobile']) ? $member['Mobile'] : null);
            $memberEmail = isset($member['Email']) && $member['Email'] ? $member['Email'] : (isset($member['Email']) ? $member['Email'] : null);
            $member['Phone'] = $memberPhone;
            $member['Email'] = $memberEmail;

            // Get active circulations
            $circulations = getMemberActiveCirculations($pdo, $memberNo);
            $member['activeCirculations'] = $circulations;
            
            sendJson(['success' => true, 'data' => $member]);
            break;
            
        case 'add':
            // Add new member
            if ($method !== 'POST') {
                sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
            }
            
            $data = $jsonData;

            $composedName = buildFullName(
                $data['FirstName'] ?? '',
                $data['MiddleName'] ?? '',
                $data['Surname'] ?? ($data['LastName'] ?? '')
            );
            $memberName = normalizeNamePart($data['MemberName'] ?? '');
            if ($memberName === '' && $composedName !== '') {
                $memberName = $composedName;
            }
            
            // Validate required fields
            if ($memberName === '' || empty($data['Group'])) {
                sendJson(['success' => false, 'message' => 'Member name and group are required'], 400);
            }
            
            // Generate member number if not provided
            $memberNo = $data['MemberNo'] ?? generateMemberNo($pdo);
            
            // Start transaction
            $pdo->beginTransaction();
            
            // Insert member
            $stmt = $pdo->prepare("
                INSERT INTO Member (MemberNo, MemberName, `Group`, Designation, Phone, Email, 
                                   FinePerDay, AdmissionDate, ClosingDate, Status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $memberNo,
                $memberName,
                $data['Group'],
                $data['Designation'] ?? null,
                $data['Phone'] ?? null,
                $data['Email'] ?? null,
                $data['FinePerDay'] ?? 2.00,
                $data['AdmissionDate'] ?? date('Y-m-d'),
                $data['ClosingDate'] ?? null,
                $data['Status'] ?? 'Active'
            ]);
            
            // If student, insert student details
            if ($data['Group'] === 'Student' && !empty($data['PRN'])) {
                $stmt = $pdo->prepare("
                    INSERT INTO Student (MemberNo, Surname, MiddleName, FirstName, DOB, Gender, 
                                        Branch, CourseName, PRN, Mobile, Email, Address, CardColour)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $memberNo,
                    $data['Surname'] ?? null,
                    $data['MiddleName'] ?? null,
                    $data['FirstName'] ?? null,
                    $data['DOB'] ?? null,
                    $data['Gender'] ?? null,
                    $data['Branch'] ?? null,
                    $data['CourseName'] ?? null,
                    $data['PRN'],
                    $data['Mobile'] ?? $data['Phone'],
                    $data['Email'],
                    $data['Address'] ?? null,
                    $data['CardColour'] ?? 'Blue'
                ]);
            }
            
            // If faculty, insert faculty details
            if ($data['Group'] === 'Faculty' && !empty($data['EmployeeID'])) {
                $stmt = $pdo->prepare("
                    INSERT INTO Faculty (MemberNo, EmployeeID, Department, Designation, 
                                        JoinDate, Mobile, Email, Address)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $memberNo,
                    $data['EmployeeID'],
                    $data['Department'] ?? null,
                    $data['Designation'] ?? null,
                    $data['JoinDate'] ?? date('Y-m-d'),
                    $data['Mobile'] ?? $data['Phone'],
                    $data['Email'],
                    $data['Address'] ?? null
                ]);
            }
            
            $pdo->commit();
            
            sendJson([
                'success' => true, 
                'message' => 'Member added successfully',
                'memberNo' => $memberNo
            ]);
            break;
            
        case 'update':
            // Update member
            if ($method !== 'POST') {
                sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
            }
            
            $data = $jsonData;
            $memberNo = $data['MemberNo'] ?? 0;

            $composedName = buildFullName(
                $data['FirstName'] ?? '',
                $data['MiddleName'] ?? '',
                $data['Surname'] ?? ($data['LastName'] ?? '')
            );
            $memberName = normalizeNamePart($data['MemberName'] ?? '');
            if ($memberName === '' && $composedName !== '') {
                $memberName = $composedName;
            }
            
            if (!$memberNo) {
                sendJson(['success' => false, 'message' => 'Member number is required'], 400);
            }
            if ($memberName === '') {
                sendJson(['success' => false, 'message' => 'Member name is required'], 400);
            }
            
            // Update member
            $stmt = $pdo->prepare("
                UPDATE Member 
                SET MemberName = ?, `Group` = ?, Designation = ?, Phone = ?, 
                    Email = ?, Status = ?, ClosingDate = ?
                WHERE MemberNo = ?
            ");
            
            $stmt->execute([
                $memberName,
                $data['Group'],
                $data['Designation'] ?? null,
                $data['Phone'] ?? null,
                $data['Email'] ?? null,
                $data['Status'] ?? 'Active',
                $data['ClosingDate'] ?? null,
                $memberNo
            ]);
            
            sendJson(['success' => true, 'message' => 'Member updated successfully']);
            break;

        case 'bulk_member_status':
            if ($method !== 'POST') {
                sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
            }

            $memberNos = $jsonData['memberNos'] ?? [];
            $status = trim((string)($jsonData['status'] ?? ''));

            if (!is_array($memberNos) || count($memberNos) === 0) {
                sendJson(['success' => false, 'message' => 'No members selected'], 400);
            }

            if (!in_array($status, ['Active', 'Inactive', 'Suspended'], true)) {
                sendJson(['success' => false, 'message' => 'Invalid status'], 400);
            }

            $cleanNos = array_values(array_filter(array_map('intval', $memberNos), function ($no) {
                return $no > 0;
            }));

            if (count($cleanNos) === 0) {
                sendJson(['success' => false, 'message' => 'No valid member numbers provided'], 400);
            }

            $placeholders = implode(',', array_fill(0, count($cleanNos), '?'));
            $params = array_merge([$status], $cleanNos);
            $stmt = $pdo->prepare("UPDATE Member SET Status = ? WHERE MemberNo IN ($placeholders)");
            $stmt->execute($params);

            sendJson([
                'success' => true,
                'message' => 'Status updated successfully',
                'updated' => $stmt->rowCount()
            ]);
            break;
            
        case 'delete':
            // Delete member (only if no active circulations)
            if ($method !== 'POST') {
                sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
            }
            
            $data = $jsonData;
            $memberNo = $data['MemberNo'] ?? 0;
            
            // Check for active circulations
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM Circulation WHERE MemberNo = ? AND Status = 'Active'");
            $stmt->execute([$memberNo]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                sendJson(['success' => false, 'message' => 'Cannot delete member with active book issues'], 400);
            }
            
            // Soft delete - update status to Inactive
            $stmt = $pdo->prepare("UPDATE Member SET Status = 'Inactive' WHERE MemberNo = ?");
            $stmt->execute([$memberNo]);
            
            sendJson(['success' => true, 'message' => 'Member deactivated successfully']);
            break;
            
        case 'search':
            // Quick search members
            $query = $_GET['q'] ?? '';
            
            if (strlen($query) < 2) {
                sendJson(['success' => true, 'data' => []]);
            }
            
            $searchTerm = "%{$query}%";
            $stmt = $pdo->prepare("
                SELECT MemberNo, MemberName, `Group`, Phone, Email, Status, BooksIssued
                FROM Member
                WHERE (MemberName LIKE ? OR MemberNo LIKE ? OR Phone LIKE ?)
                  AND Status = 'Active'
                ORDER BY MemberName
                LIMIT 20
            ");
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
            $members = $stmt->fetchAll();
            
            sendJson(['success' => true, 'data' => $members]);
            break;
            
        case 'list_students':
            // Get all students with member information
            $name = $_GET['name'] ?? '';
            $prn = $_GET['prn'] ?? '';
            $branch = $_GET['branch'] ?? '';
            $status = $_GET['status'] ?? '';
            
            $sql = "
                SELECT 
                    s.*,
                    m.MemberNo,
                    m.MemberName,
                    m.`Group`,
                    m.Phone,
                    m.Email as MemberEmail,
                    m.Status,
                    m.BooksIssued,
                    m.Designation,
                    CONCAT(s.FirstName, ' ', COALESCE(s.MiddleName, ''), ' ', COALESCE(s.Surname, '')) as FullName
                FROM Student s
                INNER JOIN Member m ON s.MemberNo = m.MemberNo
                WHERE 1=1
            ";
            
            $params = [];
            
            if ($name) {
                $sql .= " AND (m.MemberName LIKE ? OR s.FirstName LIKE ? OR s.Surname LIKE ?)";
                $nameTerm = "%{$name}%";
                $params[] = $nameTerm;
                $params[] = $nameTerm;
                $params[] = $nameTerm;
            }
            
            if ($prn) {
                $sql .= " AND s.PRN LIKE ?";
                $params[] = "%{$prn}%";
            }
            
            if ($branch) {
                $sql .= " AND s.Branch = ?";
                $params[] = $branch;
            }
            
            if ($status) {
                $sql .= " AND m.Status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY m.MemberName ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $students = $stmt->fetchAll();
            
            sendJson(['success' => true, 'data' => $students, 'count' => count($students)]);
            break;
            
        case 'add_student':
            // Add new student with photo upload support
            if ($method !== 'POST') {
                sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
            }
            
            // Get form data
            $surname = normalizeNamePart($_POST['Surname'] ?? ($_POST['LastName'] ?? ''));
            $middleName = normalizeNamePart($_POST['MiddleName'] ?? '');
            $firstName = normalizeNamePart($_POST['FirstName'] ?? '');
            $prn = $_POST['PRN'] ?? '';
            $branch = $_POST['Branch'] ?? '';
            $courseName = $_POST['CourseName'] ?? '';
            $gender = $_POST['Gender'] ?? '';
            $dob = $_POST['DOB'] ?? null;
            $bloodGroup = $_POST['BloodGroup'] ?? '';
            $mobile = $_POST['Mobile'] ?? '';
            $email = $_POST['Email'] ?? '';
            $address = $_POST['Address'] ?? '';
            $validTill = $_POST['ValidTill'] ?? null;
            $cardColour = $_POST['CardColour'] ?? 'Blue';
            
            // Validate required fields
            if (empty($firstName) || empty($prn) || empty($branch)) {
                sendJson(['success' => false, 'message' => 'First name, PRN, and Branch are required'], 400);
            }
            
            // Handle photo upload
            $photoData = null;
            if (isset($_FILES['Photo']) && $_FILES['Photo']['error'] === UPLOAD_ERR_OK) {
                $photoData = file_get_contents($_FILES['Photo']['tmp_name']);
            }
            
            // Generate member number
            $stmt = $pdo->query("SELECT MAX(MemberNo) as maxNo FROM Member");
            $result = $stmt->fetch();
            $memberNo = ($result['maxNo'] ?? 0) + 1;
            
            // Create full name for Member table
            $fullName = buildFullName($firstName, $middleName, $surname);
            
            // Start transaction
            $pdo->beginTransaction();
            
            try {
                // Insert into Member table
                $stmt = $pdo->prepare("
                    INSERT INTO Member (MemberNo, MemberName, `Group`, Phone, Email, 
                                       FinePerDay, AdmissionDate, Status, BooksIssued)
                    VALUES (?, ?, 'Student', ?, ?, 2.00, CURDATE(), 'Active', 0)
                ");
                
                $stmt->execute([
                    $memberNo,
                    $fullName,
                    $mobile,
                    $email
                ]);
                
                // Generate QR code data and image
                require_once '../../libs/phpqrcode/qrlib.php';
                $qrData = "$prn-$memberNo";
                
                // Generate QR code image to memory buffer
                ob_start();
                QRcode::png($qrData, null, QR_ECLEVEL_L, 4);
                $qrImageData = ob_get_contents();
                ob_end_clean();
                
                // Insert into Student table with QR code image stored in BLOB
                $stmt = $pdo->prepare("
                    INSERT INTO Student (MemberNo, Surname, MiddleName, FirstName, DOB, Gender, 
                                        BloodGroup, Branch, CourseName, ValidTill, PRN, Mobile, 
                                        Email, Address, CardColour, QRCode, Photo)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $memberNo,
                    $surname,
                    $middleName,
                    $firstName,
                    $dob,
                    $gender,
                    $bloodGroup,
                    $branch,
                    $courseName,
                    $validTill,
                    $prn,
                    $mobile,
                    $email,
                    $address,
                    $cardColour,
                    $qrImageData,  // Store QR code PNG image data
                    $photoData
                ]);
                
                $studentId = $pdo->lastInsertId();
                
                $pdo->commit();
                
                sendJson([
                    'success' => true, 
                    'message' => 'Student added successfully',
                    'memberNo' => $memberNo,
                    'studentId' => $studentId,
                    'qrData' => $qrData
                ]);
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            
        case 'get_student':
            // Get single student details
            $studentId = $_GET['studentId'] ?? 0;
            
            $stmt = $pdo->prepare("
                SELECT s.*, m.*
                FROM Student s
                INNER JOIN Member m ON s.MemberNo = m.MemberNo
                WHERE s.StudentID = ?
            ");
            $stmt->execute([$studentId]);
            $student = $stmt->fetch();
            
            if (!$student) {
                sendJson(['success' => false, 'message' => 'Student not found'], 404);
            }
            
            sendJson(['success' => true, 'data' => $student]);
            break;

        case 'update_student':
            if ($method !== 'POST') {
                sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
            }

            $payload = array_merge($jsonData, $_POST);
            $studentId = (int)($payload['StudentID'] ?? $payload['studentId'] ?? 0);

            if ($studentId <= 0) {
                sendJson(['success' => false, 'message' => 'Student ID is required'], 400);
            }

            $stmt = $pdo->prepare("\n                SELECT s.StudentID, s.MemberNo, s.Surname, s.MiddleName, s.FirstName, s.DOB, s.Gender, s.BloodGroup,\n                       s.Branch, s.CourseName, s.ValidTill, s.PRN, s.Mobile, s.Email, s.Address, s.CardColour, s.Photo,\n                       m.MemberName, m.Phone, m.Email AS MemberEmail, m.Status, m.Designation\n                FROM Student s\n                INNER JOIN Member m ON s.MemberNo = m.MemberNo\n                WHERE s.StudentID = ?\n            ");
            $stmt->execute([$studentId]);
            $existing = $stmt->fetch();

            if (!$existing) {
                sendJson(['success' => false, 'message' => 'Student not found'], 404);
            }

            $surname = normalizeNamePart($payload['Surname'] ?? $existing['Surname']);
            $middleName = normalizeNamePart($payload['MiddleName'] ?? $existing['MiddleName']);
            $firstName = normalizeNamePart($payload['FirstName'] ?? $existing['FirstName']);

            if ($firstName === '') {
                sendJson(['success' => false, 'message' => 'First name is required'], 400);
            }

            $prn = trim((string)($payload['PRN'] ?? $existing['PRN']));
            $branch = trim((string)($payload['Branch'] ?? $existing['Branch']));

            if ($prn === '' || $branch === '') {
                sendJson(['success' => false, 'message' => 'PRN and Branch are required'], 400);
            }

            $mobile = trim((string)($payload['Mobile'] ?? $payload['Phone'] ?? $existing['Mobile'] ?? $existing['Phone']));
            $email = trim((string)($payload['Email'] ?? $payload['StudentEmail'] ?? $existing['Email'] ?? $existing['MemberEmail']));
            $memberName = normalizeNamePart($payload['MemberName'] ?? buildFullName($firstName, $middleName, $surname));
            if ($memberName === '') {
                $memberName = $existing['MemberName'];
            }

            $status = trim((string)($payload['Status'] ?? $existing['Status']));
            $allowedStatuses = ['Active', 'Inactive', 'Suspended'];
            if (!in_array($status, $allowedStatuses, true)) {
                $status = $existing['Status'];
            }

            $photoData = $existing['Photo'];
            if (isset($_FILES['Photo']) && $_FILES['Photo']['error'] === UPLOAD_ERR_OK) {
                $photoData = file_get_contents($_FILES['Photo']['tmp_name']);
            }

            $pdo->beginTransaction();

            try {
                $stmt = $pdo->prepare("\n                    UPDATE Member\n                    SET MemberName = ?, Phone = ?, Email = ?, Status = ?, Designation = ?\n                    WHERE MemberNo = ?\n                ");
                $stmt->execute([
                    $memberName,
                    $mobile !== '' ? $mobile : null,
                    $email !== '' ? $email : null,
                    $status,
                    $payload['Designation'] ?? $existing['Designation'],
                    $existing['MemberNo']
                ]);

                $stmt = $pdo->prepare("\n                    UPDATE Student\n                    SET Surname = ?, MiddleName = ?, FirstName = ?, DOB = ?, Gender = ?, BloodGroup = ?,\n                        Branch = ?, CourseName = ?, ValidTill = ?, PRN = ?, Mobile = ?, Email = ?, Address = ?,\n                        CardColour = ?, Photo = ?\n                    WHERE StudentID = ?\n                ");
                $stmt->execute([
                    $surname,
                    $middleName !== '' ? $middleName : null,
                    $firstName,
                    $payload['DOB'] ?? $existing['DOB'],
                    $payload['Gender'] ?? $existing['Gender'],
                    $payload['BloodGroup'] ?? $existing['BloodGroup'],
                    $branch,
                    $payload['CourseName'] ?? $existing['CourseName'],
                    $payload['ValidTill'] ?? $existing['ValidTill'],
                    $prn,
                    $mobile !== '' ? $mobile : null,
                    $email !== '' ? $email : null,
                    $payload['Address'] ?? $existing['Address'],
                    $payload['CardColour'] ?? $existing['CardColour'],
                    $photoData,
                    $studentId
                ]);

                $pdo->commit();
                sendJson(['success' => true, 'message' => 'Student updated successfully']);
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'bulk_student_status':
            if ($method !== 'POST') {
                sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
            }

            $studentIds = $jsonData['studentIds'] ?? [];
            $status = trim((string)($jsonData['status'] ?? ''));

            if (!is_array($studentIds) || count($studentIds) === 0) {
                sendJson(['success' => false, 'message' => 'No students selected'], 400);
            }

            if (!in_array($status, ['Active', 'Inactive', 'Suspended'], true)) {
                sendJson(['success' => false, 'message' => 'Invalid status'], 400);
            }

            $cleanIds = array_values(array_filter(array_map('intval', $studentIds), function ($id) {
                return $id > 0;
            }));

            if (count($cleanIds) === 0) {
                sendJson(['success' => false, 'message' => 'No valid student IDs provided'], 400);
            }

            $placeholders = implode(',', array_fill(0, count($cleanIds), '?'));
            $stmt = $pdo->prepare("SELECT MemberNo FROM Student WHERE StudentID IN ($placeholders)");
            $stmt->execute($cleanIds);
            $memberNos = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (count($memberNos) === 0) {
                sendJson(['success' => false, 'message' => 'No matching students found'], 404);
            }

            $memberPlaceholders = implode(',', array_fill(0, count($memberNos), '?'));
            $updateParams = array_merge([$status], $memberNos);
            $stmt = $pdo->prepare("UPDATE Member SET Status = ? WHERE MemberNo IN ($memberPlaceholders)");
            $stmt->execute($updateParams);

            sendJson([
                'success' => true,
                'message' => 'Status updated successfully',
                'updated' => $stmt->rowCount()
            ]);
            break;

        case 'bulk_extend_membership':
            if ($method !== 'POST') {
                sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
            }

            $studentIds = $jsonData['studentIds'] ?? [];
            $months = (int)($jsonData['months'] ?? 12);

            if (!is_array($studentIds) || count($studentIds) === 0) {
                sendJson(['success' => false, 'message' => 'No students selected'], 400);
            }

            if ($months < 1 || $months > 60) {
                sendJson(['success' => false, 'message' => 'Invalid extension period'], 400);
            }

            $cleanIds = array_values(array_filter(array_map('intval', $studentIds), function ($id) {
                return $id > 0;
            }));

            if (count($cleanIds) === 0) {
                sendJson(['success' => false, 'message' => 'No valid student IDs provided'], 400);
            }

            $pdo->beginTransaction();
            try {
                $extended = 0;
                $updateStmt = $pdo->prepare("\n                    UPDATE Student\n                    SET ValidTill = CASE\n                        WHEN ValidTill IS NULL OR ValidTill < CURDATE() THEN DATE_ADD(CURDATE(), INTERVAL ? MONTH)\n                        ELSE DATE_ADD(ValidTill, INTERVAL ? MONTH)\n                    END\n                    WHERE StudentID = ?\n                ");
                $statusStmt = $pdo->prepare("\n                    UPDATE Member m\n                    INNER JOIN Student s ON m.MemberNo = s.MemberNo\n                    SET m.Status = 'Active'\n                    WHERE s.StudentID = ?\n                ");

                foreach ($cleanIds as $id) {
                    $updateStmt->execute([$months, $months, $id]);
                    if ($updateStmt->rowCount() > 0) {
                        $extended++;
                    }
                    $statusStmt->execute([$id]);
                }

                $pdo->commit();

                sendJson([
                    'success' => true,
                    'message' => 'Membership validity updated successfully',
                    'updated' => $extended
                ]);
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        case 'delete_student':
            // Delete student and associated member
            if ($method !== 'POST') {
                sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
            }
            
            $data = $jsonData;
            $studentId = $data['studentId'] ?? 0;
            
            if (!$studentId) {
                sendJson(['success' => false, 'message' => 'Student ID is required'], 400);
            }
            
            // Get member number first
            $stmt = $pdo->prepare("SELECT MemberNo FROM Student WHERE StudentID = ?");
            $stmt->execute([$studentId]);
            $student = $stmt->fetch();
            
            if (!$student) {
                sendJson(['success' => false, 'message' => 'Student not found'], 404);
            }
            
            $memberNo = $student['MemberNo'];
            
            // Check if student has active circulations
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM Circulation WHERE MemberNo = ? AND Status = 'Issued'");
            $stmt->execute([$memberNo]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                sendJson(['success' => false, 'message' => 'Cannot delete student with active book issues. Please return all books first.'], 400);
            }
            
            // Start transaction
            $pdo->beginTransaction();
            
            try {
                // Delete student record
                $stmt = $pdo->prepare("DELETE FROM Student WHERE StudentID = ?");
                $stmt->execute([$studentId]);
                
                // Delete member record
                $stmt = $pdo->prepare("DELETE FROM Member WHERE MemberNo = ?");
                $stmt->execute([$memberNo]);
                
                $pdo->commit();
                
                sendJson([
                    'success' => true, 
                    'message' => 'Student and member record deleted successfully'
                ]);
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        case 'get_qr_code':
            // Get QR code for a student
            $studentId = $_GET['studentId'] ?? 0;
            
            if (!$studentId) {
                sendJson(['success' => false, 'message' => 'Student ID is required'], 400);
            }
            
            // Get student data including QR code from database
            $stmt = $pdo->prepare("
                SELECT s.QRCode, s.PRN, m.MemberNo, m.MemberName
                FROM Student s
                INNER JOIN Member m ON s.MemberNo = m.MemberNo
                WHERE s.StudentID = ?
            ");
            $stmt->execute([$studentId]);
            $student = $stmt->fetch();
            
            if (!$student) {
                sendJson(['success' => false, 'message' => 'Student not found'], 404);
            }
            
            // Check if QR code exists in database
            if (!empty($student['QRCode'])) {
                // QR code exists in database, return it
                $qrCodeBase64 = base64_encode($student['QRCode']);
            } else {
                // Generate QR code and save to database
                require_once '../../libs/phpqrcode/qrlib.php';
                
                $qrData = $student['PRN'] . '-' . $student['MemberNo'];
                
                // Generate QR code to temporary memory buffer
                ob_start();
                QRcode::png($qrData, null, QR_ECLEVEL_L, 4);
                $qrImageData = ob_get_contents();
                ob_end_clean();
                
                // Save to database
                $updateStmt = $pdo->prepare("UPDATE Student SET QRCode = ? WHERE StudentID = ?");
                $updateStmt->execute([$qrImageData, $studentId]);
                
                $qrCodeBase64 = base64_encode($qrImageData);
            }
            
            $qrData = $student['PRN'] . '-' . $student['MemberNo'];
            
            sendJson([
                'success' => true,
                'qrCode' => $qrCodeBase64,
                'qrData' => $qrData,
                'studentName' => $student['MemberName'],
                'prn' => $student['PRN'],
                'memberNo' => $student['MemberNo']
            ]);
            break;
        
        default:
            sendJson(['success' => false, 'message' => 'Invalid action'], 400);
    }
    
} catch (Exception $e) {
    sendJson(['success' => false, 'message' => $e->getMessage()], 500);
}
?>
