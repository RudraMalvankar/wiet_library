<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['faculty_id'])) {
    header('Location: faculty_login.php');
    exit();
}
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$faculty_id = $_SESSION['faculty_id'];
$member_no = $_SESSION['member_no'];
$profile = [];

try {
    $stmt = $pdo->prepare("
        SELECT f.*, m.MemberName, m.Phone, m.Email as MemberEmail, m.Status, m.BooksIssued, m.`Group`, m.AdmissionDate
        FROM Faculty f
        INNER JOIN Member m ON f.MemberNo = m.MemberNo
        WHERE f.FacultyID = ?
    ");
    $stmt->execute([$faculty_id]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Profile error: " . $e->getMessage());
}
?>
<style>
    .page-title { color: #263c79; font-size: 24px; font-weight: 700; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #cfac69; }
    .profile-card { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); max-width: 600px; }
    .profile-header { background: linear-gradient(135deg, #263c79, #3d5a9e); color: white; padding: 25px; border-radius: 8px 8px 0 0; text-align: center; }
    .profile-avatar { width: 80px; height: 80px; border-radius: 50%; background: #cfac69; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 32px; color: #263c79; }
    .profile-name { font-size: 22px; font-weight: 700; margin-bottom: 5px; }
    .profile-role { opacity: 0.9; font-size: 14px; }
    .profile-body { padding: 25px; }
    .profile-row { display: flex; padding: 12px 0; border-bottom: 1px solid #f0f0f0; }
    .profile-label { width: 140px; font-weight: 600; color: #666; flex-shrink: 0; }
    .profile-value { color: #333; flex: 1; }
    .profile-row:last-child { border-bottom: none; }
</style>
<h2 class="page-title"><i class="fas fa-user"></i> My Profile</h2>
<?php if ($profile): ?>
<div class="profile-card">
    <div class="profile-header">
        <div class="profile-avatar"><i class="fas fa-chalkboard-teacher"></i></div>
        <div class="profile-name"><?php echo htmlspecialchars($profile['MemberName']); ?></div>
        <div class="profile-role"><?php echo htmlspecialchars($profile['Designation']); ?> &middot; <?php echo htmlspecialchars($profile['Department']); ?></div>
    </div>
    <div class="profile-body">
        <div class="profile-row"><div class="profile-label">Member No</div><div class="profile-value"><?php echo htmlspecialchars($profile['MemberNo']); ?></div></div>
        <div class="profile-row"><div class="profile-label">Employee ID</div><div class="profile-value"><?php echo htmlspecialchars($profile['EmployeeID'] ?? '-'); ?></div></div>
        <div class="profile-row"><div class="profile-label">Group</div><div class="profile-value"><?php echo htmlspecialchars($profile['Group']); ?></div></div>
        <div class="profile-row"><div class="profile-label">Department</div><div class="profile-value"><?php echo htmlspecialchars($profile['Department'] ?? '-'); ?></div></div>
        <div class="profile-row"><div class="profile-label">Designation</div><div class="profile-value"><?php echo htmlspecialchars($profile['Designation'] ?? '-'); ?></div></div>
        <div class="profile-row"><div class="profile-label">Email</div><div class="profile-value"><?php echo htmlspecialchars($profile['Email'] ?? $profile['MemberEmail'] ?? '-'); ?></div></div>
        <div class="profile-row"><div class="profile-label">Mobile</div><div class="profile-value"><?php echo htmlspecialchars($profile['Mobile'] ?? $profile['Phone'] ?? '-'); ?></div></div>
        <div class="profile-row"><div class="profile-label">Books Issued</div><div class="profile-value"><?php echo $profile['BooksIssued']; ?>/5</div></div>
        <div class="profile-row"><div class="profile-label">Status</div><div class="profile-value"><span style="color:#28a745;font-weight:600;"><?php echo htmlspecialchars($profile['Status']); ?></span></div></div>
        <div class="profile-row"><div class="profile-label">Member Since</div><div class="profile-value"><?php echo $profile['AdmissionDate'] ? date('d M Y', strtotime($profile['AdmissionDate'])) : '-'; ?></div></div>
    </div>
</div>
<?php endif; ?>
