<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Entry - QR Scanner</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
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
    padding: 20px;
}

.container {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    max-width: 1100px;
    width: 100%;
    margin: 0 auto;
    overflow: hidden;
}

.header {
    background: linear-gradient(135deg, #263c79 0%, #1a2a52 100%);
    color: white;
    padding: 30px;
    text-align: center;
    border-bottom: 4px solid #cfac69;
}

.header h1 {
    font-size: 28px;
    margin-bottom: 5px;
    font-weight: 700;
}

.header p {
    font-size: 14px;
    opacity: 0.9;
}

.content {
    padding: 40px;
}

/* Section Title */
.section-title {
    color: #263c79;
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 25px;
    padding-bottom: 12px;
    border-bottom: 2px solid #cfac69;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    color: #cfac69;
    font-size: 20px;
}

/* Scan Area Container */
.scan-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.scan-group {
    display: flex;
    flex-direction: column;
}

.scan-group label {
    font-weight: 600;
    color: #263c79;
    margin-bottom: 12px;
    font-size: 14px;
}

/* Scan Area with Dashed Border - Matching Circulation */
.scan-area {
    border: 2px dashed #cfac69;
    padding: 15px;
    text-align: center;
    border-radius: 8px;
    background: white;
    position: relative;
    overflow: hidden;
}

.camera-container {
    position: relative;
    width: 100%;
    height: 300px;
    background: #f8f9fa;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 10px;
}

.camera-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    color: #6c757d;
    font-size: 14px;
    text-align: center;
    flex-direction: column;
}

.scan-icon {
    font-size: 48px;
    color: #cfac69;
    margin-bottom: 10px;
}

.scan-text {
    font-size: 14px;
    color: #666;
    margin-top: 10px;
}

/* Camera Video Display */
#reader {
    width: 100%;
    height: 300px;
    border-radius: 4px;
    overflow: hidden;
    display: none; /* Hidden by default, shown when camera starts */
}

#reader video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Scan Controls - Matching Circulation Buttons */
.scan-controls {
    display: flex;
    gap: 8px;
    justify-content: center;
    margin-top: 10px;
}

.btn-scan {
    padding: 8px 16px;
    background-color: #263c79;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s;
    font-family: 'Poppins', sans-serif;
}

.btn-scan:hover {
    background-color: #1e2d5f;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(38, 60, 121, 0.3);
}

.btn-scan:disabled {
    background-color: #6c757d;
    cursor: not-allowed;
    transform: none;
}

.btn-scan i {
    font-size: 14px;
}

.btn-scan-secondary {
    background-color: #6c757d;
}

.btn-scan-secondary:hover {
    background-color: #5a6268;
}

/* Manual Entry Group */
.manual-group {
    display: flex;
    flex-direction: column;
}

.manual-group label {
    font-weight: 600;
    color: #263c79;
    margin-bottom: 12px;
    font-size: 14px;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
    transition: all 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #cfac69;
    box-shadow: 0 0 0 3px rgba(207, 172, 105, 0.1);
}

.btn-primary {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #263c79 0%, #1e2d5f 100%);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 10px;
    transition: all 0.3s;
    font-family: 'Poppins', sans-serif;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(38, 60, 121, 0.4);
}

/* Purpose Dropdown */
.purpose-group {
    margin-top: 20px;
}

.purpose-group label {
    font-weight: 600;
    color: #263c79;
    margin-bottom: 12px;
    font-size: 14px;
    display: block;
}

.purpose-group select {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
    background: white;
    cursor: pointer;
    transition: all 0.3s;
}

.purpose-group select:focus {
    outline: none;
    border-color: #cfac69;
    box-shadow: 0 0 0 3px rgba(207, 172, 105, 0.1);
}

.scan-section, .manual-section {
    display: none;
}

.scan-section.active, .manual-section.active {
    display: block;
}

#qr-reader {
    border: 3px solid #263c79;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 20px;
}

.manual-form {
    max-width: 500px;
    margin: 0 auto;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #374151;
    font-weight: 600;
    font-size: 14px;
}

.form-group input, .form-group select {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 16px;
    font-family: 'Poppins', sans-serif;
    transition: border-color 0.3s;
}

.form-group input:focus, .form-group select:focus {
    outline: none;
    border-color: #263c79;
}

.btn {
    background: #263c79;
    color: white;
    border: none;
    padding: 15px 30px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    width: 100%;
    transition: all 0.3s;
    font-family: 'Poppins', sans-serif;
}

.btn:hover {
    background: #1a2a52;
    transform: translateY(-2px);
}

.btn:disabled {
    background: #9ca3af;
    cursor: not-allowed;
    transform: none;
}

.success-msg, .error-msg {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: none;
    font-weight: 500;
}

.success-msg {
    background: #d1fae5;
    color: #065f46;
    border: 2px solid #10b981;
}

.error-msg {
    background: #fee2e2;
    color: #991b1b;
    border: 2px solid #ef4444;
}

.success-msg.show, .error-msg.show {
    display: block;
}

.member-info {
    background: #f9fafb;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    display: none;
}

.member-info.show {
    display: block;
}

.member-info h3 {
    color: #263c79;
    margin-bottom: 15px;
    font-size: 18px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #e5e7eb;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    color: #6b7280;
    font-weight: 500;
}

.info-value {
    color: #111827;
    font-weight: 600;
}

.recent-visitors {
    margin-top: 30px;
    padding-top: 30px;
    border-top: 2px solid #e5e7eb;
}

.recent-visitors h3 {
    color: #263c79;
    margin-bottom: 15px;
    font-size: 18px;
}

.visitor-item {
    background: #f9fafb;
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.visitor-name {
    font-weight: 600;
    color: #111827;
}

.visitor-time {
    color: #6b7280;
    font-size: 14px;
}

.stats-bar {
    display: flex;
    gap: 15px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.stat-card {
    flex: 1;
    min-width: 150px;
    background: #f0f9ff;
    border: 2px solid #bfdbfe;
    border-radius: 12px;
    padding: 15px;
    text-align: center;
}

.stat-number {
    font-size: 32px;
    font-weight: 700;
    color: #263c79;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 12px;
    color: #6b7280;
    text-transform: uppercase;
    font-weight: 600;
}

@media (max-width: 768px) {
    .content {
        padding: 20px;
    }
    
    .mode-selector {
        flex-direction: column;
    }
    
    .mode-btn {
        max-width: 100%;
    }
}
</style>

<body>
<div class="container">
    <div class="header">
        <h1><i class="fas fa-door-open"></i> Library Entry System</h1>
        <p>Scan your Digital ID or enter your Member Number</p>
    </div>
    
    <div class="content">
        <!-- Stats Bar -->
        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-number" id="todayVisits">0</div>
                <div class="stat-label">Today's Visits</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="activeVisitors">0</div>
                <div class="stat-label">Active Now</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="weekVisits">0</div>
                <div class="stat-label">This Week</div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <div class="success-msg" id="successMsg"></div>
        <div class="error-msg" id="errorMsg"></div>

        <!-- Main Scanner Interface (Matching Circulation) -->
        <div class="section-title">
            <i class="fas fa-user-check"></i>
            Step 1: Scan or Search Member
        </div>

        <div class="scan-container">
            <!-- QR Scanner Group -->
            <div class="scan-group">
                <label for="memberScan">Member QR Code / ID Card</label>
                <div class="scan-area">
                    <div class="camera-container">
                        <div class="camera-placeholder" id="cameraPlaceholder">
                            <div>
                                <i class="fas fa-qrcode scan-icon"></i>
                                <div class="scan-text">Position member QR code or ID card here</div>
                            </div>
                        </div>
                        <div id="reader"></div>
                    </div>
                    <div class="scan-controls">
                        <button class="btn-scan" onclick="startScanner()" id="startBtn">
                            <i class="fas fa-camera"></i>
                            Start Camera
                        </button>
                        <button class="btn-scan btn-scan-secondary" onclick="stopScanner()" id="stopBtn" disabled>
                            <i class="fas fa-stop"></i>
                            Stop
                        </button>
                    </div>
                </div>
            </div>

            <!-- Manual Entry Group -->
            <div class="manual-group">
                <label for="memberNo">Or Enter Member Number</label>
                <input type="text" id="memberNo" class="form-control" placeholder="Enter member number or PRN...">
                <button type="button" class="btn-primary" onclick="searchMember()">
                    <i class="fas fa-search"></i>
                    Search Member
                </button>
            </div>
        </div>

        <!-- Member Info Display -->
        <div class="member-info" id="memberInfo">
            <h3>Member Information</h3>
            <div class="info-row">
                <span class="info-label">Name:</span>
                <span class="info-value" id="infoName">-</span>
            </div>
            <div class="info-row">
                <span class="info-label">Member No:</span>
                <span class="info-value" id="infoMemberNo">-</span>
            </div>
            <div class="info-row">
                <span class="info-label">Branch/Course:</span>
                <span class="info-value" id="infoBranch">-</span>
            </div>
            <div class="info-row">
                <span class="info-label">Entry Time:</span>
                <span class="info-value" id="infoTime">-</span>
            </div>
        </div>

        <!-- Recent Visitors -->
        <div class="recent-visitors">
            <h3><i class="fas fa-clock"></i> Recent Check-ins</h3>
            <div id="recentVisitorsList">
                <!-- Dynamic content -->
            </div>
        </div>
    </div>
</div>

<script>
let html5QrCode;
let scannerActive = false;

// Start scanner function
function startScanner() {
    const placeholder = document.getElementById('cameraPlaceholder');
    const reader = document.getElementById('reader');
    const startBtn = document.getElementById('startBtn');
    const stopBtn = document.getElementById('stopBtn');
    
    // Hide placeholder, show reader
    placeholder.style.display = 'none';
    reader.style.display = 'block';
    
    // Update buttons
    startBtn.disabled = true;
    stopBtn.disabled = false;
    
    // Initialize scanner
    html5QrCode = new Html5Qrcode("reader");
    const config = { fps: 10, qrbox: { width: 250, height: 250 } };
    
    html5QrCode.start(
        { facingMode: "environment" },
        config,
        onScanSuccess,
        onScanFailure
    ).then(() => {
        scannerActive = true;
    }).catch(err => {
        console.error("QR Scanner error:", err);
        showError("Camera access denied. Please check browser permissions or use manual entry.");
        stopScanner();
    });
}

// Stop scanner function
function stopScanner() {
    const placeholder = document.getElementById('cameraPlaceholder');
    const reader = document.getElementById('reader');
    const startBtn = document.getElementById('startBtn');
    const stopBtn = document.getElementById('stopBtn');
    
    if (html5QrCode && scannerActive) {
        html5QrCode.stop().then(() => {
            scannerActive = false;
            reader.style.display = 'none';
            placeholder.style.display = 'flex';
            startBtn.disabled = false;
            stopBtn.disabled = true;
        }).catch(err => {
            console.error("Stop error:", err);
        });
    } else {
        reader.style.display = 'none';
        placeholder.style.display = 'flex';
        startBtn.disabled = false;
        stopBtn.disabled = true;
    }
}

function onScanSuccess(decodedText, decodedResult) {
    console.log(`QR Code detected: ${decodedText}`);
    
    // Stop scanning temporarily
    if (html5QrCode && scannerActive) {
        html5QrCode.pause();
    }
    
    // Process QR code (format: MemberNo_Year or PRN_Year)
    const memberIdentifier = decodedText.split('_')[0];
    checkIn(memberIdentifier, 'QR Scan');
    
    // Resume after 3 seconds
    setTimeout(() => {
        if (html5QrCode && scannerActive) {
            html5QrCode.resume();
        }
    }, 3000);
}

function onScanFailure(error) {
    // Silent failure - QR not detected yet
}

// Search member by manual entry
function searchMember() {
    const memberNo = document.getElementById('memberNo').value.trim();
    
    if (!memberNo) {
        showError('Please enter a member number');
        return;
    }
    
    checkIn(memberNo, 'Manual Entry');
}

// Check-in function - simplified, no purpose needed
async function checkIn(memberIdentifier, method) {
    try {
        const response = await fetch('api/checkin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                member_identifier: memberIdentifier,
                entry_method: method,
                purpose: 'Library Visit'  // Always use default
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess(data.message || 'Check-in successful!');
            displayMemberInfo(data.member);
            refreshStats();
            refreshRecentVisitors();
            
            // Clear form
            document.getElementById('manualForm').reset();
        } else {
            showError(data.message || 'Check-in failed. Please try again.');
        }
    } catch (error) {
        console.error('Check-in error:', error);
        showError('Network error. Please check your connection.');
    }
}

function displayMemberInfo(member) {
    document.getElementById('infoName').textContent = member.name;
    document.getElementById('infoMemberNo').textContent = member.member_no;
    document.getElementById('infoBranch').textContent = member.branch || member.course || 'N/A';
    document.getElementById('infoTime').textContent = new Date().toLocaleString();
    document.getElementById('memberInfo').classList.add('show');
    
    // Hide after 5 seconds
    setTimeout(() => {
        document.getElementById('memberInfo').classList.remove('show');
    }, 5000);
}

function showSuccess(message) {
    const elem = document.getElementById('successMsg');
    elem.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
    elem.classList.add('show');
    setTimeout(() => elem.classList.remove('show'), 4000);
}

function showError(message) {
    const elem = document.getElementById('errorMsg');
    elem.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
    elem.classList.add('show');
    setTimeout(() => elem.classList.remove('show'), 4000);
}

async function refreshStats() {
    try {
        const response = await fetch('api/footfall-stats.php');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('todayVisits').textContent = data.stats.today_visits || 0;
            document.getElementById('activeVisitors').textContent = data.stats.active_visitors || 0;
            document.getElementById('weekVisits').textContent = data.stats.week_visits || 0;
        }
    } catch (error) {
        console.error('Stats refresh error:', error);
    }
}

async function refreshRecentVisitors() {
    try {
        const response = await fetch('api/recent-visitors.php?limit=5');
        const data = await response.json();
        
        if (data.success && data.visitors) {
            const container = document.getElementById('recentVisitorsList');
            container.innerHTML = data.visitors.map(v => `
                <div class="visitor-item">
                    <span class="visitor-name">${v.name}</span>
                    <span class="visitor-time">${v.time}</span>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Recent visitors refresh error:', error);
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    refreshStats();
    refreshRecentVisitors();
    
    // Auto-refresh stats every 30 seconds
    setInterval(refreshStats, 30000);
    setInterval(refreshRecentVisitors, 30000);
});
</script>
</body>
</html>
