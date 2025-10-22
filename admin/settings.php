<?php
session_start();

// No database connection needed for frontend development
// Sample data will be used to demonstrate functionality

$admin_id = $_SESSION['admin_id'] ?? 1;
$admin_name = $_SESSION['admin_name'] ?? 'Admin User';

// Sample system settings data
$system_settings = [
    'library_name' => 'WIET College Library',
    'library_code' => 'WIET-LIB',
    'library_address' => 'Watumull Institute of Electronic Engineering & Computer Technology, Mumbai',
    'library_phone' => '+91-22-12345678',
    'library_email' => 'library@wiet.edu',
    'working_hours_start' => '08:00',
    'working_hours_end' => '20:00',
    'max_issue_days' => 15,
    'max_books_per_member' => 3,
    'fine_per_day' => 2.00,
    'renewal_allowed' => true,
    'max_renewals' => 2,
    'reservation_period' => 7,
    'grace_period' => 3,
    'academic_year_start' => '2024-06-01',
    'academic_year_end' => '2025-05-31',
    'backup_frequency' => 'daily',
    'notification_enabled' => true,
    'sms_gateway' => 'enabled',
    'email_notifications' => true,
    'auto_backup' => true,
    'session_timeout' => 30,
    'password_expiry_days' => 90,
    'max_login_attempts' => 5
];

// Member group settings
$member_groups = [
    'Student' => [
        'max_books' => 3,
        'issue_period' => 15,
        'fine_per_day' => 2.00,
        'renewal_allowed' => true,
        'max_renewals' => 2,
        'reservation_allowed' => true
    ],
    'Faculty' => [
        'max_books' => 10,
        'issue_period' => 30,
        'fine_per_day' => 1.00,
        'renewal_allowed' => true,
        'max_renewals' => 5,
        'reservation_allowed' => true
    ],
    'Staff' => [
        'max_books' => 5,
        'issue_period' => 20,
        'fine_per_day' => 2.00,
        'renewal_allowed' => true,
        'max_renewals' => 3,
        'reservation_allowed' => true
    ],
    'Guest' => [
        'max_books' => 2,
        'issue_period' => 7,
        'fine_per_day' => 5.00,
        'renewal_allowed' => false,
        'max_renewals' => 0,
        'reservation_allowed' => false
    ]
];

// Circulation policies
$circulation_policies = [
    'issue_policy' => 'Books can only be issued to active members with valid ID cards',
    'return_policy' => 'Books must be returned in good condition within due date',
    'fine_policy' => 'Late return fine applies from the day after due date',
    'lost_book_policy' => 'Lost books must be replaced or cost paid with 20% processing fee',
    'damage_policy' => 'Damaged books may incur repair or replacement costs',
    'suspension_policy' => 'Members with overdue books >30 days will be suspended'
];

// System backup history
$backup_history = [
    [
        'backup_id' => 1,
        'backup_date' => '2024-12-19 02:00:00',
        'backup_type' => 'Full System Backup',
        'file_size' => '2.4 GB',
        'status' => 'Completed',
        'location' => '/backups/full_backup_20241219.sql.gz'
    ],
    [
        'backup_id' => 2,
        'backup_date' => '2024-12-18 02:00:00',
        'backup_type' => 'Incremental Backup',
        'file_size' => '124 MB',
        'status' => 'Completed',
        'location' => '/backups/incremental_20241218.sql.gz'
    ],
    [
        'backup_id' => 3,
        'backup_date' => '2024-12-17 02:00:00',
        'backup_type' => 'Database Backup',
        'file_size' => '890 MB',
        'status' => 'Completed',
        'location' => '/backups/db_backup_20241217.sql.gz'
    ]
];

// Notification templates
$notification_templates = [
    'due_reminder' => [
        'subject' => 'Library Book Due Reminder',
        'message' => 'Dear {member_name}, your book "{book_title}" is due on {due_date}. Please return it to avoid late fees.',
        'active' => true
    ],
    'overdue_notice' => [
        'subject' => 'Overdue Book Notice',
        'message' => 'Dear {member_name}, your book "{book_title}" is overdue. Fine: ₹{fine_amount}. Please return immediately.',
        'active' => true
    ],
    'new_arrival' => [
        'subject' => 'New Book Arrival',
        'message' => 'New books have arrived in the library! Visit to explore our latest collection.',
        'active' => true
    ],
    'reservation_ready' => [
        'subject' => 'Reserved Book Available',
        'message' => 'Dear {member_name}, your reserved book "{book_title}" is now available for pickup.',
        'active' => true
    ]
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .settings-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #cfac69;
        }

        .settings-title {
            color: #263c79;
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 15px 0;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: flex-start;
        }

        /* Desktop view */
        @media (min-width: 768px) {
            .settings-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .settings-title {
                margin: 0;
            }
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
            flex-shrink: 0;
            white-space: nowrap;
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

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .settings-section {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            color: #263c79;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #cfac69;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #263c79;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 8px 12px;
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

        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox-group input[type="checkbox"] {
            margin: 0;
        }

        .policy-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .policy-title {
            font-weight: 600;
            color: #263c79;
            margin-bottom: 8px;
        }

        .policy-description {
            color: #6c757d;
            font-size: 14px;
            line-height: 1.4;
        }

        .group-settings-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .group-settings-table th,
        .group-settings-table td {
            padding: 10px;
            border: 1px solid #e9ecef;
            text-align: left;
            font-size: 14px;
        }

        .group-settings-table th {
            background-color: #f8f9fa;
            color: #263c79;
            font-weight: 600;
        }

        .backup-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .backup-table th {
            background-color: #263c79;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .backup-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
        }

        .backup-table tr:hover {
            background-color: rgba(207, 172, 105, 0.1);
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-running {
            background-color: #fff3cd;
            color: #856404;
        }

        .template-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .template-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .template-title {
            font-weight: 600;
            color: #263c79;
            margin: 0;
        }

        .template-toggle {
            position: relative;
            display: inline-block;
            width: 40px;
            height: 20px;
        }

        .template-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 20px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.toggle-slider {
            background-color: #28a745;
        }

        input:checked+.toggle-slider:before {
            transform: translateX(20px);
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 6px;
        }

        .alert-warning {
            color: #856404;
            background-color: #fff3cd;
            border-color: #ffeaa7;
        }

        .alert-info {
            color: #0c5460;
            background-color: #d1ecf1;
            border-color: #b8daff;
        }

        .system-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .info-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #cfac69;
        }

        .info-label {
            font-weight: 600;
            color: #495057;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .info-value {
            color: #263c79;
            font-weight: 600;
            font-size: 16px;
        }

        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .form-row .form-group {
                margin-bottom: 15px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .tab-buttons {
                overflow-x: auto;
                white-space: nowrap;
            }

            .group-settings-table {
                font-size: 12px;
            }

            .backup-table {
                font-size: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="settings-header">
        <h1 class="settings-title">
            <i class="fas fa-cog"></i>
            System Settings
        </h1>
        <div class="action-buttons">
            <button class="btn btn-success" onclick="saveAllSettings()">
                <i class="fas fa-save"></i>
                Save Changes
            </button>
            <button class="btn btn-warning" onclick="backupSystem()">
                <i class="fas fa-download"></i>
                Backup Now
            </button>
            <button class="btn btn-info" onclick="exportSettings()">
                <i class="fas fa-file-export"></i>
                Export Settings
            </button>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs-container">
        <div class="tab-buttons">
            <button class="tab-btn active" onclick="showTab('general')">
                <i class="fas fa-cog"></i>
                General Settings
            </button>
            <button class="tab-btn" onclick="showTab('circulation')">
                <i class="fas fa-exchange-alt"></i>
                Circulation Policies
            </button>
            <button class="tab-btn" onclick="showTab('member-groups')">
                <i class="fas fa-users"></i>
                Member Groups
            </button>
            <button class="tab-btn" onclick="showTab('notifications')">
                <i class="fas fa-bell"></i>
                Notifications
            </button>
            <button class="tab-btn" onclick="showTab('backup')">
                <i class="fas fa-database"></i>
                Backup & Recovery
            </button>
            <button class="tab-btn" onclick="showTab('security')">
                <i class="fas fa-shield-alt"></i>
                Security
            </button>
            <button class="tab-btn" onclick="showTab('system-info')">
                <i class="fas fa-info-circle"></i>
                System Info
            </button>
        </div>

        <!-- General Settings Tab -->
        <div id="general" class="tab-content active">
            <div class="settings-grid">
                <div class="settings-section">
                    <h3 class="section-title">
                        <i class="fas fa-building"></i>
                        Library Information
                    </h3>
                    <div class="form-group">
                        <label for="libraryName">Library Name</label>
                        <input type="text" id="libraryName" class="form-control" value="<?php echo htmlspecialchars($system_settings['library_name']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="libraryCode">Library Code</label>
                        <input type="text" id="libraryCode" class="form-control" value="<?php echo htmlspecialchars($system_settings['library_code']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="libraryAddress">Address</label>
                        <textarea id="libraryAddress" class="form-control" rows="3"><?php echo htmlspecialchars($system_settings['library_address']); ?></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="libraryPhone">Phone</label>
                            <input type="tel" id="libraryPhone" class="form-control" value="<?php echo htmlspecialchars($system_settings['library_phone']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="libraryEmail">Email</label>
                            <input type="email" id="libraryEmail" class="form-control" value="<?php echo htmlspecialchars($system_settings['library_email']); ?>">
                        </div>
                    </div>
                </div>

                <div class="settings-section">
                    <h3 class="section-title">
                        <i class="fas fa-clock"></i>
                        Working Hours
                    </h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="workingHoursStart">Opening Time</label>
                            <input type="time" id="workingHoursStart" class="form-control" value="<?php echo $system_settings['working_hours_start']; ?>">
                        </div>
                        <div class="form-group">
                            <label for="workingHoursEnd">Closing Time</label>
                            <input type="time" id="workingHoursEnd" class="form-control" value="<?php echo $system_settings['working_hours_end']; ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="academicYearStart">Academic Year Start</label>
                            <input type="date" id="academicYearStart" class="form-control" value="<?php echo $system_settings['academic_year_start']; ?>">
                        </div>
                        <div class="form-group">
                            <label for="academicYearEnd">Academic Year End</label>
                            <input type="date" id="academicYearEnd" class="form-control" value="<?php echo $system_settings['academic_year_end']; ?>">
                        </div>
                    </div>
                </div>

                <div class="settings-section">
                    <h3 class="section-title">
                        <i class="fas fa-book-open"></i>
                        Default Issue Settings
                    </h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="maxIssueDays">Default Issue Period (Days)</label>
                            <input type="number" id="maxIssueDays" class="form-control" value="<?php echo $system_settings['max_issue_days']; ?>" min="1" max="365">
                        </div>
                        <div class="form-group">
                            <label for="maxBooksPerMember">Max Books per Member</label>
                            <input type="number" id="maxBooksPerMember" class="form-control" value="<?php echo $system_settings['max_books_per_member']; ?>" min="1" max="50">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="finePerDay">Default Fine per Day (₹)</label>
                            <input type="number" id="finePerDay" class="form-control" value="<?php echo $system_settings['fine_per_day']; ?>" min="0" step="0.50">
                        </div>
                        <div class="form-group">
                            <label for="maxRenewals">Max Renewals Allowed</label>
                            <input type="number" id="maxRenewals" class="form-control" value="<?php echo $system_settings['max_renewals']; ?>" min="0" max="10">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="reservationPeriod">Reservation Period (Days)</label>
                            <input type="number" id="reservationPeriod" class="form-control" value="<?php echo $system_settings['reservation_period']; ?>" min="1" max="30">
                        </div>
                        <div class="form-group">
                            <label for="gracePeriod">Grace Period (Days)</label>
                            <input type="number" id="gracePeriod" class="form-control" value="<?php echo $system_settings['grace_period']; ?>" min="0" max="7">
                        </div>
                    </div>
                    <div class="checkbox-group">
                        <input type="checkbox" id="renewalAllowed" <?php echo $system_settings['renewal_allowed'] ? 'checked' : ''; ?>>
                        <label for="renewalAllowed">Allow Book Renewals</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Circulation Policies Tab -->
        <div id="circulation" class="tab-content">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Information:</strong> These policies are displayed to users and staff. They help maintain consistency in library operations.
            </div>

            <div class="settings-grid">
                <?php foreach ($circulation_policies as $key => $policy): ?>
                    <div class="policy-card">
                        <div class="policy-title"><?php echo ucwords(str_replace('_', ' ', $key)); ?></div>
                        <div class="policy-description">
                            <textarea class="form-control" rows="3" id="<?php echo $key; ?>"><?php echo htmlspecialchars($policy); ?></textarea>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Member Groups Tab -->
        <div id="member-groups" class="tab-content">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Warning:</strong> Changes to member group settings will affect all future transactions. Existing issued books will retain their original due dates.
            </div>

            <table class="group-settings-table">
                <thead>
                    <tr>
                        <th>Member Group</th>
                        <th>Max Books</th>
                        <th>Issue Period (Days)</th>
                        <th>Fine per Day (₹)</th>
                        <th>Renewal Allowed</th>
                        <th>Max Renewals</th>
                        <th>Reservation Allowed</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($member_groups as $group => $settings): ?>
                        <tr>
                            <td><strong><?php echo $group; ?></strong></td>
                            <td>
                                <input type="number" class="form-control" value="<?php echo $settings['max_books']; ?>" min="1" max="50" style="width: 80px;">
                            </td>
                            <td>
                                <input type="number" class="form-control" value="<?php echo $settings['issue_period']; ?>" min="1" max="365" style="width: 80px;">
                            </td>
                            <td>
                                <input type="number" class="form-control" value="<?php echo $settings['fine_per_day']; ?>" min="0" step="0.50" style="width: 80px;">
                            </td>
                            <td>
                                <input type="checkbox" <?php echo $settings['renewal_allowed'] ? 'checked' : ''; ?>>
                            </td>
                            <td>
                                <input type="number" class="form-control" value="<?php echo $settings['max_renewals']; ?>" min="0" max="10" style="width: 80px;">
                            </td>
                            <td>
                                <input type="checkbox" <?php echo $settings['reservation_allowed'] ? 'checked' : ''; ?>>
                            </td>
                            <td>
                                <button class="btn btn-primary" onclick="updateGroupSettings('<?php echo $group; ?>')" style="padding: 4px 8px; font-size: 12px;">
                                    <i class="fas fa-save"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="margin-top: 20px;">
                <button class="btn btn-success" onclick="addNewMemberGroup()">
                    <i class="fas fa-plus"></i>
                    Add New Member Group
                </button>
            </div>
        </div>

        <!-- Notifications Tab -->
        <div id="notifications" class="tab-content">
            <div class="settings-grid">
                <div class="settings-section">
                    <h3 class="section-title">
                        <i class="fas fa-toggle-on"></i>
                        Notification Settings
                    </h3>
                    <div class="checkbox-group" style="margin-bottom: 15px;">
                        <input type="checkbox" id="notificationEnabled" <?php echo $system_settings['notification_enabled'] ? 'checked' : ''; ?>>
                        <label for="notificationEnabled">Enable Notifications</label>
                    </div>
                    <div class="checkbox-group" style="margin-bottom: 15px;">
                        <input type="checkbox" id="emailNotifications" <?php echo $system_settings['email_notifications'] ? 'checked' : ''; ?>>
                        <label for="emailNotifications">Email Notifications</label>
                    </div>
                    <div class="checkbox-group" style="margin-bottom: 15px;">
                        <input type="checkbox" id="smsGateway" <?php echo $system_settings['sms_gateway'] === 'enabled' ? 'checked' : ''; ?>>
                        <label for="smsGateway">SMS Notifications</label>
                    </div>
                </div>
            </div>

            <h3 style="color: #263c79; margin: 30px 0 15px 0;">Notification Templates</h3>

            <?php foreach ($notification_templates as $key => $template): ?>
                <div class="template-card">
                    <div class="template-header">
                        <h4 class="template-title"><?php echo ucwords(str_replace('_', ' ', $key)); ?></h4>
                        <label class="template-toggle">
                            <input type="checkbox" <?php echo $template['active'] ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($template['subject']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Message Template</label>
                        <textarea class="form-control" rows="3"><?php echo htmlspecialchars($template['message']); ?></textarea>
                        <small style="color: #6c757d; font-size: 12px;">
                            Available variables: {member_name}, {book_title}, {due_date}, {fine_amount}, {library_name}
                        </small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Backup & Recovery Tab -->
        <div id="backup" class="tab-content">
            <div class="settings-grid">
                <div class="settings-section">
                    <h3 class="section-title">
                        <i class="fas fa-download"></i>
                        Backup Settings
                    </h3>
                    <div class="form-group">
                        <label for="backupFrequency">Backup Frequency</label>
                        <select id="backupFrequency" class="form-control">
                            <option value="daily" <?php echo $system_settings['backup_frequency'] === 'daily' ? 'selected' : ''; ?>>Daily</option>
                            <option value="weekly" <?php echo $system_settings['backup_frequency'] === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                            <option value="monthly" <?php echo $system_settings['backup_frequency'] === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                        </select>
                    </div>
                    <div class="checkbox-group" style="margin-bottom: 15px;">
                        <input type="checkbox" id="autoBackup" <?php echo $system_settings['auto_backup'] ? 'checked' : ''; ?>>
                        <label for="autoBackup">Enable Automatic Backup</label>
                    </div>
                    <div style="margin-top: 20px;">
                        <button class="btn btn-primary" onclick="runFullBackup()">
                            <i class="fas fa-database"></i>
                            Full System Backup
                        </button>
                        <button class="btn btn-info" onclick="runDatabaseBackup()" style="margin-left: 10px;">
                            <i class="fas fa-table"></i>
                            Database Only
                        </button>
                    </div>
                </div>
            </div>

            <h3 style="color: #263c79; margin: 30px 0 15px 0;">Backup History</h3>

            <table class="backup-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Backup Type</th>
                        <th>File Size</th>
                        <th>Status</th>
                        <th>Location</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($backup_history as $backup): ?>
                        <tr>
                            <td><?php echo date('M j, Y g:i A', strtotime($backup['backup_date'])); ?></td>
                            <td><?php echo $backup['backup_type']; ?></td>
                            <td><?php echo $backup['file_size']; ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($backup['status']); ?>">
                                    <?php echo $backup['status']; ?>
                                </span>
                            </td>
                            <td style="font-family: monospace; font-size: 12px;"><?php echo $backup['location']; ?></td>
                            <td>
                                <button class="btn btn-info" onclick="downloadBackup(<?php echo $backup['backup_id']; ?>)" style="padding: 4px 8px; font-size: 12px;">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button class="btn btn-warning" onclick="restoreBackup(<?php echo $backup['backup_id']; ?>)" style="padding: 4px 8px; font-size: 12px; margin-left: 5px;">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Security Tab -->
        <div id="security" class="tab-content">
            <div class="settings-grid">
                <div class="settings-section">
                    <h3 class="section-title">
                        <i class="fas fa-lock"></i>
                        Authentication Settings
                    </h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="sessionTimeout">Session Timeout (minutes)</label>
                            <input type="number" id="sessionTimeout" class="form-control" value="<?php echo $system_settings['session_timeout']; ?>" min="5" max="480">
                        </div>
                        <div class="form-group">
                            <label for="maxLoginAttempts">Max Login Attempts</label>
                            <input type="number" id="maxLoginAttempts" class="form-control" value="<?php echo $system_settings['max_login_attempts']; ?>" min="3" max="10">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="passwordExpiryDays">Password Expiry (days)</label>
                        <input type="number" id="passwordExpiryDays" class="form-control" value="<?php echo $system_settings['password_expiry_days']; ?>" min="30" max="365">
                    </div>
                </div>

                <div class="settings-section">
                    <h3 class="section-title">
                        <i class="fas fa-shield-alt"></i>
                        Security Policies
                    </h3>
                    <div class="checkbox-group" style="margin-bottom: 15px;">
                        <input type="checkbox" id="forcePasswordChange" checked>
                        <label for="forcePasswordChange">Force password change on first login</label>
                    </div>
                    <div class="checkbox-group" style="margin-bottom: 15px;">
                        <input type="checkbox" id="requireStrongPassword" checked>
                        <label for="requireStrongPassword">Require strong passwords</label>
                    </div>
                    <div class="checkbox-group" style="margin-bottom: 15px;">
                        <input type="checkbox" id="enableAccountLockout" checked>
                        <label for="enableAccountLockout">Enable account lockout after failed attempts</label>
                    </div>
                    <div class="checkbox-group" style="margin-bottom: 15px;">
                        <input type="checkbox" id="logSecurityEvents" checked>
                        <label for="logSecurityEvents">Log all security events</label>
                    </div>
                </div>

                <div class="settings-section">
                    <h3 class="section-title">
                        <i class="fas fa-history"></i>
                        System Maintenance
                    </h3>
                    <div style="margin-bottom: 15px;">
                        <button class="btn btn-warning" onclick="clearSystemLogs()">
                            <i class="fas fa-trash"></i>
                            Clear System Logs
                        </button>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <button class="btn btn-info" onclick="optimizeDatabase()">
                            <i class="fas fa-wrench"></i>
                            Optimize Database
                        </button>
                    </div>
                    <div>
                        <button class="btn btn-secondary" onclick="generateSecurityReport()">
                            <i class="fas fa-file-alt"></i>
                            Generate Security Report
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Info Tab -->
        <div id="system-info" class="tab-content">
            <div class="system-info">
                <div class="info-item">
                    <div class="info-label">System Version</div>
                    <div class="info-value">WIET-LIB v2.1.0</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Database Version</div>
                    <div class="info-value">MySQL 8.0.35</div>
                </div>
                <div class="info-item">
                    <div class="info-label">PHP Version</div>
                    <div class="info-value">PHP 8.2.12</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Server OS</div>
                    <div class="info-value">Ubuntu 22.04 LTS</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Total Books</div>
                    <div class="info-value">15,247 titles</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Total Members</div>
                    <div class="info-value">1,456 active</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Database Size</div>
                    <div class="info-value">2.4 GB</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Last Backup</div>
                    <div class="info-value">Dec 19, 2024</div>
                </div>
                <div class="info-item">
                    <div class="info-label">System Uptime</div>
                    <div class="info-value">15 days, 6 hours</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Disk Usage</div>
                    <div class="info-value">67% of 100 GB</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Active Sessions</div>
                    <div class="info-value">23 users</div>
                </div>
                <div class="info-item">
                    <div class="info-label">System Status</div>
                    <div class="info-value" style="color: #28a745;">Healthy</div>
                </div>
            </div>

            <div style="margin-top: 30px;">
                <h3 style="color: #263c79; margin-bottom: 15px;">System Health Check</h3>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>System Status:</strong> All services are running normally. Database connections are stable. No critical issues detected.
                </div>

                <button class="btn btn-primary" onclick="runHealthCheck()">
                    <i class="fas fa-heart"></i>
                    Run Health Check
                </button>
                <button class="btn btn-info" onclick="viewSystemLogs()" style="margin-left: 10px;">
                    <i class="fas fa-file-alt"></i>
                    View System Logs
                </button>
                <button class="btn btn-warning" onclick="restartServices()" style="margin-left: 10px;">
                    <i class="fas fa-redo"></i>
                    Restart Services
                </button>
            </div>
        </div>
    </div>

    <script>
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
        }

        // Settings functions
        function saveAllSettings() {
            console.log('Saving all settings...');
            alert('Settings saved successfully!');
        }

        function backupSystem() {
            if (confirm('This will create a full system backup. This may take several minutes. Continue?')) {
                console.log('Starting system backup...');
                alert('System backup started. You will be notified when complete.');
            }
        }

        function exportSettings() {
            console.log('Exporting settings...');
            alert('Settings exported to configuration file.');
        }

        function updateGroupSettings(group) {
            console.log('Updating settings for group:', group);
            alert(`Settings updated for ${group} group.`);
        }

        function addNewMemberGroup() {
            const groupName = prompt('Enter new member group name:');
            if (groupName) {
                console.log('Adding new member group:', groupName);
                alert(`New member group "${groupName}" added successfully.`);
            }
        }

        function runFullBackup() {
            if (confirm('This will create a complete backup of the system including database and files. Continue?')) {
                console.log('Running full backup...');
                alert('Full backup started. This may take 10-15 minutes.');
            }
        }

        function runDatabaseBackup() {
            if (confirm('This will backup only the database. Continue?')) {
                console.log('Running database backup...');
                alert('Database backup started.');
            }
        }

        function downloadBackup(backupId) {
            console.log('Downloading backup:', backupId);
            alert(`Downloading backup ID: ${backupId}`);
        }

        function restoreBackup(backupId) {
            if (confirm('WARNING: This will restore the system to a previous state. All current data may be lost. Are you sure?')) {
                console.log('Restoring backup:', backupId);
                alert(`Restoring from backup ID: ${backupId}. System will restart.`);
            }
        }

        function clearSystemLogs() {
            if (confirm('This will permanently delete all system logs. Continue?')) {
                console.log('Clearing system logs...');
                alert('System logs cleared successfully.');
            }
        }

        function optimizeDatabase() {
            if (confirm('This will optimize database tables for better performance. Continue?')) {
                console.log('Optimizing database...');
                alert('Database optimization completed.');
            }
        }

        function generateSecurityReport() {
            console.log('Generating security report...');
            alert('Security report generated and saved to reports folder.');
        }

        function runHealthCheck() {
            console.log('Running system health check...');

            // Simulate health check process
            setTimeout(() => {
                alert('System Health Check Results:\n\n✓ Database: OK\n✓ File System: OK\n✓ Network: OK\n✓ Services: All Running\n✓ Memory Usage: Normal\n✓ Disk Space: Sufficient\n\nSystem is healthy!');
            }, 2000);
        }

        function viewSystemLogs() {
            console.log('Opening system logs...');
            alert('Opening system logs viewer...');
        }

        function restartServices() {
            if (confirm('This will restart all system services. Users may experience brief interruption. Continue?')) {
                console.log('Restarting services...');
                alert('Services restarted successfully.');
            }
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Settings page loaded');

            // Add any initialization code here
            // For example, load current settings from server

            // Auto-save draft changes every 30 seconds
            setInterval(() => {
                console.log('Auto-saving draft settings...');
            }, 30000);
        });
    </script>
</body>

</html>