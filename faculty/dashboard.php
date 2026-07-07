<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['faculty_id'])) {
    header('Location: faculty_login.php');
    exit();
}
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$faculty_name = $_SESSION['faculty_name'] ?? 'Faculty';
$faculty_id = $_SESSION['faculty_id'] ?? null;
$member_no = $_SESSION['member_no'] ?? null;
$faculty_department = $_SESSION['faculty_department'] ?? '';
$faculty_designation = $_SESSION['faculty_designation'] ?? '';

try {
    $quick_stats_query = "
        SELECT
            COUNT(DISTINCT c.CirculationID) as books_issued,
            COUNT(DISTINCT CASE WHEN DATEDIFF(c.DueDate, CURDATE()) <= 7 AND DATEDIFF(c.DueDate, CURDATE()) >= 0 THEN c.CirculationID END) as books_due
        FROM Circulation c
        WHERE c.MemberNo = :member_no AND c.Status = 'Active'
    ";
    $stmt = $pdo->prepare($quick_stats_query);
    $stmt->execute(['member_no' => $member_no]);
    $quick_stats = $stmt->fetch(PDO::FETCH_ASSOC);

    $fines_query = "
        SELECT
            c.CirculationID,
            CASE
                WHEN r.CirculationID IS NOT NULL THEN r.FineAmount
                ELSE GREATEST(DATEDIFF(CURDATE(), c.DueDate), 0) * COALESCE(m.FinePerDay, 2)
            END AS CalculatedFine,
            COALESCE(SUM(fp.PaidAmount), 0) AS PaidAmount
        FROM Circulation c
        INNER JOIN Member m ON c.MemberNo = m.MemberNo
        LEFT JOIN `Return` r ON c.CirculationID = r.CirculationID
        LEFT JOIN FinePayments fp ON c.CirculationID = fp.CirculationID
        WHERE c.MemberNo = :member_no
        AND ((r.CirculationID IS NOT NULL AND r.FineAmount > 0) OR (r.CirculationID IS NULL AND c.DueDate < CURDATE()))
        GROUP BY c.CirculationID, m.FinePerDay, r.CirculationID, r.FineAmount
        HAVING CalculatedFine > PaidAmount
    ";
    $stmt = $pdo->prepare($fines_query);
    $stmt->execute(['member_no' => $member_no]);
    $fines_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $pending_fines_total = 0;
    foreach ($fines_rows as $fine_row) {
        $pending_fines_total += ((float)$fine_row['CalculatedFine'] - (float)$fine_row['PaidAmount']);
    }
    $quick_stats['pending_fines'] = (int)round($pending_fines_total);

    $upcoming_due_query = "
        SELECT b.Title, b.Author1, c.DueDate, DATEDIFF(c.DueDate, CURDATE()) as days_left
        FROM Circulation c
        INNER JOIN Holding h ON c.AccNo = h.AccNo
        INNER JOIN Books b ON h.CatNo = b.CatNo
        WHERE c.MemberNo = :member_no AND c.Status = 'Active'
        AND DATEDIFF(c.DueDate, CURDATE()) <= 7 AND DATEDIFF(c.DueDate, CURDATE()) >= 0
        ORDER BY c.DueDate ASC LIMIT 5
    ";
    $stmt = $pdo->prepare($upcoming_due_query);
    $stmt->execute(['member_no' => $member_no]);
    $upcoming_due = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $quick_stats = ['books_issued' => 0, 'books_due' => 0, 'pending_fines' => 0];
    $upcoming_due = [];
}

$notifications = [];
try {
    $overdue_query = "SELECT COUNT(*) as cnt FROM Circulation WHERE MemberNo = ? AND Status = 'Active' AND DueDate < CURDATE()";
    $stmt = $pdo->prepare($overdue_query);
    $stmt->execute([$member_no]);
    $overdue_count = (int)$stmt->fetchColumn();

    if ($overdue_count > 0) {
        $notifications[] = ['type' => 'warning', 'message' => "You have {$overdue_count} overdue book(s). Please return them to avoid fines."];
    }
    if ($quick_stats['pending_fines'] > 0) {
        $notifications[] = ['type' => 'warning', 'message' => 'You have pending fines of ₹' . $quick_stats['pending_fines'] . '. Please clear them at the circulation desk.'];
    }

    $due_soon_query = "SELECT b.Title, c.DueDate, DATEDIFF(c.DueDate, CURDATE()) as days_left FROM Circulation c INNER JOIN Holding h ON c.AccNo = h.AccNo INNER JOIN Books b ON h.CatNo = b.CatNo WHERE c.MemberNo = ? AND c.Status = 'Active' AND DATEDIFF(c.DueDate, CURDATE()) BETWEEN 0 AND 3 ORDER BY c.DueDate ASC LIMIT 2";
    $stmt = $pdo->prepare($due_soon_query);
    $stmt->execute([$member_no]);
    foreach ($stmt->fetchAll() as $book) {
        $notifications[] = ['type' => 'warning', 'message' => "Book \"" . htmlspecialchars($book['Title']) . "\" is due in {$book['days_left']} day(s)."];
    }

    if (empty($notifications)) {
        $notifications[] = ['type' => 'success', 'message' => 'All good! You have no overdue books or pending fines.'];
    }
} catch (Exception $e) {
    $notifications = [['type' => 'info', 'message' => 'Welcome to your dashboard!']];
}
?>
<style>
    .dashboard-header { margin-bottom: 30px; padding-bottom: 15px; border-bottom: 2px solid #cfac69; }
    .dashboard-title { color: #263c79; font-size: 28px; font-weight: 700; margin-bottom: 5px; }
    .dashboard-subtitle { color: #666; font-size: 16px; margin: 0; }
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 25px; margin-bottom: 30px; }
    .stat-card {
        background: white; border: 1px solid #e9ecef; border-radius: 12px; padding: 25px;
        text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        border-left: 4px solid #cfac69; height: 120px;
    }
    .stat-card.success { border-left-color: #28a745; }
    .stat-card.danger { border-left-color: #dc3545; }
    .stat-number { font-size: 26px; font-weight: 700; color: #263c79; margin-bottom: 8px; display: block; }
    .stat-label { color: #666; font-size: 14px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
    .dashboard-section { background: white; border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 25px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .section-header { background: #f8f9fa; padding: 15px 20px; border-bottom: 1px solid #e0e0e0; }
    .section-title { color: #263c79; font-size: 18px; font-weight: 600; margin: 0; }
    .section-content { padding: 20px; }
    .notification { padding: 12px 15px; border-radius: 6px; margin-bottom: 10px; display: flex; align-items: center; gap: 10px; }
    .notification.warning { background: #fff3cd; border-left: 4px solid #ffc107; color: #856404; }
    .notification.success { background: #d4edda; border-left: 4px solid #28a745; color: #155724; }
    .notification.info { background: #d1ecf1; border-left: 4px solid #17a2b8; color: #0c5460; }
    .dashboard-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
    .due-item { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f0f0f0; }
    .due-item:last-child { border-bottom: none; }
    .due-title { font-weight: 600; color: #263c79; }
    .due-author { color: #666; font-size: 14px; }
    .due-date { color: #888; font-size: 13px; }
    .due-status { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; }
    .due-urgent { background: #ffe6e6; color: #d63384; }
    .due-soon { background: #fff3cd; color: #856404; }
    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 15px; }
        .dashboard-grid { grid-template-columns: 1fr; }
    }
</style>
<div class="dashboard-header">
    <h1 class="dashboard-title">Welcome, <?php echo htmlspecialchars($faculty_name); ?>!</h1>
    <p class="dashboard-subtitle"><?php echo htmlspecialchars($faculty_designation); ?> | <?php echo htmlspecialchars($faculty_department); ?> | Last Login: <?php echo date('F j, Y g:i A'); ?></p>
</div>
<div class="stats-grid">
    <div class="stat-card success"><span class="stat-number"><?php echo $quick_stats['books_issued'] ?? 0; ?></span><div class="stat-label">Books Issued</div></div>
    <div class="stat-card danger"><span class="stat-number"><?php echo $quick_stats['books_due'] ?? 0; ?></span><div class="stat-label">Due Soon</div></div>
    <div class="stat-card"><span class="stat-number">₹<?php echo $quick_stats['pending_fines'] ?? 0; ?></span><div class="stat-label">Pending Fines</div></div>
    <div class="stat-card"><span class="stat-number"><?php echo $_SESSION['books_issued'] ?? 0; ?>/5</span><div class="stat-label">Books Limit</div></div>
</div>
<div class="dashboard-section">
    <div class="section-header"><h3 class="section-title"><i class="fas fa-bell" style="margin-right:8px;"></i>Notifications</h3></div>
    <div class="section-content">
        <?php foreach ($notifications as $n): ?>
            <div class="notification <?php echo $n['type']; ?>">
                <i class="fas fa-<?php echo $n['type'] == 'warning' ? 'exclamation-triangle' : ($n['type'] == 'info' ? 'info-circle' : 'check-circle'); ?>"></i>
                <?php echo htmlspecialchars($n['message']); ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<div class="dashboard-grid">
    <div class="dashboard-section">
        <div class="section-header"><h3 class="section-title"><i class="fas fa-clock" style="margin-right:8px;"></i>Books Due Soon</h3></div>
        <div class="section-content">
            <?php if (!empty($upcoming_due)): ?>
                <?php foreach ($upcoming_due as $book): ?>
                    <div class="due-item">
                        <div class="due-details">
                            <div class="due-title"><?php echo htmlspecialchars($book['Title']); ?></div>
                            <div class="due-author">by <?php echo htmlspecialchars($book['Author1']); ?></div>
                        </div>
                        <div style="text-align:right;">
                            <div class="due-status <?php echo $book['days_left'] <= 2 ? 'due-urgent' : 'due-soon'; ?>"><?php echo $book['days_left']; ?> days left</div>
                            <div class="due-date">Due: <?php echo date('M j', strtotime($book['DueDate'])); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color:#666;">No books due soon.</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="dashboard-section">
        <div class="section-header"><h3 class="section-title"><i class="fas fa-info-circle" style="margin-right:8px;"></i>Quick Links</h3></div>
        <div class="section-content">
            <p style="margin-bottom:10px;color:#666;">Use the sidebar to navigate:</p>
            <ul style="list-style:none;padding:0;">
                <li style="padding:8px 0;border-bottom:1px solid #f0f0f0;"><i class="fas fa-book" style="color:#cfac69;margin-right:10px;"></i><strong>My Books</strong> - View currently issued books</li>
                <li style="padding:8px 0;border-bottom:1px solid #f0f0f0;"><i class="fas fa-history" style="color:#cfac69;margin-right:10px;"></i><strong>Borrowing History</strong> - Past borrowing records</li>
                <li style="padding:8px 0;"><i class="fas fa-search" style="color:#cfac69;margin-right:10px;"></i><strong>Search Books</strong> - Search the library catalog</li>
            </ul>
        </div>
    </div>
</div>
