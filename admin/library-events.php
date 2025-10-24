        <?php
        // ...existing PHP code...
        ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <!-- ...existing code... -->
        </head>
        <body>
            <!-- ...existing code... -->
            <script>
            function markAttendance(registrationId) {
                if (confirm('Mark this member as present?')) {
                    fetch('api/event_registrations.php?action=mark_attendance', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `RegistrationID=${registrationId}`
                    })
                    .then(res => res.json())
                    .then(result => {
                        if (result.success) {
                            alert('Attendance marked!');
                            loadStatistics();
                            document.querySelector('.registrations-table tbody').innerHTML = '';
                            location.reload();
                        } else {
                            alert('Error: ' + result.message);
                        }
                    });
                }
            }

            function deleteRegistration(registrationId) {
                if (confirm('Delete this registration?')) {
                    fetch('api/event_registrations.php?action=delete', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `RegistrationID=${registrationId}`
                    })
                    .then(res => res.json())
                    .then(result => {
                        if (result.success) {
                            alert('Registration deleted!');
                            loadStatistics();
                            document.querySelector('.registrations-table tbody').innerHTML = '';
                            location.reload();
                        } else {
                            alert('Error: ' + result.message);
                        }
                    });
                }
            }
            </script>
        </body>
        </html>
<?php
session_start();
require_once '../includes/db_connect.php';

$admin_id = $_SESSION['admin_id'] ?? 1;
$admin_name = $_SESSION['admin_name'] ?? 'Admin User';

// Fetch events from database
$events = [];
$result = $pdo->query('SELECT * FROM library_events ORDER BY StartDate DESC');
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $events[] = $row;
}

// Fetch registrations from database
$registrations = [];
$regResult = $pdo->query('SELECT r.*, e.EventTitle FROM event_registrations r JOIN library_events e ON r.EventID = e.EventID ORDER BY r.RegistrationDate DESC');
while ($row = $regResult->fetch(PDO::FETCH_ASSOC)) {
    $registrations[] = $row;
}

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
            <form id="addEventInlineForm" onsubmit="saveEventInline(); return false;">
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
                        <button type="button" class="btn btn-success" onclick="saveEventInline();">
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
                <form id="addEventForm" onsubmit="saveEvent(); return false;">
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
    const eventTypes = <?php echo json_encode($eventTypes); ?>;

        // Tab functionality
        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            document.getElementById(tabName).classList.add('active');
            // Fix: get the button that triggered the tab switch
            // If called from onclick, 'this' is the button
            if (typeof event !== 'undefined' && event.target) {
                event.target.classList.add('active');
            } else {
                // fallback: activate first matching tab-btn
                const btns = document.querySelectorAll('.tab-btn');
                btns.forEach(btn => {
                    if (btn.getAttribute('onclick') && btn.getAttribute('onclick').includes(tabName)) {
                        btn.classList.add('active');
                    }
                });
            }
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
            fetch('api/events.php?action=list')
                .then(res => res.json())
                .then(result => {
                    let filteredEvents = Array.isArray(result.data) ? result.data : [];
                    let eventsHTML = '';
                    // Apply search filters
                    if (searchParams.title) {
                        filteredEvents = filteredEvents.filter(event =>
                            event.EventTitle && event.EventTitle.toLowerCase().includes(searchParams.title.toLowerCase())
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
                });
        }

        function loadCalendarView() {
            // Interactive calendar implementation
            const calendarContainer = document.getElementById('calendarContainer');
            calendarContainer.innerHTML = '<div style="text-align:center; padding:40px; color:#6c757d;"><i class="fas fa-spinner fa-spin" style="font-size:32px;"></i> Loading calendar...</div>';
            // Get current month/year from state
            if (!window._calendarState) {
                const today = new Date();
                window._calendarState = { month: today.getMonth(), year: today.getFullYear() };
            }
            let month = window._calendarState.month;
            let year = window._calendarState.year;
            // Fetch events
            fetch('api/events.php?action=list')
                .then(res => res.json())
                .then(result => {
                    const events = Array.isArray(result.data) ? result.data : [];
                    // Build calendar grid
                    const firstDay = new Date(year, month, 1).getDay();
                    const daysInMonth = new Date(year, month + 1, 0).getDate();
                    const monthName = new Date(year, month, 1).toLocaleString('default', { month: 'long' });
                    let calendarHTML = `<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                        <button class="btn btn-secondary" onclick="previousMonth()"><i class="fas fa-chevron-left"></i> Previous</button>
                        <h3 style="color:#263c79; margin:0;">${monthName} ${year}</h3>
                        <button class="btn btn-primary" onclick="nextMonth()">Next <i class="fas fa-chevron-right"></i></button>
                    </div>`;
                    calendarHTML += '<table class="calendar-table" style="width:100%; border-collapse:collapse; background:white; border-radius:8px; box-shadow:0 2px 4px rgba(0,0,0,0.08);">';
                    calendarHTML += '<thead><tr>';
                    ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'].forEach(d => {
                        calendarHTML += `<th style="padding:8px; background:#f8f9fa; color:#263c79;">${d}</th>`;
                    });
                    calendarHTML += '</tr></thead><tbody><tr>';
                    let day = 1;
                    for (let i = 0; i < 42; i++) {
                        if (i < firstDay || day > daysInMonth) {
                            calendarHTML += '<td style="padding:16px; background:#f8f9fa;"></td>';
                        } else {
                            // Events for this day
                            const dateStr = `${year}-${String(month+1).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
                            const dayEvents = events.filter(e => e.StartDate <= dateStr && e.EndDate >= dateStr);
                            calendarHTML += `<td style="vertical-align:top; padding:8px; border:1px solid #e9ecef; cursor:pointer;" onclick="showDayEvents('${dateStr}')">
                                <div style="font-weight:600; color:#263c79;">${day}</div>
                                ${dayEvents.length > 0 ? dayEvents.map(ev => `<div style='margin:4px 0; background:#e3f2fd; border-radius:4px; padding:2px 6px; font-size:12px; color:#1976d2;'>${ev.EventTitle}</div>`).join('') : '<div style="color:#adb5bd; font-size:12px;">No events</div>'}
                            </td>`;
                            day++;
                        }
                        if ((i+1)%7 === 0 && i < 41) calendarHTML += '</tr><tr>';
                    }
                    calendarHTML += '</tr></tbody></table>';
                    calendarHTML += '<div id="dayEventsModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.3); z-index:9999; align-items:center; justify-content:center;"><div id="dayEventsContent" style="background:white; border-radius:8px; padding:32px; max-width:400px; margin:auto; position:relative;"></div></div>';
                    calendarContainer.innerHTML = calendarHTML;
                });

            // Helper to show events for a day
            window.showDayEvents = function(dateStr) {
                fetch('api/events.php?action=list')
                    .then(res => res.json())
                    .then(result => {
                        const events = Array.isArray(result.data) ? result.data : [];
                        const dayEvents = events.filter(e => e.StartDate <= dateStr && e.EndDate >= dateStr);
                        let html = `<h4 style='color:#263c79; margin-bottom:12px;'>Events on ${new Date(dateStr).toLocaleDateString('en-IN')}</h4>`;
                        if (dayEvents.length === 0) {
                            html += '<div style="color:#adb5bd;">No events scheduled.</div>';
                        } else {
                            dayEvents.forEach(ev => {
                                html += `<div style='margin-bottom:16px; padding:12px; background:#f8f9fa; border-radius:6px;'>
                                    <strong>${ev.EventTitle}</strong><br>
                                    <span style='font-size:12px;'>${ev.StartTime} - ${ev.EndTime}</span><br>
                                    <span style='font-size:12px;'>${ev.Venue}</span><br>
                                    <button class='btn btn-info' style='margin-top:8px;' onclick='viewEvent(${ev.EventID})'><i class='fas fa-eye'></i> View</button>
                                    ${ev.RegistrationRequired ? `<button class='btn btn-success' style='margin-top:8px; margin-left:8px;' onclick='registerForEvent(${ev.EventID})'><i class='fas fa-user-plus'></i> Register</button>` : ''}
                                </div>`;
                            });
                        }
                        html += `<button class='btn btn-secondary' style='margin-top:12px;' onclick='closeDayEventsModal()'>Close</button>`;
                        document.getElementById('dayEventsContent').innerHTML = html;
                        document.getElementById('dayEventsModal').style.display = 'flex';
                    });
            };
            window.closeDayEventsModal = function() {
                document.getElementById('dayEventsModal').style.display = 'none';
            };
            // Register for event (admin quick action)
            window.registerForEvent = function(eventId) {
                alert('Registration for event ID ' + eventId + ' (admin quick action). Implement registration logic as needed.');
            };
            // Month navigation
            window.previousMonth = function() {
                window._calendarState.month--;
                if (window._calendarState.month < 0) {
                    window._calendarState.month = 11;
                    window._calendarState.year--;
                }
                loadCalendarView();
            };
            window.nextMonth = function() {
                window._calendarState.month++;
                if (window._calendarState.month > 11) {
                    window._calendarState.month = 0;
                    window._calendarState.year++;
                }
                loadCalendarView();
            };
        }

        function loadRegistrationsContent() {
            let registrationsHTML = `
                <div style="margin-bottom: 20px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px;">
                    <h3 style="color: #263c79; margin: 0; flex:1;">Event Registrations Management</h3>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
                        <input type="text" id="regSearchInput" class="form-control" placeholder="Search by member/event..." style="width:200px;">
                        <select id="regStatusFilter" class="form-control" style="width:140px;">
                            <option value="">All Status</option>
                            <option value="Confirmed">Confirmed</option>
                            <option value="Pending">Pending</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                        <select id="regTypeFilter" class="form-control" style="width:140px;"><option value="">All Types</option></select>
                        <input type="date" id="regStartDate" class="form-control" style="width:140px;">
                        <input type="date" id="regEndDate" class="form-control" style="width:140px;">
                        <button class="btn btn-info" id="downloadRegPDF" title="Download PDF report"><i class="fas fa-file-pdf"></i> PDF Report</button>
                        <button class="btn btn-primary" onclick="exportRegistrationsCSV()" title="Export registrations as CSV">
                            <i class="fas fa-download"></i>
                            Export CSV
                        </button>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px;">
                    <div style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <h4 style="color: #263c79; margin-bottom: 15px;">Registration Statistics</h4>
                        <div style="margin-bottom: 10px;"><span style="font-weight: 600;">Total Registrations:</span> <span style="color: #263c79; font-weight: 600;" id="totalRegCount">-</span></div>
                        <div style="margin-bottom: 10px;"><span style="font-weight: 600;">Confirmed:</span> <span style="color: #28a745; font-weight: 600;" id="confirmedRegCount">-</span></div>
                        <div style="margin-bottom: 10px;"><span style="font-weight: 600;">Present:</span> <span style="color: #17a2b8; font-weight: 600;" id="presentRegCount">-</span></div>
                    </div>
                    <div style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <h4 style="color: #263c79; margin-bottom: 15px;">Attendance Summary</h4>
                        <canvas id="attendanceChart" width="320" height="120"></canvas>
                        <canvas id="registrationTrendChart" width="320" height="120" style="margin-top:18px;"></canvas>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:30px;">
                    <div style="background:white; border:1px solid #e9ecef; border-radius:8px; padding:20px; box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                        <h4 style="color:#263c79; margin-bottom:12px;">Top Members by Attendance</h4>
                        <ul id="topMembersList" style="list-style:none; padding:0; margin:0;"></ul>
                    </div>
                    <div style="background:white; border:1px solid #e9ecef; border-radius:8px; padding:20px; box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                        <h4 style="color:#263c79; margin-bottom:12px;">Interactive Attendance Chart</h4>
                        <canvas id="interactiveAttendanceChart" width="320" height="120"></canvas>
                    </div>
                </div>

                <div style="overflow-x:auto;">
                <table class="registrations-table" style="min-width:900px; border-collapse:collapse;">
                    <thead style="position:sticky; top:0; background:#f8f9fa; z-index:2;">
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
                    <tbody id="registrationsTableBody"></tbody>
                </table>
                </div>
            `;

            // Fetch live registrations and events
            document.getElementById('registrationsContent').innerHTML = registrationsHTML;
            Promise.all([
                fetch('api/event_registrations.php?action=list').then(res => res.json()),
                fetch('api/events.php?action=list').then(res => res.json())
            ]).then(([regRes, eventsRes]) => {
                let registrations = Array.isArray(regRes.data) ? regRes.data : [];
                const events = Array.isArray(eventsRes.data) ? eventsRes.data : [];
                // Fill event type filter
                const typeSet = new Set(events.map(e => e.EventType));
                const typeFilter = document.getElementById('regTypeFilter');
                typeSet.forEach(type => {
                    if (type) {
                        const opt = document.createElement('option');
                        opt.value = type;
                        opt.textContent = type;
                        typeFilter.appendChild(opt);
                    }
                });
                // Statistics
                document.getElementById('totalRegCount').textContent = registrations.length;
                document.getElementById('confirmedRegCount').textContent = registrations.filter(r => r.Status === 'Confirmed').length;
                document.getElementById('presentRegCount').textContent = registrations.filter(r => r.AttendanceStatus === 'Present').length;
                // Advanced filter logic
                function renderRows(filter = '', status = '', type = '', start = '', end = '') {
                    let rowsHTML = '';
                    let filteredRegs = registrations;
                    if (filter) {
                        const f = filter.toLowerCase();
                        filteredRegs = filteredRegs.filter(r => {
                            const event = events.find(e => e.EventID == r.EventID);
                            return (r.MemberName && r.MemberName.toLowerCase().includes(f)) ||
                                   (r.MemberNo && r.MemberNo.toLowerCase().includes(f)) ||
                                   (event && event.EventTitle && event.EventTitle.toLowerCase().includes(f));
                        });
                    }
                    if (status) filteredRegs = filteredRegs.filter(r => r.Status === status);
                    if (type) filteredRegs = filteredRegs.filter(r => {
                        const event = events.find(e => e.EventID == r.EventID);
                        return event && event.EventType === type;
                    });
                    if (start) filteredRegs = filteredRegs.filter(r => r.RegistrationDate >= start);
                    if (end) filteredRegs = filteredRegs.filter(r => r.RegistrationDate <= end);
                    filteredRegs.forEach((registration, idx) => {
                        const event = events.find(e => e.EventID == registration.EventID);
                        let statusColor = '#adb5bd', statusText = registration.Status;
                        if (registration.Status === 'Confirmed') { statusColor = '#28a745'; }
                        else if (registration.Status === 'Pending') { statusColor = '#ffc107'; }
                        else if (registration.Status === 'Cancelled') { statusColor = '#dc3545'; }
                        let attColor = '#adb5bd', attText = registration.AttendanceStatus || 'Not Marked';
                        if (registration.AttendanceStatus === 'Present') { attColor = '#17a2b8'; attText = 'Present'; }
                        else if (registration.AttendanceStatus === 'Absent') { attColor = '#dc3545'; attText = 'Absent'; }
                        const rowBg = idx % 2 === 0 ? 'background:#f8f9fa;' : '';
                        rowsHTML += `
                            <tr style='${rowBg}'>
                                <td><strong>${event ? event.EventTitle : 'Unknown Event'}</strong></td>
                                <td>${registration.MemberName}</td>
                                <td>${registration.MemberNo}</td>
                                <td><div>${registration.Email}</div><div>${registration.Phone}</div></td>
                                <td>${new Date(registration.RegistrationDate).toLocaleDateString('en-IN')}</td>
                                <td><span style='display:inline-block; min-width:80px; padding:4px 10px; border-radius:12px; color:white; background:${statusColor}; font-size:13px;'>${statusText}</span></td>
                                <td><span style='display:inline-block; min-width:80px; padding:4px 10px; border-radius:12px; color:white; background:${attColor}; font-size:13px;'>${attText}</span></td>
                                <td>
                                    <button class="btn-info" onclick="markAttendance(${registration.RegistrationID})" style="padding: 4px 8px; font-size: 12px;" title="Mark attendance"><i class="fas fa-check"></i></button>
                                    <button class="btn-danger" onclick="deleteRegistration(${registration.RegistrationID})" style="padding: 4px 8px; font-size: 12px; margin-left: 4px;" title="Delete registration"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `;
                    });
                    document.getElementById('registrationsTableBody').innerHTML = rowsHTML;
                }
                // Initial render
                renderRows();
                // Filter listeners
                document.getElementById('regSearchInput').addEventListener('input', function() {
                    renderRows(this.value, document.getElementById('regStatusFilter').value, typeFilter.value, document.getElementById('regStartDate').value, document.getElementById('regEndDate').value);
                });
                document.getElementById('regStatusFilter').addEventListener('change', function() {
                    renderRows(document.getElementById('regSearchInput').value, this.value, typeFilter.value, document.getElementById('regStartDate').value, document.getElementById('regEndDate').value);
                });
                typeFilter.addEventListener('change', function() {
                    renderRows(document.getElementById('regSearchInput').value, document.getElementById('regStatusFilter').value, this.value, document.getElementById('regStartDate').value, document.getElementById('regEndDate').value);
                });
                document.getElementById('regStartDate').addEventListener('change', function() {
                    renderRows(document.getElementById('regSearchInput').value, document.getElementById('regStatusFilter').value, typeFilter.value, this.value, document.getElementById('regEndDate').value);
                });
                document.getElementById('regEndDate').addEventListener('change', function() {
                    renderRows(document.getElementById('regSearchInput').value, document.getElementById('regStatusFilter').value, typeFilter.value, document.getElementById('regStartDate').value, this.value);
                });
                // Registration trend chart
                setTimeout(() => {
                    const ctx = document.getElementById('registrationTrendChart').getContext('2d');
                    ctx.clearRect(0,0,320,120);
                    // Group by date
                    const dateCounts = {};
                    registrations.forEach(r => {
                        const d = r.RegistrationDate.substr(0,10);
                        dateCounts[d] = (dateCounts[d]||0)+1;
                    });
                    const dates = Object.keys(dateCounts).sort();
                    const maxCount = Math.max(...Object.values(dateCounts),1);
                    dates.forEach((d,i) => {
                        ctx.fillStyle = '#17a2b8';
                        ctx.fillRect(20+i*22, 110-(dateCounts[d]/maxCount)*80, 16, (dateCounts[d]/maxCount)*80);
                        ctx.fillStyle = '#263c79';
                        ctx.font = '11px Arial';
                        ctx.fillText(d, 20+i*22, 118);
                        ctx.fillText(dateCounts[d], 20+i*22, 110-(dateCounts[d]/maxCount)*80-6);
                    });
                }, 300);
                // Top members by attendance
                setTimeout(() => {
                    const memberCounts = {};
                    registrations.forEach(r => {
                        if (r.AttendanceStatus === 'Present') {
                            memberCounts[r.MemberName] = (memberCounts[r.MemberName]||0)+1;
                        }
                    });
                    const topMembers = Object.entries(memberCounts).sort((a,b)=>b[1]-a[1]).slice(0,5);
                    const ul = document.getElementById('topMembersList');
                    ul.innerHTML = topMembers.length === 0 ? '<li style="color:#adb5bd;">No data</li>' : topMembers.map(([name,count]) => `<li style="margin-bottom:8px; font-weight:600; color:#263c79;"><i class='fas fa-user-check' style='color:#28a745;'></i> ${name} <span style='background:#e3f2fd; color:#1976d2; border-radius:8px; padding:2px 10px; font-size:13px;'>${count} attended</span></li>`).join('');
                }, 300);
                // Interactive attendance chart
                setTimeout(() => {
                    const ctx = document.getElementById('interactiveAttendanceChart').getContext('2d');
                    ctx.clearRect(0,0,320,120);
                    const present = registrations.filter(r => r.AttendanceStatus === 'Present').length;
                    const absent = registrations.filter(r => r.AttendanceStatus === 'Absent').length;
                    const notMarked = registrations.length - present - absent;
                    const total = registrations.length || 1;
                    // Pie chart
                    let startAngle = 0;
                    const data = [present, absent, notMarked];
                    const colors = ['#17a2b8','#dc3545','#adb5bd'];
                    data.forEach((val,i) => {
                        const slice = val/total * 2*Math.PI;
                        ctx.beginPath();
                        ctx.moveTo(60,60);
                        ctx.arc(60,60,50,startAngle,startAngle+slice);
                        ctx.closePath();
                        ctx.fillStyle = colors[i];
                        ctx.fill();
                        startAngle += slice;
                    });
                    // Legend
                    ['Present','Absent','Not Marked'].forEach((label,i) => {
                        ctx.fillStyle = colors[i];
                        ctx.fillRect(140,30+i*22,14,14);
                        ctx.fillStyle = '#263c79';
                        ctx.font = '13px Arial';
                        ctx.fillText(label+` (${data[i]})`, 160, 42+i*22);
                    });
                }, 300);
                // PDF report
                document.getElementById('downloadRegPDF').onclick = function() {
                    const pdfContent = document.querySelector('.registrations-table').outerHTML;
                    const blob = new Blob([pdfContent], { type: 'application/pdf' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'registrations_report.pdf';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                };
                // CSV export
                window.exportRegistrationsCSV = function() {
                    let csv = 'Event,Member Name,Member No,Email,Phone,Registration Date,Status,Attendance\n';
                    registrations.forEach(registration => {
                        const event = events.find(e => e.EventID == registration.EventID);
                        csv += `"${event ? event.EventTitle : 'Unknown Event'}","${registration.MemberName}","${registration.MemberNo}","${registration.Email}","${registration.Phone}","${new Date(registration.RegistrationDate).toLocaleDateString('en-IN')}","${registration.Status}","${registration.AttendanceStatus || 'Not Marked'}"\n`;
                    });
                    const blob = new Blob([csv], { type: 'text/csv' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'event_registrations.csv';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                };
            });
        }

        function loadAnalyticsContent() {
            document.getElementById('analyticsContent').innerHTML = `
                <div style="margin-bottom: 20px; display: flex; flex-wrap: wrap; align-items: center; gap: 18px; justify-content: space-between;">
                    <h3 style="color: #263c79; margin-bottom: 0;">Events Analytics & Reports</h3>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <input type="date" id="analyticsStartDate" class="form-control" style="width:140px;">
                        <input type="date" id="analyticsEndDate" class="form-control" style="width:140px;">
                        <select id="analyticsTypeFilter" class="form-control" style="width:160px;">
                            <option value="">All Types</option>
                        </select>
                        <button class="btn btn-info" id="downloadAnalyticsPDF" style="margin-left:8px;">
                            <i class="fas fa-file-pdf"></i> Download PDF Report
                        </button>
                    </div>
                </div>
                <div id="analyticsDashboard" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(370px, 1fr)); gap: 28px;"></div>
                <div style="margin-top:32px; display:grid; grid-template-columns: 1fr 1fr; gap:32px;">
                    <div style="background:white; border:1px solid #e9ecef; border-radius:12px; padding:24px; box-shadow:0 2px 8px rgba(38,60,121,0.08);">
                        <h4 style="color:#263c79; margin-bottom:18px; font-size:1.1rem;"><i class="fas fa-chart-pie" style="color:#17a2b8;"></i> Event Type Breakdown</h4>
                        <canvas id="eventTypePieChart" width="320" height="220"></canvas>
                    </div>
                    <div style="background:white; border:1px solid #e9ecef; border-radius:12px; padding:24px; box-shadow:0 2px 8px rgba(38,60,121,0.08);">
                        <h4 style="color:#263c79; margin-bottom:18px; font-size:1.1rem;"><i class="fas fa-balance-scale" style="color:#ffc107;"></i> Compare Two Events</h4>
                        <div style="display:flex; gap:12px; align-items:center; margin-bottom:12px;">
                            <select id="compareEvent1" class="form-control" style="width:180px;"></select>
                            <span style="font-weight:600; color:#263c79;">vs</span>
                            <select id="compareEvent2" class="form-control" style="width:180px;"></select>
                        </div>
                        <canvas id="eventCompareChart" width="320" height="180"></canvas>
                    </div>
                </div>
                <div style="margin-top:32px; background:white; border:1px solid #e9ecef; border-radius:12px; padding:24px; box-shadow:0 2px 8px rgba(38,60,121,0.08);">
                    <h4 style="color:#263c79; margin-bottom:18px; font-size:1.1rem;"><i class="fas fa-th" style="color:#28a745;"></i> Attendance Rate Heatmap</h4>
                    <canvas id="attendanceHeatmap" width="700" height="220"></canvas>
                </div>
            `;
            Promise.all([
                fetch('api/events.php?action=list').then(res => res.json()),
                fetch('api/event_registrations.php?action=list').then(res => res.json())
            ]).then(([eventsRes, regRes]) => {
                const events = Array.isArray(eventsRes.data) ? eventsRes.data : [];
                const registrations = Array.isArray(regRes.data) ? regRes.data : [];
                // Fill event type filter
                const typeSet = new Set(events.map(e => e.EventType));
                const typeFilter = document.getElementById('analyticsTypeFilter');
                typeSet.forEach(type => {
                    if (type) {
                        const opt = document.createElement('option');
                        opt.value = type;
                        opt.textContent = type;
                        typeFilter.appendChild(opt);
                    }
                });
                // Fill compare event dropdowns
                const compare1 = document.getElementById('compareEvent1');
                const compare2 = document.getElementById('compareEvent2');
                events.forEach(ev => {
                    const opt1 = document.createElement('option');
                    opt1.value = ev.EventID;
                    opt1.textContent = ev.EventTitle;
                    compare1.appendChild(opt1);
                    const opt2 = document.createElement('option');
                    opt2.value = ev.EventID;
                    opt2.textContent = ev.EventTitle;
                    compare2.appendChild(opt2);
                });
                // Filter logic
                function getFilteredEvents() {
                    let filtered = events;
                    const type = typeFilter.value;
                    const start = document.getElementById('analyticsStartDate').value;
                    const end = document.getElementById('analyticsEndDate').value;
                    if (type) filtered = filtered.filter(e => e.EventType === type);
                    if (start) filtered = filtered.filter(e => e.StartDate >= start);
                    if (end) filtered = filtered.filter(e => e.EndDate <= end);
                    return filtered;
                }
                // Event stats
                function getEventStats(evList) {
                    return evList.map(event => {
                        const regs = registrations.filter(r => r.EventID == event.EventID);
                        const present = regs.filter(r => r.AttendanceStatus === 'Present').length;
                        return {
                            EventID: event.EventID,
                            EventTitle: event.EventTitle,
                            EventType: event.EventType,
                            Registered: regs.length,
                            Present: present,
                            Capacity: event.Capacity || '-',
                            AttendancePercent: regs.length > 0 ? Math.round((present / regs.length) * 100) : 0
                        };
                    });
                }
                // Pie chart: event type breakdown
                function renderEventTypePie() {
                    const ctx = document.getElementById('eventTypePieChart').getContext('2d');
                    const filtered = getFilteredEvents();
                    const typeCounts = {};
                    filtered.forEach(e => {
                        typeCounts[e.EventType] = (typeCounts[e.EventType] || 0) + 1;
                    });
                    const labels = Object.keys(typeCounts);
                    const data = Object.values(typeCounts);
                    // Simple pie chart
                    ctx.clearRect(0,0,320,220);
                    let total = data.reduce((a,b) => a+b,0);
                    let startAngle = 0;
                    const colors = ['#17a2b8','#ffc107','#28a745','#1976d2','#dc3545','#6c757d','#e83e8c'];
                    labels.forEach((label, i) => {
                        const slice = data[i]/total * 2*Math.PI;
                        ctx.beginPath();
                        ctx.moveTo(160,110);
                        ctx.arc(160,110,90,startAngle,startAngle+slice);
                        ctx.closePath();
                        ctx.fillStyle = colors[i%colors.length];
                        ctx.fill();
                        startAngle += slice;
                    });
                    // Legend
                    labels.forEach((label,i) => {
                        ctx.fillStyle = colors[i%colors.length];
                        ctx.fillRect(20,200+i*18,14,14);
                        ctx.fillStyle = '#263c79';
                        ctx.font = '13px Arial';
                        ctx.fillText(label+` (${data[i]})`, 40, 212+i*18);
                    });
                }
                // Event comparison chart
                function renderEventCompare() {
                    const ctx = document.getElementById('eventCompareChart').getContext('2d');
                    ctx.clearRect(0,0,320,180);
                    const id1 = compare1.value, id2 = compare2.value;
                    const stat1 = getEventStats(events.filter(e=>e.EventID==id1))[0];
                    const stat2 = getEventStats(events.filter(e=>e.EventID==id2))[0];
                    if (!stat1 || !stat2) return;
                    // Bar chart
                    const barW = 40, gap = 60, baseY = 150;
                    // Event 1
                    ctx.fillStyle = '#17a2b8';
                    ctx.fillRect(60, baseY-stat1.AttendancePercent, barW, stat1.AttendancePercent);
                    ctx.fillStyle = '#263c79';
                    ctx.fillText(stat1.EventTitle, 60, baseY+16);
                    ctx.fillText(stat1.AttendancePercent+'%', 60, baseY-stat1.AttendancePercent-8);
                    // Event 2
                    ctx.fillStyle = '#ffc107';
                    ctx.fillRect(60+barW+gap, baseY-stat2.AttendancePercent, barW, stat2.AttendancePercent);
                    ctx.fillStyle = '#263c79';
                    ctx.fillText(stat2.EventTitle, 60+barW+gap, baseY+16);
                    ctx.fillText(stat2.AttendancePercent+'%', 60+barW+gap, baseY-stat2.AttendancePercent-8);
                }
                // Attendance heatmap
                function renderAttendanceHeatmap() {
                    const ctx = document.getElementById('attendanceHeatmap').getContext('2d');
                    ctx.clearRect(0,0,700,220);
                    const filtered = getFilteredEvents();
                    const stats = getEventStats(filtered);
                    // X: events, Y: months
                    const months = [...new Set(filtered.map(e=>e.StartDate.substr(0,7)))];
                    stats.forEach((stat,i) => {
                        const monthIdx = months.indexOf(events.find(e=>e.EventID==stat.EventID).StartDate.substr(0,7));
                        const x = 60+i*60, y = 40+monthIdx*60;
                        ctx.fillStyle = `rgba(23,162,184,${stat.AttendancePercent/100})`;
                        ctx.fillRect(x,y,40,40);
                        ctx.fillStyle = '#263c79';
                        ctx.font = '13px Arial';
                        ctx.fillText(stat.AttendancePercent+'%', x+8, y+24);
                        ctx.fillText(stat.EventTitle, x-10, y+58);
                    });
                    // Month labels
                    months.forEach((m,idx) => {
                        ctx.fillStyle = '#263c79';
                        ctx.font = '13px Arial';
                        ctx.fillText(m, 10, 60+idx*60+24);
                    });
                }
                // Interactive charts: re-render on filter change
                typeFilter.onchange = () => { renderEventTypePie(); renderAttendanceHeatmap(); };
                document.getElementById('analyticsStartDate').onchange = () => { renderEventTypePie(); renderAttendanceHeatmap(); };
                document.getElementById('analyticsEndDate').onchange = () => { renderEventTypePie(); renderAttendanceHeatmap(); };
                compare1.onchange = renderEventCompare;
                compare2.onchange = renderEventCompare;
                // Initial render
                renderEventTypePie();
                renderEventCompare();
                renderAttendanceHeatmap();
                // Download PDF report
                document.getElementById('downloadAnalyticsPDF').onclick = function() {
                    const pdfContent = document.getElementById('analyticsDashboard').outerHTML;
                    const blob = new Blob([pdfContent], { type: 'application/pdf' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'analytics_report.pdf';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                };
                // ...existing dashboardHTML code for top events, trends, attendance percentage...
                let dashboardHTML = '';
                // ...existing code...
                document.getElementById('analyticsDashboard').innerHTML = dashboardHTML;
            });
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
            const form = document.getElementById('addEventForm');
            const formData = new FormData(form);
            const eventData = Object.fromEntries(formData);
            eventData.Status = 'Upcoming';
            // Ensure checkbox value is 1 or 0
            eventData.RegistrationRequired = document.getElementById('registrationRequired').checked ? 1 : 0;
            const editId = form.getAttribute('data-edit-id');
            let url = 'api/events.php?action=create';
            if (editId) {
                eventData.EventID = editId;
                url = 'api/events.php?action=edit';
            }
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(eventData)
            })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    alert(editId ? 'Event updated successfully!' : 'Event created successfully!');
                    closeModal('addEventModal');
                    form.removeAttribute('data-edit-id');
                    loadEventsList();
                } else {
                    alert('Error: ' + result.message);
                }
            });
        }

        function saveEventInline() {
            const formData = new FormData(document.getElementById('addEventInlineForm'));
            const eventData = Object.fromEntries(formData);
            eventData.Status = 'Upcoming';
            eventData.RegistrationRequired = document.getElementById('registrationRequiredInline').checked ? 1 : 0;
            fetch('api/events.php?action=create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(eventData)
            })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    alert('Event created successfully!');
                    document.getElementById('addEventInlineForm').reset();
                    loadEventsList();
                } else {
                    alert('Error: ' + result.message);
                }
            });
        }

        // Event actions
        function viewEvent(eventId) {
            console.log('Viewing event:', eventId);
            alert(`Opening detailed view for Event ID: ${eventId}`);
        }

        function editEvent(eventId) {
            fetch(`api/events.php?action=get&EventID=${eventId}`)
                .then(res => res.json())
                .then(result => {
                    if (result.success && result.data) {
                        const event = result.data;
                        document.getElementById('eventTitle').value = event.EventTitle;
                        document.getElementById('eventType').value = event.EventType;
                        document.getElementById('eventDescription').value = event.Description;
                        document.getElementById('startDate').value = event.StartDate;
                        document.getElementById('endDate').value = event.EndDate;
                        document.getElementById('startTime').value = event.StartTime;
                        document.getElementById('endTime').value = event.EndTime;
                        document.getElementById('venue').value = event.Venue;
                        document.getElementById('capacity').value = event.Capacity;
                        document.getElementById('organizedBy').value = event.OrganizedBy;
                        document.getElementById('contactPerson').value = event.ContactPerson;
                        document.getElementById('contactEmail').value = event.ContactEmail;
                        document.getElementById('contactPhone').value = event.ContactPhone;
                        document.getElementById('registrationRequired').checked = event.RegistrationRequired == 1;
                        document.getElementById('registrationDeadline').value = event.RegistrationDeadline;
                        document.getElementById('addEventForm').setAttribute('data-edit-id', event.EventID);
                        openAddEventModal();
                    } else {
                        alert('Event not found.');
                    }
                });
        }

        function cancelEvent(eventId) {
            if (confirm(`Are you sure you want to cancel Event ID: ${eventId}?`)) {
                fetch('api/events.php?action=delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `EventID=${eventId}`
                })
                .then(res => res.json())
                .then(result => {
                    if (result.success) {
                        alert('Event cancelled successfully!');
                        loadEventsList();
                    } else {
                        alert('Error: ' + result.message);
                    }
                });
            }
        }

        function markAttendance(registrationId) {
            fetch('api/event_registrations.php?action=mark_attendance', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `RegistrationID=${registrationId}`
            })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    alert('Attendance marked!');
                    loadRegistrationsContent();
                } else {
                    alert('Error: ' + result.message);
                }
            });
        function deleteRegistration(registrationId) {
            if (confirm('Are you sure you want to delete this registration?')) {
                fetch('api/event_registrations.php?action=delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `RegistrationID=${registrationId}`
                })
                .then(res => res.json())
                .then(result => {
                    if (result.success) {
                        alert('Registration deleted!');
                        loadRegistrationsContent();
                    } else {
                        alert('Error: ' + result.message);
                    }
                });
            }
        }
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
            Promise.all([
                fetch('api/events.php?action=list').then(res => res.json()),
                fetch('api/event_registrations.php?action=list').then(res => res.json())
            ]).then(([eventsRes, regRes]) => {
                const events = Array.isArray(eventsRes.data) ? eventsRes.data : [];
                const registrations = Array.isArray(regRes.data) ? regRes.data : [];
                const totalEvents = events.length;
                const upcomingEvents = events.filter(e => e.Status === 'Upcoming').length;
                const activeEvents = events.filter(e => e.Status === 'Active').length;
                const completedEvents = events.filter(e => e.Status === 'Completed').length;
                const totalRegistrations = registrations.length;
                // Calculate average attendance (mock: percent of registrations marked attended)
                const attended = registrations.filter(r => r.AttendanceMarked == 1).length;
                const averageAttendance = totalRegistrations > 0 ? Math.round((attended / totalRegistrations) * 100) : 0;
                document.getElementById('totalEvents').textContent = totalEvents;
                document.getElementById('upcomingEvents').textContent = upcomingEvents;
                document.getElementById('activeEvents').textContent = activeEvents;
                document.getElementById('completedEvents').textContent = completedEvents;
                document.getElementById('totalRegistrations').textContent = totalRegistrations;
                document.getElementById('averageAttendance').textContent = averageAttendance + '%';
            });
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