<?php
// Admin Footfall Analytics Dashboard - Enhanced UI
// session_start();

// // Check admin authentication
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header('Location: login.php');
//     exit();
// }

require_once '../includes/db_connect.php';

$admin_id = $_SESSION['admin_id'] ?? 1;
$admin_name = $_SESSION['admin_name'] ?? 'Admin User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <title>Footfall Analytics</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background-color: #f5f5f5;
    color: #333;
    line-height: 1.6;
}

.page-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px;
    background-color: white;
    min-height: 100vh;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
}

.circulation-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #cfac69;
}

.circulation-title {
    color: #263c79;
    font-size: 28px;
    font-weight: 700;
    margin: 0;
}

.header-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-primary {
    background-color: #263c79;
    color: white;
}

.btn-primary:hover {
    background-color: #1e2d5f;
}

.btn-success {
    background-color: #28a745;
    color: white;
}

.btn-success:hover {
    background-color: #218838;
}

.btn-warning {
    background-color: #ffc107;
    color: #212529;
}

.btn-warning:hover {
    background-color: #e0a800;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-left: 4px solid #cfac69;
}

.stat-card.active {
    border-left-color: #28a745;
}

.stat-card.today {
    border-left-color: #263c79;
}

.stat-card.week {
    border-left-color: #17a2b8;
}

.stat-number {
    font-size: 32px;
    font-weight: 700;
    color: #263c79;
    margin-bottom: 5px;
}

.stat-label {
    color: #6c757d;
    font-size: 14px;
    font-weight: 500;
}

.tabs-container {
    margin-bottom: 20px;
}

.tab-buttons {
    display: flex;
    border-bottom: 2px solid #e9ecef;
    margin-bottom: 20px;
    overflow-x: auto;
}

.tab-btn {
    background: none;
    border: none;
    padding: 12px 20px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 500;
    color: #6c757d;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
    flex-shrink: 0;
    white-space: nowrap;
}

.tab-btn.active {
    color: #263c79;
    border-bottom-color: #cfac69;
    font-weight: 600;
}

.tab-btn:hover {
    color: #263c79;
    background-color: rgba(207, 172, 105, 0.1);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.search-filters {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.form-row {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.form-group {
    flex: 1;
    min-width: 200px;
}

.form-group label {
    display: block;
    color: #495057;
    font-weight: 500;
    margin-bottom: 5px;
    font-size: 14px;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    box-sizing: border-box;
}

.form-control:focus {
    outline: none;
    border-color: #cfac69;
    box-shadow: 0 0 0 2px rgba(207, 172, 105, 0.2);
}

.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.chart-card {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.chart-card h3 {
    color: #263c79;
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 20px;
}

.table-container {
    background: white;
    border-radius: 8px;
    overflow-x: auto;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.circulation-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    min-width: 600px;
}

.circulation-table th {
    background-color: #263c79;
    color: white;
    padding: 12px 8px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
}

.circulation-table td {
    padding: 12px 8px;
    border-bottom: 1px solid #e9ecef;
    font-size: 14px;
}

.circulation-table tr:hover {
    background-color: rgba(207, 172, 105, 0.1);
}

.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.badge-success {
    background-color: #d4edda;
    color: #155724;
}

.badge-danger {
    background-color: #f8d7da;
    color: #721c24;
}

.badge-warning {
    background-color: #fff3cd;
    color: #856404;
}

.badge-primary {
    background-color: #cfe2ff;
    color: #084298;
}

@media (max-width: 768px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        flex-direction: column;
    }
    
    .header-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .header-actions .btn {
        width: 100%;
    }
    
    .circulation-table {
        font-size: 12px;
    }
}
</style>
</head>
<body>

<div class="page-container">
    <div class="circulation-header">
        <h1 class="circulation-title">
            <i class="fas fa-chart-line"></i>
            Footfall Analytics
        </h1>
        <div class="header-actions">
            <button class="btn btn-success" onclick="exportToExcel()">
                <i class="fas fa-file-excel"></i>
                Export Excel
            </button>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i>
                Print
            </button>
            <a href="../footfall/scanner.php" class="btn btn-warning" target="_blank">
                <i class="fas fa-qrcode"></i>
                Open Scanner
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number" id="totalVisits">-</div>
            <div class="stat-label">Total Visits</div>
        </div>
        <div class="stat-card today">
            <div class="stat-number" id="uniqueVisitors">-</div>
            <div class="stat-label">Unique Visitors</div>
        </div>
        <div class="stat-card active">
            <div class="stat-number" id="activeNow">-</div>
            <div class="stat-label">Active Now</div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs-container">
        <div class="tab-buttons">
            <button class="tab-btn active" data-tab="analytics" onclick="showTab('analytics')">
                <i class="fas fa-chart-bar"></i>
                Analytics & Charts
            </button>
            <button class="tab-btn" data-tab="records" onclick="showTab('records')">
                <i class="fas fa-list"></i>
                All Records
            </button>
            <button class="tab-btn" data-tab="active" onclick="showTab('active')">
                <i class="fas fa-user-clock"></i>
                Currently Active
            </button>
            <button class="tab-btn" data-tab="reports" onclick="showTab('reports')">
                <i class="fas fa-file-alt"></i>
                Reports
            </button>
        </div>

        <!-- Analytics Tab -->
        <div id="analytics" class="tab-content active">
            <div class="search-filters">
                <h3 style="color: #263c79; margin-bottom: 15px; font-size: 16px;">
                    <i class="fas fa-filter"></i> Filter Data
                </h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="dateFrom">From Date</label>
                        <input type="date" id="dateFrom" class="form-control" value="<?php echo date('Y-m-01'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="dateTo">To Date</label>
                        <input type="date" id="dateTo" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="branchFilter">Branch</label>
                        <select id="branchFilter" class="form-control">
                            <option value="all">All Branches</option>
                            <option value="CS">Computer Science</option>
                            <option value="ETC">Electronics & Telecom</option>
                            <option value="MECH">Mechanical</option>
                            <option value="CIVIL">Civil</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="purposeFilter">Purpose</label>
                        <select id="purposeFilter" class="form-control">
                            <option value="all">All Purposes</option>
                            <option value="Library Visit">Library Visit</option>
                            <option value="Study">Study</option>
                            <option value="Research">Research</option>
                            <option value="Book Issue/Return">Book Issue/Return</option>
                            <option value="Reading">Reading</option>
                            <option value="Computer Use">Computer Use</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <button class="btn btn-primary" onclick="loadAnalytics()">
                        <i class="fas fa-search"></i>
                        Apply Filters
                    </button>
                    <button class="btn btn-secondary" onclick="resetFilters()">
                        <i class="fas fa-redo"></i>
                        Reset
                    </button>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="charts-grid">
                <div class="chart-card">
                    <h3><i class="fas fa-chart-line"></i> Daily Visits Trend</h3>
                    <canvas id="dailyChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3><i class="fas fa-chart-bar"></i> Hourly Distribution</h3>
                    <canvas id="hourlyChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3><i class="fas fa-chart-pie"></i> Purpose Distribution</h3>
                    <canvas id="purposeChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3><i class="fas fa-chart-pie"></i> Branch Distribution</h3>
                    <canvas id="branchChart"></canvas>
                </div>
            </div>
        </div>

        <!-- All Records Tab -->
        <div id="records" class="tab-content">
            <div class="search-filters">
                <div class="form-row">
                    <div class="form-group">
                        <label for="searchMember">Search Member</label>
                        <input type="text" id="searchMember" class="form-control" placeholder="Enter name or member number...">
                    </div>
                    <div class="form-group">
                        <label for="recordsDateFrom">From Date</label>
                        <input type="date" id="recordsDateFrom" class="form-control" value="<?php echo date('Y-m-01'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="recordsDateTo">To Date</label>
                        <input type="date" id="recordsDateTo" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group" style="display: flex; align-items: flex-end;">
                        <button class="btn btn-primary" onclick="loadAllRecords()" style="width: 100%;">
                            <i class="fas fa-search"></i>
                            Search
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table class="circulation-table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Name</th>
                            <th>Branch</th>
                            <th>Entry Time</th>
                            <th>Purpose</th>
                            <th>Method</th>
                        </tr>
                    </thead>
                    <tbody id="allRecordsTable">
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px;">
                                <i class="fas fa-spinner fa-spin"></i> Loading records...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Currently Active Tab -->
        <div id="active" class="tab-content">
            <div class="table-container">
                <table class="circulation-table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Name</th>
                            <th>Branch</th>
                            <th>Entry Time</th>
                            <th>Purpose</th>
                            <th>Method</th>
                        </tr>
                    </thead>
                    <tbody id="activeVisitorsTable">
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px;">
                                <i class="fas fa-spinner fa-spin"></i> Loading active visitors...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Reports Tab -->
        <div id="reports" class="tab-content">
            <div class="search-filters">
                <h3 style="color: #263c79; margin-bottom: 15px;">
                    <i class="fas fa-file-alt"></i> Generate Reports
                </h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="reportType">Report Type</label>
                        <select id="reportType" class="form-control">
                            <option value="daily">Daily Summary</option>
                            <option value="weekly">Weekly Summary</option>
                            <option value="monthly">Monthly Summary</option>
                            <option value="custom">Custom Date Range</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="reportFormat">Export Format</label>
                        <select id="reportFormat" class="form-control">
                            <option value="excel">Excel (XLSX)</option>
                            <option value="csv">CSV</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                    <div class="form-group" style="display: flex; align-items: flex-end;">
                        <button class="btn btn-success" onclick="generateReport()" style="width: 100%;">
                            <i class="fas fa-download"></i>
                            Generate Report
                        </button>
                    </div>
                </div>
            </div>

            <div class="stats-grid" style="margin-top: 30px;">
                <div class="stat-card">
                    <div class="stat-number" id="reportTotalVisits">0</div>
                    <div class="stat-label">Total Visits</div>
                </div>
                <div class="stat-card today">
                    <div class="stat-number" id="reportPeakHour">-</div>
                    <div class="stat-label">Peak Hour</div>
                </div>
                <div class="stat-card active">
                    <div class="stat-number" id="reportBusiestDay">-</div>
                    <div class="stat-label">Busiest Day</div>
                </div>
                <div class="stat-card week">
                    <div class="stat-number" id="reportAvgDaily">0</div>
                    <div class="stat-label">Avg Daily Visits</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
let dailyChart, hourlyChart, purposeChart, branchChart;

// Tab switching
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
        tab.style.display = 'none';
    });
    
    // Remove active from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    const selectedTab = document.getElementById(tabName);
    if (selectedTab) {
        selectedTab.classList.add('active');
        selectedTab.style.display = 'block';
    }
    
    // Activate button
    const activeButton = document.querySelector(`.tab-btn[data-tab="${tabName}"]`);
    if (activeButton) {
        activeButton.classList.add('active');
    }
    
    // Load content based on tab
    if (tabName === 'analytics') {
        loadAnalytics();
    } else if (tabName === 'records') {
        loadAllRecords();
    } else if (tabName === 'active') {
        loadActiveVisitors();
    } else if (tabName === 'reports') {
        loadReportStats();
    }
}

// Load statistics
async function loadStatistics() {
    try {
        const response = await fetch('../footfall/api/footfall-stats.php');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('totalVisits').textContent = data.data.today_visits || 0;
            document.getElementById('uniqueVisitors').textContent = data.data.week_visits || 0;
            document.getElementById('activeNow').textContent = data.data.active_visitors || 0;
        }
    } catch (error) {
        console.error('Error loading statistics:', error);
    }
}

// Load analytics and charts
async function loadAnalytics() {
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    try {
        const response = await fetch(`../footfall/api/analytics-data.php?date_from=${dateFrom}&date_to=${dateTo}`);
        const data = await response.json();
        
        if (data.success) {
            renderDailyChart(data.data.daily);
            renderHourlyChart(data.data.hourly);
            renderPurposeChart(data.data.purpose);
            renderBranchChart(data.data.branch);
        }
    } catch (error) {
        console.error('Error loading analytics:', error);
    }
}

// Render Daily Chart
function renderDailyChart(data) {
    const ctx = document.getElementById('dailyChart').getContext('2d');
    
    if (dailyChart) {
        dailyChart.destroy();
    }
    
    dailyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Daily Visits',
                data: data.values,
                borderColor: '#263c79',
                backgroundColor: 'rgba(38, 60, 121, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

// Render Hourly Chart
function renderHourlyChart(data) {
    const ctx = document.getElementById('hourlyChart').getContext('2d');
    
    if (hourlyChart) {
        hourlyChart.destroy();
    }
    
    hourlyChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Visits per Hour',
                data: data.values,
                backgroundColor: '#cfac69'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

// Render Purpose Chart
function renderPurposeChart(data) {
    const ctx = document.getElementById('purposeChart').getContext('2d');
    
    if (purposeChart) {
        purposeChart.destroy();
    }
    
    purposeChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                backgroundColor: [
                    '#263c79',
                    '#cfac69',
                    '#28a745',
                    '#17a2b8',
                    '#ffc107',
                    '#dc3545'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true
        }
    });
}

// Render Branch Chart
function renderBranchChart(data) {
    const ctx = document.getElementById('branchChart').getContext('2d');
    
    if (branchChart) {
        branchChart.destroy();
    }
    
    branchChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                backgroundColor: [
                    '#263c79',
                    '#cfac69',
                    '#28a745',
                    '#17a2b8',
                    '#ffc107'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true
        }
    });
}

// Load all records
async function loadAllRecords() {
    const dateFrom = document.getElementById('recordsDateFrom').value;
    const dateTo = document.getElementById('recordsDateTo').value;
    
    try {
        const response = await fetch(`../footfall/api/footfall-records.php?date_from=${dateFrom}&date_to=${dateTo}&limit=100`);
        const data = await response.json();
        
        if (data.success && data.data.records) {
            let html = '';
            
            if (data.data.records.length === 0) {
                html = '<tr><td colspan="6" style="text-align: center; padding: 20px;">No records found</td></tr>';
            } else {
                data.data.records.forEach(record => {
                    html += `
                        <tr>
                            <td>${escapeHtml(record.member_no)}</td>
                            <td>${escapeHtml(record.name)}</td>
                            <td>${escapeHtml(record.branch)}</td>
                            <td>${escapeHtml(record.entry_time)}</td>
                            <td>${escapeHtml(record.purpose)}</td>
                            <td><span class="badge badge-primary">${escapeHtml(record.method)}</span></td>
                        </tr>
                    `;
                });
            }
            
            document.getElementById('allRecordsTable').innerHTML = html;
        }
    } catch (error) {
        console.error('Error loading records:', error);
        document.getElementById('allRecordsTable').innerHTML = 
            '<tr><td colspan="6" style="text-align: center; padding: 20px; color: red;">Error loading records</td></tr>';
    }
}

// Load active visitors
async function loadActiveVisitors() {
    try {
        const response = await fetch('../footfall/api/footfall-records.php?status=Active&limit=50');
        const data = await response.json();
        
        if (data.success && data.data.records) {
            let html = '';
            
            if (data.data.records.length === 0) {
                html = '<tr><td colspan="6" style="text-align: center; padding: 20px;">No active visitors</td></tr>';
            } else {
                data.data.records.forEach(record => {
                    html += `
                        <tr>
                            <td>${escapeHtml(record.member_no)}</td>
                            <td>${escapeHtml(record.name)}</td>
                            <td>${escapeHtml(record.branch)}</td>
                            <td>${escapeHtml(record.entry_time)}</td>
                            <td>${escapeHtml(record.purpose)}</td>
                            <td><span class="badge badge-primary">${escapeHtml(record.method)}</span></td>
                        </tr>
                    `;
                });
            }
            
            document.getElementById('activeVisitorsTable').innerHTML = html;
        }
    } catch (error) {
        console.error('Error loading active visitors:', error);
    }
}

// Check out visitor
async function checkoutVisitor(memberNo) {
    if (!confirm('Check out this visitor?')) return;
    
    try {
        const response = await fetch('../footfall/api/checkout.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({member_identifier: memberNo})
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Visitor checked out successfully!');
            loadActiveVisitors();
            loadStatistics();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Network error occurred');
    }
}

// Load report stats
async function loadReportStats() {
    try {
        const response = await fetch('../footfall/api/footfall-stats.php');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('reportTotalVisits').textContent = data.data.month_visits || 0;
            document.getElementById('reportPeakHour').textContent = data.data.peak_hour || '-';
            document.getElementById('reportBusiestDay').textContent = 'Today';
            
            // Calculate average daily visits (month visits / days in month)
            const monthVisits = data.data.month_visits || 0;
            const today = new Date();
            const daysInMonth = today.getDate(); // Current day of month
            const avgDaily = daysInMonth > 0 ? Math.round(monthVisits / daysInMonth) : 0;
            document.getElementById('reportAvgDaily').textContent = avgDaily;
        }
    } catch (error) {
        console.error('Error loading report stats:', error);
    }
}

// Generate report
async function generateReport() {
    const reportType = document.getElementById('reportType').value;
    const reportFormat = document.getElementById('reportFormat').value;
    
    alert('Report generation feature will be implemented based on selected type: ' + reportType + ' in format: ' + reportFormat);
}

// Export to Excel
async function exportToExcel() {
    try {
        const dateFrom = document.getElementById('recordsDateFrom').value;
        const dateTo = document.getElementById('recordsDateTo').value;
        
        const response = await fetch(`../footfall/api/export-footfall.php?date_from=${dateFrom}&date_to=${dateTo}&format=json`);
        const data = await response.json();
        
        if (data.success && data.data.records) {
            const ws = XLSX.utils.json_to_sheet(data.data.records);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Footfall Data');
            XLSX.writeFile(wb, `footfall_report_${dateFrom}_to_${dateTo}.xlsx`);
        } else {
            alert('No data available to export');
        }
    } catch (error) {
        console.error('Export error:', error);
        alert('Error exporting data');
    }
}

// Reset filters
function resetFilters() {
    document.getElementById('dateFrom').value = '<?php echo date('Y-m-01'); ?>';
    document.getElementById('dateTo').value = '<?php echo date('Y-m-d'); ?>';
    document.getElementById('branchFilter').value = 'all';
    document.getElementById('purposeFilter').value = 'all';
    loadAnalytics();
}

// Utility function
function escapeHtml(text) {
    if (typeof text !== 'string' && typeof text !== 'number') return '';
    const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'};
    return String(text).replace(/[&<>"']/g, c => map[c]);
}

// Initialize function
function initFootfallAnalytics() {
    console.log('Footfall Analytics initialized');
    loadStatistics();
    loadAnalytics();
    
    // Auto-refresh every 60 seconds
    setInterval(() => {
        loadStatistics();
        const activeTab = document.querySelector('.tab-content.active');
        if (activeTab && activeTab.id === 'active') {
            loadActiveVisitors();
        }
    }, 60000);
}

// Initialize on page load (works for both direct access and AJAX loading)
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFootfallAnalytics);
} else {
    // DOM already loaded (AJAX case)
    initFootfallAnalytics();
}
</script>

</body>
</html>

