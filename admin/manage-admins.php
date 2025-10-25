<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'] ?? 1;
$admin_name = $_SESSION['admin_name'] ?? 'Admin User';

// Fetch all admins from database
try {
    $stmt = $pdo->query("SELECT * FROM Admin ORDER BY CreatedDate DESC");
    $sampleAdmins = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Admins fetch error: " . $e->getMessage());
    $sampleAdmins = [];
}

// Calculate statistics
try {
    // Total admins
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Admin");
    $total_admins = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Active admins
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Admin WHERE Status = 'Active'");
    $active_admins = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Inactive admins
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Admin WHERE Status = 'Inactive'");
    $inactive_admins = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Suspended admins
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Admin WHERE Status = 'Suspended'");
    $suspended_admins = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Recently active (logged in today)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Admin WHERE DATE(LastLogin) = CURDATE()");
    $recent_active = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
} catch (PDOException $e) {
    error_log("Admin stats error: " . $e->getMessage());
    $total_admins = $active_admins = $inactive_admins = $suspended_admins = $recent_active = 0;
}

// Fetch recent activities from ActivityLog
try {
    $stmt = $pdo->prepare("
        SELECT a.*, ad.Name as AdminName 
        FROM ActivityLog a
        LEFT JOIN Admin ad ON a.UserID = ad.AdminID
        WHERE a.UserType = 'Admin'
        ORDER BY a.Timestamp DESC
        LIMIT 10
    ");
    $stmt->execute();
    $recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Activity log fetch error: " . $e->getMessage());
    $recentActivities = [];
}

// Role definitions with permissions (static configuration)
$rolePermissions = [
    'Super Admin' => [
        'permissions' => ['All System Access', 'User Management', 'System Configuration', 'Reports & Analytics'],
        'description' => 'Complete system access with all administrative privileges',
        'level' => 1,
        'color' => '#dc3545'
    ],
    'Library Manager' => [
        'permissions' => ['Library Operations', 'Staff Management', 'Collection Management', 'Reports'],
        'description' => 'Manages library operations and staff coordination',
        'level' => 2,
        'color' => '#263c79'
    ],
    'Librarian' => [
        'permissions' => ['Circulation', 'Member Management', 'Book Management', 'Events'],
        'description' => 'Handles day-to-day library operations and member services',
        'level' => 3,
        'color' => '#28a745'
    ],
    'Assistant Librarian' => [
        'permissions' => ['Circulation', 'Basic Reports', 'Member Support'],
        'description' => 'Assists with circulation and basic member services',
        'level' => 4,
        'color' => '#17a2b8'
    ],
    'Data Entry Operator' => [
        'permissions' => ['Data Entry', 'Book Cataloging'],
        'description' => 'Responsible for data entry and book cataloging tasks',
        'level' => 5,
        'color' => '#ffc107'
    ],
    'Admin' => [
        'permissions' => ['Circulation', 'Member Management', 'Book Management', 'Basic Reports'],
        'description' => 'General administrative access to library operations',
        'level' => 3,
        'color' => '#17a2b8'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Users Management</title>
    <style>
        .admin-users-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #cfac69;
        }

        .admin-users-title {
            color: #263c79;
            font-size: 28px;
            font-weight: 700;
            margin: 0;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
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

        .btn-info {
            background-color: #17a2b8;
            color: white;
        }

        .btn-info:hover {
            background-color: #138496;
        }

        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .btn-warning:hover {
            background-color: #e0a800;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
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

        .stat-card.online {
            border-left-color: #28a745;
        }

        .stat-card.roles {
            border-left-color: #17a2b8;
        }

        .stat-card.suspended {
            border-left-color: #dc3545;
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
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }

        .search-row {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            min-width: 200px;
        }

        .form-group label {
            font-weight: 600;
            color: #263c79;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .form-control {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-control:focus {
            outline: none;
            border-color: #cfac69;
            box-shadow: 0 0 0 2px rgba(207, 172, 105, 0.2);
        }

        .admin-users-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .admin-users-table th {
            background-color: #263c79;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .admin-users-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
        }

        .admin-users-table tr:hover {
            background-color: rgba(207, 172, 105, 0.1);
        }

        .action-links {
            display: flex;
            gap: 8px;
        }

        .action-links a,
        .action-links button {
            padding: 4px 8px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            border: none;
            cursor: pointer;
        }

        .btn-view {
            background-color: #17a2b8;
            color: white;
        }

        .btn-edit {
            background-color: #ffc107;
            color: #212529;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
        }

        .btn-suspend {
            background-color: #6c757d;
            color: white;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
        }

        .status-suspended {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-inactive {
            background-color: #e2e3e5;
            color: #495057;
        }

        .role-badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .online-indicator {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
        }

        .online-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }


        .offline {
            background-color: #6c757d;
        }

        .role-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .role-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .role-name {
            font-size: 18px;
            font-weight: 600;
            color: #263c79;
            margin: 0;
        }

        .role-level {
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            color: white;
        }

        .permissions-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .permission-tag {
            background: rgba(38, 60, 121, 0.1);
            color: #263c79;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .activity-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .activity-table th {
            background-color: #f8f9fa;
            color: #263c79;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            border-bottom: 2px solid #e9ecef;
        }

        .activity-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
        }

        .activity-action {
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
        }

        .action-login {
            background-color: #d4edda;
            color: #155724;
        }

        .action-create {
            background-color: #cce7ff;
            color: #004085;
        }

        .action-update {
            background-color: #fff3cd;
            color: #856404;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 8px;
            width: 95%;
            max-width: 900px;
            max-height: 85vh;
            overflow-y: auto;
            margin-top: 160px;
            position: relative;
            z-index: 1001;
        }

        .modal-header {
            background-color: #263c79;
            color: white;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }

        .close {
            color: white;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            background: none;
            border: none;
        }

        .close:hover {
            opacity: 0.7;
        }

        .modal-body {
            padding: 25px;
        }

        .form-section {
            margin-bottom: 25px;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            background: #f8f9fa;
        }

        .section-title {
            color: #263c79;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #cfac69;
        }

        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .form-group-modal {
            flex: 1;
            min-width: 250px;
        }

        .form-group-modal label {
            display: block;
            font-weight: 600;
            color: #263c79;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .form-group-modal input,
        .form-group-modal select,
        .form-group-modal textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .required {
            color: #dc3545;
        }

        .modal-footer {
            padding: 15px 25px;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .security-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #ffc107;
        }

        .security-notice h4 {
            color: #856404;
            margin-bottom: 10px;
        }

        .security-notice p {
            color: #856404;
            margin: 5px 0;
            font-size: 14px;
        }

        /* Add Admin Section Styles */
        .add-admin-section {
            margin-bottom: 25px;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            background: #f8f9fa;
        }

        .add-admin-section .section-title {
            color: #263c79;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #cfac69;
        }

        .admin-form {
            padding: 25px;
        }

        .form-actions {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: flex-start;
        }

        .form-actions .btn {
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .search-row {
                flex-direction: column;
            }

            .form-group {
                min-width: 100%;
            }

            .admin-users-table {
                font-size: 12px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .tab-buttons {
                overflow-x: auto;
                white-space: nowrap;
            }

            .form-row {
                flex-direction: column;
            }

            .form-group-modal {
                min-width: 100%;
            }

            .modal-content {
                margin-top: 150px;
                width: 98%;
                max-height: 80vh;
            }
        }

        @media (max-width: 480px) {
            .modal-content {
                margin-top: 135px;
                width: 99%;
                max-height: 75vh;
            }
        }
    </style>
</head>

<body>
    <div class="admin-users-header">
        <h1 class="admin-users-title">
            <i class="fas fa-user-shield"></i>
            Admin Users Management
        </h1>
        <div class="action-buttons">
            <button class="btn btn-info" onclick="exportAdminData()">
                <i class="fas fa-download"></i>
                Export Data
            </button>
            <button class="btn btn-warning" onclick="auditSecurity()">
                <i class="fas fa-shield-alt"></i>
                Security Audit
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number" id="totalAdmins"><?php echo number_format($total_admins); ?></div>
            <div class="stat-label">Total Admins</div>
        </div>
        <div class="stat-card online">
            <div class="stat-number" id="onlineAdmins"><?php echo number_format($active_admins); ?></div>
            <div class="stat-label">Active Admins</div>
        </div>
        <div class="stat-card roles">
            <div class="stat-number" id="totalRoles"><?php echo number_format($recent_active); ?></div>
            <div class="stat-label">Logged In Today</div>
        </div>
        <div class="stat-card suspended">
            <div class="stat-number" id="suspendedAdmins"><?php echo number_format($suspended_admins); ?></div>
            <div class="stat-label">Suspended Accounts</div>
        </div>
    </div>

    <!-- Add New Admin Form -->
    <div class="add-admin-section">
        <h3 class="section-title">
            <i class="fas fa-user-plus"></i>
            Add New Admin User
        </h3>
        <form id="addAdminForm" class="admin-form">
            <!-- Basic Information Section -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-user"></i>
                    Basic Information
                </div>
                <div class="form-row">
                    <div class="form-group-modal">
                        <label for="adminName">Full Name <span class="required">*</span></label>
                        <input type="text" id="adminName" name="Name" required>
                    </div>
                    <div class="form-group-modal">
                        <label for="adminEmail">Email Address <span class="required">*</span></label>
                        <input type="email" id="adminEmail" name="Email" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group-modal">
                        <label for="adminPhone">Phone Number <span class="required">*</span></label>
                        <input type="tel" id="adminPhone" name="Phone" required>
                    </div>
                    <div class="form-group-modal">
                        <label for="adminRole">Role <span class="required">*</span></label>
                        <select id="adminRole" name="Role" required onchange="updateRoleInfo()">
                            <option value="">Select Role</option>
                            <option value="Super Admin">Super Admin</option>
                            <option value="Library Manager">Library Manager</option>
                            <option value="Librarian">Librarian</option>
                            <option value="Assistant Librarian">Assistant Librarian</option>
                            <option value="Data Entry Operator">Data Entry Operator</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Security Settings Section -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-lock"></i>
                    Security Settings
                </div>
                <div class="form-row">
                    <div class="form-group-modal">
                        <label for="adminPassword">Password <span class="required">*</span></label>
                        <input type="password" id="adminPassword" name="Password" required minlength="8">
                        <small style="color: #6c757d;">Minimum 8 characters</small>
                    </div>
                    <div class="form-group-modal">
                        <label for="confirmPassword">Confirm Password <span class="required">*</span></label>
                        <input type="password" id="confirmPassword" required minlength="8">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group-modal">
                        <label>
                            <input type="checkbox" id="forcePasswordChange" name="ForcePasswordChange" value="1">
                            Force password change on first login
                        </label>
                    </div>
                </div>
            </div>

            <!-- Role Information Display -->
            <div id="roleInfo" class="security-notice" style="display: none;">
                <h4><i class="fas fa-info-circle"></i> Role Information</h4>
                <p id="roleDescription">-</p>
                <div id="rolePermissions" class="permissions-list"></div>
            </div>

            <!-- Submit Button -->
            <div class="form-actions" style="justify-content:flex-start;">
                <button type="button" class="btn btn-success" onclick="saveAdmin()">
                    <i class="fas fa-paper-plane"></i>
                    Create Admin User
                </button>
            </div>
        </form>
    </div>

    <!-- Tabs -->
    <div class="tabs-container">
        <div class="tab-buttons">
            <button class="tab-btn active" onclick="showTab('admin-list')">
                <i class="fas fa-users"></i>
                Admin Users
            </button>
            <button class="tab-btn" onclick="showTab('roles-permissions')">
                <i class="fas fa-key"></i>
                Roles & Permissions
            </button>
            <button class="tab-btn" onclick="showTab('activity-log')">
                <i class="fas fa-history"></i>
                Activity Log
            </button>
            <button class="tab-btn" onclick="showTab('security-settings')">
                <i class="fas fa-shield-alt"></i>
                Security Settings
            </button>
        </div>

        <!-- Admin Users List Tab -->
        <div id="admin-list" class="tab-content active">
            <div class="search-filters">
                <div class="search-row">
                    <div class="form-group">
                        <label for="searchName">Admin Name</label>
                        <input type="text" id="searchName" class="form-control" placeholder="Search by name...">
                    </div>
                    <div class="form-group">
                        <label for="searchEmail">Email</label>
                        <input type="text" id="searchEmail" class="form-control" placeholder="Search by email...">
                    </div>
                    <div class="form-group">
                        <label for="searchRole">Role</label>
                        <select id="searchRole" class="form-control">
                            <option value="">All Roles</option>
                            <option value="Super Admin">Super Admin</option>
                            <option value="Library Manager">Library Manager</option>
                            <option value="Librarian">Librarian</option>
                            <option value="Assistant Librarian">Assistant Librarian</option>
                            <option value="Data Entry Operator">Data Entry Operator</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="searchStatus">Status</label>
                        <select id="searchStatus" class="form-control">
                            <option value="">All Status</option>
                            <option value="Active">Active</option>
                            <option value="Suspended">Suspended</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-primary" onclick="searchAdmins()">
                            <i class="fas fa-search"></i>
                            Search
                        </button>
                    </div>
                </div>
            </div>

            <div id="adminUsersTableContainer">
                <!-- Admin users table will be loaded here -->
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 24px;"></i>
                    <p style="margin-top: 10px;">Loading admin users...</p>
                </div>
            </div>
        </div>

        <!-- Roles & Permissions Tab -->
        <div id="roles-permissions" class="tab-content">
            <div id="rolesContent">
                <!-- Roles content will be loaded here -->
            </div>
        </div>

        <!-- Activity Log Tab -->
        <div id="activity-log" class="tab-content">
            <div id="activityContent">
                <!-- Activity log will be loaded here -->
            </div>
        </div>

        <!-- Security Settings Tab -->
        <div id="security-settings" class="tab-content">
            <div id="securityContent">
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <i class="fas fa-shield-alt" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <h3>Security Settings</h3>
                    <p>Configure security policies, password requirements, and access controls.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Admin Modal -->
    <div id="addAdminModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add New Admin User</h3>
                <button class="close" onclick="closeModal('addAdminModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addAdminForm">
                    <!-- Basic Information Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-user"></i>
                            Basic Information
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="adminName">Full Name <span class="required">*</span></label>
                                <input type="text" id="adminName" name="Name" required>
                            </div>
                            <div class="form-group-modal">
                                <label for="adminEmail">Email Address <span class="required">*</span></label>
                                <input type="email" id="adminEmail" name="Email" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="adminPhone">Phone Number <span class="required">*</span></label>
                                <input type="tel" id="adminPhone" name="Phone" required>
                            </div>
                            <div class="form-group-modal">
                                <label for="adminRole">Role <span class="required">*</span></label>
                                <select id="adminRole" name="Role" required onchange="updateRoleInfo()">
                                    <option value="">Select Role</option>
                                    <option value="Super Admin">Super Admin</option>
                                    <option value="Library Manager">Library Manager</option>
                                    <option value="Librarian">Librarian</option>
                                    <option value="Assistant Librarian">Assistant Librarian</option>
                                    <option value="Data Entry Operator">Data Entry Operator</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Security Settings Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-lock"></i>
                            Security Settings
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="adminPassword">Password <span class="required">*</span></label>
                                <input type="password" id="adminPassword" name="Password" required minlength="8">
                                <small style="color: #6c757d;">Minimum 8 characters</small>
                            </div>
                            <div class="form-group-modal">
                                <label for="confirmPassword">Confirm Password <span class="required">*</span></label>
                                <input type="password" id="confirmPassword" required minlength="8">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label>
                                    <input type="checkbox" id="forcePasswordChange" name="ForcePasswordChange" value="1">
                                    Force password change on first login
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Role Information Display -->
                    <div id="roleInfo" class="security-notice" style="display: none;">
                        <h4><i class="fas fa-info-circle"></i> Role Information</h4>
                        <p id="roleDescription">-</p>
                        <div id="rolePermissions" class="permissions-list"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addAdminModal')">Cancel</button>
                <button type="button" class="btn btn-success" onclick="saveAdmin()">
                    <i class="fas fa-save"></i>
                    Create Admin User
                </button>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        const sampleAdmins = <?php echo json_encode($sampleAdmins); ?>;
        const rolePermissions = <?php echo json_encode($rolePermissions); ?>;
        const recentActivities = <?php echo json_encode($recentActivities); ?>;

        // Tab functionality
        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');

            loadTabContent(tabName);
        }

        function loadTabContent(tabName) {
            switch (tabName) {
                case 'admin-list':
                    loadAdminUsersTable();
                    break;
                case 'roles-permissions':
                    loadRolesContent();
                    break;
                case 'activity-log':
                    loadActivityLog();
                    break;
                case 'security-settings':
                    loadSecuritySettings();
                    break;
            }
        }

        function loadAdminUsersTable(searchParams = {}) {
            let filteredAdmins = sampleAdmins;

            // Apply search filters
            if (searchParams.name) {
                filteredAdmins = filteredAdmins.filter(admin =>
                    admin.Name.toLowerCase().includes(searchParams.name.toLowerCase())
                );
            }
            if (searchParams.email) {
                filteredAdmins = filteredAdmins.filter(admin =>
                    admin.Email.toLowerCase().includes(searchParams.email.toLowerCase())
                );
            }
            if (searchParams.role) {
                filteredAdmins = filteredAdmins.filter(admin =>
                    admin.Role === searchParams.role
                );
            }
            if (searchParams.status) {
                filteredAdmins = filteredAdmins.filter(admin =>
                    admin.Status === searchParams.status
                );
            }

            let tableHTML = `
                <table class="admin-users-table">
                    <thead>
                        <tr>
                            <th>Admin Details</th>
                            <th>Contact</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Online Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            if (filteredAdmins.length === 0) {
                tableHTML += `
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: #6c757d;">
                            <i class="fas fa-search" style="font-size: 24px; margin-bottom: 10px;"></i>
                            <p>No admin users found matching your search criteria.</p>
                        </td>
                    </tr>
                `;
            } else {
                filteredAdmins.forEach(admin => {
                    const statusClass = {
                        'Active': 'status-active',
                        'Suspended': 'status-suspended',
                        'Inactive': 'status-inactive'
                    } [admin.Status] || 'status-active';

                    const roleColor = rolePermissions[admin.Role]?.color || '#6c757d';

                    tableHTML += `
                        <tr>
                            <td>
                                <strong>${admin.Name}</strong><br>
                                <small style="color: #6c757d;">ID: ${admin.AdminID}</small><br>
                                <small style="color: #6c757d;">Created: ${new Date(admin.CreatedDate).toLocaleDateString('en-IN')}</small>
                            </td>
                            <td>
                                <div><i class="fas fa-envelope"></i> ${admin.Email}</div>
                                <div><i class="fas fa-phone"></i> ${admin.Phone}</div>
                            </td>
                            <td>
                                <span class="role-badge" style="background-color: ${roleColor}; color: white;">
                                    ${admin.Role}
                                </span>
                            </td>
                            <td><span class="status-badge ${statusClass}">${admin.Status}</span></td>
                            <td>${new Date(admin.LastLogin).toLocaleDateString('en-IN')}<br>
                                <small>${new Date(admin.LastLogin).toLocaleTimeString('en-IN')}</small>
                            </td>
                            <td>
                                <div class="online-indicator">
                                    <div class="online-dot ${admin.IsOnline ? 'online' : 'offline'}"></div>
                                    ${admin.IsOnline ? 'Online' : 'Offline'}
                                </div>
                            </td>
                            <td class="action-links">
                                <a href="#" class="btn-view" onclick="viewAdmin(${admin.AdminID})">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="#" class="btn-edit" onclick="editAdmin(${admin.AdminID})">
                                    <i class="fas fa-edit"></i>
                                </a>
                                ${admin.Status === 'Active' ? 
                                    `<button class="btn-suspend" onclick="suspendAdmin(${admin.AdminID})">
                                        <i class="fas fa-pause"></i>
                                    </button>` :
                                    `<button class="btn-success" onclick="activateAdmin(${admin.AdminID})" style="background-color: #28a745;">
                                        <i class="fas fa-play"></i>
                                    </button>`
                                }
                                <button class="btn-delete" onclick="deleteAdmin(${admin.AdminID})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
            }

            tableHTML += `
                    </tbody>
                </table>
            `;

            document.getElementById('adminUsersTableContainer').innerHTML = tableHTML;
        }

        function loadRolesContent() {
            let rolesHTML = `
                <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="color: #263c79; margin: 0;">Roles & Permissions Management</h3>
                    <button class="btn btn-primary" onclick="addNewRole()">
                        <i class="fas fa-plus"></i>
                        Add New Role
                    </button>
                </div>
            `;

            Object.entries(rolePermissions).forEach(([roleName, roleData]) => {
                rolesHTML += `
                    <div class="role-card">
                        <div class="role-header">
                            <h3 class="role-name">${roleName}</h3>
                            <span class="role-level" style="background-color: ${roleData.color};">
                                Level ${roleData.level}
                            </span>
                        </div>
                        <p style="color: #6c757d; margin-bottom: 10px;">${roleData.description}</p>
                        <div class="permissions-list">
                            ${roleData.permissions.map(permission => 
                                `<span class="permission-tag">${permission}</span>`
                            ).join('')}
                        </div>
                        <div style="margin-top: 15px;">
                            <button class="btn btn-secondary" onclick="editRole('${roleName}')" style="margin-right: 10px;">
                                <i class="fas fa-edit"></i>
                                Edit Permissions
                            </button>
                            <button class="btn btn-info" onclick="viewRoleUsers('${roleName}')">
                                <i class="fas fa-users"></i>
                                View Users (${sampleAdmins.filter(a => a.Role === roleName).length})
                            </button>
                        </div>
                    </div>
                `;
            });

            document.getElementById('rolesContent').innerHTML = rolesHTML;
        }

        function loadActivityLog() {
            let activityHTML = `
                <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="color: #263c79; margin: 0;">Recent Admin Activities</h3>
                    <button class="btn btn-info" onclick="exportActivityLog()">
                        <i class="fas fa-download"></i>
                        Export Log
                    </button>
                </div>

                <table class="activity-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Admin User</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            if (recentActivities.length === 0) {
                activityHTML += `
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: #6c757d;">
                            <i class="fas fa-clipboard-list" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                            <p>No recent activities found</p>
                        </td>
                    </tr>
                `;
            } else {
                recentActivities.forEach(activity => {
                    const actionClass = {
                        'User Login': 'action-login',
                        'User Created': 'action-create',
                        'Password Changed': 'action-update',
                        'BOOK_ADD': 'action-create',
                        'BOOK_UPDATE': 'action-update',
                        'BOOK_DELETE': 'action-delete'
                    } [activity.Action] || 'action-update';

                    const timestamp = activity.Timestamp || '';
                    const adminName = activity.AdminName || 'Unknown';
                    const action = activity.Action || '-';
                    const details = activity.Details || '-';
                    const ipAddress = activity.IPAddress || '-';

                    activityHTML += `
                        <tr>
                            <td>${timestamp ? new Date(timestamp).toLocaleString('en-IN') : '-'}</td>
                            <td>${adminName}</td>
                            <td><span class="activity-action ${actionClass}">${action}</span></td>
                            <td>${details}</td>
                            <td>${ipAddress}</td>
                        </tr>
                    `;
                });
            }

            activityHTML += `
                    </tbody>
                </table>
            `;

            document.getElementById('activityContent').innerHTML = activityHTML;
        }

        function loadSecuritySettings() {
            const securityHTML = `
                <div style="margin-bottom: 20px;">
                    <h3 style="color: #263c79; margin-bottom: 15px;">Security Configuration</h3>
                </div>

                <div class="security-notice">
                    <h4><i class="fas fa-shield-alt"></i> Current Security Policy</h4>
                    <p><strong>Password Requirements:</strong> Minimum 8 characters, must include uppercase, lowercase, number, and special character</p>
                    <p><strong>Session Timeout:</strong> 30 minutes of inactivity</p>
                    <p><strong>Failed Login Attempts:</strong> Account locked after 5 failed attempts</p>
                    <p><strong>Two-Factor Authentication:</strong> Enabled for Super Admin and Library Manager roles</p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
                    <div class="role-card">
                        <h4 style="color: #263c79;">Password Policy</h4>
                        <p>Configure password strength requirements and expiration policies.</p>
                        <button class="btn btn-primary" onclick="configurePasswordPolicy()">
                            <i class="fas fa-key"></i>
                            Configure
                        </button>
                    </div>

                    <div class="role-card">
                        <h4 style="color: #263c79;">Access Control</h4>
                        <p>Manage IP restrictions and access time limitations.</p>
                        <button class="btn btn-primary" onclick="configureAccessControl()">
                            <i class="fas fa-lock"></i>
                            Configure
                        </button>
                    </div>

                    <div class="role-card">
                        <h4 style="color: #263c79;">Audit Trail</h4>
                        <p>Configure logging levels and retention policies.</p>
                        <button class="btn btn-primary" onclick="configureAuditTrail()">
                            <i class="fas fa-clipboard-list"></i>
                            Configure
                        </button>
                    </div>

                    <div class="role-card">
                        <h4 style="color: #263c79;">Backup & Recovery</h4>
                        <p>Manage system backups and disaster recovery procedures.</p>
                        <button class="btn btn-primary" onclick="configureBackup()">
                            <i class="fas fa-database"></i>
                            Configure
                        </button>
                    </div>
                </div>
            `;

            document.getElementById('securityContent').innerHTML = securityHTML;
        }

        function searchAdmins() {
            const searchParams = {
                name: document.getElementById('searchName').value.trim(),
                email: document.getElementById('searchEmail').value.trim(),
                role: document.getElementById('searchRole').value,
                status: document.getElementById('searchStatus').value
            };

            loadAdminUsersTable(searchParams);
        }

        // Modal functions
        function openAddAdminModal() {
            document.getElementById('addAdminModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';

            if (modalId === 'addAdminModal') {
                document.getElementById('addAdminForm').reset();
                document.getElementById('roleInfo').style.display = 'none';
            }
        }

        function updateRoleInfo() {
            const role = document.getElementById('adminRole').value;
            const roleInfo = document.getElementById('roleInfo');
            const roleDescription = document.getElementById('roleDescription');
            const rolePermissionsDiv = document.getElementById('rolePermissions');

            if (role && rolePermissions[role]) {
                roleDescription.textContent = rolePermissions[role].description;
                rolePermissionsDiv.innerHTML = rolePermissions[role].permissions
                    .map(permission => `<span class="permission-tag">${permission}</span>`)
                    .join('');
                roleInfo.style.display = 'block';
            } else {
                roleInfo.style.display = 'none';
            }
        }

        function saveAdmin() {
            const formData = new FormData(document.getElementById('addAdminForm'));
            const adminData = Object.fromEntries(formData);

            // Password confirmation check
            const password = document.getElementById('adminPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                return;
            }

            // Generate new admin ID
            const newAdminID = Math.max(...sampleAdmins.map(a => a.AdminID)) + 1;

            // Create new admin object
            const newAdmin = {
                AdminID: newAdminID,
                Name: adminData.Name,
                Email: adminData.Email,
                Phone: adminData.Phone,
                Role: adminData.Role,
                Password: 'hashed_password_' + newAdminID, // In real app, this would be properly hashed
                Status: 'Active',
                LastLogin: new Date().toISOString().slice(0, 19).replace('T', ' '),
                CreatedDate: new Date().toISOString().slice(0, 10),
                CreatedBy: 1, // Current admin ID
                IsOnline: false
            };

            // Add new admin to the sampleAdmins array
            sampleAdmins.push(newAdmin);

            console.log('Creating new admin:', newAdmin);

            alert(`Admin user created successfully!\nAdmin ID: ${newAdminID}\nLogin credentials will be sent to the provided email.`);
            
            // Clear the form
            document.getElementById('addAdminForm').reset();
            
            // Refresh the admin table and stats
            loadAdminUsersTable();
            updateStats();
        }

        // Admin actions
        function viewAdmin(adminId) {
            console.log('Viewing admin:', adminId);
            alert(`Opening detailed view for Admin ID: ${adminId}`);
        }

        function editAdmin(adminId) {
            console.log('Editing admin:', adminId);
            alert(`Opening edit form for Admin ID: ${adminId}`);
        }

        function suspendAdmin(adminId) {
            if (confirm(`Are you sure you want to suspend Admin ID: ${adminId}?`)) {
                console.log('Suspending admin:', adminId);
                alert('Admin user suspended successfully!');
                loadAdminUsersTable();
            }
        }

        function activateAdmin(adminId) {
            if (confirm(`Are you sure you want to activate Admin ID: ${adminId}?`)) {
                console.log('Activating admin:', adminId);
                alert('Admin user activated successfully!');
                loadAdminUsersTable();
            }
        }

        function deleteAdmin(adminId) {
            if (confirm(`Are you sure you want to DELETE Admin ID: ${adminId}? This action cannot be undone.`)) {
                console.log('Deleting admin:', adminId);
                alert('Admin user deleted successfully!');
                loadAdminUsersTable();
            }
        }

        // Other functions
        function exportAdminData() {
            console.log('Exporting admin data...');
            alert('Exporting admin user data to CSV...');
        }

        function auditSecurity() {
            console.log('Running security audit...');
            alert('Running comprehensive security audit...');
        }

        function addNewRole() {
            console.log('Adding new role...');
            alert('Opening new role creation interface...');
        }

        function editRole(roleName) {
            console.log('Editing role:', roleName);
            alert(`Opening permissions editor for role: ${roleName}`);
        }

        function viewRoleUsers(roleName) {
            console.log('Viewing users for role:', roleName);
            alert(`Showing all users with role: ${roleName}`);
        }

        function exportActivityLog() {
            console.log('Exporting activity log...');
            alert('Exporting activity log to CSV...');
        }

        function configurePasswordPolicy() {
            console.log('Configuring password policy...');
            alert('Opening password policy configuration...');
        }

        function configureAccessControl() {
            console.log('Configuring access control...');
            alert('Opening access control configuration...');
        }

        function configureAuditTrail() {
            console.log('Configuring audit trail...');
            alert('Opening audit trail configuration...');
        }

        function configureBackup() {
            console.log('Configuring backup...');
            alert('Opening backup configuration...');
        }

        // Statistics are now loaded from PHP directly in HTML
        // No need for loadStatistics() function

        // Close modals when clicking outside
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        };

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadAdminUsersTable();
        });
    </script>
</body>

</html>