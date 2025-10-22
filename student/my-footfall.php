<?php
// My Footfall Content - Library visit tracking and analytics
// This file will be included in the main content area

// Session variables for student info
$student_name = isset($_SESSION['student_name']) ? $_SESSION['student_name'] : 'John Doe';
$student_id = isset($_SESSION['student_id']) ? $_SESSION['student_id'] : 'STU2024001';

// Mock data for demonstration - replace with actual database queries
$monthly_stats = [
    'current_month' => [
        'visits' => 18,
        'hours' => 47.5,
        'avg_duration' => '2h 38m',
        'books_accessed' => 23
    ],
    'last_month' => [
        'visits' => 22,
        'hours' => 56.2,
        'avg_duration' => '2h 33m',
        'books_accessed' => 31
    ]
];

$recent_visits = [
    [
        'date' => '2025-09-23',
        'entry_time' => '09:15 AM',
        'exit_time' => '11:45 AM',
        'duration' => '2h 30m',
        'purpose' => 'Study Session',
        'section' => 'Computer Science'
    ],
    [
        'date' => '2025-09-21',
        'entry_time' => '02:30 PM',
        'exit_time' => '04:15 PM',
        'duration' => '1h 45m',
        'purpose' => 'Book Research',
        'section' => 'Reference Section'
    ],
    [
        'date' => '2025-09-19',
        'entry_time' => '10:00 AM',
        'exit_time' => '01:30 PM',
        'duration' => '3h 30m',
        'purpose' => 'Project Work',
        'section' => 'Reading Hall'
    ],
    [
        'date' => '2025-09-17',
        'entry_time' => '03:15 PM',
        'exit_time' => '05:00 PM',
        'duration' => '1h 45m',
        'purpose' => 'E-Resource Access',
        'section' => 'Digital Section'
    ],
    [
        'date' => '2025-09-15',
        'entry_time' => '11:30 AM',
        'exit_time' => '02:15 PM',
        'duration' => '2h 45m',
        'purpose' => 'Study Session',
        'section' => 'Silent Zone'
    ]
];

$weekly_chart_data = [
    'Mon' => 3,
    'Tue' => 2,
    'Wed' => 4,
    'Thu' => 1,
    'Fri' => 3,
    'Sat' => 2,
    'Sun' => 3
];
?>

<style>
    .footfall-header {
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #cfac69;
    }

    .footfall-title {
        color: #263c79;
        font-size: 28px;
        font-weight: 700;
        margin: 0 0 8px 0;
    }

    .footfall-subtitle {
        color: #666;
        font-size: 16px;
        margin: 0;
    }

    .stats-overview {
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
        transition: all 0.3s ease;
        border-left: 4px solid #cfac69;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .stat-icon {
        font-size: 32px;
        color: #cfac69;
        margin-bottom: 10px;
    }

    .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #263c79;
        margin-bottom: 5px;
    }

    .stat-label {
        color: #666;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .stat-change {
        font-size: 12px;
        font-weight: 600;
        padding: 2px 6px;
        border-radius: 3px;
    }

    .stat-increase {
        background: #d4edda;
        color: #155724;
    }

    .stat-decrease {
        background: #f8d7da;
        color: #721c24;
    }

    .charts-section {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 25px;
        margin-bottom: 30px;
    }

    .chart-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .chart-title {
        color: #263c79;
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .weekly-chart {
        height: 200px;
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 8px;
        padding: 20px 0;
        border-bottom: 2px solid #e0e0e0;
        margin-bottom: 10px;
    }

    .chart-bar {
        flex: 1;
        background: #cfac69;
        border-radius: 4px 4px 0 0;
        position: relative;
        min-height: 20px;
        transition: all 0.3s ease;
        opacity: 0;
        transform: scaleY(0);
        transform-origin: bottom;
    }

    .chart-bar:hover {
        background: #b8956b;
        transform: scale(1.05);
    }

    .chart-bar::after {
        content: attr(data-value);
        position: absolute;
        top: -20px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 11px;
        font-weight: 600;
        color: #263c79;
    }

    .chart-labels {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        color: #666;
        font-weight: 500;
    }

    .purpose-breakdown {
        margin-top: 20px;
    }

    .purpose-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .purpose-item:last-child {
        border-bottom: none;
    }

    .purpose-name {
        font-size: 13px;
        color: #263c79;
        font-weight: 500;
    }

    .purpose-count {
        font-size: 12px;
        color: #666;
        background: #f0f2f5;
        padding: 3px 8px;
        border-radius: 3px;
    }

    .visits-table-container {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .table-header {
        background: #f8f9fa;
        padding: 15px 20px;
        border-bottom: 1px solid #e0e0e0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-title {
        color: #263c79;
        font-size: 18px;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .visits-table {
        width: 100%;
        border-collapse: collapse;
    }

    .visits-table th {
        background: #f8f9fa;
        color: #263c79;
        font-weight: 600;
        padding: 12px 15px;
        text-align: left;
        border-bottom: 2px solid #e0e0e0;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .visits-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: top;
    }

    .visits-table tr:last-child td {
        border-bottom: none;
    }

    .visits-table tr:hover {
        background: #f8f9fa;
    }

    .visit-date {
        font-weight: 600;
        color: #263c79;
        font-size: 14px;
    }

    .visit-day {
        font-size: 11px;
        color: #666;
        text-transform: uppercase;
    }

    .time-info {
        font-family: monospace;
        font-size: 13px;
        line-height: 1.4;
    }

    .entry-time {
        color: #28a745;
        margin-bottom: 2px;
    }

    .exit-time {
        color: #dc3545;
    }

    .duration {
        font-weight: 600;
        color: #263c79;
        font-size: 14px;
    }

    .purpose-badge {
        background: #f0f2f5;
        color: #263c79;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 500;
        display: inline-block;
        margin-bottom: 4px;
    }

    .section-info {
        font-size: 12px;
        color: #666;
    }

    .filter-controls {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .filter-btn {
        background: transparent;
        border: 1px solid #263c79;
        color: #263c79;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .filter-btn.active,
    .filter-btn:hover {
        background: #263c79;
        color: white;
    }

    @media (max-width: 968px) {
        .charts-section {
            grid-template-columns: 1fr;
        }

        .stats-overview {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .stats-overview {
            grid-template-columns: 1fr;
        }

        .table-header {
            flex-direction: column;
            gap: 10px;
            align-items: flex-start;
        }

        .filter-controls {
            width: 100%;
            justify-content: center;
        }

        .visits-table {
            font-size: 12px;
        }

        .visits-table th,
        .visits-table td {
            padding: 8px 10px;
        }
    }
</style>

<div class="footfall-header">
    <h1 class="footfall-title">My Footfall</h1>
    <p class="footfall-subtitle">Track your library visits and analyze your study patterns</p>
</div>

<!-- Statistics Overview -->
<div class="stats-overview">
    <div class="stat-card">
        <i class="stat-icon fas fa-walking"></i>
        <div class="stat-value"><?php echo $monthly_stats['current_month']['visits']; ?></div>
        <div class="stat-label">Visits This Month</div>
        <span class="stat-change <?php echo $monthly_stats['current_month']['visits'] > $monthly_stats['last_month']['visits'] ? 'stat-increase' : 'stat-decrease'; ?>">
            <?php
            $change = $monthly_stats['current_month']['visits'] - $monthly_stats['last_month']['visits'];
            echo ($change > 0 ? '+' : '') . $change . ' from last month';
            ?>
        </span>
    </div>

    <!-- <div class="stat-card">
        <i class="stat-icon fas fa-clock"></i>
        <div class="stat-value"><?php echo $monthly_stats['current_month']['hours']; ?>h</div>
        <div class="stat-label">Total Hours</div>
        <span class="stat-change <?php echo $monthly_stats['current_month']['hours'] > $monthly_stats['last_month']['hours'] ? 'stat-increase' : 'stat-decrease'; ?>">
            <?php
            $change = $monthly_stats['current_month']['hours'] - $monthly_stats['last_month']['hours'];
            echo ($change > 0 ? '+' : '') . number_format($change, 1) . 'h from last month';
            ?>
        </span>
    </div> -->

   <!-- <div class="stat-card">
        <i class="stat-icon fas fa-chart-line"></i>
        <div class="stat-value"><?php echo $monthly_stats['current_month']['avg_duration']; ?></div>
        <div class="stat-label">Average Duration</div>
        <span class="stat-change stat-increase">Optimal range</span>
    </div> -->

    <div class="stat-card">
        <i class="stat-icon fas fa-book"></i>
        <div class="stat-value"><?php echo $monthly_stats['current_month']['books_accessed']; ?></div>
        <div class="stat-label">Books Accessed</div>
        <span class="stat-change <?php echo $monthly_stats['current_month']['books_accessed'] > $monthly_stats['last_month']['books_accessed'] ? 'stat-increase' : 'stat-decrease'; ?>">
            <?php
            $change = $monthly_stats['current_month']['books_accessed'] - $monthly_stats['last_month']['books_accessed'];
            echo ($change > 0 ? '+' : '') . $change . ' from last month';
            ?>
        </span>
    </div>
</div>

<!-- Charts Section -->
<div class="charts-section">
    <div class="chart-card">
        <h3 class="chart-title">
            <i class="fas fa-chart-bar"></i>
            Weekly Visit Pattern
        </h3>
        <div class="weekly-chart">
            <?php foreach ($weekly_chart_data as $day => $visits): ?>
                <div class="chart-bar" style="height: <?php echo ($visits * 30) + 20; ?>px;" data-value="<?php echo $visits; ?>"></div>
            <?php endforeach; ?>
        </div>
        <div class="chart-labels">
            <?php foreach (array_keys($weekly_chart_data) as $day): ?>
                <span><?php echo $day; ?></span>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="chart-card">
        <h3 class="chart-title">
            <i class="fas fa-tasks"></i>
            Visit Purposes
        </h3>
        <div class="purpose-breakdown">
            <div class="purpose-item">
                <span class="purpose-name">Study Session</span>
                <span class="purpose-count">8 visits</span>
            </div>
            <div class="purpose-item">
                <span class="purpose-name">Project Work</span>
                <span class="purpose-count">5 visits</span>
            </div>
            <div class="purpose-item">
                <span class="purpose-name">Book Research</span>
                <span class="purpose-count">3 visits</span>
            </div>
            <div class="purpose-item">
                <span class="purpose-name">E-Resource Access</span>
                <span class="purpose-count">2 visits</span>
            </div>
        </div>
    </div>
</div>

<!-- Recent Visits Table -->
<div class="visits-table-container">
    <div class="table-header">
        <h3 class="table-title">
            <i class="fas fa-history"></i>
            Recent Visits
        </h3>
        <div class="filter-controls">
            <button class="filter-btn active" onclick="filterVisits('all')">All</button>
            <button class="filter-btn" onclick="filterVisits('week')">This Week</button>
            <button class="filter-btn" onclick="filterVisits('month')">This Month</button>
        </div>
    </div>

    <table class="visits-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Entry/Exit Time</th>
                <th>Duration</th>
                <th>Purpose & Section</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_visits as $visit): ?>
                <tr>
                    <td>
                        <div class="visit-date"><?php echo date('M j, Y', strtotime($visit['date'])); ?></div>
                        <div class="visit-day"><?php echo date('l', strtotime($visit['date'])); ?></div>
                    </td>
                    <td>
                        <div class="time-info">
                            <div class="entry-time">⬇ <?php echo $visit['entry_time']; ?></div>
                            <div class="exit-time">⬆ <?php echo $visit['exit_time']; ?></div>
                        </div>
                    </td>
                    <td>
                        <div class="duration"><?php echo $visit['duration']; ?></div>
                    </td>
                    <td>
                        <div class="purpose-badge"><?php echo htmlspecialchars($visit['purpose']); ?></div>
                        <div class="section-info"><?php echo htmlspecialchars($visit['section']); ?></div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function filterVisits(period) {
        // Remove active class from all buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        // Add active class to clicked button
        event.target.classList.add('active');

        // In a real implementation, this would filter the table data
        console.log('Filtering visits for period:', period);

        // Simulate filtering (in real app, you'd make an AJAX request)
        const rows = document.querySelectorAll('.visits-table tbody tr');
        rows.forEach(row => {
            // This is just a demo - implement actual filtering logic
            row.style.display = 'table-row';
        });
    }

    // Initialize chart animations
    document.addEventListener('DOMContentLoaded', function() {
        const bars = document.querySelectorAll('.chart-bar');
        bars.forEach((bar, index) => {
            setTimeout(() => {
                bar.style.opacity = '1';
                bar.style.transform = 'scaleY(1)';
            }, index * 100);
        });
    });
</script>