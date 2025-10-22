<?php
// Admin Analytics Page - Library Administration Staff
// This file will be included in the main-content area of admin/layout.php

session_start();

$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : "Library Admin";
$admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : "ADM2025001";

// TODO: Replace with actual database queries
// Mock analytics data based on actual database schema
$stats = [
    'total_books' => 12450,           // Count from Books table
    'total_copies' => 18670,          // Count from Holding table
    'books_available' => 14280,       // Holding.Status = 'Available'
    'books_issued' => 4390,           // Holding.Status = 'Issued'
    'active_members' => 850,          // Member.Status = 'Active'
    'overdue_books' => 67,            // Circulation where DueDate < today
    'pending_returns' => 134,         // DropReturn with Outcome = 'PENDING'
    'todays_footfall' => 178,         // Footfall.Date = today
];

// Monthly circulation trend (last 6 months)
$circulation_trend = [
    ['month' => 'Apr 2024', 'issued' => 420, 'returned' => 395],
    ['month' => 'May 2024', 'issued' => 380, 'returned' => 410],
    ['month' => 'Jun 2024', 'issued' => 450, 'returned' => 425],
    ['month' => 'Jul 2024', 'issued' => 520, 'returned' => 490],
    ['month' => 'Aug 2024', 'issued' => 485, 'returned' => 510],
    ['month' => 'Sep 2024', 'issued' => 430, 'returned' => 465],
    ['month' => 'Oct 2024', 'issued' => 460, 'returned' => 440],
    
];

// Most borrowed books (from Circulation + Books tables)
$popular_books = [
    ['title' => 'Introduction to Algorithms', 'author' => 'Cormen, Thomas H.', 'times_borrowed' => 48, 'available_copies' => 3],
    ['title' => 'Operating System Concepts', 'author' => 'Silberschatz, Abraham', 'times_borrowed' => 44, 'available_copies' => 2],
    ['title' => 'Database System Concepts', 'author' => 'Korth, Henry F.', 'times_borrowed' => 39, 'available_copies' => 5],
    ['title' => 'Computer Networks', 'author' => 'Tanenbaum, Andrew S.', 'times_borrowed' => 36, 'available_copies' => 1],
    ['title' => 'Software Engineering', 'author' => 'Pressman, Roger S.', 'times_borrowed' => 32, 'available_copies' => 4],
];

// Top active members (most books issued)
$active_members = [
    ['name' => 'Rahul Sharma', 'member_no' => 'MEM2024001', 'books_issued' => 8, 'group' => 'B.Tech Final Year'],
    ['name' => 'Priya Patel', 'member_no' => 'MEM2024015', 'books_issued' => 7, 'group' => 'M.Tech'],
    ['name' => 'Amit Kumar', 'member_no' => 'MEM2024032', 'books_issued' => 6, 'group' => 'B.Tech Third Year'],
    ['name' => 'Sneha Gupta', 'member_no' => 'MEM2024008', 'books_issued' => 6, 'group' => 'Faculty'],
    ['name' => 'Vikash Singh', 'member_no' => 'MEM2024021', 'books_issued' => 5, 'group' => 'B.Tech Second Year'],
];

// Recent acquisitions
$recent_acquisitions = [
    ['title' => 'Artificial Intelligence: A Modern Approach', 'author' => 'Russell, Stuart', 'date_added' => '2024-09-20', 'copies' => 3],
    ['title' => 'Clean Architecture', 'author' => 'Martin, Robert C.', 'date_added' => '2024-09-18', 'copies' => 2],
    ['title' => 'Machine Learning Yearning', 'author' => 'Ng, Andrew', 'date_added' => '2024-09-15', 'copies' => 4],
    ['title' => 'Design Patterns', 'author' => 'Gamma, Erich', 'date_added' => '2024-09-12', 'copies' => 2],
];

// Subject-wise distribution
$subject_distribution = [
    ['subject' => 'Computer Science', 'book_count' => 3250, 'percentage' => 26.1],
    ['subject' => 'Electronics & Communication', 'book_count' => 2100, 'percentage' => 16.9],
    ['subject' => 'Mechanical Engineering', 'book_count' => 1890, 'percentage' => 15.2],
    ['subject' => 'Mathematics', 'book_count' => 1450, 'percentage' => 11.6],
    ['subject' => 'Physics', 'book_count' => 1200, 'percentage' => 9.6],
    ['subject' => 'Others', 'book_count' => 2560, 'percentage' => 20.6],
];
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
                $max_value = max(array_merge(array_column($circulation_trend, 'issued'), array_column($circulation_trend, 'returned')));
                foreach ($circulation_trend as $month):
                    $issued_height = ($max_value > 0) ? round(($month['issued'] / $max_value) * 180) : 0;
                    $returned_height = ($max_value > 0) ? round(($month['returned'] / $max_value) * 180) : 0;
                ?>
                    <div class="bar-group">
                        <div class="bar-pair">
                            <div class="bar-rect bar-issued" style="height: <?php echo $issued_height; ?>px;"></div>
                            <div class="bar-rect bar-returned" style="height: <?php echo $returned_height; ?>px;"></div>
                        </div>
                        <div class="bar-label"><?php echo $month['month']; ?></div>
                    </div>
                <?php endforeach; ?>
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
            <?php foreach ($subject_distribution as $subject): ?>
                <div style="margin-bottom: 15px;">
                    <div class="subject-label"><?php echo $subject['subject']; ?> (<?php echo number_format($subject['book_count']); ?>)</div>
                    <div class="subject-bar">
                        <div class="subject-fill" style="width: <?php echo $subject['percentage']; ?>%;">
                            <?php echo $subject['percentage']; ?>%
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
                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                        <td><?php echo $book['times_borrowed']; ?></td>
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
    </div>
</div>

<!-- Active Members and Recent Acquisitions -->
<div class="analytics-row">
    <div class="analytics-section">
        <div class="section-header">
            <h3 class="section-title"><i class="fas fa-users" style="margin-right: 8px;"></i> Most Active Members</h3>
        </div>
        <div class="section-content">
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
                            <td><?php echo htmlspecialchars($member['group']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="analytics-section">
        <div class="section-header">
            <h3 class="section-title"><i class="fas fa-plus-circle" style="margin-right: 8px;"></i> Recent Acquisitions</h3>
        </div>
        <div class="section-content">
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
                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($book['date_added'])); ?></td>
                            <td><?php echo $book['copies']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>