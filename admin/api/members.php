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
            
        default:
            sendJson(['success' => false, 'message' => 'Invalid action'], 400);
    }
    
} catch (Exception $e) {
    sendJson(['success' => false, 'message' => $e->getMessage()], 500);
}
?>
