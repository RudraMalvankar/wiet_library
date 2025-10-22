<?php
session_start();

// No database connection needed for frontend development
// Sample data will be used to demonstrate functionality

$admin_id = $_SESSION['admin_id'] ?? 1;
$admin_name = $_SESSION['admin_name'] ?? 'Admin User';

// Sample notification data matching potential database schema
$sampleNotifications = [
    [
        'NotificationID' => 1,
        'Type' => 'Due Reminder',
        'Title' => 'Book Due Tomorrow',
        'Message' => 'Dear {member_name}, your book "{book_title}" is due tomorrow. Please return it to avoid late fees.',
        'Recipients' => 'Specific Members',
        'Status' => 'Sent',
        'SentCount' => 23,
        'DeliveredCount' => 22,
        'FailedCount' => 1,
        'ScheduledDate' => '2024-12-19 09:00:00',
        'SentDate' => '2024-12-19 09:00:00',
        'CreatedBy' => 1,
        'CreatedDate' => '2024-12-18 15:30:00',
        'Channels' => ['Email', 'SMS'],
        'Priority' => 'High'
    ],
    [
        'NotificationID' => 2,
        'Type' => 'Overdue Notice',
        'Title' => 'Overdue Books - Immediate Action Required',
        'Message' => 'Dear {member_name}, you have {overdue_count} overdue book(s). Total fine: ₹{fine_amount}. Please return immediately.',
        'Recipients' => 'Overdue Members',
        'Status' => 'Sent',
        'SentCount' => 15,
        'DeliveredCount' => 14,
        'FailedCount' => 1,
        'ScheduledDate' => '2024-12-19 10:30:00',
        'SentDate' => '2024-12-19 10:30:00',
        'CreatedBy' => 1,
        'CreatedDate' => '2024-12-19 08:00:00',
        'Channels' => ['Email', 'SMS', 'Push'],
        'Priority' => 'Critical'
    ],
    [
        'NotificationID' => 3,
        'Type' => 'New Arrivals',
        'Title' => 'New Books Added to Library Collection',
        'Message' => 'New books in Computer Science and Engineering have arrived! Visit the library to explore our latest collection.',
        'Recipients' => 'All Active Members',
        'Status' => 'Scheduled',
        'SentCount' => 0,
        'DeliveredCount' => 0,
        'FailedCount' => 0,
        'ScheduledDate' => '2024-12-20 14:00:00',
        'SentDate' => null,
        'CreatedBy' => 2,
        'CreatedDate' => '2024-12-19 12:15:00',
        'Channels' => ['Email', 'Push'],
        'Priority' => 'Medium'
    ],
    [
        'NotificationID' => 4,
        'Type' => 'Event Reminder',
        'Title' => 'Digital Library Workshop Tomorrow',
        'Message' => 'Reminder: Digital Library Workshop is scheduled for tomorrow at 2:00 PM in Computer Lab 1. Don\'t miss out!',
        'Recipients' => 'Event Registrants',
        'Status' => 'Draft',
        'SentCount' => 0,
        'DeliveredCount' => 0,
        'FailedCount' => 0,
        'ScheduledDate' => '2024-12-19 18:00:00',
        'SentDate' => null,
        'CreatedBy' => 1,
        'CreatedDate' => '2024-12-19 14:45:00',
        'Channels' => ['Email'],
        'Priority' => 'Medium'
    ],
    [
        'NotificationID' => 5,
        'Type' => 'System Maintenance',
        'Title' => 'Library System Maintenance Notice',
        'Message' => 'The library system will be under maintenance on Sunday from 2:00 AM to 6:00 AM. Online services will be temporarily unavailable.',
        'Recipients' => 'All Members',
        'Status' => 'Failed',
        'SentCount' => 0,
        'DeliveredCount' => 0,
        'FailedCount' => 1456,
        'ScheduledDate' => '2024-12-18 20:00:00',
        'SentDate' => '2024-12-18 20:00:00',
        'CreatedBy' => 1,
        'CreatedDate' => '2024-12-18 16:00:00',
        'Channels' => ['Email', 'SMS', 'Push'],
        'Priority' => 'High'
    ]
];

// Notification templates
$notificationTemplates = [
    'due_reminder' => [
        'name' => 'Book Due Reminder',
        'subject' => 'Library Book Due Reminder - {book_title}',
        'message' => 'Dear {member_name},\n\nThis is a friendly reminder that your book "{book_title}" (Acc No: {acc_no}) is due on {due_date}.\n\nPlease return it on time to avoid late fees.\n\nThank you,\nWIET College Library',
        'variables' => ['member_name', 'book_title', 'acc_no', 'due_date'],
        'channels' => ['Email', 'SMS'],
        'category' => 'Circulation'
    ],
    'overdue_notice' => [
        'name' => 'Overdue Book Notice',
        'subject' => 'Overdue Books - Action Required',
        'message' => 'Dear {member_name},\n\nYou have {overdue_count} overdue book(s):\n{book_list}\n\nTotal fine amount: ₹{fine_amount}\n\nPlease return the books immediately to avoid additional charges.\n\nWIET College Library',
        'variables' => ['member_name', 'overdue_count', 'book_list', 'fine_amount'],
        'channels' => ['Email', 'SMS', 'Push'],
        'category' => 'Circulation'
    ],
    'new_arrivals' => [
        'name' => 'New Book Arrivals',
        'subject' => 'New Books Added to Library Collection',
        'message' => 'Dear {member_name},\n\nWe have added new books to our collection in the following subjects:\n{subject_list}\n\nVisit the library to explore our latest additions!\n\nWIET College Library',
        'variables' => ['member_name', 'subject_list'],
        'channels' => ['Email', 'Push'],
        'category' => 'Announcements'
    ],
    'reservation_ready' => [
        'name' => 'Reserved Book Available',
        'subject' => 'Your Reserved Book is Ready for Pickup',
        'message' => 'Dear {member_name},\n\nYour reserved book "{book_title}" is now available for pickup.\n\nPlease collect it within {pickup_days} days.\n\nWIET College Library',
        'variables' => ['member_name', 'book_title', 'pickup_days'],
        'channels' => ['Email', 'SMS', 'Push'],
        'category' => 'Circulation'
    ],
    'membership_expiry' => [
        'name' => 'Membership Expiry Notice',
        'subject' => 'Library Membership Expires Soon',
        'message' => 'Dear {member_name},\n\nYour library membership expires on {expiry_date}.\n\nPlease renew your membership to continue accessing library services.\n\nWIET College Library',
        'variables' => ['member_name', 'expiry_date'],
        'channels' => ['Email', 'SMS'],
        'category' => 'Membership'
    ],
    'event_announcement' => [
        'name' => 'Event Announcement',
        'subject' => '{event_title} - Library Event',
        'message' => 'Dear {member_name},\n\nWe are excited to announce: {event_title}\n\nDate: {event_date}\nTime: {event_time}\nVenue: {event_venue}\n\n{event_description}\n\nWIET College Library',
        'variables' => ['member_name', 'event_title', 'event_date', 'event_time', 'event_venue', 'event_description'],
        'channels' => ['Email', 'Push'],
        'category' => 'Events'
    ]
];

// Notification statistics
$notificationStats = [
    'total_sent' => 1248,
    'delivered' => 1189,
    'failed' => 59,
    'pending' => 23,
    'scheduled' => 5,
    'delivery_rate' => 95.3
];

// Recent delivery logs
$deliveryLogs = [
    [
        'LogID' => 1,
        'NotificationID' => 1,
        'MemberNo' => 2024001,
        'MemberName' => 'Rahul Sharma',
        'Channel' => 'Email',
        'Status' => 'Delivered',
        'SentAt' => '2024-12-19 09:00:15',
        'DeliveredAt' => '2024-12-19 09:00:47',
        'ErrorMessage' => null
    ],
    [
        'LogID' => 2,
        'NotificationID' => 1,
        'MemberNo' => 2024002,
        'Channel' => 'SMS',
        'Status' => 'Failed',
        'SentAt' => '2024-12-19 09:00:20',
        'DeliveredAt' => null,
        'ErrorMessage' => 'Invalid phone number format'
    ],
    [
        'LogID' => 3,
        'NotificationID' => 2,
        'MemberNo' => 2024003,
        'Channel' => 'Email',
        'Status' => 'Delivered',
        'SentAt' => '2024-12-19 10:30:08',
        'DeliveredAt' => '2024-12-19 10:30:32',
        'ErrorMessage' => null
    ]
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .notifications-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #cfac69;
        }

        .notifications-title {
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
            .notifications-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .notifications-title {
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

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
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

        .stat-card.delivered {
            border-left-color: #28a745;
        }

        .stat-card.failed {
            border-left-color: #dc3545;
        }

        .stat-card.pending {
            border-left-color: #ffc107;
        }

        .stat-card.scheduled {
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

        .notifications-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .notifications-table th {
            background-color: #263c79;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .notifications-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
        }

        .notifications-table tr:hover {
            background-color: rgba(207, 172, 105, 0.1);
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-sent {
            background-color: #d4edda;
            color: #155724;
        }

        .status-scheduled {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-draft {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
        }

        .priority-badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .priority-critical {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .priority-high {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        .priority-medium {
            background-color: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
        }

        .priority-low {
            background-color: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }

        .channel-badges {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
        }

        .channel-badge {
            padding: 2px 6px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .channel-email {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .channel-sms {
            background-color: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
        }

        .channel-push {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        .template-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .template-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .template-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .template-name {
            color: #263c79;
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }

        .template-category {
            background: rgba(38, 60, 121, 0.1);
            color: #263c79;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .template-preview {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 15px;
            font-size: 14px;
            line-height: 1.5;
            color: #495057;
        }

        .template-variables {
            margin-bottom: 15px;
        }

        .variable-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 8px;
        }

        .variable-tag {
            background: rgba(207, 172, 105, 0.1);
            color: #cfac69;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 500;
            font-family: monospace;
        }

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

        .form-group-modal textarea {
            min-height: 80px;
            resize: vertical;
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

        .delivery-logs-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .delivery-logs-table th {
            background-color: #263c79;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .delivery-logs-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
        }

        .delivery-logs-table tr:hover {
            background-color: rgba(207, 172, 105, 0.1);
        }

        .checkbox-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        @media (max-width: 768px) {
            .search-row {
                flex-direction: column;
            }

            .form-group {
                min-width: 100%;
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

            .notifications-table {
                font-size: 12px;
            }

            .channel-badges {
                flex-direction: column;
                gap: 2px;
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
    <div class="notifications-header">
        <h1 class="notifications-title">
            <i class="fas fa-bell"></i>
            Notifications Management
        </h1>
        <div class="action-buttons">
        <!-- Inline Create Notification Form will be placed below stats -->
            <button class="btn btn-info" onclick="bulkNotification()">
                <i class="fas fa-broadcast-tower"></i>
                Bulk Send
            </button>
            <button class="btn btn-warning" onclick="manageTemplates()">
                <i class="fas fa-file-alt"></i>
                Manage Templates
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number" id="totalSent">-</div>
            <div class="stat-label">Total Sent</div>
        </div>
        <div class="stat-card delivered">
            <div class="stat-number" id="delivered">-</div>
            <div class="stat-label">Delivered</div>
        </div>
        <div class="stat-card failed">
            <div class="stat-number" id="failed">-</div>
            <div class="stat-label">Failed</div>
        </div>
        <div class="stat-card pending">
            <div class="stat-number" id="pending">-</div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card scheduled">
            <div class="stat-number" id="scheduled">-</div>
            <div class="stat-label">Scheduled</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="deliveryRate">-</div>
            <div class="stat-label">Delivery Rate</div>
        </div>
    </div>

    <!-- Inline Create Notification Form -->
    <div class="form-section" style="margin-bottom:30px;">
        <form id="createNotificationInlineForm" onsubmit="saveNotificationInline(); return false;">
            <div class="section-title">Create Notification</div>
            <div class="form-row">
                <div class="form-group-modal">
                    <label for="notificationTypeInline">Type <span class="required">*</span></label>
                    <select id="notificationTypeInline" name="Type" required>
                        <option value="">Select Type</option>
                        <option value="Due Reminder">Due Reminder</option>
                        <option value="Overdue Notice">Overdue Notice</option>
                        <option value="New Arrivals">New Arrivals</option>
                        <option value="Event Reminder">Event Reminder</option>
                        <option value="System Maintenance">System Maintenance</option>
                    </select>
                </div>
                <div class="form-group-modal">
                    <label for="notificationTitleInline">Title <span class="required">*</span></label>
                    <input type="text" id="notificationTitleInline" name="Title" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group-modal">
                    <label for="notificationMessageInline">Message <span class="required">*</span></label>
                    <textarea id="notificationMessageInline" name="Message" required></textarea>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group-modal">
                    <label for="notificationRecipientsInline">Recipients <span class="required">*</span></label>
                    <select id="notificationRecipientsInline" name="Recipients" required>
                        <option value="">Select Recipients</option>
                        <option value="All Members">All Members</option>
                        <option value="Specific Members">Specific Members</option>
                        <option value="Overdue Members">Overdue Members</option>
                        <option value="Event Registrants">Event Registrants</option>
                    </select>
                </div>
                <div class="form-group-modal">
                    <label for="notificationChannelsInline">Channels <span class="required">*</span></label>
                    <select id="notificationChannelsInline" name="Channels[]" multiple required>
                        <option value="Email">Email</option>
                        <option value="SMS">SMS</option>
                        <option value="Push">Push</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group-modal">
                    <label for="notificationPriorityInline">Priority <span class="required">*</span></label>
                    <select id="notificationPriorityInline" name="Priority" required>
                        <option value="">Select Priority</option>
                        <option value="Critical">Critical</option>
                        <option value="High">High</option>
                        <option value="Medium">Medium</option>
                        <option value="Low">Low</option>
                    </select>
                </div>
                <div class="form-group-modal">
                    <label for="notificationScheduleInline">Schedule Date</label>
                    <input type="datetime-local" id="notificationScheduleInline" name="ScheduledDate">
                </div>
            </div>
            <div class="form-actions" style="justify-content:flex-start;">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-paper-plane"></i>
                    Create Notification
                </button>
            </div>
        </form>
    </div>

    <!-- Tabs -->
    <div class="tabs-container">
        <div class="tab-buttons">
            <button class="tab-btn active" onclick="showTab('notifications-list')">
                <i class="fas fa-list"></i>
                All Notifications
            </button>
            <button class="tab-btn" onclick="showTab('templates')">
                <i class="fas fa-file-alt"></i>
                Templates
            </button>
            <button class="tab-btn" onclick="showTab('delivery-logs')">
                <i class="fas fa-shipping-fast"></i>
                Delivery Logs
            </button>
            <button class="tab-btn" onclick="showTab('settings')">
                <i class="fas fa-cog"></i>
                Settings
            </button>
        </div>

        <!-- Notifications List Tab -->
        <div id="notifications-list" class="tab-content active">
            <div class="search-filters">
                <div class="search-row">
                    <div class="form-group">
                        <label for="searchTitle">Title</label>
                        <input type="text" id="searchTitle" class="form-control" placeholder="Search by title...">
                    </div>
                    <div class="form-group">
                        <label for="searchType">Type</label>
                        <select id="searchType" class="form-control">
                            <option value="">All Types</option>
                            <option value="Due Reminder">Due Reminder</option>
                            <option value="Overdue Notice">Overdue Notice</option>
                            <option value="New Arrivals">New Arrivals</option>
                            <option value="Event Reminder">Event Reminder</option>
                            <option value="System Maintenance">System Maintenance</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="searchStatus">Status</label>
                        <select id="searchStatus" class="form-control">
                            <option value="">All Status</option>
                            <option value="Sent">Sent</option>
                            <option value="Scheduled">Scheduled</option>
                            <option value="Draft">Draft</option>
                            <option value="Failed">Failed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="searchDate">Date</label>
                        <input type="date" id="searchDate" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-primary" onclick="searchNotifications()">
                            <i class="fas fa-search"></i>
                            Search
                        </button>
                    </div>
                </div>
            </div>

            <div id="notificationsTableContainer">
                <!-- Notifications table will be loaded here -->
            </div>
        </div>

        <!-- Templates Tab -->
        <div id="templates" class="tab-content">
            <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="color: #263c79; margin: 0;">Notification Templates</h3>
                <button class="btn btn-success" onclick="openCreateTemplateModal()">
                    <i class="fas fa-plus"></i>
                    Create Template
                </button>
            </div>

            <div id="templatesContainer">
                <!-- Templates will be loaded here -->
            </div>
        </div>

        <!-- Delivery Logs Tab -->
        <div id="delivery-logs" class="tab-content">
            <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="color: #263c79; margin: 0;">Delivery Logs</h3>
                <button class="btn btn-info" onclick="exportDeliveryLogs()">
                    <i class="fas fa-download"></i>
                    Export Logs
                </button>
            </div>

            <div id="deliveryLogsContainer">
                <!-- Delivery logs will be loaded here -->
            </div>
        </div>

        <!-- Settings Tab -->
        <div id="settings" class="tab-content">
            <div id="settingsContent">
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <i class="fas fa-cog" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <h3>Notification Settings</h3>
                    <p>Configure notification channels, delivery preferences, and system settings.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Notification Modal -->
    <div id="createNotificationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Create New Notification</h3>
                <button class="close" onclick="closeModal('createNotificationModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="createNotificationForm">
                    <!-- Basic Information Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-info-circle"></i>
                            Basic Information
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="notificationType">Notification Type <span class="required">*</span></label>
                                <select id="notificationType" name="Type" required>
                                    <option value="">Select Type</option>
                                    <option value="Due Reminder">Due Reminder</option>
                                    <option value="Overdue Notice">Overdue Notice</option>
                                    <option value="New Arrivals">New Arrivals</option>
                                    <option value="Event Reminder">Event Reminder</option>
                                    <option value="System Maintenance">System Maintenance</option>
                                    <option value="Custom">Custom</option>
                                </select>
                            </div>
                            <div class="form-group-modal">
                                <label for="notificationPriority">Priority <span class="required">*</span></label>
                                <select id="notificationPriority" name="Priority" required>
                                    <option value="Low">Low</option>
                                    <option value="Medium" selected>Medium</option>
                                    <option value="High">High</option>
                                    <option value="Critical">Critical</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="notificationTitle">Title <span class="required">*</span></label>
                                <input type="text" id="notificationTitle" name="Title" required placeholder="Enter notification title">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="notificationMessage">Message <span class="required">*</span></label>
                                <textarea id="notificationMessage" name="Message" rows="4" required placeholder="Enter notification message..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Recipients Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-users"></i>
                            Recipients
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="recipientType">Recipient Type <span class="required">*</span></label>
                                <select id="recipientType" name="Recipients" required>
                                    <option value="">Select Recipients</option>
                                    <option value="All Active Members">All Active Members</option>
                                    <option value="Students Only">Students Only</option>
                                    <option value="Faculty Only">Faculty Only</option>
                                    <option value="Staff Only">Staff Only</option>
                                    <option value="Overdue Members">Members with Overdue Books</option>
                                    <option value="Due Tomorrow">Books Due Tomorrow</option>
                                    <option value="Event Registrants">Event Registrants</option>
                                    <option value="Specific Members">Specific Members</option>
                                    <option value="Custom Query">Custom Query</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row" id="specificMembersRow" style="display: none;">
                            <div class="form-group-modal">
                                <label for="specificMembers">Member Numbers (comma-separated)</label>
                                <textarea id="specificMembers" name="SpecificMembers" rows="3" placeholder="Enter member numbers separated by commas (e.g., 2024001, 2024002, 2024003)"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Settings Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-paper-plane"></i>
                            Delivery Settings
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label>Delivery Channels <span class="required">*</span></label>
                                <div class="checkbox-group">
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="channelEmail" name="Channels[]" value="Email" checked>
                                        <label for="channelEmail">Email</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="channelSMS" name="Channels[]" value="SMS">
                                        <label for="channelSMS">SMS</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="channelPush" name="Channels[]" value="Push">
                                        <label for="channelPush">Push Notification</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="scheduledDate">Schedule Date & Time</label>
                                <input type="datetime-local" id="scheduledDate" name="ScheduledDate" value="<?php echo date('Y-m-d\TH:i'); ?>">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('createNotificationModal')">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="saveAsDraft()">
                    <i class="fas fa-save"></i>
                    Save as Draft
                </button>
                <button type="button" class="btn btn-success" onclick="sendNotification()">
                    <i class="fas fa-paper-plane"></i>
                    Send Now
                </button>
            </div>
        </div>
    </div>

    <script>
        // Inline Create Notification handler
        function saveNotificationInline() {
            const formData = new FormData(document.getElementById('createNotificationInlineForm'));
            const notificationData = Object.fromEntries(formData);
            notificationData.Channels = Array.from(document.getElementById('notificationChannelsInline').selectedOptions).map(opt => opt.value);
            notificationData.NotificationID = sampleNotifications.length + 1;
            notificationData.Status = 'Draft';
            notificationData.CreatedBy = 1;
            notificationData.CreatedDate = new Date().toISOString();
            sampleNotifications.push(notificationData);
            alert('Notification created successfully!');
            loadNotificationsList();
            document.getElementById('createNotificationInlineForm').reset();
        }
        // Global variables
        const sampleNotifications = <?php echo json_encode($sampleNotifications); ?>;
        const notificationTemplates = <?php echo json_encode($notificationTemplates); ?>;
        const notificationStats = <?php echo json_encode($notificationStats); ?>;
        const deliveryLogs = <?php echo json_encode($deliveryLogs); ?>;

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
                case 'notifications-list':
                    loadNotificationsList();
                    break;
                case 'templates':
                    loadTemplatesContent();
                    break;
                case 'delivery-logs':
                    loadDeliveryLogsContent();
                    break;
                case 'settings':
                    loadSettingsContent();
                    break;
            }
        }

        function loadNotificationsList(searchParams = {}) {
            let filteredNotifications = sampleNotifications;

            // Apply search filters
            if (searchParams.title) {
                filteredNotifications = filteredNotifications.filter(notification =>
                    notification.Title.toLowerCase().includes(searchParams.title.toLowerCase())
                );
            }
            if (searchParams.type) {
                filteredNotifications = filteredNotifications.filter(notification =>
                    notification.Type === searchParams.type
                );
            }
            if (searchParams.status) {
                filteredNotifications = filteredNotifications.filter(notification =>
                    notification.Status === searchParams.status
                );
            }

            let tableHTML = `
                <table class="notifications-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Title</th>
                            <th>Recipients</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Channels</th>
                            <th>Delivery Stats</th>
                            <th>Scheduled/Sent</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            if (filteredNotifications.length === 0) {
                tableHTML += `
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px; color: #6c757d;">
                            <i class="fas fa-search" style="font-size: 24px; margin-bottom: 10px;"></i>
                            <p>No notifications found matching your search criteria.</p>
                        </td>
                    </tr>
                `;
            } else {
                filteredNotifications.forEach(notification => {
                    const statusClass = `status-${notification.Status.toLowerCase()}`;
                    const priorityClass = `priority-${notification.Priority.toLowerCase()}`;

                    tableHTML += `
                        <tr>
                            <td><strong>${notification.Type}</strong></td>
                            <td>
                                <strong style="color: #263c79;">${notification.Title}</strong>
                                <br><small style="color: #6c757d;">${notification.Message.substring(0, 50)}...</small>
                            </td>
                            <td>${notification.Recipients}</td>
                            <td><span class="status-badge ${statusClass}">${notification.Status}</span></td>
                            <td><span class="priority-badge ${priorityClass}">${notification.Priority}</span></td>
                            <td>
                                <div class="channel-badges">
                                    ${notification.Channels.map(channel => 
                                        `<span class="channel-badge channel-${channel.toLowerCase()}">${channel}</span>`
                                    ).join('')}
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 12px;">
                                    <div>Sent: <strong style="color: #263c79;">${notification.SentCount}</strong></div>
                                    <div>Delivered: <strong style="color: #28a745;">${notification.DeliveredCount}</strong></div>
                                    ${notification.FailedCount > 0 ? `<div>Failed: <strong style="color: #dc3545;">${notification.FailedCount}</strong></div>` : ''}
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 12px;">
                                    <div>${notification.ScheduledDate ? new Date(notification.ScheduledDate).toLocaleDateString('en-IN') : '-'}</div>
                                    <div style="color: #6c757d;">${notification.ScheduledDate ? new Date(notification.ScheduledDate).toLocaleTimeString('en-IN', {hour: '2-digit', minute: '2-digit'}) : ''}</div>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                    <button class="btn-info" onclick="viewNotification(${notification.NotificationID})" style="padding: 4px 8px; font-size: 11px;">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    ${notification.Status === 'Draft' || notification.Status === 'Scheduled' ? 
                                        `<button class="btn-warning" onclick="editNotification(${notification.NotificationID})" style="padding: 4px 8px; font-size: 11px;">
                                            <i class="fas fa-edit"></i>
                                        </button>` : ''
                                    }
                                    ${notification.Status === 'Scheduled' ? 
                                        `<button class="btn-danger" onclick="cancelNotification(${notification.NotificationID})" style="padding: 4px 8px; font-size: 11px;">
                                            <i class="fas fa-times"></i>
                                        </button>` : ''
                                    }
                                    <button class="btn-secondary" onclick="duplicateNotification(${notification.NotificationID})" style="padding: 4px 8px; font-size: 11px;">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                });
            }

            tableHTML += `
                    </tbody>
                </table>
            `;

            document.getElementById('notificationsTableContainer').innerHTML = tableHTML;
        }

        function loadTemplatesContent() {
            let templatesHTML = '';

            Object.entries(notificationTemplates).forEach(([key, template]) => {
                templatesHTML += `
                    <div class="template-card">
                        <div class="template-header">
                            <h4 class="template-name">${template.name}</h4>
                            <span class="template-category">${template.category}</span>
                        </div>
                        
                        <div class="template-preview">
                            <strong>Subject:</strong> ${template.subject}<br><br>
                            ${template.message.substring(0, 200)}${template.message.length > 200 ? '...' : ''}
                        </div>
                        
                        <div class="template-variables">
                            <strong style="color: #263c79; font-size: 14px;">Available Variables:</strong>
                            <div class="variable-tags">
                                ${template.variables.map(variable => 
                                    `<span class="variable-tag">{${variable}}</span>`
                                ).join('')}
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <div class="channel-badges">
                                ${template.channels.map(channel => 
                                    `<span class="channel-badge channel-${channel.toLowerCase()}">${channel}</span>`
                                ).join('')}
                            </div>
                            <div style="margin-left: auto; display: flex; gap: 8px;">
                                <button class="btn btn-primary" onclick="useTemplate('${key}')" style="padding: 6px 12px; font-size: 12px;">
                                    <i class="fas fa-paper-plane"></i>
                                    Use Template
                                </button>
                                <button class="btn btn-secondary" onclick="editTemplate('${key}')" style="padding: 6px 12px; font-size: 12px;">
                                    <i class="fas fa-edit"></i>
                                    Edit
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });

            document.getElementById('templatesContainer').innerHTML = templatesHTML;
        }

        function loadDeliveryLogsContent() {
            let logsHTML = `
                <table class="delivery-logs-table">
                    <thead>
                        <tr>
                            <th>Notification</th>
                            <th>Member</th>
                            <th>Channel</th>
                            <th>Status</th>
                            <th>Sent At</th>
                            <th>Delivered At</th>
                            <th>Error</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            deliveryLogs.forEach(log => {
                const notification = sampleNotifications.find(n => n.NotificationID === log.NotificationID);
                const statusClass = log.Status === 'Delivered' ? 'status-sent' : 'status-failed';

                logsHTML += `
                    <tr>
                        <td><strong>${notification ? notification.Title : 'Unknown'}</strong></td>
                        <td>
                            <div>${log.MemberName || 'Unknown Member'}</div>
                            <small style="color: #6c757d;">${log.MemberNo || ''}</small>
                        </td>
                        <td><span class="channel-badge channel-${log.Channel.toLowerCase()}">${log.Channel}</span></td>
                        <td><span class="status-badge ${statusClass}">${log.Status}</span></td>
                        <td>${new Date(log.SentAt).toLocaleString('en-IN')}</td>
                        <td>${log.DeliveredAt ? new Date(log.DeliveredAt).toLocaleString('en-IN') : '-'}</td>
                        <td>${log.ErrorMessage ? `<span style="color: #dc3545; font-size: 12px;">${log.ErrorMessage}</span>` : '-'}</td>
                    </tr>
                `;
            });

            logsHTML += `
                    </tbody>
                </table>
            `;

            document.getElementById('deliveryLogsContainer').innerHTML = logsHTML;
        }

        function loadSettingsContent() {
            const settingsHTML = `
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                    <div onclick="configureEmailSettings()" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-envelope" style="font-size: 24px; color: #28a745; margin-bottom: 10px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 5px;">Email Settings</h4>
                        <p style="color: #6c757d; font-size: 14px;">Configure SMTP settings and email templates</p>
                    </div>
                    
                    <div onclick="configureSMSSettings()" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-sms" style="font-size: 24px; color: #17a2b8; margin-bottom: 10px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 5px;">SMS Settings</h4>
                        <p style="color: #6c757d; font-size: 14px;">Configure SMS gateway and message settings</p>
                    </div>
                    
                    <div onclick="configurePushSettings()" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-mobile-alt" style="font-size: 24px; color: #ffc107; margin-bottom: 10px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 5px;">Push Notifications</h4>
                        <p style="color: #6c757d; font-size: 14px;">Configure push notification services</p>
                    </div>
                    
                    <div onclick="configureDeliveryRules()" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-route" style="font-size: 24px; color: #6f42c1; margin-bottom: 10px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 5px;">Delivery Rules</h4>
                        <p style="color: #6c757d; font-size: 14px;">Set up delivery preferences and rules</p>
                    </div>
                </div>
            `;

            document.getElementById('settingsContent').innerHTML = settingsHTML;
        }

        function searchNotifications() {
            const searchParams = {
                title: document.getElementById('searchTitle').value.trim(),
                type: document.getElementById('searchType').value,
                status: document.getElementById('searchStatus').value,
                date: document.getElementById('searchDate').value
            };

            loadNotificationsList(searchParams);
        }

        // Modal functions
        function openCreateNotificationModal() {
            document.getElementById('createNotificationModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';

            if (modalId === 'createNotificationModal') {
                document.getElementById('createNotificationForm').reset();
            }
        }

        // Handle recipient type change
        document.addEventListener('DOMContentLoaded', function() {
            const recipientType = document.getElementById('recipientType');
            const specificMembersRow = document.getElementById('specificMembersRow');

            if (recipientType && specificMembersRow) {
                recipientType.addEventListener('change', function() {
                    if (this.value === 'Specific Members') {
                        specificMembersRow.style.display = 'flex';
                    } else {
                        specificMembersRow.style.display = 'none';
                    }
                });
            }
        });

        function saveAsDraft() {
            const formData = new FormData(document.getElementById('createNotificationForm'));
            const notificationData = Object.fromEntries(formData);

            console.log('Saving notification as draft:', notificationData);
            alert('Notification saved as draft successfully!');
            closeModal('createNotificationModal');
            loadNotificationsList();
        }

        function sendNotification() {
            const formData = new FormData(document.getElementById('createNotificationForm'));
            const notificationData = Object.fromEntries(formData);

            if (confirm('Are you sure you want to send this notification now?')) {
                console.log('Sending notification:', notificationData);
                alert('Notification sent successfully!');
                closeModal('createNotificationModal');
                loadNotificationsList();
            }
        }

        // Notification actions
        function viewNotification(notificationId) {
            console.log('Viewing notification:', notificationId);
            alert(`Opening detailed view for Notification ID: ${notificationId}`);
        }

        function editNotification(notificationId) {
            console.log('Editing notification:', notificationId);
            alert(`Opening edit form for Notification ID: ${notificationId}`);
        }

        function cancelNotification(notificationId) {
            if (confirm('Are you sure you want to cancel this scheduled notification?')) {
                console.log('Cancelling notification:', notificationId);
                alert('Notification cancelled successfully!');
                loadNotificationsList();
            }
        }

        function duplicateNotification(notificationId) {
            console.log('Duplicating notification:', notificationId);
            alert(`Creating copy of Notification ID: ${notificationId}`);
        }

        // Template functions
        function useTemplate(templateKey) {
            console.log('Using template:', templateKey);
            alert(`Loading template: ${notificationTemplates[templateKey].name}`);
            openCreateNotificationModal();
        }

        function editTemplate(templateKey) {
            console.log('Editing template:', templateKey);
            alert(`Opening editor for template: ${notificationTemplates[templateKey].name}`);
        }

        function openCreateTemplateModal() {
            console.log('Opening create template modal...');
            alert('Opening template creation interface...');
        }

        // Other functions
        function bulkNotification() {
            console.log('Opening bulk notification...');
            alert('Opening bulk notification interface...');
        }

        function manageTemplates() {
            showTab('templates');
            document.querySelector('.tab-btn[onclick="showTab(\'templates\')"]').classList.add('active');
        }

        function exportDeliveryLogs() {
            console.log('Exporting delivery logs...');
            alert('Exporting delivery logs to CSV...');
        }

        function configureEmailSettings() {
            console.log('Opening email settings...');
            alert('Opening email configuration...');
        }

        function configureSMSSettings() {
            console.log('Opening SMS settings...');
            alert('Opening SMS gateway configuration...');
        }

        function configurePushSettings() {
            console.log('Opening push notification settings...');
            alert('Opening push notification configuration...');
        }

        function configureDeliveryRules() {
            console.log('Opening delivery rules...');
            alert('Opening delivery rules configuration...');
        }

        // Load statistics
        function loadStatistics() {
            document.getElementById('totalSent').textContent = notificationStats.total_sent.toLocaleString();
            document.getElementById('delivered').textContent = notificationStats.delivered.toLocaleString();
            document.getElementById('failed').textContent = notificationStats.failed.toLocaleString();
            document.getElementById('pending').textContent = notificationStats.pending.toLocaleString();
            document.getElementById('scheduled').textContent = notificationStats.scheduled.toLocaleString();
            document.getElementById('deliveryRate').textContent = notificationStats.delivery_rate + '%';
        }

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
            loadStatistics();
            loadNotificationsList();
        });
    </script>
</body>

</html>