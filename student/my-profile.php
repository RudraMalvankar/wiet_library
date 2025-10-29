<?php
// My Profile Content - Student profile management and settings
// This file will be included in the main content area

// Start session and check authentication
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: student_login.php');
    exit();
}

// Include database connection
require_once '../includes/db_connect.php';

// Session variables for student info
$student_id = $_SESSION['student_id'] ?? null;
$member_no = $_SESSION['member_no'] ?? null;

// Fetch real student profile data from database
$student_profile = [
    'personal_info' => [],
    'academic_info' => [],
    'library_stats' => [],
    'preferences' => []
];

$recent_activity = [];

try {
    // Fetch personal and academic info
    $stmt = $pdo->prepare("
        SELECT 
            s.*,
            m.MemberName,
            m.Phone,
            m.Email as MemberEmail,
            m.AdmissionDate,
            m.Status,
            m.BooksIssued
        FROM Student s
        INNER JOIN Member m ON s.MemberNo = m.MemberNo
        WHERE s.StudentID = ?
        LIMIT 1
    ");
    $stmt->execute([$student_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($data) {
        $student_profile['personal_info'] = [
            'full_name' => $data['MemberName'],
            'student_id' => $data['PRN'] ?? $data['StudentID'],
            'email' => $data['Email'] ?? $data['MemberEmail'],
            'phone' => $data['Mobile'] ?? $data['Phone'],
            'date_of_birth' => $data['DOB'] ?? 'N/A',
            'gender' => $data['Gender'] ?? 'N/A',
            'blood_group' => $data['BloodGroup'] ?? 'N/A',
            'address' => $data['Address'] ?? 'N/A'
        ];
        
        $student_profile['academic_info'] = [
            'course' => $data['CourseName'] ?? 'N/A',
            'branch' => $data['Branch'] ?? 'N/A',
            'year' => '', // Calculate from admission date
            'semester' => '', // Calculate from admission date
            'roll_number' => $data['PRN'] ?? 'N/A',
            'admission_year' => date('Y', strtotime($data['AdmissionDate'])),
            'expected_graduation' => '' // Calculate
        ];
        
        // Fetch library statistics
        $stats_stmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT c.CirculationID) as total_borrowed,
                COUNT(DISTINCT CASE WHEN c.Status = 'Active' THEN c.CirculationID END) as current_borrowed,
                COALESCE(SUM(f.Amount), 0) as total_fines
            FROM Student s
            LEFT JOIN Member m ON s.MemberNo = m.MemberNo
            LEFT JOIN Circulation c ON m.MemberNo = c.MemberNo
            LEFT JOIN FinePayments f ON m.MemberNo = f.MemberNo
            WHERE s.StudentID = ?
            GROUP BY s.StudentID
        ");
        $stats_stmt->execute([$student_id]);
        $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Fetch footfall count
        $footfall_stmt = $pdo->prepare("
            SELECT COUNT(*) as total_visits 
            FROM footfall 
            WHERE MemberNo = ?
        ");
        $footfall_stmt->execute([$member_no]);
        $footfall = $footfall_stmt->fetch(PDO::FETCH_ASSOC);
        
        $student_profile['library_stats'] = [
            'membership_since' => $data['AdmissionDate'] ?? 'N/A',
            'total_books_borrowed' => $stats['total_borrowed'] ?? 0,
            'current_borrowed' => $stats['current_borrowed'] ?? 0,
            'total_visits' => $footfall['total_visits'] ?? 0,
            'total_fines_paid' => $stats['total_fines'] ?? 0,
            'favorite_sections' => [$data['Branch']] // Based on branch
        ];
    }
    
    // Fetch recent activity
    $activity_stmt = $pdo->prepare("
        SELECT 
            'Borrowed' as action,
            b.Title as details,
            c.IssueDate as date,
            'borrow' as type
        FROM Circulation c
        INNER JOIN Holding h ON c.AccNo = h.AccNo
        INNER JOIN Books b ON h.CallNo = b.CallNo
        WHERE c.MemberNo = ?
        
        UNION ALL
        
        SELECT 
            'Returned' as action,
            b.Title as details,
            r.ReturnDate as date,
            'return' as type
        FROM `Return` r
        INNER JOIN Circulation c ON r.CirculationID = c.CirculationID
        INNER JOIN Holding h ON c.AccNo = h.AccNo
        INNER JOIN Books b ON h.CallNo = b.CallNo
        WHERE c.MemberNo = ?
        
        ORDER BY date DESC
        LIMIT 10
    ");
    $activity_stmt->execute([$member_no, $member_no]);
    $recent_activity = $activity_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Profile fetch error: " . $e->getMessage());
    // Use session data as fallback
    $student_profile['personal_info'] = [
        'full_name' => $_SESSION['student_name'] ?? 'Student',
        'student_id' => $_SESSION['student_prn'] ?? $student_id,
        'email' => $_SESSION['student_email'] ?? 'N/A',
        'phone' => $_SESSION['student_mobile'] ?? 'N/A',
        'date_of_birth' => 'N/A',
        'gender' => 'N/A',
        'address' => 'N/A'
    ];
    
    $student_profile['academic_info'] = [
        'course' => $_SESSION['student_course'] ?? 'N/A',
        'branch' => $_SESSION['student_branch'] ?? 'N/A',
        'year' => 'N/A',
        'semester' => 'N/A',
        'roll_number' => $_SESSION['student_prn'] ?? 'N/A',
        'admission_year' => date('Y'),
        'expected_graduation' => 'N/A'
    ];
    
    $student_profile['library_stats'] = [
        'membership_since' => date('Y-m-d'),
        'total_books_borrowed' => 0,
        'current_borrowed' => $_SESSION['books_issued'] ?? 0,
        'total_visits' => 0,
        'total_fines_paid' => 0,
        'favorite_sections' => []
    ];
}

$student_profile['preferences'] = [
    'notification_email' => true,
    'notification_sms' => false,
    'reminder_before_due' => 2,
    'preferred_language' => 'English',
    'privacy_profile' => 'Private'
];
?>
    ['action' => 'Downloaded "Digital Signal Processing" eBook', 'date' => '2025-09-08', 'type' => 'download']
];
?>

<style>
    .profile-header {
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #cfac69;
    }

    .profile-title {
        color: #263c79;
        font-size: 28px;
        font-weight: 700;
        margin: 0 0 8px 0;
    }

    .profile-subtitle {
        color: #666;
        font-size: 16px;
        margin: 0;
    }

    .profile-layout {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 25px;
        margin-bottom: 30px;
    }

    .profile-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .card-title {
        color: #263c79;
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
        padding-bottom: 10px;
        border-bottom: 1px solid #f0f0f0;
    }

    .profile-avatar {
        text-align: center;
        margin-bottom: 20px;
    }

    .avatar-circle {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #263c79, #cfac69);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        color: white;
        font-size: 48px;
        font-weight: 700;
        letter-spacing: 2px;
    }

    .student-name {
        font-size: 20px;
        font-weight: 600;
        color: #263c79;
        margin-bottom: 5px;
    }

    .student-id {
        font-size: 14px;
        color: #666;
        margin-bottom: 15px;
    }

    .membership-badge {
        background: #d4edda;
        color: #155724;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }

    .info-grid {
        display: grid;
        gap: 15px;
    }

    .info-row {
        display: grid;
        grid-template-columns: 120px 1fr;
        gap: 10px;
        padding: 12px 0;
        border-bottom: 1px solid #f8f9fa;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-size: 13px;
        color: #666;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-value {
        font-size: 14px;
        color: #263c79;
        font-weight: 500;
    }

    .stats-overview {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-bottom: 20px;
    }

    .stat-item {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 6px;
        text-align: center;
    }

    .stat-number {
        font-size: 20px;
        font-weight: 700;
        color: #263c79;
        margin-bottom: 3px;
    }

    .stat-text {
        font-size: 11px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .favorite-sections {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 10px;
    }

    .section-tag {
        background: #e3f2fd;
        color: #1976d2;
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 500;
    }

    .tabs-container {
        margin-bottom: 20px;
    }

    .tabs-nav {
        display: flex;
        border-bottom: 2px solid #f0f0f0;
        margin-bottom: 20px;
    }

    .tab-btn {
        background: none;
        border: none;
        padding: 12px 20px;
        font-size: 14px;
        font-weight: 500;
        color: #666;
        cursor: pointer;
        border-bottom: 2px solid transparent;
        transition: all 0.3s ease;
    }

    .tab-btn.active {
        color: #263c79;
        border-bottom-color: #cfac69;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-bottom: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-label {
        font-size: 13px;
        color: #263c79;
        font-weight: 500;
        margin-bottom: 5px;
    }

    .form-input, .form-select, .form-textarea {
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        transition: border-color 0.3s ease;
    }

    .form-input:focus, .form-select:focus, .form-textarea:focus {
        outline: none;
        border-color: #cfac69;
        box-shadow: 0 0 0 2px rgba(207, 172, 105, 0.1);
    }

    .form-textarea {
        resize: vertical;
        min-height: 80px;
    }

    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
    }

    .checkbox-group input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: #cfac69;
    }

    .checkbox-label {
        font-size: 14px;
        color: #263c79;
    }

    .btn-group {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: #263c79;
        color: white;
    }

    .btn-primary:hover {
        background: #1e2f5a;
    }

    .btn-secondary {
        background: transparent;
        color: #263c79;
        border: 1px solid #263c79;
    }

    .btn-secondary:hover {
        background: #263c79;
        color: white;
    }

    .activity-list {
        max-height: 300px;
        overflow-y: auto;
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }

    .activity-icon.borrow {
        background: #e8f5e8;
        color: #2e7d32;
    }

    .activity-icon.return {
        background: #e3f2fd;
        color: #1976d2;
    }

    .activity-icon.renew {
        background: #fff3e0;
        color: #f57c00;
    }

    .activity-icon.settings {
        background: #f3e5f5;
        color: #7b1fa2;
    }

    .activity-icon.download {
        background: #e0f2f1;
        color: #00796b;
    }

    .activity-details {
        flex: 1;
    }

    .activity-text {
        font-size: 14px;
        color: #263c79;
        margin-bottom: 3px;
    }

    .activity-date {
        font-size: 12px;
        color: #666;
    }

    @media (max-width: 968px) {
        .profile-layout {
            grid-template-columns: 1fr;
        }
        
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .stats-overview {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (max-width: 768px) {
        .tabs-nav {
            flex-wrap: wrap;
        }
        
        .tab-btn {
            flex: 1;
            min-width: 120px;
        }
        
        .stats-overview {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .btn-group {
            flex-direction: column;
        }
    }
</style>

<div class="profile-header">
    <h1 class="profile-title">My Profile</h1>
    <p class="profile-subtitle">Manage your personal information, academic details, and library preferences</p>
</div>

<div class="profile-layout">
    <!-- Left Sidebar - Profile Summary -->
    <div class="profile-card">
        <div class="profile-avatar">
            <div class="avatar-circle">
                <?php echo strtoupper(substr($student_profile['personal_info']['full_name'], 0, 2)); ?>
            </div>
            <div class="student-name"><?php echo htmlspecialchars($student_profile['personal_info']['full_name']); ?></div>
            <div class="student-id"><?php echo htmlspecialchars($student_profile['personal_info']['student_id']); ?></div>
            <div class="membership-badge">
                Active Member since <?php echo date('M Y', strtotime($student_profile['library_stats']['membership_since'])); ?>
            </div>
        </div>

        <div class="stats-overview">
            <div class="stat-item">
                <div class="stat-number"><?php echo $student_profile['library_stats']['total_books_borrowed']; ?></div>
                <div class="stat-text">Books Borrowed</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $student_profile['library_stats']['current_borrowed']; ?></div>
                <div class="stat-text">Currently Issued</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $student_profile['library_stats']['total_visits']; ?></div>
                <div class="stat-text">Library Visits</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">₹<?php echo $student_profile['library_stats']['total_fines_paid']; ?></div>
                <div class="stat-text">Fines Paid</div>
            </div>
        </div>

        <div style="margin-top: 20px;">
            <div class="info-label">Favorite Sections</div>
            <div class="favorite-sections">
                <?php foreach ($student_profile['library_stats']['favorite_sections'] as $section): ?>
                    <span class="section-tag"><?php echo htmlspecialchars($section); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Right Content - Detailed Information -->
    <div class="profile-card">
        <div class="tabs-container">
            <div class="tabs-nav">
                <button class="tab-btn active" onclick="switchTab('personal')">Personal Info</button>
                <button class="tab-btn" onclick="switchTab('academic')">Academic Details</button>
                <button class="tab-btn" onclick="switchTab('preferences')">Preferences</button>
                <button class="tab-btn" onclick="switchTab('activity')">Recent Activity</button>
            </div>

            <!-- Personal Information Tab -->
            <div id="personal-tab" class="tab-content active">
                <h3 class="card-title">
                    <i class="fas fa-user"></i>
                    Personal Information
                </h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-input" value="<?php echo htmlspecialchars($student_profile['personal_info']['full_name']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Student ID</label>
                        <input type="text" class="form-input" value="<?php echo htmlspecialchars($student_profile['personal_info']['student_id']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-input" value="<?php echo htmlspecialchars($student_profile['personal_info']['email']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" class="form-input" value="<?php echo htmlspecialchars($student_profile['personal_info']['phone']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" class="form-input" value="<?php echo $student_profile['personal_info']['date_of_birth']; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Gender</label>
                        <select class="form-select">
                            <option value="Male" <?php echo $student_profile['personal_info']['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $student_profile['personal_info']['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo $student_profile['personal_info']['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Address</label>
                        <textarea class="form-textarea"><?php echo htmlspecialchars($student_profile['personal_info']['address']); ?></textarea>
                    </div>
                </div>
                <div class="btn-group">
                    <button class="btn btn-primary" onclick="savePersonalInfo()">Save Changes</button>
                    <button class="btn btn-secondary" onclick="resetForm()">Reset</button>
                </div>
            </div>

            <!-- Academic Details Tab -->
            <div id="academic-tab" class="tab-content">
                <h3 class="card-title">
                    <i class="fas fa-graduation-cap"></i>
                    Academic Information
                </h3>
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">Course</div>
                        <div class="info-value"><?php echo htmlspecialchars($student_profile['academic_info']['course']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Branch</div>
                        <div class="info-value"><?php echo htmlspecialchars($student_profile['academic_info']['branch']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Current Year</div>
                        <div class="info-value"><?php echo htmlspecialchars($student_profile['academic_info']['year']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Semester</div>
                        <div class="info-value"><?php echo htmlspecialchars($student_profile['academic_info']['semester']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Roll Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($student_profile['academic_info']['roll_number']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Admission Year</div>
                        <div class="info-value"><?php echo htmlspecialchars($student_profile['academic_info']['admission_year']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Expected Graduation</div>
                        <div class="info-value"><?php echo htmlspecialchars($student_profile['academic_info']['expected_graduation']); ?></div>
                    </div>
                </div>
            </div>

            <!-- Preferences Tab -->
            <div id="preferences-tab" class="tab-content">
                <h3 class="card-title">
                    <i class="fas fa-cog"></i>
                    Library Preferences
                </h3>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">Notification Settings</label>
                        <div class="checkbox-group">
                            <input type="checkbox" id="email-notifications" <?php echo $student_profile['preferences']['notification_email'] ? 'checked' : ''; ?>>
                            <label for="email-notifications" class="checkbox-label">Email Notifications</label>
                        </div>
                        <div class="checkbox-group">
                            <input type="checkbox" id="sms-notifications" <?php echo $student_profile['preferences']['notification_sms'] ? 'checked' : ''; ?>>
                            <label for="sms-notifications" class="checkbox-label">SMS Notifications</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Reminder Before Due (days)</label>
                        <select class="form-select">
                            <option value="1" <?php echo $student_profile['preferences']['reminder_before_due'] == 1 ? 'selected' : ''; ?>>1 Day</option>
                            <option value="2" <?php echo $student_profile['preferences']['reminder_before_due'] == 2 ? 'selected' : ''; ?>>2 Days</option>
                            <option value="3" <?php echo $student_profile['preferences']['reminder_before_due'] == 3 ? 'selected' : ''; ?>>3 Days</option>
                            <option value="7" <?php echo $student_profile['preferences']['reminder_before_due'] == 7 ? 'selected' : ''; ?>>1 Week</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Preferred Language</label>
                        <select class="form-select">
                            <option value="English" <?php echo $student_profile['preferences']['preferred_language'] == 'English' ? 'selected' : ''; ?>>English</option>
                            <option value="Hindi" <?php echo $student_profile['preferences']['preferred_language'] == 'Hindi' ? 'selected' : ''; ?>>Hindi</option>
                            <option value="Marathi" <?php echo $student_profile['preferences']['preferred_language'] == 'Marathi' ? 'selected' : ''; ?>>Marathi</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Profile Privacy</label>
                        <select class="form-select">
                            <option value="Public" <?php echo $student_profile['preferences']['privacy_profile'] == 'Public' ? 'selected' : ''; ?>>Public</option>
                            <option value="Private" <?php echo $student_profile['preferences']['privacy_profile'] == 'Private' ? 'selected' : ''; ?>>Private</option>
                        </select>
                    </div>
                </div>
                <div class="btn-group">
                    <button class="btn btn-primary" onclick="savePreferences()">Save Preferences</button>
                    <button class="btn btn-secondary" onclick="resetPreferences()">Reset to Default</button>
                </div>
            </div>

            <!-- Recent Activity Tab -->
            <div id="activity-tab" class="tab-content">
                <h3 class="card-title">
                    <i class="fas fa-history"></i>
                    Recent Activity
                </h3>
                <div class="activity-list">
                    <?php foreach ($recent_activity as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon <?php echo $activity['type']; ?>">
                                <?php
                                $icons = [
                                    'borrow' => 'fas fa-plus',
                                    'return' => 'fas fa-undo',
                                    'renew' => 'fas fa-refresh',
                                    'settings' => 'fas fa-cog',
                                    'download' => 'fas fa-download'
                                ];
                                ?>
                                <i class="<?php echo $icons[$activity['type']]; ?>"></i>
                            </div>
                            <div class="activity-details">
                                <div class="activity-text"><?php echo htmlspecialchars($activity['action']); ?></div>
                                <div class="activity-date"><?php echo date('M j, Y', strtotime($activity['date'])); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function switchTab(tabName) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Remove active class from all tab buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Show selected tab content
        document.getElementById(tabName + '-tab').classList.add('active');
        
        // Add active class to clicked button
        event.target.classList.add('active');
    }

    function savePersonalInfo() {
        // In a real implementation, this would send data to the server
        alert('Personal information updated successfully!');
    }

    function resetForm() {
        // Reset form to original values
        location.reload();
    }

    function savePreferences() {
        // In a real implementation, this would save preferences to the server
        alert('Preferences saved successfully!');
    }

    function resetPreferences() {
        // Reset preferences to default values
        if (confirm('Are you sure you want to reset all preferences to default values?')) {
            // Reset logic here
            alert('Preferences reset to default values.');
        }
    }
</script>
