<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['faculty_id'])) {
    header('Location: faculty_login.php');
    exit();
}
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$member_no = $_SESSION['member_no'] ?? null;
$notifications = [];

try {
    $stmt = $pdo->prepare("
        SELECT NotificationID, Title, Message, Type, IsRead, CreatedDate
        FROM Notifications
        WHERE MemberNo = ? OR MemberNo IS NULL
        ORDER BY CreatedDate DESC LIMIT 50
    ");
    $stmt->execute([$member_no]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $pdo->prepare("UPDATE Notifications SET IsRead = 1 WHERE MemberNo = ? AND IsRead = 0")->execute([$member_no]);
} catch (Exception $e) {
    error_log("Notifications error: " . $e->getMessage());
}
?>
<style>
    .page-title { color: #263c79; font-size: 24px; font-weight: 700; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #cfac69; }
    .notif-card { background: white; border: 1px solid #e0e0e0; border-radius: 8px; padding: 15px 20px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); display: flex; align-items: flex-start; gap: 15px; }
    .notif-card.unread { border-left: 4px solid #263c79; background: #f8f9ff; }
    .notif-icon { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .notif-icon.info { background: #d1ecf1; color: #0c5460; }
    .notif-icon.warning { background: #fff3cd; color: #856404; }
    .notif-icon.overdue { background: #f8d7da; color: #721c24; }
    .notif-title { font-weight: 600; color: #263c79; margin-bottom: 4px; }
    .notif-message { color: #555; font-size: 14px; }
    .notif-date { color: #999; font-size: 12px; margin-top: 4px; }
    .empty-state { text-align: center; padding: 60px 20px; color: #6c757d; }
    .empty-state i { font-size: 48px; margin-bottom: 15px; opacity: 0.3; }
</style>
<h2 class="page-title"><i class="fas fa-bell"></i> Notifications</h2>
<?php if (empty($notifications)): ?>
    <div class="empty-state">
        <i class="fas fa-bell-slash"></i>
        <h3>No Notifications</h3>
        <p>You have no notifications at this time.</p>
    </div>
<?php else: ?>
    <?php foreach ($notifications as $n): ?>
        <div class="notif-card <?php echo $n['IsRead'] ? '' : 'unread'; ?>">
            <div class="notif-icon <?php echo strtolower($n['Type'] ?? 'info'); ?>">
                <i class="fas fa-<?php echo $n['Type'] === 'Overdue' ? 'exclamation-triangle' : ($n['Type'] === 'Warning' ? 'exclamation-circle' : 'info-circle'); ?>"></i>
            </div>
            <div style="flex:1;">
                <div class="notif-title"><?php echo htmlspecialchars($n['Title'] ?? 'Notification'); ?></div>
                <div class="notif-message"><?php echo htmlspecialchars($n['Message'] ?? ''); ?></div>
                <div class="notif-date"><?php echo date('d M Y, h:i A', strtotime($n['CreatedDate'])); ?></div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
