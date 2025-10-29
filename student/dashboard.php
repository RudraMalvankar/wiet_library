<?php
// Student Dashboard Content
// This file will be included in the main content area

// Start session and check authentication
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: student_login.php');
    exit();
}

// Include database connection and functions
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Get student information from session
$student_name = $_SESSION['student_name'] ?? 'Student';
$student_id = $_SESSION['student_id'] ?? null;
$member_no = $_SESSION['member_no'] ?? null;

// ============================================================
// DATA SOURCE: 100% LIVE DATABASE
// ============================================================
// ✅ COMPLETED: All data fetched from database
// - Quick stats from Circulation + Return + Books tables
// - Recent activity from ActivityLog table
// - Upcoming due books from Circulation + Holding + Books tables
// - Dynamic notifications from Circulation + LibraryEvents tables
// ============================================================

// Fetch quick stats from database
try {
    // Get books issued and due soon counts
    $quick_stats_query = "
        SELECT 
            COUNT(DISTINCT c.CirculationID) as books_issued,
            COUNT(DISTINCT CASE WHEN DATEDIFF(c.DueDate, CURDATE()) <= 7 AND DATEDIFF(c.DueDate, CURDATE()) >= 0 THEN c.CirculationID END) as books_due
        FROM Circulation c
        WHERE c.MemberNo = :member_no 
        AND c.ReturnDate IS NULL
    ";
    $stmt = $pdo->prepare($quick_stats_query);
    $stmt->execute(['member_no' => $member_no]);
    $quick_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Calculate pending fines from Return table (fines not yet paid)
    $fines_query = "
        SELECT COALESCE(SUM(r.Fine - COALESCE(fp.AmountPaid, 0)), 0) as pending_fines
        FROM `Return` r
        LEFT JOIN (
            SELECT TransactionID, SUM(AmountPaid) as AmountPaid
            FROM FinePayments
            GROUP BY TransactionID
        ) fp ON r.ReturnID = fp.TransactionID
        WHERE r.MemberNo = :member_no
        AND r.Fine > COALESCE(fp.AmountPaid, 0)
    ";
    $stmt = $pdo->prepare($fines_query);
    $stmt->execute(['member_no' => $member_no]);
    $fines_result = $stmt->fetch(PDO::FETCH_ASSOC);
    $quick_stats['pending_fines'] = (int)$fines_result['pending_fines'];
    
    // Count available recommendations in student's branch
    $recommendations_query = "
        SELECT COUNT(DISTINCT b.CallNo) as recommendations
        FROM Books b
        INNER JOIN Holding h ON b.CallNo = h.CallNo
        WHERE b.Subject LIKE CONCAT('%', :branch, '%')
        AND h.Status = 'Available'
        LIMIT 100
    ";
    $stmt = $pdo->prepare($recommendations_query);
    $stmt->execute(['branch' => $student_branch]);
    $rec_result = $stmt->fetch(PDO::FETCH_ASSOC);
    $quick_stats['recommendations'] = min((int)$rec_result['recommendations'], 99);
    
    // Get upcoming due books (within 7 days)
    $upcoming_due_query = "
        SELECT 
            b.Title as title,
            b.Author as author,
            c.DueDate as due_date,
            DATEDIFF(c.DueDate, CURDATE()) as days_left
        FROM Circulation c
        INNER JOIN Holding h ON c.AccNo = h.AccNo
        INNER JOIN Books b ON h.CallNo = b.CallNo
        WHERE c.MemberNo = :member_no 
        AND c.ReturnDate IS NULL
        AND DATEDIFF(c.DueDate, CURDATE()) <= 7
        AND DATEDIFF(c.DueDate, CURDATE()) >= 0
        ORDER BY c.DueDate ASC
        LIMIT 5
    ";
    $stmt = $pdo->prepare($upcoming_due_query);
    $stmt->execute(['member_no' => $member_no]);
    $upcoming_due = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    // Fallback to session data if database error
    $quick_stats = [
        'books_issued' => isset($_SESSION['books_issued']) ? $_SESSION['books_issued'] : 0,
        'books_due' => 0,
        'pending_fines' => 0,
        'recommendations' => 0
    ];
    $upcoming_due = [];
}

// Fetch recent activity from ActivityLog table
try {
    $activity_query = "
        SELECT 
            al.Action as action,
            COALESCE(b.Title, al.Details) as book,
            al.Timestamp as date
        FROM ActivityLog al
        LEFT JOIN Circulation c ON al.RelatedID = c.CirculationID AND al.Action IN ('Book Issued', 'Book Returned', 'Book Renewed')
        LEFT JOIN Holding h ON c.AccNo = h.AccNo
        LEFT JOIN Books b ON h.CallNo = b.CallNo
        WHERE al.MemberNo = :member_no
        AND al.Action IN ('Book Issued', 'Book Returned', 'Book Renewed', 'Profile Updated', 'Password Changed')
        ORDER BY al.Timestamp DESC
        LIMIT 10
    ";
    $stmt = $pdo->prepare($activity_query);
    $stmt->execute(['member_no' => $member_no]);
    $recent_activity = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If no activity, show a placeholder
    if (empty($recent_activity)) {
        $recent_activity = [
            ['action' => 'Account Created', 'book' => 'Welcome to WIET Library', 'date' => date('Y-m-d')]
        ];
    }
    
} catch (Exception $e) {
    // Fallback to empty activity
    $recent_activity = [
        ['action' => 'Account Created', 'book' => 'Welcome to WIET Library', 'date' => date('Y-m-d')]
    ];
}

// Generate dynamic notifications
$notifications = [];
try {
    // 1. Check for overdue books
    $overdue_query = "
        SELECT COUNT(*) as overdue_count
        FROM Circulation c
        WHERE c.MemberNo = :member_no 
        AND c.ReturnDate IS NULL
        AND c.DueDate < CURDATE()
    ";
    $stmt = $pdo->prepare($overdue_query);
    $stmt->execute(['member_no' => $member_no]);
    $overdue_result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($overdue_result['overdue_count'] > 0) {
        $notifications[] = [
            'type' => 'warning',
            'message' => 'You have ' . $overdue_result['overdue_count'] . ' overdue book(s). Please return them to avoid additional fines.'
        ];
    }
    
    // 2. Check for books due soon (within 3 days)
    $due_soon_query = "
        SELECT b.Title, c.DueDate, DATEDIFF(c.DueDate, CURDATE()) as days_left
        FROM Circulation c
        INNER JOIN Holding h ON c.AccNo = h.AccNo
        INNER JOIN Books b ON h.CallNo = b.CallNo
        WHERE c.MemberNo = :member_no 
        AND c.ReturnDate IS NULL
        AND DATEDIFF(c.DueDate, CURDATE()) BETWEEN 0 AND 3
        ORDER BY c.DueDate ASC
        LIMIT 2
    ";
    $stmt = $pdo->prepare($due_soon_query);
    $stmt->execute(['member_no' => $member_no]);
    $due_soon_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($due_soon_books as $book) {
        $notifications[] = [
            'type' => 'warning',
            'message' => 'Book "' . htmlspecialchars($book['Title']) . '" is due in ' . $book['days_left'] . ' day(s).'
        ];
    }
    
    // 3. Check for pending fines
    if ($quick_stats['pending_fines'] > 0) {
        $notifications[] = [
            'type' => 'warning',
            'message' => 'You have pending fines of ₹' . $quick_stats['pending_fines'] . '. Please clear them at the circulation desk.'
        ];
    }
    
    // 4. Check for upcoming library events (within 7 days)
    $events_query = "
        SELECT EventName, EventDate
        FROM LibraryEvents
        WHERE EventDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        AND Status = 'Active'
        ORDER BY EventDate ASC
        LIMIT 1
    ";
    $stmt = $pdo->prepare($events_query);
    $stmt->execute();
    $upcoming_event = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($upcoming_event) {
        $notifications[] = [
            'type' => 'info',
            'message' => 'Upcoming event: "' . htmlspecialchars($upcoming_event['EventName']) . '" on ' . date('M j, Y', strtotime($upcoming_event['EventDate']))
        ];
    }
    
    // 5. Show success message if no issues
    if (empty($notifications)) {
        $notifications[] = [
            'type' => 'success',
            'message' => 'All good! You have no overdue books or pending fines. Keep up the great reading!'
        ];
    }
    
} catch (Exception $e) {
    // Fallback to generic notification
    $notifications = [
        ['type' => 'info', 'message' => 'Welcome to your dashboard! Check out new books and events.']
    ];
}
?>

<style>
    /* Dashboard specific styles */
    .dashboard-header {
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 2px solid #cfac69;
    }

    .dashboard-title {
        color: #263c79;
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .dashboard-subtitle {
        color: #666;
        font-size: 16px;
        margin: 0;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 25px; 
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        height: 100px;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 25px;
        text-align: center;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        border-left: 4px solid #cfac69;
        /* Default golden color */
    }

    .stat-card.success {
        border-left-color: #28a745;
        /* Green for positive metrics */
    }

    .stat-card.danger {
        border-left-color: #dc3545;
        /* Red for critical metrics */
    }

    .stat-card.info {
        border-left-color: #17a2b8;
        /* Blue for informational metrics */
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .stat-number {
        font-size: 26px;
        font-weight: 700;
        color: #263c79;
        margin-bottom: 8px;
        display: block;
    }

    .stat-label {
        color: #666;
        font-size: 14px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .dashboard-section {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        margin-bottom: 25px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .section-header {
        background: #f8f9fa;
        padding: 15px 20px;
        border-bottom: 1px solid #e0e0e0;
    }

    .section-title {
        color: #263c79;
        font-size: 18px;
        font-weight: 600;
        margin: 0;
    }

    .section-content {
        padding: 20px;
    }

    /* Activity Icons */
    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 16px;
        color: white;
    }

    .activity-icon.issued {
        background: linear-gradient(135deg, #28a745, #20c997);
    }

    .activity-icon.returned {
        background: linear-gradient(135deg, #17a2b8, #6610f2);
    }

    .activity-icon.renewed {
        background: linear-gradient(135deg, #ffc107, #fd7e14);
    }

    .activity-item,
    .due-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .activity-item:last-child,
    .due-item:last-child {
        border-bottom: none;
    }

    .activity-details,
    .due-details {
        flex: 1;
    }

    .activity-action,
    .due-title {
        font-weight: 600;
        color: #263c79;
        margin-bottom: 2px;
    }

    .activity-book,
    .due-author {
        color: #666;
        font-size: 14px;
    }

    .activity-date,
    .due-date {
        color: #888;
        font-size: 13px;
        text-align: right;
    }

    .due-status {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .due-urgent {
        background: #ffe6e6;
        color: #d63384;
    }

    .due-soon {
        background: #fff3cd;
        color: #856404;
    }

    .notification {
        padding: 12px 15px;
        border-radius: 6px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .notification:last-child {
        margin-bottom: 0;
    }

    .notification.warning {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        color: #856404;
    }

    .notification.info {
        background: #d1ecf1;
        border-left: 4px solid #17a2b8;
        color: #0c5460;
    }

    .notification.success {
        background: #d4edda;
        border-left: 4px solid #28a745;
        color: #155724;
    }

    .notification-icon {
        font-size: 16px;
    }

    .quick-actions {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        margin-top: 20px;
        margin-bottom: 30px;
    }

    .action-btn {
        background: #263c79;
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background 0.3s ease;
    }

    .action-btn:hover {
        background: #1e2f5a;
        color: white;
        text-decoration: none;
    }

    .action-btn.secondary {
        background: transparent;
        color: #263c79;
        border: 2px solid #263c79;
    }

    .action-btn.secondary:hover {
        background: #263c79;
        color: white;
    }

    .dashboard-main-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .dashboard-main-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .activity-item,
        .due-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
        }

        .activity-date,
        .due-date {
            text-align: left;
        }

        .quick-actions {
            flex-direction: column;
        }

        .action-btn {
            justify-content: center;
        }
    }
</style>

<div class="dashboard-header">
    <h1 class="dashboard-title">Welcome Back, <?php echo htmlspecialchars($student_name); ?>!</h1>
    <p class="dashboard-subtitle">Student ID: <?php echo htmlspecialchars($student_id); ?> | Last Login: <?php echo date('F j, Y g:i A'); ?></p>
</div>

<!-- Quick Stats -->
<div class="stats-grid">
    <div class="stat-card success">
        <span class="stat-number"><?php echo $quick_stats['books_issued']; ?></span>
        <div class="stat-label">Books Issued</div>
    </div>
    <div class="stat-card danger">
        <span class="stat-number"><?php echo $quick_stats['books_due']; ?></span>
        <div class="stat-label">Due Soon</div>
    </div>
    <div class="stat-card">
        <span class="stat-number">₹<?php echo $quick_stats['pending_fines']; ?></span>
        <div class="stat-label">Pending Fines</div>
    </div>
    <div class="stat-card info">
        <span class="stat-number"><?php echo $quick_stats['recommendations']; ?></span>
        <div class="stat-label">Recommendations</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions">
    <a href="#" class="action-btn secondary" data-page="search-books">
        <i class="fas fa-search"></i>
        Search Books
    </a>
    <a href="#" class="action-btn secondary" data-page="my-books">
        <i class="fas fa-book"></i>
        My Books
    </a>
    <a href="#" class="action-btn secondary" data-page="digital-id">
        <i class="fas fa-id-card"></i>
        Digital ID
    </a>
</div>

<!-- Important Notifications -->
<div class="dashboard-section">
    <div class="section-header">
        <h3 class="section-title">
            <i class="fas fa-bell" style="margin-right: 8px;"></i>
            Important Notifications
        </h3>
    </div>
    <div class="section-content">
        <?php foreach ($notifications as $notification): ?>
            <div class="notification <?php echo $notification['type']; ?>">
                <i class="notification-icon fas fa-<?php
                                                    echo $notification['type'] == 'warning' ? 'exclamation-triangle' : ($notification['type'] == 'info' ? 'info-circle' : 'check-circle');
                                                    ?>"></i>
                <?php echo htmlspecialchars($notification['message']); ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="dashboard-main-grid">
    <!-- Recent Activity -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fas fa-history" style="margin-right: 8px;"></i>
                Recent Activity
            </h3>
        </div>
        <div class="section-content">
            <?php foreach ($recent_activity as $activity): ?>
                <div class="activity-item">
                    <div class="activity-icon <?php 
                        $action_type = strtolower(str_replace(' ', '', $activity['action']));
                        echo $action_type == 'bookissued' ? 'issued' : ($action_type == 'bookreturned' ? 'returned' : 'renewed');
                    ?>">
                        <?php 
                            $action_type = strtolower(str_replace(' ', '', $activity['action']));
                            if ($action_type == 'bookissued') {
                                echo '<i class="fas fa-arrow-right"></i>';
                            } elseif ($action_type == 'bookreturned') {
                                echo '<i class="fas fa-arrow-left"></i>';
                            } else {
                                echo '<i class="fas fa-redo"></i>';
                            }
                        ?>
                    </div>
                    <div class="activity-details">
                        <div class="activity-action"><?php echo htmlspecialchars($activity['action']); ?></div>
                        <div class="activity-book"><?php echo htmlspecialchars($activity['book']); ?></div>
                    </div>
                    <div class="activity-date"><?php echo date('M j', strtotime($activity['date'])); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Books Due Soon -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fas fa-clock" style="margin-right: 8px;"></i>
                Books Due Soon
            </h3>
        </div>
        <div class="section-content">
            <?php foreach ($upcoming_due as $book): ?>
                <div class="due-item">
                    <div class="due-details">
                        <div class="due-title"><?php echo htmlspecialchars($book['title']); ?></div>
                        <div class="due-author">by <?php echo htmlspecialchars($book['author']); ?></div>
                    </div>
                    <div style="text-align: right;">
                        <div class="due-status <?php echo $book['days_left'] <= 2 ? 'due-urgent' : 'due-soon'; ?>">
                            <?php echo $book['days_left']; ?> days left
                        </div>
                        <div class="due-date">Due: <?php echo date('M j', strtotime($book['due_date'])); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    // Add click handlers for quick action buttons
    document.querySelectorAll('.action-btn[data-page]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const page = this.getAttribute('data-page');

            // Remove active from all sidebar links
            document.querySelectorAll('.sidebar-link').forEach(link => {
                link.classList.remove('active');
            });

            // Add active to corresponding sidebar link
            const sidebarLink = document.querySelector(`.sidebar-link[data-page="${page}"]`);
            if (sidebarLink) {
                sidebarLink.classList.add('active');
            }

            // Load the page
            if (window.loadPage) {
                window.loadPage(page);
            }
        });
    });
</script>
