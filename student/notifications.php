<?php
// Notifications Content - Student notification center
// This file will be included in the main content area

// ============================================================
// DATA SOURCE: DATABASE (100% LIVE)
// ============================================================
// ✅ Overdue books - FROM Circulation + Books + Holding
// ✅ Due soon books - FROM Circulation + Books + Holding
// ✅ Library events - FROM library_events
// ✅ Activity log - FROM ActivityLog
// ============================================================

// Start session and check authentication
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: student_login.php');
    exit();
}

// Include database connection
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
$page_csrf_token = generateCSRFToken();

// Session variables for student info
$student_id = $_SESSION['student_id'] ?? null;
$member_no = $_SESSION['member_no'] ?? null;

// Fetch real notifications from database
$notifications = [];

// DEBUG: Log what we're looking for
error_log("Student Notifications - Member No: " . $member_no);

// ============================================================
// 1. FETCH ADMIN-CREATED NOTIFICATIONS FROM DATABASE
// ============================================================
try {
    $admin_notif_stmt = $pdo->prepare("
        SELECT 
            NotificationID,
            Title,
            Message,
            Type,
            IsRead,
            CreatedDate
        FROM Notifications
        WHERE (MemberNo = ? OR MemberNo IS NULL)
        ORDER BY CreatedDate DESC
        LIMIT 50
    ");
    $admin_notif_stmt->execute([$member_no]);
    
    // DEBUG: Log how many we found
    error_log("Admin notifications fetched: " . $admin_notif_stmt->rowCount());
    $admin_notifications = $admin_notif_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add admin notifications to the list
    foreach ($admin_notifications as $admin_notif) {
        $type_map = [
            'Due Reminder' => 'warning',
            'Overdue Notice' => 'error',
            'New Arrivals' => 'info',
            'Event Reminder' => 'info',
            'System Maintenance' => 'warning',
            'Membership Expiry' => 'warning'
        ];
        
        $notifications[] = [
            'id' => 'admin_' . $admin_notif['NotificationID'],
            'notification_db_id' => $admin_notif['NotificationID'], // For marking as read
            'title' => $admin_notif['Title'],
            'message' => $admin_notif['Message'],
            'type' => $type_map[$admin_notif['Type']] ?? 'info',
            'category' => strtolower(str_replace(' ', '_', $admin_notif['Type'])),
            'date' => $admin_notif['CreatedDate'],
            'read' => (bool)$admin_notif['IsRead'],
            'action_required' => false,
            'is_admin_notification' => true
        ];
    }
} catch (PDOException $e) {
    error_log("Admin notifications fetch error: " . $e->getMessage());
}

// ============================================================
// 2. FETCH SYSTEM-GENERATED NOTIFICATIONS (OVERDUE, DUE SOON, ETC)
// ============================================================
try {
    // Get overdue books (warning notifications)
    $overdue_stmt = $pdo->prepare("
        SELECT 
            c.CirculationID,
            b.Title,
            c.DueDate,
            DATEDIFF(CURRENT_DATE, c.DueDate) as days_overdue
        FROM Circulation c
        INNER JOIN Holding h ON c.AccNo = h.AccNo
        INNER JOIN Books b ON h.CatNo = b.CatNo
        WHERE c.MemberNo = ?
        AND c.Status = 'Active'
        AND c.DueDate < CURRENT_DATE
        ORDER BY c.DueDate ASC
    ");
    $overdue_stmt->execute([$member_no]);
    $overdue_books = $overdue_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($overdue_books as $book) {
        $notifications[] = [
            'id' => 'overdue_' . $book['CirculationID'],
            'title' => 'Book Overdue',
            'message' => sprintf('The book "%s" is overdue by %d days. Please return it as soon as possible to avoid additional fines.', 
                $book['Title'], $book['days_overdue']),
            'type' => 'error',
            'category' => 'overdue',
            'date' => date('Y-m-d H:i:s'),
            'read' => false,
            'action_required' => true,
            'book_title' => $book['Title']
        ];
    }
    
    // Get books due soon (next 3 days)
    $due_soon_stmt = $pdo->prepare("
        SELECT 
            c.CirculationID,
            b.Title,
            c.DueDate,
            DATEDIFF(c.DueDate, CURRENT_DATE) as days_remaining
        FROM Circulation c
        INNER JOIN Holding h ON c.AccNo = h.AccNo
        INNER JOIN Books b ON h.CatNo = b.CatNo
        WHERE c.MemberNo = ?
        AND c.Status = 'Active'
        AND c.DueDate BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 3 DAY)
        ORDER BY c.DueDate ASC
    ");
    $due_soon_stmt->execute([$member_no]);
    $due_soon_books = $due_soon_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($due_soon_books as $book) {
        $notifications[] = [
            'id' => 'due_' . $book['CirculationID'],
            'title' => 'Book Due Soon',
            'message' => sprintf('The book "%s" is due in %d days (%s). Please renew or return on time.', 
                $book['Title'], $book['days_remaining'], date('M d, Y', strtotime($book['DueDate']))),
            'type' => 'warning',
            'category' => 'due_reminder',
            'date' => date('Y-m-d H:i:s'),
            'read' => false,
            'action_required' => true,
            'book_title' => $book['Title']
        ];
    }
} catch (PDOException $e) {
    error_log("Overdue/due books fetch error: " . $e->getMessage());
}
    
// ============================================================
// 3. FETCH LIBRARY EVENTS
// ============================================================
try {
    $events_stmt = $pdo->query("
        SELECT 
            EventID,
            EventTitle,
            StartDate,
            Venue
        FROM library_events
        WHERE Status IN ('Active', 'Upcoming')
        AND StartDate >= CURRENT_DATE
        ORDER BY StartDate ASC
        LIMIT 3
    ");
    $events = $events_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($events as $event) {
        $notifications[] = [
            'id' => 'event_' . $event['EventID'],
            'title' => 'Upcoming Library Event: ' . $event['EventTitle'],
            'message' => sprintf('Event scheduled on %s at %s. Register now!', 
                date('M d, Y', strtotime($event['StartDate'])), $event['Venue']),
            'type' => 'info',
            'category' => 'event',
            'date' => date('Y-m-d H:i:s'),
            'read' => false,
            'action_required' => false
        ];
    }
} catch (PDOException $e) {
    error_log("Library events fetch error: " . $e->getMessage());
}
    
// ============================================================
// 4. FETCH ACTIVITY LOG
// ============================================================
try {
    $activity_stmt = $pdo->prepare("
        SELECT 
            Action,
            Details,
            Timestamp
        FROM ActivityLog
        WHERE UserID = ?
        AND UserType = 'Student'
        AND Action IN ('Book Issued', 'Book Returned', 'Fine Paid')
        ORDER BY Timestamp DESC
        LIMIT 5
    ");
    $activity_stmt->execute([$student_id]);
    $activities = $activity_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($activities as $activity) {
        $type = 'info';
        if (strpos($activity['Action'], 'Fine') !== false) {
            $type = 'warning';
        }
        
        $notifications[] = [
            'id' => 'activity_' . strtotime($activity['Timestamp']),
            'title' => $activity['Action'],
            'message' => $activity['Details'],
            'type' => $type,
            'category' => 'activity',
            'date' => $activity['Timestamp'],
            'read' => true,
            'action_required' => false
        ];
    }
} catch (PDOException $e) {
    error_log("Activity log fetch error: " . $e->getMessage());
}

// Ensure notifications is an array
if (!is_array($notifications)) {
    $notifications = [];
}

// Sort notifications by date (newest first)
if (count($notifications) > 0) {
    usort($notifications, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
}

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

// Show raw debug diagnostics only when explicitly requested.
$show_notifications_debug = isset($_GET['debug_notifications']) && $_GET['debug_notifications'] === '1';
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

<!-- DEBUG INFO (Visible only in explicit debug mode) -->
<?php if (count($notifications) === 0 && $show_notifications_debug): ?>
<div style="background: #fff3cd; border: 2px solid #ffc107; padding: 15px; margin-bottom: 20px; border-radius: 8px;">
    <h3 style="color: #856404; margin: 0 0 10px 0;">🔍 Debug Info</h3>
    <p style="margin: 5px 0;"><strong>Member No:</strong> <?php echo $member_no ?? 'NOT SET'; ?></p>
    <p style="margin: 5px 0;"><strong>Student ID:</strong> <?php echo $student_id ?? 'NOT SET'; ?></p>
    <p style="margin: 5px 0;"><strong>Total Notifications Found:</strong> <?php echo count($notifications); ?></p>
    <p style="margin: 5px 0; color: #dc3545;"><strong>⚠️ Notification list is empty for this member/context.</strong></p>
    <p style="margin: 5px 0;">Tip: verify rows in <code>Notifications</code> for this <code>MemberNo</code> or global entries (<code>MemberNo IS NULL</code>).</p>
</div>
<?php endif; ?>

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
    <?php if (count($notifications) === 0): ?>
        <div class="empty-state">
            <i class="fas fa-bell-slash"></i>
            <h3>No notifications yet</h3>
            <p>You are all caught up. New alerts will appear here when available.</p>
        </div>
    <?php else: ?>
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
                                <button class="notification-btn" onclick="markAsRead('<?php echo $notification['id']; ?>', <?php echo isset($notification['notification_db_id']) ? $notification['notification_db_id'] : 'null'; ?>)">
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
    <?php endif; ?>
</div>

<script>
    const CSRF_TOKEN = '<?= htmlspecialchars($page_csrf_token) ?>';

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

    function markAsRead(notificationId, dbNotificationId) {
        const notification = document.querySelector(`[data-id="${notificationId}"]`);
        notification.classList.remove('unread');

        // Remove mark read button
        const markReadBtn = notification.querySelector('button[onclick*="markAsRead"]');
        if (markReadBtn) {
            markReadBtn.remove();
        }

        // Update unread count
        updateUnreadCount();

        // If this is an admin notification, update the database
        if (dbNotificationId) {
            fetch('mark_notification_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    notification_id: dbNotificationId,
                    csrf_token: CSRF_TOKEN
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Notification marked as read in database');
                } else {
                    console.error('Failed to mark notification as read:', data.message);
                }
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
            });
        } else {
            console.log('Marking system notification as read (client-side only):', notificationId);
        }
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
