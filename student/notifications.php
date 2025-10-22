<?php
// Notifications Content - Student notification center
// This file will be included in the main content area

// Session variables for student info
$student_name = isset($_SESSION['student_name']) ? $_SESSION['student_name'] : 'John Doe';
$student_id = isset($_SESSION['student_id']) ? $_SESSION['student_id'] : 'STU2024001';

// Mock data for demonstration - replace with actual database queries
$notifications = [
    [
        'id' => 1,
        'title' => 'Book Due Tomorrow',
        'message' => 'The book "Database Management Systems" is due tomorrow (Sep 24, 2025). Please renew or return on time to avoid fines.',
        'type' => 'warning',
        'category' => 'due_reminder',
        'date' => '2025-09-23 10:30:00',
        'read' => false,
        'action_required' => true,
        'book_id' => 'BK001',
        'book_title' => 'Database Management Systems'
    ],
    [
        'id' => 2,
        'title' => 'New Books Added - Computer Science',
        'message' => '15 new books have been added to the Computer Science section. Check out the latest titles in AI, Machine Learning, and Data Science.',
        'type' => 'info',
        'category' => 'new_arrivals',
        'date' => '2025-09-22 14:15:00',
        'read' => false,
        'action_required' => false
    ],
    [
        'id' => 3,
        'title' => 'Library Maintenance Notice',
        'message' => 'The library will be closed for system maintenance on September 25, 2025 from 2:00 PM to 6:00 PM. Digital resources will remain accessible.',
        'type' => 'announcement',
        'category' => 'maintenance',
        'date' => '2025-09-21 09:00:00',
        'read' => true,
        'action_required' => false
    ],
    [
        'id' => 4,
        'title' => 'Fine Payment Reminder',
        'message' => 'You have an outstanding fine of ₹50 for late return of "Introduction to Algorithms". Please clear your dues at the earliest.',
        'type' => 'error',
        'category' => 'fine',
        'date' => '2025-09-20 11:45:00',
        'read' => false,
        'action_required' => true,
        'fine_amount' => 50
    ],
    [
        'id' => 5,
        'title' => 'Book Reservation Ready',
        'message' => 'Your reserved book "Clean Code" by Robert Martin is now available for pickup. Please collect it within 24 hours.',
        'type' => 'success',
        'category' => 'reservation',
        'date' => '2025-09-19 16:20:00',
        'read' => true,
        'action_required' => true,
        'book_title' => 'Clean Code'
    ],
    [
        'id' => 6,
        'title' => 'E-Resource Access Update',
        'message' => 'New databases IEEE Xplore and ACM Digital Library are now available. Access them from the E-Resources section.',
        'type' => 'info',
        'category' => 'eresources',
        'date' => '2025-09-18 13:30:00',
        'read' => true,
        'action_required' => false
    ],
    [
        'id' => 7,
        'title' => 'Library Workshop Registration',
        'message' => 'Register for the "Research Methodology" workshop on October 5, 2025. Limited seats available. Registration deadline: September 30.',
        'type' => 'event',
        'category' => 'workshop',
        'date' => '2025-09-17 10:00:00',
        'read' => true,
        'action_required' => true,
        'deadline' => '2025-09-30'
    ]
];

// Filter notifications by category
$categories = [
    'all' => 'All Notifications',
    'due_reminder' => 'Due Reminders',
    'fine' => 'Fines & Payments',
    'reservation' => 'Reservations',
    'new_arrivals' => 'New Arrivals',
    'maintenance' => 'System Updates',
    'eresources' => 'E-Resources',
    'workshop' => 'Events & Workshops'
];

// Count unread notifications
$unread_count = count(array_filter($notifications, function ($n) {
    return !$n['read'];
}));
$action_required_count = count(array_filter($notifications, function ($n) {
    return $n['action_required'];
}));
?>

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
        margin: 0 0 8px 0;
    }

    .notifications-subtitle {
        color: #666;
        font-size: 16px;
        margin: 0;
    }

    .notifications-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }

    .stat-card {
        background: white;
        height: 100px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border-left: 4px solid #cfac69;
    }

    .stat-card.success {
        border-left-color: #28a745;
        /* Green for positive metrics */
    }

    .stat-card.danger {
        border-left-color: #dc3545;
        /* Red for critical metrics */
    }

    .stat-card.info {
        border-left-color: #17a2b8;
        /* Blue for informational metrics */
    }

    /* .stat-icon {
        font-size: 28px;
        margin-bottom: 5px;
    }

    .stat-icon.unread {
        color: #dc3545;
    }

    .stat-icon.action {
        color: #ffc107;
    }

    .stat-icon.total {
        color: #263c79;
    } */

    .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #263c79;
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 14px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .notifications-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .filter-tabs {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }

    .filter-tab {
        background: transparent;
        border: 1px solid #e0e0e0;
        color: #666;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .filter-tab.active {
        background: #263c79;
        color: white;
        border-color: #263c79;
    }

    .filter-tab:hover {
        border-color: #cfac69;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
    }

    .action-btn {
        background: #f8f9fa;
        border: 1px solid #e0e0e0;
        color: #263c79;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .action-btn:hover {
        background: #263c79;
        color: white;
    }

    .notifications-list {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .notification-item {
        padding: 20px;
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.3s ease;
        position: relative;
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-item:hover {
        background: #f8f9fa;
    }

    .notification-item.unread {
        background: #fff9f0;
        border-left: 4px solid #cfac69;
    }

    .notification-item.unread::before {
        content: '';
        position: absolute;
        top: 20px;
        right: 20px;
        width: 8px;
        height: 8px;
        background: #dc3545;
        border-radius: 50%;
    }

    .notification-header {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        margin-bottom: 10px;
    }

    .notification-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .notification-icon.warning {
        background: #fff3cd;
        color: #856404;
    }

    .notification-icon.info {
        background: #d1ecf1;
        color: #0c5460;
    }

    .notification-icon.success {
        background: #d4edda;
        color: #155724;
    }

    .notification-icon.error {
        background: #f8d7da;
        color: #721c24;
    }

    .notification-icon.announcement {
        background: #e2e3e5;
        color: #383d41;
    }

    .notification-icon.event {
        background: #f3e5f5;
        color: #7b1fa2;
    }

    .notification-content {
        flex: 1;
    }

    .notification-title {
        font-size: 16px;
        font-weight: 600;
        color: #263c79;
        margin-bottom: 5px;
        line-height: 1.3;
    }

    .notification-message {
        font-size: 14px;
        color: #666;
        line-height: 1.5;
        margin-bottom: 10px;
    }

    .notification-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .notification-date {
        font-size: 12px;
        color: #999;
    }

    .notification-actions {
        display: flex;
        gap: 8px;
    }

    .notification-btn {
        background: transparent;
        border: 1px solid #e0e0e0;
        color: #666;
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .notification-btn.primary {
        background: #263c79;
        color: white;
        border-color: #263c79;
    }

    .notification-btn.primary:hover {
        background: #1e2f5a;
    }

    .notification-btn:hover {
        border-color: #cfac69;
        color: #263c79;
    }

    .action-required-badge {
        background: #ffc107;
        color: #212529;
        padding: 3px 6px;
        border-radius: 3px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }

    .empty-state i {
        font-size: 48px;
        color: #cfac69;
        margin-bottom: 15px;
    }

    .empty-state h3 {
        font-size: 18px;
        color: #263c79;
        margin-bottom: 8px;
    }

    .empty-state p {
        font-size: 14px;
        color: #666;
    }

    @media (max-width: 768px) {
        .notifications-controls {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-tabs {
            justify-content: center;
        }

        .action-buttons {
            justify-content: center;
        }

        .notification-header {
            flex-direction: column;
            gap: 10px;
        }

        .notification-meta {
            flex-direction: column;
            align-items: flex-start;
        }

        .notifications-stats {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 480px) {
        .notifications-stats {
            grid-template-columns: 1fr;
        }

        .filter-tabs {
            flex-direction: column;
        }

        .filter-tab {
            text-align: center;
        }
    }
</style>

<div class="notifications-header">
    <h1 class="notifications-title">Notifications</h1>
    <p class="notifications-subtitle">Stay updated with library alerts, due dates, and important announcements</p>
</div>

<!-- Statistics Overview -->
<div class="notifications-stats">
    <div class="stat-card danger">
        <!-- <i class="stat-icon unread fas fa-bell"></i> -->
        <div class="stat-value"><?php echo $unread_count; ?></div>
        <div class="stat-label">Unread Notifications</div>
    </div>
    <div class="stat-card danger">
        <!-- <i class="stat-icon action fas fa-exclamation-triangle"></i> -->
        <div class="stat-value"><?php echo $action_required_count; ?></div>
        <div class="stat-label">Action Required</div>
    </div>
    <div class="stat-card info">
        <!-- <i class="stat-icon total fas fa-list"></i> -->
        <div class="stat-value"><?php echo count($notifications); ?></div>
        <div class="stat-label">Total Notifications</div>
    </div>
</div>

<!-- Controls -->
<div class="notifications-controls">
    <div class="filter-tabs">
        <?php foreach ($categories as $key => $label): ?>
            <button class="filter-tab <?php echo $key === 'all' ? 'active' : ''; ?>"
                onclick="filterNotifications('<?php echo $key; ?>')"
                data-category="<?php echo $key; ?>">
                <?php echo $label; ?>
            </button>
        <?php endforeach; ?>
    </div>

    <div class="action-buttons">
        <button class="action-btn" onclick="markAllAsRead()">
            <i class="fas fa-check-double"></i> Mark All Read
        </button>
        <button class="action-btn" onclick="clearReadNotifications()">
            <i class="fas fa-trash"></i> Clear Read
        </button>
    </div>
</div>

<!-- Notifications List -->
<div class="notifications-list" id="notifications-container">
    <?php foreach ($notifications as $notification): ?>
        <div class="notification-item <?php echo !$notification['read'] ? 'unread' : ''; ?>"
            data-category="<?php echo $notification['category']; ?>"
            data-id="<?php echo $notification['id']; ?>">

            <div class="notification-header">
                <div class="notification-icon <?php echo $notification['type']; ?>">
                    <?php
                    $type_icons = [
                        'warning' => 'fas fa-exclamation-triangle',
                        'info' => 'fas fa-info-circle',
                        'success' => 'fas fa-check-circle',
                        'error' => 'fas fa-times-circle',
                        'announcement' => 'fas fa-bullhorn',
                        'event' => 'fas fa-calendar-alt'
                    ];
                    ?>
                    <i class="<?php echo $type_icons[$notification['type']]; ?>"></i>
                </div>

                <div class="notification-content">
                    <h3 class="notification-title">
                        <?php echo htmlspecialchars($notification['title']); ?>
                        <?php if ($notification['action_required']): ?>
                            <span class="action-required-badge">Action Required</span>
                        <?php endif; ?>
                    </h3>

                    <p class="notification-message">
                        <?php echo htmlspecialchars($notification['message']); ?>
                    </p>

                    <div class="notification-meta">
                        <span class="notification-date">
                            <i class="fas fa-clock"></i>
                            <?php echo date('M j, Y \a\t g:i A', strtotime($notification['date'])); ?>
                        </span>

                        <div class="notification-actions">
                            <?php if (!$notification['read']): ?>
                                <button class="notification-btn" onclick="markAsRead(<?php echo $notification['id']; ?>)">
                                    Mark Read
                                </button>
                            <?php endif; ?>

                            <?php if ($notification['action_required']): ?>
                                <?php if ($notification['category'] === 'due_reminder'): ?>
                                    <button class="notification-btn primary" onclick="renewBook('<?php echo $notification['book_id']; ?>')">
                                        Renew Book
                                    </button>
                                <?php elseif ($notification['category'] === 'fine'): ?>
                                    <button class="notification-btn primary" onclick="payFine(<?php echo $notification['fine_amount']; ?>)">
                                        Pay Fine
                                    </button>
                                <?php elseif ($notification['category'] === 'reservation'): ?>
                                    <button class="notification-btn primary" onclick="viewReservation()">
                                        View Details
                                    </button>
                                <?php elseif ($notification['category'] === 'workshop'): ?>
                                    <button class="notification-btn primary" onclick="registerWorkshop()">
                                        Register
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>

                            <button class="notification-btn" onclick="deleteNotification(<?php echo $notification['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
    function filterNotifications(category) {
        // Update active tab
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelector(`[data-category="${category}"]`).classList.add('active');

        // Filter notifications
        const notifications = document.querySelectorAll('.notification-item');
        let visibleCount = 0;

        notifications.forEach(notification => {
            const notificationCategory = notification.getAttribute('data-category');

            if (category === 'all' || notificationCategory === category) {
                notification.style.display = 'block';
                visibleCount++;
            } else {
                notification.style.display = 'none';
            }
        });

        // Show empty state if no notifications
        const container = document.getElementById('notifications-container');
        const existingEmptyState = container.querySelector('.empty-state');

        if (visibleCount === 0 && !existingEmptyState) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-bell-slash"></i>
                    <h3>No notifications found</h3>
                    <p>There are no notifications in this category.</p>
                </div>
            `;
        } else if (visibleCount > 0 && existingEmptyState) {
            location.reload(); // Reload to show notifications again
        }
    }

    function markAsRead(notificationId) {
        const notification = document.querySelector(`[data-id="${notificationId}"]`);
        notification.classList.remove('unread');

        // Remove mark read button
        const markReadBtn = notification.querySelector('button[onclick*="markAsRead"]');
        if (markReadBtn) {
            markReadBtn.remove();
        }

        // Update unread count
        updateUnreadCount();

        // In real implementation, make AJAX call to server
        console.log('Marking notification as read:', notificationId);
    }

    function markAllAsRead() {
        document.querySelectorAll('.notification-item.unread').forEach(notification => {
            notification.classList.remove('unread');

            // Remove mark read buttons
            const markReadBtn = notification.querySelector('button[onclick*="markAsRead"]');
            if (markReadBtn) {
                markReadBtn.remove();
            }
        });

        updateUnreadCount();
        alert('All notifications marked as read!');
    }

    function clearReadNotifications() {
        if (confirm('Are you sure you want to delete all read notifications?')) {
            document.querySelectorAll('.notification-item:not(.unread)').forEach(notification => {
                notification.remove();
            });

            // Check if any notifications remain
            const remainingNotifications = document.querySelectorAll('.notification-item').length;
            if (remainingNotifications === 0) {
                document.getElementById('notifications-container').innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-bell-slash"></i>
                        <h3>No notifications</h3>
                        <p>All notifications have been cleared.</p>
                    </div>
                `;
            }

            alert('Read notifications cleared!');
        }
    }

    function deleteNotification(notificationId) {
        if (confirm('Are you sure you want to delete this notification?')) {
            const notification = document.querySelector(`[data-id="${notificationId}"]`);
            notification.remove();

            updateUnreadCount();
            console.log('Deleting notification:', notificationId);
        }
    }

    function updateUnreadCount() {
        const unreadCount = document.querySelectorAll('.notification-item.unread').length;
        document.querySelector('.stat-value').textContent = unreadCount;
    }

    // Action handlers
    function renewBook(bookId) {
        alert(`Redirecting to renew book: ${bookId}`);
        // In real implementation, redirect to book renewal page
    }

    function payFine(amount) {
        alert(`Redirecting to payment page for ₹${amount}`);
        // In real implementation, redirect to payment gateway
    }

    function viewReservation() {
        alert('Redirecting to reservation details...');
        // In real implementation, redirect to reservation page
    }

    function registerWorkshop() {
        alert('Redirecting to workshop registration...');
        // In real implementation, redirect to workshop registration
    }

    // Auto-refresh notifications every 5 minutes
    setInterval(function() {
        console.log('Auto-refreshing notifications...');
        // In real implementation, make AJAX call to fetch new notifications
    }, 300000); // 5 minutes
</script>