<?php
// Admin Dashboard Content - Library Administration Staff
// This file will be included in the main-content area of admin/layout.php

session_start();

// Include database connection and functions
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Admin information
$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : "Library Admin";
$admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 1;
$is_superadmin = $_SESSION['is_superadmin'] ?? false;

// Set display name for dashboard header
$display_name = $is_superadmin ? "Super Admin" : $admin_name;

// Add last login information
$last_login = isset($_SESSION['last_login']) ? $_SESSION['last_login'] : date("Y-m-d H:i:s");

// Dashboard Statistics - Fetch from database
try {
    $quick_stats = getDashboardStats($pdo);
    
    // Add additional stats
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM LibraryEvents WHERE MONTH(EventDate) = MONTH(CURDATE())");
    $quick_stats['events_this_month'] = $stmt->fetch()['count'] ?? 0;
    
    // Rename keys to match existing code
    $quick_stats['total_books'] = $quick_stats['totalBooks'] ?? 0;
    $quick_stats['total_copies'] = $quick_stats['totalCopies'] ?? 0;
    $quick_stats['active_members'] = $quick_stats['activeMembers'] ?? 0;
    $quick_stats['books_issued'] = $quick_stats['booksIssued'] ?? 0;
    $quick_stats['books_overdue'] = $quick_stats['overdueBooks'] ?? 0;
    $quick_stats['footfall_today'] = $quick_stats['todayFootfall'] ?? 0;
    $quick_stats['pending_acquisitions'] = 0; // Not yet implemented
    $quick_stats['dropbox_active'] = 0; // Not yet implemented
    $quick_stats['e_resources'] = 0; // Not yet implemented
    
} catch (Exception $e) {
    // Fallback to empty stats if database error
    $quick_stats = [
        'total_books' => 0,
        'total_copies' => 0,
        'active_members' => 0,
        'books_issued' => 0,
        'books_overdue' => 0,
        'pending_acquisitions' => 0,
        'dropbox_active' => 0,
        'events_this_month' => 0,
        'footfall_today' => 0,
        'e_resources' => 0
    ];
    error_log("Dashboard stats error: " . $e->getMessage());
}

// Recent Activities - Fetch from ActivityLog table
try {
    $stmt = $pdo->query("
        SELECT * FROM ActivityLog 
        ORDER BY Timestamp DESC 
        LIMIT 10
    ");
    $activities = $stmt->fetchAll();
    
    $recent_activities = [];
    foreach ($activities as $activity) {
        $recent_activities[] = [
            'type' => strtolower($activity['Action']),
            'action' => $activity['Action'],
            'details' => $activity['Details'] ?? '',
            'member' => $activity['UserID'] ?? 'System',
            'time' => $activity['Timestamp'],
            'status' => 'success'
        ];
    }
} catch (Exception $e) {
    $recent_activities = [];
}

// If no activities from database, use sample data
if (empty($recent_activities)) {
    $recent_activities = [
        ['type' => 'circulation', 'action' => 'Book Issued', 'details' => 'Operating Systems Concepts', 'member' => 'STU2024089', 'time' => date('Y-m-d H:i:s'), 'status' => 'success'],
        ['type' => 'circulation', 'action' => 'Book Returned', 'details' => 'Database Management Systems', 'member' => 'STU2024034', 'time' => date('Y-m-d H:i:s', strtotime('-1 hour')), 'status' => 'success']
    ];
}

// Critical Alerts - Things that need immediate attention
$critical_alerts = [];

if ($quick_stats['books_overdue'] > 0) {
    $critical_alerts[] = [
        'type' => 'overdue', 
        'title' => $quick_stats['books_overdue'] . ' Overdue Books', 
        'description' => 'Books not returned past due date', 
        'priority' => 'high', 
        'count' => $quick_stats['books_overdue'], 
        'action' => 'circulation'
    ];
}

// Note: recent activities and critical alerts should come from the database.
// We previously attempted to populate them above; do not overwrite with static/dummy arrays here.

// Popular Books - Most circulated (fetch from DB)
try {
    $stmt = $pdo->prepare("
        SELECT b.Title, b.Author1, COUNT(c.CirculationID) as circulation_count,
               COUNT(h.HoldID) as total_copies,
               SUM(CASE WHEN h.Status = 'Available' THEN 1 ELSE 0 END) as available_copies
        FROM Circulation c
        JOIN Holding h ON c.AccNo = h.AccNo
        JOIN Books b ON h.CatNo = b.CatNo
        GROUP BY b.CatNo
        ORDER BY circulation_count DESC
        LIMIT 4
    ");
    $stmt->execute();
    $popular_books = [];
    foreach ($stmt->fetchAll() as $row) {
        $popular_books[] = [
            'title' => $row['Title'],
            'author' => $row['Author1'] ?? 'Unknown',
            'circulation_count' => (int)$row['circulation_count'],
            'available_copies' => (int)($row['available_copies'] ?? 0),
            'total_copies' => (int)($row['total_copies'] ?? 1)
        ];
    }
} catch (Exception $e) {
    $popular_books = [];
}

// Active Borrowers
try {
    $stmt = $pdo->prepare("
        SELECT m.MemberNo, m.MemberName, m.BooksIssued, COUNT(c.CirculationID) as TotalIssues
        FROM Member m
        LEFT JOIN Circulation c ON m.MemberNo = c.MemberNo
        WHERE m.Status = 'Active' AND m.BooksIssued > 0
        GROUP BY m.MemberNo
        ORDER BY m.BooksIssued DESC
        LIMIT 4
    ");
    $stmt->execute();
    $active_borrowers = $stmt->fetchAll();
} catch (Exception $e) {
    $active_borrowers = [];
}

// System Health Metrics
$system_health = [
    'dropbox_connectivity' => 95,      // Percentage of DropBoxes online
    'database_performance' => 98,      // Response time metrics
    'storage_usage' => 67,             // Disk usage percentage
    'backup_status' => 100             // Last backup success rate
];
?>

<style>
    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .dashboard-header {
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 2px solid #cfac69;
        background: transparent;
        box-shadow: none;
        border-radius: 0;
        color: #263c79;
    }

    .dashboard-title {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        gap: 15px;
        color: #263c79;
    }

    .dashboard-subtitle {
        font-size: 16px;
        color: #666;
        margin: 0;
        opacity: 1;
    }

    .header-stats {
        display: flex;
        gap: 30px;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #e9ecef;
    }

    .header-stat {
        text-align: center;
    }

    .header-stat-number {
        font-size: 24px;
        font-weight: 700;
        color: #263c79;
    }

    .header-stat-label {
        font-size: 12px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 1px;
        opacity: 0.8;
    }

    /* Main Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
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

 
    .stat-card::before {
        display: none;
        /* Remove the top border since we now have left border */
    }

    .stat-icon {
        font-size: 32px;
        color: #263c79;
        margin-bottom: 15px;
        display: block;
    }

    .stat-number {
        font-size: 26px !important;
        font-weight: 700;
        color: #263c79;
        margin-bottom: 8px;
        display: block;
    }

    .stat-label {
        color: #666;
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-trend {
        font-size: 12px;
        margin-top: 8px;
        padding: 4px 8px;
        border-radius: 12px;
        display: inline-block;
    }

    .trend-up {
        background: rgba(40, 167, 69, 0.1);
        color: #28a745;
    }

    .trend-down {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    /* Dashboard Layout */
    .dashboard-row {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
        margin-bottom: 30px;
    }

    .dashboard-section {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
    }

    .section-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 20px 25px;
        border-bottom: 2px solid #cfac69;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .section-title {
        color: #263c79;
        font-size: 20px;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-action {
        color: #cfac69;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        padding: 8px 16px;
        border: 2px solid #cfac69;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .section-action:hover {
        background: #cfac69;
        color: white;
        text-decoration: none;
    }

    .section-content {
        padding: 25px;
        max-height: 400px;
        overflow-y: auto;
    }

    /* Activity Items */
    .activity-item {
        display: flex;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #f1f3f4;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

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

    .activity-icon.circulation {
        background: linear-gradient(135deg, #28a745, #20c997);
    }

    .activity-icon.dropbox {
        background: linear-gradient(135deg, #17a2b8, #6f42c1);
    }

    .activity-icon.member {
        background: linear-gradient(135deg, #007bff, #0056b3);
    }

    .activity-icon.acquisition {
        background: linear-gradient(135deg, #ffc107, #fd7e14);
    }

    .activity-icon.analytics {
        background: linear-gradient(135deg, #6c757d, #495057);
    }

    .activity-details {
        flex: 1;
    }

    .activity-action {
        font-weight: 600;
        color: #263c79;
        margin-bottom: 4px;
    }

    .activity-description {
        color: #666;
        font-size: 14px;
        margin-bottom: 4px;
    }

    .activity-meta {
        color: #999;
        font-size: 12px;
    }

    .activity-time {
        color: #666;
        font-size: 12px;
        text-align: right;
        min-width: 80px;
    }

    /* Alert Cards */
    .alerts-grid {
        display: grid;
        gap: 15px;
    }

    .alert-card {
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .alert-card:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .alert-high {
        background: rgba(220, 53, 69, 0.05);
        border-color: #dc3545;
    }

    .alert-medium {
        background: rgba(255, 193, 7, 0.05);
        border-color: #ffc107;
    }

    .alert-low {
        background: rgba(23, 162, 184, 0.05);
        border-color: #17a2b8;
    }

    .alert-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 8px;
    }

    .alert-title {
        font-weight: 600;
        color: #263c79;
        font-size: 16px;
    }

    .alert-count {
        background: #263c79;
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        margin-left: auto;
    }

    .alert-description {
        color: #666;
        font-size: 14px;
    }

    /* Popular Books */
    .book-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #f1f3f4;
    }

    .book-item:last-child {
        border-bottom: none;
    }

    .book-info {
        flex: 1;
    }

    .book-title {
        font-weight: 600;
        color: #263c79;
        margin-bottom: 4px;
    }

    .book-author {
        color: #666;
        font-size: 14px;
    }

    .book-stats {
        text-align: right;
        min-width: 100px;
    }

    .book-circulation {
        font-weight: 600;
        color: #cfac69;
        font-size: 14px;
    }

    .book-availability {
        font-size: 12px;
        color: #666;
    }

    /* Quick Actions */
    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .action-card {
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 25px;
        text-align: center;
        text-decoration: none;
        color: #263c79;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .action-card:hover {
        border-color: #cfac69;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(207, 172, 105, 0.2);
        text-decoration: none;
        color: #263c79;
    }

    .action-icon {
        font-size: 36px;
        color: #cfac69;
        margin-bottom: 15px;
        display: block;
    }

    .action-title {
        font-weight: 600;
        font-size: 16px;
        margin-bottom: 8px;
    }

    .action-description {
        color: #666;
        font-size: 14px;
    }

    /* System Health */
    .health-metrics {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .health-metric {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }

    .health-label {
        font-weight: 600;
        color: #263c79;
    }

    .health-value {
        font-weight: 700;
        color: #cfac69;
    }

    .health-bar {
        width: 60px;
        height: 6px;
        background: #e9ecef;
        border-radius: 3px;
        overflow: hidden;
        margin-left: 10px;
    }

    .health-progress {
        height: 100%;
        background: linear-gradient(90deg, #cfac69, #263c79);
        transition: width 0.3s ease;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .dashboard-row {
            grid-template-columns: 1fr;
        }

        .stats-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .quick-actions-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .dashboard-header {
            padding-bottom: 15px;
        }

        .dashboard-title {
            font-size: 24px;
        }

        .header-stats {
            flex-direction: column;
            gap: 15px;
        }
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .quick-actions-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="dashboard-container">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <h1 class="dashboard-title">
            <!-- <i class="fas fa-tachometer-alt"></i> -->
            Welcome Back, <?php echo htmlspecialchars($display_name); ?>!
        </h1>
        <p class="dashboard-subtitle">Admin ID: <?php echo htmlspecialchars($admin_id); ?> | Last Login: <?php echo date('F j, Y g:i A', strtotime($last_login)); ?></p>

        <div class="header-stats">
            <div class="header-stat">
                    <div id="footfall_today" class="header-stat-number"><?php echo number_format($quick_stats['footfall_today']); ?></div>
                    <div class="header-stat-label">Today's Footfall</div>
                </div>
                <div class="header-stat">
                    <div id="books_issued" class="header-stat-number"><?php echo number_format($quick_stats['books_issued']); ?></div>
                    <div class="header-stat-label">Books Currently Out</div>
                </div>
                <div class="header-stat">
                    <div id="dropbox_active" class="header-stat-number"><?php echo $quick_stats['dropbox_active']; ?></div>
                    <div class="header-stat-label">Active DropBoxes</div>
                </div>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card"> <!-- Default golden for total books -->
            <!-- <i class="stat-icon fas fa-book"></i> -->
            <span id="total_books" class="stat-number"><?php echo number_format($quick_stats['total_books']); ?></span>
            <div class="stat-label">Total Books</div>
        </div>

        <div class="stat-card"> <!-- Default golden for total copies -->
            <!-- <i class="stat-icon fas fa-layer-group"></i> -->
            <span id="total_copies" class="stat-number"><?php echo number_format($quick_stats['total_copies']); ?></span>
            <div class="stat-label">Total Copies</div>
        </div>

        <div class="stat-card success"> <!-- Green for active members (positive metric) -->
            <!-- <i class="stat-icon fas fa-users"></i> -->
            <span id="active_members" class="stat-number"><?php echo number_format($quick_stats['active_members']); ?></span>
            <div class="stat-label">Active Members</div>
        </div>

        <div class="stat-card"> <!-- Default golden for books issued -->
            <!-- <i class="stat-icon fas fa-exchange-alt"></i> -->
            <span id="books_issued_card" class="stat-number"><?php echo number_format($quick_stats['books_issued']); ?></span>
            <div class="stat-label">Books Issued</div>
        </div>

        <div class="stat-card danger"> <!-- Red for overdue books (critical metric) -->
            <!-- <i class="stat-icon fas fa-exclamation-triangle"></i> -->
            <span id="books_overdue" class="stat-number"><?php echo $quick_stats['books_overdue']; ?></span>
            <div class="stat-label">Overdue Books</div>

        </div>

        <div class="stat-card info"> <!-- Blue for e-resources (informational) -->
            <!-- <i class="stat-icon fas fa-cloud"></i> -->
            <span id="e_resources" class="stat-number"><?php echo number_format($quick_stats['e_resources']); ?></span>
            <div class="stat-label">E-Resources</div>

        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions-grid">
        <div class="action-card" data-page="circulation">
            <i class="action-icon fas fa-qrcode"></i>
            <div class="action-title">Issue/Return Books</div>
            <div class="action-description">Scan QR codes to manage circulation</div>
        </div>

        <div class="action-card" data-page="books-management">
            <i class="action-icon fas fa-plus-circle"></i>
            <div class="action-title">Add New Book</div>
            <div class="action-description">Add books and generate holdings</div>
        </div>

        <div class="action-card" data-page="student-management">
            <i class="action-icon fas fa-user-plus"></i>
            <div class="action-title">Register Member</div>
            <div class="action-description">Create new member accounts</div>
        </div>

        <div class="action-card" data-page="analytics">
            <i class="action-icon fas fa-chart-line"></i>
            <div class="action-title">View Analytics</div>
            <div class="action-description">Generate reports and insights</div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="dashboard-row">
        <!-- Recent Activities -->
        <div class="dashboard-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-clock"></i>
                    Recent Activities
                </h3>
                <a href="#" class="section-action" data-page="analytics">View All</a>
            </div>
            <div class="section-content">
                <?php foreach ($recent_activities as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-icon <?php echo $activity['type']; ?>">
                            <?php
                            $icons = [
                                'circulation' => 'fas fa-exchange-alt',
                                'dropbox' => 'fas fa-inbox',
                                'member' => 'fas fa-user-plus',
                                'acquisition' => 'fas fa-shopping-cart',
                                'analytics' => 'fas fa-chart-bar'
                            ];
                            ?>
                            <i class="<?php echo $icons[$activity['type']]; ?>"></i>
                        </div>
                        <div class="activity-details">
                            <div class="activity-action"><?php echo htmlspecialchars($activity['action']); ?></div>
                            <div class="activity-description"><?php echo htmlspecialchars($activity['details']); ?></div>
                            <div class="activity-meta">
                                <?php if ($activity['member'] !== 'System'): ?>
                                    Member: <?php echo htmlspecialchars($activity['member']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="activity-time">
                            <?php echo date('H:i', strtotime($activity['time'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Critical Alerts -->
        <div class="dashboard-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-bell"></i>
                    Critical Alerts
                </h3>
                <a href="#" class="section-action" data-page="notifications">Manage</a>
            </div>
            <div class="section-content">
                <div class="alerts-grid">
                    <?php foreach ($critical_alerts as $alert): ?>
                        <div class="alert-card alert-<?php echo $alert['priority']; ?>" data-page="<?php echo $alert['action']; ?>">
                            <div class="alert-header">
                                <div class="alert-title"><?php echo htmlspecialchars($alert['title']); ?></div>
                                <div class="alert-count"><?php echo $alert['count']; ?></div>
                            </div>
                            <div class="alert-description"><?php echo htmlspecialchars($alert['description']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Row -->
    <div class="dashboard-row">
        <!-- Popular Books -->
        <div class="dashboard-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-fire"></i>
                    Most Popular Books
                </h3>
                <a href="#" class="section-action" data-page="books-management">View Catalog</a>
            </div>
            <div class="section-content">
                <?php if (!empty($popular_books)): ?>
                    <?php foreach ($popular_books as $book): ?>
                        <div class="book-item">
                            <div class="book-info">
                                <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                                <div class="book-author">by <?php echo htmlspecialchars($book['author']); ?></div>
                            </div>
                            <div class="book-stats">
                                <div class="book-circulation"><?php echo $book['circulation_count']; ?> issues</div>
                                <div class="book-availability">
                                    <?php echo $book['available_copies']; ?>/<?php echo $book['total_copies']; ?> available
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align:center; padding:20px; color:#6c757d;">
                        <i class="fas fa-book-open" style="font-size:32px; margin-bottom:10px;"></i>
                        <p>No circulation data yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Active Borrowers -->
        <div class="dashboard-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-users"></i>
                    Active Borrowers
                </h3>
                <a href="#" class="section-action" data-page="members">View All</a>
            </div>
            <div class="section-content">
                <?php if (!empty($active_borrowers)): ?>
                    <?php foreach ($active_borrowers as $borrower): ?>
                        <div class="book-item" style="cursor:pointer;">
                            <div class="book-info">
                                <div class="book-title"><?php echo htmlspecialchars($borrower['MemberName']); ?></div>
                                <div class="book-author">ID: <?php echo htmlspecialchars($borrower['MemberNo']); ?></div>
                            </div>
                            <div class="book-stats">
                                <div class="book-circulation"><?php echo $borrower['BooksIssued']; ?> books</div>
                                <div class="book-availability">
                                    <span class="status-badge status-available">Active</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align:center; padding:20px; color:#6c757d;">
                        <i class="fas fa-user-clock" style="font-size:32px; margin-bottom:10px;"></i>
                        <p>No active borrowers</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <script>
            // Dashboard interactivity
            document.addEventListener('DOMContentLoaded', function() {
                // Quick action cards navigation
                document.querySelectorAll('.action-card[data-page], .alert-card[data-page]').forEach(card => {
                    card.addEventListener('click', function() {
                        const page = this.getAttribute('data-page');
                        navigateToPage(page);
                    });
                });

                // Section action links
                document.querySelectorAll('.section-action[data-page]').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const page = this.getAttribute('data-page');
                        navigateToPage(page);
                    });
                });

                function navigateToPage(page) {
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

                    // Update URL hash
                    window.location.hash = page;
                }

                // Animate stat numbers on load
                animateNumbers();

                    // Auto-refresh dashboard data every 30 seconds
                    setInterval(refreshDashboardData, 30000);
            });

            function animateNumbers() {
                document.querySelectorAll('.stat-number').forEach(element => {
                    const finalNumber = element.textContent.replace(/,/g, '');
                    const duration = 2000;
                    const increment = finalNumber / (duration / 16);
                    let current = 0;

                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= finalNumber) {
                            element.textContent = parseInt(finalNumber).toLocaleString();
                            clearInterval(timer);
                        } else {
                            element.textContent = Math.floor(current).toLocaleString();
                        }
                    }, 16);
                });
            }

            function refreshDashboardData() {
                    // Fetch live dashboard stats from API and update the DOM
                    fetch('api/dashboard.php')
                        .then(resp => resp.json())
                        .then(json => {
                            if (!json.success) throw new Error(json.message || 'API returned failure');
                            const d = json.data;

                            updateNumber('#footfall_today', d.footfall_today);
                            updateNumber('#books_issued', d.books_issued);
                            updateNumber('#dropbox_active', d.dropbox_active);

                            updateNumber('#total_books', d.total_books);
                            updateNumber('#total_copies', d.total_copies);
                            updateNumber('#active_members', d.active_members);
                            updateNumber('#books_issued_card', d.books_issued);
                            updateNumber('#books_overdue', d.books_overdue);
                            updateNumber('#e_resources', d.e_resources);

                            // Rebuild critical alerts for overdue if applicable
                            try {
                                const alertsGrid = document.querySelector('.alerts-grid');
                                if (alertsGrid) {
                                    // If overdue books exist, show a single alert — keep it simple
                                    alertsGrid.innerHTML = '';
                                    if (d.books_overdue && d.books_overdue > 0) {
                                        const div = document.createElement('div');
                                        div.className = 'alert-card alert-high';
                                        div.innerHTML = `
                                            <div class="alert-header">
                                                <div class="alert-title">${d.books_overdue} Overdue Books</div>
                                                <div class="alert-count">${d.books_overdue}</div>
                                            </div>
                                            <div class="alert-description">Books not returned past due date</div>
                                        `;
                                        alertsGrid.appendChild(div);
                                    } else {
                                        const div = document.createElement('div');
                                        div.className = 'alert-card alert-low';
                                        div.innerHTML = `
                                            <div class="alert-header">
                                                <div class="alert-title">No Critical Alerts</div>
                                                <div class="alert-count">0</div>
                                            </div>
                                            <div class="alert-description">All systems normal</div>
                                        `;
                                        alertsGrid.appendChild(div);
                                    }
                                }
                            } catch (err) {
                                console.warn('Failed to rebuild alerts', err);
                            }

                        })
                        .catch(err => {
                            console.warn('Dashboard refresh failed:', err);
                        });

                    // Update timestamp
                    const now = new Date();
                    console.log(`Dashboard refreshed at ${now.toLocaleTimeString()}`);
            }

                function updateNumber(selector, value) {
                    try {
                        const el = document.querySelector(selector);
                        if (!el) return;
                        const final = Number(value) || 0;
                        // Smoothly animate from current to final
                        const current = Number(el.textContent.replace(/,/g, '')) || 0;
                        const duration = 600;
                        const frames = Math.max(6, Math.round(duration / 16));
                        const step = (final - current) / frames;
                        let i = 0;
                        const t = setInterval(() => {
                            i++;
                            const v = Math.round(current + step * i);
                            el.textContent = v.toLocaleString();
                            if (i >= frames) {
                                el.textContent = final.toLocaleString();
                                clearInterval(t);
                            }
                        }, 16);
                    } catch (e) {
                        // fallback: set raw value
                        const el = document.querySelector(selector);
                        if (el) el.textContent = (Number(value) || 0).toLocaleString();
                    }
                }
        </script>