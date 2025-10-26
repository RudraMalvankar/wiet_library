<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'] ?? 'Admin User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Library System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #cfac69;
        }

        .header h1 {
            color: #263c79;
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header h1 i {
            color: #cfac69;
        }

        .back-btn {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #e0e0e0;
            flex-wrap: wrap;
        }

        .tab {
            padding: 15px 25px;
            background: transparent;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
        }

        .tab:hover {
            color: #263c79;
            background: #f8f9fa;
        }

        .tab.active {
            color: #263c79;
            border-bottom-color: #cfac69;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .controls {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: end;
        }

        .form-group {
            flex: 1;
            min-width: 200px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 13px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #263c79;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #263c79;
            color: white;
        }

        .btn-primary:hover {
            background: #1a2850;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(38, 60, 121, 0.3);
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .btn-info {
            background: #17a2b8;
            color: white;
        }

        .btn-info:hover {
            background: #138496;
            transform: translateY(-2px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #263c79 0%, #3d5a9e 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .stat-card.purple {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-card.green {
            background: linear-gradient(135deg, #28a745 0%, #34ce57 100%);
        }

        .stat-card.red {
            background: linear-gradient(135deg, #dc3545 0%, #e55561 100%);
        }

        .stat-card.blue {
            background: linear-gradient(135deg, #17a2b8 0%, #20c0de 100%);
        }

        .stat-number {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 14px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .chart-container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            border: 1px solid #e0e0e0;
        }

        .chart-container h3 {
            color: #263c79;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #cfac69;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .data-table thead {
            background: #263c79;
            color: white;
        }

        .data-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }

        .data-table tbody tr:hover {
            background: #f8f9fa;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #263c79;
        }

        .loading i {
            font-size: 48px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-chart-line"></i> Reports Dashboard</h1>
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <div class="tabs">
            <button class="tab active" onclick="switchTab('circulation')">
                <i class="fas fa-exchange-alt"></i> Circulation Reports
            </button>
            <button class="tab" onclick="switchTab('financial')">
                <i class="fas fa-rupee-sign"></i> Financial Reports
            </button>
            <button class="tab" onclick="switchTab('inventory')">
                <i class="fas fa-boxes"></i> Inventory Reports
            </button>
            <button class="tab" onclick="switchTab('members')">
                <i class="fas fa-users"></i> Member Reports
            </button>
        </div>

        <!-- Circulation Reports Tab -->
        <div id="circulation" class="tab-content active">
            <div class="controls">
                <div class="form-group">
                    <label>From Date</label>
                    <input type="date" id="circ_from" value="<?php echo date('Y-m-01'); ?>">
                </div>
                <div class="form-group">
                    <label>To Date</label>
                    <input type="date" id="circ_to" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="btn-group">
                    <button class="btn btn-primary" onclick="loadCirculationReport()">
                        <i class="fas fa-sync"></i> Generate Report
                    </button>
                    <button class="btn btn-success" onclick="exportReport('circulation', 'pdf')">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                    <button class="btn btn-info" onclick="exportReport('circulation', 'excel')">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                </div>
            </div>

            <div id="circ_stats" class="stats-grid"></div>
            <div id="circ_charts"></div>
            <div id="circ_table"></div>
        </div>

        <!-- Financial Reports Tab -->
        <div id="financial" class="tab-content">
            <div class="controls">
                <div class="form-group">
                    <label>From Date</label>
                    <input type="date" id="fin_from" value="<?php echo date('Y-m-01'); ?>">
                </div>
                <div class="form-group">
                    <label>To Date</label>
                    <input type="date" id="fin_to" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="btn-group">
                    <button class="btn btn-primary" onclick="loadFinancialReport()">
                        <i class="fas fa-sync"></i> Generate Report
                    </button>
                    <button class="btn btn-success" onclick="exportReport('financial', 'pdf')">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                    <button class="btn btn-info" onclick="exportReport('financial', 'excel')">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                </div>
            </div>

            <div id="fin_stats" class="stats-grid"></div>
            <div id="fin_charts"></div>
            <div id="fin_table"></div>
        </div>

        <!-- Inventory Reports Tab -->
        <div id="inventory" class="tab-content">
            <div class="controls">
                <div class="form-group">
                    <label>Report Type</label>
                    <select id="inv_type">
                        <option value="summary">Stock Summary</option>
                        <option value="acquisitions">New Acquisitions</option>
                        <option value="condition">Book Condition</option>
                        <option value="low_stock">Low Stock Alert</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>From Date</label>
                    <input type="date" id="inv_from" value="<?php echo date('Y-m-01'); ?>">
                </div>
                <div class="form-group">
                    <label>To Date</label>
                    <input type="date" id="inv_to" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="btn-group">
                    <button class="btn btn-primary" onclick="loadInventoryReport()">
                        <i class="fas fa-sync"></i> Generate Report
                    </button>
                    <button class="btn btn-success" onclick="exportReport('inventory', 'pdf')">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                    <button class="btn btn-info" onclick="exportReport('inventory', 'excel')">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                </div>
            </div>

            <div id="inv_stats" class="stats-grid"></div>
            <div id="inv_charts"></div>
            <div id="inv_table"></div>
        </div>

        <!-- Member Reports Tab -->
        <div id="members" class="tab-content">
            <div class="controls">
                <div class="form-group">
                    <label>Report Type</label>
                    <select id="mem_type">
                        <option value="summary">Member Summary</option>
                        <option value="department">Department-wise</option>
                        <option value="activity">Activity Report</option>
                        <option value="registrations">New Registrations</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>From Date</label>
                    <input type="date" id="mem_from" value="<?php echo date('Y-m-01'); ?>">
                </div>
                <div class="form-group">
                    <label>To Date</label>
                    <input type="date" id="mem_to" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="btn-group">
                    <button class="btn btn-primary" onclick="loadMemberReport()">
                        <i class="fas fa-sync"></i> Generate Report
                    </button>
                    <button class="btn btn-success" onclick="exportReport('members', 'pdf')">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                    <button class="btn btn-info" onclick="exportReport('members', 'excel')">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                </div>
            </div>

            <div id="mem_stats" class="stats-grid"></div>
            <div id="mem_charts"></div>
            <div id="mem_table"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        let currentCharts = [];

        function switchTab(tab) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(t => {
                t.classList.remove('active');
            });

            // Show selected tab
            document.getElementById(tab).classList.add('active');
            event.target.closest('.tab').classList.add('active');

            // Destroy existing charts
            currentCharts.forEach(chart => chart.destroy());
            currentCharts = [];

            // Auto-load report
            if (tab === 'circulation') loadCirculationReport();
            if (tab === 'financial') loadFinancialReport();
            if (tab === 'inventory') loadInventoryReport();
            if (tab === 'members') loadMemberReport();
        }

        // Circulation Reports
        function loadCirculationReport() {
            const from = document.getElementById('circ_from').value;
            const to = document.getElementById('circ_to').value;

            showLoading('circ_stats');
            showLoading('circ_charts');
            showLoading('circ_table');

            fetch(`api/reports.php?action=circulation&from=${from}&to=${to}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        displayCircStats(data.stats);
                        displayCircCharts(data.charts);
                        displayCircTable(data.details);
                    } else {
                        showError('circ_stats', data.message);
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    showError('circ_stats', 'Failed to load report: ' + err.message);
                });
        }

        function displayCircStats(stats) {
            const html = `
                <div class="stat-card purple">
                    <div class="stat-number">${stats.totalIssued || 0}</div>
                    <div class="stat-label">Total Issued</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-number">${stats.totalReturned || 0}</div>
                    <div class="stat-label">Total Returned</div>
                </div>
                <div class="stat-card red">
                    <div class="stat-number">${stats.overdue || 0}</div>
                    <div class="stat-label">Overdue Books</div>
                </div>
                <div class="stat-card blue">
                    <div class="stat-number">${stats.activeMembers || 0}</div>
                    <div class="stat-label">Active Members</div>
                </div>
            `;
            document.getElementById('circ_stats').innerHTML = html;
        }

        function displayCircCharts(charts) {
            currentCharts.forEach(chart => chart.destroy());
            currentCharts = [];

            const html = `
                <div class="chart-container">
                    <h3>Daily Circulation Trend</h3>
                    <canvas id="circTrendChart" height="80"></canvas>
                </div>
            `;
            document.getElementById('circ_charts').innerHTML = html;

            const ctx = document.getElementById('circTrendChart');
            if (ctx && charts.trend && charts.trend.labels.length > 0) {
                const chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: charts.trend.labels.map(d => formatDate(d)),
                        datasets: [{
                            label: 'Issues',
                            data: charts.trend.issues,
                            borderColor: '#263c79',
                            backgroundColor: 'rgba(38, 60, 121, 0.1)',
                            tension: 0.4
                        }, {
                            label: 'Returns',
                            data: charts.trend.returns,
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'top' } }
                    }
                });
                currentCharts.push(chart);
            }
        }

        function displayCircTable(details) {
            let html = '<div class="chart-container"><h3>Circulation Details</h3>';
            html += '<table class="data-table"><thead><tr>' +
                '<th>Member</th><th>Book</th><th>Issue Date</th><th>Due Date</th><th>Return Date</th><th>Status</th>' +
                '</tr></thead><tbody>';

            if (details && details.length > 0) {
                details.forEach(row => {
                    const status = row.ReturnDate ? 'Returned' : (new Date(row.DueDate) < new Date() ? 'Overdue' : 'Issued');
                    const statusColor = status === 'Returned' ? '#28a745' : (status === 'Overdue' ? '#dc3545' : '#ffc107');
                    
                    html += `<tr>
                        <td>${row.MemberName} (${row.MemberNo})</td>
                        <td>${row.Title || 'N/A'}</td>
                        <td>${formatDate(row.IssueDate)}</td>
                        <td>${formatDate(row.DueDate)}</td>
                        <td>${row.ReturnDate ? formatDate(row.ReturnDate) : '-'}</td>
                        <td><span style="color: ${statusColor}; font-weight: 600;">${status}</span></td>
                    </tr>`;
                });
            } else {
                html += '<tr><td colspan="6" class="no-data">No records found</td></tr>';
            }

            html += '</tbody></table></div>';
            document.getElementById('circ_table').innerHTML = html;
        }

        // Financial Reports
        function loadFinancialReport() {
            const from = document.getElementById('fin_from').value;
            const to = document.getElementById('fin_to').value;

            showLoading('fin_stats');
            showLoading('fin_charts');
            showLoading('fin_table');

            fetch(`api/reports.php?action=financial&from=${from}&to=${to}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        displayFinStats(data.stats);
                        displayFinCharts(data.charts);
                        displayFinTable(data.details);
                    } else {
                        showError('fin_stats', data.message);
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    showError('fin_stats', 'Failed to load report: ' + err.message);
                });
        }

        function displayFinStats(stats) {
            const html = `
                <div class="stat-card green">
                    <div class="stat-number">₹${stats.totalCollected || 0}</div>
                    <div class="stat-label">Total Collected</div>
                </div>
                <div class="stat-card red">
                    <div class="stat-number">₹${stats.pendingFines || 0}</div>
                    <div class="stat-label">Pending Fines</div>
                </div>
                <div class="stat-card blue">
                    <div class="stat-number">₹${stats.totalWaived || 0}</div>
                    <div class="stat-label">Total Waived</div>
                </div>
                <div class="stat-card purple">
                    <div class="stat-number">${stats.transactions || 0}</div>
                    <div class="stat-label">Transactions</div>
                </div>
            `;
            document.getElementById('fin_stats').innerHTML = html;
        }

        function displayFinCharts(charts) {
            currentCharts.forEach(chart => chart.destroy());
            currentCharts = [];

            const html = `
                <div class="chart-container">
                    <h3>Daily Collection Trend</h3>
                    <canvas id="finTrendChart" height="80"></canvas>
                </div>
            `;
            document.getElementById('fin_charts').innerHTML = html;

            const ctx = document.getElementById('finTrendChart');
            if (ctx && charts.trend && charts.trend.labels.length > 0) {
                const chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: charts.trend.labels.map(d => formatDate(d)),
                        datasets: [{
                            label: 'Collection (₹)',
                            data: charts.trend.values,
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'top' } }
                    }
                });
                currentCharts.push(chart);
            }
        }

        function displayFinTable(details) {
            let html = '<div class="chart-container"><h3>Payment Details</h3>';
            html += '<table class="data-table"><thead><tr>' +
                '<th>Receipt No</th><th>Date</th><th>Member</th><th>Fine</th><th>Paid</th><th>Method</th>' +
                '</tr></thead><tbody>';

            if (details && details.length > 0) {
                details.forEach(row => {
                    html += `<tr>
                        <td>${row.ReceiptNo}</td>
                        <td>${formatDate(row.PaymentDate)}</td>
                        <td>${row.MemberName} (${row.MemberNo})</td>
                        <td>₹${parseFloat(row.FineAmount).toFixed(2)}</td>
                        <td>₹${parseFloat(row.PaidAmount).toFixed(2)}</td>
                        <td>${row.PaymentMethod}</td>
                    </tr>`;
                });
            } else {
                html += '<tr><td colspan="6" class="no-data">No records found</td></tr>';
            }

            html += '</tbody></table></div>';
            document.getElementById('fin_table').innerHTML = html;
        }

        // Inventory Reports
        function loadInventoryReport() {
            const type = document.getElementById('inv_type').value;
            const from = document.getElementById('inv_from').value;
            const to = document.getElementById('inv_to').value;

            showLoading('inv_stats');
            showLoading('inv_charts');
            showLoading('inv_table');

            fetch(`api/reports.php?action=inventory&type=${type}&from=${from}&to=${to}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        displayInvStats(data.stats);
                        displayInvCharts(data.charts);
                        displayInvTable(data.details);
                    } else {
                        showError('inv_stats', data.message);
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    showError('inv_stats', 'Failed to load report: ' + err.message);
                });
        }

        function displayInvStats(stats) {
            const html = `
                <div class="stat-card blue">
                    <div class="stat-number">${stats.totalBooks || 0}</div>
                    <div class="stat-label">Total Books</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-number">${stats.available || 0}</div>
                    <div class="stat-label">Available</div>
                </div>
                <div class="stat-card purple">
                    <div class="stat-number">${stats.issued || 0}</div>
                    <div class="stat-label">Currently Issued</div>
                </div>
                <div class="stat-card red">
                    <div class="stat-number">${stats.damaged || 0}</div>
                    <div class="stat-label">Damaged/Lost</div>
                </div>
            `;
            document.getElementById('inv_stats').innerHTML = html;
        }

        function displayInvCharts(charts) {
            currentCharts.forEach(chart => chart.destroy());
            currentCharts = [];

            const html = `
                <div class="chart-container">
                    <h3>Category-wise Stock</h3>
                    <canvas id="invCategoryChart" height="80"></canvas>
                </div>
            `;
            document.getElementById('inv_charts').innerHTML = html;

            const ctx = document.getElementById('invCategoryChart');
            if (ctx && charts.category && charts.category.labels.length > 0) {
                const chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: charts.category.labels,
                        datasets: [{
                            label: 'Number of Books',
                            data: charts.category.values,
                            backgroundColor: 'rgba(38, 60, 121, 0.8)'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } }
                    }
                });
                currentCharts.push(chart);
            }
        }

        function displayInvTable(details) {
            let html = '<div class="chart-container"><h3>Inventory Details</h3>';
            
            if (details && details.length > 0) {
                html += '<table class="data-table"><thead><tr>';
                Object.keys(details[0]).forEach(key => {
                    html += `<th>${key}</th>`;
                });
                html += '</tr></thead><tbody>';
                
                details.forEach(row => {
                    html += '<tr>';
                    Object.values(row).forEach(val => {
                        html += `<td>${val || '-'}</td>`;
                    });
                    html += '</tr>';
                });
                html += '</tbody></table>';
            } else {
                html += '<p class="no-data">No records found</p>';
            }
            
            html += '</div>';
            document.getElementById('inv_table').innerHTML = html;
        }

        // Member Reports
        function loadMemberReport() {
            const type = document.getElementById('mem_type').value;
            const from = document.getElementById('mem_from').value;
            const to = document.getElementById('mem_to').value;

            showLoading('mem_stats');
            showLoading('mem_charts');
            showLoading('mem_table');

            fetch(`api/reports.php?action=members&type=${type}&from=${from}&to=${to}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        displayMemStats(data.stats);
                        displayMemCharts(data.charts);
                        displayMemTable(data.details);
                    } else {
                        showError('mem_stats', data.message);
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    showError('mem_stats', 'Failed to load report: ' + err.message);
                });
        }

        function displayMemStats(stats) {
            const html = `
                <div class="stat-card blue">
                    <div class="stat-number">${stats.totalMembers || 0}</div>
                    <div class="stat-label">Total Members</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-number">${stats.activeMembers || 0}</div>
                    <div class="stat-label">Active Members</div>
                </div>
                <div class="stat-card red">
                    <div class="stat-number">${stats.inactiveMembers || 0}</div>
                    <div class="stat-label">Inactive Members</div>
                </div>
                <div class="stat-card purple">
                    <div class="stat-number">${stats.newRegistrations || 0}</div>
                    <div class="stat-label">New This Month</div>
                </div>
            `;
            document.getElementById('mem_stats').innerHTML = html;
        }

        function displayMemCharts(charts) {
            currentCharts.forEach(chart => chart.destroy());
            currentCharts = [];

            const html = `
                <div class="chart-container">
                    <h3>Department-wise Distribution</h3>
                    <canvas id="memDeptChart" height="80"></canvas>
                </div>
            `;
            document.getElementById('mem_charts').innerHTML = html;

            const ctx = document.getElementById('memDeptChart');
            if (ctx && charts.department && charts.department.labels.length > 0) {
                const chart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: charts.department.labels,
                        datasets: [{
                            data: charts.department.values,
                            backgroundColor: ['#263c79', '#28a745', '#dc3545', '#17a2b8', '#ffc107', '#6f42c1']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'right' } }
                    }
                });
                currentCharts.push(chart);
            }
        }

        function displayMemTable(details) {
            let html = '<div class="chart-container"><h3>Member Details</h3>';
            
            if (details && details.length > 0) {
                html += '<table class="data-table"><thead><tr>';
                Object.keys(details[0]).forEach(key => {
                    html += `<th>${key}</th>`;
                });
                html += '</tr></thead><tbody>';
                
                details.forEach(row => {
                    html += '<tr>';
                    Object.values(row).forEach(val => {
                        html += `<td>${val || '-'}</td>`;
                    });
                    html += '</tr>';
                });
                html += '</tbody></table>';
            } else {
                html += '<p class="no-data">No records found</p>';
            }
            
            html += '</div>';
            document.getElementById('mem_table').innerHTML = html;
        }

        // Export function
        function exportReport(type, format) {
            let url = `api/reports.php?action=${type}&export=${format}`;
            
            if (type === 'circulation') {
                url += `&from=${document.getElementById('circ_from').value}&to=${document.getElementById('circ_to').value}`;
            } else if (type === 'financial') {
                url += `&from=${document.getElementById('fin_from').value}&to=${document.getElementById('fin_to').value}`;
            } else if (type === 'inventory') {
                url += `&type=${document.getElementById('inv_type').value}&from=${document.getElementById('inv_from').value}&to=${document.getElementById('inv_to').value}`;
            } else if (type === 'members') {
                url += `&type=${document.getElementById('mem_type').value}&from=${document.getElementById('mem_from').value}&to=${document.getElementById('mem_to').value}`;
            }
            
            window.open(url, '_blank');
        }

        // Utility functions
        function showLoading(elementId) {
            document.getElementById(elementId).innerHTML = '<div class="loading"><i class="fas fa-spinner"></i><br>Loading...</div>';
        }

        function showError(elementId, message) {
            document.getElementById(elementId).innerHTML = `<div class="alert alert-danger"><strong>Error:</strong> ${message}</div>`;
        }

        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
        }

        // Auto-load first report
        document.addEventListener('DOMContentLoaded', function() {
            loadCirculationReport();
        });
    </script>
</body>
</html>
