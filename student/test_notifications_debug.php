<?php
// Quick diagnostic to check notifications
require_once '../includes/db_connect.php';

echo "<h2>Diagnostics - Notifications Table</h2>";

try {
    // Check if Notifications table exists and has data
    $stmt = $pdo->query("SELECT * FROM Notifications ORDER BY CreatedDate DESC LIMIT 10");
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Total Notifications in Database: " . count($notifications) . "</h3>";
    
    if (count($notifications) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>MemberNo</th><th>Title</th><th>Message</th><th>Type</th><th>IsRead</th><th>CreatedDate</th></tr>";
        foreach ($notifications as $notif) {
            echo "<tr>";
            echo "<td>" . $notif['NotificationID'] . "</td>";
            echo "<td>" . ($notif['MemberNo'] ?? 'NULL (All)') . "</td>";
            echo "<td>" . htmlspecialchars($notif['Title']) . "</td>";
            echo "<td>" . htmlspecialchars(substr($notif['Message'], 0, 100)) . "...</td>";
            echo "<td>" . $notif['Type'] . "</td>";
            echo "<td>" . $notif['IsRead'] . "</td>";
            echo "<td>" . $notif['CreatedDate'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>No notifications found in database!</p>";
    }
    
    // Check session variables
    echo "<h3>Session Info</h3>";
    session_start();
    echo "<p>Student ID: " . ($_SESSION['student_id'] ?? 'NOT SET') . "</p>";
    echo "<p>Member No: " . ($_SESSION['member_no'] ?? 'NOT SET') . "</p>";
    echo "<p>Logged In: " . (($_SESSION['logged_in'] ?? false) ? 'YES' : 'NO') . "</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
