<?php
// Student Library Events Content
// This file will be included in the main content area

// Start session for user authentication
session_start();

// Get student information (should come from session/database)
$student_name = isset($_SESSION['student_name']) ? $_SESSION['student_name'] : "John Doe";
$student_id = isset($_SESSION['student_id']) ? $_SESSION['student_id'] : "STU2024001";

// Mock events data - in real implementation, this would come from database
// Events that are visible to students (Status: Active, Upcoming, Completed)
$library_events = [
    [
        'EventID' => 1,
        'EventTitle' => 'New Book Arrivals Exhibition',
        'EventType' => 'Exhibition',
        'Description' => 'Showcasing latest arrivals in Computer Science, Engineering, and Technology domains. Students can explore and reserve books.',
        'StartDate' => '2025-12-25',
        'EndDate' => '2025-12-30',
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
        'RegistrationDeadline' => '2025-12-23',
        'EventImage' => '/images/events/book-exhibition.jpg'
    ],
    [
        'EventID' => 2,
        'EventTitle' => 'Digital Library Workshop',
        'EventType' => 'Workshop',
        'Description' => 'Hands-on workshop on utilizing digital resources, online databases, and e-learning platforms effectively.',
        'StartDate' => '2025-10-20',
        'EndDate' => '2025-10-20',
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
        'RegistrationDeadline' => '2025-10-18'
    ],
    [
        'EventID' => 3,
        'EventTitle' => 'Research Paper Writing Seminar',
        'EventType' => 'Seminar',
        'Description' => 'Learn effective techniques for writing research papers, citations, and academic writing standards.',
        'StartDate' => '2025-09-15',
        'EndDate' => '2025-09-15',
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
        'RegistrationDeadline' => '2025-09-13'
    ],
    [
        'EventID' => 4,
        'EventTitle' => 'Book Reading Marathon',
        'EventType' => 'Competition',
        'Description' => '24-hour reading challenge for students to promote reading culture and win exciting prizes.',
        'StartDate' => '2025-11-10',
        'EndDate' => '2025-11-11',
        'StartTime' => '18:00:00',
        'EndTime' => '18:00:00',
        'Venue' => 'Library Reading Hall',
        'Capacity' => 50,
        'Registered' => 28,
        'Status' => 'Upcoming',
        'OrganizedBy' => 'Library Committee',
        'ContactPerson' => 'Mr. Vikash Singh',
        'ContactEmail' => 'vikash.singh@wiet.edu',
        'ContactPhone' => '9876543214',
        'RegistrationRequired' => true,
        'RegistrationDeadline' => '2025-11-08'
    ],
    [
        'EventID' => 5,
        'EventTitle' => 'Career Guidance Session',
        'EventType' => 'Seminar',
        'Description' => 'Interactive session on career opportunities, interview preparation, and industry insights for final year students.',
        'StartDate' => '2025-10-05',
        'EndDate' => '2025-10-05',
        'StartTime' => '15:00:00',
        'EndTime' => '17:00:00',
        'Venue' => 'Conference Hall',
        'Capacity' => 80,
        'Registered' => 65,
        'Status' => 'Active',
        'OrganizedBy' => 'Career Development Cell',
        'ContactPerson' => 'Dr. Amit Kumar',
        'ContactEmail' => 'amit.kumar@wiet.edu',
        'ContactPhone' => '9876543215',
        'RegistrationRequired' => true,
        'RegistrationDeadline' => '2025-10-03'
    ]
];

// Separate events by status
$upcoming_events = array_filter($library_events, function($event) {
    return $event['Status'] === 'Upcoming';
});

$active_events = array_filter($library_events, function($event) {
    return $event['Status'] === 'Active';
});

$completed_events = array_filter($library_events, function($event) {
    return $event['Status'] === 'Completed';
});

// Function to format date
function formatEventDate($date) {
    return date('M j, Y', strtotime($date));
}

// Function to format time
function formatEventTime($time) {
    return date('g:i A', strtotime($time));
}

// Function to get days until event
function getDaysUntilEvent($date) {
    $event_date = new DateTime($date);
    $current_date = new DateTime();
    $diff = $current_date->diff($event_date);
    
    if ($event_date < $current_date) {
        return 'Past event';
    } else {
        return $diff->days . ' days';
    }
}
?>

<style>
    /* Library Events Page Styles */
    .events-header {
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 2px solid #cfac69;
    }

    .events-title {
        color: #263c79;
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .events-subtitle {
        color: #666;
        font-size: 16px;
        margin: 0;
    }

    .events-tabs {
        display: flex;
        margin-bottom: 25px;
        border-bottom: 2px solid #e0e0e0;
        gap: 10px;
    }

    .tab-button {
        background: none;
        border: none;
        padding: 12px 20px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        color: #666;
        border-bottom: 3px solid transparent;
        transition: all 0.3s ease;
    }

    .tab-button:hover {
        color: #263c79;
        background-color: rgba(207, 172, 105, 0.1);
    }

    .tab-button.active {
        color: #263c79;
        border-bottom-color: #cfac69;
    }

    .events-container {
        margin-bottom: 30px;
    }

    .events-section {
        display: none;
    }

    .events-section.active {
        display: block;
    }

    .events-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .event-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        position: relative;
    }

    .event-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .event-status-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        z-index: 2;
    }

    .status-upcoming {
        background: #e3f2fd;
        color: #1976d2;
    }

    .status-active {
        background: #e8f5e8;
        color: #2e7d32;
    }

    .status-completed {
        background: #f3e5f5;
        color: #7b1fa2;
    }

    .event-header {
        background: #263c79;
        color: white;
        padding: 12px 20px;
        position: relative;
    }

    .event-type {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        opacity: 0.9;
        margin-bottom: 0;
    }

    .event-title {
        font-size: 18px;
        font-weight: 700;
        margin: 0;
        line-height: 1.2;
        margin-top: 3px;
    }

    .event-body {
        padding: 20px;
    }

    .event-description {
        color: #666;
        font-size: 14px;
        line-height: 1.5;
        margin-bottom: 15px;
    }

    .event-details {
        margin-bottom: 15px;
    }

    .event-detail-row {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .detail-icon {
        color: #263c79;
        width: 20px;
        margin-right: 10px;
        text-align: center;
    }

    .detail-label {
        font-weight: 600;
        color: #333;
        margin-right: 8px;
        min-width: 80px;
    }

    .detail-value {
        color: #666;
    }

    .event-footer {
        padding: 15px 20px;
        background: #f8f9fa;
        border-top: 1px solid #e0e0e0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .event-capacity {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        color: #666;
    }

    .capacity-bar {
        width: 80px;
        height: 6px;
        background: #e0e0e0;
        border-radius: 3px;
        overflow: hidden;
    }

    .capacity-fill {
        height: 100%;
        background: linear-gradient(90deg, #263c79, #cfac69);
        border-radius: 3px;
        transition: width 0.3s ease;
    }

    .register-button {
        background: #263c79;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .register-button:hover {
        background: #1a2a5a;
        transform: translateY(-1px);
    }

    .register-button:disabled {
        background: #ccc;
        cursor: not-allowed;
        transform: none;
    }

    .register-button.registered {
        background: #cfac69;
        cursor: default;
    }

    .register-button.registered:hover {
        background: #cfac69;
        transform: none;
    }

    .no-events {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }

    .no-events i {
        font-size: 48px;
        color: #ddd;
        margin-bottom: 15px;
    }

    .contact-info {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #eee;
        font-size: 12px;
        color: #888;
    }

    .contact-person {
        font-weight: 600;
        color: #263c79;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .events-grid {
            grid-template-columns: 1fr;
        }

        .events-tabs {
            flex-wrap: wrap;
        }

        .tab-button {
            font-size: 14px;
            padding: 10px 16px;
        }

        .event-card {
            margin-bottom: 15px;
        }

        .event-header {
            padding: 15px;
        }

        .event-title {
            font-size: 16px;
        }

        .event-body {
            padding: 15px;
        }
    }
</style>

<div class="events-header">
    <h1 class="events-title">
        <i class="fas fa-calendar-alt" style="margin-right: 10px; color: #cfac69;"></i>
        Library Events
    </h1>
    <p class="events-subtitle">Discover workshops, seminars, exhibitions and competitions organized by the library</p>
</div>

<!-- Event Type Tabs -->
<div class="events-tabs">
    <button class="tab-button active" onclick="showEventType('active')">
        <i class="fas fa-play-circle" style="margin-right: 5px;"></i>
        Active Events (<?php echo count($active_events); ?>)
    </button>
    <button class="tab-button" onclick="showEventType('upcoming')">
        <i class="fas fa-clock" style="margin-right: 5px;"></i>
        Upcoming Events (<?php echo count($upcoming_events); ?>)
    </button>
    <button class="tab-button" onclick="showEventType('completed')">
        <i class="fas fa-check-circle" style="margin-right: 5px;"></i>
        Completed Events (<?php echo count($completed_events); ?>)
    </button>
</div>

<!-- Active Events Section -->
<div class="events-container">
    <div id="active-events" class="events-section active">
        <?php if (!empty($active_events)): ?>
            <div class="events-grid">
                <?php foreach ($active_events as $event): ?>
                    <div class="event-card">
                        <div class="event-status-badge status-active">Active</div>
                        <div class="event-header">
                            <div class="event-type"><?php echo htmlspecialchars($event['EventType']); ?></div>
                            <h3 class="event-title"><?php echo htmlspecialchars($event['EventTitle']); ?></h3>
                        </div>
                        <div class="event-body">
                            <p class="event-description"><?php echo htmlspecialchars($event['Description']); ?></p>
                            
                            <div class="event-details">
                                <div class="event-detail-row">
                                    <i class="detail-icon fas fa-calendar"></i>
                                    <span class="detail-label">Date:</span>
                                    <span class="detail-value">
                                        <?php echo formatEventDate($event['StartDate']); ?>
                                        <?php if ($event['StartDate'] !== $event['EndDate']): ?>
                                            - <?php echo formatEventDate($event['EndDate']); ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="event-detail-row">
                                    <i class="detail-icon fas fa-clock"></i>
                                    <span class="detail-label">Time:</span>
                                    <span class="detail-value">
                                        <?php echo formatEventTime($event['StartTime']); ?> - <?php echo formatEventTime($event['EndTime']); ?>
                                    </span>
                                </div>
                                <div class="event-detail-row">
                                    <i class="detail-icon fas fa-map-marker-alt"></i>
                                    <span class="detail-label">Venue:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($event['Venue']); ?></span>
                                </div>
                                <div class="event-detail-row">
                                    <i class="detail-icon fas fa-users"></i>
                                    <span class="detail-label">Organizer:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($event['OrganizedBy']); ?></span>
                                </div>
                            </div>
                            
                            <div class="contact-info">
                                <i class="fas fa-info-circle" style="margin-right: 5px;"></i>
                                Contact: <span class="contact-person"><?php echo htmlspecialchars($event['ContactPerson']); ?></span> 
                                | <?php echo htmlspecialchars($event['ContactEmail']); ?> 
                                | <?php echo htmlspecialchars($event['ContactPhone']); ?>
                            </div>
                        </div>
                        <div class="event-footer">
                            <div class="event-capacity">
                                <span><?php echo $event['Registered']; ?>/<?php echo $event['Capacity']; ?> registered</span>
                                <div class="capacity-bar">
                                    <div class="capacity-fill" style="width: <?php echo ($event['Registered'] / $event['Capacity']) * 100; ?>%;"></div>
                                </div>
                            </div>
                            <?php if ($event['RegistrationRequired']): ?>
                                <button class="register-button" onclick="registerForEvent(<?php echo $event['EventID']; ?>)">
                                    <i class="fas fa-user-plus" style="margin-right: 5px;"></i>
                                    Register Now
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-events">
                <i class="fas fa-calendar-times"></i>
                <h3>No Active Events</h3>
                <p>There are currently no active events. Check upcoming events or browse completed events.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Upcoming Events Section -->
    <div id="upcoming-events" class="events-section">
        <?php if (!empty($upcoming_events)): ?>
            <div class="events-grid">
                <?php foreach ($upcoming_events as $event): ?>
                    <div class="event-card">
                        <div class="event-status-badge status-upcoming">
                            <?php echo getDaysUntilEvent($event['StartDate']); ?>
                        </div>
                        <div class="event-header">
                            <div class="event-type"><?php echo htmlspecialchars($event['EventType']); ?></div>
                            <h3 class="event-title"><?php echo htmlspecialchars($event['EventTitle']); ?></h3>
                        </div>
                        <div class="event-body">
                            <p class="event-description"><?php echo htmlspecialchars($event['Description']); ?></p>
                            
                            <div class="event-details">
                                <div class="event-detail-row">
                                    <i class="detail-icon fas fa-calendar"></i>
                                    <span class="detail-label">Date:</span>
                                    <span class="detail-value">
                                        <?php echo formatEventDate($event['StartDate']); ?>
                                        <?php if ($event['StartDate'] !== $event['EndDate']): ?>
                                            - <?php echo formatEventDate($event['EndDate']); ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="event-detail-row">
                                    <i class="detail-icon fas fa-clock"></i>
                                    <span class="detail-label">Time:</span>
                                    <span class="detail-value">
                                        <?php echo formatEventTime($event['StartTime']); ?> - <?php echo formatEventTime($event['EndTime']); ?>
                                    </span>
                                </div>
                                <div class="event-detail-row">
                                    <i class="detail-icon fas fa-map-marker-alt"></i>
                                    <span class="detail-label">Venue:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($event['Venue']); ?></span>
                                </div>
                                <div class="event-detail-row">
                                    <i class="detail-icon fas fa-users"></i>
                                    <span class="detail-label">Organizer:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($event['OrganizedBy']); ?></span>
                                </div>
                                <?php if ($event['RegistrationRequired'] && isset($event['RegistrationDeadline'])): ?>
                                    <div class="event-detail-row">
                                        <i class="detail-icon fas fa-exclamation-triangle"></i>
                                        <span class="detail-label">Reg. Deadline:</span>
                                        <span class="detail-value"><?php echo formatEventDate($event['RegistrationDeadline']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="contact-info">
                                <i class="fas fa-info-circle" style="margin-right: 5px;"></i>
                                Contact: <span class="contact-person"><?php echo htmlspecialchars($event['ContactPerson']); ?></span> 
                                | <?php echo htmlspecialchars($event['ContactEmail']); ?> 
                                | <?php echo htmlspecialchars($event['ContactPhone']); ?>
                            </div>
                        </div>
                        <div class="event-footer">
                            <div class="event-capacity">
                                <span><?php echo $event['Registered']; ?>/<?php echo $event['Capacity']; ?> registered</span>
                                <div class="capacity-bar">
                                    <div class="capacity-fill" style="width: <?php echo ($event['Registered'] / $event['Capacity']) * 100; ?>%;"></div>
                                </div>
                            </div>
                            <?php if ($event['RegistrationRequired']): ?>
                                <button class="register-button" onclick="registerForEvent(<?php echo $event['EventID']; ?>)">
                                    <i class="fas fa-user-plus" style="margin-right: 5px;"></i>
                                    Pre-Register
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-events">
                <i class="fas fa-calendar-plus"></i>
                <h3>No Upcoming Events</h3>
                <p>No events are scheduled for the near future. Check back later for updates.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Completed Events Section -->
    <div id="completed-events" class="events-section">
        <?php if (!empty($completed_events)): ?>
            <div class="events-grid">
                <?php foreach ($completed_events as $event): ?>
                    <div class="event-card">
                        <div class="event-status-badge status-completed">Completed</div>
                        <div class="event-header">
                            <div class="event-type"><?php echo htmlspecialchars($event['EventType']); ?></div>
                            <h3 class="event-title"><?php echo htmlspecialchars($event['EventTitle']); ?></h3>
                        </div>
                        <div class="event-body">
                            <p class="event-description"><?php echo htmlspecialchars($event['Description']); ?></p>
                            
                            <div class="event-details">
                                <div class="event-detail-row">
                                    <i class="detail-icon fas fa-calendar"></i>
                                    <span class="detail-label">Date:</span>
                                    <span class="detail-value">
                                        <?php echo formatEventDate($event['StartDate']); ?>
                                        <?php if ($event['StartDate'] !== $event['EndDate']): ?>
                                            - <?php echo formatEventDate($event['EndDate']); ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="event-detail-row">
                                    <i class="detail-icon fas fa-clock"></i>
                                    <span class="detail-label">Time:</span>
                                    <span class="detail-value">
                                        <?php echo formatEventTime($event['StartTime']); ?> - <?php echo formatEventTime($event['EndTime']); ?>
                                    </span>
                                </div>
                                <div class="event-detail-row">
                                    <i class="detail-icon fas fa-map-marker-alt"></i>
                                    <span class="detail-label">Venue:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($event['Venue']); ?></span>
                                </div>
                                <div class="event-detail-row">
                                    <i class="detail-icon fas fa-users"></i>
                                    <span class="detail-label">Organizer:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($event['OrganizedBy']); ?></span>
                                </div>
                            </div>
                            
                            <div class="contact-info">
                                <i class="fas fa-info-circle" style="margin-right: 5px;"></i>
                                Organized by: <span class="contact-person"><?php echo htmlspecialchars($event['ContactPerson']); ?></span> 
                                | <?php echo htmlspecialchars($event['OrganizedBy']); ?>
                            </div>
                        </div>
                        <div class="event-footer">
                            <div class="event-capacity">
                                <span><?php echo $event['Registered']; ?>/<?php echo $event['Capacity']; ?> attended</span>
                                <div class="capacity-bar">
                                    <div class="capacity-fill" style="width: <?php echo ($event['Registered'] / $event['Capacity']) * 100; ?>%;"></div>
                                </div>
                            </div>
                            <button class="register-button registered">
                                <i class="fas fa-check" style="margin-right: 5px;"></i>
                                Completed
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-events">
                <i class="fas fa-calendar-check"></i>
                <h3>No Completed Events</h3>
                <p>No events have been completed yet. Check back after events conclude.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Function to show different event types
    function showEventType(type) {
        // Hide all sections
        document.querySelectorAll('.events-section').forEach(section => {
            section.classList.remove('active');
        });

        // Remove active class from all tabs
        document.querySelectorAll('.tab-button').forEach(tab => {
            tab.classList.remove('active');
        });

        // Show selected section
        document.getElementById(type + '-events').classList.add('active');
        
        // Add active class to clicked tab
        event.target.classList.add('active');
    }

    // Function to register for an event
    function registerForEvent(eventId) {
        // In a real implementation, this would make an AJAX call to register the student
        if (confirm('Are you sure you want to register for this event?')) {
            alert('Registration successful! You will receive a confirmation email shortly.');
            
            // Update button to show registered state
            const button = event.target;
            button.innerHTML = '<i class="fas fa-check" style="margin-right: 5px;"></i> Registered';
            button.classList.add('registered');
            button.disabled = true;
        }
    }

    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        // Set default active tab
        showEventType('active');
    });
</script>