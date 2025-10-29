<?php
/**
 * Admin Activity Log Viewer
 * Displays comprehensive audit trail of all system activities
 */

// Start session and check authentication
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../includes/db_connect.php';

// Get admin info
$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f6fa;
            color: #333;
        }

        .page-header {
            background: linear-gradient(135deg, #263c79 0%, #1a2850 100%);
            color: white;
            padding: 2rem 2rem 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-title i {
            font-size: 2rem;
            color: #cfac69;
        }

        .page-title h1 {
            font-size: 1.75rem;
            font-weight: 700;
        }

        .page-title p {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-top: 0.25rem;
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn {
            padding: 0.6rem 1.25rem;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background: #cfac69;
            color: #263c79;
        }

        .btn-primary:hover {
            background: #e0bc7a;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(207, 172, 105, 0.3);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .main-content {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .filter-section {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .filter-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #263c79;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-group label {
            font-size: 0.85rem;
            font-weight: 500;
            color: #555;
        }

        .filter-group select,
        .filter-group input {
            padding: 0.6rem;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: border-color 0.3s ease;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #cfac69;
        }

        .filter-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-icon.blue {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .stat-icon.green {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .stat-icon.orange {
            background: linear-gradient(135deg, #ee9ca7 0%, #ffdde1 100%);
            color: #e74c3c;
        }

        .stat-icon.purple {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            color: #9b59b6;
        }

        .stat-details {
            flex: 1;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #263c79;
        }

        .stat-label {
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.25rem;
        }

        .log-table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .table-header {
            padding: 1.25rem 1.5rem;
            background: #f8f9fa;
            border-bottom: 2px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #263c79;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .table-header .table-info {
            font-size: 0.85rem;
            color: #666;
        }

        .log-table {
            width: 100%;
            border-collapse: collapse;
        }

        .log-table thead {
            background: #263c79;
            color: white;
        }

        .log-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .log-table tbody tr {
            border-bottom: 1px solid #e0e0e0;
            transition: background 0.2s ease;
        }

        .log-table tbody tr:hover {
            background: #f8f9fa;
        }

        .log-table td {
            padding: 1rem;
            font-size: 0.9rem;
            color: #555;
        }

        .log-type-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .log-type-badge.admin {
            background: #667eea;
            color: white;
        }

        .log-type-badge.student {
            background: #38ef7d;
            color: white;
        }

        .log-type-badge.system {
            background: #ffa726;
            color: white;
        }

        .action-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .action-badge.login {
            background: #e3f2fd;
            color: #1976d2;
        }

        .action-badge.logout {
            background: #fff3e0;
            color: #f57c00;
        }

        .action-badge.issue {
            background: #e8f5e9;
            color: #388e3c;
        }

        .action-badge.return {
            background: #fce4ec;
            color: #c2185b;
        }

        .action-badge.create {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .action-badge.update {
            background: #e0f2f1;
            color: #00796b;
        }

        .action-badge.delete {
            background: #ffebee;
            color: #c62828;
        }

        .loading {
            text-align: center;
            padding: 3rem;
            color: #999;
        }

        .no-data {
            text-align: center;
            padding: 3rem;
            color: #999;
        }

        .no-data i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            padding: 1.5rem;
            background: #f8f9fa;
        }

        .pagination button {
            padding: 0.5rem 1rem;
            border: 1px solid #e0e0e0;
            background: white;
            color: #263c79;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .pagination button:hover:not(:disabled) {
            background: #263c79;
            color: white;
        }

        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination .page-info {
            font-size: 0.9rem;
            color: #666;
            margin: 0 1rem;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                align-items: flex-start;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .log-table {
                font-size: 0.8rem;
            }

            .log-table th,
            .log-table td {
                padding: 0.75rem 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="header-content">
            <div class="page-title">
                <i class="fas fa-history"></i>
                <div>
                    <h1>Activity Log</h1>
                    <p>Complete audit trail of system activities</p>
                </div>
            </div>
            <div class="header-actions">
                <button class="btn btn-secondary" onclick="exportLogs()">
                    <i class="fas fa-download"></i>
                    Export
                </button>
                <button class="btn btn-primary" onclick="refreshLogs()">
                    <i class="fas fa-sync-alt"></i>
                    Refresh
                </button>
            </div>
        </div>
    </div>

    <div class="main-content">
        <!-- Statistics Cards -->
        <div class="stats-grid" id="statsCards">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-list"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-value" id="totalLogs">-</div>
                    <div class="stat-label">Total Activities</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-value" id="todayLogs">-</div>
                    <div class="stat-label">Today's Activities</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-value" id="activeUsers">-</div>
                    <div class="stat-label">Active Users (24h)</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-value" id="avgDaily">-</div>
                    <div class="stat-label">Avg. Daily Activities</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-section">
            <div class="filter-title">
                <i class="fas fa-filter"></i>
                Filter Logs
            </div>
            <div class="filter-grid">
                <div class="filter-group">
                    <label>User Type</label>
                    <select id="filterUserType">
                        <option value="">All Types</option>
                        <option value="Admin">Admin</option>
                        <option value="Student">Student</option>
                        <option value="System">System</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Action</label>
                    <select id="filterAction">
                        <option value="">All Actions</option>
                        <option value="Login">Login</option>
                        <option value="Logout">Logout</option>
                        <option value="Issue Book">Issue Book</option>
                        <option value="Return Book">Return Book</option>
                        <option value="Create">Create</option>
                        <option value="Update">Update</option>
                        <option value="Delete">Delete</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>From Date</label>
                    <input type="date" id="filterFromDate">
                </div>
                <div class="filter-group">
                    <label>To Date</label>
                    <input type="date" id="filterToDate">
                </div>
            </div>
            <div class="filter-actions">
                <button class="btn btn-secondary" onclick="clearFilters()">
                    <i class="fas fa-times"></i>
                    Clear
                </button>
                <button class="btn btn-primary" onclick="applyFilters()">
                    <i class="fas fa-search"></i>
                    Apply Filters
                </button>
            </div>
        </div>

        <!-- Activity Log Table -->
        <div class="log-table-container">
            <div class="table-header">
                <h3>
                    <i class="fas fa-list-ul"></i>
                    Activity Records
                </h3>
                <span class="table-info" id="tableInfo">Loading...</span>
            </div>
            <div id="tableContent">
                <div class="loading">
                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem;"></i>
                    <p style="margin-top: 1rem;">Loading activity logs...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let totalPages = 1;
        const logsPerPage = 50;
        let currentFilters = {};

        // Load statistics
        async function loadStatistics() {
            try {
                const response = await fetch('api/activity-log.php?action=stats');
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('totalLogs').textContent = data.data.total_logs.toLocaleString();
                    document.getElementById('todayLogs').textContent = data.data.today_logs.toLocaleString();
                    document.getElementById('activeUsers').textContent = data.data.active_users_24h.toLocaleString();
                    document.getElementById('avgDaily').textContent = data.data.avg_daily_logs.toLocaleString();
                }
            } catch (error) {
                console.error('Error loading statistics:', error);
            }
        }

        // Load activity logs
        async function loadLogs(page = 1) {
            currentPage = page;
            const tableContent = document.getElementById('tableContent');
            
            // Show loading
            tableContent.innerHTML = `
                <div class="loading">
                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem;"></i>
                    <p style="margin-top: 1rem;">Loading activity logs...</p>
                </div>
            `;

            try {
                const params = new URLSearchParams({
                    action: 'list',
                    page: page,
                    limit: logsPerPage,
                    ...currentFilters
                });

                const response = await fetch(`api/activity-log.php?${params}`);
                const data = await response.json();
                
                if (data.success) {
                    renderLogs(data.data.logs);
                    totalPages = data.data.total_pages;
                    updateTableInfo(data.data);
                    renderPagination();
                } else {
                    showNoData('Failed to load logs');
                }
            } catch (error) {
                console.error('Error loading logs:', error);
                showNoData('Error loading logs');
            }
        }

        // Render logs table
        function renderLogs(logs) {
            const tableContent = document.getElementById('tableContent');
            
            if (logs.length === 0) {
                showNoData('No activity logs found');
                return;
            }

            let html = `
                <table class="log-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Timestamp</th>
                            <th>User Type</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            logs.forEach(log => {
                const actionClass = getActionClass(log.Action);
                const typeClass = log.UserType.toLowerCase();
                
                html += `
                    <tr>
                        <td>#${log.LogID}</td>
                        <td>${formatTimestamp(log.Timestamp)}</td>
                        <td><span class="log-type-badge ${typeClass}">${log.UserType}</span></td>
                        <td>${log.UserName || 'Unknown'}</td>
                        <td><span class="action-badge ${actionClass}">${log.Action}</span></td>
                        <td>${log.Details || '-'}</td>
                        <td>${log.IPAddress || '-'}</td>
                    </tr>
                `;
            });

            html += `
                    </tbody>
                </table>
            `;

            tableContent.innerHTML = html;
            renderPagination();
        }

        // Get action class for badge
        function getActionClass(action) {
            action = action.toLowerCase();
            if (action.includes('login')) return 'login';
            if (action.includes('logout')) return 'logout';
            if (action.includes('issue')) return 'issue';
            if (action.includes('return')) return 'return';
            if (action.includes('create') || action.includes('add')) return 'create';
            if (action.includes('update') || action.includes('edit')) return 'update';
            if (action.includes('delete')) return 'delete';
            return 'login';
        }

        // Format timestamp
        function formatTimestamp(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diff = now - date;
            const hours = Math.floor(diff / 3600000);
            
            if (hours < 1) {
                const minutes = Math.floor(diff / 60000);
                return `${minutes}m ago`;
            } else if (hours < 24) {
                return `${hours}h ago`;
            } else {
                return date.toLocaleString('en-IN', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        }

        // Show no data message
        function showNoData(message) {
            const tableContent = document.getElementById('tableContent');
            tableContent.innerHTML = `
                <div class="no-data">
                    <i class="fas fa-inbox"></i>
                    <p>${message}</p>
                </div>
            `;
        }

        // Update table info
        function updateTableInfo(data) {
            document.getElementById('tableInfo').textContent = 
                `Showing ${data.logs.length} of ${data.total_logs.toLocaleString()} records`;
        }

        // Render pagination
        function renderPagination() {
            const tableContent = document.getElementById('tableContent');
            
            const paginationHTML = `
                <div class="pagination">
                    <button onclick="loadLogs(1)" ${currentPage === 1 ? 'disabled' : ''}>
                        <i class="fas fa-angle-double-left"></i>
                    </button>
                    <button onclick="loadLogs(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
                        <i class="fas fa-angle-left"></i> Previous
                    </button>
                    <span class="page-info">Page ${currentPage} of ${totalPages}</span>
                    <button onclick="loadLogs(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
                        Next <i class="fas fa-angle-right"></i>
                    </button>
                    <button onclick="loadLogs(${totalPages})" ${currentPage === totalPages ? 'disabled' : ''}>
                        <i class="fas fa-angle-double-right"></i>
                    </button>
                </div>
            `;
            
            if (!tableContent.querySelector('.pagination')) {
                tableContent.insertAdjacentHTML('beforeend', paginationHTML);
            }
        }

        // Apply filters
        function applyFilters() {
            currentFilters = {
                user_type: document.getElementById('filterUserType').value,
                action: document.getElementById('filterAction').value,
                from_date: document.getElementById('filterFromDate').value,
                to_date: document.getElementById('filterToDate').value
            };
            
            // Remove empty filters
            Object.keys(currentFilters).forEach(key => {
                if (!currentFilters[key]) delete currentFilters[key];
            });
            
            loadLogs(1);
        }

        // Clear filters
        function clearFilters() {
            document.getElementById('filterUserType').value = '';
            document.getElementById('filterAction').value = '';
            document.getElementById('filterFromDate').value = '';
            document.getElementById('filterToDate').value = '';
            currentFilters = {};
            loadLogs(1);
        }

        // Refresh logs
        function refreshLogs() {
            loadStatistics();
            loadLogs(currentPage);
        }

        // Export logs
        async function exportLogs() {
            try {
                const params = new URLSearchParams({
                    action: 'export',
                    ...currentFilters
                });
                
                window.location.href = `api/activity-log.php?${params}`;
            } catch (error) {
                console.error('Error exporting logs:', error);
                alert('Failed to export logs');
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadStatistics();
            loadLogs(1);
            
            // Set default date range (last 7 days)
            const today = new Date();
            const lastWeek = new Date(today);
            lastWeek.setDate(today.getDate() - 7);
            
            document.getElementById('filterFromDate').value = lastWeek.toISOString().split('T')[0];
            document.getElementById('filterToDate').value = today.toISOString().split('T')[0];
        });
    </script>
</body>
</html>
