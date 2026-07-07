<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['faculty_id'])) {
    header('Location: faculty_login.php');
    exit();
}
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$member_no = $_SESSION['member_no'] ?? null;
$issued_books = [];
try {
    $activeCirculations = getMemberActiveCirculations($pdo, $member_no);
    foreach ($activeCirculations as $circ) {
        $daysLeft = ceil((strtotime($circ['DueDate']) - time()) / (60 * 60 * 24));
        $fine = 0;
        if ($daysLeft < 0) {
            $member = getMemberByNo($pdo, $member_no);
            $fine = abs($daysLeft) * ($member['FinePerDay'] ?? 2.00);
        }
        $issued_books[] = [
            'acc_no' => $circ['AccNo'],
            'title' => $circ['Title'] ?? 'Unknown',
            'author' => $circ['Author1'] ?? 'Unknown',
            'issue_date' => $circ['IssueDate'],
            'due_date' => $circ['DueDate'],
            'days_left' => $daysLeft,
            'fine' => $fine,
            'renewal_count' => $circ['RenewalCount'] ?? 0,
            'circulation_id' => $circ['CirculationID']
        ];
    }
} catch (Exception $e) {
    error_log("My books error: " . $e->getMessage());
}
?>
<style>
    .page-title { color: #263c79; font-size: 24px; font-weight: 700; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #cfac69; }
    .books-table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .books-table th { background: #263c79; color: white; padding: 12px; text-align: left; font-weight: 600; }
    .books-table td { padding: 12px; border-bottom: 1px solid #e9ecef; }
    .books-table tr:hover { background: rgba(207,172,105,0.1); }
    .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500; }
    .status-available { background: #d4edda; color: #155724; }
    .status-overdue { background: #f8d7da; color: #721c24; }
    .status-warning { background: #fff3cd; color: #856404; }
    .empty-state { text-align: center; padding: 60px 20px; color: #6c757d; }
    .empty-state i { font-size: 48px; margin-bottom: 15px; opacity: 0.3; }
</style>
<h2 class="page-title"><i class="fas fa-book"></i> My Books (Currently Issued)</h2>
<?php if (empty($issued_books)): ?>
    <div class="empty-state">
        <i class="fas fa-book-open"></i>
        <h3>No Books Issued</h3>
        <p>You have no books currently issued. Visit the library to borrow books.</p>
    </div>
<?php else: ?>
    <div style="overflow-x:auto;">
        <table class="books-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Accession No</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Issue Date</th>
                    <th>Due Date</th>
                    <th>Days Left</th>
                    <th>Fine</th>
                    <th>Renewals</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($issued_books as $book): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo htmlspecialchars($book['acc_no']); ?></td>
                        <td><strong><?php echo htmlspecialchars($book['title']); ?></strong></td>
                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                        <td><?php echo date('d-m-Y', strtotime($book['issue_date'])); ?></td>
                        <td><?php echo date('d-m-Y', strtotime($book['due_date'])); ?></td>
                        <td>
                            <?php if ($book['days_left'] < 0): ?>
                                <span class="status-badge status-overdue"><?php echo abs($book['days_left']); ?> days overdue</span>
                            <?php elseif ($book['days_left'] <= 3): ?>
                                <span class="status-badge status-warning"><?php echo $book['days_left']; ?> days left</span>
                            <?php else: ?>
                                <span class="status-badge status-available"><?php echo $book['days_left']; ?> days left</span>
                            <?php endif; ?>
                        </td>
                        <td>₹<?php echo number_format($book['fine'], 2); ?></td>
                        <td><?php echo $book['renewal_count']; ?>/2</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
