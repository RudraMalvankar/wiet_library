<?php
// Footfall Analytics Dashboard - Admin View
session_start();

// Sample footfall data for demonstration
$sampleFootfallData = [
    ['FootfallID' => 1, 'MemberNo' => 101, 'Date' => '2024-10-01', 'TimeIn' => '09:15:00', 'TimeOut' => '11:30:00'],
    ['FootfallID' => 2, 'MemberNo' => 102, 'Date' => '2024-10-01', 'TimeIn' => '10:20:00', 'TimeOut' => '12:45:00'],
    ['FootfallID' => 3, 'MemberNo' => 103, 'Date' => '2024-10-01', 'TimeIn' => '14:30:00', 'TimeOut' => '16:15:00'],
    ['FootfallID' => 4, 'MemberNo' => 101, 'Date' => '2024-10-02', 'TimeIn' => '08:45:00', 'TimeOut' => '10:20:00'],
    ['FootfallID' => 5, 'MemberNo' => 104, 'Date' => '2024-10-02', 'TimeIn' => '11:00:00', 'TimeOut' => '13:30:00'],
    ['FootfallID' => 6, 'MemberNo' => 105, 'Date' => '2024-10-02', 'TimeIn' => '15:20:00', 'TimeOut' => '17:45:00'],
    ['FootfallID' => 7, 'MemberNo' => 102, 'Date' => '2024-10-03', 'TimeIn' => '09:30:00', 'TimeOut' => '11:15:00'],
    ['FootfallID' => 8, 'MemberNo' => 106, 'Date' => '2024-10-03', 'TimeIn' => '13:45:00', 'TimeOut' => '15:30:00'],
];

// Sample member data for analysis
$sampleMembers = [
    101 => ['Name' => 'Rahul Sharma', 'Branch' => 'Computer Science', 'Group' => 'Student'],
    102 => ['Name' => 'Priya Patel', 'Branch' => 'Electronics', 'Group' => 'Student'],
    103 => ['Name' => 'Amit Kumar', 'Branch' => 'Mechanical', 'Group' => 'Faculty'],
    104 => ['Name' => 'Sneha Gupta', 'Branch' => 'Civil', 'Group' => 'Student'],
    105 => ['Name' => 'Vikram Singh', 'Branch' => 'Computer Science', 'Group' => 'Student'],
    106 => ['Name' => 'Dr. Rajesh Verma', 'Branch' => 'Physics', 'Group' => 'Faculty'],
    107 => ['Name' => 'Anita Desai', 'Branch' => 'Mathematics', 'Group' => 'Faculty'],
];

// Calculate analytics
$totalVisits = count($sampleFootfallData);
$uniqueVisitors = count(array_unique(array_column($sampleFootfallData, 'MemberNo')));
$avgDuration = 0;
$totalDuration = 0;

foreach ($sampleFootfallData as $visit) {
    if ($visit['TimeOut']) {
        $timeIn = strtotime($visit['TimeIn']);
        $timeOut = strtotime($visit['TimeOut']);
        $duration = ($timeOut - $timeIn) / 3600; // Convert to hours
        $totalDuration += $duration;
    }
}

$avgDuration = $totalVisits > 0 ? round($totalDuration / $totalVisits, 2) : 0;

// Peak hours analysis
$hourlyVisits = [];
for ($i = 8; $i <= 18; $i++) {
    $hourlyVisits[$i] = 0;
}

foreach ($sampleFootfallData as $visit) {
    $hour = (int) date('H', strtotime($visit['TimeIn']));
    if (isset($hourlyVisits[$hour])) {
        $hourlyVisits[$hour]++;
    }
}

// Daily visits
$dailyVisits = [];
foreach ($sampleFootfallData as $visit) {
    $date = $visit['Date'];
    if (!isset($dailyVisits[$date])) {
        $dailyVisits[$date] = 0;
    }
    $dailyVisits[$date]++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Footfall Analytics - WIET Library</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: "Lato", sans-serif;
        font-weight: 400;
        font-style: normal;
        background: white;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        padding: 70px;
        max-width: 1400px;
        margin: 0 auto;
        width: 100%;
    }

    h1, h2, h3, h4, h5, h6 {
        font-family: "Poppins", sans-serif;
        font-weight: 700;
    }



    .analytics-header {
        text-align: center;
        margin-bottom: 1.5rem;
        border-bottom: 2px solid #263c79;
        padding-bottom: 1rem;
        position: relative;
        z-index: 2;
    }

    .analytics-title {
        color: #263c79;
        font-size: 1.8rem;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.8rem;
    }

    .analytics-subtitle {
        color: #6c757d;
        font-size: 0.9rem;
        font-weight: 400;
    }

    .stats-overview {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        /* background: linear-gradient(135deg, #263c79 0%, #1e2d5f 100%); */
        color: #263c79;
        padding: .5rem;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 8px 20px rgba(38, 60, 121, 0.3);
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-number {
        font-size: 1.3rem;
        font-weight: 700;
        color: #263c79;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        font-size: 0.85rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .stat-icon {
        font-size: 0.9rem;
        margin-bottom: 0.8rem;
        color: #263c79;
    }

    .charts-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .chart-container {
        background: #f8f9fa;
        border: 2px solid #263c79;
        border-radius: 15px;
        padding: 1.2rem;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .chart-title {
        color: #263c79;
        font-size: 1.1rem;
        margin-bottom: 1rem;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .recent-activity {
        background: #f8f9fa;
        border: 2px solid #263c79;
        border-radius: 15px;
        padding: 1.2rem;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .activity-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }

    .activity-table th {
        background: #263c79;
        color: white;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .activity-table td {
        padding: 12px;
        border-bottom: 1px solid #e9ecef;
        font-size: 0.9rem;
    }

    .activity-table tr:hover {
        background-color: rgba(207, 172, 105, 0.1);
    }

    .member-badge {
        background: #263c79;
        color: white;
        padding: 2px 6px;
        border-radius: 8px;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .duration-badge {
        background: #28a745;
        color: white;
        padding: 4px 8px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .time-badge {
        background: #17a2b8;
        color: white;
        padding: 2px 6px;
        border-radius: 6px;
        font-size: 0.75rem;
    }



    .filter-section {
        background: #e8f4f8;
        border: 2px solid #17a2b8;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 2rem;
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        min-width: 150px;
    }

    .filter-label {
        color: #263c79;
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
    }

    .filter-input {
        padding: 0.4rem;
        border: 2px solid #263c79;
        border-radius: 5px;
        font-size: 0.85rem;
    }

    .filter-button {
        background: #263c79;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 25px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 1.25rem;
        transition: all 0.3s ease;
    }

    .filter-button:hover {
        background: #1e2d5f;
        color: white;
    }

    @media (max-width: 768px) {
        body {
            padding: 20px;
        }

        .analytics-title {
            font-size: 1.4rem;
            flex-direction: column;
            gap: 0.5rem;
        }

        .analytics-subtitle {
            font-size: 0.8rem;
        }

        .charts-section {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .stats-overview {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .stat-card {
            padding: 1rem;
        }

        .stat-number {
            font-size: 1.1rem;
        }

        .stat-label {
            font-size: 0.75rem;
        }

        .filter-section {
            flex-direction: column;
            align-items: stretch;
            gap: 0.5rem;
        }

        .filter-group {
            min-width: auto;
        }

        .filter-button {
            margin-top: 0.5rem;
            padding: 0.6rem 1rem;
        }

        .chart-container {
            padding: 1rem;
        }

        .chart-title {
            font-size: 1rem;
            margin-bottom: 0.8rem;
        }

        .recent-activity {
            padding: 1rem;
            overflow-x: auto;
        }

        .activity-table {
            font-size: 0.75rem;
            min-width: 500px;
        }

        .activity-table th,
        .activity-table td {
            padding: 8px;
        }

        .member-badge {
            font-size: 0.6rem;
            padding: 1px 4px;
        }

        .time-badge {
            font-size: 0.65rem;
            padding: 1px 4px;
        }
    }

    @media (max-width: 480px) {
        body {
            padding: 15px;
        }

        .analytics-title {
            font-size: 1.2rem;
        }

        .stats-overview {
            grid-template-columns: 1fr;
            gap: 0.8rem;
        }

        .stat-card {
            padding: 0.8rem;
        }

        .chart-container {
            padding: 0.8rem;
        }

        .chart-title {
            font-size: 0.9rem;
        }

        .filter-section {
            padding: 0.8rem;
        }

        .filter-input {
            padding: 0.5rem;
            font-size: 0.8rem;
        }

        .activity-table {
            font-size: 0.7rem;
        }

        .activity-table th,
        .activity-table td {
            padding: 6px;
        }
    }
</style>

<body>
    <div class="analytics-header">
            <h1 class="analytics-title">
                <i class="fas fa-chart-line"></i>
                Library Footfall Analytics
            </h1>
            <p class="analytics-subtitle">Comprehensive visitor insights and activity tracking</p>
        </div>

        <!-- Statistics Overview -->
        <div class="stats-overview">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?php echo $totalVisits; ?></div>
                <div class="stat-label">Total Visits</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-friends"></i>
                </div>
                <div class="stat-number"><?php echo $uniqueVisitors; ?></div>
                <div class="stat-label">Unique Visitors</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number"><?php echo $avgDuration; ?>h</div>
                <div class="stat-label">Avg Duration</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-number"><?php echo count($dailyVisits); ?></div>
                <div class="stat-label">Active Days</div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-group">
                <label class="filter-label">Date From</label>
                <input type="date" class="filter-input" value="2024-10-01">
            </div>
            <div class="filter-group">
                <label class="filter-label">Date To</label>
                <input type="date" class="filter-input" value="2024-10-03">
            </div>
            <div class="filter-group">
                <label class="filter-label">Member Group</label>
                <select class="filter-input">
                    <option value="">All Groups</option>
                    <option value="Student">Students</option>
                    <option value="Faculty">Faculty</option>
                    <option value="Staff">Staff</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">Branch</label>
                <select class="filter-input">
                    <option value="">All Branches</option>
                    <option value="Computer Science">Computer Science</option>
                    <option value="Electronics">Electronics</option>
                    <option value="Mechanical">Mechanical</option>
                    <option value="Civil">Civil</option>
                </select>
            </div>
            <button class="filter-button">
                <i class="fas fa-filter"></i>
                Apply Filters
            </button>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <div class="chart-container">
                <h3 class="chart-title">
                    <i class="fas fa-chart-bar"></i>
                    Peak Hours Analysis
                </h3>
                <canvas id="hourlyChart" width="400" height="200"></canvas>
            </div>
            <div class="chart-container">
                <h3 class="chart-title">
                    <i class="fas fa-chart-line"></i>
                    Daily Visits Trend
                </h3>
                <canvas id="dailyChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Recent Activity Table -->
        <div class="recent-activity">
            <h3 class="chart-title">
                <i class="fas fa-history"></i>
                Recent Visitor Activity
            </h3>
            <table class="activity-table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Branch</th>
                        <th>Group</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse($sampleFootfallData) as $visit): ?>
                    <?php 
                        $member = $sampleMembers[$visit['MemberNo']];
                        $timeIn = strtotime($visit['TimeIn']);
                        $timeOut = $visit['TimeOut'] ? strtotime($visit['TimeOut']) : null;
                        $duration = $timeOut ? round(($timeOut - $timeIn) / 3600, 2) : 'Ongoing';
                    ?>
                    <tr>
                        <td>
                            <span class="member-badge"><?php echo $visit['MemberNo']; ?></span>
                            <strong><?php echo $member['Name']; ?></strong>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($visit['Date'])); ?></td>
                        <td><span class="time-badge"><?php echo date('g:i A', $timeIn); ?></span></td>
                        <td><?php echo $member['Branch']; ?></td>
                        <td>
                            <span style="background: <?php echo $member['Group'] == 'Faculty' ? '#28a745' : '#17a2b8'; ?>; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem;">
                                <?php echo $member['Group']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <script>
        // Hourly visits chart
        const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
        const hourlyData = <?php echo json_encode(array_values($hourlyVisits)); ?>;
        const hourlyLabels = <?php echo json_encode(array_map(function($h) { return $h . ':00'; }, array_keys($hourlyVisits))); ?>;

        new Chart(hourlyCtx, {
            type: 'bar',
            data: {
                labels: hourlyLabels,
                datasets: [{
                    label: 'Visits per Hour',
                    data: hourlyData,
                    backgroundColor: 'rgba(38, 60, 121, 0.8)',
                    borderWidth: 0,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Daily visits chart
        const dailyCtx = document.getElementById('dailyChart').getContext('2d');
        const dailyData = <?php echo json_encode(array_values($dailyVisits)); ?>;
        const dailyLabels = <?php echo json_encode(array_map(function($d) { return date('M j', strtotime($d)); }, array_keys($dailyVisits))); ?>;

        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: dailyLabels,
                datasets: [{
                    label: 'Daily Visits',
                    data: dailyData,
                    backgroundColor: 'rgba(207, 172, 105, 0.2)',
                    borderColor: '#cfac69',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#263c79',
                    pointBorderColor: '#cfac69',
                    pointBorderWidth: 2,
                    pointRadius: 6
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
</body>
</html>
