<?php
/**
 * Member API Endpoints
 * Handles CRUD operations for library members
 */

require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            // Get all members or filtered list
            $status = $_GET['status'] ?? 'all';
            $group = $_GET['group'] ?? 'all';
            $search = $_GET['search'] ?? '';
            
            $sql = "SELECT m.*, 
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
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            if (empty($data['MemberName']) || empty($data['Group'])) {
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
                $data['MemberName'],
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
            
            $data = json_decode(file_get_contents('php://input'), true);
            $memberNo = $data['MemberNo'] ?? 0;
            
            if (!$memberNo) {
                sendJson(['success' => false, 'message' => 'Member number is required'], 400);
            }
            
            // Update member
            $stmt = $pdo->prepare("
                UPDATE Member 
                SET MemberName = ?, `Group` = ?, Designation = ?, Phone = ?, 
                    Email = ?, Status = ?, ClosingDate = ?
                WHERE MemberNo = ?
            ");
            
            $stmt->execute([
                $data['MemberName'],
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
            
        case 'delete':
            // Delete member (only if no active circulations)
            if ($method !== 'POST') {
                sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
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
            $surname = $_POST['Surname'] ?? '';
            $middleName = $_POST['MiddleName'] ?? '';
            $firstName = $_POST['FirstName'] ?? '';
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
            $fullName = trim("$firstName $middleName $surname");
            
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
                
                // Generate QR code (simple format: PRN-MemberNo)
                $qrCode = "$prn-$memberNo";
                
                // Insert into Student table
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
                    $qrCode,
                    $photoData
                ]);
                
                $studentId = $pdo->lastInsertId();
                
                $pdo->commit();
                
                sendJson([
                    'success' => true, 
                    'message' => 'Student added successfully',
                    'memberNo' => $memberNo,
                    'studentId' => $studentId,
                    'qrCode' => $qrCode
                ]);
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
            
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
            
        case 'delete_student':
            // Delete student and associated member
            if ($method !== 'POST') {
                sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
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
            
        default:
            sendJson(['success' => false, 'message' => 'Invalid action'], 400);
    }
    
} catch (Exception $e) {
    sendJson(['success' => false, 'message' => $e->getMessage()], 500);
}
?>
