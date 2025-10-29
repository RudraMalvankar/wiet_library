<?php
// Student Self Check-in Page
session_start();

// Check if logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: student_login.php');
    exit();
}

$student_name = $_SESSION['student_name'] ?? 'Student';
$member_no = $_SESSION['member_no'] ?? null;
$student_id = $_SESSION['student_id'] ?? null;

require_once '../includes/db_connect.php';

// Check if already checked in today
$isCheckedIn = false;
$currentEntry = null;

try {
    $stmt = $pdo->prepare("
        SELECT FootfallID, EntryTime 
        FROM footfall 
        WHERE MemberNo = :member_no 
        AND DATE(EntryTime) = CURDATE()
        AND Status = 'Active'
        LIMIT 1
    ");
    $stmt->execute(['member_no' => $member_no]);
    $currentEntry = $stmt->fetch(PDO::FETCH_ASSOC);
    $isCheckedIn = !empty($currentEntry);
} catch (PDOException $e) {
    error_log('Check-in status error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Check-in - Student Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #263c79 0%, #1a2a52 100%);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

.container {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    max-width: 600px;
    width: 100%;
    overflow: hidden;
}

.header {
    background: linear-gradient(135deg, #263c79 0%, #1a2a52 100%);
    color: white;
    padding: 40px 30px;
    text-align: center;
}

.header h1 {
    font-size: 32px;
    margin-bottom: 10px;
    font-weight: 700;
}

.header p {
    font-size: 16px;
    opacity: 0.9;
}

.content {
    padding: 40px;
}

.status-card {
    background: #f9fafb;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 30px;
    text-align: center;
    margin-bottom: 30px;
}

.status-card.checked-in {
    background: #d1fae5;
    border-color: #10b981;
}

.status-card.checked-out {
    background: #dbeafe;
    border-color: #3b82f6;
}

.status-icon {
    font-size: 64px;
    margin-bottom: 20px;
}

.status-icon.in {
    color: #10b981;
}

.status-icon.out {
    color: #3b82f6;
}

.status-title {
    font-size: 24px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 10px;
}

.status-time {
    font-size: 16px;
    color: #6b7280;
    margin-bottom: 5px;
}

.status-duration {
    font-size: 14px;
    color: #9ca3af;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    margin-bottom: 10px;
    color: #374151;
    font-weight: 600;
    font-size: 14px;
}

.form-group select {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 16px;
    font-family: 'Poppins', sans-serif;
    transition: border-color 0.3s;
}

.form-group select:focus {
    outline: none;
    border-color: #263c79;
}

.btn {
    width: 100%;
    padding: 16px 24px;
    border: none;
    border-radius: 10px;
    font-size: 18px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    font-family: 'Poppins', sans-serif;
}

.btn-primary {
    background: #263c79;
    color: white;
}

.btn-primary:hover {
    background: #1a2a52;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(38, 60, 121, 0.4);
}

.btn-danger {
    background: #ef4444;
    color: white;
}

.btn-danger:hover {
    background: #dc2626;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
}

.btn:disabled {
    background: #9ca3af;
    cursor: not-allowed;
    transform: none;
}

.btn i {
    margin-right: 8px;
}

.alert {
    padding: 16px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-weight: 500;
    display: none;
}

.alert.show {
    display: block;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 2px solid #10b981;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 2px solid #ef4444;
}

.back-link {
    text-align: center;
    margin-top: 20px;
}

.back-link a {
    color: #263c79;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s;
}

.back-link a:hover {
    color: #1a2a52;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-bottom: 25px;
}

.info-item {
    background: #f9fafb;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
}

.info-label {
    font-size: 12px;
    color: #6b7280;
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 5px;
}

.info-value {
    font-size: 20px;
    color: #111827;
    font-weight: 700;
}
</style>

<body>
<div class="container">
    <div class="header">
        <h1><i class="fas fa-door-open"></i> Library Check-in</h1>
        <p>Welcome, <?php echo htmlspecialchars($student_name); ?>!</p>
    </div>
    
    <div class="content">
        <div class="alert alert-success" id="successAlert"></div>
        <div class="alert alert-error" id="errorAlert"></div>
        
        <?php if ($isCheckedIn): ?>
            <div class="status-card checked-in">
                <div class="status-icon in">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="status-title">You're Checked In</div>
                <div class="status-time">
                    Entry Time: <?php echo date('g:i A', strtotime($currentEntry['EntryTime'])); ?>
                </div>
                <div class="status-duration" id="durationDisplay">
                    Duration: Calculating...
                </div>
            </div>
            
            <button class="btn btn-danger" onclick="checkOut()">
                <i class="fas fa-sign-out-alt"></i> Check Out
            </button>
        <?php else: ?>
            <div class="status-card checked-out">
                <div class="status-icon out">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="status-title">Ready to Check In?</div>
                <div class="status-time">
                    Current Time: <span id="currentTime"><?php echo date('g:i A'); ?></span>
                </div>
            </div>
            
            <form id="checkinForm">
                <div class="form-group">
                    <label for="purpose">Purpose of Visit</label>
                    <select id="purpose" required>
                        <option value="Library Visit">Library Visit</option>
                        <option value="Study">Study</option>
                        <option value="Research">Research</option>
                        <option value="Borrow Books">Borrow Books</option>
                        <option value="Return Books">Return Books</option>
                        <option value="Reading Room">Reading Room</option>
                        <option value="Digital Resources">Digital Resources</option>
                        <option value="Group Study">Group Study</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Check In Now
                </button>
            </form>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>
</div>

<script>
const memberNo = <?php echo json_encode($member_no); ?>;
const entryTime = <?php echo json_encode($currentEntry['EntryTime'] ?? null); ?>;
const isCheckedIn = <?php echo json_encode($isCheckedIn); ?>;

// Update current time
setInterval(() => {
    const elem = document.getElementById('currentTime');
    if (elem) {
        elem.textContent = new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
    }
}, 1000);

// Update duration if checked in
if (isCheckedIn && entryTime) {
    function updateDuration() {
        const start = new Date(entryTime);
        const now = new Date();
        const diff = now - start;
        
        const hours = Math.floor(diff / 3600000);
        const minutes = Math.floor((diff % 3600000) / 60000);
        
        document.getElementById('durationDisplay').textContent = 
            `Duration: ${hours}h ${minutes}m`;
    }
    
    updateDuration();
    setInterval(updateDuration, 60000); // Update every minute
}

// Check-in form submission
document.getElementById('checkinForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const purpose = document.getElementById('purpose').value;
    
    try {
        const response = await fetch('../footfall/api/checkin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                member_identifier: memberNo,
                entry_method: 'Student Portal',
                purpose: purpose
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess(data.message);
            setTimeout(() => location.reload(), 2000);
        } else {
            showError(data.message);
        }
    } catch (error) {
        showError('Network error. Please try again.');
    }
});

// Check-out function
async function checkOut() {
    if (!confirm('Are you sure you want to check out?')) return;
    
    try {
        const response = await fetch('../footfall/api/checkout.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                member_identifier: memberNo
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess(data.message + ' Duration: ' + data.duration);
            setTimeout(() => location.reload(), 2000);
        } else {
            showError(data.message);
        }
    } catch (error) {
        showError('Network error. Please try again.');
    }
}

function showSuccess(message) {
    const elem = document.getElementById('successAlert');
    elem.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
    elem.classList.add('show');
    setTimeout(() => elem.classList.remove('show'), 5000);
}

function showError(message) {
    const elem = document.getElementById('errorAlert');
    elem.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
    elem.classList.add('show');
    setTimeout(() => elem.classList.remove('show'), 5000);
}
</script>
</body>
</html>

