<?php
// Admin Analytics Page - Library Administration Staff
// This file will be included in the main-content area of admin/layout.php

// Include AJAX handler FIRST
require_once 'ajax-handler.php';

session_start();
require_once '../includes/db_connect.php';

$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : "Library Admin";
$admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : "ADM2025001";

// Fetch real analytics data from database
$stats = [
    'total_books' => 0,
    'total_copies' => 0,
    'books_available' => 0,
    'books_issued' => 0,
    'active_members' => 0,
    'overdue_books' => 0,
    'pending_returns' => 0,
    'todays_footfall' => 0,
];

try {
    // Total unique books
    $stmt = $pdo->query("SELECT COUNT(DISTINCT CatNo) as count FROM Books");
    $stats['total_books'] = (int)$stmt->fetchColumn();
    
    // Total copies
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM Holding");
    $stats['total_copies'] = (int)$stmt->fetchColumn();
    
    // Available books
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM Holding WHERE Status = 'Available'");
    $stats['books_available'] = (int)$stmt->fetchColumn();
    
    // Issued books
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM Holding WHERE Status = 'Issued'");
    $stats['books_issued'] = (int)$stmt->fetchColumn();
    
    // Active members
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM Member WHERE Status = 'Active'");
    $stats['active_members'] = (int)$stmt->fetchColumn();
    
    // Overdue books (DueDate < today and not returned)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM Circulation WHERE DueDate < CURDATE() AND ReturnDate IS NULL");
    $stats['overdue_books'] = (int)$stmt->fetchColumn();
    
    // Pending returns from DropReturn
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM DropReturn WHERE Outcome = 'PENDING'");
    $stats['pending_returns'] = (int)$stmt->fetchColumn();
    
    // Today's footfall
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM Footfall WHERE DATE(EntryTime) = CURDATE()");
    $stats['todays_footfall'] = (int)$stmt->fetchColumn();
} catch (Exception $e) {
    error_log("Analytics - Error fetching stats: " . $e->getMessage());
}

// Monthly circulation trend (last 6 months)
$circulation_trend = [];
try {
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(IssueDate, '%b %Y') as month,
            COUNT(*) as issued,
            SUM(CASE WHEN ReturnDate IS NOT NULL THEN 1 ELSE 0 END) as returned
        FROM Circulation
        WHERE IssueDate >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(IssueDate, '%Y-%m')
        ORDER BY IssueDate
    ");
    $circulation_trend = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Analytics - Error fetching circulation trend: " . $e->getMessage());
}

// Most borrowed books (from Circulation + Books tables)
$popular_books = [];
try {
    $stmt = $pdo->query("
        SELECT 
            b.Title as title,
            b.Author1 as author,
            COUNT(c.CirculationID) as times_borrowed,
            SUM(CASE WHEN h.Status = 'Available' THEN 1 ELSE 0 END) as available_copies
        FROM Circulation c
        JOIN Holding h ON c.AccNo = h.AccNo
        JOIN Books b ON h.CatNo = b.CatNo
        GROUP BY b.CatNo
        ORDER BY times_borrowed DESC
        LIMIT 5
    ");
    $popular_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Analytics - Error fetching popular books: " . $e->getMessage());
}

// Top active members (most books issued)
$active_members = [];
try {
    $stmt = $pdo->query("
        SELECT 
            m.Name as name,
            m.MemberNo as member_no,
            COUNT(c.CirculationID) as books_issued,
            m.MemberGroup as `group`
        FROM Circulation c
        JOIN Member m ON c.MemberNo = m.MemberNo
        WHERE c.ReturnDate IS NULL
        GROUP BY m.MemberNo
        ORDER BY books_issued DESC
        LIMIT 5
    ");
    $active_members = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Analytics - Error fetching active members: " . $e->getMessage());
}

// Recent acquisitions
$recent_acquisitions = [];
try {
    $stmt = $pdo->query("
        SELECT 
            b.Title as title,
            b.Author1 as author,
            DATE(b.EntryDate) as date_added,
            COUNT(h.AccNo) as copies
        FROM Books b
        LEFT JOIN Holding h ON b.CatNo = h.CatNo
        GROUP BY b.CatNo
        ORDER BY b.EntryDate DESC
        LIMIT 5
    ");
    $recent_acquisitions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Analytics - Error fetching recent acquisitions: " . $e->getMessage());
}

// Category-wise distribution
$category_distribution = [];
try {
    $stmt = $pdo->query("
        SELECT 
            COALESCE(Subject, 'Uncategorized') as category,
            COUNT(DISTINCT b.CatNo) as book_count,
            COUNT(h.AccNo) as copy_count
        FROM Books b
        LEFT JOIN Holding h ON b.CatNo = h.CatNo
        GROUP BY Subject
        ORDER BY book_count DESC
        LIMIT 10
    ");
    $category_distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Analytics - Error fetching category distribution: " . $e->getMessage());
}

// Calculate percentages for category distribution
$total_category_books = array_sum(array_column($category_distribution, 'book_count'));
foreach ($category_distribution as &$cat) {
    $cat['percentage'] = $total_category_books > 0 ? round(($cat['book_count'] / $total_category_books) * 100, 1) : 0;
}
unset($cat);
?>

<style>
    .analytics-header {
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 2px solid #cfac69;
    }

    .analytics-title {
        color: #263c79;
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .analytics-subtitle {
        color: #666;
        font-size: 16px;
        margin: 0;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s, box-shadow 0.2s;
        border-left: 4px solid #cfac69;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .stat-card.overdue {
        border-left: 4px solid #dc3545;
    }

    .stat-card.success {
        border-left: 4px solid #28a745;
    }

    .stat-card.issued {
        border-left: 4px solid #ffc107;
    }

    .stat-number {
        font-size: 26px !important;
        font-weight: 700;
        color: #263c79;
        margin-bottom: 5px;
        display: block;
    }

    .stat-label {
        color: #666;
        font-size: 14px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .analytics-row {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 25px;
        margin-bottom: 25px;
        margin-top: 35px;
    }

    .analytics-section {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
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

    .trend-chart {
        width: 100%;
        height: 250px;
        background: #f8f9fa;
        border-radius: 8px;
        display: flex;
        align-items: flex-end;
        gap: 8px;
        padding: 20px 10px 0 10px;
        margin-bottom: 10px;
    }

    .bar-group {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end;
    }

    .bar-pair {
        display: flex;
        gap: 2px;
        margin-bottom: 5px;
    }

    .bar-rect {
        width: 15px;
        border-radius: 3px 3px 0 0;
        transition: height 0.3s;
    }

    .bar-issued {
        background: #263c79;
    }

    .bar-returned {
        background: #cfac69;
    }

    .bar-label {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
        text-align: center;
    }

    .chart-legend {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: 10px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 13px;
    }

    .legend-color {
        width: 15px;
        height: 15px;
        border-radius: 3px;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .data-table th,
    .data-table td {
        border: 1px solid #e0e0e0;
        padding: 10px 12px;
        text-align: left;
        font-size: 13px;
    }

    .data-table th {
        background: #f8f9fa;
        color: #263c79;
        font-weight: 600;
    }

    .data-table td {
        color: #666;
    }

    .book-title {
        color: #263c79;
        font-weight: 600;
    }

    .availability-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .available {
        background: #d4edda;
        color: #155724;
    }

    .low-stock {
        background: #fff3cd;
        color: #856404;
    }

    .out-of-stock {
        background: #f8d7da;
        color: #721c24;
    }

    .subject-bar {
        background: #f8f9fa;
        height: 30px;
        border-radius: 15px;
        overflow: hidden;
        margin: 5px 0;
        position: relative;
    }

    .subject-fill {
        height: 100%;
        background: linear-gradient(90deg, #263c79, #cfac69);
        border-radius: 15px;
        display: flex;
        align-items: center;
        padding: 0 10px;
        color: white;
        font-size: 12px;
        font-weight: 600;
    }

    .subject-label {
        font-size: 13px;
        color: #263c79;
        font-weight: 600;
        margin-bottom: 2px;
    }

    @media (max-width: 1024px) {
        .analytics-row {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .trend-chart {
            height: 180px;
            padding: 10px 5px 0 5px;
        }
    }
</style>

<div class="analytics-header">
    <h1 class="analytics-title">Library Analytics Dashboard</h1>
    <p class="analytics-subtitle">Comprehensive overview of library operations and usage statistics</p>
</div>

<!-- Quick Stats Overview -->
<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-number"><?php echo number_format($stats['total_books']); ?></span>
        <div class="stat-label">Total Book Titles</div>
    </div>
    <div class="stat-card">
        <span class="stat-number"><?php echo number_format($stats['total_copies']); ?></span>
        <div class="stat-label">Total Book Copies</div>
    </div>
    <div class="stat-card success">
        <span class="stat-number"><?php echo number_format($stats['books_available']); ?></span>
        <div class="stat-label">Available Copies</div>
    </div>
    <div class="stat-card issued">
        <span class="stat-number"><?php echo number_format($stats['books_issued']); ?></span>
        <div class="stat-label">Currently Issued</div>
    </div>
    <div class="stat-card">
        <span class="stat-number"><?php echo number_format($stats['active_members']); ?></span>
        <div class="stat-label">Active Members</div>
    </div>
    <div class="stat-card overdue">
        <span class="stat-number"><?php echo $stats['overdue_books']; ?></span>
        <div class="stat-label">Overdue Books</div>
    </div>
    <div class="stat-card">
        <span class="stat-number"><?php echo $stats['pending_returns']; ?></span>
        <div class="stat-label">Pending Returns</div>
    </div>
    <div class="stat-card">
        <span class="stat-number"><?php echo $stats['todays_footfall']; ?></span>
        <div class="stat-label">Today's Footfall</div>
    </div>
</div>

<!-- Circulation Trend and Subject Distribution -->
<div class="analytics-row">
    <div class="analytics-section">
        <div class="section-header">
            <h3 class="section-title"><i class="fas fa-chart-line" style="margin-right: 8px;"></i> Circulation Trend (Last 6 Months)</h3>
        </div>
        <div class="section-content">
            <div class="trend-chart">
                <?php
                if (!empty($circulation_trend)):
                    $max_value = max(array_merge(array_column($circulation_trend, 'issued'), array_column($circulation_trend, 'returned')));
                    if ($max_value == 0) $max_value = 1; // Prevent division by zero
                    foreach ($circulation_trend as $month):
                        $issued_height = ($max_value > 0) ? round(($month['issued'] / $max_value) * 180) : 0;
                        $returned_height = ($max_value > 0) ? round(($month['returned'] / $max_value) * 180) : 0;
                ?>
                    <div class="bar-group">
                        <div class="bar-pair">
                            <div class="bar-rect bar-issued" style="height: <?php echo $issued_height; ?>px;"></div>
                            <div class="bar-rect bar-returned" style="height: <?php echo $returned_height; ?>px;"></div>
                        </div>
                        <div class="bar-label"><?php echo htmlspecialchars($month['month']); ?></div>
                    </div>
                <?php 
                    endforeach;
                else:
                ?>
                    <div style="text-align:center; padding:40px; color:#6c757d;">
                        <i class="fas fa-chart-line" style="font-size:48px; opacity:0.3;"></i>
                        <p style="margin-top:15px;">No circulation data available for the last 6 months</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="chart-legend">
                <div class="legend-item">
                    <div class="legend-color bar-issued"></div>
                    <span>Books Issued</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color bar-returned"></div>
                    <span>Books Returned</span>
                </div>
            </div>
        </div>
    </div>

    <div class="analytics-section">
        <div class="section-header">
            <h3 class="section-title"><i class="fas fa-chart-pie" style="margin-right: 8px;"></i> Collection by Subject</h3>
        </div>
        <div class="section-content">
            <?php foreach ($category_distribution as $cat): ?>
                <div style="margin-bottom: 15px;">
                    <div class="subject-label"><?php echo htmlspecialchars($cat['category']); ?> (<?php echo number_format($cat['book_count']); ?>)</div>
                    <div class="subject-bar">
                        <div class="subject-fill" style="width: <?php echo $cat['percentage']; ?>%;">
                            <?php echo $cat['percentage']; ?>%
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Most Popular Books -->
<div class="analytics-section">
    <div class="section-header">
        <h3 class="section-title"><i class="fas fa-star" style="margin-right: 8px;"></i> Most Borrowed Books</h3>
    </div>
    <div class="section-content">
        <?php if (!empty($popular_books)): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Book Title</th>
                    <th>Author</th>
                    <th>Times Borrowed</th>
                    <th>Available Copies</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($popular_books as $book): ?>
                    <tr>
                        <td class="book-title"><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['author'] ?? 'Unknown'); ?></td>
                        <td><?php echo number_format($book['times_borrowed']); ?></td>
                        <td><?php echo $book['available_copies']; ?></td>
                        <td>
                            <?php if ($book['available_copies'] > 3): ?>
                                <span class="availability-badge available">Available</span>
                            <?php elseif ($book['available_copies'] > 0): ?>
                                <span class="availability-badge low-stock">Low Stock</span>
                            <?php else: ?>
                                <span class="availability-badge out-of-stock">Out of Stock</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div style="text-align:center; padding:40px; color:#6c757d;">
                <i class="fas fa-book" style="font-size:48px; opacity:0.3;"></i>
                <p style="margin-top:15px;">No circulation data available yet</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Active Members and Recent Acquisitions -->
<div class="analytics-row">
    <div class="analytics-section">
        <div class="section-header">
            <h3 class="section-title"><i class="fas fa-users" style="margin-right: 8px;"></i> Most Active Members</h3>
        </div>
        <div class="section-content">
            <?php if (!empty($active_members)): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Member Name</th>
                        <th>Member No</th>
                        <th>Books Issued</th>
                        <th>Group</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($active_members as $member): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($member['name']); ?></td>
                            <td><?php echo htmlspecialchars($member['member_no']); ?></td>
                            <td><?php echo $member['books_issued']; ?></td>
                            <td><?php echo htmlspecialchars($member['group'] ?? 'N/A'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div style="text-align:center; padding:40px; color:#6c757d;">
                    <i class="fas fa-users" style="font-size:48px; opacity:0.3;"></i>
                    <p style="margin-top:15px;">No active members with borrowed books</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="analytics-section">
        <div class="section-header">
            <h3 class="section-title"><i class="fas fa-plus-circle" style="margin-right: 8px;"></i> Recent Acquisitions</h3>
        </div>
        <div class="section-content">
            <?php if (!empty($recent_acquisitions)): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Date Added</th>
                        <th>Copies</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_acquisitions as $book): ?>
                        <tr>
                            <td class="book-title"><?php echo htmlspecialchars($book['title']); ?></td>
                            <td><?php echo htmlspecialchars($book['author'] ?? 'Unknown'); ?></td>
                            <td><?php echo $book['date_added'] ? date('M j, Y', strtotime($book['date_added'])) : 'N/A'; ?></td>
                            <td><?php echo $book['copies']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div style="text-align:center; padding:40px; color:#6c757d;">
                    <i class="fas fa-book" style="font-size:48px; opacity:0.3;"></i>
                    <p style="margin-top:15px;">No recent acquisitions</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>