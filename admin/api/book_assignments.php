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

function fetch_books($pdo) {
    $search = $_GET['search'] ?? '';
    $sql = "SELECT b.CatNo, b.Title, b.Author1, b.Subject, 
            (SELECT COUNT(*) FROM Holding h WHERE h.CatNo = b.CatNo AND h.Status = 'Available') AS AvailableCopies
            FROM Books b WHERE 1=1";
    $params = [];
    
    if ($search) {
        $sql .= " AND (b.Title LIKE ? OR b.Author1 LIKE ? OR b.Subject LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $sql .= " ORDER BY b.Title LIMIT 50";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $books]);
}

function fetch_statistics($pdo) {
    // Get total assignments
    $totalStmt = $pdo->query("SELECT COUNT(*) as count FROM BookAssignments");
    $total = $totalStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get active assignments
    $activeStmt = $pdo->query("SELECT COUNT(*) as count FROM BookAssignments WHERE Status = 'Active'");
    $active = $activeStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get shortage assignments
    $shortageStmt = $pdo->query("SELECT COUNT(*) as count FROM BookAssignments WHERE Status = 'Shortage'");
    $shortage = $shortageStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get total unique courses
    $coursesStmt = $pdo->query("SELECT COUNT(DISTINCT CourseCode) as count FROM BookAssignments");
    $courses = $coursesStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'totalAssignments' => (int)$total,
            'activeAssignments' => (int)$active,
            'shortageItems' => (int)$shortage,
            'totalCourses' => (int)$courses
        ]
    ]);
}

try {
    // Use global $pdo from db_connect.php
    global $pdo;
    
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
        case 'books':
            fetch_books($pdo);
            break;
        case 'stats':
            fetch_statistics($pdo);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
