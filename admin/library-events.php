<?php
session_start();

// No database connection needed for frontend development
// Sample data will be used to demonstrate functionality

$admin_id = $_SESSION['admin_id'] ?? 1;
$admin_name = $_SESSION['admin_name'] ?? 'Admin User';

// Sample events data matching potential database schema
$sampleEvents = [
    [
        'EventID' => 1,
        'EventTitle' => 'New Book Arrivals Exhibition',
        'EventType' => 'Exhibition',
        'Description' => 'Showcasing latest arrivals in Computer Science, Engineering, and Technology domains. Students can explore and reserve books.',
        'StartDate' => '2024-12-25',
        'EndDate' => '2024-12-30',
        'StartTime' => '09:00:00',
        'EndTime' => '17:00:00',
        'Venue' => 'Library Main Hall',
        'Capacity' => 100,
        'Registered' => 67,
        'Status' => 'Upcoming',
        'OrganizedBy' => 'Library Staff',
        'ContactPerson' => 'Ms. Priya Patel',
        'ContactEmail' => 'priya.patel@wiet.edu',
        'ContactPhone' => '9876543211',
        'RegistrationRequired' => true,
        'RegistrationDeadline' => '2024-12-23',
        'EventImage' => '/images/events/book-exhibition.jpg',
        'CreatedBy' => 1,
        'CreatedDate' => '2024-12-01 10:30:00',
        'ModifiedBy' => 1,
        'ModifiedDate' => '2024-12-15 14:20:00'
    ],
    [
        'EventID' => 2,
        'EventTitle' => 'Digital Library Workshop',
        'EventType' => 'Workshop',
        'Description' => 'Hands-on workshop on utilizing digital resources, online databases, and e-learning platforms effectively.',
        'StartDate' => '2024-12-20',
        'EndDate' => '2024-12-20',
        'StartTime' => '14:00:00',
        'EndTime' => '16:30:00',
        'Venue' => 'Computer Lab 1',
        'Capacity' => 40,
        'Registered' => 35,
        'Status' => 'Active',
        'OrganizedBy' => 'IT Department & Library',
        'ContactPerson' => 'Dr. Rajesh Kumar',
        'ContactEmail' => 'rajesh.kumar@wiet.edu',
        'ContactPhone' => '9876543210',
        'RegistrationRequired' => true,
        'RegistrationDeadline' => '2024-12-18',
        'EventImage' => '/images/events/digital-workshop.jpg',
        'CreatedBy' => 2,
        'CreatedDate' => '2024-11-15 09:00:00',
        'ModifiedBy' => 1,
        'ModifiedDate' => '2024-12-10 11:15:00'
    ],
    [
        'EventID' => 3,
        'EventTitle' => 'Research Paper Writing Seminar',
        'EventType' => 'Seminar',
        'Description' => 'Learn effective techniques for writing research papers, citations, and academic writing standards.',
        'StartDate' => '2024-12-15',
        'EndDate' => '2024-12-15',
        'StartTime' => '10:00:00',
        'EndTime' => '12:00:00',
        'Venue' => 'Auditorium',
        'Capacity' => 150,
        'Registered' => 120,
        'Status' => 'Completed',
        'OrganizedBy' => 'Academic Department',
        'ContactPerson' => 'Prof. Sneha Gupta',
        'ContactEmail' => 'sneha.gupta@wiet.edu',
        'ContactPhone' => '9876543213',
        'RegistrationRequired' => true,
        'RegistrationDeadline' => '2024-12-13',
        'EventImage' => '/images/events/research-seminar.jpg',
        'CreatedBy' => 1,
        'CreatedDate' => '2024-11-20 16:45:00',
        'ModifiedBy' => 2,
        'ModifiedDate' => '2024-12-14 08:30:00'
    ],
    [
        'EventID' => 4,
        'EventTitle' => 'Book Reading Marathon',
        'EventType' => 'Competition',
        'Description' => '24-hour reading challenge for students to promote reading culture and win exciting prizes.',
        'StartDate' => '2025-01-10',
        'EndDate' => '2025-01-11',
        'StartTime' => '18:00:00',
        'EndTime' => '18:00:00',
        'Venue' => 'Library Reading Hall',
        'Capacity' => 50,
        'Registered' => 28,
        'Status' => 'Upcoming',
        'OrganizedBy' => 'Library Committee',
        'ContactPerson' => 'Mr. Amit Sharma',
        'ContactEmail' => 'amit.sharma@wiet.edu',
        'ContactPhone' => '9876543212',
        'RegistrationRequired' => true,
        'RegistrationDeadline' => '2025-01-05',
        'EventImage' => '/images/events/reading-marathon.jpg',
        'CreatedBy' => 3,
        'CreatedDate' => '2024-12-05 12:00:00',
        'ModifiedBy' => null,
        'ModifiedDate' => null
    ],
    [
        'EventID' => 5,
        'EventTitle' => 'Library Orientation for New Students',
        'EventType' => 'Orientation',
        'Description' => 'Comprehensive orientation program for newly admitted students about library facilities, rules, and services.',
        'StartDate' => '2024-11-25',
        'EndDate' => '2024-11-25',
        'StartTime' => '11:00:00',
        'EndTime' => '13:00:00',
        'Venue' => 'Library Main Hall',
        'Capacity' => 200,
        'Registered' => 180,
        'Status' => 'Completed',
        'OrganizedBy' => 'Library Administration',
        'ContactPerson' => 'Ms. Priya Patel',
        'ContactEmail' => 'priya.patel@wiet.edu',
        'ContactPhone' => '9876543211',
        'RegistrationRequired' => false,
        'RegistrationDeadline' => null,
        'EventImage' => '/images/events/orientation.jpg',
        'CreatedBy' => 1,
        'CreatedDate' => '2024-11-01 14:30:00',
        'ModifiedBy' => 1,
        'ModifiedDate' => '2024-11-20 16:00:00'
    ]
];

// Event types configuration
$eventTypes = [
    'Workshop' => ['icon' => 'fas fa-tools', 'color' => '#17a2b8'],
    'Seminar' => ['icon' => 'fas fa-chalkboard-teacher', 'color' => '#28a745'],
    'Exhibition' => ['icon' => 'fas fa-eye', 'color' => '#ffc107'],
    'Competition' => ['icon' => 'fas fa-trophy', 'color' => '#dc3545'],
    'Orientation' => ['icon' => 'fas fa-compass', 'color' => '#6f42c1'],
    'Conference' => ['icon' => 'fas fa-users', 'color' => '#fd7e14'],
    'Training' => ['icon' => 'fas fa-graduation-cap', 'color' => '#20c997'],
    'Meeting' => ['icon' => 'fas fa-handshake', 'color' => '#6c757d']
];

// Sample registrations data
$eventRegistrations = [
    [
        'RegistrationID' => 1,
        'EventID' => 2,
        'MemberNo' => 2024001,
        'MemberName' => 'Rahul Sharma',
        'Email' => 'rahul.sharma@student.wiet.edu',
        'Phone' => '9876543210',
        'RegistrationDate' => '2024-12-05 10:30:00',
        'Status' => 'Confirmed',
        'AttendanceStatus' => 'Present'
    ],
    [
        'RegistrationID' => 2,
        'EventID' => 2,
        'MemberNo' => 2024002,
        'MemberName' => 'Priya Patel',
        'Email' => 'priya.patel@student.wiet.edu',
        'Phone' => '9876543211',
        'RegistrationDate' => '2024-12-06 14:20:00',
        'Status' => 'Confirmed',
        'AttendanceStatus' => 'Present'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Events Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .events-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #cfac69;
        }

        .events-title {
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
            .events-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .events-title {
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

        .stat-card.upcoming {
            border-left-color: #17a2b8;
        }

        .stat-card.active {
            border-left-color: #28a745;
        }

        .stat-card.completed {
            border-left-color: #6c757d;
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

        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
        }

        .event-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .event-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .event-image {
            height: 150px;
            background: linear-gradient(135deg, #263c79, #cfac69);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
            position: relative;
        }

        .event-status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-upcoming {
            background: rgba(23, 162, 184, 0.9);
            color: white;
        }

        .status-active {
            background: rgba(40, 167, 69, 0.9);
            color: white;
        }

        .status-completed {
            background: rgba(108, 117, 125, 0.9);
            color: white;
        }

        .status-cancelled {
            background: rgba(220, 53, 69, 0.9);
            color: white;
        }

        .event-content {
            padding: 20px;
        }

        .event-header {
            margin-bottom: 15px;
        }

        .event-title {
            color: #263c79;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .event-type {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 8px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .event-details {
            margin-bottom: 15px;
            font-size: 14px;
            color: #6c757d;
        }

        .event-detail {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .event-description {
            color: #495057;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .event-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
        }

        .registration-info {
            font-size: 13px;
            color: #6c757d;
        }

        .event-actions {
            display: flex;
            gap: 8px;
        }

        .event-actions button,
        .event-actions a {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }

        .calendar-view {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: #e9ecef;
        }

        .calendar-day {
            background: white;
            min-height: 100px;
            padding: 8px;
            position: relative;
        }

        .calendar-day-number {
            font-weight: 600;
            color: #263c79;
            margin-bottom: 5px;
        }

        .calendar-event {
            background: #cfac69;
            color: white;
            font-size: 10px;
            padding: 2px 4px;
            border-radius: 3px;
            margin-bottom: 2px;
            cursor: pointer;
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

        .registrations-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .registrations-table th {
            background-color: #263c79;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .registrations-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
        }

        .registrations-table tr:hover {
            background-color: rgba(207, 172, 105, 0.1);
        }

        @media (max-width: 768px) {
            .search-row {
                flex-direction: column;
            }

            .form-group {
                min-width: 100%;
            }

            .events-grid {
                grid-template-columns: 1fr;
                gap: 15px;
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

            .calendar-grid {
                font-size: 12px;
            }

            .calendar-day {
                min-height: 80px;
                padding: 4px;
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
    <div class="events-header">
        <h1 class="events-title">
            <i class="fas fa-calendar-alt"></i>
            Library Events Management
        </h1>
        <div class="action-buttons">
            <button class="btn btn-info" onclick="exportEvents()">
                <i class="fas fa-download"></i>
                Export Calendar
            </button>
            <button class="btn btn-warning" onclick="manageNotifications()">
                <i class="fas fa-bell"></i>
                Manage Notifications
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number" id="totalEvents">-</div>
            <div class="stat-label">Total Events</div>
        </div>
        <div class="stat-card upcoming">
            <div class="stat-number" id="upcomingEvents">-</div>
            <div class="stat-label">Upcoming Events</div>
        </div>
        <div class="stat-card active">
            <div class="stat-number" id="activeEvents">-</div>
            <div class="stat-label">Active Events</div>
        </div>
        <div class="stat-card completed">
            <div class="stat-number" id="completedEvents">-</div>
            <div class="stat-label">Completed Events</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="totalRegistrations">-</div>
            <div class="stat-label">Total Registrations</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="averageAttendance">-</div>
            <div class="stat-label">Avg. Attendance %</div>
        </div>
    </div>

        <!-- Add New Event Section -->
        <div class="add-event-section">
            <h3 class="section-title">
                Create New Event
            </h3>
            <form id="addEventInlineForm">
                <!-- Basic Information Section -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-info-circle"></i>
                        Basic Information
                    </div>
                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="eventTitleInline">Event Title <span class="required">*</span></label>
                            <input type="text" id="eventTitleInline" name="EventTitle" required>
                        </div>
                        <div class="form-group-modal">
                            <label for="eventTypeInline">Event Type <span class="required">*</span></label>
                            <select id="eventTypeInline" name="EventType" required>
                                <option value="">Select Type</option>
                                <option value="Seminar">Seminar</option>
                                <option value="Workshop">Workshop</option>
                                <option value="Competition">Competition</option>
                                <option value="Exhibition">Exhibition</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="eventDescriptionInline">Event Description <span class="required">*</span></label>
                            <textarea id="eventDescriptionInline" name="Description" rows="4" required placeholder="Describe the event purpose, activities, and benefits..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Date & Time Section -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-calendar-alt"></i>
                        Date & Time
                    </div>
                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="startDateInline">Start Date <span class="required">*</span></label>
                            <input type="date" id="startDateInline" name="StartDate" required>
                        </div>
                        <div class="form-group-modal">
                            <label for="endDateInline">End Date <span class="required">*</span></label>
                            <input type="date" id="endDateInline" name="EndDate" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="startTimeInline">Start Time <span class="required">*</span></label>
                            <input type="time" id="startTimeInline" name="StartTime" required>
                        </div>
                        <div class="form-group-modal">
                            <label for="endTimeInline">End Time <span class="required">*</span></label>
                            <input type="time" id="endTimeInline" name="EndTime" required>
                        </div>
                    </div>
                </div>

                <!-- Venue & Capacity Section -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-map-marker-alt"></i>
                        Venue & Capacity
                    </div>
                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="venueInline">Venue <span class="required">*</span></label>
                            <input type="text" id="venueInline" name="Venue" required placeholder="e.g., Library Main Hall, Auditorium">
                        </div>
                        <div class="form-group-modal">
                            <label for="capacityInline">Maximum Capacity</label>
                            <input type="number" id="capacityInline" name="Capacity" min="1" max="500" placeholder="Number of participants">
                        </div>
                    </div>
                </div>

                <!-- Organization Details Section -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-users"></i>
                        Organization Details
                    </div>
                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="organizedByInline">Organized By <span class="required">*</span></label>
                            <input type="text" id="organizedByInline" name="OrganizedBy" required placeholder="Department/Organization name">
                        </div>
                        <div class="form-group-modal">
                            <label for="contactPersonInline">Contact Person <span class="required">*</span></label>
                            <input type="text" id="contactPersonInline" name="ContactPerson" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="contactEmailInline">Contact Email <span class="required">*</span></label>
                            <input type="email" id="contactEmailInline" name="ContactEmail" required>
                        </div>
                        <div class="form-group-modal">
                            <label for="contactPhoneInline">Contact Phone <span class="required">*</span></label>
                            <input type="tel" id="contactPhoneInline" name="ContactPhone" required>
                        </div>
                    </div>
                </div>

                <!-- Registration Settings Section -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-clipboard-check"></i>
                        Registration Settings
                    </div>
                    <div class="form-row">
                        <div class="form-group-modal">
                            <label>
                                <input type="checkbox" id="registrationRequiredInline" name="RegistrationRequired" value="1">
                                Registration Required
                            </label>
                        </div>
                        <div class="form-group-modal">
                            <label for="registrationDeadlineInline">Registration Deadline</label>
                            <input type="date" id="registrationDeadlineInline" name="RegistrationDeadline">
                        </div>
                    </div>
                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-success" onclick="saveEventInline(); return false;">
                            <i class="fas fa-save"></i>
                            Create Event
                        </button>
                    </div>
                </div>
            </form>
        </div>

    <!-- Tabs -->
    <div class="tabs-container">
        <div class="tab-buttons">
            <button class="tab-btn active" onclick="showTab('events-list')">
                <i class="fas fa-list"></i>
                Events List
            </button>
            <button class="tab-btn" onclick="showTab('calendar-view')">
                <i class="fas fa-calendar"></i>
                Calendar View
            </button>
            <button class="tab-btn" onclick="showTab('registrations')">
                <i class="fas fa-user-check"></i>
                Registrations
            </button>
            <button class="tab-btn" onclick="showTab('analytics')">
                <i class="fas fa-chart-bar"></i>
                Analytics
            </button>
        </div>

        <!-- Events List Tab -->
        <div id="events-list" class="tab-content active">
            <div class="search-filters">
                <div class="search-row">
                    <div class="form-group">
                        <label for="searchTitle">Event Title</label>
                        <input type="text" id="searchTitle" class="form-control" placeholder="Search by title...">
                    </div>
                    <div class="form-group">
                        <label for="searchType">Event Type</label>
                        <select id="searchType" class="form-control">
                            <option value="">All Types</option>
                            <?php foreach ($eventTypes as $type => $config): ?>
                                <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="searchStatus">Status</label>
                        <select id="searchStatus" class="form-control">
                            <option value="">All Status</option>
                            <option value="Upcoming">Upcoming</option>
                            <option value="Active">Active</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="searchDate">Date Range</label>
                        <input type="date" id="searchDate" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-primary" onclick="searchEvents()">
                            <i class="fas fa-search"></i>
                            Search
                        </button>
                    </div>
                </div>
            </div>

            <div id="eventsContainer" class="events-grid">
                <!-- Events will be loaded here -->
            </div>
        </div>

        <!-- Calendar View Tab -->
        <div id="calendar-view" class="tab-content">
            <div class="calendar-view">
                <div class="calendar-header">
                    <h3 style="color: #263c79; margin: 0;">December 2024</h3>
                    <div>
                        <button class="btn btn-secondary" onclick="previousMonth()">
                            <i class="fas fa-chevron-left"></i>
                            Previous
                        </button>
                        <button class="btn btn-primary" onclick="nextMonth()" style="margin-left: 10px;">
                            Next
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div id="calendarContainer">
                    <!-- Calendar will be generated here -->
                    <div style="text-align: center; padding: 40px; color: #6c757d;">
                        <i class="fas fa-calendar-alt" style="font-size: 48px; margin-bottom: 15px;"></i>
                        <h3>Calendar View</h3>
                        <p>Interactive calendar showing all library events.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Registrations Tab -->
        <div id="registrations" class="tab-content">
            <div id="registrationsContent">
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <i class="fas fa-user-check" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <h3>Event Registrations</h3>
                    <p>Manage event registrations and attendance tracking.</p>
                </div>
            </div>
        </div>

        <!-- Analytics Tab -->
        <div id="analytics" class="tab-content">
            <div id="analyticsContent">
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <i class="fas fa-chart-bar" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <h3>Events Analytics</h3>
                    <p>Comprehensive analytics and reports on event performance.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Event Modal -->
    <div id="addEventModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Create New Event</h3>
                <button class="close" onclick="closeModal('addEventModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addEventForm">
                    <!-- Basic Information Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-info-circle"></i>
                            Basic Information
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="eventTitle">Event Title <span class="required">*</span></label>
                                <input type="text" id="eventTitle" name="EventTitle" required>
                            </div>
                            <div class="form-group-modal">
                                <label for="eventType">Event Type <span class="required">*</span></label>
                                <select id="eventType" name="EventType" required>
                                    <option value="">Select Type</option>
                                    <?php foreach ($eventTypes as $type => $config): ?>
                                        <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="eventDescription">Event Description <span class="required">*</span></label>
                                <textarea id="eventDescription" name="Description" rows="4" required placeholder="Describe the event purpose, activities, and benefits..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Date & Time Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-calendar-alt"></i>
                            Date & Time
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="startDate">Start Date <span class="required">*</span></label>
                                <input type="date" id="startDate" name="StartDate" required>
                            </div>
                            <div class="form-group-modal">
                                <label for="endDate">End Date <span class="required">*</span></label>
                                <input type="date" id="endDate" name="EndDate" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="startTime">Start Time <span class="required">*</span></label>
                                <input type="time" id="startTime" name="StartTime" required>
                            </div>
                            <div class="form-group-modal">
                                <label for="endTime">End Time <span class="required">*</span></label>
                                <input type="time" id="endTime" name="EndTime" required>
                            </div>
                        </div>
                    </div>

                    <!-- Venue & Capacity Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-map-marker-alt"></i>
                            Venue & Capacity
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="venue">Venue <span class="required">*</span></label>
                                <input type="text" id="venue" name="Venue" required placeholder="e.g., Library Main Hall, Auditorium">
                            </div>
                            <div class="form-group-modal">
                                <label for="capacity">Maximum Capacity</label>
                                <input type="number" id="capacity" name="Capacity" min="1" max="500" placeholder="Number of participants">
                            </div>
                        </div>
                    </div>

                    <!-- Organization Details Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-users"></i>
                            Organization Details
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="organizedBy">Organized By <span class="required">*</span></label>
                                <input type="text" id="organizedBy" name="OrganizedBy" required placeholder="Department/Organization name">
                            </div>
                            <div class="form-group-modal">
                                <label for="contactPerson">Contact Person <span class="required">*</span></label>
                                <input type="text" id="contactPerson" name="ContactPerson" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="contactEmail">Contact Email <span class="required">*</span></label>
                                <input type="email" id="contactEmail" name="ContactEmail" required>
                            </div>
                            <div class="form-group-modal">
                                <label for="contactPhone">Contact Phone <span class="required">*</span></label>
                                <input type="tel" id="contactPhone" name="ContactPhone" required>
                            </div>
                        </div>
                    </div>

                    <!-- Registration Settings Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-clipboard-check"></i>
                            Registration Settings
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label>
                                    <input type="checkbox" id="registrationRequired" name="RegistrationRequired" value="1">
                                    Registration Required
                                </label>
                            </div>
                            <div class="form-group-modal">
                                <label for="registrationDeadline">Registration Deadline</label>
                                <input type="date" id="registrationDeadline" name="RegistrationDeadline">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addEventModal')">Cancel</button>
                <button type="button" class="btn btn-success" onclick="saveEvent()">
                    <i class="fas fa-save"></i>
                    Create Event
                </button>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        const sampleEvents = <?php echo json_encode($sampleEvents); ?>;
        const eventTypes = <?php echo json_encode($eventTypes); ?>;
        const eventRegistrations = <?php echo json_encode($eventRegistrations); ?>;

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
                case 'events-list':
                    loadEventsList();
                    break;
                case 'calendar-view':
                    loadCalendarView();
                    break;
                case 'registrations':
                    loadRegistrationsContent();
                    break;
                case 'analytics':
                    loadAnalyticsContent();
                    break;
            }
        }

        function loadEventsList(searchParams = {}) {
            let filteredEvents = sampleEvents;

            // Apply search filters
            if (searchParams.title) {
                filteredEvents = filteredEvents.filter(event =>
                    event.EventTitle.toLowerCase().includes(searchParams.title.toLowerCase())
                );
            }
            if (searchParams.type) {
                filteredEvents = filteredEvents.filter(event =>
                    event.EventType === searchParams.type
                );
            }
            if (searchParams.status) {
                filteredEvents = filteredEvents.filter(event =>
                    event.Status === searchParams.status
                );
            }

            let eventsHTML = '';

            if (filteredEvents.length === 0) {
                eventsHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #6c757d; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <i class="fas fa-search" style="font-size: 48px; margin-bottom: 15px;"></i>
                        <h3>No Events Found</h3>
                        <p>No events match your search criteria.</p>
                    </div>
                `;
            } else {
                filteredEvents.forEach(event => {
                    const typeConfig = eventTypes[event.EventType] || {
                        icon: 'fas fa-calendar',
                        color: '#6c757d'
                    };
                    const statusClass = `status-${event.Status.toLowerCase()}`;

                    eventsHTML += `
                        <div class="event-card">
                            <div class="event-image">
                                <i class="${typeConfig.icon}"></i>
                                <div class="event-status-badge ${statusClass}">${event.Status}</div>
                            </div>
                            <div class="event-content">
                                <div class="event-header">
                                    <h4 class="event-title">${event.EventTitle}</h4>
                                    <span class="event-type" style="background-color: ${typeConfig.color}; color: white;">
                                        <i class="${typeConfig.icon}"></i>
                                        ${event.EventType}
                                    </span>
                                </div>
                                
                                <div class="event-details">
                                    <div class="event-detail">
                                        <i class="fas fa-calendar"></i>
                                        <span>${new Date(event.StartDate).toLocaleDateString('en-IN')} 
                                        ${event.StartDate !== event.EndDate ? ' - ' + new Date(event.EndDate).toLocaleDateString('en-IN') : ''}</span>
                                    </div>
                                    <div class="event-detail">
                                        <i class="fas fa-clock"></i>
                                        <span>${event.StartTime} - ${event.EndTime}</span>
                                    </div>
                                    <div class="event-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>${event.Venue}</span>
                                    </div>
                                    <div class="event-detail">
                                        <i class="fas fa-user"></i>
                                        <span>${event.ContactPerson} (${event.OrganizedBy})</span>
                                    </div>
                                </div>
                                
                                <div class="event-description">
                                    ${event.Description}
                                </div>
                                
                                <div class="event-footer">
                                    <div class="registration-info">
                                        ${event.RegistrationRequired ? 
                                            `<strong>${event.Registered}/${event.Capacity}</strong> registered` : 
                                            'No registration required'
                                        }
                                    </div>
                                    <div class="event-actions">
                                        <button class="btn-info" onclick="viewEvent(${event.EventID})">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <button class="btn-warning" onclick="editEvent(${event.EventID})">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        ${event.Status === 'Upcoming' ? 
                                            `<button class="btn-danger" onclick="cancelEvent(${event.EventID})">
                                                <i class="fas fa-times"></i> Cancel
                                            </button>` : ''
                                        }
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }

            document.getElementById('eventsContainer').innerHTML = eventsHTML;
        }

        function loadCalendarView() {
            // Placeholder for calendar implementation
            console.log('Loading calendar view...');
        }

        function loadRegistrationsContent() {
            let registrationsHTML = `
                <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="color: #263c79; margin: 0;">Event Registrations Management</h3>
                    <button class="btn btn-primary" onclick="exportRegistrations()">
                        <i class="fas fa-download"></i>
                        Export Registrations
                    </button>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px;">
                    <div style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <h4 style="color: #263c79; margin-bottom: 15px;">Registration Statistics</h4>
                        <div style="margin-bottom: 10px;">
                            <span style="font-weight: 600;">Total Registrations:</span>
                            <span style="color: #263c79; font-weight: 600;">${eventRegistrations.length}</span>
                        </div>
                        <div style="margin-bottom: 10px;">
                            <span style="font-weight: 600;">Confirmed:</span>
                            <span style="color: #28a745; font-weight: 600;">${eventRegistrations.filter(r => r.Status === 'Confirmed').length}</span>
                        </div>
                        <div style="margin-bottom: 10px;">
                            <span style="font-weight: 600;">Present:</span>
                            <span style="color: #17a2b8; font-weight: 600;">${eventRegistrations.filter(r => r.AttendanceStatus === 'Present').length}</span>
                        </div>
                    </div>
                </div>

                <table class="registrations-table">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Member Name</th>
                            <th>Member No</th>
                            <th>Contact</th>
                            <th>Registration Date</th>
                            <th>Status</th>
                            <th>Attendance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            eventRegistrations.forEach(registration => {
                const event = sampleEvents.find(e => e.EventID === registration.EventID);
                registrationsHTML += `
                    <tr>
                        <td><strong>${event ? event.EventTitle : 'Unknown Event'}</strong></td>
                        <td>${registration.MemberName}</td>
                        <td>${registration.MemberNo}</td>
                        <td>
                            <div>${registration.Email}</div>
                            <div>${registration.Phone}</div>
                        </td>
                        <td>${new Date(registration.RegistrationDate).toLocaleDateString('en-IN')}</td>
                        <td>
                            <span class="status-badge ${registration.Status === 'Confirmed' ? 'status-active' : 'status-pending'}">${registration.Status}</span>
                        </td>
                        <td>
                            <span class="status-badge ${registration.AttendanceStatus === 'Present' ? 'status-active' : 'status-inactive'}">${registration.AttendanceStatus || 'Not Marked'}</span>
                        </td>
                        <td>
                            <button class="btn-info" onclick="markAttendance(${registration.RegistrationID})" style="padding: 4px 8px; font-size: 12px;">
                                <i class="fas fa-check"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            registrationsHTML += `
                    </tbody>
                </table>
            `;

            document.getElementById('registrationsContent').innerHTML = registrationsHTML;
        }

        function loadAnalyticsContent() {
            const analyticsHTML = `
                <div style="margin-bottom: 20px;">
                    <h3 style="color: #263c79; margin-bottom: 15px;">Events Analytics & Reports</h3>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                    <div onclick="generateReport('events-summary')" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-chart-line" style="font-size: 24px; color: #263c79; margin-bottom: 10px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 5px;">Events Summary</h4>
                        <p style="color: #6c757d; font-size: 14px;">Complete overview of all events with statistics</p>
                    </div>
                    
                    <div onclick="generateReport('attendance-analysis')" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-users" style="font-size: 24px; color: #28a745; margin-bottom: 10px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 5px;">Attendance Analysis</h4>
                        <p style="color: #6c757d; font-size: 14px;">Detailed attendance patterns and trends</p>
                    </div>
                    
                    <div onclick="generateReport('popular-events')" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-star" style="font-size: 24px; color: #ffc107; margin-bottom: 10px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 5px;">Popular Events</h4>
                        <p style="color: #6c757d; font-size: 14px;">Most attended and successful events</p>
                    </div>
                    
                    <div onclick="generateReport('feedback-analysis')" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-comments" style="font-size: 24px; color: #17a2b8; margin-bottom: 10px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 5px;">Feedback Analysis</h4>
                        <p style="color: #6c757d; font-size: 14px;">Participant feedback and satisfaction ratings</p>
                    </div>
                </div>
            `;

            document.getElementById('analyticsContent').innerHTML = analyticsHTML;
        }

        function searchEvents() {
            const searchParams = {
                title: document.getElementById('searchTitle').value.trim(),
                type: document.getElementById('searchType').value,
                status: document.getElementById('searchStatus').value,
                date: document.getElementById('searchDate').value
            };

            loadEventsList(searchParams);
        }

        // Modal functions
        function openAddEventModal() {
            document.getElementById('addEventModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';

            if (modalId === 'addEventModal') {
                document.getElementById('addEventForm').reset();
            }
        }

        function saveEvent() {
            const formData = new FormData(document.getElementById('addEventForm'));
            const eventData = Object.fromEntries(formData);

            // Generate new event ID
            const newEventID = Math.max(...sampleEvents.map(e => e.EventID)) + 1;

            console.log('Creating new event:', {
                ...eventData,
                EventID: newEventID
            });

            alert(`Event created successfully!\nEvent ID: ${newEventID}\nNotifications will be sent to registered members.`);
            closeModal('addEventModal');
            loadEventsList();
        }

        function saveEventInline() {
            const formData = new FormData(document.getElementById('addEventInlineForm'));
            const eventData = Object.fromEntries(formData);

            // Generate new event ID
            const newEventID = Math.max(...sampleEvents.map(e => e.EventID)) + 1;

            console.log('Creating new event:', {
                ...eventData,
                EventID: newEventID
            });

            alert(`Event created successfully!\nEvent ID: ${newEventID}\nNotifications will be sent to registered members.`);
            document.getElementById('addEventInlineForm').reset();
            loadEventsList();
        }

        // Event actions
        function viewEvent(eventId) {
            console.log('Viewing event:', eventId);
            alert(`Opening detailed view for Event ID: ${eventId}`);
        }

        function editEvent(eventId) {
            console.log('Editing event:', eventId);
            alert(`Opening edit form for Event ID: ${eventId}`);
        }

        function cancelEvent(eventId) {
            if (confirm(`Are you sure you want to cancel Event ID: ${eventId}?`)) {
                console.log('Cancelling event:', eventId);
                alert('Event cancelled successfully! Cancellation notifications will be sent.');
                loadEventsList();
            }
        }

        function markAttendance(registrationId) {
            console.log('Marking attendance for registration:', registrationId);
            alert(`Attendance marked for Registration ID: ${registrationId}`);
        }

        // Other functions
        function exportEvents() {
            console.log('Exporting events calendar...');
            alert('Exporting events calendar to PDF/Excel...');
        }

        function manageNotifications() {
            console.log('Opening notification management...');
            alert('Opening notification management interface...');
        }

        function exportRegistrations() {
            console.log('Exporting registrations...');
            alert('Exporting registration data to CSV...');
        }

        function generateReport(reportType) {
            console.log('Generating report:', reportType);
            alert(`Generating ${reportType} report...`);
        }

        function previousMonth() {
            console.log('Loading previous month...');
        }

        function nextMonth() {
            console.log('Loading next month...');
        }

        // Load statistics
        function loadStatistics() {
            const totalEvents = sampleEvents.length;
            const upcomingEvents = sampleEvents.filter(e => e.Status === 'Upcoming').length;
            const activeEvents = sampleEvents.filter(e => e.Status === 'Active').length;
            const completedEvents = sampleEvents.filter(e => e.Status === 'Completed').length;
            const totalRegistrations = eventRegistrations.length;
            const averageAttendance = 85; // Mock calculation

            document.getElementById('totalEvents').textContent = totalEvents;
            document.getElementById('upcomingEvents').textContent = upcomingEvents;
            document.getElementById('activeEvents').textContent = activeEvents;
            document.getElementById('completedEvents').textContent = completedEvents;
            document.getElementById('totalRegistrations').textContent = totalRegistrations;
            document.getElementById('averageAttendance').textContent = averageAttendance + '%';
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
            loadEventsList();
        });
    </script>
</body>

</html>