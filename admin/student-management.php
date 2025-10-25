<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'] ?? 1;
$admin_name = $_SESSION['admin_name'] ?? 'Admin User';

// Fetch statistics from database
try {
    // Total students
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Student");
    $total_students = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Active students (members with Status = 'Active')
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM Student s
        INNER JOIN Member m ON s.MemberNo = m.MemberNo
        WHERE m.Status = 'Active'
    ");
    $active_students = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Students by branch
    $stmt = $pdo->query("SELECT Branch, COUNT(*) as count FROM Student GROUP BY Branch ORDER BY count DESC");
    $students_by_branch = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Students with issued books
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT s.StudentID) as total
        FROM Student s
        INNER JOIN Member m ON s.MemberNo = m.MemberNo
        WHERE m.BooksIssued > 0
    ");
    $students_with_books = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Expired memberships (ValidTill < TODAY)
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM Student 
        WHERE ValidTill IS NOT NULL AND ValidTill < CURDATE()
    ");
    $expired_memberships = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
} catch (PDOException $e) {
    error_log("Student stats error: " . $e->getMessage());
    $total_students = $active_students = $students_with_books = $expired_memberships = 0;
    $students_by_branch = [];
}

// Fetch all branches for dropdown
try {
    $stmt = $pdo->query("SELECT DISTINCT Branch FROM Student WHERE Branch IS NOT NULL AND Branch != '' ORDER BY Branch");
    $branches = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log("Branches fetch error: " . $e->getMessage());
    $branches = ['Computer Engineering', 'Mechanical Engineering', 'Electronics Engineering', 'Civil Engineering', 'Information Technology'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .student-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #cfac69;
        }

        .student-title {
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
            .student-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .student-title {
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

        .tabs-container {
            margin-bottom: 20px;
        }

        .tab-buttons {
            display: flex;
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 20px;
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

        .add-student-section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            border: 1px solid #e9ecef;
        }

        .add-student-section .section-title {
            color: #263c79;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #cfac69;
            display: flex;
            align-items: center;
            gap: 10px;
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

        .students-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .students-table th {
            background-color: #263c79;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .students-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
        }

        .students-table tr:hover {
            background-color: rgba(207, 172, 105, 0.1);
        }

        .action-links {
            display: flex;
            gap: 8px;
        }

        .action-links a {
            padding: 4px 8px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
        }

        .btn-view {
            background-color: #17a2b8;
            color: white;
        }

        .btn-edit {
            background-color: #ffc107;
            color: #212529;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-suspended {
            background-color: #fff3cd;
            color: #856404;
        }

        /* Modal Styles */
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
            /* Add top margin to clear navbar */
            position: relative;
            z-index: 1001;
            /* Ensure modal is above navbar */
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
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
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

        .photo-upload {
            text-align: center;
            border: 2px dashed #cfac69;
            padding: 20px;
            border-radius: 6px;
            background: white;
        }

        .photo-preview {
            width: 120px;
            height: 150px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            overflow: hidden;
        }

        .photo-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }

        .qr-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        .qr-preview {
            text-align: center;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            background: white;
        }

        .qr-code {
            width: 150px;
            height: 150px;
            border: 1px solid #ddd;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
        }

        .modal-footer {
            padding: 15px 25px;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .form-actions {
            padding: 20px 0 0 0;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
        }

        .page-link {
            padding: 8px 12px;
            border: 1px solid #ddd;
            color: #263c79;
            text-decoration: none;
            border-radius: 4px;
        }

        .page-link:hover,
        .page-link.active {
            background-color: #263c79;
            color: white;
        }

        @media (max-width: 767px) {
            .search-row {
                flex-direction: column;
            }

            .form-group {
                min-width: 100%;
            }

            .students-table {
                font-size: 12px;
            }

            .tab-buttons {
                overflow-x: auto;
                white-space: nowrap;
            }

            .qr-section {
                grid-template-columns: 1fr;
            }

            .form-row {
                flex-direction: column;
            }

            .form-group-modal {
                min-width: 100%;
            }

            .modal-content {
                margin-top: 150px;
                /* Adjust for mobile navbar */
                width: 98%;
                max-height: 80vh;
            }
        }

        @media (max-width: 480px) {
            .modal-content {
                margin-top: 135px;
                /* Adjust for smaller mobile navbar */
                width: 99%;
                max-height: 75vh;
            }
        }
    </style>
</head>

<body>
    <div class="student-header">
        <h1 class="student-title">
            <i class="fas fa-user-graduate"></i>
            Student Management
        </h1>
        <div class="action-buttons">
            <button class="btn btn-info" onclick="generateReports()">
                <i class="fas fa-chart-line"></i>
                Generate Report
            </button>
            <button class="btn btn-warning" onclick="bulkOperations()">
                <i class="fas fa-tasks"></i>
                Bulk Operations
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number" id="totalStudents"><?php echo number_format($total_students); ?></div>
            <div class="stat-label">Total Students</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="activeMembers"><?php echo number_format($active_students); ?></div>
            <div class="stat-label">Active Members</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="booksIssued"><?php echo number_format($students_with_books); ?></div>
            <div class="stat-label">Students with Books</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="overdueBooks"><?php echo number_format($expired_memberships); ?></div>
            <div class="stat-label">Expired Memberships</div>
        </div>
    </div>

    <!-- Add New Student Section -->
    <div class="add-student-section">
        <h3 class="section-title">
            <i class="fas fa-user-plus"></i>
            Add New Student
        </h3>
        <form id="addStudentInlineForm">
            <!-- Member Information Section -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-user"></i>
                    Member Information
                </div>
                <div class="form-row">
                    <div class="form-group-modal">
                        <label for="memberNameInline">Full Name <span class="required">*</span></label>
                        <input type="text" id="memberNameInline" name="MemberName" required>
                    </div>
                    <div class="form-group-modal">
                        <label for="memberGroupInline">Member Group <span class="required">*</span></label>
                        <select id="memberGroupInline" name="Group" required>
                            <option value="">Select Group</option>
                            <option value="Student">Student</option>
                            <option value="Faculty">Faculty</option>
                            <option value="Staff">Staff</option>
                            <option value="Guest">Guest</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group-modal">
                        <label for="memberPhoneInline">Phone Number</label>
                        <input type="tel" id="memberPhoneInline" name="Phone">
                    </div>
                    <div class="form-group-modal">
                        <label for="memberEmailInline">Email Address</label>
                        <input type="email" id="memberEmailInline" name="Email">
                    </div>
                    <div class="form-group-modal">
                        <label for="memberDesignationInline">Designation</label>
                        <input type="text" id="memberDesignationInline" name="Designation">
                    </div>
                </div>
            </div>

            <!-- Student Details Section -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-graduation-cap"></i>
                    Student Details
                </div>
                <div class="form-row">
                    <div class="form-group-modal">
                        <label for="studentSurnameInline">Surname</label>
                        <input type="text" id="studentSurnameInline" name="Surname">
                    </div>
                    <div class="form-group-modal">
                        <label for="studentFirstNameInline">First Name <span class="required">*</span></label>
                        <input type="text" id="studentFirstNameInline" name="FirstName" required>
                    </div>
                    <div class="form-group-modal">
                        <label for="studentMiddleNameInline">Middle Name</label>
                        <input type="text" id="studentMiddleNameInline" name="MiddleName">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group-modal">
                        <label for="studentPRNInline">PRN <span class="required">*</span></label>
                        <input type="text" id="studentPRNInline" name="PRN" required placeholder="Permanent Registration Number">
                    </div>
                    <div class="form-group-modal">
                        <label for="studentDOBInline">Date of Birth</label>
                        <input type="date" id="studentDOBInline" name="DOB">
                    </div>
                    <div class="form-group-modal">
                        <label for="studentGenderInline">Gender</label>
                        <select id="studentGenderInline" name="Gender">
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group-modal">
                        <label for="studentBranchInline">Branch <span class="required">*</span></label>
                        <select id="studentBranchInline" name="Branch" required>
                            <option value="">Select Branch</option>
                            <?php foreach ($branches as $branch): ?>
                                <option value="<?php echo htmlspecialchars($branch); ?>">
                                    <?php echo htmlspecialchars($branch); ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="Computer Engineering">Computer Engineering</option>
                            <option value="Mechanical Engineering">Mechanical Engineering</option>
                            <option value="Electronics Engineering">Electronics Engineering</option>
                            <option value="Civil Engineering">Civil Engineering</option>
                            <option value="Information Technology">Information Technology</option>
                        </select>
                    </div>
                    <div class="form-group-modal">
                        <label for="studentCourseNameInline">Course Name</label>
                        <select id="studentCourseNameInline" name="CourseName">
                            <option value="">Select Course</option>
                            <option value="B.Tech">B.Tech</option>
                            <option value="M.Tech">M.Tech</option>
                            <option value="Diploma">Diploma</option>
                            <option value="B.E.">B.E.</option>
                            <option value="M.E.">M.E.</option>
                        </select>
                    </div>
                    <div class="form-group-modal">
                        <label for="studentBloodGroupInline">Blood Group</label>
                        <select id="studentBloodGroupInline" name="BloodGroup">
                            <option value="">Select Blood Group</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group-modal">
                        <label for="studentMobileInline">Mobile Number</label>
                        <input type="tel" id="studentMobileInline" name="Mobile" placeholder="10-digit mobile number">
                    </div>
                    <div class="form-group-modal">
                        <label for="studentEmailInline">Student Email</label>
                        <input type="email" id="studentEmailInline" name="StudentEmail" placeholder="student@wiet.edu">
                    </div>
                    <div class="form-group-modal">
                        <label for="validTillInline">Valid Till</label>
                        <input type="date" id="validTillInline" name="ValidTill">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group-modal">
                        <label for="studentCardColourInline">Card Colour</label>
                        <select id="studentCardColourInline" name="CardColour">
                            <option value="">Select Card Colour</option>
                            <option value="Blue">Blue</option>
                            <option value="Green">Green</option>
                            <option value="Red">Red</option>
                            <option value="Yellow">Yellow</option>
                            <option value="White">White</option>
                        </select>
                    </div>
                    <div class="form-group-modal">
                        <label for="studentAddressInline">Address</label>
                        <textarea id="studentAddressInline" name="Address" rows="2"></textarea>
                    </div>
                </div>
            </div>

            <!-- Photo Upload Section -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-camera"></i>
                    Student Photo
                </div>
                <div class="photo-upload">
                    <div class="photo-preview" id="photoPreviewInline">
                        <i class="fas fa-user" style="font-size: 48px; color: #ccc;"></i>
                    </div>
                    <input type="file" id="studentPhotoInline" name="Photo" accept="image/*" style="display: none;" onchange="previewPhoto(this, 'photoPreviewInline')">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('studentPhotoInline').click()">
                        <i class="fas fa-upload"></i>
                        Upload Photo
                    </button>
                    <p style="font-size: 12px; color: #6c757d; margin-top: 10px;">
                        Recommended: 120x150 pixels, JPG/PNG format
                    </p>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="form-actions" style="justify-content:flex-start;">
                <button type="submit" class="btn btn-success" onclick="saveStudentInline(); return false;">
                    <i class="fas fa-paper-plane"></i>
                    Create Student & Generate QR
                </button>
            </div>
        </form>
    </div>

    <!-- Tabs -->
    <div class="tabs-container">
        <div class="tab-buttons">
            <button class="tab-btn active" onclick="showTab('students')">
                <i class="fas fa-user-graduate"></i>
                Students & Members
            </button>
            <button class="tab-btn" onclick="showTab('membership')">
                <i class="fas fa-id-card"></i>
                Membership Management
            </button>
            <button class="tab-btn" onclick="showTab('verification')">
                <i class="fas fa-check-circle"></i>
                Verification & QR Codes
            </button>
            <button class="tab-btn" onclick="showTab('reports')">
                <i class="fas fa-chart-pie"></i>
                Reports & Analytics
            </button>
        </div>

        <!-- Students & Members Tab -->
        <div id="students" class="tab-content active">
            <div class="search-filters">
                <div class="search-row">
                    <div class="form-group">
                        <label for="searchName">Student Name</label>
                        <input type="text" id="searchName" class="form-control" placeholder="Search by name...">
                    </div>
                    <div class="form-group">
                        <label for="searchPRN">PRN</label>
                        <input type="text" id="searchPRN" class="form-control" placeholder="Search by PRN...">
                    </div>
                    <div class="form-group">
                        <label for="searchBranch">Branch</label>
                        <select id="searchBranch" class="form-control">
                            <option value="">All Branches</option>
                            <option value="Computer Engineering">Computer Engineering</option>
                            <option value="Mechanical Engineering">Mechanical Engineering</option>
                            <option value="Electronics Engineering">Electronics Engineering</option>
                            <option value="Civil Engineering">Civil Engineering</option>
                            <option value="Information Technology">Information Technology</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="searchStatus">Status</label>
                        <select id="searchStatus" class="form-control">
                            <option value="">All Status</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                            <option value="Suspended">Suspended</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-primary" onclick="searchStudents()">
                            <i class="fas fa-search"></i>
                            Search
                        </button>
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-secondary" onclick="refreshData()" title="Refresh data from server">
                            <i class="fas fa-sync-alt"></i>
                            Refresh
                        </button>
                    </div>
                </div>
            </div>

            <div id="studentsTableContainer">
                <!-- Students table will be loaded here -->
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 24px;"></i>
                    <p style="margin-top: 10px;">Loading students...</p>
                </div>
            </div>
        </div>

        <!-- Membership Management Tab -->
        <div id="membership" class="tab-content">
            <div id="membershipContent">
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <i class="fas fa-id-card" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <h3>Membership Management</h3>
                    <p>Manage membership types, validity periods, and member privileges.</p>
                </div>
            </div>
        </div>

        <!-- Verification & QR Codes Tab -->
        <div id="verification" class="tab-content">
            <div id="verificationContent">
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <i class="fas fa-check-circle" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <h3>Verification & QR Codes</h3>
                    <p>Manage student verification status and regenerate QR codes.</p>
                </div>
            </div>
        </div>

        <!-- Reports Tab -->
        <div id="reports" class="tab-content">
            <div id="reportsContent">
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <i class="fas fa-chart-pie" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <h3>Student Reports</h3>
                    <p>Generate comprehensive reports on student data and activity.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div id="addStudentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add New Student</h3>
                <button class="close" onclick="closeModal('addStudentModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addStudentForm">
                    <!-- Member Information Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-user"></i>
                            Member Information
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="memberName">Full Name <span class="required">*</span></label>
                                <input type="text" id="memberName" name="MemberName" required>
                            </div>
                            <div class="form-group-modal">
                                <label for="memberGroup">Member Group <span class="required">*</span></label>
                                <select id="memberGroup" name="Group" required>
                                    <option value="">Select Group</option>
                                    <option value="Student">Student</option>
                                    <option value="Faculty">Faculty</option>
                                    <option value="Staff">Staff</option>
                                    <option value="Guest">Guest</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="memberPhone">Phone Number</label>
                                <input type="tel" id="memberPhone" name="Phone">
                            </div>
                            <div class="form-group-modal">
                                <label for="memberEmail">Email Address</label>
                                <input type="email" id="memberEmail" name="Email">
                            </div>
                            <div class="form-group-modal">
                                <label for="memberDesignation">Designation</label>
                                <input type="text" id="memberDesignation" name="Designation">
                            </div>
                        </div>
                    </div>

                    <!-- Student Details Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-graduation-cap"></i>
                            Student Details
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="studentName">Student Name <span class="required">*</span></label>
                                <input type="text" id="studentName" name="Name" required>
                            </div>
                            <div class="form-group-modal">
                                <label for="studentPRN">PRN <span class="required">*</span></label>
                                <input type="text" id="studentPRN" name="PRN" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="studentDOB">Date of Birth</label>
                                <input type="date" id="studentDOB" name="DOB">
                            </div>
                            <div class="form-group-modal">
                                <label for="studentBranch">Branch <span class="required">*</span></label>
                                <select id="studentBranch" name="Branch" required>
                                    <option value="">Select Branch</option>
                                    <option value="Computer Engineering">Computer Engineering</option>
                                    <option value="Mechanical Engineering">Mechanical Engineering</option>
                                    <option value="Electronics Engineering">Electronics Engineering</option>
                                    <option value="Civil Engineering">Civil Engineering</option>
                                    <option value="Information Technology">Information Technology</option>
                                </select>
                            </div>
                            <div class="form-group-modal">
                                <label for="studentBloodGroup">Blood Group</label>
                                <select id="studentBloodGroup" name="BloodGroup">
                                    <option value="">Select Blood Group</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="studentMobile">Mobile Number</label>
                                <input type="tel" id="studentMobile" name="Mobile">
                            </div>
                            <div class="form-group-modal">
                                <label for="studentAadhaar">Aadhaar Number</label>
                                <input type="text" id="studentAadhaar" name="Aadhaar" maxlength="12">
                            </div>
                            <div class="form-group-modal">
                                <label for="validTill">Valid Till</label>
                                <input type="date" id="validTill" name="ValidTill">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="studentAddress">Address</label>
                                <textarea id="studentAddress" name="Address" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Photo Upload Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-camera"></i>
                            Student Photo
                        </div>
                        <div class="photo-upload">
                            <div class="photo-preview" id="photoPreview">
                                <i class="fas fa-user" style="font-size: 48px; color: #ccc;"></i>
                            </div>
                            <input type="file" id="studentPhoto" name="Photo" accept="image/*" style="display: none;" onchange="previewPhoto(this)">
                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('studentPhoto').click()">
                                <i class="fas fa-upload"></i>
                                Upload Photo
                            </button>
                            <p style="font-size: 12px; color: #6c757d; margin-top: 10px;">
                                Recommended: 120x150 pixels, JPG/PNG format
                            </p>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('addStudentModal')">Cancel</button>
                        <button type="submit" class="btn btn-success" onclick="saveStudent()">
                            <i class="fas fa-save"></i>
                            Create Student & Generate QR
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- QR Code Modal -->
    <div id="qrCodeModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">QR Code</h3>
                <button class="close" onclick="closeModal('qrCodeModal')">&times;</button>
            </div>
            <div class="modal-body" style="text-align: center;">
                <img src="" alt="QR Code" style="max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('qrCodeModal')">
                    <i class="fas fa-times"></i>
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        // Global cache for students data
        let studentsCache = null;
        let cacheTimestamp = null;
        const CACHE_DURATION = 60000; // 1 minute

        // Tab functionality
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });

            // Remove active class from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Show selected tab
            document.getElementById(tabName).classList.add('active');

            // Add active class to clicked button - find the button that calls this tab
            const buttons = document.querySelectorAll('.tab-btn');
            buttons.forEach(btn => {
                if (btn.getAttribute('onclick') && btn.getAttribute('onclick').includes(`'${tabName}'`)) {
                    btn.classList.add('active');
                }
            });

            // Load content based on tab
            loadTabContent(tabName);
        }

        function loadTabContent(tabName) {
            switch (tabName) {
                case 'students':
                    loadStudentsTable();
                    break;
                case 'membership':
                    loadMembershipContent();
                    break;
                case 'verification':
                    loadVerificationContent();
                    break;
                case 'reports':
                    loadReportsContent();
                    break;
            }
        }

        async function fetchStudentsData(forceRefresh = false) {
            // Check if cache is valid
            const now = Date.now();
            if (!forceRefresh && studentsCache && cacheTimestamp && (now - cacheTimestamp) < CACHE_DURATION) {
                return studentsCache;
            }

            // Fetch fresh data
            const response = await fetch('api/members.php?action=list_students');
            const result = await response.json();

            if (result.success) {
                studentsCache = result.data || [];
                cacheTimestamp = now;
                return studentsCache;
            } else {
                throw new Error(result.message || 'Failed to load students');
            }
        }

        async function loadStudentsTable(searchParams = {}) {
            // Show loading state
            document.getElementById('studentsTableContainer').innerHTML = `
                <div style="text-align: center; padding: 60px 20px; color: #6c757d;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 48px; margin-bottom: 20px; color: #263c79;"></i>
                    <h3 style="color: #263c79;">Loading Students...</h3>
                    <p>Fetching data from database...</p>
                </div>
            `;

            try {
                let students;
                
                // If search params provided, fetch fresh data
                if (Object.keys(searchParams).length > 0) {
                    const params = new URLSearchParams({ action: 'list_students' });
                    if (searchParams.name) params.append('name', searchParams.name);
                    if (searchParams.prn) params.append('prn', searchParams.prn);
                    if (searchParams.branch) params.append('branch', searchParams.branch);
                    if (searchParams.status) params.append('status', searchParams.status);

                    const response = await fetch(`api/members.php?${params.toString()}`);
                    const result = await response.json();
                    
                    if (!result.success) {
                        throw new Error(result.message || 'Failed to load students');
                    }
                    students = result.data || [];
                } else {
                    // Use cached data for initial load
                    students = await fetchStudentsData();
                }

                displayStudentsTable(students);
                
            } catch (error) {
                console.error('Error loading students:', error);
                document.getElementById('studentsTableContainer').innerHTML = `
                    <div style="text-align: center; padding: 60px 20px; color: #dc3545;">
                        <i class="fas fa-exclamation-circle" style="font-size: 48px; margin-bottom: 20px;"></i>
                        <h3>Error Loading Students</h3>
                        <p>${error.message}</p>
                        <button class="btn btn-primary" onclick="loadStudentsTable()">
                            <i class="fas fa-redo"></i>
                            Retry
                        </button>
                    </div>
                `;
            }
        }

        // Manual refresh function - clears cache and reloads
        async function refreshData() {
            studentsCache = null;
            cacheTimestamp = null;
            
            // Show loading indicator
            const container = document.getElementById('studentsTableContainer');
            container.innerHTML = `
                <div style="text-align: center; padding: 40px; color: navy;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 24px;"></i>
                    <p style="margin-top: 10px;">Refreshing data from server...</p>
                </div>
            `;
            
            // Force fresh data load
            await loadStudentsTable();
            loadStatistics(); // Also refresh statistics
        }

        function displayStudentsTable(students) {
            let tableHTML = `
                <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
                    <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #263c79;">
                        <input type="checkbox" id="selectAllStudents" onchange="selectAllStudents()">
                        Select All Students
                    </label>
                    <div id="bulkActions" style="display: none;">
                        <span style="color: #6c757d; margin-right: 10px;">
                            <span id="selectedCount">0</span> selected
                        </span>
                        <button class="btn btn-warning" onclick="document.getElementById('bulkOperationsModal').style.display='block'">
                            <i class="fas fa-tasks"></i>
                            Bulk Actions
                        </button>
                    </div>
                </div>
                
                <table class="students-table">
                    <thead>
                        <tr>
                            <th>Select</th>
                            <th>Member No.</th>
                            <th>Student Details</th>
                            <th>PRN</th>
                            <th>Branch</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Books Issued</th>
                            <th>Valid Till</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            if (students.length === 0) {
                tableHTML += `
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 40px; color: #6c757d;">
                            <i class="fas fa-search" style="font-size: 24px; margin-bottom: 10px;"></i>
                            <p>No students found matching your search criteria.</p>
                        </td>
                    </tr>
                `;
            } else {
                students.forEach(student => {
                    const statusClass = {
                        'Active': 'status-active',
                        'Inactive': 'status-inactive',
                        'Suspended': 'status-suspended'
                    } [student.Status] || 'status-active';

                    tableHTML += `
                        <tr>
                            <td>
                                <input type="checkbox" class="student-checkbox" value="${student.StudentID}" onchange="updateBulkActionButtons()">
                            </td>
                            <td><strong>${student.MemberNo}</strong></td>
                            <td>
                                <strong>${student.Name}</strong><br>
                                <small style="color: #6c757d;">${student.Email}</small>
                            </td>
                            <td>${student.PRN}</td>
                            <td><span style="background: rgba(38,60,121,0.1); color: #263c79; padding: 2px 6px; border-radius: 3px; font-size: 12px;">${student.Branch}</span></td>
                            <td>${student.Mobile}</td>
                            <td><span class="status-badge ${statusClass}">${student.Status}</span></td>
                            <td><span style="color: ${student.BooksIssued > 0 ? '#dc3545' : '#28a745'}; font-weight: 600;">${student.BooksIssued}</span></td>
                            <td>${new Date(student.ValidTill).toLocaleDateString('en-IN')}</td>
                            <td class="action-links">
                                <a href="#" class="btn-view" onclick="viewStudent(${student.StudentID})">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="#" class="btn-edit" onclick="editStudent(${student.StudentID})">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="#" class="btn-delete" onclick="deleteStudent(${student.StudentID})">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <a href="#" class="btn-qr" onclick="viewQRCode(${student.StudentID})">
                                    <i class="fas fa-qrcode"></i>
                                </a>
                            </td>
                        </tr>
                    `;
                });
            }

            tableHTML += `
                    </tbody>
                </table>
            `;

            document.getElementById('studentsTableContainer').innerHTML = tableHTML;
        }

        function searchStudents() {
            const searchParams = {
                name: document.getElementById('searchName').value.trim(),
                prn: document.getElementById('searchPRN').value.trim(),
                branch: document.getElementById('searchBranch').value,
                status: document.getElementById('searchStatus').value
            };

            loadStudentsTable(searchParams);
        }

        function loadMembershipContent() {
            const membershipHTML = `
                <div style="margin-bottom: 20px;">
                    <h3 style="color: #263c79; margin-bottom: 15px;">Membership Management</h3>
                    <p style="color: #6c757d; margin-bottom: 20px;">Manage student membership validity, renewals, and status updates.</p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                    <!-- Renew Memberships -->
                    <div onclick="renewMemberships()" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-sync-alt" style="font-size: 48px; color: #263c79; margin-bottom: 15px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 10px;">Renew Memberships</h4>
                        <p style="color: #6c757d; font-size: 14px;">Extend validity for expiring members</p>
                    </div>

                    <!-- Expired Members -->
                    <div onclick="viewExpiredMembers()" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-calendar-times" style="font-size: 48px; color: #dc3545; margin-bottom: 15px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 10px;">Expired Members</h4>
                        <p style="color: #6c757d; font-size: 14px;">View and renew expired memberships</p>
                    </div>

                    <!-- Expiring Soon -->
                    <div onclick="viewExpiringSoon()" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #ffc107; margin-bottom: 15px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 10px;">Expiring Soon</h4>
                        <p style="color: #6c757d; font-size: 14px;">Members expiring in next 30 days</p>
                    </div>

                    <!-- Membership Statistics -->
                    <div onclick="viewMembershipStats()" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-chart-pie" style="font-size: 48px; color: #17a2b8; margin-bottom: 15px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 10px;">Statistics</h4>
                        <p style="color: #6c757d; font-size: 14px;">Membership validity analytics</p>
                    </div>
                </div>

                <!-- Membership Summary Table -->
                <div style="margin-top: 30px; background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px;">
                    <h4 style="color: #263c79; margin-bottom: 15px;">Quick Summary</h4>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8f9fa;">
                                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #263c79;">Status</th>
                                <th style="padding: 12px; text-align: center; border-bottom: 2px solid #263c79;">Count</th>
                                <th style="padding: 12px; text-align: center; border-bottom: 2px solid #263c79;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding: 12px; border-bottom: 1px solid #e9ecef;">
                                    <i class="fas fa-check-circle" style="color: #28a745;"></i> Active Memberships
                                </td>
                                <td id="activeMembershipsCount" style="padding: 12px; text-align: center; border-bottom: 1px solid #e9ecef; font-weight: 600;">-</td>
                                <td style="padding: 12px; text-align: center; border-bottom: 1px solid #e9ecef;">
                                    <button class="btn btn-sm btn-primary" onclick="viewActiveMembers()">View</button>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 12px; border-bottom: 1px solid #e9ecef;">
                                    <i class="fas fa-clock" style="color: #ffc107;"></i> Expiring in 30 Days
                                </td>
                                <td id="expiringSoonCount" style="padding: 12px; text-align: center; border-bottom: 1px solid #e9ecef; font-weight: 600;">-</td>
                                <td style="padding: 12px; text-align: center; border-bottom: 1px solid #e9ecef;">
                                    <button class="btn btn-sm btn-warning" onclick="viewExpiringSoon()">View</button>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 12px; border-bottom: 1px solid #e9ecef;">
                                    <i class="fas fa-times-circle" style="color: #dc3545;"></i> Expired Memberships
                                </td>
                                <td id="expiredCount" style="padding: 12px; text-align: center; border-bottom: 1px solid #e9ecef; font-weight: 600;">-</td>
                                <td style="padding: 12px; text-align: center; border-bottom: 1px solid #e9ecef;">
                                    <button class="btn btn-sm btn-danger" onclick="viewExpiredMembers()">View</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            `;

            document.getElementById('membershipContent').innerHTML = membershipHTML;
            loadMembershipSummary();
        }

        async function loadMembershipSummary() {
            try {
                const response = await fetch('api/members.php?action=list_students');
                const result = await response.json();
                
                if (result.success) {
                    const students = result.data || [];
                    const today = new Date();
                    const thirtyDaysFromNow = new Date(today.getTime() + 30 * 24 * 60 * 60 * 1000);
                    
                    const active = students.filter(s => {
                        const validTill = new Date(s.ValidTill);
                        return s.Status === 'Active' && validTill > today;
                    }).length;
                    
                    const expiringSoon = students.filter(s => {
                        const validTill = new Date(s.ValidTill);
                        return s.Status === 'Active' && validTill > today && validTill <= thirtyDaysFromNow;
                    }).length;
                    
                    const expired = students.filter(s => {
                        const validTill = new Date(s.ValidTill);
                        return validTill <= today;
                    }).length;
                    
                    document.getElementById('activeMembershipsCount').textContent = active;
                    document.getElementById('expiringSoonCount').textContent = expiringSoon;
                    document.getElementById('expiredCount').textContent = expired;
                }
            } catch (error) {
                console.error('Error loading membership summary:', error);
            }
        }

        function loadVerificationContent() {
            const verificationHTML = `
                <div style="margin-bottom: 20px;">
                    <h3 style="color: #263c79; margin-bottom: 15px;">Student Verification & QR Codes</h3>
                    <p style="color: #6c757d; margin-bottom: 20px;">Manage student verification, digital IDs, and QR code generation.</p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                    <!-- Generate QR Codes -->
                    <div onclick="generateQRCodes()" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-qrcode" style="font-size: 48px; color: #263c79; margin-bottom: 15px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 10px;">Generate QR Codes</h4>
                        <p style="color: #6c757d; font-size: 14px;">Create QR codes for all students</p>
                    </div>

                    <!-- Digital ID Cards -->
                    <div onclick="generateDigitalIDs()" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-id-card" style="font-size: 48px; color: #28a745; margin-bottom: 15px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 10px;">Digital ID Cards</h4>
                        <p style="color: #6c757d; font-size: 14px;">Generate printable student ID cards</p>
                    </div>

                    <!-- Verify Student -->
                    <div onclick="verifyStudent()" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-user-check" style="font-size: 48px; color: #17a2b8; margin-bottom: 15px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 10px;">Verify Student</h4>
                        <p style="color: #6c757d; font-size: 14px;">Scan QR code to verify identity</p>
                    </div>

                    <!-- Bulk QR Generation -->
                    <div onclick="bulkQRGeneration()" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-layer-group" style="font-size: 48px; color: #ffc107; margin-bottom: 15px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 10px;">Bulk Generate</h4>
                        <p style="color: #6c757d; font-size: 14px;">Generate QR codes in batch</p>
                    </div>
                </div>

                <!-- QR Code Scanner Section -->
                <div style="margin-top: 30px; background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px;">
                    <h4 style="color: #263c79; margin-bottom: 15px;">
                        <i class="fas fa-camera"></i> Quick QR Scanner
                    </h4>
                    <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                        <input type="text" id="qrCodeInput" placeholder="Enter PRN or scan QR code" style="flex: 1; min-width: 250px; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        <button class="btn btn-primary" onclick="verifyQRCode()">
                            <i class="fas fa-search"></i> Verify
                        </button>
                        <button class="btn btn-secondary" onclick="openQRScanner()">
                            <i class="fas fa-camera"></i> Scan QR
                        </button>
                    </div>
                    <div id="qrVerificationResult" style="margin-top: 15px;"></div>
                </div>
            `;

            document.getElementById('verificationContent').innerHTML = verificationHTML;
        }

        function loadReportsContent() {
            const reportsHTML = `
                <div style="margin-bottom: 20px;">
                    <h3 style="color: #263c79; margin-bottom: 15px;">Student Reports</h3>
                    <p style="color: #6c757d; margin-bottom: 20px;">Generate comprehensive reports on student data and activities.</p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">
                    <!-- All Students Report -->
                    <div onclick="generateReport('all-students')" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-users" style="font-size: 32px; color: #263c79; margin-bottom: 10px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 5px;">All Students</h4>
                        <p style="color: #6c757d; font-size: 14px;">Complete student database export</p>
                    </div>

                    <!-- Branch-wise Report -->
                    <div onclick="generateReport('branch-wise')" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-sitemap" style="font-size: 32px; color: #28a745; margin-bottom: 10px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 5px;">Branch-wise</h4>
                        <p style="color: #6c757d; font-size: 14px;">Students grouped by branch</p>
                    </div>

                    <!-- Books Issued Report -->
                    <div onclick="generateReport('books-issued')" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-book" style="font-size: 32px; color: #17a2b8; margin-bottom: 10px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 5px;">Books Issued</h4>
                        <p style="color: #6c757d; font-size: 14px;">Student borrowing statistics</p>
                    </div>

                    <!-- Expired Members Report -->
                    <div onclick="generateReport('expired-members')" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-calendar-times" style="font-size: 32px; color: #dc3545; margin-bottom: 10px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 5px;">Expired Members</h4>
                        <p style="color: #6c757d; font-size: 14px;">Students with expired validity</p>
                    </div>

                    <!-- Course-wise Report -->
                    <div onclick="generateReport('course-wise')" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-graduation-cap" style="font-size: 32px; color: #ffc107; margin-bottom: 10px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 5px;">Course-wise</h4>
                        <p style="color: #6c757d; font-size: 14px;">Students by course/program</p>
                    </div>

                    <!-- Custom Report -->
                    <div onclick="generateReport('custom')" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-sliders-h" style="font-size: 32px; color: #6c757d; margin-bottom: 10px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 5px;">Custom Report</h4>
                        <p style="color: #6c757d; font-size: 14px;">Build your own report</p>
                    </div>
                </div>

                <!-- Export Options -->
                <div style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px;">
                    <h4 style="color: #263c79; margin-bottom: 15px;">Export Format</h4>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <button class="btn btn-success" onclick="exportReport('excel')">
                            <i class="fas fa-file-excel"></i> Export to Excel
                        </button>
                        <button class="btn btn-danger" onclick="exportReport('pdf')">
                            <i class="fas fa-file-pdf"></i> Export to PDF
                        </button>
                        <button class="btn btn-info" onclick="exportReport('csv')">
                            <i class="fas fa-file-csv"></i> Export to CSV
                        </button>
                        <button class="btn btn-secondary" onclick="printReport()">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                    </div>
                </div>
            `;

            document.getElementById('reportsContent').innerHTML = reportsHTML;
        }

        // Modal functions
        function openAddStudentModal() {
            document.getElementById('addStudentModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';

            // Reset form
            if (modalId === 'addStudentModal') {
                document.getElementById('addStudentForm').reset();
                document.getElementById('photoPreview').innerHTML = '<i class="fas fa-user" style="font-size: 48px; color: #ccc;"></i>';
            }
        }

        function previewPhoto(input, previewId = 'photoPreview') {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById(previewId).innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        async function saveStudent() {
            const form = document.getElementById('addStudentForm');
            const formData = new FormData(form);
            
            // Add action parameter
            formData.append('action', 'add_student');

            try {
                const response = await fetch('api/members.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert(`Student created successfully!\nMember No: ${result.memberNo}\nStudent ID: ${result.studentId}\nQR Code generated and ready for printing.`);
                    closeModal('addStudentModal');
                    studentsCache = null; // Clear cache
                    loadStudentsTable(); // Refresh table
                    loadStatistics(); // Refresh stats
                } else {
                    alert('Error creating student: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error saving student:', error);
                alert('Error saving student. Please try again.');
            }
        }

        async function saveStudentInline() {
            const form = document.getElementById('addStudentInlineForm');
            const formData = new FormData(form);
            
            // Add action parameter
            formData.append('action', 'add_student');

            try {
                const response = await fetch('api/members.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert(`Student created successfully!\nMember No: ${result.memberNo}\nStudent ID: ${result.studentId}\nQR Code generated and ready for printing.`);
                    
                    // Clear the form
                    form.reset();
                    
                    // Clear photo preview
                    const photoPreview = document.getElementById('photoPreviewInline');
                    photoPreview.innerHTML = '<i class="fas fa-user" style="font-size: 48px; color: #ccc;"></i>';

                    studentsCache = null; // Clear cache
                    loadStudentsTable(); // Refresh table
                    loadStatistics(); // Refresh stats
                } else {
                    alert('Error creating student: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error saving student:', error);
                alert('Error saving student. Please try again.');
            }
        }

        // Student actions
        async function viewStudent(studentId) {
            try {
                const response = await fetch(`api/members.php?action=get_student&studentId=${studentId}`);
                const result = await response.json();

                if (result.success) {
                    const student = result.data;
                    // Display in a modal or detailed view
                    alert(`Student Details:\n\nName: ${student.FirstName} ${student.MiddleName || ''} ${student.Surname || ''}\nPRN: ${student.PRN}\nBranch: ${student.Branch}\nMobile: ${student.Mobile}\nEmail: ${student.Email}\nStatus: ${student.Status}`);
                    // TODO: Create a proper view modal
                } else {
                    alert('Error loading student details: ' + result.message);
                }
            } catch (error) {
                console.error('Error viewing student:', error);
                alert('Error loading student details. Please try again.');
            }
        }

        async function editStudent(studentId) {
            try {
                const response = await fetch(`api/members.php?action=get_student&studentId=${studentId}`);
                const result = await response.json();

                if (result.success) {
                    const student = result.data;
                    // Populate the inline form with student data
                    document.getElementById('studentSurnameInline').value = student.Surname || '';
                    document.getElementById('studentMiddleNameInline').value = student.MiddleName || '';
                    document.getElementById('studentFirstNameInline').value = student.FirstName || '';
                    document.getElementById('studentPRNInline').value = student.PRN || '';
                    document.getElementById('studentBranchInline').value = student.Branch || '';
                    document.getElementById('studentCourseNameInline').value = student.CourseName || '';
                    document.getElementById('studentGenderInline').value = student.Gender || '';
                    document.getElementById('studentDOBInline').value = student.DOB || '';
                    document.getElementById('studentBloodGroupInline').value = student.BloodGroup || '';
                    document.getElementById('studentMobileInline').value = student.Mobile || '';
                    document.getElementById('studentEmailInline').value = student.Email || '';
                    document.getElementById('studentAddressInline').value = student.Address || '';
                    document.getElementById('studentValidTillInline').value = student.ValidTill || '';
                    document.getElementById('studentCardColourInline').value = student.CardColour || '';
                    
                    // Store student ID for update
                    document.getElementById('addStudentInlineForm').dataset.studentId = studentId;
                    
                    // Scroll to form
                    document.getElementById('addStudentInlineForm').scrollIntoView({ behavior: 'smooth' });
                    
                    alert('Student data loaded in form. Update the fields and click Save.');
                    // TODO: Change button text to "Update Student" instead of "Add Student"
                } else {
                    alert('Error loading student details: ' + result.message);
                }
            } catch (error) {
                console.error('Error editing student:', error);
                alert('Error loading student details. Please try again.');
            }
        }

        async function deleteStudent(studentId) {
            if (!confirm(`Are you sure you want to delete Student ID: ${studentId}?\n\nThis will also delete the associated member record. This action cannot be undone.`)) {
                return;
            }

            try {
                const response = await fetch('api/members.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'delete_student',
                        studentId: studentId
                    })
                });

                const result = await response.json();

                if (result.success) {
                    alert('Student deleted successfully!');
                    studentsCache = null; // Clear cache
                    loadStudentsTable();
                    loadStatistics(); // Refresh stats
                } else {
                    alert('Error deleting student: ' + result.message);
                }
            } catch (error) {
                console.error('Error deleting student:', error);
                alert('Error deleting student. Please try again.');
            }
        }

        function generateReports() {
            showTab('reports');
        }

        // Membership management functions
        function renewMemberships() {
            if (confirm('Renew memberships for all expiring members?\n\nThis will extend validity by 1 year.')) {
                alert('Membership renewal process initiated...\n\nMembers will be notified via email.');
                // TODO: Implement renewal API
            }
        }

        async function viewExpiredMembers() {
            try {
                const response = await fetch('api/members.php?action=list_students');
                const result = await response.json();
                
                if (result.success) {
                    const students = result.data || [];
                    const today = new Date();
                    const expired = students.filter(s => new Date(s.ValidTill) <= today);
                    
                    if (expired.length === 0) {
                        alert('No expired memberships found. All members are active!');
                    } else {
                        alert(`Found ${expired.length} expired memberships.\n\nSwitching to Students tab with filter applied...`);
                        showTab('students');
                        // TODO: Apply filter to show only expired
                    }
                }
            } catch (error) {
                console.error('Error loading expired members:', error);
                alert('Error loading expired members data.');
            }
        }

        async function viewExpiringSoon() {
            try {
                const response = await fetch('api/members.php?action=list_students');
                const result = await response.json();
                
                if (result.success) {
                    const students = result.data || [];
                    const today = new Date();
                    const thirtyDaysFromNow = new Date(today.getTime() + 30 * 24 * 60 * 60 * 1000);
                    
                    const expiringSoon = students.filter(s => {
                        const validTill = new Date(s.ValidTill);
                        return s.Status === 'Active' && validTill > today && validTill <= thirtyDaysFromNow;
                    });
                    
                    if (expiringSoon.length === 0) {
                        alert('No memberships expiring in the next 30 days.');
                    } else {
                        alert(`Found ${expiringSoon.length} memberships expiring in next 30 days.\n\nSwitching to Students tab...`);
                        showTab('students');
                        // TODO: Apply filter to show only expiring soon
                    }
                }
            } catch (error) {
                console.error('Error loading expiring members:', error);
                alert('Error loading membership data.');
            }
        }

        function viewMembershipStats() {
            alert('Membership Statistics:\n\nThis will show:\n- Active vs Expired ratio\n- Average validity period\n- Renewal trends\n- Branch-wise distribution');
            // TODO: Create stats dashboard
        }

        function viewActiveMembers() {
            showTab('students');
            document.getElementById('searchStatus').value = 'Active';
            searchStudents();
        }

        // Verification functions
        function generateQRCodes() {
            if (confirm('Generate QR codes for all students?\n\nThis will create QR codes for students who don\'t have one.')) {
                alert('QR code generation started...\n\nProcessing students in background.\nYou will be notified when complete.');
                // TODO: Implement QR generation API
            }
        }

        function generateDigitalIDs() {
            if (confirm('Generate digital ID cards for all students?\n\nThis will create printable PDF ID cards with photos and QR codes.')) {
                alert('Digital ID card generation started...\n\nCards will be available for download in a few minutes.');
                // TODO: Implement ID card generation
            }
        }

        function verifyStudent() {
            const prn = prompt('Enter student PRN to verify:');
            if (prn) {
                verifyQRCode(prn);
            }
        }

        async function verifyQRCode(code) {
            const qrCode = code || document.getElementById('qrCodeInput').value.trim();
            
            if (!qrCode) {
                alert('Please enter a PRN or QR code to verify.');
                return;
            }
            
            const resultDiv = document.getElementById('qrVerificationResult');
            resultDiv.innerHTML = '<p style="color: #6c757d;">Verifying...</p>';
            
            try {
                const response = await fetch(`api/members.php?action=list_students&prn=${qrCode}`);
                const result = await response.json();
                
                if (result.success && result.data.length > 0) {
                    const student = result.data[0];
                    const validTill = new Date(student.ValidTill);
                    const isValid = validTill > new Date() && student.Status === 'Active';
                    
                    resultDiv.innerHTML = `
                        <div style="padding: 15px; border-radius: 6px; background: ${isValid ? '#d4edda' : '#f8d7da'}; border: 1px solid ${isValid ? '#c3e6cb' : '#f5c6cb'};">
                            <h4 style="color: ${isValid ? '#155724' : '#721c24'}; margin-bottom: 10px;">
                                <i class="fas fa-${isValid ? 'check-circle' : 'times-circle'}"></i>
                                ${isValid ? 'Valid Student' : 'Invalid/Expired'}
                            </h4>
                            <p style="margin: 5px 0;"><strong>Name:</strong> ${student.FirstName} ${student.MiddleName || ''} ${student.Surname || ''}</p>
                            <p style="margin: 5px 0;"><strong>PRN:</strong> ${student.PRN}</p>
                            <p style="margin: 5px 0;"><strong>Branch:</strong> ${student.Branch}</p>
                            <p style="margin: 5px 0;"><strong>Valid Till:</strong> ${new Date(student.ValidTill).toLocaleDateString()}</p>
                            <p style="margin: 5px 0;"><strong>Books Issued:</strong> ${student.BooksIssued || 0}</p>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div style="padding: 15px; border-radius: 6px; background: #f8d7da; border: 1px solid #f5c6cb;">
                            <p style="color: #721c24; margin: 0;">
                                <i class="fas fa-exclamation-triangle"></i>
                                Student not found. Please check the PRN/QR code.
                            </p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error verifying student:', error);
                resultDiv.innerHTML = `
                    <div style="padding: 15px; border-radius: 6px; background: #f8d7da; border: 1px solid #f5c6cb;">
                        <p style="color: #721c24; margin: 0;">Error verifying student. Please try again.</p>
                    </div>
                `;
            }
        }

        function openQRScanner() {
            alert('QR Scanner Interface\n\nIn production, this would:\n- Open camera\n- Scan QR code\n- Automatically verify student\n\nFor now, please enter PRN manually.');
            // TODO: Implement camera-based QR scanner
        }

        function bulkQRGeneration() {
            const selectedStudents = Array.from(document.querySelectorAll('.student-checkbox:checked'))
                .map(cb => cb.value);
            
            if (selectedStudents.length === 0) {
                alert('Please select students from the Students tab first, then return here to generate QR codes.');
                showTab('students');
                return;
            }
            
            if (confirm(`Generate QR codes for ${selectedStudents.length} selected student(s)?`)) {
                alert(`Generating QR codes for ${selectedStudents.length} students...\n\nProcessing will complete shortly.`);
                // TODO: Implement bulk QR generation
            }
        }

        // Report generation functions
        async function generateReport(reportType) {
            try {
                const response = await fetch('api/members.php?action=list_students');
                const result = await response.json();
                
                if (!result.success) {
                    throw new Error('Failed to fetch student data');
                }
                
                const students = result.data || [];
                let reportData = [];
                let reportTitle = '';
                
                switch(reportType) {
                    case 'all-students':
                        reportData = students;
                        reportTitle = 'All Students Report';
                        break;
                    case 'branch-wise':
                        reportTitle = 'Branch-wise Report';
                        const branch = prompt('Enter branch name (e.g., Computer, Mechanical, Civil):');
                        if (branch) {
                            reportData = students.filter(s => s.Branch && s.Branch.toLowerCase().includes(branch.toLowerCase()));
                        }
                        break;
                    case 'books-issued':
                        reportData = students.filter(s => s.BooksIssued > 0);
                        reportTitle = 'Students with Books Issued';
                        break;
                    case 'expired-members':
                        const today = new Date();
                        reportData = students.filter(s => new Date(s.ValidTill) <= today);
                        reportTitle = 'Expired Memberships Report';
                        break;
                    case 'course-wise':
                        const course = prompt('Enter course name (e.g., B.Tech, M.Tech, Diploma):');
                        if (course) {
                            reportData = students.filter(s => s.CourseName && s.CourseName.toLowerCase().includes(course.toLowerCase()));
                        }
                        reportTitle = `${course} Students Report`;
                        break;
                    case 'custom':
                        alert('Custom Report Builder\n\nSelect criteria:\n- Date range\n- Branch\n- Course\n- Status\n- Books issued\n\nThis feature will be available soon.');
                        return;
                }
                
                if (reportData.length === 0) {
                    alert('No data found for the selected report criteria.');
                    return;
                }
                
                alert(`${reportTitle}\n\nTotal Records: ${reportData.length}\n\nReport will be generated. Please select export format.`);
                
            } catch (error) {
                console.error('Error generating report:', error);
                alert('Error generating report. Please try again.');
            }
        }

        function exportReport(format) {
            alert(`Exporting report to ${format.toUpperCase()}...\n\nDownload will start shortly.\n\nNote: This is a demo. In production, actual file will be generated.`);
            // TODO: Implement actual export functionality
        }

        function printReport() {
            alert('Opening print preview...\n\nIn production, this would open a printer-friendly view of the current report.');
            // TODO: Implement print functionality
        }

        function bulkOperations() {
            document.getElementById('bulkOperationsModal').style.display = 'block';
        }

        function closeBulkModal() {
            document.getElementById('bulkOperationsModal').style.display = 'none';
            // Reset selections
            document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = false);
            updateBulkActionButtons();
        }

        function selectAllStudents() {
            const selectAll = document.getElementById('selectAllStudents');
            const checkboxes = document.querySelectorAll('.student-checkbox');

            checkboxes.forEach(cb => {
                cb.checked = selectAll.checked;
            });

            updateBulkActionButtons();
        }

        function updateBulkActionButtons() {
            const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
            const bulkActions = document.getElementById('bulkActions');
            const selectedCount = document.getElementById('selectedCount');

            if (checkedBoxes.length > 0) {
                bulkActions.style.display = 'block';
                selectedCount.textContent = checkedBoxes.length;
            } else {
                bulkActions.style.display = 'none';
            }
        }

        async function performBulkAction(action) {
            const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
            const selectedIds = Array.from(checkedBoxes).map(cb => cb.value);

            if (selectedIds.length === 0) {
                alert('Please select at least one student.');
                return;
            }

            let confirmMessage = '';
            switch (action) {
                case 'activate':
                    confirmMessage = `Activate ${selectedIds.length} selected student(s)?`;
                    break;
                case 'deactivate':
                    confirmMessage = `Deactivate ${selectedIds.length} selected student(s)?`;
                    break;
                case 'suspend':
                    confirmMessage = `Suspend ${selectedIds.length} selected student(s)?`;
                    break;
                case 'extend':
                    confirmMessage = `Extend membership validity for ${selectedIds.length} selected student(s) by 1 year?`;
                    break;
                case 'regenerate-qr':
                    confirmMessage = `Regenerate QR codes for ${selectedIds.length} selected student(s)?`;
                    break;
                case 'export':
                    confirmMessage = `Export data for ${selectedIds.length} selected student(s) to Excel?`;
                    break;
                case 'send-notification':
                    confirmMessage = `Send notification to ${selectedIds.length} selected student(s)?`;
                    break;
                case 'delete':
                    confirmMessage = `Are you sure you want to DELETE ${selectedIds.length} selected student(s)? This action cannot be undone.`;
                    break;
                default:
                    confirmMessage = `Perform action on ${selectedIds.length} selected student(s)?`;
            }

            if (!confirm(confirmMessage)) {
                return;
            }

            try {
                let successMessage = '';
                
                switch (action) {
                    case 'activate':
                    case 'deactivate':
                    case 'suspend':
                        // TODO: Implement bulk status update API
                        successMessage = `${selectedIds.length} student(s) status updated successfully.`;
                        break;
                    case 'extend':
                        // TODO: Implement bulk validity extension API
                        successMessage = `Membership extended for ${selectedIds.length} student(s).`;
                        break;
                    case 'regenerate-qr':
                        // TODO: Implement bulk QR regeneration API
                        successMessage = `QR codes regenerated for ${selectedIds.length} student(s).`;
                        break;
                    case 'export':
                        successMessage = `Data exported for ${selectedIds.length} student(s). Download will start shortly.`;
                        // TODO: Implement export functionality
                        break;
                    case 'send-notification':
                        successMessage = `Notifications sent to ${selectedIds.length} student(s).`;
                        // TODO: Implement notification sending
                        break;
                    case 'delete':
                        // TODO: Implement bulk delete with validation
                        successMessage = `${selectedIds.length} student(s) processed for deletion.`;
                        break;
                }

                alert(successMessage + '\n\nNote: Bulk operations are in demo mode.');
                closeBulkModal();
                loadStudentsTable(); // Refresh the table
                
            } catch (error) {
                console.error('Error performing bulk action:', error);
                alert('Error performing bulk action. Please try again.');
            }
        }

        // Load statistics with calculated data
        // Load statistics from database
        async function loadStatistics() {
            try {
                const response = await fetch('api/members.php?action=list_students');
                const result = await response.json();
                
                if (result.success) {
                    const students = result.data || [];
                    const totalStudents = students.length;
                    const activeMembers = students.filter(s => s.Status === 'Active').length;
                    const totalBooksIssued = students.reduce((sum, s) => sum + (parseInt(s.BooksIssued) || 0), 0);
                    
                    // Calculate expired memberships
                    const today = new Date();
                    const expired = students.filter(s => new Date(s.ValidTill) <= today).length;

                    document.getElementById('totalStudents').textContent = totalStudents.toLocaleString();
                    document.getElementById('activeMembers').textContent = activeMembers.toLocaleString();
                    document.getElementById('booksIssued').textContent = totalBooksIssued.toLocaleString();
                    document.getElementById('overdueBooks').textContent = expired.toLocaleString();
                } else {
                    // Show dash if failed
                    document.getElementById('totalStudents').textContent = '-';
                    document.getElementById('activeMembers').textContent = '-';
                    document.getElementById('booksIssued').textContent = '-';
                    document.getElementById('overdueBooks').textContent = '-';
                }
            } catch (error) {
                console.error('Error loading statistics:', error);
                // Show dash on error
                document.getElementById('totalStudents').textContent = '-';
                document.getElementById('activeMembers').textContent = '-';
                document.getElementById('booksIssued').textContent = '-';
                document.getElementById('overdueBooks').textContent = '-';
            }
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

        // Attach showTab to the global window object
        window.showTab = showTab;

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadStatistics();
            loadStudentsTable();
        });

        async function viewQRCode(studentId) {
            try {
                const response = await fetch(`api/members.php?action=get_qr_code&studentId=${studentId}`);
                const result = await response.json();

                if (result.success) {
                    const qrCodeData = result.qrCode;
                    const qrModal = document.getElementById('qrCodeModal');
                    const qrImage = qrModal.querySelector('img');

                    qrImage.src = `data:image/png;base64,${qrCodeData}`;
                    qrModal.style.display = 'block';
                } else {
                    alert('Error fetching QR code: ' + result.message);
                }
            } catch (error) {
                console.error('Error fetching QR code:', error);
                alert('Error fetching QR code. Please try again.');
            }
        }
    </script>

    <!-- Bulk Operations Modal -->
    <div id="bulkOperationsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-tasks"></i>
                    Bulk Operations
                </h3>
                <button class="close" onclick="closeBulkModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div style="margin-bottom: 20px;">
                    <p style="color: #6c757d; margin-bottom: 15px;">
                        Select an action to perform on multiple students at once. You can select students from the table and then choose one of the operations below.
                    </p>
                </div>

                <!-- Status Operations -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-user-check"></i>
                        Status Management
                    </div>
                    <div class="form-row">
                        <button class="btn btn-success" onclick="performBulkAction('activate')" style="flex: 1;">
                            <i class="fas fa-user-check"></i>
                            Activate Members
                        </button>
                        <button class="btn btn-secondary" onclick="performBulkAction('deactivate')" style="flex: 1;">
                            <i class="fas fa-user-times"></i>
                            Deactivate Members
                        </button>
                        <button class="btn btn-warning" onclick="performBulkAction('suspend')" style="flex: 1;">
                            <i class="fas fa-user-slash"></i>
                            Suspend Members
                        </button>
                    </div>
                </div>

                <!-- Membership Operations -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-calendar-alt"></i>
                        Membership Operations
                    </div>
                    <div class="form-row">
                        <button class="btn btn-info" onclick="performBulkAction('extend')" style="flex: 1;">
                            <i class="fas fa-calendar-plus"></i>
                            Extend Validity
                        </button>
                        <button class="btn btn-primary" onclick="performBulkAction('regenerate-qr')" style="flex: 1;">
                            <i class="fas fa-qrcode"></i>
                            Regenerate QR Codes
                        </button>
                    </div>
                </div>

                <!-- Communication Operations -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-envelope"></i>
                        Communication
                    </div>
                    <div class="form-row">
                        <button class="btn btn-primary" onclick="performBulkAction('send-notification')" style="flex: 1;">
                            <i class="fas fa-bell"></i>
                            Send Notification
                        </button>
                        <button class="btn btn-info" onclick="performBulkAction('send-reminder')" style="flex: 1;">
                            <i class="fas fa-clock"></i>
                            Send Due Reminders
                        </button>
                    </div>
                </div>

                <!-- Data Operations -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-database"></i>
                        Data Operations
                    </div>
                    <div class="form-row">
                        <button class="btn btn-success" onclick="performBulkAction('export')" style="flex: 1;">
                            <i class="fas fa-download"></i>
                            Export Selected Data
                        </button>
                        <button class="btn btn-warning" onclick="performBulkAction('backup')" style="flex: 1;">
                            <i class="fas fa-archive"></i>
                            Backup Records
                        </button>
                    </div>
                </div>

                <!-- Dangerous Operations -->
                <div class="form-section" style="border-color: #dc3545; background-color: #fff5f5;">
                    <div class="section-title" style="color: #dc3545; border-color: #dc3545;">
                        <i class="fas fa-exclamation-triangle"></i>
                        Dangerous Operations
                    </div>
                    <div class="form-row">
                        <button class="btn" onclick="performBulkAction('delete')"
                            style="flex: 1; background-color: #dc3545; color: white;">
                            <i class="fas fa-trash-alt"></i>
                            Delete Selected Students
                        </button>
                    </div>
                    <p style="color: #dc3545; font-size: 12px; margin-top: 10px; margin-bottom: 0;">
                        <i class="fas fa-warning"></i>
                        Warning: Deletion is permanent and cannot be undone. Use with extreme caution.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeBulkModal()">
                    <i class="fas fa-times"></i>
                    Close
                </button>
            </div>
        </div>
    </div>
</body>

</html>