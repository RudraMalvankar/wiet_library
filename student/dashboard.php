<?php
// Student Dashboard Content
// This file will be included in the main content area

// Start session for user authentication
session_start();

// Include database connection and functions
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Get student information from session
$student_name = isset($_SESSION['student_name']) ? $_SESSION['student_name'] : "John Doe";
$student_id = isset($_SESSION['student_id']) ? $_SESSION['student_id'] : "STU2024001";
$member_no = isset($_SESSION['MemberNo']) ? $_SESSION['MemberNo'] : 2511;

// ============================================================
// DATA SOURCE: MIXED (Database + Dummy)
// ============================================================
// TODO: Full database integration needed for:
// - Recent activity from ActivityLog table
// - Upcoming due books from Circulation table
// - Notifications from Notifications table
// ============================================================

// Try to fetch real data from database, fallback to dummy if not available
try {
    // Get real member statistics
    $member = getMemberByNo($pdo, $member_no);
    $activeBooks = getMemberActiveCirculations($pdo, $member_no);
    
    $quick_stats = [
        'books_issued' => count($activeBooks),
        'books_due' => 0, // TODO: Calculate from DueDate
        'pending_fines' => 0, // TODO: Calculate from Return table
        'recommendations' => 0 // TODO: Get from Recommendations table
    ];
    
    // Calculate books due soon (within 3 days)
    foreach ($activeBooks as $book) {
        $daysLeft = (strtotime($book['DueDate']) - time()) / (60 * 60 * 24);
        if ($daysLeft <= 3 && $daysLeft >= 0) {
            $quick_stats['books_due']++;
        }
    }
    
    $upcoming_due = [];
    foreach ($activeBooks as $book) {
        $daysLeft = ceil((strtotime($book['DueDate']) - time()) / (60 * 60 * 24));
        $upcoming_due[] = [
            'title' => $book['Title'],
            'author' => $book['Author1'],
            'due_date' => $book['DueDate'],
            'days_left' => $daysLeft
        ];
    }
    
} catch (Exception $e) {
    // Fallback to dummy data if database error
    $quick_stats = [
        'books_issued' => 3,
        'books_due' => 1,
        'pending_fines' => 0,
        'recommendations' => 2
    ];
    
    $upcoming_due = [
        ['title' => 'Database Management Systems', 'author' => 'Ramez Elmasri', 'due_date' => '2025-09-25', 'days_left' => 2],
        ['title' => 'Software Engineering', 'author' => 'Ian Sommerville', 'due_date' => '2025-09-28', 'days_left' => 5]
    ];
}

// TODO: Fetch from ActivityLog table
$recent_activity = [
    ['action' => 'Book Issued', 'book' => 'Data Structures and Algorithms', 'date' => '2025-09-20'],
    ['action' => 'Book Returned', 'book' => 'Computer Networks', 'date' => '2025-09-18'],
    ['action' => 'Book Renewed', 'book' => 'Operating Systems', 'date' => '2025-09-15']
];

// TODO: Fetch from Notifications table
$notifications = [
    ['type' => 'warning', 'message' => 'Book "Database Management Systems" is due in 2 days'],
    ['type' => 'info', 'message' => 'New arrivals: 15 books added to Computer Science section'],
    ['type' => 'success', 'message' => 'Your recommendation for "Clean Code" has been approved']
];
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