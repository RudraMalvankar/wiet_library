<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['faculty_id'])) {
    header('Location: faculty_login.php');
    exit();
}
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$member_no = $_SESSION['member_no'] ?? null;
$limit = $_GET['limit'] ?? 50;
$history = [];

try {
    $stmt = $pdo->prepare("
        SELECT c.CirculationID, c.IssueDate, c.DueDate, c.Status, c.RenewalCount,
               h.AccNo, b.Title, b.Author1, b.Publisher,
               r.ReturnDate, r.FineAmount, r.FinePaid, r.`Condition`
        FROM Circulation c
        JOIN Holding h ON c.AccNo = h.AccNo
        JOIN Books b ON h.CatNo = b.CatNo
        LEFT JOIN `Return` r ON c.CirculationID = r.CirculationID
        WHERE c.MemberNo = ?
        ORDER BY c.IssueDate DESC
        LIMIT ?
    ");
    $stmt->execute([$member_no, (int)$limit]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Borrowing history error: " . $e->getMessage());
}
?>
<style>
    .page-title { color: #263c79; font-size: 24px; font-weight: 700; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #cfac69; }
    .history-table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .history-table th { background: #263c79; color: white; padding: 12px; text-align: left; font-weight: 600; }
    .history-table td { padding: 12px; border-bottom: 1px solid #e9ecef; }
    .history-table tr:hover { background: rgba(207,172,105,0.1); }
    .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500; }
    .status-returned { background: #d4edda; color: #155724; }
    .status-active { background: #d1ecf1; color: #0c5460; }
    .empty-state { text-align: center; padding: 60px 20px; color: #6c757d; }
    .empty-state i { font-size: 48px; margin-bottom: 15px; opacity: 0.3; }
</style>
<h2 class="page-title"><i class="fas fa-history"></i> Borrowing History</h2>
<?php if (empty($history)): ?>
    <div class="empty-state">
        <i class="fas fa-history"></i>
        <h3>No Borrowing History</h3>
        <p>No borrowing records found for your account.</p>
    </div>
<?php else: ?>
    <div style="overflow-x:auto;">
        <table class="history-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Acc No</th>
                    <th>Issue Date</th>
                    <th>Due Date</th>
                    <th>Return Date</th>
                    <th>Fine</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $row): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($row['Title'] ?? 'Unknown'); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['Author1'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($row['AccNo']); ?></td>
                        <td><?php echo date('d-m-Y', strtotime($row['IssueDate'])); ?></td>
                        <td><?php echo date('d-m-Y', strtotime($row['DueDate'])); ?></td>
                        <td><?php echo $row['ReturnDate'] ? date('d-m-Y', strtotime($row['ReturnDate'])) : '-'; ?></td>
                        <td>₹<?php echo number_format($row['FineAmount'] ?? 0, 2); ?></td>
                        <td>
                            <?php if ($row['Status'] === 'Returned'): ?>
                                <span class="status-badge status-returned">Returned</span>
                            <?php else: ?>
                                <span class="status-badge status-active"><?php echo htmlspecialchars($row['Status']); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
