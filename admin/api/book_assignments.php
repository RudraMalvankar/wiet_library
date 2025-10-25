<?php
// Book Assignments API
require_once '../../includes/db_connect.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

function fetch_assignments($pdo) {
    $sql = "SELECT a.AssignmentID, a.CourseCode, a.CourseName, a.Branch, a.Semester, a.Year, a.Faculty, a.CatNo, b.Title, b.Author1 AS Author, a.AssignmentType, a.Priority, a.RequiredCopies, 
        (SELECT COUNT(*) FROM Holding h WHERE h.CatNo = a.CatNo AND h.Status = 'Available') AS AvailableCopies, a.Status, a.DateAssigned, a.ValidTill
    FROM BookAssignments a
    JOIN Books b ON a.CatNo = b.CatNo
    ORDER BY a.DateAssigned DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $rows]);
}

function create_assignment($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $sql = "INSERT INTO BookAssignments (CourseCode, CourseName, Branch, Semester, Year, Faculty, CatNo, AssignmentType, Priority, RequiredCopies, Status, DateAssigned, ValidTill) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $data['CourseCode'], $data['CourseName'], $data['Branch'], $data['Semester'], $data['Year'], $data['Faculty'], $data['CatNo'], $data['AssignmentType'], $data['Priority'], $data['RequiredCopies'], 'Active', date('Y-m-d'), $data['ValidTill']
    ]);
    echo json_encode(['success' => true]);
}

function delete_assignment($pdo) {
    $id = $_POST['AssignmentID'] ?? 0;
    $stmt = $pdo->prepare("DELETE FROM BookAssignments WHERE AssignmentID = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
}

function update_assignment($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $sql = "UPDATE BookAssignments SET CourseCode=?, CourseName=?, Branch=?, Semester=?, Year=?, Faculty=?, CatNo=?, AssignmentType=?, Priority=?, RequiredCopies=?, Status=?, ValidTill=? WHERE AssignmentID=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $data['CourseCode'], $data['CourseName'], $data['Branch'], $data['Semester'], $data['Year'], $data['Faculty'], $data['CatNo'], $data['AssignmentType'], $data['Priority'], $data['RequiredCopies'], $data['Status'], $data['ValidTill'], $data['AssignmentID']
    ]);
    echo json_encode(['success' => true]);
}

try {
    $pdo = getDb();
    switch ($action) {
        case 'list':
            fetch_assignments($pdo);
            break;
        case 'create':
            create_assignment($pdo);
            break;
        case 'delete':
            delete_assignment($pdo);
            break;
        case 'edit':
            update_assignment($pdo);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
