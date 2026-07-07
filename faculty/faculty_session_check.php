<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['faculty_id'])) {
    header('Location: faculty_login.php');
    exit();
}

$timeout = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    header('Location: faculty_login.php?timeout=1');
    exit();
}

$_SESSION['last_activity'] = time();

$faculty_id = $_SESSION['faculty_id'] ?? null;
$member_no = $_SESSION['member_no'] ?? null;
$faculty_name = $_SESSION['faculty_name'] ?? 'Faculty';
$faculty_email = $_SESSION['faculty_email'] ?? '';
$faculty_department = $_SESSION['faculty_department'] ?? '';
$faculty_designation = $_SESSION['faculty_designation'] ?? '';
$employee_id = $_SESSION['employee_id'] ?? '';
$faculty_group = $_SESSION['faculty_group'] ?? '';
$books_issued = $_SESSION['books_issued'] ?? 0;

if (!isset($_SESSION['last_refresh']) || (time() - $_SESSION['last_refresh']) > 300) {
    try {
        require_once '../includes/db_connect.php';

        $stmt = $pdo->prepare("
            SELECT
                f.FacultyID, f.MemberNo, f.Department, f.Designation,
                f.EmployeeID, m.MemberName, m.Status, m.BooksIssued, m.`Group`
            FROM Faculty f
            INNER JOIN Member m ON f.MemberNo = m.MemberNo
            WHERE f.FacultyID = ? AND m.Status = 'Active'
            LIMIT 1
        ");

        $stmt->execute([$faculty_id]);
        $faculty_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($faculty_data) {
            $_SESSION['books_issued'] = $faculty_data['BooksIssued'];
            $_SESSION['last_refresh'] = time();
        } else {
            session_unset();
            session_destroy();
            header('Location: faculty_login.php?inactive=1');
            exit();
        }
    } catch (PDOException $e) {
        error_log("Faculty session refresh error: " . $e->getMessage());
    }
}
?>
